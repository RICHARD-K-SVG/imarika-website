<?php
require __DIR__ . '/includes/require-login.php';
require __DIR__ . '/../includes/db-config.php';

$pdo = getDbConnection();
$message = $_SESSION['admin_news_flash'] ?? '';
unset($_SESSION['admin_news_flash']);
$error = '';
$categories = ['Announcements', 'Events', 'Sports', 'Academic Results'];
$csrfToken = $_SESSION['admin_news_csrf'] ?? '';
if ($csrfToken === '') {
    $csrfToken = bin2hex(random_bytes(32));
    $_SESSION['admin_news_csrf'] = $csrfToken;
}
$title = '';
$category = $categories[0];
$snippet = '';
$content = '';
$pinned = 0;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!is_string($_POST['csrf_token'] ?? null) || !hash_equals($csrfToken, $_POST['csrf_token'])) {
        $error = 'Your session has expired. Refresh the page and try again.';
    } elseif ($pdo === null) {
        $error = 'News updates are temporarily unavailable. Please try again later.';
    } else {
        try {
            if (isset($_POST['add_post'])) {
                $title = trim(is_string($_POST['title'] ?? null) ? $_POST['title'] : '');
                $category = $_POST['category'] ?? '';
                $snippet = trim(is_string($_POST['snippet'] ?? null) ? $_POST['snippet'] : '');
                $content = trim(is_string($_POST['content'] ?? null) ? $_POST['content'] : '');
                $pinned = !empty($_POST['pinned']) ? 1 : 0;

                if ($title !== '' && $snippet !== '' && in_array($category, $categories, true)) {
                    $pdo->beginTransaction();
                    if ($pinned) {
                        $pdo->exec('UPDATE news SET pinned = 0');
                    }
                    $stmt = $pdo->prepare(
                        'INSERT INTO news (title, category, snippet, content, pinned) VALUES (:title, :category, :snippet, :content, :pinned)'
                    );
                    $stmt->execute(['title' => $title, 'category' => $category, 'snippet' => $snippet, 'content' => $content, 'pinned' => $pinned]);
                    $pdo->commit();
                    $message = 'Post published.';
                } else {
                    $error = 'Enter a title and summary, and select a valid category.';
                }
            } elseif (isset($_POST['delete_id'])) {
                $stmt = $pdo->prepare('DELETE FROM news WHERE id = :id');
                $stmt->execute(['id' => (int) $_POST['delete_id']]);
                if ($stmt->rowCount()) {
                    $message = 'Post deleted.';
                } else {
                    $error = 'That post could not be found.';
                }
            } elseif (isset($_POST['pin_id'])) {
                $pinId = (int) $_POST['pin_id'];
                $pdo->beginTransaction();
                $check = $pdo->prepare('SELECT id FROM news WHERE id = :id FOR UPDATE');
                $check->execute(['id' => $pinId]);
                if ($check->fetchColumn() === false) {
                    $pdo->rollBack();
                    $error = 'That post could not be found.';
                } else {
                    $pdo->exec('UPDATE news SET pinned = 0');
                    $stmt = $pdo->prepare('UPDATE news SET pinned = 1 WHERE id = :id');
                    $stmt->execute(['id' => $pinId]);
                    $pdo->commit();
                    $message = 'Post pinned.';
                }
            }
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Admin news action failed: ' . $exception->getMessage());
            $error = 'The news update could not be saved. Please try again.';
        }
    }
}

if ($message !== '' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $_SESSION['admin_news_flash'] = $message;
    header('Location: news.php');
    exit;
}

