// ==========================================================================
// Imarika School — language switcher
// Requires translations.js to be loaded first (defines the `translations` object).
// Add data-i18n="keyName" to any element whose text should be translated.
// ==========================================================================

(function () {
  const STORAGE_KEY = 'imarika_lang';

  function getSavedLanguage() {
    return localStorage.getItem(STORAGE_KEY) || 'en';
  }

  function applyLanguage(lang) {
    const dict = translations[lang] || translations.en;

    document.querySelectorAll('[data-i18n]').forEach((el) => {
      const key = el.getAttribute('data-i18n');
      if (dict[key]) {
        el.textContent = dict[key];
      }
    });

    document.documentElement.lang = lang;
    localStorage.setItem(STORAGE_KEY, lang);

    document.querySelectorAll('.lang-btn').forEach((btn) => {
      btn.setAttribute('aria-pressed', btn.dataset.lang === lang ? 'true' : 'false');
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    applyLanguage(getSavedLanguage());

    document.querySelectorAll('.lang-btn').forEach((btn) => {
      btn.addEventListener('click', () => applyLanguage(btn.dataset.lang));
    });
  });
})();
