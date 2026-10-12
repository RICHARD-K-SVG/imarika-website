<?php
/**
 * admissions-handler.php
 * -----------------------------------------------------------------------
 * Receives the admission form + 2 uploads, then:
 *   1. Saves the uploaded files permanently (so they survive after this
 *      request ends — PHP's temp upload files get deleted automatically).
 *   2. Inserts the application into the `admissions` table via PDO. This
 *      is now the authoritative record of the application.
 *   3. Emails a copy via PHPMailer as a notification. This step is
 *      best-effort: if email fails but the database insert succeeded,
 *      the application is still considered successfully submitted,
 *      since it's safely stored and recoverable from phpMyAdmin.
 * -----------------------------------------------------------------------
 */

header('Content-Type: application/json');
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/db-config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function respond(bool $ok, string $msg = ''): void { echo json_encode(['success' => $ok, 'message' => $msg]); exit; }

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); respond(false, 'Method not allowed.'); }
if (!empty($_POST['website'])) respond(false, 'Submission rejected.'); // honeypot

$f = [];
foreach (['student_name','dob','gender','level','address','guardian_name','relationship','phone'] as $k) {
    $f[$k] = trim($_POST[$k] ?? '');
    if ($f[$k] === '' || strlen($f[$k]) > 500) respond(false, 'Please complete all required fields.');
}

// Email is optional — not all parents have one — but if they did enter
// something, it still needs to be a valid address.
$f['email'] = trim($_POST['email'] ?? '');
if ($f['email'] !== '' && !filter_var($f['email'], FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Please enter a valid email address, or leave it blank.');
}

if (!preg_match('/^[0-9+()\s-]{7,20}$/', $f['phone'])) respond(false, 'Please enter a valid phone number.');
$d = DateTime::createFromFormat('Y-m-d', $f['dob']);
if (!$d || $d > new DateTime()) respond(false, 'Please enter a valid date of birth.');
if (empty($_POST['consent'])) respond(false, 'Please confirm the information is accurate.');

// ---- Validate uploads, then move them out of PHP's temp folder into
// permanent storage. This folder sits inside includes/, which should
// already have a "Deny from all" .htaccess from the security hardening
// pass — that keeps these documents from being reachable by direct URL,
// since they're sensitive (birth certificates, a child's photo). ----
$img = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];
$rules = ['birth_certificate' => $img + ['pdf' => 'application/pdf'], 'student_photo' => $img];
$finfo = new finfo(FILEINFO_MIME_TYPE);

$uploadDir = __DIR__ . '/uploads/admissions/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$storedPaths = [];  // for the database (relative paths)
$attach = [];       // for the email (absolute paths)

foreach ($rules as $key => $allowed) {
    $u = $_FILES[$key] ?? null;
    if (!$u || $u['error'] !== UPLOAD_ERR_OK) respond(false, 'Please upload all required documents.');
    if ($u['size'] > 5 * 1024 * 1024) respond(false, 'Each file must be 5 MB or smaller.');

    $ext = strtolower(pathinfo($u['name'], PATHINFO_EXTENSION));
    if (!isset($allowed[$ext]) || $finfo->file($u['tmp_name']) !== $allowed[$ext]) respond(false, 'Unsupported file type.');

    // Unique filename so two applicants' files never collide or overwrite each other.
    $uniqueName = $key . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destination = $uploadDir . $uniqueName;

    if (!move_uploaded_file($u['tmp_name'], $destination)) {
        error_log("Admissions: failed to move uploaded file for $key");
        respond(false, 'Sorry, there was a problem saving your documents. Please try again.');
    }

    $storedPaths[$key] = 'includes/uploads/admissions/' . $uniqueName; // relative path, for the DB
    $attach[] = [$destination, $key . '.' . $ext];                     // absolute path, for the email
}

$savedToDb = false;
$emailed = false;
$dbErrorDebug = ''; 
$mailErrorDebug = '';

// ---- 1. Save to the database (the authoritative record) ----
$pdo = getDbConnection();
if ($pdo !== null) {
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO admissions
                (student_name, dob, gender, level, address, guardian_name, relationship, phone, email,
                 birth_certificate_path, student_photo_path)
             VALUES
                (:student_name, :dob, :gender, :level, :address, :guardian_name, :relationship, :phone, :email,
                 :birth_certificate_path, :student_photo_path)'
        );
        $stmt->execute([
            'student_name'           => $f['student_name'],
            'dob'                    => $f['dob'],
            'gender'                 => $f['gender'],
            'level'                  => $f['level'],
            'address'                => $f['address'],
            'guardian_name'          => $f['guardian_name'],
            'relationship'           => $f['relationship'],
            'phone'                  => $f['phone'],
            'email'                  => $f['email'] !== '' ? $f['email'] : null,
            'birth_certificate_path' => $storedPaths['birth_certificate'],
            'student_photo_path'     => $storedPaths['student_photo'],
        ]);
        $savedToDb = true;
    } catch (PDOException $e) {
        error_log('Admissions DB insert failed: ' . $e->getMessage());
         $dbErrorDebug = $e->getMessage();
    }
} else {
    error_log('Admissions: database not configured — skipping DB storage, relying on email only.');
}

// ---- 2. Email a notification copy (best-effort — doesn't block success) ----
// Copy these values from contact-handler.php (use real credentials only on your server)
$RECEIVING_EMAIL = 'richard2002kikoti@gmail.com';
$FROM_EMAIL      = 'noreply@imarikaschool.example';
$SMTP_HOST = 'sandbox.smtp.mailtrap.io'; 
$SMTP_USERNAME = '0e658f9e03b9b2'; 
$SMTP_PASSWORD = 'a4e9ecd10d1de6'; 
$SMTP_PORT = 587;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP(); $mail->Host = $SMTP_HOST; $mail->SMTPAuth = true;
    $mail->Username = $SMTP_USERNAME; $mail->Password = $SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; $mail->Port = $SMTP_PORT;
    $mail->setFrom($FROM_EMAIL, 'Imarika School Website');
    $mail->addAddress($RECEIVING_EMAIL);
    if ($f['email'] !== '') {
        $mail->addReplyTo($f['email'], $f['guardian_name']);
    }
    foreach ($attach as [$path, $name]) $mail->addAttachment($path, $name);

    $labels = ['student_name'=>'Student','dob'=>'Date of birth','gender'=>'Gender','level'=>'Level','address'=>'Home address',
               'guardian_name'=>'Guardian','relationship'=>'Relationship','phone'=>'Phone','email'=>'Email'];
    $rows = '';
    foreach ($labels as $k => $l) {
        $value = $f[$k] !== '' ? nl2br(htmlspecialchars($f[$k])) : '<em>Not provided</em>';
        $rows .= '<tr><td><strong>' . $l . '</strong></td><td>' . $value . '</td></tr>';
    }
    $mail->isHTML(true);
    $mail->Subject = 'New Admission Application — ' . $f['student_name'];
    $mail->Body = '<table cellpadding="6" border="1" style="border-collapse:collapse">' . $rows . '</table>';
    $mail->send();
    $emailed = true;
} catch (Exception $e) {
    error_log('Admissions mailer error: ' . $mail->ErrorInfo);
    $mailErrorDebug = $mail->ErrorInfo;
}

// ---- 3. Decide the final response ----
// Success as long as the application was captured in at least one place.
if ($savedToDb || $emailed) {
    respond(true, 'Application submitted.');
}

respond(false, 'Sorry, your application could not be submitted. Please try again later or contact the school directly.');
