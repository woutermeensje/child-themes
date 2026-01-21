<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// WP Job Manager AJAX filters
wp_enqueue_script( 'wp-job-manager-ajax-filters' );
do_action( 'job_manager_job_filters_before', $atts );

/**
 * ✅ Selected values come from: $_GET > $_POST > shortcode atts
 * Supports both key and filter_key (WPJM uses filter_*).
 */
$selected = [
  'job_company'   => [],
  'job_sector'    => [],
  'job_types'     => [],
  'certificering' => [],
];

$shortcode_atts = shortcode_atts([
  'job_company'      => '',
  'job_sector'       => '',
  'job_listing_type' => '',
  'certificering'    => '',
], $atts);

// helper: get value from request (supports both key and filter_key)
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

// map selected key -> request key (because WPJM uses filter_job_types)
$req_map = [
  'job_types' => 'job_types', // we read filter_job_types via helper below by passing "job_types"? nope.
];

// IMPORTANT: for job_types we must read filter_job_types, not filter_job_types[]? helper reads filter_{key}
// so for job_types we pass "job_types" and it reads filter_job_types? Actually filter_key becomes filter_job_types if key is "job_types" -> yes.
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

// (optional) read keyword/location from WPJM vars if available
$keywords = isset( $keywords ) ? $keywords : ( $_GET['search_keywords'] ?? '' );
$location = isset( $location ) ? $location : ( $_GET['search_location'] ?? '' );
?>

