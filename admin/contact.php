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
  <link rel="stylesheet" href="../styles.css?v=20261020-responsive-perf1">
  <link rel="stylesheet" href="admin.css?v=20261020-responsive-perf1">
  <script src="admin-record-search.js?v=20261019-records1" defer></script>
</head>
<body>
  <?php require __DIR__ . '/includes/admin-nav.php'; ?>
  <main class="admin-main">
    <div class="admin-page-heading">
      <div>
        <p class="admin-page-eyebrow">Family enquiries</p>
        <h1>Contact messages</h1>
        <p>Search messages from families and follow up on their questions.</p>
      </div>
    </div>
    <?php if ($message): ?><p class="admin-success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="admin-error" role="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($listingAvailable && !$rows): ?>
      <div class="admin-empty-state">
        <span class="admin-empty-state-icon" aria-hidden="true">+</span>
        <h2>No messages yet</h2>
        <p>Messages from the website contact form will appear here.</p>
      </div>
    <?php elseif ($listingAvailable): ?>
    <section class="admin-record-search" data-record-search data-search-label="messages" aria-label="Search contact messages">
      <div class="admin-record-search-bar">
        <label for="message-search">Find a message</label>
        <input type="search" id="message-search" data-record-search-input placeholder="Search name, email or message">
        <p class="admin-record-search-count" data-record-search-count aria-live="polite"><?= count($rows) ?> <?= count($rows) === 1 ? 'message' : 'messages' ?></p>
      </div>
      <div class="admin-table-wrap admin-responsive-records" data-record-search-results>
      <table class="admin-table admin-messages-table" data-record-search-table>
        <thead><tr><th>Name</th><th>Email</th><th>Message</th><th>Received</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td data-label="Name"><?= htmlspecialchars($r['name'] ?? '') ?></td>
            <td data-label="Email"><?= htmlspecialchars($r['email'] ?? '') ?></td>
            <td data-label="Message"><?= nl2br(htmlspecialchars($r['message'] ?? '')) ?></td>
            <td data-label="Received"><?= htmlspecialchars($r['submitted_at'] ?? '') ?></td>
            <td data-label="Actions">
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
      <p class="admin-record-search-empty" data-record-search-empty hidden>No messages match your search. Try another name, email address or word.</p>
    </section>
    <?php endif; ?>
  </main>
  <?php require __DIR__ . '/includes/admin-footer.php'; ?>
</body>
</html>
