document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-record-search]').forEach((section) => {
    const input = section.querySelector('[data-record-search-input]');
    const table = section.querySelector('[data-record-search-table]');
    const results = section.querySelector('[data-record-search-results]');
    const count = section.querySelector('[data-record-search-count]');
    const emptyState = section.querySelector('[data-record-search-empty]');
    if (!(input instanceof HTMLInputElement) || !table || !results || !count || !emptyState) return;

    const rows = Array.from(table.querySelectorAll('tbody tr'));
    const label = section.getAttribute('data-search-label') || 'records';

    input.addEventListener('input', () => {
      const query = input.value.trim().toLocaleLowerCase();
      let visibleRows = 0;

      rows.forEach((row) => {
        const matches = row.textContent.toLocaleLowerCase().includes(query);
        row.hidden = !matches;
        if (matches) visibleRows += 1;
      });

      const noun = query ? label : (visibleRows === 1 ? label.replace(/s$/, '') : label);
      count.textContent = query
        ? `${visibleRows} of ${rows.length} ${label}`
        : `${visibleRows} ${noun}`;
      results.hidden = visibleRows === 0;
      emptyState.hidden = visibleRows !== 0;
    });
  });
});
