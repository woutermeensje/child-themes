(function () {
  function slugToLabel(slug) {
    return slug
      .replace(/-/g, " ")
      .replace(/\b\w/g, function (m) {
        return m.toUpperCase();
      });
  }

  function termSlugsForItem(item, prefixes) {
    return Array.from(item.classList)
      .filter(function (className) {
        return prefixes.some(function (prefix) {
          return className.indexOf(prefix) === 0;
        });
      })
      .map(function (className) {
        var matchedPrefix = prefixes.find(function (prefix) {
          return className.indexOf(prefix) === 0;
        });

        return matchedPrefix ? className.replace(matchedPrefix, "") : "";
      })
      .filter(Boolean);
  }

  function collectTermSlugs(items, prefixes) {
    var termSet = new Set();

    items.forEach(function (item) {
      termSlugsForItem(item, prefixes).forEach(function (slug) {
        termSet.add(slug);
      });
    });

    return Array.from(termSet).sort();
  }

  function closeAllSelects(except) {
    document.querySelectorAll('.pms-select.is-open').forEach(function (selectRoot) {
      if (except && selectRoot === except) {
        return;
      }

      selectRoot.classList.remove('is-open');
      var button = selectRoot.querySelector('.pms-select-btn');
      if (button) {
        button.setAttribute('aria-expanded', 'false');
      }
    });
  }

  function initCatalogLayout(productsList) {
    if (!productsList || productsList.closest('.pms-catalog-layout')) return;

    var items = Array.from(productsList.querySelectorAll('li.product'));
    if (!items.length) return;

    var wrapper = document.createElement('section');
    wrapper.className = 'pms-catalog-layout';

    var sidebar = document.createElement('aside');
    sidebar.className = 'pms-catalog-sidebar';

    var sidebarInner = document.createElement('div');
    sidebarInner.className = 'pms-catalog-sidebar-inner';

    var searchWrap = document.createElement('div');
    searchWrap.className = 'pms-catalog-block';

    var searchTitle = document.createElement('h3');
    searchTitle.textContent = 'Zoek product';

    var searchInput = document.createElement('input');
    searchInput.type = 'search';
    searchInput.className = 'pms-catalog-search';
    searchInput.placeholder = 'Typ een productnaam';

    searchWrap.appendChild(searchTitle);
    searchWrap.appendChild(searchInput);

    var categoryPrefixes = ['product_cat-'];
    var brandPrefixes = ['product_brand-', 'pa_brand-', 'pa_merk-'];

    var categoryTerms = collectTermSlugs(items, categoryPrefixes);
    var brandTerms = collectTermSlugs(items, brandPrefixes);

    var selectedCategories = new Set();
    var selectedBrands = new Set();

    function createMultiSelectBlock(config) {
      var block = document.createElement('div');
      block.className = 'pms-catalog-block';

      var title = document.createElement('h3');
      title.textContent = config.title;
      block.appendChild(title);

      var selectWrap = document.createElement('div');
      selectWrap.className = 'pms-select-wrap';

      var selectRoot = document.createElement('div');
      selectRoot.className = 'pms-select';

      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'pms-select-btn';
      button.setAttribute('aria-haspopup', 'listbox');
      button.setAttribute('aria-expanded', 'false');

      var label = document.createElement('span');
      label.className = 'pms-select-label';

      var arrow = document.createElement('span');
      arrow.className = 'pms-select-arrow';
      arrow.setAttribute('aria-hidden', 'true');

      button.appendChild(label);
      button.appendChild(arrow);

      var options = document.createElement('div');
      options.className = 'pms-select-options';
      options.setAttribute('role', 'listbox');
      options.setAttribute('aria-multiselectable', 'true');

      function updateLabel() {
        var selectedSlugs = Array.from(config.selectedSet);

        if (!selectedSlugs.length) {
          label.textContent = config.placeholder;
          label.classList.add('is-placeholder');
          return;
        }

        label.classList.remove('is-placeholder');

        if (selectedSlugs.length === 1) {
          label.textContent = slugToLabel(selectedSlugs[0]);
          return;
        }

        label.textContent = selectedSlugs.length + ' geselecteerd';
      }

      config.terms.forEach(function (slug) {
        var option = document.createElement('label');
        option.className = 'pms-select-option';

        var checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.value = slug;
        checkbox.checked = config.selectedSet.has(slug);

        checkbox.addEventListener('change', function () {
          if (checkbox.checked) {
            config.selectedSet.add(slug);
          } else {
            config.selectedSet.delete(slug);
          }

          updateLabel();
          config.onChange();
        });

        var text = document.createElement('span');
        text.className = 'pms-select-option-text';
        text.textContent = slugToLabel(slug);

        option.appendChild(checkbox);
        option.appendChild(text);
        options.appendChild(option);
      });

      button.addEventListener('click', function (event) {
        event.stopPropagation();
        var open = selectRoot.classList.contains('is-open');

        closeAllSelects(selectRoot);

        if (open) {
          selectRoot.classList.remove('is-open');
          button.setAttribute('aria-expanded', 'false');
        } else {
          selectRoot.classList.add('is-open');
          button.setAttribute('aria-expanded', 'true');
        }
      });

      selectRoot.appendChild(button);
      selectRoot.appendChild(options);
      selectWrap.appendChild(selectRoot);
      block.appendChild(selectWrap);

      updateLabel();
      return block;
    }

    var metaWrap = document.createElement('div');
    metaWrap.className = 'pms-catalog-meta';
    var countText = document.createElement('p');
    countText.className = 'pms-catalog-count';
    metaWrap.appendChild(countText);

    sidebarInner.appendChild(searchWrap);

    if (categoryTerms.length) {
      sidebarInner.appendChild(
        createMultiSelectBlock({
          title: 'Categorie',
          placeholder: 'Kies categorieen',
          terms: categoryTerms,
          selectedSet: selectedCategories,
          onChange: applyFilters,
        })
      );
    }

    if (brandTerms.length) {
      sidebarInner.appendChild(
        createMultiSelectBlock({
          title: 'Merk',
          placeholder: 'Kies merken',
          terms: brandTerms,
          selectedSet: selectedBrands,
          onChange: applyFilters,
        })
      );
    }

    sidebarInner.appendChild(metaWrap);
    sidebar.appendChild(sidebarInner);

    var gridWrap = document.createElement('div');
    gridWrap.className = 'pms-catalog-grid-wrap';
    productsList.parentNode.insertBefore(wrapper, productsList);
    gridWrap.appendChild(productsList);
    wrapper.appendChild(sidebar);
    wrapper.appendChild(gridWrap);

    function titleOf(item) {
      var title = item.querySelector('.woocommerce-loop-product__title');
      return (title ? title.textContent : '').toLowerCase();
    }

    function applyFilters() {
      var query = searchInput.value.trim().toLowerCase();
      var visible = 0;

      items.forEach(function (item) {
        var titleMatch = !query || titleOf(item).indexOf(query) !== -1;
        var categories = termSlugsForItem(item, categoryPrefixes);
        var brands = termSlugsForItem(item, brandPrefixes);

        var categoryMatch =
          selectedCategories.size === 0 ||
          categories.some(function (slug) {
            return selectedCategories.has(slug);
          });

        var brandMatch =
          selectedBrands.size === 0 ||
          brands.some(function (slug) {
            return selectedBrands.has(slug);
          });

        var show = titleMatch && categoryMatch && brandMatch;
        item.style.display = show ? '' : 'none';

        if (show) {
          visible++;
        }
      });

      countText.textContent = visible + ' van ' + items.length + ' producten';
    }

    searchInput.addEventListener('input', applyFilters);

    document.addEventListener('click', function (event) {
      if (!event.target.closest('.pms-select')) {
        closeAllSelects();
        document.querySelectorAll('.pms-select-btn[aria-expanded="true"]').forEach(function (button) {
          button.setAttribute('aria-expanded', 'false');
        });
      }
    });

    applyFilters();
  }

  document.addEventListener('DOMContentLoaded', function () {
    var body = document.body;
    if (!body) return;

    if (body.classList.contains('single-product')) return;

    var list = document.querySelector('.woocommerce ul.products, .wp-block-woocommerce-product-collection ul.products');
    if (list) initCatalogLayout(list);
  });
})();
