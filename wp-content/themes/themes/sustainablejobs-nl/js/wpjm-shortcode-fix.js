/*
 * WPJM Shortcode Fix for Sustainablejobs.nl (Robust Version)
 */
jQuery(function($) {
    if (typeof sjn_shortcode_filters === 'undefined' || $.isEmptyObject(sjn_shortcode_filters)) {
        return;
    }

    var job_filters_form = $('.job_filters');
    if (job_filters_form.length === 0) {
        return;
    }

    var is_initial_load = true;

    job_filters_form.on('update_results', function(event, page) {
        if (is_initial_load && page === 1) {
            $.each(sjn_shortcode_filters, function(filter_key, filter_value) {
                var input_field = job_filters_form.find('[name="' + filter_key + '"]');
                if (input_field.length > 0) {
                    input_field.val(filter_value);
                }
            });
            is_initial_load = false;
        }
    });
    
    job_filters_form.triggerHandler('update_results', [1, false]);
});
