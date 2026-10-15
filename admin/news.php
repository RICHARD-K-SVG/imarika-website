<?php
require __DIR__ . '/includes/require-login.php';
require __DIR__ . '/../includes/db-config.php';

$pdo = getDbConnection();
$message = '';
$categories = ['Announcements', 'Events', 'Sports', 'Academic Results'];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $pdo !== null) {
    if (isset($_POST['add_post'])) {
        $title = trim($_POST['title'] ?? '');
        $category = $_POST['category'] ?? '';
        $snippet = trim($_POST['snippet'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $pinned = !empty($_POST['pinned']) ? 1 : 0;

        if ($title !== '' && $snippet !== '' && in_array($category, $categories, true)) {
            if ($pinned) {
                $pdo->exec('UPDATE news SET pinned = 0'); // only one pinned post at a time
            }
            $stmt = $pdo->prepare(
                'INSERT INTO news (title, category, snippet, content, pinned) VALUES (:title, :category, :snippet, :content, :pinned)'
            );
            $stmt->execute(['title' => $title, 'category' => $category, 'snippet' => $snippet, 'content' => $content, 'pinned' => $pinned]);
            $message = 'Post published.';
        } else {
            $message = 'Please fill in the title, category, and snippet.';
        }
    } elseif (isset($_POST['delete_id'])) {
        $pdo->prepare('DELETE FROM news WHERE id = :id')->execute(['id' => (int) $_POST['delete_id']]);
        $message = 'Post deleted.';
    } elseif (isset($_POST['pin_id'])) {
        $pdo->exec('UPDATE news SET pinned = 0');
        $pdo->prepare('UPDATE news SET pinned = 1 WHERE id = :id')->execute(['id' => (int) $_POST['pin_id']]);
        $message = 'Post pinned.';
    }
}

$rows = $pdo !== null ? $pdo->query('SELECT * FROM news ORDER BY pinned DESC, created_at DESC')->fetchAll(PDO::FETCH_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>News | Imarika Admin</title>
  <link rel="icon" type="image/png" href="../assets/images/Sise.png">
  <link rel="stylesheet" href="../styles.css">
  <link rel="stylesheet" href="admin.css?v=20261015-logo">
</head>
<body>
  <?php require __DIR__ . '/includes/admin-nav.php'; ?>
  <main class="admin-main">
    <h1>News Posts</h1>
    <?php if ($message): ?><p class="admin-success"><?= htmlspecialchars($message) ?></p><?php endif; ?>

    <div class="admin-form-card">
      <h2>Add New Post</h2>
      <form method="POST">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" required>

        <label for="category">Category</label>
        <select id="category" name="category" required>
          <?php foreach ($categories as $c): ?><option value="<?= $c ?>"><?= $c ?></option><?php endforeach; ?>
        </select>

        <label for="snippet">Snippet (short summary shown on the card)</label>
        <textarea id="snippet" name="snippet" required></textarea>

        <label for="content">Full content (optional, saved for future use)</label>
        <textarea id="content" name="content"></textarea>

        <label class="admin-checkbox"><input type="checkbox" name="pinned" value="1"> Pin this as the highlighted notice</label>

        <button type="submit" name="add_post" value="1" class="btn-primary">Publish Post</button>
      </form>
    </div>

    <?php if (!$rows): ?>
      <p>No news posts yet.</p>
    <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Title</th><th>Category</th><th>Pinned</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= htmlspecialchars($r['category']) ?></td>
            <td><?= $r['pinned'] ? 'Yes' : '—' ?></td>
            <td><?= htmlspecialchars($r['created_at']) ?></td>
            <td>
              <a href="news-edit.php?id=<?= (int) $r['id'] ?>" class="admin-btn-sm" style="text-decoration:none;display:inline-block">Edit</a>
              <?php if (!$r['pinned']): ?>
              <form method="POST" class="admin-inline-form">
                <input type="hidden" name="pin_id" value="<?= (int) $r['id'] ?>">
                <button type="submit" class="admin-btn-sm">Pin</button>
              </form>
              <?php endif; ?>
              <form method="POST" class="admin-inline-form" onsubmit="return confirm('Delete this post?');">
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
