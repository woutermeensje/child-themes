<?php
if ( ! defined( 'ABSPATH' ) ) exit;

wp_enqueue_script( 'wp-job-manager-ajax-filters' );
do_action( 'job_manager_job_filters_before', $atts );

$selected = [
  'job_types' => [],
];

$shortcode_atts = shortcode_atts([
  'job_listing_type' => '',
], $atts);

foreach ( $selected as $key => &$value ) {
  $shortcode_key = ( $key === 'job_types' ) ? 'job_listing_type' : $key;
  $filter_key    = 'filter_' . $key;

  if      ( ! empty( $_GET[$key] ) )             $value = (array) $_GET[$key];
  elseif  ( ! empty( $_GET[$filter_key] ) )      $value = (array) $_GET[$filter_key];
  elseif  ( ! empty( $_POST[$filter_key] ) )     $value = (array) $_POST[$filter_key];
  elseif  ( ! empty( $_POST[$key] ) )            $value = (array) $_POST[$key];
  elseif  ( ! empty( $shortcode_atts[$shortcode_key] ) ) {
    $value = array_filter( array_map( 'trim', explode( ',', sanitize_text_field( $shortcode_atts[$shortcode_key] ) ) ) );
  }
}
unset( $value );

$keywords = isset( $keywords ) ? $keywords : ( $_GET['search_keywords'] ?? '' );
$location = isset( $location ) ? $location : ( $_GET['search_location'] ?? '' );

if ( ! function_exists( 'mh_get_open_job_filter_counts' ) ) {
  function mh_get_open_job_filter_counts( $taxonomy ) {
    static $cache = [];
    $taxonomy = sanitize_key( $taxonomy );
    if ( isset( $cache[$taxonomy] ) ) return $cache[$taxonomy];
    if ( ! taxonomy_exists( $taxonomy ) ) { $cache[$taxonomy] = []; return []; }

    global $wpdb;
    $sql = $wpdb->prepare(
      "SELECT tt.term_id, COUNT(DISTINCT p.ID) AS open_jobs
       FROM {$wpdb->term_relationships} tr
       INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
       INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
       LEFT JOIN {$wpdb->postmeta} filled ON filled.post_id = p.ID AND filled.meta_key = '_filled' AND filled.meta_value = '1'
       LEFT JOIN {$wpdb->postmeta} expires ON expires.post_id = p.ID AND expires.meta_key = '_job_expires'
       WHERE tt.taxonomy = %s
         AND p.post_type = 'job_listing'
         AND p.post_status = 'publish'
         AND filled.meta_id IS NULL
         AND (expires.meta_id IS NULL OR expires.meta_value = '' OR expires.meta_value >= %s)
       GROUP BY tt.term_id",
      $taxonomy,
      current_time( 'Y-m-d' )
    );
    $counts = [];
    foreach ( (array) $wpdb->get_results( $sql ) as $row ) {
      $counts[ (int) $row->term_id ] = (int) $row->open_jobs;
    }
    $cache[$taxonomy] = $counts;
    return $counts;
  }
}

$job_type_counts = mh_get_open_job_filter_counts( 'job_listing_type' );
?>

<form class="job_filters">
  <?php do_action( 'job_manager_job_filters_start', $atts ); ?>

  <div class="filter-header">
    <h2>Werken bij Modulairehuisvesting</h2>
    <p>Bekijk onze openstaande vacatures en word onderdeel van ons team.</p>
  </div>

  <div class="search-basic">
    <?php do_action( 'job_manager_job_filters_search_jobs_start', $atts ); ?>

    <div class="search_keywords">
      <input type="text" name="search_keywords" id="search_keywords"
             placeholder="Functienaam of afdeling.."
             value="<?php echo esc_attr( $keywords ); ?>" />
    </div>

    <div class="search_location">
      <input type="text" name="search_location" id="search_location"
             placeholder="Stad of locatie"
             value="<?php echo esc_attr( $location ); ?>" />
    </div>

    <?php do_action( 'job_manager_job_filters_search_jobs_end', $atts ); ?>
  </div>

  <div class="filter-box">

    <!-- Dienstverband (MULTI) -->
    <div class="job_type">
      <select name="filter_job_types[]" id="filter_job_types"
              class="js-custom-select job_types"
              data-placeholder="Dienstverband"
              multiple>
        <?php foreach ( get_job_listing_types() as $type ) :
          $count = $job_type_counts[ (int) $type->term_id ] ?? 0;
        ?>
          <option value="<?php echo esc_attr( $type->slug ); ?>"
            data-label="<?php echo esc_attr( $type->name ); ?>"
            data-count="<?php echo esc_attr( $count ); ?>"
            <?php selected( in_array( $type->slug, $selected['job_types'], true ) ); ?>>
            <?php echo esc_html( $type->name . ' (' . $count . ')' ); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

  </div>

  <!-- Active filters (chips below the filter-box) -->
  <div class="active-filters" id="active-filters" aria-live="polite"></div>

