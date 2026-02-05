<?php if (!defined('ABSPATH')) exit; ?>

<div class="si-opdrachten-filter-wrap ga-filter-wrap">
  <div class="filter-header-extra ga-filter-inner">

    <div class="si-filter-header ga-filter-header">
      <div class="si-filter-header-top">
        <div class="si-filter-header-text">
          <h1>Geefacties die jouw hulp kunnen gebruiken</h1>
        </div>
      </div>
    </div>

    <form id="gaFilterForm" class="si-opdrachten-filter ga-filter" method="get">

      <!-- Rij 1: Zoek + Soort geefactie (JS multi) -->
      <div class="ga-filter-row ga-filter-row--top">
        <div class="ga-filter-item ga-filter-search">
          <input
            id="ga_search"
            name="ga_search"
            type="text"
            placeholder="Zoek..."
            value="<?php echo esc_attr($search_query ?? ''); ?>"
            autocomplete="off"
          >
          <span class="ga-filter-search-icon" aria-hidden="true">🔍</span>
        </div>

        <!-- MULTI: Soort geefactie -->
        <div class="ga-filter-item ga-filter-type">
          <div class="si-multi" data-name="ga_type[]">
            <button type="button" class="si-multi-btn" aria-expanded="false">
              <span class="si-multi-placeholder">Geefacties: Alle soorten</span>
              <span class="si-multi-tags" aria-hidden="true"></span>
              <span class="si-multi-caret">▾</span>
            </button>

            <div class="si-multi-panel" role="listbox">
              <div class="si-multi-options">
                <?php if (!is_wp_error($types) && !empty($types)) : ?>
                  <?php foreach ($types as $term): ?>
                    <label class="si-multi-option">
                      <input
                        type="checkbox"
                        value="<?php echo esc_attr($term->slug); ?>"
                        <?php checked(in_array($term->slug, ($selected_types ?? []), true)); ?>
                      >
                      <span><?php echo esc_html($term->name); ?></span>
                    </label>
                  <?php endforeach; ?>
                <?php else : ?>
                  <div class="si-multi-empty">Nog geen soorten.</div>
                <?php endif; ?>
              </div>
            </div>

            <div class="si-multi-hidden"></div>
          </div>
        </div>
      </div>

      <!-- Rij 2: Thema (JS multi) -->
      <div class="ga-filter-row ga-filter-row--bottom">
        <div class="ga-filter-item ga-filter-thema">
          <div class="si-multi" data-name="ga_thema[]">
            <button type="button" class="si-multi-btn" aria-expanded="false">
              <span class="si-multi-placeholder">Thema: Alle thema's</span>
              <span class="si-multi-tags" aria-hidden="true"></span>
              <span class="si-multi-caret">▾</span>
            </button>

            <div class="si-multi-panel" role="listbox">
              <div class="si-multi-options">
                <?php if (!is_wp_error($themas) && !empty($themas)) : ?>
                  <?php foreach ($themas as $term): ?>
                    <label class="si-multi-option">
                      <input
                        type="checkbox"
                        value="<?php echo esc_attr($term->slug); ?>"
                        <?php checked(in_array($term->slug, ($selected_thema ?? []), true)); ?>
                      >
                      <span><?php echo esc_html($term->name); ?></span>
                    </label>
                  <?php endforeach; ?>
                <?php else : ?>
                  <div class="si-multi-empty">Nog geen thema's.</div>
                <?php endif; ?>
              </div>
            </div>

            <div class="si-multi-hidden"></div>
          </div>
        </div>
      </div>

      <div class="ga-filter-reset-row">
        <a class="si-reset-link" href="<?php echo esc_url(get_permalink()); ?>">Reset</a>
      </div>

    </form>

  </div>
</div>

<script>
(function(){
  function closeAll(except){
    document.querySelectorAll('#gaFilterForm .si-multi.is-open').forEach(function(el){
      if(except && el === except) return;
      el.classList.remove('is-open');
      var btn = el.querySelector('.si-multi-btn');
      if(btn) btn.setAttribute('aria-expanded','false');
    });
  }

  function updateHiddenInputs(multi){
    var name = multi.getAttribute('data-name');
    var hiddenWrap = multi.querySelector('.si-multi-hidden');
    if(!hiddenWrap) return;
    hiddenWrap.innerHTML = '';

    var checked = Array.from(multi.querySelectorAll('input[type="checkbox"]:checked'));
    checked.forEach(function(cb){
      var input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      input.value = cb.value;
      hiddenWrap.appendChild(input);
    });

    var placeholderEl = multi.querySelector('.si-multi-placeholder');
    if(!placeholderEl) return;

    if(!placeholderEl.getAttribute('data-default')){
      placeholderEl.setAttribute('data-default', placeholderEl.textContent.trim());
    }

    if(checked.length === 0){
      placeholderEl.textContent = placeholderEl.getAttribute('data-default');
      return;
    }
    placeholderEl.textContent = (checked.length === 1) ? '1 geselecteerd' : (checked.length + ' geselecteerd');
  }

  function submitFormFrom(el){
    var form = el.closest('form');
    if(form) form.submit();
  }

  // Init multi-selects
  document.querySelectorAll('#gaFilterForm .si-multi').forEach(function(multi){
    var btn = multi.querySelector('.si-multi-btn');
    if(!btn) return;

    btn.addEventListener('click', function(e){
      e.preventDefault();
      var isOpen = multi.classList.contains('is-open');
      closeAll(multi);
      multi.classList.toggle('is-open', !isOpen);
      btn.setAttribute('aria-expanded', (!isOpen) ? 'true' : 'false');
    });

    multi.querySelectorAll('input[type="checkbox"]').forEach(function(cb){
      cb.addEventListener('change', function(){
        updateHiddenInputs(multi);
        submitFormFrom(multi);
      });
    });

    updateHiddenInputs(multi);
  });

  // debounce search
  var searchInput = document.getElementById('ga_search');
  if(searchInput){
    var t;
    searchInput.addEventListener('input', function(){
      clearTimeout(t);
      t = setTimeout(function(){ submitFormFrom(searchInput); }, 450);
    });
  }

  // Outside click closes
  document.addEventListener('click', function(e){
    var inside = e.target.closest('#gaFilterForm .si-multi');
    if(!inside) closeAll(null);
  });

  // Escape closes
  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape') closeAll(null);
  });
})();
</script>