<?php
require __DIR__ . '/includes/require-login.php';
require __DIR__ . '/../includes/db-config.php';

$pdo = getDbConnection();
$categories = ['Announcements', 'Events', 'Sports', 'Academic Results'];
$id = filter_var($_GET['id'] ?? $_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$message = '';
$csrfToken = $_SESSION['admin_news_csrf'] ?? '';
if ($csrfToken === '') {
    $csrfToken = bin2hex(random_bytes(32));
    $_SESSION['admin_news_csrf'] = $csrfToken;
}

if ($id === false || $pdo === null) { header('Location: news.php'); exit; }

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $title = trim(is_string($_POST['title'] ?? null) ? $_POST['title'] : '');
    $category = $_POST['category'] ?? '';
    $snippet = trim(is_string($_POST['snippet'] ?? null) ? $_POST['snippet'] : '');
    $content = trim(is_string($_POST['content'] ?? null) ? $_POST['content'] : '');
    $pinned = !empty($_POST['pinned']) ? 1 : 0;

    if (!is_string($_POST['csrf_token'] ?? null) || !hash_equals($csrfToken, $_POST['csrf_token'])) {
        $message = 'Your session has expired. Refresh the page and try again.';
    } elseif ($title !== '' && $snippet !== '' && in_array($category, $categories, true)) {
        try {
            $pdo->beginTransaction();
            if ($pinned) {
                $pdo->exec('UPDATE news SET pinned = 0'); // only one pinned post at a time
            }
            $stmt = $pdo->prepare(
                'UPDATE news SET title=:title, category=:category, snippet=:snippet, content=:content, pinned=:pinned WHERE id=:id'
            );
            $stmt->execute(['title' => $title, 'category' => $category, 'snippet' => $snippet, 'content' => $content, 'pinned' => $pinned, 'id' => $id]);
            $pdo->commit();
            header('Location: news.php');
            exit;
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Admin news edit failed: ' . $exception->getMessage());
            $message = 'The news update could not be saved. Please try again.';
        }
    } else {
        $message = 'Enter a title and summary, and select a valid category.';
    }
}

try {
    $stmt = $pdo->prepare('SELECT * FROM news WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    error_log('Admin news post load failed: ' . $exception->getMessage());
    header('Location: news.php');
    exit;
}
if (!$post) { header('Location: news.php'); exit; }
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $post['title'] = $title;
    $post['category'] = $category;
    $post['snippet'] = $snippet;
    $post['content'] = $content;
    $post['pinned'] = $pinned;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Post | Imarika Admin</title>
  <link rel="icon" type="image/png" href="../assets/images/Sise-160.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600&amp;family=Work+Sans:wght@400;500&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../styles.css?v=20261020-responsive-perf1">
  <link rel="stylesheet" href="admin.css?v=20261020-responsive-perf1">
</head>
<body>
  <?php require __DIR__ . '/includes/admin-nav.php'; ?>
  <main class="admin-main admin-news-main">
    <div class="admin-page-heading admin-news-heading">
      <div>
        <p class="admin-page-eyebrow">Content management</p>
        <h1>Edit post</h1>
        <p>Update this news item and keep families up to date.</p>
      </div>
      <a class="admin-back-link" href="news.php"><span aria-hidden="true">&larr;</span> Back to news</a>
    </div>
    <?php if ($message): ?><p class="admin-error" role="alert"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <section class="admin-news-composer" aria-labelledby="edit-post-title">
      <div class="admin-news-section-heading">
        <p class="admin-page-eyebrow">Post details</p>
        <h2 id="edit-post-title">Edit “<?= htmlspecialchars($post['title']) ?>”</h2>
        <p>Changes will appear on the public news page when saved.</p>
      </div>
      <form method="POST" class="admin-news-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
        <div class="admin-news-fields">
          <div class="admin-news-field">
            <label for="title">Post title</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($post['title']) ?>" maxlength="255" required>
          </div>
          <div class="admin-news-field">
            <label for="category">Category</label>
            <select id="category" name="category" required>
              <?php foreach ($categories as $c): ?>
                <option value="<?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>" <?= $post['category'] === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="admin-news-field admin-news-field-wide">
            <label for="snippet">Short summary <span>Up to 300 characters</span></label>
            <textarea id="snippet" name="snippet" maxlength="300" rows="3" required><?= htmlspecialchars($post['snippet']) ?></textarea>
          </div>
          <div class="admin-news-field admin-news-field-wide">
            <label for="content">Full content <span>Optional</span></label>
            <textarea id="content" name="content" rows="5"><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
          </div>
        </div>
        <div class="admin-news-form-footer">
          <label class="admin-news-pin-option">
            <input type="checkbox" name="pinned" value="1" <?= $post['pinned'] ? 'checked' : '' ?>>
            <span><strong>Highlight this post</strong><small>Show it as the featured notice on the public news page.</small></span>
          </label>
          <button type="submit" class="btn-primary">Save changes</button>
        </div>
      </form>
    </section>
  </main>
  <?php require __DIR__ . '/includes/admin-footer.php'; ?>
</body>
</html>
