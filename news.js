// ==========================================================================
// News & Updates — fetches posts from includes/news-fetch.php (PDO/MySQL)
// and renders the pinned notice + filterable grid.
// ==========================================================================

let allPosts = [];
let returnFocusElement = null;

document.addEventListener('DOMContentLoaded', loadNews);

async function loadNews() {
  const grid = document.getElementById('news-grid');
  try {
    const res = await fetch('includes/news-fetch.php');
    const data = await res.json();
    if (!data.success) throw new Error(data.message || 'Unknown error');

    allPosts = data.posts;
    renderPinned();
    renderGrid('all');
    initFilters();
    initModal();
  } catch (err) {
    grid.innerHTML = '<div class="news-error"><p>News updates are temporarily unavailable. Please check back later or contact the school.</p><a href="contact.html">Contact Imarika</a></div>';
  }
}

function renderPinned() {
  const box = document.getElementById('pinned-notice');
  const pinned = allPosts.find((p) => Number(p.pinned) === 1);

  if (!pinned) {
    box.hidden = true;
    return;
  }

  box.hidden = false;
  box.innerHTML = `
    <span class="pinned-label">Pinned Notice</span>
    <h2>${escapeHtml(pinned.title)}</h2>
    <p>${escapeHtml(pinned.snippet)}</p>
    <span class="pinned-date">${formatDate(pinned.created_at)}</span>
  `;
}

function renderGrid(filter) {
  const grid = document.getElementById('news-grid');
  const posts = allPosts.filter(
    (p) => Number(p.pinned) !== 1 && (filter === 'all' || p.category === filter)
  );

  if (!posts.length) {
    grid.innerHTML = '<p class="news-empty">No posts in this category yet.</p>';
    return;
  }

  grid.innerHTML = posts
    .map(
      (p) => `
    <article class="news-card">
      <span class="news-badge ${categoryClass(p.category)}">${escapeHtml(p.category)}</span>
      <h3>${escapeHtml(p.title)}</h3>
      <time datetime="${escapeHtml(p.created_at)}">${formatDate(p.created_at)}</time>
      <p>${escapeHtml(p.snippet)}</p>
      <button class="btn-secondary read-more" data-id="${p.id}">Read more</button>
    </article>
  `
    )
    .join('');

  // Re-attach click handlers each time the grid is re-rendered (filtering rebuilds the HTML)
  grid.querySelectorAll('.read-more').forEach((btn) => {
    btn.addEventListener('click', () => openModal(btn.dataset.id));
  });
}

function openModal(id) {
  const post = allPosts.find((p) => String(p.id) === String(id));
  if (!post) return;

  const modal = document.getElementById('news-modal') || document.querySelector('.news-modal');
  returnFocusElement = document.activeElement;
  document.getElementById('modal-badge').textContent = post.category;
  document.getElementById('modal-badge').className = 'news-badge ' + post.category.toLowerCase().replace(/\s+/g, '-');
  document.getElementById('modal-title').textContent = post.title;
  document.getElementById('modal-date').textContent = formatDate(post.created_at);
  // Fall back to the snippet if no full content was written for this post yet
  document.getElementById('modal-content').textContent = post.content && post.content.trim() !== '' ? post.content : post.snippet;

  modal.hidden = false;
  modal.setAttribute('aria-hidden', 'false');
  modal.querySelector('.news-modal-close').focus();
}

function initModal() {
  const modal = document.querySelector('.news-modal');
  if (!modal) return;
  const closeBtn = modal.querySelector('.news-modal-close');

  function close() {
    modal.hidden = true;
    modal.setAttribute('aria-hidden', 'true');
    if (returnFocusElement) returnFocusElement.focus();
  }

  closeBtn.addEventListener('click', close);
  modal.addEventListener('keydown', (e) => {
    if (e.key === 'Tab') {
      e.preventDefault();
      closeBtn.focus();
    }
  });
  modal.addEventListener('click', (e) => { if (e.target === modal) close(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !modal.hidden) close(); });
}

function initFilters() {
  const tabs = document.querySelectorAll('.filter-tab');
  const filterGroup = document.querySelector('.filter-tabs');
  if (filterGroup) filterGroup.hidden = false;
  tabs.forEach((btn) => {
    btn.addEventListener('click', () => {
      tabs.forEach((b) => b.setAttribute('aria-pressed', 'false'));
      btn.setAttribute('aria-pressed', 'true');
      renderGrid(btn.dataset.filter);
    });
  });
}

function categoryClass(category) {
  return String(category || '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str ?? '';
  return div.innerHTML;
}

function formatDate(mysqlDatetime) {
  const d = new Date(String(mysqlDatetime).replace(' ', 'T'));
  if (isNaN(d)) return '';
  return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}
