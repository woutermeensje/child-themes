jQuery(document).ready(function ($) {
    // Initialise Select2
    $('.company_filter-select').select2({
        width: '100%',
        allowClear: true,
        placeholder: function () {
            return $(this).data('placeholder');
        }
    });

    // Auto-filter on change
    $('#company-page-filter-form').on('change', 'select, input', function () {
        filterCompanyPages();
    });

    // Initial load
    filterCompanyPages();

    function filterCompanyPages() {
        var data = $('#company-page-filter-form').serialize();
        $.post(company_filter_ajax.ajaxurl, {
            action: 'filter_company_pages',
            ...Object.fromEntries(new URLSearchParams(data))
        }, function (response) {
            $('#company-results').html(response);
        });
    }
});
