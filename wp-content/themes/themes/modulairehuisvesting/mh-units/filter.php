<?php if (!defined('ABSPATH')) exit; ?>

<?php
$search         = isset($search) ? (string) $search : '';
$types_selected = isset($types_selected) && is_array($types_selected) ? $types_selected : [];

$types = get_terms([
    'taxonomy'   => 'mh_unit_type',
    'hide_empty' => false,
]);
?>

<aside class="mh-catalog-sidebar mh-units-catalog__sidebar" aria-label="Unit filters">
    <div class="mh-catalog-sidebar-inner mh-units-catalog__sidebar-inner">
        <form class="mh-units-filter-form" method="get">
            <input type="hidden" name="mh_units_filter" value="1">

            <div class="mh-catalog-block mh-units-catalog__filter-block">
                <label class="mh-units-filter-label screen-reader-text" for="mh_search">Zoek</label>
                <input
                    id="mh_search"
                    name="mh_search"
                    type="text"
                    class="mh-units-catalog__search"
                    aria-label="Zoek units"
                    placeholder="Zoek op naam, trefwoord..."
                    value="<?php echo esc_attr($search); ?>"
                >
            </div>

            <fieldset class="mh-catalog-block mh-units-catalog__filter-block mh-units-filter-field--types">
                <legend class="mh-units-filter-legend screen-reader-text">Type unit</legend>

                <div class="mh-units-catalog__select" data-name="mh_type[]">
                    <button type="button" class="mh-units-catalog__select-btn" aria-haspopup="listbox" aria-expanded="false">
                        <span class="mh-units-catalog__select-label is-placeholder">Selecteer type unit</span>
                        <svg class="mh-units-catalog__select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>

                    <div class="mh-units-catalog__select-panel" role="listbox" aria-multiselectable="true">
                        <div class="mh-units-catalog__select-search-wrap">
                            <input type="search" class="mh-units-catalog__select-search" placeholder="Zoek type unit...">
                        </div>

                        <div class="mh-units-catalog__select-options">
                            <?php if (!is_wp_error($types) && !empty($types)) : ?>
                                <?php foreach ($types as $type): ?>
                                    <div
                                        class="mh-units-catalog__select-option<?php echo in_array($type->slug, $types_selected, true) ? ' is-selected' : ''; ?>"
                                        role="option"
                                        aria-selected="<?php echo in_array($type->slug, $types_selected, true) ? 'true' : 'false'; ?>"
                                        data-value="<?php echo esc_attr($type->slug); ?>"
                                        data-label="<?php echo esc_attr($type->name); ?>"
                                    >
                                        <span><?php echo esc_html($type->name); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="mh-units-catalog__select-empty">
                            Geen types gevonden.
                        </div>
                    </div>

                    <div class="mh-units-catalog__select-tags">
                        <?php if (!is_wp_error($types) && !empty($types)) : ?>
                            <?php foreach ($types as $type): ?>
                                <?php if (!in_array($type->slug, $types_selected, true)) continue; ?>
                                <span class="mh-units-catalog__select-tag" data-value="<?php echo esc_attr($type->slug); ?>">
                                    <span><?php echo esc_html($type->name); ?></span>
                                    <button type="button" class="mh-units-catalog__select-tag-remove" data-value="<?php echo esc_attr($type->slug); ?>" aria-label="Verwijder <?php echo esc_attr($type->name); ?>">&times;</button>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="mh-units-catalog__select-hidden">
                        <?php foreach ($types_selected as $selected_type): ?>
                            <input type="hidden" name="mh_type[]" value="<?php echo esc_attr($selected_type); ?>">
                        <?php endforeach; ?>
                    </div>
                </div>
            </fieldset>

            <div class="mh-catalog-block mh-catalog-block--actions mh-units-catalog__actions">
                <button type="submit" class="mh-units-catalog__submit">Zoeken</button>
            </div>
        </form>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var filterForm = document.querySelector('.mh-units-filter-form');
  if (!filterForm) return;

  function closeAllSelects(except) {
    document.querySelectorAll('.mh-units-catalog__select.is-open').forEach(function (select) {
      if (except && select === except) return;
      select.classList.remove('is-open');
      var button = select.querySelector('.mh-units-catalog__select-btn');
      var panel = select.querySelector('.mh-units-catalog__select-panel');
      var chevron = select.querySelector('.mh-units-catalog__select-chevron');
      if (button) button.setAttribute('aria-expanded', 'false');
      if (panel) panel.style.display = 'none';
      if (chevron) chevron.style.transform = '';
    });
  }

  document.querySelectorAll('.mh-units-catalog__select').forEach(function (select) {
    var button = select.querySelector('.mh-units-catalog__select-btn');
    var label = select.querySelector('.mh-units-catalog__select-label');
    var options = Array.from(select.querySelectorAll('.mh-units-catalog__select-option'));
    var tagsWrap = select.querySelector('.mh-units-catalog__select-tags');
    var hiddenWrap = select.querySelector('.mh-units-catalog__select-hidden');
    var searchInput = select.querySelector('.mh-units-catalog__select-search');
    var emptyState = select.querySelector('.mh-units-catalog__select-empty');
    var inputName = select.getAttribute('data-name') || 'mh_type[]';
    var selected = new Set();

    function syncHiddenInputs() {
      hiddenWrap.innerHTML = '';
      Array.from(selected).forEach(function (value) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = inputName;
        input.value = value;
        hiddenWrap.appendChild(input);
      });
    }

    function syncLabel() {
      if (!selected.size) {
        label.textContent = 'Selecteer type unit';
        label.classList.add('is-placeholder');
        return;
      }

      label.textContent = selected.size === 1 ? '1 geselecteerd' : selected.size + ' geselecteerd';
      label.classList.remove('is-placeholder');
    }

    function renderTags() {
      tagsWrap.innerHTML = '';

      Array.from(selected).forEach(function (value) {
        var option = options.find(function (item) { return item.getAttribute('data-value') === value; });
        if (!option) return;

        var tag = document.createElement('span');
        tag.className = 'mh-units-catalog__select-tag';
        tag.style.cssText = 'display:inline-flex;align-items:center;gap:6px;padding:7px 12px 7px 16px;border:1px solid #DEDEDE;border-radius:999px;background:#fff;color:#333;font-family:"Poppins",sans-serif;font-size:13px;font-weight:700;line-height:1.3;box-shadow:0 4px 20px -4px rgba(0,0,0,0.08);';

        var text = document.createElement('span');
        text.textContent = option.getAttribute('data-label') || value;

        var remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'mh-units-catalog__select-tag-remove';
        remove.setAttribute('aria-label', 'Verwijder ' + text.textContent);
        remove.innerHTML = '&times;';
        remove.style.cssText = 'appearance:none;display:flex;align-items:center;justify-content:center;width:16px;height:16px;padding:0;margin:0;border:none;border-radius:50%;background:transparent;color:#999;font-size:17px;font-weight:700;line-height:1;cursor:pointer;';
        remove.addEventListener('click', function (event) {
          event.stopPropagation();
          selected.delete(value);
          syncState();
        });

        tag.appendChild(text);
        tag.appendChild(remove);
        tagsWrap.appendChild(tag);
      });
    }

    function syncOptions() {
      options.forEach(function (option) {
        var isSelected = selected.has(option.getAttribute('data-value') || '');
        option.classList.toggle('is-selected', isSelected);
        option.setAttribute('aria-selected', isSelected ? 'true' : 'false');
        option.style.background = isSelected ? '#f1f8ee' : '';
        option.style.color = isSelected ? '#111' : '#333';
        option.style.fontWeight = isSelected ? '600' : '400';
      });
    }

    function syncState() {
      syncOptions();
      syncHiddenInputs();
      syncLabel();
      renderTags();
    }

    function syncEmptyState() {
      if (!emptyState) return;
      var hasVisible = options.some(function (option) {
        return option.style.display !== 'none';
      });
      emptyState.style.display = hasVisible ? 'none' : 'block';
    }

    options.forEach(function (option) {
      var value = option.getAttribute('data-value') || '';
      if (option.classList.contains('is-selected')) {
        selected.add(value);
      }

      option.addEventListener('click', function (event) {
        event.stopPropagation();
        if (selected.has(value)) {
          selected.delete(value);
        } else {
          selected.add(value);
        }
        syncState();
      });
    });

    button.addEventListener('click', function (event) {
      event.stopPropagation();
      var willOpen = !select.classList.contains('is-open');
      var panel = select.querySelector('.mh-units-catalog__select-panel');
      var chevron = select.querySelector('.mh-units-catalog__select-chevron');
      closeAllSelects(select);
      select.classList.toggle('is-open', willOpen);
      button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      if (panel) panel.style.display = willOpen ? 'block' : 'none';
      if (chevron) chevron.style.transform = willOpen ? 'rotate(180deg)' : '';
      if (willOpen && searchInput) searchInput.focus();
    });

    if (searchInput) {
      searchInput.addEventListener('input', function () {
        var query = searchInput.value.trim().toLowerCase();
        options.forEach(function (option) {
          var text = (option.getAttribute('data-label') || '').toLowerCase();
          option.style.display = !query || text.indexOf(query) !== -1 ? '' : 'none';
        });
        syncEmptyState();
      });
    }

    syncState();
    syncEmptyState();
  });

  document.addEventListener('click', function () {
    closeAllSelects();
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closeAllSelects();
    }
  });
});
</script>
