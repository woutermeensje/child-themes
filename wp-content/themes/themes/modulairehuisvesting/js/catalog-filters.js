(function () {
  function getCatalogUrl() {
    return new URL(window.location.href);
  }

  function getSelectedTermsForTaxonomy(taxonomy) {
    var params = new URLSearchParams(window.location.search);
    var raw = params.get('mh_filter_' + taxonomy);
    if (!raw) return new Set();

    return new Set(
      raw
        .split(',')
        .map(function (value) { return value.trim(); })
        .filter(Boolean)
    );
  }

  function navigateWithFilters(activeSets) {
    var url = getCatalogUrl();

    Object.keys(activeSets).forEach(function (taxonomy) {
      var key = 'mh_filter_' + taxonomy;
      var selected = Array.from(activeSets[taxonomy] || []);

      if (selected.length) {
        url.searchParams.set(key, selected.join(','));
      } else {
        url.searchParams.delete(key);
      }
    });

    url.searchParams.delete('paged');
    url.searchParams.delete('product-page');
    url.searchParams.delete('orderby');

    window.location.href = url.toString();
  }

  function closeAllSelects(except) {
    document.querySelectorAll('.mh-select.is-open').forEach(function (el) {
      if (except && el === except) return;
      el.classList.remove('is-open');
      var button = el.querySelector('.mh-select-btn');
      if (button) button.setAttribute('aria-expanded', 'false');
    });
  }

  function initMultiSelect(root, activeSets) {
    var taxonomy = root.getAttribute('data-taxonomy');
    if (!taxonomy) return;

    var selectedSet = activeSets[taxonomy] || new Set();
    var button = root.querySelector('.mh-select-btn');
    var label = root.querySelector('.mh-select-label');
    var optionsWrap = root.querySelector('.mh-select-options');
    var searchInput = root.querySelector('.mh-option-search');
    var tagsWrap = root.querySelector('.mh-selected-tags');
    var options = Array.from(root.querySelectorAll('.mh-select-option'));
    var chipMap = {};

    function updateLabel() {
      if (!selectedSet.size) {
        label.textContent = button.dataset.placeholder || label.textContent;
        label.classList.add('is-placeholder');
        return;
      }

      label.textContent = selectedSet.size === 1 ? '1 geselecteerd' : selectedSet.size + ' geselecteerd';
      label.classList.remove('is-placeholder');
    }

    function removeChip(slug) {
      if (chipMap[slug]) {
        chipMap[slug].remove();
        delete chipMap[slug];
      }
    }

    function syncOptionState(option, selected) {
      option.classList.toggle('is-selected', selected);
      option.setAttribute('aria-selected', selected ? 'true' : 'false');
    }

    function addChip(slug, text) {
      if (chipMap[slug]) return;

      var chip = document.createElement('span');
      chip.className = 'mh-tag-chip';

      var chipLabel = document.createElement('span');
      chipLabel.textContent = text;

      var chipRemove = document.createElement('button');
      chipRemove.type = 'button';
      chipRemove.className = 'mh-tag-chip-remove';
      chipRemove.setAttribute('aria-label', 'Verwijder ' + text);
      chipRemove.innerHTML = '&times;';
      chipRemove.addEventListener('click', function (event) {
        event.stopPropagation();
        selectedSet.delete(slug);
        removeChip(slug);
        options.forEach(function (option) {
          if (option.getAttribute('data-slug') === slug) {
            syncOptionState(option, false);
          }
        });
        updateLabel();
        navigateWithFilters(activeSets);
      });

      chip.appendChild(chipLabel);
      chip.appendChild(chipRemove);
      tagsWrap.appendChild(chip);
      chipMap[slug] = chip;
    }

    function renderInitialState() {
      options.forEach(function (option) {
        var slug = option.getAttribute('data-slug') || '';
        var text = option.getAttribute('data-label') || slug;
        var selected = selectedSet.has(slug);
        syncOptionState(option, selected);
        if (selected) addChip(slug, text);
      });
      updateLabel();
    }

    button.dataset.placeholder = label.textContent;
    button.addEventListener('click', function (event) {
      event.stopPropagation();
      var willOpen = !root.classList.contains('is-open');
      closeAllSelects(root);
      root.classList.toggle('is-open', willOpen);
      button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      if (willOpen && searchInput) {
        searchInput.focus();
      }
    });

    options.forEach(function (option) {
      option.addEventListener('click', function (event) {
        event.stopPropagation();
        var slug = option.getAttribute('data-slug') || '';
        var text = option.getAttribute('data-label') || slug;
        var selected = selectedSet.has(slug);

        if (selected) {
          selectedSet.delete(slug);
          removeChip(slug);
        } else {
          selectedSet.add(slug);
          addChip(slug, text);
        }

        syncOptionState(option, !selected);
        updateLabel();
        navigateWithFilters(activeSets);
      });
    });

    if (searchInput) {
      searchInput.addEventListener('input', function () {
        var query = searchInput.value.trim().toLowerCase();
        options.forEach(function (option) {
          var text = (option.getAttribute('data-label') || '').toLowerCase();
          var slug = (option.getAttribute('data-slug') || '').toLowerCase();
          var match = !query || text.indexOf(query) !== -1 || slug.indexOf(query) !== -1;
          option.style.display = match ? '' : 'none';
        });
      });
    }

    renderInitialState();
  }

  document.addEventListener('click', function () {
    closeAllSelects();
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closeAllSelects();
    }
  });

  document.addEventListener('DOMContentLoaded', function () {
    var selects = Array.from(document.querySelectorAll('.mh-select[data-taxonomy]'));
    if (!selects.length) return;

    var activeSets = {};
    selects.forEach(function (select) {
      var taxonomy = select.getAttribute('data-taxonomy');
      if (!taxonomy) return;
      activeSets[taxonomy] = getSelectedTermsForTaxonomy(taxonomy);
    });

    selects.forEach(function (select) {
      initMultiSelect(select, activeSets);
    });

    var resetButton = document.querySelector('.mh-catalog-reset');
    if (resetButton) {
      resetButton.addEventListener('click', function () {
        navigateWithFilters({});
      });
    }
  });
})();
