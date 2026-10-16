<?php
require __DIR__ . '/includes/require-login.php';
require __DIR__ . '/../includes/db-config.php';

$pdo = getDbConnection();
$message = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['delete_id']) && $pdo !== null) {
    $pdo->prepare('DELETE FROM inquiries WHERE id = :id')->execute(['id' => (int) $_POST['delete_id']]);
    $message = 'Message deleted.';
}

$rows = $pdo !== null ? $pdo->query('SELECT * FROM inquiries ORDER BY submitted_at DESC')->fetchAll(PDO::FETCH_ASSOC) : [];
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
  <link rel="stylesheet" href="../styles.css">
  <link rel="stylesheet" href="admin.css?v=20261015-logo">
</head>
<body>
  <?php require __DIR__ . '/includes/admin-nav.php'; ?>
  <main class="admin-main">
    <h1>Contact Messages</h1>
    <?php if ($message): ?><p class="admin-success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <?php if (!$rows): ?>
      <p>No messages yet.</p>
    <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Name</th><th>Email</th><th>Message</th><th>Received</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['name']) ?></td>
            <td><?= htmlspecialchars($r['email']) ?></td>
            <td><?= nl2br(htmlspecialchars($r['message'])) ?></td>
            <td><?= htmlspecialchars($r['submitted_at']) ?></td>
            <td>
              <form method="POST" class="admin-inline-form" onsubmit="return confirm('Delete this message permanently?');">
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
