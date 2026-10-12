<?php
require __DIR__ . '/includes/require-login.php';
require __DIR__ . '/../includes/db-config.php';

$pdo = getDbConnection();
$counts = ['admissions' => 0, 'news' => 0, 'messages' => 0];
if ($pdo !== null) {
    $counts['admissions'] = (int) $pdo->query('SELECT COUNT(*) FROM admissions')->fetchColumn();
    $counts['news']       = (int) $pdo->query('SELECT COUNT(*) FROM news')->fetchColumn();
    $counts['messages']   = (int) $pdo->query('SELECT COUNT(*) FROM inquiries')->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | Imarika Pre-Primary School Admin</title>
  <link rel="icon" href="../assets/Sise.png">
  <link rel="stylesheet" href="../styles.css">
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <?php require __DIR__ . '/includes/admin-nav.php'; ?>
  <main class="admin-main">
    <h1>Dashboard</h1>
    <p>Welcome, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?>.</p>
    <div class="admin-cards">
      <a class="admin-card" href="admissions.php"><span class="admin-card-num"><?= $counts['admissions'] ?></span><span>Admission Applications</span></a>
      <a class="admin-card" href="news.php"><span class="admin-card-num"><?= $counts['news'] ?></span><span>News Posts</span></a>
      <a class="admin-card" href="contact.php"><span class="admin-card-num"><?= $counts['messages'] ?></span><span>Contact Messages</span></a>
    </div>
  </main>
</body>
</html>
