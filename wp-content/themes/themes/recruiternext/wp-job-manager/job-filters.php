<?php
if (!defined('ABSPATH')) exit;

wp_enqueue_script('wp-job-manager-ajax-filters');
do_action('job_manager_job_filters_before', $atts);

$selected = [
    'job_company'          => [],
    'organization_type'    => [],
    'job_sector'           => [],
    'job_types'            => [],
    'job_listing_category' => [],
];

$shortcode_atts = shortcode_atts([
    'job_company'          => '',
    'organization_type'    => '',
    'job_sector'           => '',
    'job_listing_type'     => '',
    'job_listing_category' => '',
], $atts);

foreach ($selected as $key => &$value) {
    $shortcode_key = $key === 'job_types' ? 'job_listing_type' : $key;

    $req = srmb_get_req_value($key);
    if (!empty($req)) {
        $value = $req;
    } elseif (!empty($shortcode_atts[$shortcode_key])) {
        $value = array_filter(array_map('trim', explode(',', sanitize_text_field($shortcode_atts[$shortcode_key]))));
    }
}
unset($value);
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Work+Sans:wght@700;800;900&display=swap" rel="stylesheet">

<?php
$hero_attachment = get_page_by_path('recruiter-vacatures-opdrachten-homepage', OBJECT, 'attachment');
$hero_image = $hero_attachment
    ? wp_get_attachment_url($hero_attachment->ID)
    : get_the_post_thumbnail_url(get_the_ID(), 'full');
$job_count = 0;
if (function_exists('get_job_listings')) {
    $job_count_query = get_job_listings([
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
    ]);
    $job_count = isset($job_count_query->found_posts) ? (int) $job_count_query->found_posts : 0;
}
?>
<div class="recruiternext-job-hero"<?php if ($hero_image) : ?> style="background-image: url('<?php echo esc_url($hero_image); ?>');"<?php endif; ?>>
    <div class="recruiternext-job-hero__inner">
        <span class="recruiternext-job-hero__eyebrow">Recruiternext.nl</span>
        <div class="recruiternext-job-hero__content">
            <h1 class="recruiternext-job-hero__title"><span class="recruiternext-job-hero__highlight">Vacaturesite</span> voor <span class="recruiternext-job-hero__highlight">Recruiters</span>, Recruitment Marketeers en HR Professionals.</h1>
<p class="recruiternext-job-hero__subtitle">
                Bekijk alle <a href="#job_listings" class="recruiternext-job-hero__vacatures-link"><span class="recruiternext-hero-job-count recruiternext-job-hero__highlight"><?php echo esc_html(number_format_i18n($job_count)); ?></span> openstaande vacatures</a> of maak een <a href="<?php echo esc_url(home_url('/job-alerts/')); ?>" class="recruiternext-job-hero__link">Vacature Alert</a> aan.
            </p>
        </div>
    </div>
</div>

