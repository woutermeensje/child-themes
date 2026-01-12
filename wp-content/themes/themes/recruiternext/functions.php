<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * ✅ ENQUEUE STYLES (with Elementor check)
 */
add_action('wp_enqueue_scripts', function () {
    $dependencies = ['parent-style'];
    if (did_action('elementor/loaded') && wp_style_is('elementor-frontend', 'registered')) {
        $dependencies[] = 'elementor-frontend';
    }

    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', $dependencies, wp_get_theme()->get('Version'));
    wp_enqueue_style('poppins-font', 'https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap', [], null);
    wp_enqueue_style('inter-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', [], null);
    wp_enqueue_style('custom-fonts', get_stylesheet_directory_uri() . '/fonts/fonts.css');
    wp_enqueue_style('child-gf-styles', get_stylesheet_directory_uri() . '/css/gravity-forms.css');
});

/**
 * ✅ WP JOB MANAGER: TEMPLATE OVERRIDES
 */
add_filter('job_manager_locate_template', function ($template, $template_name) {
    $custom_templates = [
        'content-job_listing.php',
        'content-single-job_listing.php',
        'job-filters.php',
        'job-filter-job-types.php',
        'job-listings-start.php',
        'job-listings-end.php',
        'job-submit.php',
        'functions.php',
    ];
    $custom_path = get_stylesheet_directory() . '/wp-job-manager/' . $template_name;
    return (in_array($template_name, $custom_templates) && file_exists($custom_path)) ? $custom_path : $template;
}, 10, 2);

/**
 * ✅ REGISTER CUSTOM TAXONOMIES
 */
add_action('init', function () {
    register_taxonomy('job_company', 'job_listing', [
        'label' => 'Organisaties',
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'organisatie'],
    ]);

    register_taxonomy('job_tag', 'job_listing', [
        'label' => 'Tags',
        'hierarchical' => false,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'tag'],
    ]);
});

/**
 * ✅ FILTER SUPPORT VOOR CUSTOM TAXONOMIES
 */
add_filter('job_manager_get_listings_custom_filter', function ($query_args, $args) {
    if (!empty($args['filter_job_tag'])) {
        $query_args['tax_query'][] = [
            'taxonomy' => 'job_tag',
            'field'    => 'slug',
            'terms'    => explode(',', sanitize_text_field($args['filter_job_tag'])),
            'operator' => 'IN',
        ];
    }

    if (!empty($args['filter_job_company'])) {
        $query_args['tax_query'][] = [
            'taxonomy' => 'job_company',
            'field'    => 'slug',
            'terms'    => explode(',', sanitize_text_field($args['filter_job_company'])),
            'operator' => 'IN',
        ];
    }

    return $query_args;
}, 10, 2);

/**
 * ✅ SHORTCODE SUPPORT: [jobs bedrijf="slug"]
 */
add_filter('job_manager_get_listings_args', function($args) {
    if (!empty($args['bedrijf'])) {
        $args['filter_job_company'] = sanitize_title($args['bedrijf']);
    }
    return $args;
});

/**
 * ✅ DEBUGGING LOG (optioneel)
 */
add_filter('job_manager_get_listings_start', function ($query_args, $args) {
    if (isset($_REQUEST['filter_job_company'])) {
        error_log('✅ filter_job_company ontvangen: ' . $_REQUEST['filter_job_company']);
    }
    if (isset($_REQUEST['filter_job_tag'])) {
        error_log('✅ filter_job_tag ontvangen: ' . $_REQUEST['filter_job_tag']);
    }
    return $query_args;
}, 10, 2);

/**
 * ✅ BACKUP JOB CATEGORIES als WP Job Manager ze niet registreert
 */
add_action('init', function() {
    if (!taxonomy_exists('job_listing_category')) {
        register_taxonomy('job_listing_category', 'job_listing', [
            'labels' => [
                'name' => __('Vacaturecategorieën', 'wp-job-manager'),
                'singular_name' => __('Vacaturecategorie', 'wp-job-manager'),
                'add_new_item' => __('Nieuwe categorie toevoegen', 'wp-job-manager'),
                'edit_item' => __('Categorie bewerken', 'wp-job-manager'),
                'search_items' => __('Categorie zoeken', 'wp-job-manager'),
                'all_items' => __('Alle categorieën', 'wp-job-manager'),
            ],
            'hierarchical' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'rewrite' => ['slug' => 'vacature-categorie'],
        ]);
    }
}, 20);
