<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// WP Job Manager AJAX filters
wp_enqueue_script( 'wp-job-manager-ajax-filters' );
do_action( 'job_manager_job_filters_before', $atts );

/**
 * ✅ Selected values come from: $_GET > $_POST > shortcode atts
 */
$selected = [
  'job_company'   => [],
  'job_sector'    => [],
  'job_types'     => [],
  'organisatie_type' => [],
];

$shortcode_atts = shortcode_atts([
  'job_company'      => '',
  'job_sector'       => '',
  'job_listing_type' => '',
  'organisatie_type' => '',
], $atts);

if ( ! function_exists( 'sj_get_req_value' ) ) {
  function sj_get_req_value( $key ) {
    $filter_key = 'filter_' . $key;
    if ( ! empty( $_GET[ $key ] ) )        return (array) $_GET[ $key ];
    if ( ! empty( $_GET[ $filter_key ] ) ) return (array) $_GET[ $filter_key ];
    if ( ! empty( $_POST[ $filter_key ] ) ) return (array) $_POST[ $filter_key ];
    if ( ! empty( $_POST[ $key ] ) )        return (array) $_POST[ $key ];
    return [];
  }
}

foreach ( $selected as $key => &$value ) {
  $shortcode_key = ( $key === 'job_types' ) ? 'job_listing_type' : $key;
  $req = sj_get_req_value( $key );
  if ( ! empty( $req ) ) {
    $value = $req;
  } elseif ( ! empty( $shortcode_atts[ $shortcode_key ] ) ) {
    $value = array_filter( array_map( 'trim', explode( ',', sanitize_text_field( $shortcode_atts[ $shortcode_key ] ) ) ) );
  }
}
unset( $value );

$keywords = isset( $keywords ) ? $keywords : ( $_GET['search_keywords'] ?? '' );
$location = isset( $location ) ? $location : ( $_GET['search_location'] ?? '' );

if ( ! function_exists( 'sj_get_open_job_filter_counts' ) ) {
  function sj_get_open_job_filter_counts( $taxonomy ) {
    static $counts_by_taxonomy = [];

    $taxonomy = sanitize_key( $taxonomy );
    if ( isset( $counts_by_taxonomy[ $taxonomy ] ) ) {
      return $counts_by_taxonomy[ $taxonomy ];
    }

    if ( ! taxonomy_exists( $taxonomy ) ) {
      $counts_by_taxonomy[ $taxonomy ] = [];
      return $counts_by_taxonomy[ $taxonomy ];
    }

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

    $counts_by_taxonomy[ $taxonomy ] = $counts;
    return $counts_by_taxonomy[ $taxonomy ];
  }
}

if ( ! function_exists( 'sj_render_filter_option_with_count' ) ) {
  function sj_render_filter_option_with_count( $term, $selected_values, $counts ) {
    $count           = $counts[ (int) $term->term_id ] ?? 0;
    $formatted_count = number_format_i18n( $count );
    ?>
    <option value="<?php echo esc_attr( $term->slug ); ?>"
      data-label="<?php echo esc_attr( $term->name ); ?>"
      data-count="<?php echo esc_attr( $formatted_count ); ?>"
      <?php selected( in_array( $term->slug, $selected_values, true ) ); ?>>
      <?php echo esc_html( $term->name . ' (' . $formatted_count . ')' ); ?>
    </option>
    <?php
  }
}

if ( ! function_exists( 'sj_sort_terms_by_open_job_count' ) ) {
  function sj_sort_terms_by_open_job_count( $terms, $counts ) {
    if ( empty( $terms ) || is_wp_error( $terms ) ) {
      return [];
    }

    $terms = array_values( (array) $terms );
    usort( $terms, function ( $a, $b ) use ( $counts ) {
      $count_a = $counts[ (int) $a->term_id ] ?? 0;
      $count_b = $counts[ (int) $b->term_id ] ?? 0;

      if ( $count_a === $count_b ) {
        return strcasecmp( $a->name, $b->name );
      }

      return $count_b <=> $count_a;
    } );

    return $terms;
  }
}

