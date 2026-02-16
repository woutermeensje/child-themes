<?php if (!defined('ABSPATH')) exit; ?>

<div class="filter-container">
  <h1 class="filter-title">Doorzoek alle familieberichten in ons netwerk!</h1>

  <div class="ob-filters">

    <!-- Zoekveld (boven filters) -->
    <div class="ob-field ob-field--search">
      <label for="ob-search">Zoeken</label>
      <input
        id="ob-search"
        type="search"
        name="search_keywords"
        value="<?php echo esc_attr($keywords ?? ''); ?>"
        placeholder="Zoek op naam, tekst, plaats..."
        autocomplete="off"
      >
    </div>

    <!-- Provincie (multiselect) -->
    <div class="ob-field">
      <label for="ob-prov">Provincie</label>
      <select id="ob-prov" name="provincie[]" multiple data-placeholder="Selecteer provincie(s)">
        <?php
          $terms = get_terms(['taxonomy' => OB_Taxonomies::TAX_PROV, 'hide_empty' => false]);
          if (!is_wp_error($terms) && !empty($terms)) :
            foreach ($terms as $t) :
              $is_selected = (!empty($selected['provincie']) && in_array($t->slug, (array)$selected['provincie'], true));
        ?>
              <option value="<?php echo esc_attr($t->slug); ?>" <?php selected($is_selected); ?>>
                <?php echo esc_html($t->name); ?>
              </option>
        <?php
            endforeach;
          endif;
        ?>
      </select>
    </div>

    <!-- Stad (multiselect) -->
    <div class="ob-field">
      <label for="ob-city">Stad</label>
      <select id="ob-city" name="stad[]" multiple data-placeholder="Selecteer stad(en)">
        <?php
          $terms = get_terms(['taxonomy' => OB_Taxonomies::TAX_CITY, 'hide_empty' => false]);
          if (!is_wp_error($terms) && !empty($terms)) :
            foreach ($terms as $t) :
              $is_selected = (!empty($selected['stad']) && in_array($t->slug, (array)$selected['stad'], true));
        ?>
              <option value="<?php echo esc_attr($t->slug); ?>" <?php selected($is_selected); ?>>
                <?php echo esc_html($t->name); ?>
              </option>
        <?php
            endforeach;
          endif;
        ?>
      </select>
    </div>

    <!-- Type bericht (multiselect) -->
    <div class="ob-field">
      <label for="ob-type">Type bericht</label>
      <select id="ob-type" name="type[]" multiple data-placeholder="Selecteer type(s)">
        <?php
          $terms = get_terms(['taxonomy' => OB_Taxonomies::TAX_TYPE, 'hide_empty' => false]);
          if (!is_wp_error($terms) && !empty($terms)) :
            foreach ($terms as $t) :
              $is_selected = (!empty($selected['type']) && in_array($t->slug, (array)$selected['type'], true));
        ?>
              <option value="<?php echo esc_attr($t->slug); ?>" <?php selected($is_selected); ?>>
                <?php echo esc_html($t->name); ?>
              </option>
        <?php
            endforeach;
          endif;
        ?>
      </select>
    </div>

    <div class="ob-actions">
      <button type="button" class="ob-btn ob-btn--secondary" data-ob-reset>Reset</button>
    </div>

  </div>
</div>

