document.addEventListener("DOMContentLoaded", () => {
  const root = document.querySelector('[data-component="obberichten"]');
  if (!root) return;

  const form = root.querySelector("[data-ob-form]");
  const results = root.querySelector("#ob-results");
  if (!form || !results) return;

  const debounce = (fn, delay = 250) => {
    let t;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn(...args), delay);
    };
  };

  const serialize = () => {
    const fd = new FormData(form);
    fd.append("action", "ob_filter");
    // nonce is in hidden input "nonce"
    return fd;
  };

  const setPaged = (n) => {
    const inp = form.querySelector('input[name="paged"]');
    if (inp) inp.value = String(n);
  };

  const bindPagination = () => {
    const pager = results.querySelector("[data-ob-pagination]");
    if (!pager) return;
    pager.querySelectorAll("[data-ob-page]").forEach((btn) => {
      btn.addEventListener("click", () => {
        const page = parseInt(btn.getAttribute("data-ob-page") || "1", 10);
        setPaged(page);
        fetchListings();
      });
    });
  };

  const fetchListings = async () => {
    const fd = serialize();
    try {
      const res = await fetch(OB.ajaxurl, { method: "POST", body: fd });
      const json = await res.json();
      if (json && json.success && json.data && json.data.html !== undefined) {
        results.innerHTML = json.data.html;
        bindPagination();
      }
    } catch (e) {
      console.error("OB AJAX error:", e);
    }
  };

  const fetchListingsDebounced = debounce(fetchListings, 250);

  // Dropdowns
  form.querySelectorAll("select").forEach((el) => {
    el.addEventListener("change", () => {
      setPaged(1);
      fetchListings();
    });
  });

  // Search field
  const search = form.querySelector('input[name="search_keywords"]');
  if (search) {
    search.addEventListener("input", () => {
      setPaged(1);
      fetchListingsDebounced();
    });
  }

  // Reset
  const resetBtn = root.querySelector("[data-ob-reset]");
  if (resetBtn) {
    resetBtn.addEventListener("click", () => {
      form.reset();
      setPaged(1);
      fetchListings();
    });
  }

  // initial bind for archive template (already server-rendered) still needs pagination binding
  bindPagination();
});

document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("[data-ob-condoleer-form]");
  if (!form || typeof OB === "undefined") return;

  const msg = form.querySelector("[data-ob-condoleer-msg]");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    msg.textContent = "";

    const fd = new FormData(form);
    fd.append("action", "ob_submit_condolence");

    try {
      const res = await fetch(OB.ajaxurl, { method: "POST", body: fd });
      const json = await res.json();

      if (json.success) {
        form.reset();
        msg.textContent = "Dank je wel. Je bericht is ontvangen.";
      } else {
        msg.textContent = (json.data && json.data.message) ? json.data.message : "Er ging iets mis. Probeer het opnieuw.";
      }
    } catch (err) {
      msg.textContent = "Er ging iets mis. Probeer het opnieuw.";
      console.error(err);
    }
  });
});
