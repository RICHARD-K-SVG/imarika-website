<?php
require __DIR__ . '/includes/require-login.php';
require __DIR__ . '/../includes/db-config.php';

$pdo = getDbConnection();
$message = '';
$error = '';
$csrfToken = adminCsrfToken();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!adminCsrfTokenIsValid()) {
        $error = 'Your session has expired. Refresh the page and try again.';
    } elseif ($pdo === null) {
        $error = 'Messages are temporarily unavailable. Please try again later.';
    } else {
        $id = filter_var($_POST['delete_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            $error = 'Select a valid message to delete.';
        } else {
            try {
                $stmt = $pdo->prepare('DELETE FROM inquiries WHERE id = :id');
                $stmt->execute(['id' => $id]);
                $message = $stmt->rowCount() > 0 ? 'Message deleted.' : 'That message could not be found.';
                if ($stmt->rowCount() === 0) {
                    $error = $message;
                    $message = '';
                }
            } catch (PDOException $exception) {
                error_log('Admin message deletion failed: ' . $exception->getMessage());
                $error = 'The message could not be deleted. Please try again.';
            }
        }
    }
}

$rows = [];
$listingAvailable = false;
if ($pdo === null) {
    $error = $error ?: 'Messages are temporarily unavailable. Please try again later.';
} else {
    try {
        $rows = $pdo->query('SELECT * FROM inquiries ORDER BY submitted_at DESC')->fetchAll(PDO::FETCH_ASSOC);
        $listingAvailable = true;
    } catch (PDOException $exception) {
        error_log('Admin messages listing failed: ' . $exception->getMessage());
        $error = 'Messages could not be loaded. Please check the database setup and try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Messages | Imarika Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600&amp;family=Work+Sans:wght@400;500&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../styles.css?v=20261016-responsive-perf1">
  <link rel="stylesheet" href="admin.css?v=20261016-logout-color6">
</head>
<body>
  <?php require __DIR__ . '/includes/admin-nav.php'; ?>
  <main class="admin-main">
    <h1>Contact Messages</h1>
    <?php if ($message): ?><p class="admin-success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="admin-error" role="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($listingAvailable && !$rows): ?>
      <p>No messages yet.</p>
    <?php elseif ($listingAvailable): ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Name</th><th>Email</th><th>Message</th><th>Received</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['name'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['email'] ?? '') ?></td>
            <td><?= nl2br(htmlspecialchars($r['message'] ?? '')) ?></td>
            <td><?= htmlspecialchars($r['submitted_at'] ?? '') ?></td>
            <td>
              <form method="POST" class="admin-inline-form" onsubmit="return confirm('Delete this message permanently?');">
                <input type="hidden" name="admin_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="delete_id" value="<?= (int) $r['id'] ?>">
                <button type="submit" class="admin-btn-sm admin-btn-danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </main>
  <?php require __DIR__ . '/includes/admin-footer.php'; ?>
</body>
</html>
