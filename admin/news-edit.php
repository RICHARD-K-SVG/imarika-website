<?php
require __DIR__ . '/includes/require-login.php';
require __DIR__ . '/../includes/db-config.php';

$pdo = getDbConnection();
$categories = ['Announcements', 'Events', 'Sports', 'Academic Results'];
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$message = '';

if (!$id || $pdo === null) { header('Location: news.php'); exit; }

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
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
            'UPDATE news SET title=:title, category=:category, snippet=:snippet, content=:content, pinned=:pinned WHERE id=:id'
        );
        $stmt->execute(['title' => $title, 'category' => $category, 'snippet' => $snippet, 'content' => $content, 'pinned' => $pinned, 'id' => $id]);
        header('Location: news.php');
        exit;
    }
    $message = 'Please fill in the title, category, and snippet.';
}

$stmt = $pdo->prepare('SELECT * FROM news WHERE id = :id');
$stmt->execute(['id' => $id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) { header('Location: news.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Post | Imarika Admin</title>
  <link rel="icon" type="image/png" href="../assets/images/Sise.png">
  <link rel="stylesheet" href="../styles.css">
  <link rel="stylesheet" href="admin.css?v=20261015-logo">
</head>
<body>
  <?php require __DIR__ . '/includes/admin-nav.php'; ?>
  <main class="admin-main">
    <h1>Edit Post</h1>
    <?php if ($message): ?><p class="admin-error"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <div class="admin-form-card">
      <form method="POST">
        <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>

        <label for="category">Category</label>
        <select id="category" name="category" required>
          <?php foreach ($categories as $c): ?>
            <option value="<?= $c ?>" <?= $post['category'] === $c ? 'selected' : '' ?>><?= $c ?></option>
          <?php endforeach; ?>
        </select>

        <label for="snippet">Snippet</label>
        <textarea id="snippet" name="snippet" required><?= htmlspecialchars($post['snippet']) ?></textarea>

        <label for="content">Full content</label>
        <textarea id="content" name="content"><?= htmlspecialchars($post['content'] ?? '') ?></textarea>

        <label class="admin-checkbox">
          <input type="checkbox" name="pinned" value="1" <?= $post['pinned'] ? 'checked' : '' ?>> Pin this as the highlighted notice
        </label>

        <button type="submit" class="btn-primary">Save Changes</button>
      </form>
    </div>
    <a href="news.php">&larr; Back to all posts</a>
  </main>
  <?php require __DIR__ . '/includes/admin-footer.php'; ?>
</body>
</html>
