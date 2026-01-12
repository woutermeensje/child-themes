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
    // Organisaties
    register_taxonomy('job_company', 'job_listing', [
        'labels' => ['name' => 'Organisaties'],
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'organisatie'],
    ]);

    // Regions
    register_taxonomy('job_regio', 'job_listing', [
        'labels' => ['name' => 'Regions'],
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'regio'],
    ]);

    // Job names
    register_taxonomy('job_name', 'job_listing', [
        'labels' => ['name' => 'Job Names'],
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'functie'],
        'meta_box_cb' => 'post_categories_meta_box',
    ]);

    // Salary range
    register_taxonomy('salary_range', 'job_listing', [
        'labels' => ['name' => 'Salary Ranges'],
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'salaris'],
    ]);

    // Vakgebied
    register_taxonomy('vakgebied', ['job_listing'], [
        'label' => 'Vakgebied',
        'hierarchical' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'rewrite' => ['slug' => 'vakgebied'],
    ]);

    // ✅ Job Tags (nieuw – NIET-hiërarchisch!)
    register_taxonomy('job_tag', 'job_listing', [
        'labels' => [
            'name' => __('Job Tags', 'textdomain'),
            'singular_name' => __('Job Tag', 'textdomain'),
            'all_items' => __('All Tags', 'textdomain'),
            'add_new_item' => __('Add New Tag', 'textdomain'),
            'edit_item' => __('Edit Tag', 'textdomain'),
        ],
        'hierarchical' => false,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'job-tag'],
    ]);
});

/**
 * ✅ CUSTOM FILTERS: Alleen job_tag
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
    return $query_args;
}, 10, 2);

/**
 * ✅ DEBUGGING
 */
add_filter('job_manager_get_listings_start', function ($query_args, $args) {
    if (isset($_REQUEST['filter_job_tag'])) {
        error_log('✅ filter_job_tag ontvangen: ' . $_REQUEST['filter_job_tag']);
    }
    return $query_args;
}, 10, 2);

