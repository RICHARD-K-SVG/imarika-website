// ==========================================================================
// Imarika School — shared behaviors: nav toggle, tab switch, gallery lightbox
// ==========================================================================

document.addEventListener('DOMContentLoaded', () => {
  initNavToggle();
  initTabs();
  initGalleryLightbox();
  initContactForm();
});

/* ---- Mobile nav toggle ---- */
function initNavToggle() {
  const toggle = document.querySelector('.nav-toggle');
  const links = document.querySelector('.nav-links');
  if (!toggle || !links) return;

  toggle.addEventListener('click', () => {
    const isOpen = links.classList.toggle('open');
    toggle.setAttribute('aria-expanded', String(isOpen));
  });
}

/* ---- Programs page: Pre-Primary / Primary tab switcher ---- */
function initTabs() {
  const tabButtons = document.querySelectorAll('.tab-btn');
  if (!tabButtons.length) return;

  tabButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const targetId = btn.getAttribute('aria-controls');

      tabButtons.forEach((b) => b.setAttribute('aria-selected', 'false'));
      btn.setAttribute('aria-selected', 'true');

      document.querySelectorAll('.tab-panel').forEach((panel) => {
        panel.classList.toggle('active', panel.id === targetId);
      });
    });
  });
}

/* ---- Gallery lightbox ---- */
function initGalleryLightbox() {
  const items = document.querySelectorAll('.gallery-item');
  const lightbox = document.querySelector('.lightbox');
  if (!items.length || !lightbox) return;

  const lightboxImg = lightbox.querySelector('img');
  const closeBtn = lightbox.querySelector('.lightbox-close');

  items.forEach((item) => {
    item.addEventListener('click', () => {
      const img = item.querySelector('img');
      lightboxImg.src = img.src;
      lightboxImg.alt = img.alt;
      lightbox.classList.add('open');
    });
  });

  function closeLightbox() {
    lightbox.classList.remove('open');
    lightboxImg.src = '';
  }

  closeBtn.addEventListener('click', closeLightbox);
  lightbox.addEventListener('click', (e) => {
    if (e.target === lightbox) closeLightbox();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeLightbox();
  });
}

/* ---- Contact form: basic client-side validation + PHP submit ---- */
function initContactForm() {
  const form = document.querySelector('.contact-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const status = form.querySelector('.form-status');
    status.textContent = 'Sending...';
    status.className = 'form-status';

    try {
      const formData = new FormData(form);
      const response = await fetch('includes/contact-handler.php', {
        method: 'POST',
        body: formData,
      });
      const result = await response.json();

      if (result.success) {
        status.textContent = 'Thank you — your message has been sent!';
        status.classList.add('success');
        form.reset();
      } else {
        status.textContent = result.message || 'Something went wrong. Please try again.';
        status.classList.add('error');
      }
    } catch (err) {
      status.textContent = 'Could not send message. Please try again later.';
      status.classList.add('error');
    }
  });
}
