<footer class="admin-footer">
  <div class="admin-footer-main">
    <div class="admin-footer-contact">
      <a class="admin-footer-brand" href="../index.html">
        <img src="../assets/images/Sise-160.png" alt="" width="160" height="160" loading="lazy" decoding="async">
        <span>Imarika Pre-Primary School</span>
      </a>
      <ul class="admin-footer-details">
        <li>
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 5.25-8 12-8 12S4 15.25 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
          <span>P. O. Box 1247 Iyumbu-Dodoma</span>
        </li>
        <li>
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 .5 4.2 2 2 0 0 1 2.5 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L6.1 10.3a16 16 0 0 0 6 6l1.66-1.34a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 20.5 17Z"/></svg>
          <span><a href="tel:+255652899596">+255 652 899 596</a> / <a href="tel:+255787276070">+255 787 276 070</a> / <a href="tel:+255764527179">+255 764 527 179</a></span>
        </li>
        <li>
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2Z"/><path d="m22 6-10 7L2 6"/></svg>
          <a href="mailto:imarikapreprimaryschool@gmail.com">imarikapreprimaryschool@gmail.com</a>
        </li>
      </ul>
    </div>
    <nav class="admin-footer-links" aria-label="School website">
      <a href="../about.html">About</a>
      <a href="../programs.html">Programs</a>
      <a href="../gallery.html">Gallery</a>
      <a href="../news.html">News</a>
      <a href="../admissions.html">Admissions</a>
      <a href="../contact.html">Contact</a>
    </nav>
  </div>
  <div class="admin-footer-stats">
    <div class="visitor-counter" role="status" aria-live="polite">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
      <span class="visitor-count" id="admin-visitor-count">&mdash;</span>
      <span class="visitor-label">Total visits</span>
    </div>
  </div>
  <div class="admin-footer-copy">&copy; <span id="admin-footer-year"></span> Imarika Pre-Primary School. All rights reserved.</div>
</footer>
<script>
  document.getElementById('admin-footer-year').textContent = new Date().getFullYear();
  fetch('../includes/visit-counter.php')
    .then((response) => {
      if (!response.ok) throw new Error('Unable to load visitor count.');
      return response.json();
    })
    .then((data) => {
      if (!data.success || !Number.isFinite(Number(data.total))) throw new Error('Invalid visitor count response.');
      document.getElementById('admin-visitor-count').textContent = Number(data.total).toLocaleString();
    })
    .catch((error) => {
      console.error('Admin footer visitor count:', error);
      document.querySelector('.admin-footer .visitor-counter').hidden = true;
    });
</script>
