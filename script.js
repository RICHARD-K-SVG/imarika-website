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

/* ---- Gallery lightbox: click to open, Next/Previous arrows, keyboard nav ---- */
function initGalleryLightbox() {
  const items = Array.from(document.querySelectorAll('.gallery-item'));
  const lightbox = document.querySelector('.lightbox');
  if (!items.length || !lightbox) return;

  const lightboxImg = lightbox.querySelector('img');
  const closeBtn = lightbox.querySelector('.lightbox-close');
  const prevBtn = lightbox.querySelector('.lightbox-prev');
  const nextBtn = lightbox.querySelector('.lightbox-next');

  let currentIndex = 0;
  let lastFocused = null;

  function show(index) {
    // Wrap around in both directions so Next/Previous cycle through all photos
    currentIndex = (index + items.length) % items.length;
    const img = items[currentIndex].querySelector('img');
    lightboxImg.src = img.src;
    lightboxImg.alt = img.alt;
  }

  function open(index) {
    lastFocused = document.activeElement;
    show(index);
    lightbox.classList.add('open');
    closeBtn.focus();
  }

  function close() {
    lightbox.classList.remove('open');
    lightboxImg.src = '';
    if (lastFocused) lastFocused.focus();
  }

  items.forEach((item, index) => {
    item.addEventListener('click', () => open(index));
  });

  closeBtn.addEventListener('click', close);
  prevBtn.addEventListener('click', () => show(currentIndex - 1));
  nextBtn.addEventListener('click', () => show(currentIndex + 1));

  // Click on the dark backdrop (not the image or buttons) closes it
  lightbox.addEventListener('click', (e) => {
    if (e.target === lightbox) close();
  });

  document.addEventListener('keydown', (e) => {
    if (!lightbox.classList.contains('open')) return;
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowRight') show(currentIndex + 1);
    if (e.key === 'ArrowLeft') show(currentIndex - 1);
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
