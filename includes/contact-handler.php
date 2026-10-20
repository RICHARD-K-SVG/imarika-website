<?php
/**
 * contact-handler.php
 * -----------------------------------------------------------------------
 * Processes the Contact page's 3-field form (Name, Email, Message).
 * Requires PHPMailer — see includes/vendor/README.md for install steps.
 *
 * Requires PHP-capable hosting (see PROJECT_STRUCTURE.md Section 7,
 * Open Action Item #1). Will NOT work on static hosts like Netlify or
 * GitHub Pages.
 * -----------------------------------------------------------------------
 */

header('Content-Type: application/json');

require __DIR__ . '/vendor/autoload.php'; // PHPMailer, via Composer — see vendor/README.md
require __DIR__ . '/db-config.php';
require __DIR__ . '/mail-config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function respond(bool $success, string $message = ''): void {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

 if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    respond(false, 'Method not allowed.');
}
if (!empty($_POST['website'])) { respond(false, 'Submission rejected.'); }

// ---- Validate input ----
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $message === '') {
    respond(false, 'Please fill in all fields.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Please enter a valid email address.');
}

// Basic length guards against abuse
if (strlen($name) > 200 || strlen($email) > 200 || strlen($message) > 5000) {
    respond(false, 'Submission too long.');
}

$pdo = getDbConnection();
if ($pdo === null) {
    error_log('Contact form: database is not configured or unavailable.');
    http_response_code(503);
    respond(false, 'We could not save your message right now. Please try again or contact the school by phone.');
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO inquiries (name, email, message) VALUES (:name, :email, :message)'
    );
    $stmt->execute(['name' => $name, 'email' => $email, 'message' => $message]);
} catch (PDOException $e) {
    error_log('Contact form database insert failed: ' . $e->getMessage());
    http_response_code(503);
    respond(false, 'We could not save your message right now. Please try again or contact the school by phone.');
}

$pdo = null;

$mailConfig = getSchoolMailConfig();
if ($mailConfig === null) {
    error_log('Contact form: SMTP notification is not configured; message was saved.');
    respond(true, 'Your message has been submitted successfully.');
}

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = $mailConfig['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $mailConfig['username'];
    $mail->Password   = $mailConfig['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $mailConfig['port'];
    $mail->Timeout    = 10;

    // Recipients
    $mail->setFrom($mailConfig['from_email'], 'Imarika Pre-Primary School');
    $mail->addAddress($mailConfig['school_email']);
    $mail->addReplyTo($email, $name);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'New Contact Form Submission — Imarika School Pre-Primary School Website';
    $mail->Body    = sprintf(
        '<p><strong>Name:</strong> %s</p><p><strong>Email:</strong> %s</p><p><strong>Message:</strong><br>%s</p>',
        htmlspecialchars($name),
        htmlspecialchars($email),
        nl2br(htmlspecialchars($message))
    );
    $mail->AltBody = "Name: $name\nEmail: $email\n\nMessage:\n$message";

    $mail->send();

    respond(true, 'Your message has been submitted successfully.');
} catch (Exception $e) {
    error_log('Mailer Error: ' . $mail->ErrorInfo);
    respond(true, 'Your message has been submitted successfully.');
}
