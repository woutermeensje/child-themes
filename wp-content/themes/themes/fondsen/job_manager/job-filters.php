<?php
if (!defined('ABSPATH')) exit;

wp_enqueue_script('wp-job-manager-ajax-filters');
do_action('job_manager_job_filters_before', $atts);

// ✅ Vul $selected met waarden vanuit: $_GET > $_POST > Shortcode
$selected = [
    'job_company'   => [],
    'organization_type' => [],
    'job_sector'    => [],
    'job_types'     => [],
    'job_listing_category' => [],
];

$shortcode_atts = shortcode_atts([
    'job_company' => '',
    'organization_type' => '',
    'job_sector' => '',
    'job_listing_type' => '',
    'job_listing_category' => '',
], $atts);

// helper: haal value uit request (support zowel key als filter_key)
// hier heb ik dus iets verwijderd. 




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
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<form class="job_filters">
    <?php do_action('job_manager_job_filters_start', $atts); ?>

    <div class="filter-header" style="padding: 0 20px 10px 20px;">
        <h2>Vacaturesite en impact platform voor non-profits!</h2>
        <p>Of schrijf je in voor de <a href="https://www.fondsen.org/nieuwsbrief/" target="_blank" class="unstyled-newsletter-link">vacature nieuwsbrief</a>!</p>
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

        <!-- Dienstverband (single) -->
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


        <!-- Sector (multi) -->
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

        <!-- Organisatie (single) -->
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


        <!-- Organization Type (multi) -->
        <div class="organization_type">
            <select name="filter_organization_type[]"
                    id="filter_organization_type"
                    class="js-custom-select organization_type"
                    data-placeholder="Type organisatie"
                    multiple>
                <?php foreach (get_terms(['taxonomy' => 'organization_type', 'hide_empty' => true]) as $term) : ?>
                    <option value="<?php echo esc_attr($term->slug); ?>" <?php selected(in_array($term->slug, $selected['organization_type'], true)); ?>>
                        <?php echo esc_html($term->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

    </div>

    <!-- ✅ Actieve filters tonen onder de filter-box -->
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
      console.warn("job_manager_job_filters.filter_jobs() niet gevonden.");
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

  // ✅ Active filters (chips onder de filters)
  const activeFiltersEl = document.getElementById("active-filters");

  const renderActiveFilters = () => {
    if (!activeFiltersEl) return;

    activeFiltersEl.innerHTML = "";

    const selects = document.querySelectorAll("select.js-custom-select");
    selects.forEach((select) => {
      const selectedOptions = [...select.options].filter(o => o.selected && o.value !== "");

      selectedOptions.forEach((opt) => {
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
    const isMultiple = select.multiple === true;
    const forceMode = select.dataset.mode;
    const isSingle = forceMode === "single" ? true : !isMultiple;

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

    const tagsEl = btn.querySelector(".sj-tags");
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

      // ✅ MULTI: géén chips in de filter-knop tonen
placeholderEl.textContent = placeholder;  // bv "Sector" / "Type organisatie"
placeholderEl.style.display = "inline";
tagsEl.style.display = "none";
tagsEl.innerHTML = "";
return;

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
      renderActiveFilters(); // ✅ update actieve filters onderaan
      wpjmFilter();
    });

    root.appendChild(btn);
    root.appendChild(list);
    wrap.appendChild(root);
  };

  document.querySelectorAll("select.js-custom-select").forEach(buildSelect);

  // ✅ initial render
  renderActiveFilters();

  document.addEventListener("click", (e) => {
    if (!e.target.closest(".sj-select")) closeAll();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeAll();
  });
});
</script>





<style>
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
  box-shadow: none;
  border: none;
  border-bottom: 1px solid #f5d9b8;
  border-radius: 0;
  box-sizing: border-box;
}

.filter-header,
.search-basic,
.filter-box,
.active-filters {
  max-width: 1200px;
  margin-left: auto !important;
  margin-right: auto !important;
}

