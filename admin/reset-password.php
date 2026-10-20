<?php
require __DIR__ . '/../includes/db-config.php';
$pdo = getDbConnection();
$tokenValue = $_GET['token'] ?? $_POST['token'] ?? '';
$token = is_string($tokenValue) && preg_match('/\A[a-f0-9]{64}\z/', $tokenValue) ? $tokenValue : '';
$error = '';
$success = false;

$admin = null;
if ($pdo === null) {
    $error = 'Password reset is temporarily unavailable. Please try again later.';
} elseif ($token !== '') {
    try {
        $stmt = $pdo->prepare('SELECT id, reset_expires FROM admin_users WHERE reset_token = :t');
        $stmt->execute(['t' => $token]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $exception) {
        error_log('Password reset token lookup failed: ' . $exception->getMessage());
        $error = 'Password reset is temporarily unavailable. Please try again later.';
    }
}

$expiresAt = $admin && !empty($admin['reset_expires']) ? strtotime($admin['reset_expires']) : false;
$isValid = $admin && $expiresAt !== false && $expiresAt > time();

if ($pdo !== null && !$isValid && $error === '') {
    $error = 'This reset link is invalid or has expired. Please request a new one.';
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $pdo !== null && $isValid) {
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!is_string($new) || !is_string($confirm)) {
        $error = 'Please enter a valid password.';
    } elseif (strlen($new) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($new !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE admin_users SET password_hash = :h, reset_token = NULL, reset_expires = NULL, failed_attempts = 0, locked_until = NULL WHERE id = :id AND reset_token = :t AND reset_expires > NOW()');
            $stmt->execute(['h' => $hash, 'id' => $admin['id'], 't' => $token]);
            $success = $stmt->rowCount() === 1;
            if (!$success) {
                $error = 'This reset link is invalid or has expired. Please request a new one.';
                $isValid = false;
            }
        } catch (PDOException $exception) {
            error_log('Password reset update failed: ' . $exception->getMessage());
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
  <title>Reset Password | Imarika Admin</title>
  <link rel="icon" type="image/png" href="../assets/images/Sise-160.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600&amp;family=Work+Sans:wght@400;500&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../styles.css?v=20261020-responsive-perf1">
  <link rel="stylesheet" href="admin.css?v=20261020-responsive-perf1">
</head>
<body class="admin-login-body">
  <div class="admin-login-card">
    <div style="text-align:center;margin-bottom:var(--space-2)"><img src="../assets/images/Sise-160.png" alt="Imarika Pre-Primary School logo" width="160" height="160" style="display:block;margin:0 auto;height:48px;width:48px"></div>
    <h1>Reset Password</h1>
    <?php if ($success): ?>
      <p class="admin-success">Your password has been reset. You can now log in with your new password.</p>
      <p style="text-align:center"><a href="login.php">Go to login</a></p>
    <?php elseif (!$isValid): ?>
      <p class="admin-error"><?= htmlspecialchars($error) ?></p>
      <p style="text-align:center"><a href="forgot-password.php">Request a new link</a></p>
    <?php else: ?>
      <?php if ($error): ?><p class="admin-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
      <form method="POST" novalidate>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <label for="new_password">New password</label>
        <input type="password" id="new_password" name="new_password" required minlength="8" autocomplete="new-password">
        <label for="confirm_password">Confirm new password</label>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="8" autocomplete="new-password">
        <button type="submit" class="btn-primary">Reset Password</button>
      </form>
    <?php endif; ?>
  </div>
  <?php require __DIR__ . '/includes/admin-footer.php'; ?>
</body>
</html>
