document.addEventListener("DOMContentLoaded", () => {
  const root = document.querySelector('[data-component="vpjobs"]');
  if (!root) return;

  const form = root.querySelector("[data-vpjobs-form]");
  const results = root.querySelector("#vpjobs-results");
  const chipsEl = root.querySelector("[data-vpjobs-chips]");
  if (!form || !results) return;

  const debounce = (fn, delay = 250) => {
    let t;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn(...args), delay);
    };
  };

  const closeAll = () => {
    root.querySelectorAll(".vp-select.active").forEach(el => el.classList.remove("active"));
  };

  const serialize = () => {
    const fd = new FormData(form);
    fd.append("action", "vp_filter_jobs");
    fd.append("nonce", VP_JOBS.nonce);
    return fd;
  };

  const fetchJobs = async () => {
    const fd = serialize();
    const res = await fetch(VP_JOBS.ajaxurl, { method: "POST", body: fd });
    const json = await res.json();
    if (json && json.success && json.data && json.data.html !== undefined) {
      results.innerHTML = json.data.html;
      renderChips();
    }
  };

  const fetchJobsDebounced = debounce(fetchJobs, 250);

  // Search inputs
  form.querySelectorAll('input[name="search_keywords"], input[name="search_location"]').forEach(inp => {
    inp.addEventListener("input", fetchJobsDebounced);
  });

  // Reset
  const resetBtn = root.querySelector("[data-vpjobs-reset]");
  if (resetBtn) {
    resetBtn.addEventListener("click", () => {
      form.querySelectorAll('input[type="text"]').forEach(i => i.value = "");
      form.querySelectorAll("select").forEach(sel => {
        [...sel.options].forEach(o => o.selected = false);
        sel.dispatchEvent(new Event("change", { bubbles: true }));
      });
      closeAll();
      fetchJobs();
    });
  }

  // chips
  const renderChips = () => {
    if (!chipsEl) return;
    chipsEl.innerHTML = "";

    form.querySelectorAll("select").forEach((select) => {
      [...select.options].filter(o => o.selected && o.value).forEach(opt => {
        const chip = document.createElement("span");
        chip.className = "vpjobs-active-filter";
        chip.innerHTML = `<span class="vpjobs-chip-text"></span><button type="button" class="vpjobs-chip-x" aria-label="Verwijder filter">×</button>`;
        chip.querySelector(".vpjobs-chip-text").textContent = opt.textContent;

        chip.querySelector(".vpjobs-chip-x").addEventListener("click", (e) => {
          e.preventDefault();
          opt.selected = false;
          select.dispatchEvent(new Event("change", { bubbles: true }));
        });

        chipsEl.appendChild(chip);
      });
    });

    chipsEl.style.display = chipsEl.children.length ? "flex" : "none";
  };

  // “Custom select” (zelfde principe als Sustainablejobs, maar neutral classes)
  const buildSelect = (select) => {
    const placeholder = select.dataset.placeholder || "Selecteer";
    const wrap = document.createElement("div");
    wrap.className = "vp-select-wrap";
    select.parentNode.insertBefore(wrap, select);
    wrap.appendChild(select);

    select.classList.add("vp-hidden-select");

    const rootSel = document.createElement("div");
    rootSel.className = "vp-select";

    const btn = document.createElement("div");
    btn.className = "vp-select-btn";
    btn.setAttribute("role", "button");
    btn.setAttribute("tabindex", "0");
    btn.innerHTML = `<span class="vp-placeholder">${placeholder}</span><span class="vp-chev"></span>`;

    const list = document.createElement("div");
    list.className = "vp-options";

    const optionRows = [];
    [...select.options].forEach((opt) => {
      if (!opt.value) return;

      const row = document.createElement("div");
      row.className = "vp-option";
      row.dataset.value = opt.value;
      row.textContent = opt.textContent;

      const sync = () => row.classList.toggle("is-selected", opt.selected);
      sync();

      row.addEventListener("click", (e) => {
        e.preventDefault();
        opt.selected = !opt.selected;
        sync();
        select.dispatchEvent(new Event("change", { bubbles: true }));
      });

      optionRows.push({ opt, row, sync });
      list.appendChild(row);
    });

    const toggleOpen = (e) => {
      e.preventDefault();
      const wasOpen = rootSel.classList.contains("active");
      closeAll();
      if (!wasOpen) rootSel.classList.add("active");
    };

    btn.addEventListener("click", toggleOpen);
    btn.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === " ") toggleOpen(e);
      if (e.key === "Escape") closeAll();
    });

    select.addEventListener("change", () => {
      optionRows.forEach(({ sync }) => sync());
      fetchJobs();
    });

    rootSel.appendChild(btn);
    rootSel.appendChild(list);
    wrap.appendChild(rootSel);
  };

  form.querySelectorAll("select.vpjs-select").forEach(buildSelect);
  renderChips();

  document.addEventListener("click", (e) => {
    if (!e.target.closest(".vp-select")) closeAll();
  });
});