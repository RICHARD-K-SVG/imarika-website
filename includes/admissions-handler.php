<?php
/**
 * admissions-handler.php
 * -----------------------------------------------------------------------
 * Receives the admission form, then:
 *   1. Inserts the application into the `admissions` table via PDO. This
 *      is now the authoritative record of the application.
 *   2. Emails a copy via PHPMailer as a notification. This step is
 *      best-effort: if email fails but the database insert succeeded,
 *      the application is still considered successfully submitted,
 *      since it's safely stored and recoverable from phpMyAdmin.
 * -----------------------------------------------------------------------
 */

header('Content-Type: application/json');
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/db-config.php';
require __DIR__ . '/mail-config.php';
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

$savedToDb = false;
$emailed = false;

// ---- 1. Save to the database (the authoritative record) ----
$pdo = getDbConnection();
if ($pdo !== null) {
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO admissions
                (student_name, dob, gender, level, address, guardian_name, relationship, phone, email)
             VALUES
                (:student_name, :dob, :gender, :level, :address, :guardian_name, :relationship, :phone, :email)'
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
        ]);
        $savedToDb = true;
    } catch (PDOException $e) {
        error_log('Admissions DB insert failed: ' . $e->getMessage());
    }
} else {
    error_log('Admissions: database not configured — skipping DB storage, relying on email only.');
}

$pdo = null;

// ---- 2. Email a notification copy (best-effort — doesn't block success) ----
$mailConfig = getSchoolMailConfig();
if ($mailConfig === null) {
    error_log('Admissions: SMTP environment variables are not configured.');
} else {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $mailConfig['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $mailConfig['username'];
        $mail->Password = $mailConfig['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $mailConfig['port'];
        $mail->Timeout = 10;
        $mail->setFrom($mailConfig['from_email'], 'Imarika Pre-Primary School');
        $mail->addAddress($mailConfig['school_email']);
        if ($f['email'] !== '') {
            $mail->addReplyTo($f['email'], $f['guardian_name']);
        }
        $labels = ['student_name'=>'Student','dob'=>'Date of birth','gender'=>'Gender','level'=>'Level','address'=>'Home address',
                   'guardian_name'=>'Guardian','relationship'=>'Relationship','phone'=>'Phone','email'=>'Email'];
        $rows = '';
        foreach ($labels as $k => $label) {
            $value = $f[$k] !== '' ? nl2br(htmlspecialchars($f[$k])) : '<em>Not provided</em>';
            $rows .= '<tr><td><strong>' . $label . '</strong></td><td>' . $value . '</td></tr>';
        }
        $mail->isHTML(true);
        $mail->Subject = 'New Admission Application — ' . $f['student_name'];
        $mail->Body = '<table cellpadding="6" border="1" style="border-collapse:collapse">' . $rows . '</table>';
        $mail->send();
        $emailed = true;
    } catch (Exception $e) {
        error_log('Admissions mailer error: ' . $mail->ErrorInfo);
    }
}

// ---- 3. Decide the final response ----
// Success as long as the application was captured in at least one place.
if ($savedToDb || $emailed) {
    respond(true, 'Application submitted.');
}

http_response_code(503);
respond(false, 'Sorry, your application could not be submitted. Please try again later or contact the school directly.');