$job_type_counts         = sj_get_open_job_filter_counts( 'job_listing_type' );
$job_sector_counts       = sj_get_open_job_filter_counts( 'job_sector' );
$organisatie_type_counts = sj_get_open_job_filter_counts( 'organisatie_type' );
$job_company_counts      = sj_get_open_job_filter_counts( 'job_company' );
?>

<form class="job_filters">
  <?php do_action( 'job_manager_job_filters_start', $atts ); ?>

  <div class="filter-header" style="padding: 0 24px 18px;">
    <h2>Bekijk alle Duurzame Vacatures in ons Netwerk!</h2>
    <p>Of schrijf je in voor de <a href="https://sustainablejobs.nl/nieuwsbrief/" target="_blank" class="unstyled-newsletter-link" rel="noopener">vacature nieuwsbrief</a>!</p>
  </div>

  <div class="search-basic">
    <?php do_action( 'job_manager_job_filters_search_jobs_start', $atts ); ?>

    <div class="search_keywords">
      <input type="text" name="search_keywords" id="search_keywords"
             placeholder="Functienaam, sector of onderwerp.."
             value="<?php echo esc_attr( $keywords ); ?>" />
    </div>

    <div class="search_location">
      <input type="text" name="search_location" id="search_location"
             placeholder="Stad of plaats"
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
        <?php foreach ( sj_sort_terms_by_open_job_count( get_job_listing_types(), $job_type_counts ) as $type ) : ?>
          <?php sj_render_filter_option_with_count( $type, $selected['job_types'], $job_type_counts ); ?>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Sector (MULTI) -->
    <div class="job_sector">
      <select name="filter_job_sector[]" id="filter_job_sector"
              class="js-custom-select job_sector"
              data-placeholder="Sector"
              multiple>
        <?php foreach ( sj_sort_terms_by_open_job_count( get_terms( [ 'taxonomy' => 'job_sector', 'hide_empty' => true ] ), $job_sector_counts ) as $term ) : ?>
          <?php sj_render_filter_option_with_count( $term, $selected['job_sector'], $job_sector_counts ); ?>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Type organisatie (MULTI) -->
    <div class="job_organisatie_type">
      <select name="filter_organisatie_type[]" id="filter_organisatie_type"
              class="js-custom-select job_organisatie_type"
              data-placeholder="Type organisatie"
              multiple>
        <?php foreach ( sj_sort_terms_by_open_job_count( get_terms( [ 'taxonomy' => 'organisatie_type', 'hide_empty' => false ] ), $organisatie_type_counts ) as $term ) : ?>
          <?php sj_render_filter_option_with_count( $term, $selected['organisatie_type'], $organisatie_type_counts ); ?>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Organisatie (MULTI) -->
    <div class="job_company">
      <select name="filter_job_company[]" id="filter_job_company"
              class="js-custom-select job_company"
              data-placeholder="Organisatie"
              multiple>
        <?php foreach ( sj_sort_terms_by_open_job_count( get_terms( [ 'taxonomy' => 'job_company', 'hide_empty' => true ] ), $job_company_counts ) as $term ) : ?>
          <?php sj_render_filter_option_with_count( $term, $selected['job_company'], $job_company_counts ); ?>
        <?php endforeach; ?>
      </select>
    </div>

  </div>

  <!-- Actieve filters (chips onder de filter-box) -->
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
        el.querySelectorAll(".sj-option").forEach((option) => {
          option.style.display = "";
        });
      }
    });
  };

  const activeFiltersEl = document.getElementById("active-filters");

  const renderActiveFilters = () => {
    if (!activeFiltersEl) return;
    activeFiltersEl.innerHTML = "";

    document.querySelectorAll("select.js-custom-select").forEach((select) => {
      const selectedOptions = [...select.options].filter(o => o.selected && o.value !== "");
      selectedOptions.forEach((opt) => {
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
      <span class="sj-actions">
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
        const optionLabel = opt.dataset.label || opt.textContent;
        row.style.display = optionLabel.toLowerCase().includes(term) ? "" : "none";
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

// WP Job Manager scrolt automatisch naar ul.job_listings na AJAX-load.
// Op taxonomy-pagina's (/vacatures/bedrijf/) scroll je daardoor voorbij de filter.
// Fix: zodra de listings updaten bij initial load, scroll terug naar de filter.
(function () {
  var parts = window.location.pathname.replace(/\/+$/, "").split("/").filter(Boolean);
  if (parts.length < 2) return; // alleen op /vacatures/[slug]/ pagina's

  var corrected = false;
  var listingsEl = document.querySelector(".job_listings");
  if (!listingsEl) return;

  var scrollToFilter = function () {
    if (corrected) return;
    corrected = true;
    observer.disconnect();
    requestAnimationFrame(function () {
      var filter = document.querySelector("form.job_filters");
      if (!filter) return;
      var filterTop = filter.getBoundingClientRect().top + window.scrollY;
      window.scrollTo({ top: Math.max(0, filterTop - 100), behavior: "smooth" });
    });
  };

  var observer = new MutationObserver(scrollToFilter);
  observer.observe(listingsEl, { childList: true, subtree: true, attributes: true, attributeFilter: ["class"] });

  // Fallback: als WP JM snel klaar is, dan na 1 seconde toch corrigeren
  window.addEventListener("load", function () {
    setTimeout(scrollToFilter, 1000);
  });
}());
</script>


<style>
/* =========================
   LAYOUT
   ========================= */
.job_filters {
  width: 100vw;
  position: relative;
  left: 50%;
  right: 50%;
  margin-left: -50vw;
  margin-right: -50vw;
  margin-top: 0;
  margin-bottom: 0;
  min-height: 300px;
  padding: 56px 0;
  background: var(--color-bg-filter);
  border-top: 1px solid var(--color-border);
  border-bottom: 1px solid var(--color-border);
  box-sizing: border-box;
  margin-bottom: 40px !important;
  scroll-margin-top: 90px;
}

.filter-header,
.search-basic,
.filter-box,
.active-filters {
  max-width: 1200px;
  margin-left: auto !important;
  margin-right: auto !important;
}

/* =========================
   HEADER
   ========================= */
.filter-header {
  padding: 0 24px 18px !important;
}

.filter-header h2 {
  margin: 0;
  font-family: 'Inter', sans-serif;
  font-size: 24px;
  line-height: 1.1;
  font-weight: 700;
  color: var(--color-midnight-blue);
}

.filter-header p {
  margin: 10px 0 0;
  font-family: 'Poppins', sans-serif;
  font-size: 15px;
  color: var(--color-text-muted);
}

body .filter-header a.unstyled-newsletter-link {
  color: var(--color-primary);
  font-weight: 400;
  text-decoration: underline;
  font-family: 'Poppins', sans-serif;
}

body .filter-header a.unstyled-newsletter-link:hover {
  color: var(--color-primary-hover) !important;
  text-decoration: underline;
}

/* =========================
   SEARCH INPUTS
   ========================= */
.search-basic {
  display: flex;
  gap: 16px;
  padding: 0 24px;
}

.search_location,
.search_keywords {
  flex-basis: 50%;
  max-width: 50%;
  display: flex;
  align-items: center;
  position: relative;
}

.search-basic input[type="text"] {
  width: 100%;
  padding: 13px 14px 13px 40px;
  font-size: 15px;
  border: 1px solid #DDE8C5;
  border-radius: 8px;
  background-color: #ffffff;
  color: var(--color-text);
  box-shadow: none;
  transition: border-color .2s ease, box-shadow .2s ease;
  font-family: 'Poppins', sans-serif;
  font-weight: 400;
}

.search-basic input[type="text"]:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(22, 138, 173, 0.15);
}

.search_keywords input::placeholder,
.search_location input::placeholder {
  color: #7c7c7c;
  font-size: 15px !important;
  font-style: italic;
}

.search_keywords::before,
.search_location::before {
  content: '';
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  background-repeat: no-repeat;
  background-size: contain;
  pointer-events: none;
}

.search_keywords::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23168AAD' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E");
}

.search_location::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23168AAD' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 2a7 7 0 0 1 7 7c0 5.25-7 13-7 13S5 14.25 5 9a7 7 0 0 1 7-7z'/%3E%3Ccircle cx='12' cy='9' r='2.5'/%3E%3C/svg%3E");
}

/* =========================
   FILTER BOX
   ========================= */
.filter-box {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  padding: 16px 24px 0;
}

.filter-box > div {
  flex: 0 0 auto !important;
  min-width: 0 !important;
}

/* =========================
   CUSTOM SELECT (vanilla)
   ========================= */
select.sj-hidden-select {
  position: absolute !important;
  left: -9999px !important;
  width: 1px !important;
  height: 1px !important;
  opacity: 0 !important;
}

.sj-select-wrap,
.sj-select {
  position: relative;
  width: auto;
  display: inline-block;
}

.sj-select-btn {
  width: auto;
  display: inline-flex;
  align-items: center;
  justify-content: space-between;
  white-space: nowrap;
  border-radius: 999px;
  border: 1px solid #DDE8C5 !important;
  background-color: #ffffff !important;
  padding: 12px;
  min-height: 44px;
  cursor: pointer;
  user-select: none;
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  font-size: 15px;
  color: #111111 !important;
}

.sj-select-btn:focus {
  outline: none;
  border-color: var(--color-primary) !important;
  box-shadow: 0 0 0 2px rgba(22, 138, 173, 0.18);
}

.sj-select-btn,
.sj-select-btn:hover,
.sj-select.active .sj-select-btn,
.sj-select-btn:focus {
  background: #ffffff !important;
  color: #111111 !important;
}

.sj-btn-content {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  min-width: 0;
}

.sj-placeholder {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  color: var(--color-text);
  font-size: 15px;
}

.sj-tags {
  display: none;
  gap: 6px;
  align-items: center;
  flex-wrap: wrap;
}

.sj-actions {
  display: inline-flex;
  align-items: center;
  gap: 0 !important;
  margin-left: 10px;
}

.sj-clear {
  display: none !important;
}

.sj-chev {
  width: 10px;
  height: 10px;
  border-right: 2px solid #111;
  border-bottom: 2px solid #111;
  transform: rotate(45deg);
  transition: transform .2s ease;
}

.sj-select.active .sj-chev {
  transform: rotate(-135deg);
}

/* =========================
   DROPDOWN PANEL
   ========================= */
.sj-options {
  display: none;
  position: absolute;
  left: 0;
  right: auto;
  width: max-content;
  min-width: 100%;
  margin-top: 10px;
  background: #fff;
  border: 1px solid #DDE8C5;
  border-radius: 8px;
  box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.12);
  padding: 8px;
  max-height: 280px;
  overflow: auto;
  z-index: 9999;
}

.sj-select.active .sj-options {
  display: block;
}

/* Zoekbalk binnen dropdown */
.sj-search {
  position: sticky;
  top: 0;
  z-index: 2;
  background: #ffffff;
  padding: 4px 4px 10px;
}

.sj-search-input {
  width: 100%;
  min-width: 220px;
  border: 1px solid #DDE8C5;
  border-radius: 8px;
  padding: 11px 12px;
  font-family: 'Poppins', sans-serif;
  font-size: 14px;
  color: var(--color-text);
  background: #ffffff;
  box-sizing: border-box;
}

.sj-search-input::placeholder {
  color: #7c7c7c;
}

.sj-search-input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 2px rgba(22, 138, 173, 0.16);
}

