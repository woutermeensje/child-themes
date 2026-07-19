<?php
if ( ! defined( 'ABSPATH' ) ) exit;

wp_enqueue_script( 'wp-job-manager-ajax-filters' );
do_action( 'job_manager_job_filters_before', $atts );

$selected = [
  'job_company'      => [],
  'job_sector'       => [],
  'job_types'        => [],
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
    if ( ! empty( $_GET[ $key ] ) )         return (array) $_GET[ $key ];
    if ( ! empty( $_GET[ $filter_key ] ) )  return (array) $_GET[ $filter_key ];
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
    if ( isset( $counts_by_taxonomy[ $taxonomy ] ) ) return $counts_by_taxonomy[ $taxonomy ];
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
       LEFT JOIN {$wpdb->postmeta} filled  ON filled.post_id  = p.ID AND filled.meta_key  = '_filled'      AND filled.meta_value  = '1'
       LEFT JOIN {$wpdb->postmeta} expires ON expires.post_id = p.ID AND expires.meta_key = '_job_expires'
       WHERE tt.taxonomy = %s
         AND p.post_type   = 'job_listing'
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
    if ( empty( $terms ) || is_wp_error( $terms ) ) return [];
    $terms = array_values( (array) $terms );
    usort( $terms, function ( $a, $b ) use ( $counts ) {
      $count_a = $counts[ (int) $a->term_id ] ?? 0;
      $count_b = $counts[ (int) $b->term_id ] ?? 0;
      if ( $count_a === $count_b ) return strcasecmp( $a->name, $b->name );
      return $count_b <=> $count_a;
    } );
    return $terms;
  }
}

$job_type_counts         = sj_get_open_job_filter_counts( 'job_listing_type' );
$job_sector_counts       = sj_get_open_job_filter_counts( 'job_sector' );
$organisatie_type_counts = sj_get_open_job_filter_counts( 'organisatie_type' );
$job_company_counts      = sj_get_open_job_filter_counts( 'job_company' );

if ( ! function_exists( 'sj_get_named_upload_image_url' ) ) {
  function sj_get_named_upload_image_url( $basename ) {
    $basename = sanitize_title( $basename );
    if ( ! $basename ) return '';

    $attachment = get_page_by_path( $basename, OBJECT, 'attachment' );
    if ( $attachment instanceof WP_Post ) {
      $url = wp_get_attachment_image_url( $attachment->ID, 'full' );
      if ( $url ) return $url;
    }

    global $wpdb;
    $like_with_dir = '%/' . $wpdb->esc_like( $basename . '.' ) . '%';
    $like_filename = $wpdb->esc_like( $basename . '.' ) . '%';
    $attachment_id = (int) $wpdb->get_var(
      $wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta}
         WHERE meta_key = '_wp_attached_file'
           AND (meta_value LIKE %s OR meta_value LIKE %s)
         ORDER BY post_id DESC
         LIMIT 1",
        $like_with_dir,
        $like_filename
      )
    );

    if ( $attachment_id ) {
      $url = wp_get_attachment_image_url( $attachment_id, 'full' );
      if ( $url ) return $url;
    }

    $upload_dir = wp_get_upload_dir();
    if ( ! empty( $upload_dir['error'] ) || empty( $upload_dir['basedir'] ) || empty( $upload_dir['baseurl'] ) ) return '';

    foreach ( [ 'jpg', 'jpeg', 'png', 'webp' ] as $extension ) {
      $relative_path = '2026/06/' . $basename . '.' . $extension;
      $absolute_path = trailingslashit( $upload_dir['basedir'] ) . $relative_path;
      if ( file_exists( $absolute_path ) ) {
        return trailingslashit( $upload_dir['baseurl'] ) . $relative_path;
      }
    }

    return '';
  }
}

$hero_image = sj_get_named_upload_image_url( 'sustainablejobs-nl-duurzame-vacaturesite' );
$hero_count_args = [
  'search_keywords' => is_scalar( $keywords ) ? sanitize_text_field( wp_unslash( (string) $keywords ) ) : '',
  'search_location' => is_scalar( $location ) ? sanitize_text_field( wp_unslash( (string) $location ) ) : '',
];
$hero_job_count = function_exists( 'sj_get_open_job_listing_count' ) ? sj_get_open_job_listing_count( $hero_count_args ) : 0;
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Work+Sans:wght@700;800;900&display=swap" rel="stylesheet">

