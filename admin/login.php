<?php
session_start();
require __DIR__ . '/../includes/db-config.php';

$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $pdo = getDbConnection();
    if ($pdo === null) {
        $error = 'Database is not configured. Check includes/db-config.php.';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, password_hash, failed_attempts, locked_until FROM admin_users WHERE username = :u');
        $stmt->execute(['u' => $username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        $isLocked = $admin && !empty($admin['locked_until']) && strtotime($admin['locked_until']) > time();

        if ($isLocked) {
            $minutesLeft = (int) ceil((strtotime($admin['locked_until']) - time()) / 60);
            $error = "Too many failed attempts. Please try again in {$minutesLeft} minute(s).";
        } elseif ($admin && password_verify($password, $admin['password_hash'])) {
            // Successful login — clear any prior failed-attempt count.
            $pdo->prepare('UPDATE admin_users SET failed_attempts = 0, locked_until = NULL WHERE id = :id')
                ->execute(['id' => $admin['id']]);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            // Wrong password (or unknown username) — only track attempts for
            // real accounts, so this can't be used to guess which usernames exist.
            if ($admin) {
                $attempts = (int) $admin['failed_attempts'] + 1;
                $lockedUntil = $attempts >= 5 ? date('Y-m-d H:i:s', time() + 15 * 60) : null;
                $pdo->prepare('UPDATE admin_users SET failed_attempts = :a, locked_until = :l WHERE id = :id')
                    ->execute(['a' => $attempts, 'l' => $lockedUntil, 'id' => $admin['id']]);
            }
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | Imarika Pre-Primary School</title>
    <link rel="icon" type="image/png" href="../assets/images/Sise.png">
  <link rel="stylesheet" href="../styles.css">
  <link rel="stylesheet" href="admin.css?v=20261015-logo">
</head>
<body class="admin-login-body">
  <div class="admin-login-card">
    <div style="text-align:center;margin-bottom:var(--space-2)">
        <img src="../assets/images/Sise.png" alt="Imarika Pre-Primary School logo" style="display:block;margin:0 auto;height:48px"></div>
    <h1>Admin Login</h1>
    <?php if ($error): ?><p class="admin-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="POST" novalidate>
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required autofocus autocomplete="username">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required autocomplete="current-password">
      <button type="submit" class="btn-primary">Log In</button>
    </form>
    <p style="text-align:center;margin-top:var(--space-2)"><a href="forgot-password.php">Forgot password?</a></p>
  </div>
  <?php require __DIR__ . '/includes/admin-footer.php'; ?>
</body>
</html>