.filter-header {
  padding: 0 24px 18px !important;
}

.filter-header h2 {
  margin: 0;
  font-family: 'Inter', sans-serif;
  font-size: 28px;
  line-height: 1.1;
  font-weight: 700;
  color: #333333;
}

.filter-header p {
  margin: 10px 0 0;
  font-family: 'Poppins', sans-serif;
  font-size: 15px;
  color: #333333;
}

body .filter-header a.unstyled-newsletter-link {
  color: #333333;
  font-weight: 400;
  text-decoration: underline;
  font-family: 'Poppins', sans-serif;
}

body .filter-header a.unstyled-newsletter-link:hover {
  color: var(--color-secondary) !important;
  text-decoration: underline;
}

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
  border: 1px solid #f5d9b8;
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
  border-color: var(--color-secondary);
  box-shadow: 0 0 0 3px rgba(8, 132, 204, 0.18);
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
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23055D92' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E");
}

.search_location::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23055D92' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 2a7 7 0 0 1 7 7c0 5.25-7 13-7 13S5 14.25 5 9a7 7 0 0 1 7-7z'/%3E%3Ccircle cx='12' cy='9' r='2.5'/%3E%3C/svg%3E");
}

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
  border: 1px solid var(--color-border) !important;
  background-color: #ffffff !important;
  padding: 12px;
  min-height: 44px;
  cursor: pointer;
  user-select: none;
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  font-size: 16px;
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

.sj-options {
  display: none;
  position: absolute;
  left: 0;
  right: auto;
  width: max-content;
  min-width: 100%;
  margin-top: 10px;
  background: #fff;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
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
  box-shadow: 0 0 0 2px rgba(8, 132, 204, 0.16);
}

.sj-option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 8px;
  cursor: pointer;
}

.sj-option:hover {
  background: #f8f8f8;
}

.sj-option.is-selected .sj-option-text {
  color: var(--color-secondary);
  font-weight: 700;
}

.sj-option-text {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  color: var(--color-text);
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
  width: 16px;
  height: 16px;
  background-repeat: no-repeat;
  background-size: contain;
  pointer-events: none;
  flex-shrink: 0;
}

.job_type .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23055D92' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='2' y='7' width='20' height='14' rx='2'/%3E%3Cpath d='M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2'/%3E%3Cline x1='12' y1='12' x2='12' y2='17'/%3E%3Cline x1='9.5' y1='14.5' x2='14.5' y2='14.5'/%3E%3C/svg%3E");
}

.job_sector .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23055D92' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='3' width='7' height='7'/%3E%3Crect x='14' y='3' width='7' height='7'/%3E%3Crect x='3' y='14' width='7' height='7'/%3E%3Crect x='14' y='14' width='7' height='7'/%3E%3C/svg%3E");
}

.job_company .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23055D92' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z'/%3E%3Cpolyline points='9 22 9 12 15 12 15 22'/%3E%3C/svg%3E");
}

.organization_type .sj-select-btn::before {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23055D92' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolygon points='1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6'/%3E%3Cline x1='8' y1='2' x2='8' y2='18'/%3E%3Cline x1='16' y1='6' x2='16' y2='22'/%3E%3C/svg%3E");
}

.sj-select-btn:focus {
  outline: none;
  border-color: var(--color-secondary);
  box-shadow: 0 0 0 2px rgba(8, 132, 204, 0.18);
}

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

.active-filter:hover {
  border-color: var(--color-secondary);
}

.active-filter-text {
  color: var(--color-text);
}

button.active-filter-x {
  color: var(--color-secondary);
  font-weight: 700;
  font-size: 20px;
  line-height: 1;
  margin-left: 6px;
  padding: 0;
  background: none;
  border: none;
  cursor: pointer;
}

button.active-filter-x:hover {
  color: var(--color-tertiary);
  background: none;
}

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
