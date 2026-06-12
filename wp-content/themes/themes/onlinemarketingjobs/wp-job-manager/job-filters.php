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
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<form class="job_filters">
    <?php do_action('job_manager_job_filters_start', $atts); ?>

    <div class="filter-header">
        <div class="filter-header__left">
            <h2>Bekijk alle Online Marketing Vacatures</h2>
            <p>Ontvang de laatste <a href="/nieuwsbrief/" class="filter-newsletter-link">online marketing vacatures</a> in de e-mail!</p>
        </div>
    </div>

    <div class="search-basic">
        <?php do_action('job_manager_job_filters_search_jobs_start', $atts); ?>

        <div class="search_keywords">
            <input type="text" name="search_keywords" id="search_keywords" placeholder="Functienaam, sector of onderwerp.." value="<?php echo esc_attr($keywords); ?>" />
        </div>

        <div class="search_location">
            <input type="text" name="search_location" id="search_location" placeholder="Stad of plaats" value="<?php echo esc_attr($location); ?>" />
        </div>

        <?php do_action('job_manager_job_filters_search_jobs_end', $atts); ?>
    </div>

    <div class="filter-box">

        <!-- Dienstverband -->
        <div class="job_type">
            <select name="filter_job_types[]"
                    id="filter_job_types"
                    class="js-custom-select job_types"
                    data-placeholder="Dienstverband"
                    multiple>
                <?php foreach (get_job_listing_types() as $type) : ?>
                    <option value="<?php echo esc_attr($type->slug); ?>" <?php selected(in_array($type->slug, $selected['job_types'], true)); ?>>
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
                    <option value="<?php echo esc_attr($term->slug); ?>" <?php selected(in_array($term->slug, $selected['job_sector'], true)); ?>>
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
                    <option value="<?php echo esc_attr($term->slug); ?>" <?php selected(in_array($term->slug, $selected['job_company'], true)); ?>>
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
                    <option value="<?php echo esc_attr($term->slug); ?>" <?php selected(in_array($term->slug, $selected['organization_type'], true)); ?>>
                        <?php echo esc_html($term->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

    </div>

    <!-- Actieve filters -->
    <div class="active-filters" id="active-filters" aria-live="polite"></div>

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
  const activeFiltersEl = document.getElementById("active-filters");

  const renderActiveFilters = () => {
    if (!activeFiltersEl) return;
    activeFiltersEl.innerHTML = "";

    document.querySelectorAll("select.js-custom-select").forEach((select) => {
      [...select.options].filter(o => o.selected && o.value !== "").forEach((opt) => {
        const chip = document.createElement("span");
        chip.className = "active-filter";
        chip.innerHTML = `<span class="active-filter-text"></span><button type="button" class="active-filter-x" aria-label="Verwijder filter">×</button>`;
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
      row.innerHTML = `<span class="sj-option-text"></span>`;
      row.querySelector(".sj-option-text").textContent = opt.textContent;

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
        const matches = opt.textContent.toLowerCase().includes(term);
        row.style.display = matches ? "" : "none";
      });
    };

    if (searchInput) {
      searchInput.addEventListener("input", filterOptionRows);
      searchInput.addEventListener("click", (e) => e.stopPropagation());
      searchInput.addEventListener("keydown", (e) => e.stopPropagation());
    }

    const tagsEl       = btn.querySelector(".sj-tags");
    const placeholderEl = btn.querySelector(".sj-placeholder");

    const renderState = () => {
      optionRows.forEach(({ opt, syncSelected }) => syncSelected());

      const selectedOptions = [...select.options].filter(o => o.selected && o.value !== "");
      tagsEl.innerHTML = "";

      if (selectedOptions.length === 0) {
        placeholderEl.style.display = "inline";
        tagsEl.style.display = "none";
        clearBtn.style.display = "none";
        return;
      }

      clearBtn.style.display = "inline-flex";

      if (isSingle) {
        placeholderEl.textContent = selectedOptions[0].textContent;
        placeholderEl.style.display = "inline";
        tagsEl.style.display = "none";
        return;
      }

      // Multi: toon alleen de placeholder, actieve filters onderaan
      placeholderEl.textContent = placeholder;
      placeholderEl.style.display = "inline";
      tagsEl.style.display = "none";
      tagsEl.innerHTML = "";
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
      $(".omj-listings-count__number").text(result.found_count);
    }
  });
});
</script>


