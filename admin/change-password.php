<?php
require __DIR__ . '/includes/require-login.php';
require __DIR__ . '/../includes/db-config.php';

$pdo = getDbConnection();
$message = '';
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $pdo !== null) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, password_hash FROM admin_users WHERE username = :u');
    $stmt->execute(['u' => $_SESSION['admin_username']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin || !password_verify($current, $admin['password_hash'])) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new) < 8) {
        $error = 'New password must be at least 8 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New password and confirmation do not match.';
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE admin_users SET password_hash = :h WHERE id = :id')
            ->execute(['h' => $hash, 'id' => $admin['id']]);
        $message = 'Password changed successfully.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change Password | Imarika Admin</title>
  <link rel="icon" type="image/png" href="../assets/images/Sise.png">
  <link rel="stylesheet" href="../styles.css">
  <link rel="stylesheet" href="admin.css?v=20261015-logo">
</head>
<body>
  <?php require __DIR__ . '/includes/admin-nav.php'; ?>
  <main class="admin-main">
    <h1>Change Password</h1>
    <?php if ($message): ?><p class="admin-success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="admin-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <div class="admin-form-card">
      <form method="POST">
        <label for="current_password">Current password</label>
        <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
        <label for="new_password">New password</label>
        <input type="password" id="new_password" name="new_password" required minlength="8" autocomplete="new-password">
        <label for="confirm_password">Confirm new password</label>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="8" autocomplete="new-password">
        <button type="submit" class="btn-primary">Update Password</button>
      </form>
    </div>
  </main>
  <?php require __DIR__ . '/includes/admin-footer.php'; ?>
</body>
</html>
