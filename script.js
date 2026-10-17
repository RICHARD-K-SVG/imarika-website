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
  const closeButton = links && links.querySelector('.nav-close');
  if (!toggle || !links) return;

  function closeMenu(returnFocus = false) {
    links.classList.remove('open');
    toggle.setAttribute('aria-expanded', 'false');
    if (returnFocus) toggle.focus();
  }

  toggle.addEventListener('click', () => {
    const isOpen = links.classList.toggle('open');
    toggle.setAttribute('aria-expanded', String(isOpen));
  });

  if (closeButton) {
    closeButton.addEventListener('click', () => closeMenu(true));
  }

  links.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => closeMenu()));
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && links.classList.contains('open')) closeMenu(true);
  });
  document.addEventListener('click', (event) => {
    if (!links.contains(event.target) && !toggle.contains(event.target)) closeMenu();
  });
}

/* ---- Programs page: Pre-Primary / Primary tab switcher ---- */
function initTabs() {
  const tabButtons = document.querySelectorAll('.tab-btn');
  if (!tabButtons.length) return;

  function selectTab(btn) {
    const targetId = btn.getAttribute('aria-controls');
    tabButtons.forEach((tab) => {
      const selected = tab === btn;
      tab.setAttribute('aria-selected', String(selected));
      tab.setAttribute('tabindex', selected ? '0' : '-1');
    });
    document.querySelectorAll('.tab-panel').forEach((panel) => {
      panel.classList.toggle('active', panel.id === targetId);
    });
  }

  tabButtons.forEach((btn, index) => {
    btn.addEventListener('click', () => {
      selectTab(btn);
    });

    btn.addEventListener('keydown', (event) => {
      let nextIndex = index;
      if (event.key === 'ArrowRight') nextIndex = (index + 1) % tabButtons.length;
      if (event.key === 'ArrowLeft') nextIndex = (index - 1 + tabButtons.length) % tabButtons.length;
      if (event.key === 'Home') nextIndex = 0;
      if (event.key === 'End') nextIndex = tabButtons.length - 1;
      if (nextIndex === index) return;
      event.preventDefault();
      tabButtons[nextIndex].focus();
      selectTab(tabButtons[nextIndex]);
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
    lightboxImg.src = img.currentSrc || img.src;
    lightboxImg.alt = img.alt;
  }

  function open(index) {
    lastFocused = document.activeElement;
    show(index);
    lightbox.classList.add('open');
    lightbox.setAttribute('aria-hidden', 'false');
    closeBtn.focus();
  }

  function close() {
    lightbox.classList.remove('open');
    lightbox.setAttribute('aria-hidden', 'true');
    lightboxImg.removeAttribute('src');
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

  lightbox.addEventListener('keydown', (e) => {
    if (e.key !== 'Tab') return;
    const controls = [closeBtn, prevBtn, nextBtn];
    const first = controls[0];
    const last = controls[controls.length - 1];
    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
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
    status.textContent = 'Submitting...';
    status.className = 'form-status';

    try {
      const formData = new FormData(form);
      const response = await fetch('includes/contact-handler.php', {
        method: 'POST',
        body: formData,
      });
      const result = await response.json();

      if (result.success) {
        status.textContent = result.message || 'Your message has been received.';
        status.classList.add('success');
        form.reset();
      } else {
        status.textContent = result.message || 'Something went wrong. Please try again.';
        status.classList.add('error');
      }
    } catch (err) {
      status.textContent = 'Could not submit message. Please try again later.';
      status.classList.add('error');
    }
  });
}