.sj-option {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  min-width: 240px;
  padding: 10px 12px;
  border-radius: 8px;
  cursor: pointer;
}

.sj-option:hover {
  background: var(--color-bg-filter);
}

.sj-option.is-selected .sj-option-text {
  color: var(--color-primary);
  font-weight: 700;
}

.sj-option-text {
  min-width: 0;
  overflow-wrap: anywhere;
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  color: var(--color-text);
}

.sj-option-count {
  flex: 0 0 auto;
  margin-left: 16px;
  font-family: 'Poppins', sans-serif;
  font-size: 13px;
  font-weight: 700;
  color: var(--color-text-muted);
}

.sj-option.is-selected .sj-option-count {
  color: var(--color-primary);
}

/* =========================
   FILTER BUTTON ICONEN
   ========================= */
.job_type .sj-select-btn,
.job_sector .sj-select-btn,
.job_organisatie_type .sj-select-btn,
.job_company .sj-select-btn {
  padding-left: 38px !important;
  position: relative;
}

.job_type .sj-select-btn::before,
.job_sector .sj-select-btn::before,
.job_organisatie_type .sj-select-btn::before,
.job_company .sj-select-btn::before {
  content: '';
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 16px;
  height: 16px;
  background-repeat: no-repeat;
  background-size: contain;
  pointer-events: none;
  flex-shrink: 0;
}

