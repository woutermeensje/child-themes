jQuery(document).ready(function ($) {
    // Initialise Select2.
    $('.company_filter-select').select2({
        width: '100%',
        allowClear: true,
        placeholder: function () {
            return $(this).data('placeholder');
        }
    });

    // Auto-filter on change.
    $('#companyspagina-filter-form').on('change', 'select, input', function () {
        filterCompanyspaginas();
    });

    // Initial load.
    filterCompanyspaginas();

    function filterCompanyspaginas() {
        var data = $('#companyspagina-filter-form').serialize();
        $.post(company_filter_ajax.ajaxurl, {
            action: 'filter_companyspaginas',
            ...Object.fromEntries(new URLSearchParams(data))
        }, function (response) {
            $('#company-resultaten').html(response);
        });
    }
});
