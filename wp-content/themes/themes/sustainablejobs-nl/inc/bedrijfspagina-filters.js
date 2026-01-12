jQuery(document).ready(function ($) {
    // ✅ Select2 initialiseren
    $('.company_filter-select').select2({
        width: '100%',
        allowClear: true,
        placeholder: function () {
            return $(this).data('placeholder');
        }
    });

    // ✅ Formulier automatisch filteren bij wijziging
    $('#bedrijfspagina-filter-form').on('change', 'select, input', function () {
        filterBedrijfspaginas();
    });

    // ✅ Initieel laden
    filterBedrijfspaginas();

    function filterBedrijfspaginas() {
        var data = $('#bedrijfspagina-filter-form').serialize();
        $.post(bedrijf_filter_ajax.ajaxurl, {
            action: 'filter_bedrijfspaginas',
            ...Object.fromEntries(new URLSearchParams(data))
        }, function (response) {
            $('#bedrijf-resultaten').html(response);
        });
    }
});
