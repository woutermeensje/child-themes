<?php
if (!defined('ABSPATH')) exit;

add_shortcode('si_opdrachten', function ($atts) {

    $atts = shortcode_atts([
        'per_page' => 12,
        // optioneel prefilteren via shortcode: categorie="marketing,design" type="freelance,zzp"
        'categorie' => '',
        'type'      => '',
    ], $atts, 'si_opdrachten');

    // Search (GET heeft prioriteit)
    $search = isset($_GET['si_search']) ? sanitize_text_field($_GET['si_search']) : '';

    // CATEGORIE (si_categorie[])
    $cats_selected = [];
    if (isset($_GET['si_categorie'])) {
        $raw = $_GET['si_categorie'];
        $cats_selected = is_array($raw) ? $raw : [$raw];
    } elseif (!empty($atts['categorie'])) {
        $cats_selected = array_map('trim', explode(',', $atts['categorie']));
    }
    $cats_selected = array_values(array_filter(array_map('sanitize_title', $cats_selected)));

    // TYPE (si_type[])
    $types_selected = [];
    if (isset($_GET['si_type'])) {
        $raw = $_GET['si_type'];
        $types_selected = is_array($raw) ? $raw : [$raw];
    } elseif (!empty($atts['type'])) {
        $types_selected = array_map('trim', explode(',', $atts['type']));
    }
    $types_selected = array_values(array_filter(array_map('sanitize_title', $types_selected)));

    // Tax query
    $tax_query = [];

    if (!empty($cats_selected)) {
        $tax_query[] = [
            'taxonomy' => 'si_opdracht_categorie',
            'field'    => 'slug',
            'terms'    => $cats_selected,
            'operator' => 'IN',
        ];
    }

    if (!empty($types_selected)) {
        $tax_query[] = [
            'taxonomy' => 'si_opdracht_type',
            'field'    => 'slug',
            'terms'    => $types_selected,
            'operator' => 'IN',
        ];
    }

    if (count($tax_query) > 1) {
        $tax_query = array_merge([['relation' => 'AND']], $tax_query);
    }

    $query_args = [
        'post_type'      => 'si_opdracht',
        'posts_per_page' => (int) $atts['per_page'],
        's'              => $search,
    ];

    if (!empty($tax_query)) {
        $query_args['tax_query'] = $tax_query;
    }

    $query = new WP_Query($query_args);

    ob_start();

    si_opd_render_template('filter.php', [
        'search'         => $search,
        'cats_selected'  => $cats_selected,
        'types_selected' => $types_selected,
    ]);

    si_opd_render_template('loop.php', [
        'query' => $query,
    ]);

    wp_reset_postdata();

    return ob_get_clean();
});
