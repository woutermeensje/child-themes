(function ($) {
  "use strict";

  function getFiltersFromUrl() {
    const params = new URLSearchParams(window.location.search);

    return {
      org_search: (params.get("org_search") || "").trim(),
      org_sector: params.getAll("org_sector[]"),
      org_type: params.getAll("org_type[]"),
    };
  }

  $(document).on("click", "#fod-org-loadmore", function (e) {
    e.preventDefault();

    if (typeof FOND_DIR === "undefined") {
      console.error("FOND_DIR ontbreekt. Script is niet gelocalized.");
      return;
    }

    const $btn = $(this);
    const $grid = $("#fod-org-grid");

    const page = parseInt($btn.data("page") || 2, 10);
    const perPage = parseInt($grid.data("per-page") || 30, 10);

    const filters = getFiltersFromUrl();

    $btn.prop("disabled", true).addClass("is-loading");

    $.ajax({
      url: FOND_DIR.ajax_url,
      method: "POST",
      dataType: "json",
      data: {
        action: "fondsen_org_dir_load_more",
        nonce: FOND_DIR.nonce,
        page: page,
        per_page: perPage,
        org_search: filters.org_search,
        org_sector: filters.org_sector,
        org_type: filters.org_type
      }
    })
    .done(function (resp) {
      if (!resp || resp.success !== true || !resp.data) return;

      if (resp.data.html) $grid.append(resp.data.html);

      $btn.data("page", resp.data.next_page || (page + 1));

      if (!resp.data.has_more) $btn.hide();
    })
    .fail(function (xhr) {
      console.error("AJAX error:", xhr.status, xhr.responseText);
    })
    .always(function () {
      $btn.prop("disabled", false).removeClass("is-loading");
    });
  });

})(jQuery);
