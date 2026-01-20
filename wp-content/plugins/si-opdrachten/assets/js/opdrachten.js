(function () {
  const form = document.querySelector('.si-opd-filter');
  if (!form) return;

  const search = form.querySelector('#si_search');
  const cat = form.querySelector('#si_categorie');
  const type = form.querySelector('#si_type');

  // Submit on select change
  [cat, type].forEach(el => {
    if (!el) return;
    el.addEventListener('change', () => form.submit());
  });

  // Debounced submit on typing
  if (search) {
    let t;
    search.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(() => form.submit(), 450);
    });
  }
})();