<form class="job_filters">
  <?php do_action( 'job_manager_job_filters_start', $atts ); ?>

  <div class="filter-header" style="padding: 0 20px 10px 20px;">
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
        <?php foreach ( get_job_listing_types() as $type ) : ?>
          <option value="<?php echo esc_attr( $type->slug ); ?>"
            <?php selected( in_array( $type->slug, $selected['job_types'], true ) ); ?>>
            <?php echo esc_html( $type->name ); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Sector (MULTI) -->
    <div class="job_sector">
      <select name="filter_job_sector[]" id="filter_job_sector"
              class="js-custom-select job_sector"
              data-placeholder="Sector"
              multiple>
        <?php foreach ( get_terms( [ 'taxonomy' => 'job_sector', 'hide_empty' => true ] ) as $term ) : ?>
          <option value="<?php echo esc_attr( $term->slug ); ?>"
            <?php selected( in_array( $term->slug, $selected['job_sector'], true ) ); ?>>
            <?php echo esc_html( $term->name ); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Certificering (MULTI) -->
    <div class="job_certificering">
      <select name="filter_certificering[]" id="filter_certificering"
              class="js-custom-select job_certificering"
              data-placeholder="Certificering"
              multiple>
        <?php foreach ( get_terms( [ 'taxonomy' => 'certificering', 'hide_empty' => true ] ) as $term ) : ?>
          <option value="<?php echo esc_attr( $term->slug ); ?>"
            <?php selected( in_array( $term->slug, $selected['certificering'], true ) ); ?>>
            <?php echo esc_html( $term->name ); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Organisatie (MULTI) -->
    <div class="job_company">
      <select name="filter_job_company[]" id="filter_job_company"
              class="js-custom-select job_company"
              data-placeholder="Organisatie"
              multiple>
        <?php foreach ( get_terms( [ 'taxonomy' => 'job_company', 'hide_empty' => true ] ) as $term ) : ?>
          <option value="<?php echo esc_attr( $term->slug ); ?>"
            <?php selected( in_array( $term->slug, $selected['job_company'], true ) ); ?>>
            <?php echo esc_html( $term->name ); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

          <div class="filter-actions">
        <button type="button" id="reset-filters" class="reset-filters">
          Reset filters
        </button>
      </div>


  </div>

  <!-- Active filters (chips onder de filters) -->
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

  const kw = document.querySelector("#search_keywords");
  const loc = document.querySelector("#search_location");
  if (kw) kw.addEventListener("input", debounce(wpjmFilter, 250));
  if (loc) loc.addEventListener("input", debounce(wpjmFilter, 250));

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    wpjmFilter();
  });

  const closeAll = () => {
    document.querySelectorAll(".sj-select.active").forEach(el => el.classList.remove("active"));
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
        chip.innerHTML = `
          <span class="active-filter-text"></span>
          <button type="button" class="active-filter-x" aria-label="Verwijder filter">×</button>
        `;
        chip.querySelector(".active-filter-text").textContent = opt.textContent;

        chip.querySelector(".active-filter-x").addEventListener("click", (e) => {
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
    const isMultiple = select.multiple === true;
    const placeholder = select.dataset.placeholder || "Selecteer";

    const wrap = document.createElement("div");
    wrap.className = "sj-select-wrap";
    select.parentNode.insertBefore(wrap, select);
    wrap.appendChild(select);

    select.classList.add("sj-hidden-select");

    const root = document.createElement("div");
    root.className = "sj-select";
    root.dataset.type = isMultiple ? "multi" : "single";

    // Use a div as control (no nested button)
    const btn = document.createElement("div");
    btn.className = "sj-select-btn";
    btn.setAttribute("role", "button");
    btn.setAttribute("tabindex", "0");
    btn.innerHTML = `
      <span class="sj-btn-content">
        <span class="sj-placeholder">${placeholder}</span>
        <span class="sj-tags" aria-hidden="true"></span>
      </span>
      <span class="sj-actions">
        <span class="sj-clear" role="button" aria-label="Wis selectie" title="Wis selectie">×</span>
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
    list.setAttribute("aria-multiselectable", "true");

    const optionRows = [];

    [...select.options].forEach((opt) => {
      if (!opt.value) return; // no empty option

      const row = document.createElement("div");
      row.className = "sj-option";
      row.dataset.value = opt.value;
      row.setAttribute("role", "option");
      row.setAttribute("aria-selected", opt.selected ? "true" : "false");

      row.innerHTML = `<span class="sj-option-text"></span>`;
      row.querySelector(".sj-option-text").textContent = opt.textContent;

      const sync = () => {
        row.classList.toggle("is-selected", opt.selected);
        row.setAttribute("aria-selected", opt.selected ? "true" : "false");
      };

      sync();

      row.addEventListener("click", (e) => {
        e.preventDefault();
        if (opt.disabled) return;
        opt.selected = !opt.selected;

        renderState();
        select.dispatchEvent(new Event("change", { bubbles: true }));
      });

      optionRows.push({ opt, row, sync });
      list.appendChild(row);
    });

    const placeholderEl = btn.querySelector(".sj-placeholder");

    const renderState = () => {
      optionRows.forEach(({ sync }) => sync());
      const selectedOptions = [...select.options].filter(o => o.selected && o.value !== "");

      if (selectedOptions.length === 0) {
        placeholderEl.textContent = placeholder;
        clearBtn.style.display = "none";
      } else {
        placeholderEl.textContent = placeholder; // no chips in button
        clearBtn.style.display = "inline-flex";
      }
    };

    renderState();

    const toggleOpen = (e) => {
      if (e.target.closest(".sj-clear")) return;
      e.preventDefault();
      const wasOpen = root.classList.contains("active");
      closeAll();
      if (!wasOpen) root.classList.add("active");
    };

    btn.addEventListener("click", toggleOpen);
    btn.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === " ") toggleOpen(e);
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


const resetBtn = document.getElementById("reset-filters");

if (resetBtn) {
  resetBtn.addEventListener("click", () => {

    // 1. Leeg zoekvelden
    document.querySelectorAll(
      'input[name="search_keywords"], input[name="search_location"]'
    ).forEach(input => input.value = "");

    // 2. Leeg alle selects
    document.querySelectorAll("select.js-custom-select").forEach(select => {
      [...select.options].forEach(opt => opt.selected = false);
      select.dispatchEvent(new Event("change", { bubbles: true }));
    });

    // 3. Sluit open dropdowns
    document.querySelectorAll(".sj-select.active").forEach(el => {
      el.classList.remove("active");
    });

    // 4. Forceer WPJM refresh
    wpjmFilter();
  });
}

</script>


<style>
/* =========================
   LAYOUT (Sustainablejobs)
   ========================= */
.job_filters {
  width: 90%;
  padding: 20px 0;
  margin: 20px auto;
  background-color: white;
  border: 1px solid #DEDEDE;
  box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
  border-radius: 5px; 
  
}

.filter-box {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  padding: 20px;
}

.filter-box > div {
  flex: 1 1 calc(100% / 4 - 16px) !important;
  min-width: 160px !important;
}

@media (max-width: 768px) {
  .filter-box {
    flex-direction: column;
  }
  .filter-box > div {
    width: 100% !important;
    min-width: 0 !important;
  }
}

/* Header */
.filter-header p {
  font-family: Poppins, sans-serif;
  font-size: 15px;
  color: #333333;
  margin: 10px 0;
}

.filter-header h2 {
  font-family: 'Inter', sans-serif;
  font-size: 25px;
  color: #333333;
  margin-bottom: 15px;
  display: inline;
  background: linear-gradient(transparent 60%, #E0D0E1 60%);
  font-weight: bold;
  border-radius: 2px;
}

body .filter-header a.unstyled-newsletter-link {
  color: #0a6b8d;
  text-decoration: none;
  font-weight: 400;
  font-family: "Poppins", sans-serif;
}
body .filter-header a.unstyled-newsletter-link:hover {
  color: var(--color-roze) !important;
  text-decoration: none;
}

/* Search inputs */
.search-basic {
  display: flex;
  justify-content: left;
  gap: 20px;
  padding: 0 20px;
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
  padding: 12px 14px 12px 38px;
  font-size: 16px;
  border: 1px solid #ccc;
  border-radius: 0;
  background-color: white;
  color: #222;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.search-basic input[type="text"]:focus {
  outline: none;
  border-color: #0a6b8d;
  box-shadow: 0 2px 8px rgba(10, 107, 141, 0.25);
}

.search_keywords::before {
  content: '🔍';
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 16px;
  color: #0a6b8d;
  pointer-events: none;
}

.search_location::before {
  content: '📍';
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 16px;
  color: #0a6b8d;
  pointer-events: none;
}

@media (max-width: 768px) {
  .search-basic {
    flex-direction: column;
  }
  .search_location,
  .search_keywords {
    max-width: 100%;
    flex-basis: 100%;
  }
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

.sj-select-wrap { position: relative; width: 100%; }
.sj-select { position: relative; width: 100%; }

.sj-select-btn {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-radius: 999px;
  border: 1px solid #dedede !important;
  background-color: white !important;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
  padding: 12px 12px;
  min-height: 44px;
  cursor: pointer;
  user-select: none;
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  font-size: 16px;
  color: #111;
}

.sj-btn-content{
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  min-width: 0;
}

.sj-placeholder{
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  color: #333333;
  font-size: 15px;
}

.sj-tags{ display: none; }

.sj-actions{
  display: inline-flex;
  align-items: center;
  gap: 10px;
  margin-left: 10px;
}

.sj-clear{
  display: none; /* in jouw design verbergen we dit in de knop */
  width: 24px;
  height: 24px;
  border-radius: 999px;
  border: 1px solid #E0E0E0;
  background: #fff;
  line-height: 1;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
}

.sj-chev{
  width: 10px;
  height: 10px;
  border-right: 2px solid #111;
  border-bottom: 2px solid #111;
  transform: rotate(45deg);
  transition: transform .2s ease;
}
.sj-select.active .sj-chev{ transform: rotate(-135deg); }

.sj-options{
  display: none;
  position: absolute;
  left: 0;
  right: 0;
  margin-top: 10px;
  background: #fff;
  border: 1px solid #E0E0E0;
  border-radius: 8px;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  padding: 8px;
  max-height: 280px;
  overflow: auto;
  z-index: 9999;
}
.sj-select.active .sj-options{ display: block; }

.sj-option{
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 8px;
  cursor: pointer;
}
.sj-option:hover{ background: #f2f2f2; }

.sj-option.is-selected{ background: #f7fbff; }

.sj-option-text{
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  color: #333;
}

.sj-select-btn:focus{
  outline: none;
  border-color: #0a6b8d;
  box-shadow: 0 0 0 2px rgba(10, 107, 141, 0.2);
}

/* Active filters (chips onder de filter-box) */
.active-filters {
  display: none;
  flex-wrap: wrap;
  gap: 10px;
  margin: 14px 20px 0;
}

.active-filter {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #ffffff;
  border: 1px solid rgba(0,0,0,0.12);
  border-radius: 999px;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  padding: 8px 12px;
  font-size: 16px;
  color: #111;
  font-weight: 700 !important;
  cursor: pointer;

}

.active-filter-text { color: #333333; }

.active-filter-x {
  border: none;
  background: none;
  cursor: move;
  color: #0A6B8D;
  font-size: 20px;
  font-weight: 900;
  margin-left: 6px;
  line-height: 1;
  padding: 0;
}

.active-filter-x:hover { 
  border-color: #0a6b8d; 
  background-color: #0A6B8D;
  font-weight: 700;
  padding: 0 4px;
}


  .filter-actions{

  display: flex;
  
}

.reset-filters{
  border: none; 
  background-color: none; 
  color: #0A6B8D;
  border-radius: 6px;
  padding: 8px 14px;
  font-size: 14px;
  font-family: Poppins, sans-serif;
  font-weight: 400;
  cursor: pointer;
  transition: all .2s ease;
}

.reset-filters:hover{
  background-color: #E0D0E1; 
  color: #0A6B8D;  
  border-radius: 999px;
  border: 1px solid #0A6B8D;
}


</style>