<style>
  /* Container */
  .filter-container{
    max-width:1080px;
    margin:24px auto;
    border:1px solid #DEDEDE;
    box-shadow:0px 10px 40px -5px rgba(0,0,0,0.15);
    padding:24px;
    border-radius:16px;
    background:#fff;
  }
  .filter-title{
    font-size:20px;
    font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    font-weight:700;
    color:#333;
    margin:0 0 16px;
  }

  /* Layout filters */
  .ob-filters{
    display:grid;
    grid-template-columns: repeat(3, minmax(220px, 1fr));
    gap:16px;
    align-items:end;
  }
  .ob-field{display:flex;flex-direction:column;}
  .ob-field label{font-size:14px;font-weight:600;margin-bottom:6px;color:#222;}

  /* Search full width */
  .ob-field--search{grid-column:1 / -1;}
  .ob-field input[type="search"]{
    width:100%;
    padding:8px;
    border:1px solid #D7DEE7;
    border-radius:5px;
    font-size:15px;
  }
  .ob-field input[type="search"]:focus{
    outline:none;
    border-color:#AFC0D5;
    box-shadow: 0 0 0 4px rgba(25,118,210,0.08);
  }

  /* Multiselect - override theme button styling */
  .ob-filters .vms{position:relative;width:100%;}
  .ob-filters .vms__control{
    all:unset;
    box-sizing:border-box;
    width:100%;
    min-height:54px;
    display:flex;
    align-items:center;
    gap:10px;
    padding:8px;
    border:1px solid #D7DEE7;
    border-radius:5px;
    background:#fff;
    cursor:pointer;
    font: inherit;
  }


  .ob-filters .vms__control:hover{border-color:#B9C6D6;}
  .ob-filters .vms__control[aria-expanded="true"]{border-color:#AFC0D5;}

  /* hide tag UI (looks like normal select) */
  .ob-filters .vms__tags{display:none;}
  .ob-filters .vms__placeholder{display:block;color:#111;font-size:16px;}
  .ob-filters .vms__caret{margin-left:auto;font-size:18px;opacity:.8;}

  .ob-filters .vms__dropdown{
    position:absolute;
    z-index:9999;
    left:0; right:0;
    margin-top:8px;
    border:1px solid #E6ECF3;
    border-radius:14px;
    background:#fff;
    box-shadow:0 18px 45px rgba(0,0,0,0.12);
    overflow:hidden;
  }
  .ob-filters .vms__actions{
    display:flex;
    gap:10px;
    align-items:center;
    padding:12px;
    border-bottom:1px solid #EEF2F6;
  }
  .ob-filters .vms__search{
    flex:1;
    min-height:44px;
    padding:10px 12px;
    border:1px solid #D7DEE7;
    border-radius:12px;
    font:inherit;
    font-size:14px;
  }
  .ob-filters .vms__search:focus{
    outline:none;
    border-color:#AFC0D5;
    box-shadow: 0 0 0 3px rgba(25,118,210,0.08);
  }
  .ob-filters .vms__clear{
    all:unset;
    box-sizing:border-box;
    padding:10px 14px;
    border-radius:12px;
    border:2px solid #D7DEE7;
    background:#fff;
    cursor:pointer;
    font:inherit;
    font-size:14px;
  }
  .ob-filters .vms__clear:hover{background:#F6F8FB;}

  .ob-filters .vms__list{
    max-height:340px;
    overflow:auto;
    padding:8px;
  }
  .ob-filters .vms__item{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    padding:14px 14px;
    border-radius:12px;
    cursor:pointer;
    user-select:none;
    font-size:18px;
    font-weight:600;
  }
  .ob-filters .vms__item input[type="checkbox"]{display:none;}
  .ob-filters .vms__item:hover{background:#F4F7FB;}
  .ob-filters .vms__item.is-selected::after{content:"✓";font-size:18px;opacity:.85;}
  .ob-filters .vms__noresults{padding:14px 14px;color:#667085;font-size:14px;}

  /* Reset button */
  .ob-actions{display:flex;align-items:flex-end;}
  .ob-btn{padding:12px 16px;border-radius:12px;border:1px solid #111;background:#111;color:#fff;font-size:14px;cursor:pointer;}
  .ob-btn--secondary{background:#fff;color:#111;border:2px solid #D7DEE7;}
  .ob-btn--secondary:hover{background:#F6F8FB;}

  @media (max-width:1024px){ .ob-filters{grid-template-columns:repeat(2,1fr);} }
  @media (max-width:768px){ .ob-filters{grid-template-columns:1fr;} .ob-field--search{grid-column:auto;} }
</style>

<script>
  // Vanilla multi-select with search inside dropdown
  function initVanillaMultiSelect(selectEl) {
    if (!selectEl || selectEl.dataset.vmsInit === "1") return;
    selectEl.dataset.vmsInit = "1";

    // Hide original select but keep it in DOM for FormData()
    selectEl.style.position = "absolute";
    selectEl.style.left = "-9999px";
    selectEl.style.width = "1px";
    selectEl.style.height = "1px";
    selectEl.style.opacity = "0";

    const wrapper = document.createElement("div");
    wrapper.className = "vms";

    const control = document.createElement("button");
    control.type = "button";
    control.className = "vms__control";
    control.setAttribute("aria-haspopup", "listbox");
    control.setAttribute("aria-expanded", "false");

    const tags = document.createElement("div");
    tags.className = "vms__tags";

    const placeholder = document.createElement("span");
    placeholder.className = "vms__placeholder";
    placeholder.textContent = selectEl.getAttribute("data-placeholder") || "Selecteer…";

    const caret = document.createElement("span");
    caret.className = "vms__caret";
    caret.textContent = "▾";

    const dropdown = document.createElement("div");
    dropdown.className = "vms__dropdown";
    dropdown.hidden = true;

    const actions = document.createElement("div");
    actions.className = "vms__actions";

    // 🔎 Search inside dropdown
    const search = document.createElement("input");
    search.type = "search";
    search.className = "vms__search";
    search.placeholder = "Zoek…";
    search.autocomplete = "off";

    const clearBtn = document.createElement("button");
    clearBtn.type = "button";
    clearBtn.className = "vms__clear";
    clearBtn.textContent = "Alles wissen";

    actions.appendChild(search);
    actions.appendChild(clearBtn);

    const list = document.createElement("div");
    list.className = "vms__list";
    list.setAttribute("role", "listbox");

    dropdown.appendChild(actions);
    dropdown.appendChild(list);

    control.appendChild(tags);
    control.appendChild(placeholder);
    control.appendChild(caret);

    // Insert UI after select
    selectEl.parentNode.insertBefore(wrapper, selectEl.nextSibling);
    wrapper.appendChild(selectEl);
    wrapper.appendChild(control);
    wrapper.appendChild(dropdown);

    const options = Array.from(selectEl.options).filter(o => o.value !== "");

    function renderList() {
      list.innerHTML = "";

      options.forEach(opt => {
        const item = document.createElement("label");
        item.className = "vms__item" + (opt.selected ? " is-selected" : "");

        const cb = document.createElement("input");
        cb.type = "checkbox";
        cb.checked = opt.selected;

        const text = document.createElement("span");
        text.className = "vms__text";
        text.textContent = opt.textContent;

        cb.addEventListener("change", () => {
          opt.selected = cb.checked;
          item.classList.toggle("is-selected", cb.checked);
          renderTags();
          applySearchFilter();
          selectEl.dispatchEvent(new Event("change", { bubbles: true }));
        });

        // Clicking row toggles checkbox
        item.addEventListener("click", (e) => {
          if (e.target === cb) return;
          cb.checked = !cb.checked;
          cb.dispatchEvent(new Event("change", { bubbles: false }));
        });

        item.appendChild(cb);
        item.appendChild(text);
        list.appendChild(item);
      });
    }

    function renderTags() {
      // we hide tags via CSS, but we still keep placeholder behavior
      const selectedOpts = options.filter(o => o.selected);
      placeholder.textContent = selectedOpts.length
        ? `${selectedOpts.length} geselecteerd`
        : (selectEl.getAttribute("data-placeholder") || "Selecteer…");
    }

    function applySearchFilter() {
      const q = (search.value || "").trim().toLowerCase();
      let visibleCount = 0;

      Array.from(list.children).forEach((row) => {
        const label = row.querySelector(".vms__text")?.textContent?.toLowerCase() || "";
        const show = q === "" || label.includes(q);
        row.style.display = show ? "" : "none";
        if (show) visibleCount++;
      });

      let no = dropdown.querySelector(".vms__noresults");
      if (!no) {
        no = document.createElement("div");
        no.className = "vms__noresults";
        no.textContent = "Geen resultaten";
        dropdown.appendChild(no);
      }
      no.style.display = visibleCount === 0 ? "" : "none";
    }

    function open() {
      dropdown.hidden = false;
      control.setAttribute("aria-expanded", "true");
      search.value = "";
      applySearchFilter();
      setTimeout(() => search.focus(), 0);
    }

    function close() {
      dropdown.hidden = true;
      control.setAttribute("aria-expanded", "false");
    }

    control.addEventListener("click", () => {
      dropdown.hidden ? open() : close();
    });

    clearBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      options.forEach(o => (o.selected = false));
      renderList();
      renderTags();
      applySearchFilter();
      selectEl.dispatchEvent(new Event("change", { bubbles: true }));
    });

    search.addEventListener("input", applySearchFilter);

    document.addEventListener("click", (e) => {
      if (!wrapper.contains(e.target)) close();
    });

    // init
    renderList();
    renderTags();
    applySearchFilter();
  }

  document.addEventListener("DOMContentLoaded", function () {
    // Init multiselect UI
    document.querySelectorAll(".ob-filters select[multiple]").forEach(function (sel) {
      initVanillaMultiSelect(sel);
    });

    // AJAX refresh (requires wrapper + form)
    const root = document.querySelector('[data-component="obberichten"]');
    if (!root || typeof OB === "undefined") return;

    const form = root.querySelector('[data-ob-form]');
    const results = root.querySelector('#ob-results');
    const resetBtn = root.querySelector('[data-ob-reset]');
    const searchInput = form ? form.querySelector('input[name="search_keywords"]') : null;

    if (!form || !results) return;

    const debounce = (fn, delay = 300) => {
      let t;
      return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), delay);
      };
    };

    const setPage = (page) => {
      const pageInput = form.querySelector('input[name="paged"]');
      if (pageInput) pageInput.value = String(page);
    };

    const bindPagination = () => {
      const pager = results.querySelector('[data-ob-pagination]');
      if (!pager) return;

      pager.querySelectorAll('[data-ob-page]').forEach(btn => {
        btn.addEventListener('click', () => {
          const page = parseInt(btn.getAttribute('data-ob-page') || "1", 10);
          setPage(page);
          fetchListings();
        });
      });
    };

    const fetchListings = async () => {
      const fd = new FormData(form);
      fd.append('action', 'ob_filter');

      try {
        const res = await fetch(OB.ajaxurl, { method: 'POST', body: fd });
        const json = await res.json();

        if (json && json.success && json.data && typeof json.data.html === "string") {
          results.innerHTML = json.data.html;
          bindPagination();
        }
      } catch (e) {
        console.error("OB AJAX error:", e);
      }
    };

    // On any select change (includes multiselect dispatchEvent) => refresh
    form.addEventListener('change', (e) => {
      if (e.target && e.target.matches('select')) {
        setPage(1);
        fetchListings();
      }
    });

    // Live search in results list
    if (searchInput) {
      searchInput.addEventListener('input', debounce(() => {
        setPage(1);
        fetchListings();
      }, 350));
    }

    // Reset
    if (resetBtn) {
      resetBtn.addEventListener('click', () => {
        form.reset();
        setPage(1);
        fetchListings();
      });
    }

    bindPagination();
  });
</script>