<div class="sj-job-hero"<?php if ( $hero_image ) : ?> style="background-image: url('<?php echo esc_url( $hero_image ); ?>');"<?php endif; ?>>
    <div class="sj-job-hero__inner">
        <div class="sj-job-hero__pane sj-job-hero__pane--content">
            <div class="sj-job-hero__card">
                <div class="sj-job-hero__title-wrap">
                    <h1 class="sj-job-hero__title">De #1 Duurzame Vacaturebank van Nederland!</h1>
                </div>
                <p class="sj-job-hero__subtitle">Vacatures, opdachten, stages, vrijwilligerswerk en traineeships binnen de duurzaamheid, energietransitie, ecologie en banen met een positieve impact. Bekijk alle <a href="#job_listings" class="sj-job-hero__vacatures-link"><span class="sj-hero-job-count sj-job-hero__accent"><?php echo esc_html( number_format_i18n( $hero_job_count ) ); ?></span> vacatures</a> of meld je direct aan voor onze <a href="/nieuwsbrief/" class="sj-job-hero__link">vacature nieuwsbrief</a>.</p>
                <div class="sj-job-hero__actions" aria-label="Snel naar">
                    <a class="sj-job-hero__button sj-job-hero__button--primary" href="https://sustainablejobs.nl/job-alerts/">Job alerts</a>
                    <a class="sj-job-hero__button sj-job-hero__button--secondary" href="https://sustainablejobs.nl/tarieven/">Tarieven</a>
                </div>
            </div>
        </div>
        <div class="sj-job-hero__pane sj-job-hero__pane--visual" aria-hidden="true"></div>
    </div>
</div>

<form class="job_filters">
  <?php do_action( 'job_manager_job_filters_start', $atts ); ?>

  <div class="filter-row">
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

    <!-- Dienstverband -->
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

    <!-- Sector -->
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

    <!-- Type organisatie -->
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

    <!-- Organisatie -->
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

  <!-- Actieve filters -->
  <div class="active-filters" id="active-filters" aria-live="polite"></div>

</form>

<?php do_action( 'job_manager_job_filters_after', $atts ); ?>

<script>
if ('scrollRestoration' in history) {
  history.scrollRestoration = 'manual';
}
window.addEventListener('pageshow', function(e) {
  if (e.persisted) window.scrollTo(0, 0);
});

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

jQuery(function($) {
  const updateHeroJobCount = (count) => {
    const parsed = parseInt(String(count).replace(/[^\d]/g, ""), 10);
    if (Number.isNaN(parsed)) return;

    $(".sj-hero-job-count").text(new Intl.NumberFormat(document.documentElement.lang || "nl-NL").format(parsed));
  };

  $("div.job_listings").on("updated_results", function(event, result) {
    if (result && typeof result.found_count !== "undefined") {
      updateHeroJobCount(result.found_count);
    } else if (result && result.found_jobs === false) {
      updateHeroJobCount(0);
    }
  });
});

// Scroll fix voor taxonomy-pagina's
(function () {
  var parts = window.location.pathname.replace(/\/+$/, "").split("/").filter(Boolean);
  if (parts.length < 2) return;

  var corrected  = false;
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

  window.addEventListener("load", function () {
    setTimeout(scrollToFilter, 1000);
  });
}());
</script>

<style>
/* ── Hero section ── */
.sj-job-hero {
  position: relative;
  width: 100vw;
  left: 50%;
  right: 50%;
  margin-left: -50vw;
  margin-right: -50vw;
  height: 400px;
  background-color: #254F6E;
  background-size: cover;
  background-position: center;
  overflow: hidden;
  isolation: isolate;
  display: flex;
  align-items: center;
  box-sizing: border-box;
}

.sj-job-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(37, 79, 110, 0.18);
  z-index: 1;
  pointer-events: none;
}

.sj-job-hero__inner {
  position: relative;
  z-index: 2;
  width: min(100%, 1200px);
  margin: 0 auto;
  padding: 0;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  align-items: center;
  box-sizing: border-box;
}

.sj-job-hero__pane {
  min-width: 0;
  box-sizing: border-box;
}

.sj-job-hero__card {
  width: calc(100% - 48px);
  margin: 24px;
  padding: 24px;
  background: #ffffff;
  border: 1px solid #168AAD;
  border-radius: 5px;
  box-shadow: 0 18px 45px rgba(13, 35, 45, 0.16);
  box-sizing: border-box;
}

.sj-job-hero__title-wrap {
  margin: 0 0 18px;
  max-width: 100%;
}

.sj-job-hero__title {
  font-family: "Work Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
  font-weight: 800 !important;
  font-size: 24px !important;
  line-height: 1.2 !important;
  color: #27323A !important;
  margin: 0 !important;
}

.sj-job-hero__actions {
  display: flex !important;
  flex-wrap: wrap !important;
  align-items: center !important;
  gap: 10px !important;
  margin: 28px 0 0 !important;
}