<form class="job_filters">
    <?php do_action('job_manager_job_filters_start', $atts); ?>

    <div class="filter-row rn-filter-row">
        <?php do_action('job_manager_job_filters_search_jobs_start', $atts); ?>

        <div class="search_keywords">
            <input type="text" name="search_keywords" id="search_keywords" placeholder="Functienaam, sector of onderwerp.." value="<?php echo esc_attr($keywords); ?>" />
        </div>

        <div class="search_location">
            <input type="text" name="search_location" id="search_location" placeholder="Stad of plaats" value="<?php echo esc_attr($location); ?>" />
        </div>

        <?php do_action('job_manager_job_filters_search_jobs_end', $atts); ?>

        <!-- Dienstverband -->
        <div class="job_type">
            <select name="filter_job_types[]"
                    id="filter_job_types"
                    class="js-custom-select job_types"
                    data-placeholder="Dienstverband"
                    multiple>
                <?php foreach (get_job_listing_types() as $type) : ?>
                    <option value="<?php echo esc_attr($type->slug); ?>"
                            data-label="<?php echo esc_attr($type->name); ?>"
                            <?php selected(in_array($type->slug, $selected['job_types'], true)); ?>>
                        <?php echo esc_html($type->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Sector -->
        <div class="job_sector">
            <select name="filter_job_sector[]"
                    id="filter_job_sector"
                    class="js-custom-select job_sector"
                    data-placeholder="Sector"
                    multiple>
                <?php foreach (get_terms(['taxonomy' => 'job_sector', 'hide_empty' => true]) as $term) : ?>
                    <option value="<?php echo esc_attr($term->slug); ?>"
                            data-label="<?php echo esc_attr($term->name); ?>"
                            <?php selected(in_array($term->slug, $selected['job_sector'], true)); ?>>
                        <?php echo esc_html($term->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Organisatie -->
        <div class="job_company">
            <select name="filter_job_company[]"
                    id="filter_job_company"
                    class="js-custom-select job_company"
                    data-placeholder="Organisatie"
                    multiple>
                <?php foreach (get_terms(['taxonomy' => 'job_company', 'hide_empty' => true]) as $term) : ?>
                    <option value="<?php echo esc_attr($term->slug); ?>"
                            data-label="<?php echo esc_attr($term->name); ?>"
                            <?php selected(in_array($term->slug, $selected['job_company'], true)); ?>>
                        <?php echo esc_html($term->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Provincie -->
        <div class="organization_type">
            <select name="filter_organization_type[]"
                    id="filter_organization_type"
                    class="js-custom-select organization_type"
                    data-placeholder="Provincie"
                    multiple>
                <?php foreach (get_terms(['taxonomy' => 'organization_type', 'hide_empty' => true]) as $term) : ?>
                    <option value="<?php echo esc_attr($term->slug); ?>"
                            data-label="<?php echo esc_attr($term->name); ?>"
                            <?php selected(in_array($term->slug, $selected['organization_type'], true)); ?>>
                        <?php echo esc_html($term->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

    </div>

    <!-- Actieve filters -->
    <div class="active-filters rn-active-filters" id="rn-active-filters" aria-live="polite"></div>

</form>

<?php do_action('job_manager_job_filters_after', $atts); ?>

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
  if (kw)  kw.addEventListener("input", debounce(wpjmFilter, 250));
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

  // Actieve filters tonen
  const activeFiltersEl = document.getElementById("rn-active-filters");

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
        chip.tabIndex = 0;
        chip.innerHTML = `<span class="active-filter-text"></span><span class="active-filter-x" aria-hidden="true">×</span>`;
        chip.querySelector(".active-filter-text").textContent = opt.dataset.label || opt.textContent;
        const removeFilter = (e) => {
          e.preventDefault();
          opt.selected = false;
          select.dispatchEvent(new Event("change", { bubbles: true }));
        };
        chip.addEventListener("click", removeFilter);
        chip.addEventListener("keydown", (e) => {
          if (e.key === "Enter" || e.key === " ") removeFilter(e);
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
</script>

<script>
jQuery(function($) {
  $("div.job_listings").on("updated_results", function(event, result) {
    if (result && typeof result.found_count !== "undefined") {
      const foundCount = parseInt(result.found_count, 10);
      $(".recruiternext-hero-job-count").text(Number.isNaN(foundCount) ? "0" : foundCount.toLocaleString("nl-NL"));
    }
  });
});
</script>


<style>
/* Layout – full width breakout uit Elementor container */
.job_filters {
  width: 100vw;
  position: relative;
  left: 50%;
  right: 50%;
  margin-left: -50vw;
  margin-right: -50vw;
  min-height: 300px;
  padding: 60px 0;
  background-color: #FFFACD !important;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
  border-radius: 0;
  border: none;
  box-sizing: border-box;
}

/* Binnenste containers gecentreerd */
.rn-filter-header,
.rn-search-basic,
.rn-filter-box,
.rn-active-filters {
  max-width: 1200px;
  margin-left: auto !important;
  margin-right: auto !important;
}

.rn-filter-box {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  padding: 16px 24px 0;
}

.rn-filter-box > div {
  flex: 0 0 auto !important;
  width: auto !important;
  min-width: 0 !important;
}

@media (max-width: 768px) {
  .rn-filter-box {
    flex-direction: column;
    padding: 0 16px;
    gap: 10px;
  }
  .rn-filter-box > div {
    width: 100% !important;
    min-width: 0 !important;
  }
  .sj-select-wrap,
  .sj-select {
    width: 100% !important;
    display: block !important;
  }
  .sj-select-btn {
    width: 100% !important;
    box-sizing: border-box;
  }
  .sj-panel {
    width: 100% !important;
    min-width: 0 !important;
    left: 0 !important;
    right: 0 !important;
    box-sizing: border-box;
  }
}

/* Header */
.rn-filter-header {
  padding: 20px 24px 16px !important;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
}

.rn-filter-header__left { flex: 1 1 auto; }
.rn-filter-header__right { flex: 0 0 auto; }

.rn-filter-alert-btn {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: #FFFACD;
  color: #0458ab !important;
  border: 2px solid #FFFACD;
  padding: 9px 20px;
  border-radius: 6px;
  font-family: 'Poppins', sans-serif;
  font-size: 14px;
  font-weight: 600;
  text-decoration: none !important;
  white-space: nowrap;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  transition: background .15s ease, color .15s ease, transform .15s ease;
}

.rn-filter-alert-btn:hover {
  background: var(--color-primary-mid, #2f7fd6);
  border-color: var(--color-primary-mid, #2f7fd6);
  color: #034483 !important;
  transform: translateY(-1px);
}

.rn-filter-header p {
  font-family: Poppins;
  font-size: 15px;
  color: rgba(255, 255, 255, 0.85);
  margin: 8px 0 0;
}

.rn-filter-newsletter-link {
  color: #ffffff;
  font-weight: 600;
  text-decoration: underline;
}

.rn-filter-newsletter-link:hover {
  color: #FFFACD;
}

.rn-filter-header h2 {
  font-family: 'Inter', sans-serif;
  font-size: 26px;
  color: #ffffff;
  margin: 0;
  display: block;
  font-weight: 700;
}

/* Zoekvelden */
.rn-search-basic {
  display: flex;
  justify-content: left;
  gap: 16px;
  padding: 0 24px 0;
}

.search_location,
.search_keywords {
  flex-basis: 50%;
  max-width: 50%;
  display: flex;
  align-items: center;
  position: relative;
}

.rn-search-basic input[type="text"] {
  width: 100%;
  padding: 13px 14px 13px 40px;
  font-size: 15px;
  border: 1px solid var(--color-primary, #0458ab);
  border-radius: 6px;
  background-color: #ffffff;
  color: #333;
  box-shadow: none;
  outline: none;
  transition: border-color .2s ease;
  font-family: 'Poppins', sans-serif;
  font-weight: 400;
}

.rn-search-basic input[type="text"]:focus {
  outline: none;
  border-color: var(--color-primary, #0458ab);
  box-shadow: none;
}

.search_keywords input::placeholder,
.search_location input::placeholder {
  color: #999;
  font-size: 15px !important;
  font-style: italic;
}

.search_keywords::before {
  content: '';
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230458ab' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-size: contain;
  pointer-events: none;
}

.search_location::before {
  content: '';
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230458ab' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 2a7 7 0 0 1 7 7c0 5.25-7 13-7 13S5 14.25 5 9a7 7 0 0 1 7-7z'/%3E%3Ccircle cx='12' cy='9' r='2.5'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-size: contain;
  pointer-events: none;
}

@media (max-width: 768px) {
  .rn-search-basic {
    flex-direction: column;
    padding: 0 16px;
    gap: 10px;
  }
  .search_location,
  .search_keywords {
    max-width: 100%;
    flex-basis: 100%;
    width: 100%;
  }
}

/* Custom select */
select.sj-hidden-select {
  position: absolute !important;
  left: -9999px !important;
  width: 1px !important;
  height: 1px !important;
  opacity: 0 !important;
}

.sj-select-wrap { position: relative; width: auto; display: inline-block; }
.sj-select { position: relative; width: auto; display: inline-block; }

.sj-select-btn {
  width: auto;
  display: inline-flex;
  align-items: center;
  justify-content: space-between;
  white-space: nowrap;
  border-radius: 999px;
  border: 1px solid var(--color-primary, #0458ab) !important;
  background-color: white !important;
  padding: 12px 12px;
  min-height: 44px;
  cursor: pointer;
  user-select: none;
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  font-size: 16px;
  color: #111;
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
  color: #333333;
  font-size: 15px;
}

.sj-tags { display: none; gap: 6px; align-items: center; flex-wrap: wrap; }

.sj-actions {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  margin-left: 10px;
}

.sj-clear {
  display: none;
  width: 24px;
  height: 24px;
  border-radius: 999px;
  border: 1px solid var(--color-primary, #0458ab);
  background: #fff;
  line-height: 1;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
}

.sj-chev {
  width: 10px;
  height: 10px;
  border-right: 2px solid #111;
  border-bottom: 2px solid #111;
  transform: rotate(45deg);
  transition: transform .2s ease;
}
.sj-select.active .sj-chev { transform: rotate(-135deg); }

.sj-options {
  display: none;
  position: absolute;
  left: 0;
  right: auto;
  width: max-content;
  min-width: 100%;
  margin-top: 10px;
  background: #fff;
  border: 1px solid var(--color-primary, #0458ab);
  border-radius: 8px;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  padding: 8px;
  max-height: 280px;
  overflow: auto;
  z-index: 9999;
}
.sj-select.active .sj-options { display: block; }

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
  border: 1px solid var(--color-border);
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
  border-color: var(--color-secondary);
  box-shadow: 0 0 0 2px rgba(4, 88, 171, 0.16);
}

.sj-option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 8px;
  cursor: pointer;
}
.sj-option:hover { background: rgba(4, 88, 171, 0.08); }

.sj-option.is-selected .sj-option-text { color: var(--color-primary, #0458ab); font-weight: 700; }

.sj-option-text {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  color: #333;
}

/* Focus ring */
.sj-select-btn:focus {
  outline: none;
  border-color: var(--color-primary, #0458ab);
  box-shadow: 0 0 0 2px rgba(4, 88, 171, 0.2);
}

/* Altijd wit */
.sj-select-btn,
.sj-select-btn:hover,
.sj-select.active .sj-select-btn,
.sj-select-btn:focus {
  background: #ffffff !important;
  color: #111111 !important;
}

/* Verberg clear knop in de dropdown button */
.sj-select-btn .sj-clear { display: none !important; }
.sj-select-btn .sj-actions { gap: 0 !important; }

/* Actieve filters */
.rn-active-filters {
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
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 999px;
  box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
  padding: 8px 12px;
  font-size: 16px;
  color: #111;
  font-weight: 700 !important;
  cursor: pointer;
}

span.active-filter:hover { border-color: var(--color-primary, #0458ab); }

.active-filter-text { color: #333333; }

button.active-filter-x {
  color: var(--color-primary, #0458ab);
  font-weight: 700;
  font-size: 20px;
  margin-left: 6px;
  line-height: 1;
  padding: 0;
  background: none;
  border: none;
  cursor: pointer;
}

button.active-filter-x:hover { color: #034483; background: none; }

/* ---- Filter icoontjes per dropdown ---- */

/* Ruimte voor het icoon links in de button */
.job_type .sj-select-btn,
.job_sector .sj-select-btn,
.job_company .sj-select-btn,
.organization_type .sj-select-btn {
  padding-left: 38px !important;
  position: relative;
}

/* Gedeelde pseudo-element basis */
.job_type .sj-select-btn::before,
.job_sector .sj-select-btn::before,
.job_company .sj-select-btn::before,
.organization_type .sj-select-btn::before {
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
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230458ab' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='2' y='7' width='20' height='14' rx='2'/%3E%3Cpath d='M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2'/%3E%3Cline x1='12' y1='12' x2='12' y2='17'/%3E%3Cline x1='9.5' y1='14.5' x2='14.5' y2='14.5'/%3E%3C/svg%3E");
}

/* Sector → categorie-raster */
.job_sector .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230458ab' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='3' width='7' height='7'/%3E%3Crect x='14' y='3' width='7' height='7'/%3E%3Crect x='3' y='14' width='7' height='7'/%3E%3Crect x='14' y='14' width='7' height='7'/%3E%3C/svg%3E");
}

/* Organisatie → gebouw */
.job_company .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230458ab' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z'/%3E%3Cpolyline points='9 22 9 12 15 12 15 22'/%3E%3C/svg%3E");
}

/* Provincie → kaart/locatie */
.organization_type .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230458ab' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolygon points='1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6'/%3E%3Cline x1='8' y1='2' x2='8' y2='18'/%3E%3Cline x1='16' y1='6' x2='16' y2='22'/%3E%3C/svg%3E");
}

/* Mobile */
@media (max-width: 960px) {
  /* ── Hero ── */
  .recruiternext-job-hero {
    width: 100%;
    left: 0;
    right: auto;
    margin-left: 0;
    margin-right: 0;
    min-height: 260px;
    height: auto;
    padding: 40px 0;
  }

  .recruiternext-job-hero__inner {
    max-width: none;
    padding: 0 16px;
    box-sizing: border-box;
  }

  .recruiternext-job-hero__eyebrow {
    font-size: 11px !important;
    margin-bottom: 10px !important;
  }

  .recruiternext-job-hero__title {
    font-size: clamp(20px, 6vw, 28px) !important;
    line-height: 1.15 !important;
  }

  .recruiternext-job-hero__subtitle {
    font-size: 13px !important;
    font-weight: 600 !important;
    line-height: 1.55 !important;
    margin-top: 10px !important;
  }

  .recruiternext-job-hero__vacatures-link,
  .recruiternext-job-hero__link {
    font-size: 13px !important;
    font-weight: 600 !important;
  }

  /* ── Filterformulier ── */
  form.job_filters {
    width: 100% !important;
    position: relative !important;
    left: 0 !important;
    right: auto !important;
    margin: 0 !important;
    padding: 0 !important;
    min-height: 0 !important;
    box-sizing: border-box;
  }

  form.job_filters .rn-filter-header {
    padding: 14px 16px 10px !important;
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  form.job_filters .rn-filter-header h2 {
    font-size: 18px !important;
    line-height: 1.2 !important;
  }

  form.job_filters .rn-filter-header p {
    font-size: 13px !important;
    margin: 4px 0 0 !important;
  }

  .rn-filter-alert-btn {
    display: none !important;
  }

  form.job_filters .rn-search-basic {
    flex-direction: column !important;
    padding: 0 16px !important;
    gap: 10px !important;
  }

  form.job_filters .rn-filter-row {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
    padding: 16px !important;
    width: 100%;
    box-sizing: border-box;
  }

  form.job_filters .rn-filter-row > div,
  form.job_filters .rn-filter-row .search_keywords,
  form.job_filters .rn-filter-row .search_location {
    flex: 0 0 auto !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box;
  }

  form.job_filters .rn-filter-box {
    flex-direction: column !important;
    padding: 10px 16px 0 !important;
    gap: 10px !important;
  }

  form.job_filters .rn-filter-box > div {
    width: 100% !important;
  }

  form.job_filters .sj-select-wrap,
  form.job_filters .sj-select,
  form.job_filters .sj-select-btn {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box;
  }

  form.job_filters .sj-select-btn {
    min-width: 0;
    white-space: normal;
  }

  form.job_filters .sj-options {
    width: 100% !important;
    min-width: 0 !important;
    left: 0 !important;
    right: 0 !important;
  }

  form.job_filters .rn-active-filters {
    padding: 0 16px 14px !important;
    margin-top: 0 !important;
    box-sizing: border-box;
    flex-wrap: wrap;
    gap: 6px;
  }
}

@media (max-width: 480px) {
  .recruiternext-job-hero {
    padding: 32px 0;
    min-height: 220px;
  }

  .recruiternext-job-hero__title {
    font-size: clamp(18px, 7vw, 24px) !important;
  }

  .recruiternext-job-hero__subtitle {
    font-size: 12px !important;
  }

  .recruiternext-job-hero__vacatures-link,
  .recruiternext-job-hero__link {
    font-size: 12px !important;
  }
}

/* Recruiternext job filters: fondsen-style intro + horizontal filter bar */
.recruiternext-job-hero {
  position: relative;
  width: 100vw;
  left: 50%;
  right: 50%;
  margin-left: -50vw;
  margin-right: -50vw;
  height: 380px;
  background-color: #0458ab;
  background-size: cover;
  background-position: center top;
  overflow: hidden;
  isolation: isolate;
  display: flex;
  align-items: center;
  box-sizing: border-box;
}

.recruiternext-job-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(4, 88, 171, 0.84);
  z-index: 1;
  pointer-events: none;
}

.recruiternext-job-hero__inner {
  position: relative;
  z-index: 2;
  max-width: 1200px;
  width: 100%;
  margin: 0 auto;
  padding: 0 24px;
}

.recruiternext-job-hero__eyebrow {
  display: inline-block;
  margin: 0 0 14px;
  padding-bottom: 6px;
  border-bottom: 3px solid var(--color-secondary, #FFFACD);
  font-family: 'Poppins', sans-serif;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: #ffffff;
}

.recruiternext-job-hero__content {
  max-width: 780px;
}

.recruiternext-job-hero__title {
  font-family: "Work Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
  font-weight: 800 !important;
  font-size: clamp(24px, 3vw, 36px) !important;
  line-height: 1.2 !important;
  color: #ffffff !important;
  margin: 0 !important;
}

.recruiternext-job-hero__description {
  margin: 14px 0 0;
  max-width: 60ch;
  font-family: 'Poppins', sans-serif;
  font-size: 16px;
  font-weight: 400;
  line-height: 1.65;
  color: rgba(255, 255, 255, 0.9);
}

.recruiternext-job-hero__subtitle {
  margin: 12px 0 0;
  font-family: 'Poppins', sans-serif;
  font-size: 16px;
  font-weight: 600;
  color: #ffffff;
}

.recruiternext-job-hero__highlight {
  color: #FFFACD;
}

.recruiternext-job-hero__vacatures-link,
.recruiternext-job-hero__link {
  font-family: 'Poppins', sans-serif !important;
  font-size: 16px !important;
  font-weight: 600 !important;
  color: #ffffff !important;
  text-decoration: none !important;
}

.recruiternext-job-hero__link,
.recruiternext-job-hero__vacatures-link .recruiternext-job-hero__highlight {
  color: #FFFACD !important;
}

.recruiternext-job-hero__vacatures-link:hover,
.recruiternext-job-hero__link:hover {
  color: #ffffff !important;
}

form.job_filters {
  width: 100vw;
  position: relative;
  left: 50%;
  right: 50%;
  margin-left: -50vw;
  margin-right: -50vw;
  margin-top: 0;
  margin-bottom: 40px !important;
  min-height: 100px !important;
  padding: 0 !important;
  background-color: rgba(4, 88, 171, 0.08) !important;
  background: color-mix(in srgb, var(--color-primary, #0458ab) 8%, #ffffff) !important;
  border: none;
  border-bottom: 1px solid var(--color-primary, #0458ab) !important;
  border-bottom-color: var(--color-primary, #0458ab) !important;
  border-radius: 0 !important;
  box-shadow: none !important;
  box-sizing: border-box;
  scroll-margin-top: 90px;
}

.filter-row,
.rn-filter-row,
form.job_filters .active-filters,
form.job_filters .rn-active-filters {
  max-width: 1200px;
  margin-left: auto !important;
  margin-right: auto !important;
}

.filter-row,
.rn-filter-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 28px 24px;
  box-sizing: border-box;
}

.filter-row > div,
.rn-filter-row > div {
  flex: 0 0 auto !important;
  width: auto !important;
  min-width: 0 !important;
  max-width: none !important;
}

.filter-row .search_keywords,
.filter-row .search_location,
.rn-filter-row .search_keywords,
.rn-filter-row .search_location {
  flex: 1 1 0 !important;
  display: flex;
  align-items: center;
  position: relative;
  width: auto !important;
  max-width: none !important;
}

.filter-row input[type="text"],
.rn-filter-row input[type="text"] {
  width: 100%;
  padding: 11px 14px 11px 40px;
  font-size: 15px;
  border: 1px solid var(--color-primary, #0458ab);
  border-radius: 8px;
  background-color: #ffffff;
  color: var(--color-text);
  transition: border-color .2s ease, box-shadow .2s ease;
  font-family: 'Poppins', sans-serif;
  font-weight: 400;
  box-sizing: border-box;
}

.filter-row input[type="text"]:focus,
.rn-filter-row input[type="text"]:focus {
  outline: none;
  border-color: var(--color-primary, #0458ab);
  box-shadow: 0 0 0 3px rgba(4, 88, 171, 0.15);
}

.filter-row .search_keywords input::placeholder,
.filter-row .search_location input::placeholder,
.rn-filter-row .search_keywords input::placeholder,
.rn-filter-row .search_location input::placeholder {
  color: #7c7c7c;
  font-size: 15px !important;
  font-style: italic;
}

.filter-row .search_keywords::before,
.filter-row .search_location::before,
.rn-filter-row .search_keywords::before,
.rn-filter-row .search_location::before {
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

.filter-row .search_keywords::before,
.rn-filter-row .search_keywords::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230458ab' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E");
}

.filter-row .search_location::before,
.rn-filter-row .search_location::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230458ab' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 2a7 7 0 0 1 7 7c0 5.25-7 13-7 13S5 14.25 5 9a7 7 0 0 1 7-7z'/%3E%3Ccircle cx='12' cy='9' r='2.5'/%3E%3C/svg%3E");
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
  border: 1px solid var(--color-primary, #0458ab) !important;
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
  border-color: var(--color-primary, #0458ab) !important;
  box-shadow: 0 0 0 2px rgba(4, 88, 171, 0.18);
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
  border: 1px solid var(--color-primary, #0458ab);
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
  border: 1px solid var(--color-primary, #0458ab);
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
  border-color: var(--color-primary, #0458ab);
  box-shadow: 0 0 0 2px rgba(4, 88, 171, 0.16);
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
  background: rgba(4, 88, 171, 0.08);
  background: color-mix(in srgb, var(--color-primary, #0458ab) 8%, #ffffff);
}

.sj-option.is-selected .sj-option-text,
form.job_filters .sj-option.is-selected .sj-option-text {
  color: var(--color-primary, #0458ab);
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

.sj-option.is-selected .sj-option-count {
  color: var(--color-primary, #0458ab);
}

.job_type .sj-select-btn,
.job_sector .sj-select-btn,
.job_company .sj-select-btn,
.organization_type .sj-select-btn {
  padding-left: 38px !important;
  position: relative;
}

.job_type .sj-select-btn::before,
.job_sector .sj-select-btn::before,
.job_company .sj-select-btn::before,
.organization_type .sj-select-btn::before {
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
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230458ab' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='2' y='7' width='20' height='14' rx='2'/%3E%3Cpath d='M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2'/%3E%3Cline x1='12' y1='12' x2='12' y2='17'/%3E%3Cline x1='9.5' y1='14.5' x2='14.5' y2='14.5'/%3E%3C/svg%3E");
}

.job_sector .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230458ab' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='3' width='7' height='7'/%3E%3Crect x='14' y='3' width='7' height='7'/%3E%3Crect x='3' y='14' width='7' height='7'/%3E%3Crect x='14' y='14' width='7' height='7'/%3E%3C/svg%3E");
}

.job_company .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230458ab' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z'/%3E%3Cpolyline points='9 22 9 12 15 12 15 22'/%3E%3C/svg%3E");
}

.organization_type .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230458ab' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolygon points='1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6'/%3E%3Cline x1='8' y1='2' x2='8' y2='18'/%3E%3Cline x1='16' y1='6' x2='16' y2='22'/%3E%3C/svg%3E");
}

.active-filters,
form.job_filters .rn-active-filters {
  display: none;
  flex-wrap: wrap;
  gap: 10px;
  padding: 0 24px 28px !important;
  margin-top: 0;
}

span.active-filter,
form.job_filters span.active-filter {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #ffffff;
  border: 1px solid var(--color-primary, #0458ab);
  border-radius: 999px;
  box-shadow: 0 10px 24px -18px rgba(4, 88, 171, 0.38);
  padding: 8px 12px;
  font-size: 14px;
  color: var(--color-primary, #0458ab);
  font-weight: 700;
  cursor: pointer;
  transition: background-color .18s ease, border-color .18s ease, transform .18s ease;
}

span.active-filter:hover,
form.job_filters span.active-filter:hover {
  background: rgba(4, 88, 171, 0.08);
  background: color-mix(in srgb, var(--color-primary, #0458ab) 8%, #ffffff);
  border-color: var(--color-primary, #0458ab);
  transform: translateY(-1px);
}

.active-filter-text {
  color: inherit;
  font-weight: 700;
}

.active-filter-x {
  color: var(--color-primary, #0458ab);
  font-weight: 700;
  font-size: 18px;
  line-height: 1;
  margin-left: 4px;
  pointer-events: none;
}

@media (max-width: 960px) {
  form.job_filters {
    width: 100% !important;
    position: relative !important;
    left: 0 !important;
    right: auto !important;
    margin: 0 0 40px !important;
    min-height: 0 !important;
  }

  .filter-row,
  .rn-filter-row {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
    padding: 16px !important;
    width: 100%;
    box-sizing: border-box;
  }

  .filter-row .search_keywords,
  .filter-row .search_location,
  .rn-filter-row .search_keywords,
  .rn-filter-row .search_location {
    flex: none !important;
    width: 100% !important;
  }

  .filter-row > div,
  .rn-filter-row > div,
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

  .active-filters,
  .rn-active-filters {
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

/* Screenshot-style filter controls */
form.job_filters {
  background-color: rgba(4, 88, 171, 0.08) !important;
  background: color-mix(in srgb, var(--color-primary, #0458ab) 8%, #ffffff) !important;
  border-bottom: 1px solid var(--color-primary, #0458ab) !important;
}

form.job_filters .filter-row,
form.job_filters .rn-filter-row {
  gap: 18px;
}

form.job_filters .search_keywords,
form.job_filters .search_location {
  flex: 1 1 230px !important;
}

form.job_filters .filter-row input[type="text"],
form.job_filters .rn-filter-row input[type="text"] {
  min-height: 44px;
  padding: 11px 18px 11px 52px;
  border: 1px solid var(--color-primary, #0458ab) !important;
  border-radius: 8px !important;
  background: #ffffff !important;
  box-shadow: none;
  color: #333333;
  font-size: 15px;
  line-height: 1.3;
}

form.job_filters .filter-row input[type="text"]:focus,
form.job_filters .rn-filter-row input[type="text"]:focus {
  border-color: var(--color-primary, #0458ab) !important;
  box-shadow: 0 0 0 3px rgba(4, 88, 171, 0.15);
}

form.job_filters .search_keywords input::placeholder,
form.job_filters .search_location input::placeholder {
  color: #7c7c7c;
  font-size: 15px !important;
  font-style: italic;
  font-weight: 400;
}

form.job_filters .search_keywords::before,
form.job_filters .search_location::before {
  left: 18px;
  width: 18px;
  height: 18px;
}

form.job_filters .search_keywords::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230458ab' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E") !important;
}

form.job_filters .search_location::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230458ab' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 2a7 7 0 0 1 7 7c0 5.25-7 13-7 13S5 14.25 5 9a7 7 0 0 1 7-7z'/%3E%3Ccircle cx='12' cy='9' r='2.5'/%3E%3C/svg%3E") !important;
}

form.job_filters .sj-select-btn {
  min-height: 44px;
  padding: 11px 18px 11px 52px !important;
  border: 1px solid var(--color-primary, #0458ab) !important;
  border-radius: 999px !important;
  background: #ffffff !important;
  box-shadow: none;
  color: #333333 !important;
  font-size: 16px;
}

form.job_filters .sj-select-btn:focus,
form.job_filters .sj-select.active .sj-select-btn {
  border-color: var(--color-primary, #0458ab) !important;
  box-shadow: 0 0 0 3px rgba(4, 88, 171, 0.15);
}

form.job_filters .sj-placeholder {
  color: #333 !important;
  font-size: 16px;
  font-weight: 800;
}

form.job_filters .sj-actions {
  margin-left: 14px;
}

form.job_filters .sj-chev {
  width: 9px;
  height: 9px;
  border-right: 3px solid #111111;
  border-bottom: 3px solid #111111;
}

form.job_filters .job_type .sj-select-btn::before,
form.job_filters .job_sector .sj-select-btn::before,
form.job_filters .job_company .sj-select-btn::before,
form.job_filters .organization_type .sj-select-btn::before {
  left: 18px;
  width: 18px;
  height: 18px;
}

form.job_filters .job_type .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230458ab' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='2' y='7' width='20' height='14' rx='2'/%3E%3Cpath d='M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2'/%3E%3Cline x1='12' y1='12' x2='12' y2='17'/%3E%3Cline x1='9.5' y1='14.5' x2='14.5' y2='14.5'/%3E%3C/svg%3E") !important;
}

form.job_filters .job_sector .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230458ab' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='3' width='7' height='7'/%3E%3Crect x='14' y='3' width='7' height='7'/%3E%3Crect x='3' y='14' width='7' height='7'/%3E%3Crect x='14' y='14' width='7' height='7'/%3E%3C/svg%3E") !important;
}

form.job_filters .job_company .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230458ab' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z'/%3E%3Cpolyline points='9 22 9 12 15 12 15 22'/%3E%3C/svg%3E") !important;
}

form.job_filters .organization_type .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230458ab' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolygon points='1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6'/%3E%3Cline x1='8' y1='2' x2='8' y2='18'/%3E%3Cline x1='16' y1='6' x2='16' y2='22'/%3E%3C/svg%3E") !important;
}

form.job_filters .sj-options,
form.job_filters .sj-search-input {
  border-color: var(--color-primary, #0458ab);
}

form.job_filters .sj-option:hover {
  background: rgba(4, 88, 171, 0.08);
  background: color-mix(in srgb, var(--color-primary, #0458ab) 8%, #ffffff);
}

form.job_filters .sj-option.is-selected .sj-option-text,
form.job_filters .sj-option.is-selected .sj-option-count {
  color: var(--color-primary, #0458ab);
}

@media (max-width: 960px) {
  form.job_filters .filter-row,
  form.job_filters .rn-filter-row {
    gap: 10px;
  }

  form.job_filters .search_keywords,
  form.job_filters .search_location {
    flex: 0 0 auto !important;
  }
}

</style>
