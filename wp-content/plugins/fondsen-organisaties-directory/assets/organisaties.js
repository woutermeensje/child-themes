(() => {
  const form = document.getElementById("orgFilterForm");
  if (!form) return;

  const delay = (window.FondsenOrgDir && FondsenOrgDir.autoSubmitDelay) ? FondsenOrgDir.autoSubmitDelay : 350;

  // Debounce submit (zoekveld)
  let t = null;
  const debounceSubmit = () => {
    window.clearTimeout(t);
    t = window.setTimeout(() => form.submit(), delay);
  };

  const searchInput = form.querySelector("#org_search");
  if (searchInput) {
    searchInput.addEventListener("input", debounceSubmit);
  }

  // Helpers
  const qs  = (root, sel) => root.querySelector(sel);
  const qsa = (root, sel) => Array.from(root.querySelectorAll(sel));

  const multiselects = qsa(form, ".js-ms");

  function closeAll(except) {
    multiselects.forEach(ms => {
      if (ms === except) return;
      ms.classList.remove("is-open");
      const panel = qs(ms, ".js-ms-panel");
      const control = qs(ms, ".js-ms-control");
      if (panel) panel.hidden = true;
      if (control) control.setAttribute("aria-expanded", "false");
      const search = qs(ms, ".js-ms-search");
      if (search) search.value = "";
      filterOptions(ms, "");
    });
  }

  function filterOptions(ms, term) {
    const val = (term || "").trim().toLowerCase();
    qsa(ms, ".ms__option").forEach(opt => {
      const label = opt.getAttribute("data-label") || "";
      opt.style.display = label.includes(val) ? "" : "none";
    });
  }

  function syncHiddenInputs(ms) {
    const name = ms.getAttribute("data-name");
    if (!name) return;

    // Remove existing hidden inputs
    qsa(ms, `input[type="hidden"][name="${name}[]"]`).forEach(n => n.remove());

    // Add new hidden inputs for checked
    qsa(ms, `.ms__option input[type="checkbox"]:checked`).forEach(cb => {
      const hidden = document.createElement("input");
      hidden.type = "hidden";
      hidden.name = `${name}[]`;
      hidden.value = cb.value;
      ms.appendChild(hidden);
    });
  }

  function renderChips(ms) {
    const chipsWrap = qs(ms, ".js-ms-chips");
    const placeholder = qs(ms, ".js-ms-placeholder");
    const placeholderText = ms.getAttribute("data-placeholder") || "Kies opties";

    if (!chipsWrap || !placeholder) return;

    chipsWrap.innerHTML = "";

    const checked = qsa(ms, `.ms__option input[type="checkbox"]:checked`).map(cb => {
      const text = cb.closest(".ms__option")?.querySelector("span")?.textContent?.trim() || cb.value;
      return { value: cb.value, text };
    });

    if (checked.length === 0) {
      placeholder.style.display = "";
      placeholder.textContent = placeholderText;
      return;
    }

    placeholder.style.display = "none";

    checked.forEach(item => {
      const chip = document.createElement("span");
      chip.className = "ms__chip";
      chip.innerHTML = `<span>${escapeHtml(item.text)}</span>`;

      const btn = document.createElement("button");
      btn.type = "button";
      btn.setAttribute("aria-label", "Verwijder");
      btn.textContent = "×";
      btn.addEventListener("click", (e) => {
        e.stopPropagation();
        const cb = qs(ms, `.ms__option input[type="checkbox"][value="${cssEscape(item.value)}"]`);
        if (cb) cb.checked = false;

        syncHiddenInputs(ms);
        renderChips(ms);
        form.submit();
      });

      chip.appendChild(btn);
      chipsWrap.appendChild(chip);
    });
  }

  function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, s => ({
      "&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"
    }[s]));
  }

  function cssEscape(val) {
    // Minimal escape for attribute selector
    return String(val).replace(/"/g, '\\"');
  }

  // Init + events
  multiselects.forEach(ms => {
    const control = qs(ms, ".js-ms-control");
    const panel   = qs(ms, ".js-ms-panel");
    const search  = qs(ms, ".js-ms-search");
    const clearBtn= qs(ms, ".js-ms-clear");

    if (!control || !panel) return;

    // Init from existing hidden inputs (preselected) -> set checkbox state
    const name = ms.getAttribute("data-name");
    if (name) {
      const preset = qsa(ms, `input[type="hidden"][name="${name}[]"]`).map(i => i.value);
      preset.forEach(v => {
        const cb = qs(ms, `.ms__option input[type="checkbox"][value="${cssEscape(v)}"]`);
        if (cb) cb.checked = true;
      });
    }

    syncHiddenInputs(ms);
    renderChips(ms);

    function open() {
      closeAll(ms);
      ms.classList.add("is-open");
      panel.hidden = false;
      control.setAttribute("aria-expanded", "true");
      if (search) {
        search.value = "";
        filterOptions(ms, "");
        setTimeout(() => search.focus(), 0);
      }
    }

    function close() {
      ms.classList.remove("is-open");
      panel.hidden = true;
      control.setAttribute("aria-expanded", "false");
      if (search) {
        search.value = "";
        filterOptions(ms, "");
      }
    }

    control.addEventListener("click", (e) => {
      e.preventDefault();
      const isOpen = ms.classList.contains("is-open");
      if (isOpen) close(); else open();
    });

    control.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        const isOpen = ms.classList.contains("is-open");
        if (isOpen) close(); else open();
      }
      if (e.key === "Escape") {
        close();
      }
    });

    panel.addEventListener("click", (e) => {
      // voorkomen dat click inside dropdown sluit via document handler
      e.stopPropagation();
    });

    // Checkbox change -> sync + chips + submit
    qsa(ms, `.ms__option input[type="checkbox"]`).forEach(cb => {
      cb.addEventListener("change", () => {
        syncHiddenInputs(ms);
        renderChips(ms);
        form.submit();
      });
    });

    // Search inside options
    if (search) {
      search.addEventListener("input", () => filterOptions(ms, search.value));
    }

    // Clear all
    if (clearBtn) {
      clearBtn.addEventListener("click", () => {
        qsa(ms, `.ms__option input[type="checkbox"]`).forEach(cb => cb.checked = false);
        syncHiddenInputs(ms);
        renderChips(ms);
        form.submit();
      });
    }
  });

  // Close on outside click
  document.addEventListener("click", (e) => {
    const inside = e.target && e.target.closest && e.target.closest(".js-ms");
    if (!inside) closeAll(null);
  });

  // Global escape
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeAll(null);
  });
})();
