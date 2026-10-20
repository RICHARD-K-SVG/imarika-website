<?php
require __DIR__ . '/includes/require-login.php';
require __DIR__ . '/../includes/db-config.php';

$pdo = getDbConnection();
$counts = ['admissions' => 0, 'news' => 0, 'messages' => 0];
$dashboardDataAvailable = $pdo !== null;
if ($dashboardDataAvailable) {
    try {
        $counts['admissions'] = (int) $pdo->query('SELECT COUNT(*) FROM admissions')->fetchColumn();
        $counts['news']       = (int) $pdo->query('SELECT COUNT(*) FROM news')->fetchColumn();
        $counts['messages']   = (int) $pdo->query('SELECT COUNT(*) FROM inquiries')->fetchColumn();
    } catch (PDOException $e) {
        error_log('Admin dashboard counts failed: ' . $e->getMessage());
        $counts = ['admissions' => 0, 'news' => 0, 'messages' => 0];
        $dashboardDataAvailable = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | Imarika Pre-Primary School Admin</title>
  <link rel="icon" type="image/png" href="../assets/images/Sise-160.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600&amp;family=Work+Sans:wght@400;500&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../styles.css?v=20261020-responsive-perf1">
  <link rel="stylesheet" href="admin.css?v=20261020-responsive-perf1">
</head>
<body>
  <?php require __DIR__ . '/includes/admin-nav.php'; ?>
  <main class="admin-main admin-dashboard-main">
    <section class="admin-dashboard-hero" aria-labelledby="admin-dashboard-title">
      <div class="admin-dashboard-hero-copy">
        <p class="admin-dashboard-eyebrow">School administration</p>
        <h1 id="admin-dashboard-title">Welcome back, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></h1>
        <p>Your school operations, content, and incoming messages — all in one place.</p>
      </div>
      <a class="admin-dashboard-website-link" href="../index.html">
        <span>View school website</span>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>
      </a>
      <span class="admin-dashboard-orbit admin-dashboard-orbit-one" aria-hidden="true"></span>
      <span class="admin-dashboard-orbit admin-dashboard-orbit-two" aria-hidden="true"></span>
    </section>

    <?php if (!$dashboardDataAvailable): ?>
      <p class="admin-dashboard-alert" role="status">Dashboard totals are temporarily unavailable. Please check the database connection and try again.</p>
    <?php endif; ?>

    <section class="admin-dashboard-section" aria-labelledby="admin-dashboard-stats-title">
      <div class="admin-dashboard-section-heading">
        <div>
          <p class="admin-dashboard-kicker">At a glance</p>
          <h2 id="admin-dashboard-stats-title">Your school overview</h2>
        </div>
        <span class="admin-dashboard-updated">Live totals</span>
      </div>
      <div class="admin-dashboard-stats">
        <a class="admin-dashboard-stat admin-dashboard-stat-admissions" href="admissions.php">
          <span class="admin-dashboard-stat-top">
            <span class="admin-dashboard-stat-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM20 8v6M23 11h-6"/></svg>
            </span>
            <span class="admin-dashboard-stat-arrow" aria-hidden="true">↗</span>
          </span>
          <span class="admin-dashboard-stat-number"><?= $dashboardDataAvailable ? number_format($counts['admissions']) : '&mdash;' ?></span>
          <span class="admin-dashboard-stat-label">Admission applications</span>
          <span class="admin-dashboard-stat-hint">Review and manage applications</span>
        </a>
        <a class="admin-dashboard-stat admin-dashboard-stat-news" href="news.php">
          <span class="admin-dashboard-stat-top">
            <span class="admin-dashboard-stat-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24"><path d="M4 4h16v16H4zM8 8h8M8 12h8M8 16h5"/></svg>
            </span>
            <span class="admin-dashboard-stat-arrow" aria-hidden="true">↗</span>
          </span>
          <span class="admin-dashboard-stat-number"><?= $dashboardDataAvailable ? number_format($counts['news']) : '&mdash;' ?></span>
          <span class="admin-dashboard-stat-label">Published news posts</span>
          <span class="admin-dashboard-stat-hint">Share updates with your community</span>
        </a>
        <a class="admin-dashboard-stat admin-dashboard-stat-messages" href="contact.php">
          <span class="admin-dashboard-stat-top">
            <span class="admin-dashboard-stat-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24"><path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.5Z"/></svg>
            </span>
            <span class="admin-dashboard-stat-arrow" aria-hidden="true">↗</span>
          </span>
          <span class="admin-dashboard-stat-number"><?= $dashboardDataAvailable ? number_format($counts['messages']) : '&mdash;' ?></span>
          <span class="admin-dashboard-stat-label">Contact messages</span>
          <span class="admin-dashboard-stat-hint">Keep up with family inquiries</span>
        </a>
      </div>
    </section>

    <section class="admin-dashboard-section admin-dashboard-actions-section" aria-labelledby="admin-dashboard-actions-title">
      <div class="admin-dashboard-section-heading">
        <div>
          <p class="admin-dashboard-kicker">Move things forward</p>
          <h2 id="admin-dashboard-actions-title">Quick actions</h2>
        </div>
      </div>
      <div class="admin-dashboard-actions">
        <a class="admin-dashboard-action" href="admissions.php">
          <span class="admin-dashboard-action-icon admin-dashboard-action-icon-gold" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
          </span>
          <span class="admin-dashboard-action-copy"><strong>Manage admissions</strong><span>Review new applications</span></span>
          <span class="admin-dashboard-action-arrow" aria-hidden="true">→</span>
        </a>
        <a class="admin-dashboard-action" href="news.php">
          <span class="admin-dashboard-action-icon admin-dashboard-action-icon-rose" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg>
          </span>
          <span class="admin-dashboard-action-copy"><strong>Publish a news post</strong><span>Share a school update</span></span>
          <span class="admin-dashboard-action-arrow" aria-hidden="true">→</span>
        </a>
        <a class="admin-dashboard-action" href="contact.php">
          <span class="admin-dashboard-action-icon admin-dashboard-action-icon-blue" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M4 4h16v13H8l-4 3zM8 9h8M8 13h5"/></svg>
          </span>
          <span class="admin-dashboard-action-copy"><strong>Open messages</strong><span>Respond to inquiries</span></span>
          <span class="admin-dashboard-action-arrow" aria-hidden="true">→</span>
        </a>
        <a class="admin-dashboard-action" href="change-password.php">
          <span class="admin-dashboard-action-icon admin-dashboard-action-icon-green" aria-hidden="true">
            <svg viewBox="0 0 24 24"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg>
          </span>
          <span class="admin-dashboard-action-copy"><strong>Account security</strong><span>Update your password</span></span>
          <span class="admin-dashboard-action-arrow" aria-hidden="true">→</span>
        </a>
      </div>
    </section>
  </main>
  <?php require __DIR__ . '/includes/admin-footer.php'; ?>
</body>
</html>