.sj-job-hero__button {
  flex: 1 1 0 !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  min-height: 42px !important;
  padding: 14px 28px !important;
  border: 1.5px solid transparent !important;
  border-radius: 5px !important;
  font-family: 'Work Sans', sans-serif !important;
  font-size: 17px !important;
  font-weight: 700 !important;
  line-height: 1.2 !important;
  letter-spacing: 0 !important;
  text-align: center !important;
  text-decoration: none !important;
  text-transform: none !important;
  white-space: nowrap !important;
  box-shadow: none !important;
  transition: background-color .15s ease, border-color .15s ease, color .15s ease !important;
}

.sj-job-hero__button--primary {
  background: #168AAD !important;
  border-color: #168AAD !important;
  color: #ffffff !important;
}

.sj-job-hero__button--primary:hover,
.sj-job-hero__button--primary:focus {
  background: #0F789A !important;
  border-color: #0F789A !important;
  color: #ffffff !important;
}

.sj-job-hero__button--secondary {
  background: #9BCB86 !important;
  border-color: #9BCB86 !important;
  color: #ffffff !important;
}

.sj-job-hero__button--secondary:hover,
.sj-job-hero__button--secondary:focus {
  background: #86BA72 !important;
  border-color: #86BA72 !important;
  color: #ffffff !important;
}

.sj-job-hero__subtitle {
  margin: 0 !important;
  font-family: 'Poppins', sans-serif !important;
  font-size: 15px !important;
  font-weight: 400 !important;
  line-height: 1.5 !important;
  letter-spacing: 0 !important;
  color: #3C4650 !important;
}

.sj-job-hero__accent {
  color: #168AAD !important;
}

.sj-job-hero__subtitle .sj-job-hero__vacatures-link,
.sj-job-hero__subtitle .sj-job-hero__link {
  font-family: 'Poppins', sans-serif !important;
  font-size: inherit !important;
  font-weight: 600 !important;
  line-height: inherit !important;
  letter-spacing: 0 !important;
  text-transform: none !important;
}

.sj-job-hero__subtitle .sj-job-hero__vacatures-link {
  color: #168AAD !important;
  text-decoration: none !important;
}

.sj-job-hero__subtitle .sj-job-hero__vacatures-link:hover {
  color: #0F789A !important;
}

.sj-job-hero__subtitle .sj-job-hero__vacatures-link .sj-job-hero__accent {
  font-family: inherit !important;
  font-size: inherit !important;
  font-weight: inherit !important;
  line-height: inherit !important;
  color: #168AAD !important;
}

.sj-job-hero__subtitle .sj-job-hero__link {
  color: #168AAD !important;
  text-decoration: none !important;
}

.sj-job-hero__subtitle .sj-job-hero__link:hover {
  color: #0F789A !important;
}

@media (max-width: 960px) {
  .sj-job-hero {
    height: auto;
    min-height: 360px;
    left: 0;
    right: auto;
    margin-left: 0;
    margin-right: 0;
    width: 100%;
    padding: 28px 0;
  }

  .sj-job-hero__inner {
    width: 100%;
    grid-template-columns: 1fr;
    padding: 0;
    box-sizing: border-box;
  }

  .sj-job-hero__pane--visual {
    display: none;
  }

  .sj-job-hero__card {
    width: calc(100% - 48px);
    padding: 24px;
  }

  .sj-job-hero__title-wrap {
    width: 100%;
    max-width: 100%;
    margin-bottom: 14px;
  }

  .sj-job-hero__title {
    font-size: clamp(20px, 5vw, 28px) !important;
    text-wrap: balance;
  }

  .sj-job-hero__actions {
    gap: 8px !important;
    margin-top: 22px !important;
  }

  .sj-job-hero__button {
    flex: 1 1 150px !important;
    min-height: 40px !important;
    padding: 12px 20px !important;
    font-size: 17px !important;
  }

  .sj-job-hero__subtitle {
    max-width: 100%;
    font-size: 14px !important;
    line-height: 1.45 !important;
  }
}

/* ── Filter bar ── */
.job_filters {
  width: 100vw;
  position: relative;
  left: 50%;
  right: 50%;
  margin-left: -50vw;
  margin-right: -50vw;
  margin-top: 0;
  margin-bottom: 40px !important;
  min-height: 100px;
  background: var(--color-bg-filter);
  border-bottom: 1px solid var(--color-border);
  box-sizing: border-box;
  scroll-margin-top: 90px;
}

.filter-row,
.active-filters {
  max-width: 1200px;
  margin-left: auto !important;
  margin-right: auto !important;
}

.filter-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 28px 24px;
}

.search_keywords,
.search_location {
  flex: 1;
  display: flex;
  align-items: center;
  position: relative;
}