</form>

<?php do_action( 'job_manager_job_filters_after', $atts ); ?>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form.job_filters");
  if (!form) return;

  const wpjmFilter = () => {
    if (window.job_manager_job_filters && typeof window.job_manager_job_filters.filter_jobs === "function") {
      window.job_manager_job_filters.filter_jobs();
    } else {
      form.dispatchEvent(new Event("submit", { bubbles: true, cancelable: true }));
    }
  };

  const debounce = (fn, delay = 250) => {
    let t;
    return () => { clearTimeout(t); t = setTimeout(fn, delay); };
  };

  const kw  = document.querySelector("#search_keywords");
  const loc = document.querySelector("#search_location");
  if (kw)  kw.addEventListener("input",  debounce(wpjmFilter, 250));
  if (loc) loc.addEventListener("input", debounce(wpjmFilter, 250));

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    wpjmFilter();
  });

  const closeAll = () => {
    document.querySelectorAll(".sj-select.active").forEach((el) => {
      el.classList.remove("active");
      const searchInput = el.querySelector(".sj-search-input");
      if (searchInput) {
        searchInput.value = "";
        el.querySelectorAll(".sj-option").forEach((opt) => { opt.style.display = ""; });
      }
    });
  };

  const activeFiltersEl = document.getElementById("active-filters");

  const renderActiveFilters = () => {
    if (!activeFiltersEl) return;
    activeFiltersEl.innerHTML = "";

    document.querySelectorAll("select.js-custom-select").forEach((select) => {
      [...select.options].filter(o => o.selected && o.value !== "").forEach((opt) => {
        const chip = document.createElement("span");
        chip.className = "active-filter";
        chip.setAttribute("role", "button");
        chip.setAttribute("title", "Verwijder filter");
        chip.innerHTML = `<span class="active-filter-text"></span><span class="active-filter-x" aria-hidden="true">×</span>`;
        chip.querySelector(".active-filter-text").textContent = opt.dataset.label || opt.textContent;
        chip.addEventListener("click", (e) => {
          e.preventDefault();
          opt.selected = false;
          select.dispatchEvent(new Event("change", { bubbles: true }));
        });
        activeFiltersEl.appendChild(chip);
      });
    });

    activeFiltersEl.style.display = activeFiltersEl.children.length ? "flex" : "none";
  };

  const buildSelect = (select) => {
    const isMultiple  = select.multiple === true;
    const forceMode   = select.dataset.mode;
    const isSingle    = forceMode === "single" ? true : !isMultiple;
    const placeholder = select.dataset.placeholder || "Selecteer";

    const wrap = document.createElement("div");
    wrap.className = "sj-select-wrap";
    select.parentNode.insertBefore(wrap, select);
    wrap.appendChild(select);
    select.classList.add("sj-hidden-select");

    const root = document.createElement("div");
    root.className = "sj-select";
    root.dataset.type = isSingle ? "single" : "multi";

    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "sj-select-btn";
    btn.innerHTML = `
      <span class="sj-btn-content">
        <span class="sj-placeholder">${placeholder}</span>
        <span class="sj-tags" aria-hidden="true"></span>
      </span>
      <span class="sj-select__actions">
        <button type="button" class="sj-clear" aria-label="Wis selectie" title="Wis selectie">×</button>
        <span class="sj-chev" aria-hidden="true"></span>
      </span>
    `;

    const clearBtn = btn.querySelector(".sj-clear");
    clearBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      e.preventDefault();
      [...select.options].forEach(o => o.selected = false);
      renderState();
      select.dispatchEvent(new Event("change", { bubbles: true }));
    });

    const list = document.createElement("div");
    list.className = "sj-options";
    list.setAttribute("role", "listbox");
    if (!isSingle) list.setAttribute("aria-multiselectable", "true");

    let searchInput = null;
    if (!isSingle) {
      const searchWrap = document.createElement("div");
      searchWrap.className = "sj-search";
      searchWrap.innerHTML = `<input type="text" class="sj-search-input" placeholder="Zoek in ${placeholder.toLowerCase()}">`;
      searchInput = searchWrap.querySelector(".sj-search-input");
      list.appendChild(searchWrap);
    }

    const makeOptionRow = (opt) => {
      const row = document.createElement("div");
      row.className = "sj-option";
      row.dataset.value = opt.value;
      row.setAttribute("role", "option");
      row.setAttribute("aria-selected", opt.selected ? "true" : "false");
      const optionLabel = opt.dataset.label || opt.textContent.trim();
      const optionCount = opt.dataset.count;
      row.innerHTML = `<span class="sj-option-text"></span>${optionCount !== undefined ? '<span class="sj-option-count"></span>' : ''}`;
      row.querySelector(".sj-option-text").textContent = optionLabel;
      const countEl = row.querySelector(".sj-option-count");
      if (countEl) countEl.textContent = optionCount;

      const syncSelected = () => {
        row.classList.toggle("is-selected", opt.selected);
        row.setAttribute("aria-selected", opt.selected ? "true" : "false");
      };
      syncSelected();

      row.addEventListener("click", (e) => {
        e.preventDefault();
        if (opt.disabled) return;
        if (isSingle) {
          [...select.options].forEach(o => o.selected = false);
          opt.selected = true;
          closeAll();
          root.classList.remove("active");
        } else {
          opt.selected = !opt.selected;
        }
        renderState();
        select.dispatchEvent(new Event("change", { bubbles: true }));
      });

      return { row, syncSelected };
    };

    const optionRows = [];
    [...select.options].forEach((opt) => {
      if (isSingle && opt.value === "") return;
      const { row, syncSelected } = makeOptionRow(opt);
      optionRows.push({ opt, row, syncSelected });
      list.appendChild(row);
    });

    const filterOptionRows = () => {
      if (!searchInput) return;
      const term = searchInput.value.trim().toLowerCase();
      optionRows.forEach(({ row, opt }) => {
        const label = opt.dataset.label || opt.textContent;
        row.style.display = label.toLowerCase().includes(term) ? "" : "none";
      });
    };

    if (searchInput) {
      searchInput.addEventListener("input", filterOptionRows);
      searchInput.addEventListener("click", (e) => e.stopPropagation());
      searchInput.addEventListener("keydown", (e) => e.stopPropagation());
    }

    const tagsEl        = btn.querySelector(".sj-tags");
    const placeholderEl = btn.querySelector(".sj-placeholder");

    const renderState = () => {
      optionRows.forEach(({ syncSelected }) => syncSelected());
      const selectedOptions = [...select.options].filter(o => o.selected && o.value !== "");
      tagsEl.innerHTML = "";
      if (selectedOptions.length === 0) {
        placeholderEl.style.display = "inline";
        tagsEl.style.display = "none";
        clearBtn.style.display = "none";
        return;
      }
      clearBtn.style.display = "inline-flex";
      placeholderEl.textContent = placeholder;
      placeholderEl.style.display = "inline";
      tagsEl.style.display = "none";
    };

    renderState();

    btn.addEventListener("click", (e) => {
      if (e.target.closest(".sj-clear")) return;
      e.preventDefault();
      const wasOpen = root.classList.contains("active");
      closeAll();
      if (!wasOpen) {
        root.classList.add("active");
        if (searchInput) {
          searchInput.value = "";
          filterOptionRows();
          window.setTimeout(() => searchInput.focus(), 10);
        }
      }
    });

    select.addEventListener("change", () => {
      renderState();
      renderActiveFilters();
      wpjmFilter();
    });

    root.appendChild(btn);
    root.appendChild(list);
    wrap.appendChild(root);
  };

  document.querySelectorAll("select.js-custom-select").forEach(buildSelect);
  renderActiveFilters();

  document.addEventListener("click", (e) => {
    if (!e.target.closest(".sj-select")) closeAll();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeAll();
  });
});
</script>
