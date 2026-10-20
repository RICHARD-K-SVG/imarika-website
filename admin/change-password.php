<?php
require __DIR__ . '/includes/require-login.php';
require __DIR__ . '/../includes/db-config.php';

$pdo = getDbConnection();
$message = '';
$error = '';
$csrfToken = $_SESSION['admin_password_csrf'] ?? '';
if ($csrfToken === '') {
    $csrfToken = bin2hex(random_bytes(32));
    $_SESSION['admin_password_csrf'] = $csrfToken;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!is_string($current) || !is_string($new) || !is_string($confirm)) {
        $error = 'Please enter valid password values.';
    } elseif (!is_string($_POST['csrf_token'] ?? null) || !hash_equals($csrfToken, $_POST['csrf_token'])) {
        $error = 'Your session has expired. Refresh the page and try again.';
    } elseif ($pdo === null) {
        $error = 'Password changes are temporarily unavailable. Please try again later.';
    } else {
        try {
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
                $csrfToken = bin2hex(random_bytes(32));
                $_SESSION['admin_password_csrf'] = $csrfToken;
            }
        } catch (PDOException $exception) {
            error_log('Admin password change failed: ' . $exception->getMessage());
            $error = 'The password could not be changed. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change Password | Imarika Admin</title>
  <link rel="icon" type="image/png" href="../assets/images/Sise-160.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600&amp;family=Work+Sans:wght@400;500&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../styles.css?v=20261020-responsive-perf1">
  <link rel="stylesheet" href="admin.css?v=20261020-responsive-perf1">
</head>
<body>
  <?php require __DIR__ . '/includes/admin-nav.php'; ?>
  <main class="admin-main admin-password-main">
    <div class="admin-page-heading">
      <div>
        <p class="admin-page-eyebrow">Account security</p>
        <h1>Change password</h1>
        <p>Choose a strong, unique password to help keep your school account secure.</p>
      </div>
      <a class="admin-back-link" href="dashboard.php"><span aria-hidden="true">&larr;</span> Back to dashboard</a>
    </div>

    <?php if ($message): ?><p class="admin-success" role="status"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="admin-error" role="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <div class="admin-password-layout">
      <section class="admin-password-card" aria-labelledby="password-form-title">
        <div class="admin-password-card-heading">
          <span class="admin-password-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3"/><path d="M12 14v3"/></svg>
          </span>
          <div>
            <h2 id="password-form-title">Update your password</h2>
            <p>Enter your current password, then choose and confirm a new one.</p>
          </div>
        </div>
        <form class="admin-password-form" method="POST">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
          <div class="admin-password-field">
            <label for="current_password">Current password</label>
            <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
          </div>
          <div class="admin-password-field">
            <label for="new_password">New password</label>
            <input type="password" id="new_password" name="new_password" required minlength="8" autocomplete="new-password" aria-describedby="password-requirement">
          </div>
          <div class="admin-password-field">
            <label for="confirm_password">Confirm new password</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="8" autocomplete="new-password">
          </div>
          <p class="admin-password-hint" id="password-requirement">Use at least 8 characters. A longer, unique passphrase is even stronger.</p>
          <button type="submit" class="btn-primary">Update password</button>
        </form>
      </section>

      <aside class="admin-password-guidance" aria-labelledby="password-guidance-title">
        <p class="admin-page-eyebrow">A safer account</p>
        <h2 id="password-guidance-title">Password tips</h2>
        <ul>
          <li><span aria-hidden="true">&#10003;</span> Use at least 8 characters</li>
          <li><span aria-hidden="true">&#10003;</span> Choose a password you do not use elsewhere</li>
          <li><span aria-hidden="true">&#10003;</span> Consider a memorable phrase with several words</li>
        </ul>
        <p class="admin-password-private">Never share your password. School staff will not ask you to send it by email or message.</p>
      </aside>
    </div>
  </main>
  <?php require __DIR__ . '/includes/admin-footer.php'; ?>
</body>
</html>