.filter-row input[type="text"] {
  width: 100%;
  padding: 11px 14px 11px 40px;
  font-size: 15px;
  border: 1px solid #DDE8C5;
  border-radius: 8px;
  background-color: #ffffff;
  color: var(--color-text);
  transition: border-color .2s ease, box-shadow .2s ease;
  font-family: 'Poppins', sans-serif;
  font-weight: 400;
  box-sizing: border-box;
}

.filter-row input[type="text"]:focus {
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
  width: 17px;
  height: 17px;
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

.filter-row > div {
  flex: 0 0 auto;
  min-width: 0;
}

/* ── Custom select ── */
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

.sj-select-btn:focus,
.sj-select.active .sj-select-btn {
  outline: none;
  border-color: var(--color-primary) !important;
  box-shadow: 0 0 0 2px rgba(22, 138, 173, 0.18);
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
  font-size: 14px;
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
  width: 9px;
  height: 9px;
  border-right: 2px solid #111;
  border-bottom: 2px solid #111;
  transform: rotate(45deg);
  transition: transform .2s ease;
}

.sj-select.active .sj-chev {
  transform: rotate(-135deg);
}

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
  font-size: 13px;
  color: var(--color-text);
  background: #ffffff;
  box-sizing: border-box;
}

.sj-search-input::placeholder { color: #7c7c7c; }

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

.sj-option:hover { background: var(--color-bg-filter); }

.sj-option.is-selected .sj-option-text {
  color: var(--color-primary);
  font-weight: 700;
}

.sj-option-text {
  min-width: 0;
  overflow-wrap: anywhere;
  font-family: 'Poppins', sans-serif;
  font-size: 15px;
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

.sj-option.is-selected .sj-option-count { color: var(--color-primary); }

/* Filter knop iconen */
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
  width: 15px;
  height: 15px;
  background-repeat: no-repeat;
  background-size: contain;
  pointer-events: none;
  flex-shrink: 0;
}

.job_type .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23168AAD' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='2' y='7' width='20' height='14' rx='2'/%3E%3Cpath d='M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2'/%3E%3Cline x1='12' y1='12' x2='12' y2='17'/%3E%3Cline x1='9.5' y1='14.5' x2='14.5' y2='14.5'/%3E%3C/svg%3E");
}

.job_sector .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23168AAD' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='3' width='7' height='7'/%3E%3Crect x='14' y='3' width='7' height='7'/%3E%3Crect x='3' y='14' width='7' height='7'/%3E%3Crect x='14' y='14' width='7' height='7'/%3E%3C/svg%3E");
}

.job_organisatie_type .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23168AAD' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='8' r='6'/%3E%3Cpath d='M15.477 12.89L17 22l-5-3-5 3 1.523-9.11'/%3E%3C/svg%3E");
}

.job_company .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23168AAD' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z'/%3E%3Cpolyline points='9 22 9 12 15 12 15 22'/%3E%3C/svg%3E");
}

/* Actieve filters */
.active-filters {
  display: none;
  flex-wrap: wrap;
  gap: 10px;
  padding: 0 24px 28px;
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
  transition: background-color .18s ease, border-color .18s ease, transform .18s ease;
}

.active-filter:hover {
  background: var(--color-secondary-soft);
  transform: translateY(-1px);
}

.active-filter-text { color: inherit; font-weight: 700; }

.active-filter-x {
  color: var(--color-chip-company-text);
  font-weight: 700;
  font-size: 18px;
  line-height: 1;
  margin-left: 4px;
  pointer-events: none;
}

/* Responsive */
@media (max-width: 960px) {
  form.job_filters {
    width: 100% !important;
    position: relative !important;
    left: 0 !important;
    right: auto !important;
    margin: 0 0 40px !important;
    min-height: 0 !important;
  }

  .filter-row {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
    padding: 16px !important;
    width: 100%;
    box-sizing: border-box;
  }

  .search_keywords,
  .search_location {
    flex: none;
    width: 100%;
  }

  .filter-row > div,
  .sj-select-wrap,
  .sj-select,
  .sj-select-btn {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box;
  }

  .sj-select-btn {
    min-width: 0;
    white-space: normal;
  }

  .sj-btn-content {
    flex: 1 1 auto;
  }

  .sj-placeholder {
    white-space: normal;
  }

  .sj-options {
    width: 100% !important;
    min-width: 0 !important;
    left: 0 !important;
    right: 0 !important;
  }

  .active-filters {
    padding: 0 16px 16px !important;
    box-sizing: border-box;
  }

  span.active-filter {
    max-width: 100%;
    box-sizing: border-box;
  }

  .active-filter-text {
    min-width: 0;
    overflow-wrap: anywhere;
  }
}
</style>
