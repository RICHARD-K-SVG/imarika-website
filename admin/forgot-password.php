<?php
/**
 * forgot-password.php — no login required (that's the point).
 * Generates a one-hour, single-use reset token and emails a link
 * containing it to the admin account's registered email address.
 */
require __DIR__ . '/../includes/db-config.php';
require __DIR__ . '/../includes/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$pdo = getDbConnection();
$message = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $pdo !== null) {
    $username = trim($_POST['username'] ?? '');

    $stmt = $pdo->prepare('SELECT id, email FROM admin_users WHERE username = :u');
    $stmt->execute(['u' => $username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Always show the same message whether or not the account exists —
    // this stops someone from using this form to guess valid usernames.
    $message = 'If that account exists and has a registered email, a reset link has been sent to it.';

    if ($admin && !empty($admin['email'])) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); // valid for 1 hour
        $pdo->prepare('UPDATE admin_users SET reset_token = :t, reset_expires = :e WHERE id = :id')
            ->execute(['t' => $token, 'e' => $expires, 'id' => $admin['id']]);

        // Adjust this base URL once this is on real hosting instead of localhost.
        $resetLink = 'http://localhost/imarika-website/admin/reset-password.php?token=' . $token;

        // Copy your real SMTP values from contact-handler.php
        $SMTP_HOST = 'sandbox.smtp.mailtrap.io';
        $SMTP_USERNAME = '0e658f9e03b9b2';
        $SMTP_PASSWORD = 'a4e9ecd10d1de6';
        $SMTP_PORT = 587;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = $SMTP_USERNAME;
            $mail->Password = $SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $SMTP_PORT;
            $mail->setFrom('noreply@imarikaschool.example', 'Imarika School Admin');
            $mail->addAddress($admin['email']);
            $mail->Subject = 'Admin Password Reset Request';
            $mail->Body = "Someone requested a password reset for the Imarika School admin panel.\n\n"
                . "If this was you, click the link below within the next hour:\n{$resetLink}\n\n"
                . "If you didn't request this, you can ignore this email.";
            $mail->send();
        } catch (Exception $e) {
            error_log('Password reset email failed: ' . $mail->ErrorInfo);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password | Imarika Admin</title>
  <link rel="icon" href="../assets/Sise.png">
  <link rel="stylesheet" href="../styles.css">
  <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-login-body">
  <div class="admin-login-card">
    <div style="text-align:center;margin-bottom:var(--space-2)">
      <img src="../assets/images/Sise.png" alt="Imarika Pre-Primary School logo" style="display:block;margin:0 auto;height:48px"></div>
    <h1>Forgot Password</h1>
    <?php if ($message): ?><p class="admin-success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <form method="POST" novalidate>
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required autofocus>
      <button type="submit" class="btn-primary">Send Reset Link</button>
    </form>
    <p style="text-align:center;margin-top:var(--space-2)"><a href="login.php">Back to login</a></p>
  </div>
</body>
</html>