<style>
/* Layout – full width breakout uit Elementor container */
.job_filters,
form.job_filters {
  display: block;
  float: none;
  clear: both;
  width: 100vw;
  position: relative;
  left: 50%;
  right: 50%;
  top: auto;
  transform: none;
  margin-top: 0px !important;
  margin-left: -50vw;
  margin-right: -50vw;
  margin-bottom: 0 !important;
  min-height: 300px;
  padding: 60px 0;
  background-color: var(--color-primary);
  box-shadow: none;
  border-radius: 0;
  border: none;
  border-bottom: 1px solid var(--color-primary-dk);
  box-sizing: border-box;
}

div.job_listings,
div.job_listings .showing_jobs,
div.job_listings ul.job_listings {
  margin-top: 0 !important;
}

div.job_listings {
  padding-top: 0 !important;
}

.omj-listings-header {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 24px 14px;
}

.omj-listings-count {
  font-family: 'Poppins', sans-serif;
  font-size: 14px;
  font-weight: 300;
  color: var(--color-text-muted, #777);
  display: block;
}

.omj-listings-count__number {
  font-weight: 600;
  color: var(--color-primary);
}

@media (max-width: 960px) {
  .omj-listings-header {
    padding: 0 16px 12px;
  }
}

/* Binnenste containers gecentreerd */
.filter-header,
.search-basic,
.filter-box,
.active-filters {
  max-width: 1200px;
  margin-left: auto !important;
  margin-right: auto !important;
}

.filter-box {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  padding: 16px 24px 0;
}

.filter-box > div {
  flex: 0 0 auto !important;
  width: auto !important;
  min-width: 0 !important;
}

@media (max-width: 768px) {
  .filter-box {
    flex-direction: column;
    padding: 0 16px;
    gap: 10px;
  }
  .filter-box > div {
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
.filter-header {
  padding: 20px 24px 16px !important;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
}

.filter-header__left { flex: 1 1 auto; }
.filter-header__right { flex: 0 0 auto; }

.filter-alert-btn {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: var(--color-secondary);
  color: var(--color-primary) !important;
  border: 2px solid var(--color-secondary);
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

.filter-alert-btn:hover {
  background: var(--color-tertiary);
  border-color: var(--color-tertiary);
  color: #ffffff !important;
  transform: translateY(-1px);
}

.filter-header p {
  font-family: Poppins;
  font-size: 15px;
  color: #ffffff;
  margin: 8px 0 0;
}

.filter-newsletter-link,
.filter-newsletter-link:link,
.filter-newsletter-link:visited {
  color: #ffffff !important;
  font-weight: 400;
  text-decoration: underline !important;
}

.filter-newsletter-link:hover,
.filter-newsletter-link:focus {
  color: rgba(255, 255, 255, 0.75) !important;
  text-decoration: none !important;
}

.filter-header h2 {
  font-family: 'Inter', sans-serif;
  font-size: 24px;
  color: #ffffff;
  margin: 0;
  display: block;
  font-weight: 700;
}

/* Zoekvelden */
.search-basic {
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

.search-basic input[type="text"] {
  width: 100%;
  padding: 13px 14px 13px 40px;
  font-size: 15px;
  border: 1px solid #ffffff;
  border-radius: 6px;
  background-color: #ffffff;
  color: #333;
  box-shadow: none;
  transition: border-color .2s ease, box-shadow .2s ease;
  font-family: 'Poppins', sans-serif;
  font-weight: 400;
}

.search-basic input[type="text"]:focus {
  outline: none;
  border-color: #ffffff;
  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.35);
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
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%237C5CFA' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E");
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
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%237C5CFA' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 2a7 7 0 0 1 7 7c0 5.25-7 13-7 13S5 14.25 5 9a7 7 0 0 1 7-7z'/%3E%3Ccircle cx='12' cy='9' r='2.5'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-size: contain;
  pointer-events: none;
}

@media (max-width: 768px) {
  .search-basic {
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
  border: 1px solid #E0E0E0 !important;
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
  border: 1px solid #E0E0E0;
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
  border: 1px solid #E0E0E0;
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
  box-shadow: 0 0 0 2px rgba(124, 92, 250, 0.16);
}

.sj-option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 8px;
  cursor: pointer;
}
.sj-option:hover { background: #f2f2f2; }

.sj-option.is-selected .sj-option-text { color: var(--color-primary); font-weight: 700; }

.sj-option-text {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  color: #333;
}

/* Focus ring */
.sj-select-btn:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 2px rgba(124, 92, 250, 0.2);
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
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 999px;
  box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
  padding: 8px 12px;
  font-size: 16px;
  color: #111;
  font-weight: 700 !important;
  cursor: pointer;
}

span.active-filter:hover { border-color: var(--color-primary); }

.active-filter-text { color: #333333; }

button.active-filter-x {
  color: var(--color-primary);
  font-weight: 700;
  font-size: 20px;
  margin-left: 6px;
  line-height: 1;
  padding: 0;
  background: none;
  border: none;
  cursor: pointer;
}

button.active-filter-x:hover { color: var(--color-tertiary); background: none; }

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
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%237C5CFA' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='2' y='7' width='20' height='14' rx='2'/%3E%3Cpath d='M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2'/%3E%3Cline x1='12' y1='12' x2='12' y2='17'/%3E%3Cline x1='9.5' y1='14.5' x2='14.5' y2='14.5'/%3E%3C/svg%3E");
}

/* Sector → categorie-raster */
.job_sector .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%237C5CFA' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='3' width='7' height='7'/%3E%3Crect x='14' y='3' width='7' height='7'/%3E%3Crect x='3' y='14' width='7' height='7'/%3E%3Crect x='14' y='14' width='7' height='7'/%3E%3C/svg%3E");
}

/* Organisatie → gebouw */
.job_company .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%237C5CFA' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z'/%3E%3Cpolyline points='9 22 9 12 15 12 15 22'/%3E%3C/svg%3E");
}

/* Provincie → kaart/locatie */
.organization_type .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%237C5CFA' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolygon points='1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6'/%3E%3Cline x1='8' y1='2' x2='8' y2='18'/%3E%3Cline x1='16' y1='6' x2='16' y2='22'/%3E%3C/svg%3E");
}

/* Mobile */
@media (max-width: 960px) {
  form.job_filters {
    /* Reset de desktop breakout – voorkomt verschuiving */
    width: 100% !important;
    position: relative !important;
    left: 0 !important;
    right: auto !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
    padding: 16px 0 20px !important;
    box-sizing: border-box;
  }

  form.job_filters .filter-header {
    padding: 0 16px 12px 16px !important;
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  form.job_filters .filter-header h2 {
    font-size: 20px !important;
    line-height: 1.2 !important;
  }
  form.job_filters .filter-header p {
    font-size: 13px !important;
  }
  .filter-alert-btn {
    display: none;
  }
  form.job_filters .search-basic {
    padding: 0 16px !important;
    gap: 10px !important;
  }
  form.job_filters .filter-box {
    padding: 10px 16px 0 !important;
    gap: 10px !important;
  }
  form.job_filters .active-filters {
    margin: 10px 16px 0 !important;
    flex-wrap: wrap;
    gap: 6px;
  }
}
</style>
