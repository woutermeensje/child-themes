<?php
if (!defined('ABSPATH')) exit;

add_shortcode('mh_units', function ($atts) {

    $atts = shortcode_atts([
        'per_page' => 12,
        'type' => '',      // optioneel: vooraf filteren op type slug
    ], $atts);

    $search = isset($_GET['mh_search']) ? sanitize_text_field($_GET['mh_search']) : '';
    $type   = isset($_GET['mh_type']) ? sanitize_text_field($_GET['mh_type']) : $atts['type'];

    $tax_query = [];
    if (!empty($type)) {
        $tax_query[] = [
            'taxonomy' => 'mh_unit_type',
            'field' => 'slug',
            'terms' => $type,
        ];
    }

    $query = new WP_Query([
        'post_type' => 'mh_unit',
        'posts_per_page' => (int)$atts['per_page'],
        's' => $search,
        'tax_query' => $tax_query,
    ]);

    ob_start();

    // Filter UI (aparte file)
    mh_units_render_template('filter.php', [
        'search' => $search,
        'type' => $type,
    ]);

    // Listings grid/rij (aparte files)
    mh_units_render_template('loop.php', [
        'query' => $query,
    ]);

    wp_reset_postdata();

    return ob_get_clean();
});
