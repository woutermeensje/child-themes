(function () {
  function slugToLabel(slug) {
    return slug
      .replace(/-/g, " ")
      .replace(/\b\w/g, function (m) {
        return m.toUpperCase();
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

    var categorySet = new Set();

    items.forEach(function (item) {
      var classes = Array.from(item.classList);
      classes.forEach(function (className) {
        if (className.indexOf('product_cat-') === 0) {
          categorySet.add(className.replace('product_cat-', ''));
        }
      });
    });

    var categoryWrap = document.createElement('div');
    categoryWrap.className = 'pms-catalog-block';

    var categoryTitle = document.createElement('h3');
    categoryTitle.textContent = 'Categorie';
    categoryWrap.appendChild(categoryTitle);

    var categoryList = document.createElement('div');
    categoryList.className = 'pms-catalog-options';

    var selectedCategories = new Set();

    Array.from(categorySet)
      .sort()
      .forEach(function (slug) {
        var label = document.createElement('label');
        label.className = 'pms-catalog-option';

        var checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.value = slug;

        checkbox.addEventListener('change', function () {
          if (checkbox.checked) selectedCategories.add(slug);
          else selectedCategories.delete(slug);
          applyFilters();
        });

        var text = document.createElement('span');
        text.textContent = slugToLabel(slug);

        label.appendChild(checkbox);
        label.appendChild(text);
        categoryList.appendChild(label);
      });

    categoryWrap.appendChild(categoryList);

    var metaWrap = document.createElement('div');
    metaWrap.className = 'pms-catalog-meta';
    var countText = document.createElement('p');
    countText.className = 'pms-catalog-count';
    metaWrap.appendChild(countText);

    sidebarInner.appendChild(searchWrap);
    if (categorySet.size) sidebarInner.appendChild(categoryWrap);
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

    function categoriesOf(item) {
      return Array.from(item.classList)
        .filter(function (c) {
          return c.indexOf('product_cat-') === 0;
        })
        .map(function (c) {
          return c.replace('product_cat-', '');
        });
    }

    function applyFilters() {
      var query = searchInput.value.trim().toLowerCase();
      var visible = 0;

      items.forEach(function (item) {
        var titleMatch = !query || titleOf(item).indexOf(query) !== -1;
        var cats = categoriesOf(item);
        var categoryMatch = selectedCategories.size === 0 || cats.some(function (c) {
          return selectedCategories.has(c);
        });

        var show = titleMatch && categoryMatch;
        item.style.display = show ? '' : 'none';
        if (show) visible++;
      });

      countText.textContent = visible + ' van ' + items.length + ' producten';
    }

    searchInput.addEventListener('input', applyFilters);
    applyFilters();
  }

  document.addEventListener('DOMContentLoaded', function () {
    var list = document.querySelector('.woocommerce ul.products, .wp-block-woocommerce-product-collection ul.products');
    if (list) initCatalogLayout(list);
  });
})();
