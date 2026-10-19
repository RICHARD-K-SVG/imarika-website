// ==========================================================================
// Imarika School — homepage photo slider
// Auto-plays when visible. Pauses while hovered or keyboard-focused and has a
// Pause/Play button. Respects reduced-motion preferences by default.
// ==========================================================================

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.slider').forEach(initSlider);
});

function initSlider(slider) {
  const track = slider.querySelector('.slides');
  const slides = Array.from(track.children);
  const prevBtn = slider.querySelector('.prev');
  const nextBtn = slider.querySelector('.next');
  const dotsBox = slider.querySelector('.slider-dots');
  const delay = parseInt(slider.dataset.autoplay, 10) || 5000;
  const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');

  let index = 0;
  let timer = null;
  let userPaused = motionPreference.matches;
  let manuallyPaused = false;
  let isInViewport = !('IntersectionObserver' in window);

  const dots = slides.map((_, i) => {
    const dot = document.createElement('button');
    dot.type = 'button';
    dot.className = 'slider-dot';
    dot.setAttribute('aria-label', 'Go to slide ' + (i + 1));
    dot.addEventListener('click', () => { goTo(i); restart(); });
    dotsBox.appendChild(dot);
    return dot;
  });

  // Pause / Play button
  const toggle = document.createElement('button');
  toggle.type = 'button';
  toggle.className = 'slider-toggle';
  slider.appendChild(toggle);
  function paintToggle() {
    toggle.textContent = userPaused ? '\u25B6' : '\u275A\u275A';
    toggle.setAttribute('aria-label', userPaused ? 'Play slideshow' : 'Pause slideshow');
  }
  toggle.addEventListener('click', () => {
    userPaused = !userPaused;
    manuallyPaused = userPaused;
    if (userPaused) stop(); else start();
    paintToggle();
  });
  paintToggle();

  function goTo(n) {
    index = (n + slides.length) % slides.length;
    track.style.transform = 'translateX(-' + index * 100 + '%)';
    slides.forEach((s, i) => s.setAttribute('aria-hidden', i === index ? 'false' : 'true'));
    dots.forEach((d, i) => d.setAttribute('aria-current', i === index ? 'true' : 'false'));
  }

  function start() {
    if (timer || userPaused || !isInViewport || document.hidden) return;
    timer = setInterval(() => goTo(index + 1), delay);
  }
  function stop() { clearInterval(timer); timer = null; }
  function restart() { stop(); start(); }

  motionPreference.addEventListener('change', (event) => {
    if (manuallyPaused) return;
    userPaused = event.matches;
    if (userPaused) stop(); else start();
    paintToggle();
  });

  if ('IntersectionObserver' in window) {
    const visibilityObserver = new IntersectionObserver(([entry]) => {
      isInViewport = entry.isIntersecting;
      if (isInViewport) start(); else stop();
    });
    visibilityObserver.observe(slider);
  }

  prevBtn.addEventListener('click', () => { goTo(index - 1); restart(); });
  nextBtn.addEventListener('click', () => { goTo(index + 1); restart(); });

  slider.addEventListener('mouseenter', stop);
  slider.addEventListener('mouseleave', start);
  // Only pause for keyboard focus, not after a mouse click on a button
  slider.addEventListener('focusin', (e) => { if (e.target.matches(':focus-visible')) stop(); });
  slider.addEventListener('focusout', start);
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) stop(); else start();
  });

  slider.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') { goTo(index - 1); restart(); }
    if (e.key === 'ArrowRight') { goTo(index + 1); restart(); }
  });

  // Swipe on touch screens
  let startX = null;
  slider.addEventListener('touchstart', (e) => { startX = e.touches[0].clientX; stop(); }, { passive: true });
  slider.addEventListener('touchend', (e) => {
    if (startX !== null) {
      const dx = e.changedTouches[0].clientX - startX;
      if (Math.abs(dx) > 40) goTo(dx < 0 ? index + 1 : index - 1);
    }
    startX = null;
    start();
  });

  goTo(0);
  start();
}
