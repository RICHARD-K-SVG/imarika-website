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
    <span class="admin-brand"><img src="../assets/images/Sise-160.png" alt="Imarika Pre-Primary School logo" width="160" height="160">Imarika Pre-Primary School Admin</span>
    <button class="admin-nav-toggle" type="button" aria-label="Open admin menu" aria-expanded="false" aria-controls="admin-navigation">
      <span class="admin-nav-toggle-icon" aria-hidden="true"></span>
    </button>
    <nav class="admin-nav" id="admin-navigation" aria-label="Admin">
      <a href="dashboard.php"<?= navActive('dashboard.php', $currentPage) ?>>Dashboard</a>
      <a href="admissions.php"<?= navActive('admissions.php', $currentPage) ?>>Admissions</a>
      <a href="news.php"<?= ($currentPage === 'news.php' || $currentPage === 'news-edit.php') ? ' aria-current="page"' : '' ?>>News</a>
      <a href="contact.php"<?= navActive('contact.php', $currentPage) ?>>Messages</a>
      <a href="change-password.php"<?= navActive('change-password.php', $currentPage) ?>>Change Password</a>
      <form method="POST" action="logout.php" class="admin-nav-logout-form">
        <input type="hidden" name="admin_csrf_token" value="<?= htmlspecialchars(adminCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="admin-logout">Log Out</button>
      </form>
    </nav>
  </div>
</header>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('.admin-nav-toggle');
    const nav = document.querySelector('.admin-nav');
    const header = document.querySelector('.admin-header');
    if (!toggle || !nav || !header) return;

    const setMenuOpen = (isOpen) => {
      nav.classList.toggle('open', isOpen);
      toggle.setAttribute('aria-expanded', String(isOpen));
      toggle.setAttribute('aria-label', isOpen ? 'Close admin menu' : 'Open admin menu');
    };

    toggle.addEventListener('click', () => {
      setMenuOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });

    nav.addEventListener('click', (event) => {
      if (event.target instanceof Element && event.target.closest('a, button')) {
        setMenuOpen(false);
      }
    });

    document.addEventListener('click', (event) => {
      if (toggle.getAttribute('aria-expanded') === 'true' && event.target instanceof Node && !header.contains(event.target)) {
        setMenuOpen(false);
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
        setMenuOpen(false);
        toggle.focus();
      }
    });

    window.addEventListener('resize', () => {
      if (window.matchMedia('(min-width: 1121px)').matches) {
        setMenuOpen(false);
      }
    });
  });
</script>
