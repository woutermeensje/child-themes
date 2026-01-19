<?php
if (!defined('ABSPATH')) exit;

add_shortcode('mh_units', function ($atts) {

    // Shortcode defaults (support comma-separated values)
    $atts = shortcode_atts([
        'per_page'  => 12,
        'type'      => '', // bv: "kantine" of "kantine,kantoor"
        'conditie'  => '', // bv: "nieuwstaat" of "goed,gebruikt"
    ], $atts, 'mh_units');

    // Search (GET heeft altijd prioriteit)
    $search = isset($_GET['mh_search']) ? sanitize_text_field($_GET['mh_search']) : '';

    /**
     * TYPE (mh_type[])
     * - Eerst: GET (user interactie)
     * - Anders: shortcode attribuut (default prefilter)
     */
    $types_selected = [];
    if (isset($_GET['mh_type'])) {
        $raw = $_GET['mh_type'];
        $types_selected = is_array($raw) ? $raw : [$raw];
    } elseif (!empty($atts['type'])) {
        $types_selected = array_map('trim', explode(',', $atts['type']));
    }
    $types_selected = array_values(array_filter(array_map('sanitize_title', $types_selected)));

    /**
     * CONDITIE (mh_conditie[])
     * - Eerst: GET (user interactie)
     * - Anders: shortcode attribuut (default prefilter)
     */
    $condities_selected = [];
    if (isset($_GET['mh_conditie'])) {
        $raw = $_GET['mh_conditie'];
        $condities_selected = is_array($raw) ? $raw : [$raw];
    } elseif (!empty($atts['conditie'])) {
        $condities_selected = array_map('trim', explode(',', $atts['conditie']));
    }
    $condities_selected = array_values(array_filter(array_map('sanitize_title', $condities_selected)));

    // Tax query opbouwen
    $tax_query = [];

    if (!empty($types_selected)) {
        $tax_query[] = [
            'taxonomy' => 'mh_unit_type',
            'field'    => 'slug',
            'terms'    => $types_selected,
            'operator' => 'IN',
        ];
    }

    if (!empty($condities_selected)) {
        $tax_query[] = [
            'taxonomy' => 'mh_unit_conditie',
            'field'    => 'slug',
            'terms'    => $condities_selected,
            'operator' => 'IN',
        ];
    }

    // Als er meerdere tax filters zijn: beide moeten matchen (AND)
    if (count($tax_query) > 1) {
        $tax_query = array_merge([['relation' => 'AND']], $tax_query);
    }

    // Query args
    $query_args = [
        'post_type'      => 'mh_unit',
        'posts_per_page' => (int) $atts['per_page'],
        's'              => $search,
    ];

    if (!empty($tax_query)) {
        $query_args['tax_query'] = $tax_query;
    }

    $query = new WP_Query($query_args);

    ob_start();

    // Filter UI (aparte file)
    mh_units_render_template('filter.php', [
        'search'             => $search,
        'types_selected'     => $types_selected,
        'condities_selected' => $condities_selected,
    ]);

    // Listings
    mh_units_render_template('loop.php', [
        'query' => $query,
    ]);

    wp_reset_postdata();

    return ob_get_clean();
});
