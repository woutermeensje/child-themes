(function () {
  function onReady(callback) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", callback);
      return;
    }

    callback();
  }

  function getSelectValues(select, fallbackToAll) {
    var selected = Array.prototype.filter.call(select.options, function (option) {
      return option.selected && option.value !== "";
    }).map(function (option) {
      return option.value;
    });

    if (selected.length || !fallbackToAll) {
      return selected;
    }

    return Array.prototype.filter.call(select.options, function (option) {
      return option.value !== "";
    }).map(function (option) {
      return option.value;
    });
  }

  function syncJobTypeInputs(form) {
    var selects = form.querySelectorAll("select.js-custom-select[data-wpjm-filter='job_type']");
    var syncBox = form.querySelector(".mh-job-type-values");

    if (!selects.length) {
      return;
    }

    if (!syncBox) {
      syncBox = document.createElement("div");
      syncBox.className = "job_types mh-job-type-values";
      syncBox.setAttribute("aria-hidden", "true");
      form.appendChild(syncBox);
    }

    syncBox.innerHTML = "";

    selects.forEach(function (select) {
      getSelectValues(select, true).forEach(function (value) {
        var input = document.createElement("input");
        input.type = "checkbox";
        input.name = "filter_job_type[]";
        input.value = value;
        input.checked = true;
        syncBox.appendChild(input);
      });
    });
  }

  function triggerWpjmFilter(form) {
    syncJobTypeInputs(form);

    if (window.jQuery) {
      var listings = window.jQuery(form).closest("div.job_listings");
      if (listings.length) {
        listings.triggerHandler("update_results", [1, false]);
        return;
      }
    }

    form.dispatchEvent(new Event("change", { bubbles: true, cancelable: true }));
  }

  function debounce(fn, delay) {
    var timer;

    return function () {
      window.clearTimeout(timer);
      timer = window.setTimeout(fn, delay);
    };
  }

  function closeAll(except) {
    document.querySelectorAll(".sj-select.active").forEach(function (select) {
      if (except && select === except) {
        return;
      }

      select.classList.remove("active");

      var button = select.querySelector(".sj-select-btn");
      if (button) {
        button.setAttribute("aria-expanded", "false");
      }

      var searchInput = select.querySelector(".sj-search-input");
      if (searchInput) {
        searchInput.value = "";
        select.querySelectorAll(".sj-option").forEach(function (option) {
          option.style.display = "";
        });
      }
    });
  }

  function renderActiveFilters(form) {
    var activeFiltersEl = form.querySelector(".active-filters");
    if (!activeFiltersEl) {
      return;
    }

    activeFiltersEl.innerHTML = "";

    form.querySelectorAll("select.js-custom-select").forEach(function (select) {
      Array.prototype.filter.call(select.options, function (option) {
        return option.selected && option.value !== "";
      }).forEach(function (option) {
        var chip = document.createElement("span");
        chip.className = "active-filter";
        chip.setAttribute("role", "button");
        chip.setAttribute("tabindex", "0");
        chip.setAttribute("title", "Verwijder filter");
        chip.innerHTML = '<span class="active-filter-text"></span><span class="active-filter-x" aria-hidden="true">x</span>';
        chip.querySelector(".active-filter-text").textContent = option.dataset.label || option.textContent;
        chip.addEventListener("click", function (event) {
          event.preventDefault();
          option.selected = false;
          select.dispatchEvent(new Event("change", { bubbles: true }));
        });
        chip.addEventListener("keydown", function (event) {
          if (event.key !== "Enter" && event.key !== " ") {
            return;
          }

          event.preventDefault();
          option.selected = false;
          select.dispatchEvent(new Event("change", { bubbles: true }));
        });
        activeFiltersEl.appendChild(chip);
      });
    });

    activeFiltersEl.style.display = activeFiltersEl.children.length ? "flex" : "none";
  }

  function buildSelect(select, form) {
    if (select.dataset.mhSelectReady === "true") {
      return;
    }

    select.dataset.mhSelectReady = "true";

    var isMultiple = select.multiple === true;
    var forceMode = select.dataset.mode;
    var isSingle = forceMode === "single" ? true : !isMultiple;
    var placeholder = select.dataset.placeholder || "Selecteer";

    var wrap = document.createElement("div");
    wrap.className = "sj-select-wrap";
    select.parentNode.insertBefore(wrap, select);
    wrap.appendChild(select);
    select.classList.add("sj-hidden-select");

    var root = document.createElement("div");
    root.className = "sj-select";
    root.dataset.type = isSingle ? "single" : "multi";

    var button = document.createElement("div");
    button.className = "sj-select-btn";
    button.setAttribute("role", "button");
    button.setAttribute("tabindex", "0");
    button.setAttribute("aria-haspopup", "listbox");
    button.setAttribute("aria-expanded", "false");
    button.innerHTML = [
      '<span class="sj-btn-content">',
      '<span class="sj-placeholder"></span>',
      '<span class="sj-tags" aria-hidden="true"></span>',
      '</span>',
      '<span class="sj-select__actions">',
      '<button type="button" class="sj-clear" aria-label="Wis selectie" title="Wis selectie">x</button>',
      '<span class="sj-chev" aria-hidden="true"></span>',
      '</span>'
    ].join("");

    var placeholderEl = button.querySelector(".sj-placeholder");
    var tagsEl = button.querySelector(".sj-tags");
    var clearButton = button.querySelector(".sj-clear");
    placeholderEl.textContent = placeholder;

    var list = document.createElement("div");
    list.className = "sj-options";
    list.setAttribute("role", "listbox");
    if (!isSingle) {
      list.setAttribute("aria-multiselectable", "true");
    }

    var searchInput = null;
    if (!isSingle) {
      var searchWrap = document.createElement("div");
      searchWrap.className = "sj-search";
      searchWrap.innerHTML = '<input type="text" class="sj-search-input" placeholder="Zoek in ' + placeholder.toLowerCase() + '">';
      searchInput = searchWrap.querySelector(".sj-search-input");
      list.appendChild(searchWrap);
    }

    var optionRows = [];

    Array.prototype.forEach.call(select.options, function (option) {
      if (isSingle && option.value === "") {
        return;
      }

      var row = document.createElement("div");
      row.className = "sj-option";
      row.dataset.value = option.value;
      row.setAttribute("role", "option");

      var optionLabel = option.dataset.label || option.textContent.trim();
      var optionCount = option.dataset.count;
      row.innerHTML = '<span class="sj-option-text"></span>' + (optionCount !== undefined ? '<span class="sj-option-count"></span>' : "");
      row.querySelector(".sj-option-text").textContent = optionLabel;

      var countEl = row.querySelector(".sj-option-count");
      if (countEl) {
        countEl.textContent = optionCount;
      }

      var syncSelected = function () {
        row.classList.toggle("is-selected", option.selected);
        row.setAttribute("aria-selected", option.selected ? "true" : "false");
      };

      row.addEventListener("click", function (event) {
        event.preventDefault();

        if (option.disabled) {
          return;
        }

        if (isSingle) {
          Array.prototype.forEach.call(select.options, function (selectOption) {
            selectOption.selected = false;
          });
          option.selected = true;
          closeAll(root);
        } else {
          option.selected = !option.selected;
        }

        renderState();
        select.dispatchEvent(new Event("change", { bubbles: true }));
      });

      optionRows.push({ option: option, row: row, syncSelected: syncSelected });
      list.appendChild(row);
      syncSelected();
    });

    function filterOptionRows() {
      if (!searchInput) {
        return;
      }

      var term = searchInput.value.trim().toLowerCase();
      optionRows.forEach(function (item) {
        var label = item.option.dataset.label || item.option.textContent;
        item.row.style.display = label.toLowerCase().indexOf(term) !== -1 ? "" : "none";
      });
    }

    function renderState() {
      var selectedOptions = Array.prototype.filter.call(select.options, function (option) {
        return option.selected && option.value !== "";
      });

      optionRows.forEach(function (item) {
        item.syncSelected();
      });

      tagsEl.innerHTML = "";
      placeholderEl.textContent = selectedOptions.length ? placeholder + " (" + selectedOptions.length + ")" : placeholder;
      placeholderEl.style.display = "inline";
      tagsEl.style.display = "none";
      clearButton.style.display = selectedOptions.length ? "inline-flex" : "none";
    }

    function toggleSelect() {
      var wasOpen = root.classList.contains("active");
      closeAll(root);

      if (wasOpen) {
        root.classList.remove("active");
        button.setAttribute("aria-expanded", "false");
        return;
      }

      root.classList.add("active");
      button.setAttribute("aria-expanded", "true");

      if (searchInput) {
        searchInput.value = "";
        filterOptionRows();
        window.setTimeout(function () {
          searchInput.focus();
        }, 10);
      }
    }

    if (searchInput) {
      searchInput.addEventListener("input", filterOptionRows);
      searchInput.addEventListener("click", function (event) {
        event.stopPropagation();
      });
      searchInput.addEventListener("keydown", function (event) {
        event.stopPropagation();
      });
    }

    clearButton.addEventListener("click", function (event) {
      event.stopPropagation();
      event.preventDefault();
      Array.prototype.forEach.call(select.options, function (option) {
        option.selected = false;
      });
      renderState();
      select.dispatchEvent(new Event("change", { bubbles: true }));
    });

    button.addEventListener("click", function (event) {
      if (event.target.closest(".sj-clear")) {
        return;
      }

      event.preventDefault();
      toggleSelect();
    });

    button.addEventListener("keydown", function (event) {
      if (event.key !== "Enter" && event.key !== " ") {
        return;
      }

      event.preventDefault();
      toggleSelect();
    });

    select.addEventListener("change", function () {
      renderState();
      syncJobTypeInputs(form);
      renderActiveFilters(form);
      triggerWpjmFilter(form);
    });

    renderState();
    root.appendChild(button);
    root.appendChild(list);
    wrap.appendChild(root);
  }

  function initForm(form) {
    if (form.dataset.mhJobFiltersReady === "true") {
      return;
    }

    form.dataset.mhJobFiltersReady = "true";

    var filterJobs = debounce(function () {
      triggerWpjmFilter(form);
    }, 250);

    var keywords = form.querySelector("#search_keywords");
    var location = form.querySelector("#search_location");

    if (keywords) {
      keywords.addEventListener("input", filterJobs);
    }

    if (location) {
      location.addEventListener("input", filterJobs);
    }

    form.querySelectorAll("select.js-custom-select").forEach(function (select) {
      buildSelect(select, form);
    });

    syncJobTypeInputs(form);
    renderActiveFilters(form);
  }

  onReady(function () {
    document.querySelectorAll("form.job_filters").forEach(initForm);

    document.addEventListener("click", function (event) {
      if (!event.target.closest(".sj-select")) {
        closeAll();
      }
    });

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") {
        closeAll();
      }
    });
  });
}());
