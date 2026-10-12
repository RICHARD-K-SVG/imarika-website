// ==========================================================================
// Imarika School — footer visitor counter
// Counts one visit per browsing session, then shows the running total with
// a count-up animation when the footer scrolls into view.
// ==========================================================================

document.addEventListener('DOMContentLoaded', async () => {
  const el = document.getElementById('visitor-count');
  if (!el) return;
  const box = el.closest('.visitor-counter');
  const KEY = 'imarika_visit_counted';
  const isNewSession = !sessionStorage.getItem(KEY);

  try {
    const res = await fetch('includes/visit-counter.php', { method: isNewSession ? 'POST' : 'GET' });
    const data = await res.json();
    if (!data.success) throw new Error('counter unavailable');
    if (isNewSession) sessionStorage.setItem(KEY, '1');
    showWhenVisible(el, data.total);
  } catch (err) {
    box.hidden = true; // fail quietly: no broken-looking footer if the counter is down
  }
});

function showWhenVisible(el, total) {
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduceMotion || !('IntersectionObserver' in window)) {
    el.textContent = total.toLocaleString();
    return;
  }
  const observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting) {
      observer.disconnect();
      countUp(el, total, 1400);
    }
  }, { threshold: 0.4 });
  observer.observe(el);
}

function countUp(el, target, duration) {
  const start = performance.now();
  function frame(now) {
    const t = Math.min((now - start) / duration, 1);
    const eased = 1 - Math.pow(1 - t, 3); // ease-out
    el.textContent = Math.round(target * eased).toLocaleString();
    if (t < 1) requestAnimationFrame(frame);
  }
  requestAnimationFrame(frame);
}
