document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('admission-form');
  if (!form) return;
  const status = form.querySelector('.form-status');
  const MAX = 5 * 1024 * 1024;
  form.elements.dob.max = new Date().toISOString().split('T')[0];

  form.querySelectorAll('.upload input[type=file]').forEach((input) => {
    input.addEventListener('change', () => {
      const label = input.closest('.upload').querySelector('.file-name');
      const file = input.files[0];
      if (file && file.size > MAX) { input.value = ''; label.textContent = 'File too large (max 5 MB)'; return; }
      label.textContent = file ? file.name : 'No file chosen';
    });
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!form.checkValidity()) { form.reportValidity(); return; }
    const btn = form.querySelector('button[type=submit]');
    btn.disabled = true;
    status.className = 'form-status';
    status.textContent = 'Submitting…';
    try {
      const res = await fetch('includes/admissions-handler.php', { method: 'POST', body: new FormData(form) });
      const data = await res.json();
      if (data.success) {
        form.hidden = true;
        const box = document.getElementById('confirmation');
        box.hidden = false;
        box.focus();
        box.scrollIntoView({ behavior: 'smooth' });
      } else {
        status.textContent = data.message || 'Something went wrong. Please try again.';
        status.classList.add('error');
        btn.disabled = false;
      }
    } catch (err) {
      status.textContent = 'Could not submit. Please try again later.';
      status.classList.add('error');
      btn.disabled = false;
    }
  });
});