/* Dienstverband → aktetas */
.job_type .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23168AAD' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='2' y='7' width='20' height='14' rx='2'/%3E%3Cpath d='M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2'/%3E%3Cline x1='12' y1='12' x2='12' y2='17'/%3E%3Cline x1='9.5' y1='14.5' x2='14.5' y2='14.5'/%3E%3C/svg%3E");
}

/* Sector → categorie-raster */
.job_sector .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23168AAD' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='3' width='7' height='7'/%3E%3Crect x='14' y='3' width='7' height='7'/%3E%3Crect x='3' y='14' width='7' height='7'/%3E%3Crect x='14' y='14' width='7' height='7'/%3E%3C/svg%3E");
}

/* Certificering → badge/award */
.job_organisatie_type .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23168AAD' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='8' r='6'/%3E%3Cpath d='M15.477 12.89L17 22l-5-3-5 3 1.523-9.11'/%3E%3C/svg%3E");
}

/* Organisatie → gebouw */
.job_company .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23168AAD' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z'/%3E%3Cpolyline points='9 22 9 12 15 12 15 22'/%3E%3C/svg%3E");
}

/* =========================
   ACTIEVE FILTERS (chips)
   ========================= */
.active-filters {
  display: none;
  flex-wrap: wrap;
  gap: 10px;
  margin: 14px 24px 0 !important;
}

