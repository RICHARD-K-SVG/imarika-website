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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function respond(bool $success, string $message = ''): void {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

// ---- PLACEHOLDER: school's receiving email + SMTP credentials ----
// Update these once hosting/email is confirmed (Section 7, Open Action Items).
$SCHOOL_RECEIVING_EMAIL = 'richard2002kikoti@gmail.com';
$SMTP_HOST = 'sandbox.smtp.mailtrap.io';
$SMTP_USERNAME = '0e658f9e03b9b2';
$SMTP_PASSWORD = 'a4e9ecd10d1de6';
$SMTP_PORT = 587;

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = $SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = $SMTP_USERNAME;
    $mail->Password   = $SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $SMTP_PORT;

    // Recipients
    $mail->setFrom($SCHOOL_RECEIVING_EMAIL, 'Imarika School Pre-Primary School Website');
    $mail->addAddress($SCHOOL_RECEIVING_EMAIL);
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

    // ---- Optional: also store in MySQL (safe no-op if DB not configured) ----
    $pdo = getDbConnection();
    if ($pdo !== null) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO inquiries (name, email, message) VALUES (:name, :email, :message)'
            );
            $stmt->execute(['name' => $name, 'email' => $email, 'message' => $message]);
        } catch (PDOException $e) {
            // Log but don't fail the request — the email already sent successfully.
            error_log('DB insert failed: ' . $e->getMessage());
        }
    }

    respond(true, 'Message sent successfully.');
} catch (Exception $e) {
    error_log('Mailer Error: ' . $mail->ErrorInfo);
    respond(false, 'DEBUG: ' . $mail->ErrorInfo);
}