$pageSize = 25;
$page = max(1, (int) ($_GET['page'] ?? 1));
$totalPosts = 0;
$pinnedPosts = 0;
$rows = [];
$totalPages = 0;
$listingAvailable = false;
if ($pdo !== null) {
    try {
        $summary = $pdo->query('SELECT COUNT(*) AS total, COALESCE(SUM(pinned), 0) AS pinned FROM news')->fetch(PDO::FETCH_ASSOC);
        $totalPosts = (int) $summary['total'];
        $pinnedPosts = (int) $summary['pinned'];
        $totalPages = max(1, (int) ceil($totalPosts / $pageSize));
        $page = min($page, $totalPages);

        $stmt = $pdo->prepare('SELECT id, title, category, pinned, created_at FROM news ORDER BY pinned DESC, created_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * $pageSize, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $listingAvailable = true;
    } catch (PDOException $exception) {
        error_log('Admin news listing failed: ' . $exception->getMessage());
        $error = 'News posts could not be loaded. Please try again later.';
    }
} else {
    $error = 'News updates are temporarily unavailable. Please try again later.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>News | Imarika Admin</title>
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
        <h1>News &amp; updates</h1>
        <p>Share school announcements, events and achievements with families.</p>
      </div>
      <a class="admin-news-create-link" href="#new-post"><span aria-hidden="true">+</span> Create a post</a>
    </div>

    <?php if ($message): ?><p class="admin-success" role="status"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="admin-error" role="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <section class="admin-news-stats" aria-label="News post overview">
      <div class="admin-news-stat">
        <span class="admin-news-stat-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21V5.5Z"/><path d="M4 17.5A2.5 2.5 0 0 1 6.5 15H20M8 7h8M8 10h8"/></svg>
        </span>
        <span class="admin-news-stat-label">All posts</span>
        <strong><?= number_format($totalPosts) ?></strong>
      </div>
      <div class="admin-news-stat">
        <span class="admin-news-stat-icon admin-news-stat-icon-pinned" aria-hidden="true">
          <svg viewBox="0 0 24 24"><path d="m16 3 5 5-4 1-4 4-1 5-2-2-5 5-2-2 5-5-2-2 5-1 4-4 1-4Z"/></svg>
        </span>
        <span class="admin-news-stat-label">Highlighted</span>
        <strong><?= number_format($pinnedPosts) ?></strong>
      </div>
    </section>

    <section class="admin-news-composer" id="new-post" aria-labelledby="new-post-title">
      <div class="admin-news-section-heading">
        <div>
          <p class="admin-page-eyebrow">Write something new</p>
          <h2 id="new-post-title">Create a post</h2>
          <p>Your summary appears on the public news page. Full content is optional.</p>
        </div>
      </div>
      <form method="POST" class="admin-news-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <div class="admin-news-fields">
          <div class="admin-news-field admin-news-title-field">
            <label for="title">Post title</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>" maxlength="255" required>
          </div>
          <div class="admin-news-field">
            <label for="category">Category</label>
            <select id="category" name="category" required>
              <?php foreach ($categories as $c): ?><option value="<?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>" <?= $category === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="admin-news-field admin-news-field-wide">
            <label for="snippet">Short summary <span>Up to 300 characters</span></label>
            <textarea id="snippet" name="snippet" maxlength="300" rows="3" required><?= htmlspecialchars($snippet) ?></textarea>
          </div>
          <div class="admin-news-field admin-news-field-wide">
            <label for="content">Full content <span>Optional</span></label>
            <textarea id="content" name="content" rows="5"><?= htmlspecialchars($content) ?></textarea>
          </div>
        </div>
        <div class="admin-news-form-footer">
          <label class="admin-news-pin-option">
            <input type="checkbox" name="pinned" value="1" <?= $pinned ? 'checked' : '' ?>>
            <span><strong>Highlight this post</strong><small>Show it as the featured notice on the public news page.</small></span>
          </label>
          <button type="submit" name="add_post" value="1" class="btn-primary">Publish post</button>
        </div>
      </form>
    </section>

    <section class="admin-news-list" aria-labelledby="news-list-title">
      <div class="admin-news-section-heading admin-news-list-heading">
        <div>
          <p class="admin-page-eyebrow">Published content</p>
          <h2 id="news-list-title">Manage posts</h2>
        </div>
        <span class="admin-news-results"><?= number_format($totalPosts) ?> <?= $totalPosts === 1 ? 'post' : 'posts' ?></span>
      </div>
      <?php if ($listingAvailable && !$rows): ?>
        <div class="admin-news-empty">
          <h3>No posts yet</h3>
          <p>Your published news updates will appear here. Create your first post above.</p>
        </div>
      <?php elseif ($listingAvailable): ?>
      <div class="admin-table-wrap admin-news-table-wrap">
      <table class="admin-table">
        <thead><tr><th scope="col">Post</th><th scope="col">Category</th><th scope="col">Status</th><th scope="col">Published</th><th scope="col">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td data-label="Post"><strong class="admin-news-post-title"><?= htmlspecialchars($r['title']) ?></strong></td>
            <td data-label="Category"><span class="admin-news-category"><?= htmlspecialchars($r['category']) ?></span></td>
            <td data-label="Status"><?php if ($r['pinned']): ?><span class="admin-news-status">Highlighted</span><?php else: ?><span class="admin-news-status admin-news-status-regular">Published</span><?php endif; ?></td>
            <td data-label="Published"><time datetime="<?= htmlspecialchars($r['created_at'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(date('M j, Y', strtotime($r['created_at']))) ?></time></td>
            <td data-label="Actions" class="admin-news-actions">
              <a href="news-edit.php?id=<?= (int) $r['id'] ?>" class="admin-btn-sm admin-news-edit">Edit</a>
              <?php if (!$r['pinned']): ?>
              <form method="POST" class="admin-inline-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="pin_id" value="<?= (int) $r['id'] ?>">
                <button type="submit" class="admin-btn-sm">Highlight</button>
              </form>
              <?php endif; ?>
              <form method="POST" class="admin-inline-form" onsubmit="return confirm('Delete this post?');">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="delete_id" value="<?= (int) $r['id'] ?>">
                <button type="submit" class="admin-btn-sm admin-btn-danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      </div>
      <?php if ($listingAvailable && $totalPages > 1): ?>
        <nav class="admin-news-pagination" aria-label="News posts pages">
          <?php if ($page > 1): ?><a href="?page=<?= $page - 1 ?>" rel="prev">&larr; Previous</a><?php else: ?><span aria-disabled="true">&larr; Previous</span><?php endif; ?>
          <span>Page <?= number_format($page) ?> of <?= number_format($totalPages) ?></span>
          <?php if ($page < $totalPages): ?><a href="?page=<?= $page + 1 ?>" rel="next">Next &rarr;</a><?php else: ?><span aria-disabled="true">Next &rarr;</span><?php endif; ?>
        </nav>
      <?php endif; ?>
      <?php endif; ?>
    </section>
  </main>
  <?php require __DIR__ . '/includes/admin-footer.php'; ?>
</body>
</html>