span.active-filter {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #ffffff;
  border: 1px solid var(--color-chip-company-text);
  border-radius: 999px;
  box-shadow: 0 10px 24px -18px rgba(37, 79, 110, 0.38);
  padding: 8px 12px;
  font-size: 14px;
  color: var(--color-chip-company-text);
  font-weight: 700;
  cursor: pointer;
  transition: background-color .18s ease, border-color .18s ease, transform .18s ease, box-shadow .18s ease;
}

.active-filter:hover {
  background: var(--color-secondary-soft);
  border-color: var(--color-chip-company-text);
  color: var(--color-chip-company-text);
  transform: translateY(-1px);
  box-shadow: 0 14px 26px -18px rgba(37, 79, 110, 0.42);
}

.active-filter-text {
  color: inherit;
  font-weight: 700;
}

.active-filter-x {
  color: var(--color-chip-company-text);
  font-weight: 700;
  font-size: 18px;
  line-height: 1;
  margin-left: 4px;
  pointer-events: none;
}

/* =========================
   RESPONSIVE
   ========================= */
@media (max-width: 960px) {
  form.job_filters {
    width: 100% !important;
    position: relative !important;
    left: 0 !important;
    right: auto !important;
    margin: 0 !important;
    padding: 24px 0 !important;
  }

  .filter-header {
    padding: 0 16px 12px !important;
  }

  .search-basic {
    flex-direction: column;
    gap: 10px;
    padding: 0 16px !important;
  }

  .search_location,
  .search_keywords {
    max-width: 100%;
    flex-basis: 100%;
    width: 100%;
  }

  .filter-box {
    flex-direction: column;
    padding: 12px 16px 0 !important;
    gap: 10px;
  }

  .filter-box > div,
  .sj-select-wrap,
  .sj-select,
  .sj-select-btn {
    width: 100% !important;
  }

  .sj-options {
    width: 100% !important;
    min-width: 0 !important;
    left: 0 !important;
    right: 0 !important;
  }

  .active-filters {
    margin: 12px 16px 0 !important;
  }
}
</style>
