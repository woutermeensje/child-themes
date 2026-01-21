(function () {
  function init() {
    const root = document.getElementById("sj-feedback-root");
    if (!root) return;

    // ✅ Cruciaal: hang direct onder <body> (zoals jouw console-test)
    if (root.parentElement !== document.body) {
      document.body.appendChild(root);
    }

    // Forceer fixed positie (extra robuust)
    root.style.position = "fixed";
    root.style.right = "20px";
    root.style.bottom = "20px";
    root.style.left = "auto";
    root.style.top = "auto";
    root.style.zIndex = "999999";

    const btn = document.getElementById("sj-feedback-btn");
    const panel = document.getElementById("sj-feedback-panel");
    const closeBtn = root.querySelector(".fpsj__close");
    const form = root.querySelector(".fpsj__form");
    const status = root.querySelector(".fpsj__status");
    const message = root.querySelector("#fpsj-message");
    const submitBtn = root.querySelector(".fpsj__submit");

    if (!btn || !panel || !closeBtn || !form) return;

    const open = () => {
      panel.hidden = false;
      btn.setAttribute("aria-expanded", "true");
      root.classList.add("is-open");
      setTimeout(() => message?.focus(), 50);
    };

    const close = () => {
      panel.hidden = true;
      btn.setAttribute("aria-expanded", "false");
      root.classList.remove("is-open");
      if (status) status.textContent = "";
    };

    btn.addEventListener("click", () => {
      root.classList.contains("is-open") ? close() : open();
    });

    closeBtn.addEventListener("click", (e) => {
      e.preventDefault();
      close();
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && root.classList.contains("is-open")) close();
    });

    document.addEventListener("click", (e) => {
      if (!root.classList.contains("is-open")) return;
      if (!e.target.closest("#sj-feedback-root")) close();
    });

    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      const msg = (message?.value || "").trim();
      if (msg.length < 5) {
        if (status) status.textContent = "Vul alsjeblieft wat feedback in.";
        return;
      }

      if (submitBtn) submitBtn.disabled = true;
      if (status) status.textContent = (window.FPSJ?.labels?.sending) || "Versturen...";

      const fd = new FormData(form);
      fd.append("action", "fpsj_submit");
      fd.append("nonce", window.FPSJ?.nonce || "");

      try {
        const res = await fetch(window.FPSJ.ajax_url, {
          method: "POST",
          credentials: "same-origin",
          body: fd
        });

        const data = await res.json();

        if (!res.ok || !data?.success) {
          if (status) status.textContent = data?.data?.message || (window.FPSJ?.labels?.error ?? "Oeps, fout.");
          if (submitBtn) submitBtn.disabled = false;
          return;
        }

        if (status) status.textContent = window.FPSJ?.labels?.sent || "Dankjewel!";
        form.reset();
        setTimeout(close, 900);
      } catch (err) {
        if (status) status.textContent = window.FPSJ?.labels?.error || "Oeps, versturen lukte niet.";
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
