(function ($) {
  "use strict";

  function getFiltersFromUrl() {
    const params = new URLSearchParams(window.location.search);

    return {
      ga_search: (params.get("ga_search") || "").trim(),
      ga_toon: (params.get("ga_toon") || "all").trim(),
      ga_sort: (params.get("ga_sort") || "trending").trim(),
      ga_thema: params.getAll("ga_thema[]"),
      ga_type: params.getAll("ga_type[]"),
    };
  }

  $(document).on("click", "#fod-ga-loadmore", function (e) {
    e.preventDefault();

    if (typeof FOND_GEEF === "undefined") {
      console.error("FOND_GEEF ontbreekt. Script is niet gelocalized.");
      return;
    }

    const $btn = $(this);
    const $grid = $("#fod-ga-grid");

    const page = parseInt($btn.data("page") || 2, 10);
    const perPage = parseInt($grid.data("per-page") || 18, 10);

    const filters = getFiltersFromUrl();

    $btn.prop("disabled", true).addClass("is-loading");

    $.ajax({
      url: FOND_GEEF.ajax_url,
      method: "POST",
      dataType: "json",
      data: {
        action: "fondsen_geefacties_load_more",
        nonce: FOND_GEEF.nonce,
        page: page,
        per_page: perPage,
        ga_search: filters.ga_search,
        ga_toon: filters.ga_toon,
        ga_sort: filters.ga_sort,
        ga_thema: filters.ga_thema,
        ga_type: filters.ga_type,
      },
    })
      .done(function (resp) {
        if (!resp || resp.success !== true || !resp.data) return;

        if (resp.data.html) $grid.append(resp.data.html);

        $btn.data("page", resp.data.next_page || page + 1);

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
