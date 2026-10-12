<?php
// Detects which admin page is currently open so the matching nav link
// gets the "active" underline — no need to edit every page individually.
$currentPage = basename($_SERVER['PHP_SELF']);
function navActive(string $file, string $current): string {
    return $file === $current ? ' aria-current="page"' : '';
}
?>
<header class="admin-header">
  <div class="admin-header-inner">
    <span class="admin-brand"><img src="../assets/images/Sise.png" alt="Imarika Pre-Primary School logo">Imarika Pre-Primary School Admin</span>
    <button class="admin-nav-toggle" aria-label="Toggle menu" aria-expanded="false">&#9776;</button>
    <nav class="admin-nav">
      <a href="dashboard.php"<?= navActive('dashboard.php', $currentPage) ?>>Dashboard</a>
      <a href="admissions.php"<?= navActive('admissions.php', $currentPage) ?>>Admissions</a>
      <a href="news.php"<?= ($currentPage === 'news.php' || $currentPage === 'news-edit.php') ? ' aria-current="page"' : '' ?>>News</a>
      <a href="contact.php"<?= navActive('contact.php', $currentPage) ?>>Messages</a>
      <a href="change-password.php"<?= navActive('change-password.php', $currentPage) ?>>Change Password</a>
      <a href="logout.php" class="admin-logout">Log Out</a>
    </nav>
  </div>
</header>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('.admin-nav-toggle');
    const nav = document.querySelector('.admin-nav');
    if (toggle && nav) {
      toggle.addEventListener('click', () => {
        const isOpen = nav.classList.toggle('open');
        toggle.setAttribute('aria-expanded', String(isOpen));
      });
    }
  });
</script>
