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

      <!-- Rij 1: Zoek + type -->
      <div class="ga-filter-row ga-filter-row--top">
        <div class="ga-filter-item ga-filter-search">
          <input
            id="ga_search"
            name="ga_search"
            type="text"
            placeholder="Zoek..."
            value="<?php echo esc_attr($search_query); ?>"
            autocomplete="off"
          >
          <span class="ga-filter-search-icon" aria-hidden="true">🔍</span>
        </div>

        <div class="ga-filter-item ga-filter-type">
          <select name="ga_type[]" id="ga_type">
            <option value="">Geefacties</option>
            <?php if (!is_wp_error($types) && !empty($types)) : ?>
              <?php foreach ($types as $term): ?>
                <option value="<?php echo esc_attr($term->slug); ?>" <?php selected(in_array($term->slug, $selected_types, true)); ?>>
                  <?php echo esc_html($term->name); ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
      </div>

      <!-- Rij 2: Sorteren + Toon + Thema -->
      <div class="ga-filter-row ga-filter-row--bottom">
        <div class="ga-filter-item ga-filter-sort">
          <select name="ga_sort" id="ga_sort">
            <option value="trending" <?php selected($selected_sort === 'trending'); ?>>Sorteren: Trending</option>
            <option value="nieuw" <?php selected($selected_sort === 'nieuw'); ?>>Sorteren: Nieuw</option>
            <option value="veelgelezen" <?php selected($selected_sort === 'veelgelezen'); ?>>Sorteren: Veel gelezen</option>
          </select>
        </div>

        <div class="ga-filter-item ga-filter-toon">
          <select name="ga_toon" id="ga_toon">
            <option value="all" <?php selected($selected_toon === 'all'); ?>>Toon: Alle geefacties</option>
            <option value="active" <?php selected($selected_toon === 'active'); ?>>Toon: Actief</option>
            <option value="completed" <?php selected($selected_toon === 'completed'); ?>>Toon: Succesvol</option>
          </select>
        </div>

        <div class="ga-filter-item ga-filter-thema">
          <select name="ga_thema[]" id="ga_thema">
            <option value="">Thema: Alle thema's</option>
            <?php if (!is_wp_error($themas) && !empty($themas)) : ?>
              <?php foreach ($themas as $term): ?>
                <option value="<?php echo esc_attr($term->slug); ?>" <?php selected(in_array($term->slug, $selected_thema, true)); ?>>
                  <?php echo esc_html($term->name); ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
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
  function submitForm(){
    var form = document.getElementById('gaFilterForm');
    if(form) form.submit();
  }

  // change => submit
  ['ga_type','ga_sort','ga_toon','ga_thema'].forEach(function(id){
    var el = document.getElementById(id);
    if(el){
      el.addEventListener('change', function(){
        submitForm();
      });
    }
  });

  // debounce search
  var searchInput = document.getElementById('ga_search');
  if(searchInput){
    var t;
    searchInput.addEventListener('input', function(){
      clearTimeout(t);
      t = setTimeout(function(){ submitForm(); }, 450);
    });
  }
})();
</script>
