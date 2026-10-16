<?php
/**
 * forgot-password.php — no login required (that's the point).
 * Generates a one-hour, single-use reset token and emails a link
 * containing it to the admin account's registered email address.
 */
require __DIR__ . '/../includes/db-config.php';
require __DIR__ . '/../includes/vendor/autoload.php';
require __DIR__ . '/../includes/mail-config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$pdo = getDbConnection();
$message = '';
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if ($pdo === null) {
        $error = 'Password reset is temporarily unavailable. Please try again later.';
    } else {
      try {
        $username = is_string($_POST['username'] ?? null) ? trim($_POST['username']) : '';

        $stmt = $pdo->prepare('SELECT id, email FROM admin_users WHERE username = :u');
        $stmt->execute(['u' => $username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Keep the response independent of whether an account exists.
        $message = 'If that account exists and has a registered email, a reset link will be sent to it.';

        if ($admin && !empty($admin['email'])) {
          $mailConfig = getSchoolMailConfig();
          $baseUrl = trim((string) getenv('IMARIKA_BASE_URL'));

          if ($mailConfig === null || filter_var($baseUrl, FILTER_VALIDATE_URL) === false) {
            error_log('Password reset: mail settings or IMARIKA_BASE_URL are not configured.');
          } else {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600);
            $pdo->prepare('UPDATE admin_users SET reset_token = :t, reset_expires = :e WHERE id = :id')
              ->execute(['t' => $token, 'e' => $expires, 'id' => $admin['id']]);

            $resetLink = rtrim($baseUrl, '/') . '/admin/reset-password.php?token=' . $token;
            $mail = new PHPMailer(true);
            try {
              $mail->isSMTP();
              $mail->Host = $mailConfig['host'];
              $mail->SMTPAuth = true;
              $mail->Username = $mailConfig['username'];
              $mail->Password = $mailConfig['password'];
              $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
              $mail->Port = $mailConfig['port'];
              $mail->setFrom($mailConfig['from_email'], 'Imarika School Admin');
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
      } catch (PDOException $exception) {
        error_log('Password reset request failed: ' . $exception->getMessage());
        $error = 'Password reset is temporarily unavailable. Please try again later.';
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
  <link rel="icon" type="image/png" href="../assets/images/Sise.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600&amp;family=Work+Sans:wght@400;500&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../styles.css?v=20261016-responsive-perf1">
  <link rel="stylesheet" href="admin.css?v=20261016-logout-color6">
</head>
<body class="admin-login-body">
  <div class="admin-login-card">
    <div style="text-align:center;margin-bottom:var(--space-2)">
      <img src="../assets/images/Sise.png" alt="Imarika Pre-Primary School logo" style="display:block;margin:0 auto;height:48px"></div>
    <h1>Forgot Password</h1>
    <?php if ($message): ?><p class="admin-success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="admin-error" role="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="POST" novalidate>
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required autofocus>
      <button type="submit" class="btn-primary">Send Reset Link</button>
    </form>
    <p style="text-align:center;margin-top:var(--space-2)"><a href="login.php">Back to login</a></p>
  </div>
  <?php require __DIR__ . '/includes/admin-footer.php'; ?>
</body>
</html>
