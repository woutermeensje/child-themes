(function () {

  // Bekende WooCommerce klassen die geen taxonomie zijn
  var IGNORE_PREFIXES = [
    'product', 'type-', 'status-', 'post-', 'hentry', 'has-', 'is-',
    'instock', 'outofstock', 'onbackorder', 'downloadable', 'virtual',
    'taxable', 'shipping-taxable', 'purchasable', 'product-type-',
    'first', 'last', 'sale', 'featured', 'wc-', 'entry-', 'woosb-',
  ];

  // Taxonomie prefix → leesbaar label
  var TAXONOMY_LABELS = {
    'product_cat':  'Categorie',
    'product_tag':  'Tag',
    'product_brand': 'Merk',
  };

  function slugToLabel(slug) {
    return slug
      .replace(/-/g, ' ')
      .replace(/_/g, ' ')
      .replace(/\b\w/g, function (m) { return m.toUpperCase(); });
  }

  // Haal de taxonomie prefix en de term slug op uit een CSS klasse.
  // Voorbeeld: 'product_cat-stoelen' → { tax: 'product_cat', term: 'stoelen' }
  //            'pa_kleur-rood'       → { tax: 'pa_kleur',    term: 'rood' }
  function parseClass(cls) {
    // Bekende prefixen
    var known = ['product_cat-', 'product_tag-', 'product_brand-'];
    for (var k = 0; k < known.length; k++) {
      if (cls.indexOf(known[k]) === 0) {
        return { tax: known[k].slice(0, -1), term: cls.slice(known[k].length) };
      }
    }
    // pa_* attributen: eerste streepje scheidt attribuutnaam van termwaarde
    if (cls.indexOf('pa_') === 0) {
      var dash = cls.indexOf('-');
      if (dash > 0) {
        return { tax: cls.slice(0, dash), term: cls.slice(dash + 1) };
      }
    }
    return null;
  }

  // Bouw een Map: taxSlug → Set van term slugs
  function detectTaxonomies(items) {
    var map = new Map();
    items.forEach(function (item) {
      item.classList.forEach(function (cls) {
        var parsed = parseClass(cls);
        if (!parsed) return;
        if (!map.has(parsed.tax)) map.set(parsed.tax, new Set());
        map.get(parsed.tax).add(parsed.term);
      });
    });
    return map;
  }

  function closeAllSelects(except) {
    document.querySelectorAll('.pms-select.is-open').forEach(function (el) {
      if (except && el === except) return;
      el.classList.remove('is-open');
      var btn = el.querySelector('.pms-select-btn');
      if (btn) btn.setAttribute('aria-expanded', 'false');
    });
  }

  // Bouw één multiselect-blok met zoekbalk
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

    // Knop
    var button = document.createElement('button');
    button.type = 'button';
    button.className = 'pms-select-btn';
    button.setAttribute('aria-haspopup', 'listbox');
    button.setAttribute('aria-expanded', 'false');

    var label = document.createElement('span');
    label.className = 'pms-select-label';

    // Chevron SVG icon
    var chevron = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    chevron.setAttribute('class', 'pms-select-chevron');
    chevron.setAttribute('viewBox', '0 0 24 24');
    chevron.setAttribute('fill', 'none');
    chevron.setAttribute('stroke', 'currentColor');
    chevron.setAttribute('stroke-width', '2');
    chevron.setAttribute('stroke-linecap', 'round');
    chevron.setAttribute('stroke-linejoin', 'round');
    chevron.setAttribute('aria-hidden', 'true');
    var chevronPath = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
    chevronPath.setAttribute('points', '6 9 12 15 18 9');
    chevron.appendChild(chevronPath);

    button.appendChild(label);
    button.appendChild(chevron);

    // Dropdown
    var options = document.createElement('div');
    options.className = 'pms-select-options';
    options.setAttribute('role', 'listbox');
    options.setAttribute('aria-multiselectable', 'true');

    // Zoekbalk bovenaan de dropdown
    var searchWrap = document.createElement('div');
    searchWrap.className = 'pms-option-search-wrap';

    var searchInput = document.createElement('input');
    searchInput.type = 'search';
    searchInput.className = 'pms-option-search';
    searchInput.placeholder = 'Zoek ' + config.title.toLowerCase() + '...';
    searchInput.addEventListener('input', function () {
      var q = searchInput.value.trim().toLowerCase();
      optionItems.forEach(function (pair) {
        var match = !q || pair.slug.indexOf(q) !== -1 || pair.text.toLowerCase().indexOf(q) !== -1;
        pair.el.style.display = match ? '' : 'none';
      });
    });
    searchWrap.appendChild(searchInput);
    options.appendChild(searchWrap);

    // Tags rij onder de dropdown
    var tagsWrap = document.createElement('div');
    tagsWrap.className = 'pms-selected-tags';

    var chipMap = {};

    function addChip(slug, labelText) {
      if (chipMap[slug]) return;
      var chip = document.createElement('span');
      chip.className = 'pms-tag-chip';

      var chipLabel = document.createElement('span');
      chipLabel.textContent = labelText;

      var chipRemove = document.createElement('button');
      chipRemove.type = 'button';
      chipRemove.className = 'pms-tag-chip-remove';
      chipRemove.setAttribute('aria-label', 'Verwijder ' + labelText);
      chipRemove.innerHTML = '&times;';
      chipRemove.addEventListener('click', function (e) {
        e.stopPropagation();
        deselect(slug);
      });

      chip.appendChild(chipLabel);
      chip.appendChild(chipRemove);
      tagsWrap.appendChild(chip);
      chipMap[slug] = chip;
    }

    function removeChip(slug) {
      if (chipMap[slug]) {
        chipMap[slug].remove();
        delete chipMap[slug];
      }
    }

    function deselect(slug) {
      config.selectedSet.delete(slug);
      removeChip(slug);
      // sync optionEl state
      optionItems.forEach(function (p) {
        if (p.slug === slug) {
          p.el.classList.remove('is-selected');
          p.el.setAttribute('aria-selected', 'false');
        }
      });
      updateLabel();
      config.onChange();
    }

    function updateLabel() {
      var sel = Array.from(config.selectedSet);
      if (!sel.length) {
        label.textContent = config.placeholder;
        label.classList.add('is-placeholder');
        return;
      }
      label.classList.remove('is-placeholder');
      label.textContent = config.placeholder;
    }

    var optionItems = [];
    var sortedTerms = Array.from(config.terms).sort();

    sortedTerms.forEach(function (slug) {
      var optionEl = document.createElement('div');
      optionEl.className = 'pms-select-option';
      optionEl.setAttribute('role', 'option');
      optionEl.setAttribute('aria-selected', 'false');
      if (config.selectedSet.has(slug)) optionEl.classList.add('is-selected');

      var text = document.createElement('span');
      text.className = 'pms-select-option-text';
      text.textContent = slugToLabel(slug);

      optionEl.appendChild(text);
      options.appendChild(optionEl);

      optionEl.addEventListener('click', function (e) {
        e.stopPropagation();
        if (config.selectedSet.has(slug)) {
          config.selectedSet.delete(slug);
          optionEl.classList.remove('is-selected');
          optionEl.setAttribute('aria-selected', 'false');
          removeChip(slug);
        } else {
          config.selectedSet.add(slug);
          optionEl.classList.add('is-selected');
          optionEl.setAttribute('aria-selected', 'true');
          addChip(slug, text.textContent);
        }
        updateLabel();
        config.onChange();
      });

      optionItems.push({ slug: slug, text: text.textContent, el: optionEl });
    });

    button.addEventListener('click', function (e) {
      e.stopPropagation();
      var open = selectRoot.classList.contains('is-open');
      closeAllSelects(selectRoot);
      if (open) {
        selectRoot.classList.remove('is-open');
        button.setAttribute('aria-expanded', 'false');
      } else {
        selectRoot.classList.add('is-open');
        button.setAttribute('aria-expanded', 'true');
        searchInput.value = '';
        optionItems.forEach(function (p) { p.el.style.display = ''; });
        setTimeout(function () { searchInput.focus(); }, 50);
      }
    });

    selectRoot.appendChild(button);
    selectRoot.appendChild(options);
    selectWrap.appendChild(selectRoot);
    block.appendChild(selectWrap);
    block.appendChild(tagsWrap);

    updateLabel();
    return block;
  }

  function initCatalogLayout(productsList) {
    if (!productsList || productsList.closest('.pms-catalog-layout')) return;

    var items = Array.from(productsList.querySelectorAll('li.product'));
    if (!items.length) return;

    // Wrapper aanmaken
    var wrapper = document.createElement('section');
    wrapper.className = 'pms-catalog-layout';

    var sidebar = document.createElement('aside');
    sidebar.className = 'pms-catalog-sidebar';

    var sidebarInner = document.createElement('div');
    sidebarInner.className = 'pms-catalog-sidebar-inner';

    // Zoekbalk bovenaan de sidebar
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
    sidebarInner.appendChild(searchWrap);

    // Verplaats "Sorteren op" naar de filterkolom
    var orderingForm = null;
    var siblingForOrdering = productsList.previousElementSibling;
    while (siblingForOrdering) {
      var previousOrderingSibling = siblingForOrdering.previousElementSibling;
      if (siblingForOrdering.tagName === 'FORM' && siblingForOrdering.classList.contains('pms-tax-toolbar__ordering')) {
        orderingForm = siblingForOrdering;
        break;
      }
      if (!orderingForm && siblingForOrdering.classList && siblingForOrdering.classList.contains('pms-tax-toolbar')) {
        orderingForm = siblingForOrdering.querySelector('form.pms-tax-toolbar__ordering');
        if (orderingForm) break;
      }
      siblingForOrdering = previousOrderingSibling;
    }

    if (orderingForm) {
      var orderingWrap = document.createElement('div');
      orderingWrap.className = 'pms-catalog-block pms-catalog-block--ordering';
      orderingWrap.appendChild(orderingForm);
      sidebarInner.appendChild(orderingWrap);
    }

    // Detecteer alle taxonomieën
    var taxonomyMap = detectTaxonomies(items);
    var activeSets = {};

    taxonomyMap.forEach(function (terms, taxSlug) {
      if (terms.size === 0) return;
      activeSets[taxSlug] = new Set();

      var label = TAXONOMY_LABELS[taxSlug] || slugToLabel(taxSlug.replace(/^pa_/, ''));

      sidebarInner.appendChild(createMultiSelectBlock({
        title:       label,
        placeholder: 'Alle ' + label.toLowerCase() + 'en',
        terms:       terms,
        selectedSet: activeSets[taxSlug],
        onChange:    applyFilters,
      }));
    });

    // Teller
    var metaWrap = document.createElement('div');
    metaWrap.className = 'pms-catalog-meta';
    var countText = document.createElement('p');
    countText.className = 'pms-catalog-count';
    metaWrap.appendChild(countText);
    sidebarInner.appendChild(metaWrap);

    sidebar.appendChild(sidebarInner);

    // Grid wrap
    var gridWrap = document.createElement('div');
    gridWrap.className = 'pms-catalog-grid-wrap';

    // Op archief/categorie pagina's: schuif de header, count en ordering
    // ook de gridWrap in zodat ze naast de sidebar staan (niet erboven).
    var parent = productsList.parentNode;
    var prependEls = [];

    // Zoek elementen die vóór ul.products staan en erbij horen
    var sibling = productsList.previousElementSibling;
    while (sibling) {
      var prev = sibling.previousElementSibling;
      if (
        sibling.classList.contains('pms-tax-toolbar') ||
        sibling.classList.contains('woocommerce-products-header') ||
        sibling.classList.contains('woocommerce-result-count')
      ) {
        prependEls.unshift(sibling);
      }
      sibling = prev;
    }

    parent.insertBefore(wrapper, productsList);

    prependEls.forEach(function (el) {
      gridWrap.appendChild(el);
    });

    gridWrap.appendChild(productsList);
    wrapper.appendChild(sidebar);
    wrapper.appendChild(gridWrap);

    function titleOf(item) {
      var t = item.querySelector('.pms-product-card__title, .woocommerce-loop-product__title');
      return (t ? t.textContent : '').toLowerCase();
    }

    function applyFilters() {
      var query = searchInput.value.trim().toLowerCase();
      var visible = 0;

      items.forEach(function (item) {
        var show = true;

        // Tekstzoekopdracht
        if (query && titleOf(item).indexOf(query) === -1) show = false;

        // Taxonomie filters: elk actief filter moet matchen (AND)
        if (show) {
          taxonomyMap.forEach(function (terms, taxSlug) {
            var selected = activeSets[taxSlug];
            if (!selected || selected.size === 0) return;

            // Welke termen heeft dit product voor deze taxonomie?
            var prefix = taxSlug + '-';
            var productTerms = Array.from(item.classList)
              .filter(function (c) { return c.indexOf(prefix) === 0; })
              .map(function (c) { return c.slice(prefix.length); });

            var match = productTerms.some(function (t) { return selected.has(t); });
            if (!match) show = false;
          });
        }

        item.style.display = show ? '' : 'none';
        if (show) visible++;
      });

      countText.textContent = visible + ' van ' + items.length + ' producten';
    }

    searchInput.addEventListener('input', applyFilters);

    document.addEventListener('click', function (e) {
      if (!e.target.closest('.pms-select')) closeAllSelects();
    });

    applyFilters();
  }

  document.addEventListener('DOMContentLoaded', function () {
    if (document.body.classList.contains('single-product')) return;
    var list = document.querySelector('.woocommerce ul.products, .wp-block-woocommerce-product-collection ul.products');
    if (list) initCatalogLayout(list);
  });

})();
