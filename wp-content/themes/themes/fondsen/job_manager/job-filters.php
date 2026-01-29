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
        <h2>Alle Vacatures</h2>
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
    document.querySelectorAll(".sj-select.active").forEach(el => el.classList.remove("active"));
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
      if (!wasOpen) root.classList.add("active");
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
/* =========================
   LAYOUT (jouw bestaande)
   ========================= */
.job_filters {
  width: 95%;
  padding: 20px 0;
  margin: 20px auto;
  background-color: #FBFAF8;
  box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
  border-radius: 6px;
  border: 1px solid #E0E0E0;
}

.filter-box {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  padding: 20px;
}

.filter-box > div {
  flex: 1 1 calc(100% / 5 - 16px) !important;
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
  font-family: Poppins;
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
  font-weight: bold;
  border-radius: 2px;
}

body .filter-header a.unstyled-newsletter-link {
  color: #0884CC;
  text-decoration: none;
  font-weight: 400;
  font-family: "Poppins", sans-serif;
}
body .filter-header a.unstyled-newsletter-link:hover {
  color: #0884CC !important;
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
  border: 1px solid #dedede;
  border-radius: 0;
  background-color: white;
  color: #222;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
  font-family: 'Poppins', sans-serif;
  font-weight: 400;

}

.search-basic input[type="text"]:focus {
  outline: none;
  border-color: #0a6b8d;
  box-shadow: 0 2px 8px rgba(10, 107, 141, 0.25);
}

.search_keywords input::placeholder,
.search_location input::placeholder {
    color: #333;
    font-size: 16px !important;
    font-style: italic;
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
   CUSTOM SELECT (YouTube stijl)
   ========================= */

/* verberg echte selects, maar behoud ze voor WPJM */
select.sj-hidden-select {
  position: absolute !important;
  left: -9999px !important;
  width: 1px !important;
  height: 1px !important;
  opacity: 0 !important;
}

/* wrapper */
.sj-select-wrap { position: relative; width: 100%; }

.sj-select { position: relative; width: 100%; }

/* button */
.sj-select-btn {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;

  border-radius: 6px;
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
  font-weight: 400;
  color: #333333;
  font-size: 15px;
}

.sj-tags{
  display: none;
  gap: 6px;
  align-items: center;
  flex-wrap: wrap;
}

.sj-actions{
  display: inline-flex;
  align-items: center;
  gap: 10px;
  margin-left: 10px;
}

/* clear button */
.sj-clear{
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

/* chevron */
.sj-chev{
  width: 10px;
  height: 10px;
  border-right: 2px solid #111;
  border-bottom: 2px solid #111;
  transform: rotate(45deg);
  transition: transform .2s ease;
}
.sj-select.active .sj-chev{ transform: rotate(-135deg); }

/* dropdown */
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

.sj-option-check{
  width: 18px;
  height: 18px;
  border: 1px solid #D0D5DD;
  border-radius: 4px;
  display: inline-block;
  position: relative;
  flex: 0 0 auto;
}
.sj-option.is-selected .sj-option-check{
  border-color: #0a6b8d;
}
.sj-option.is-selected .sj-option-check::after{
  content: "";
  position: absolute;
  left: 5px;
  top: 2px;
  width: 5px;
  height: 10px;
  border-right: 2px solid #0a6b8d;
  border-bottom: 2px solid #0a6b8d;
  transform: rotate(45deg);
}

.sj-option-text{
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  color: #333;
}

/* chips */
.sj-chip{
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: 1px solid #E0E0E0;
  background: #fff;
  border-radius: 999px;
  padding: 6px 10px;
  font-size: 13px;
  font-weight: 700;
}
.sj-chip-x{
  border: none;
  background: transparent;
  cursor: pointer;
  font-weight: 900;
  line-height: 1;
  font-size: 14px;
}

/* focus ring */
.sj-select-btn:focus{
  outline: none;
  border-color: #0a6b8d;
  box-shadow: 0 0 0 2px rgba(10, 107, 141, 0.2);
}


/* FORCEER YouTube look: altijd wit */
.sj-select-btn {
  background: #ffffff !important;
  color: #111111 !important;
}

/* Ook bij hover / focus / active */
.sj-select-btn:hover,
.sj-select.active .sj-select-btn,
.sj-select-btn:focus {
  background: #ffffff !important;
  color: #111111 !important;
}

/* Actieve filters onder de filter-box */
.active-filters {
  display: none; /* wordt automatisch 'flex' als er iets geselecteerd is */
  flex-wrap: wrap;
  gap: 8px;
  margin: 12px 20px 0;
}

.active-filter {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 10px;
  background: #e9f2ff;
  border-radius: 20px;
  font-size: 14px;
}

.active-filter-x {
  border: none;
  background: none;
  cursor: pointer;
  font-size: 16px;
  line-height: 1;
}

/* ❌ Verberg het kruisje (clear) in de filter zelf */
.sj-select-btn .sj-clear {
  display: none !important;
}

/* Optioneel: haal extra spacing weg die door de clear-knop ontstond */
.sj-select-btn .sj-actions {
  gap: 0 !important;
}

/* Container onderaan */
.active-filters {
  margin: 14px 20px 0;
  gap: 10px;
}

/* Chip styling */
.active-filter {
  background: #ffffff;                 /* wit */
  border: 1px solid #d7e6ff;           /* zachte blauwe lijn */
  border-radius: 999px;
  padding: 8px 12px;
  font-size: 14px;
  box-shadow: none;
}

/* Tekst */
.active-filter-text {
  color: #333333;
}

/* Kruisje in chip (onderaan) */
.active-filter-x {
  color: #d0002d;                      /* beetje rood zoals je huidige */
  font-size: 20px;
  font-weight: 600;
  margin-left: 6px;
  line-height: 1;
  padding: 0;
}

/* Hover */
.active-filter:hover {
  border-color: #0884CC;               /* Fondsen blauw */
}



</style>


<style>


/* Individuele filter-chip */
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

button.active-filter-x {
  color: #0884CC; 
  font-weight: 700; 
  background: none; 
}

button.active-filter-x:hover {
  color: #0884CC; 
  font-weight: 700; 
  background: none; 
}

/* =========================
   MOBILE: filter net zo breed als de job cards
   ========================= */
@media (max-width: 960px) {

  /* Zelfde horizontale marge/gevoel als jouw job listings */
  form.job_filters{
    width: 100% !important;
    margin: 12px auto !important;
    padding: 0 !important; /* we sturen padding per blok */
    box-sizing: border-box;
  }

  /* Inhoud binnenin dezelfde "gutter" als listings (12px) */
  form.job_filters .filter-header{
    padding: 0 12px 10px 12px !important;
  }

  form.job_filters .search-basic{
    padding: 0 12px !important;
  }

  form.job_filters .filter-box{
    padding: 12px !important;
  }

  /* Active filters chips ook uitlijnen */
  form.job_filters .active-filters{
    margin: 12px 12px 0 !important;
  }
}


/*
</style>
