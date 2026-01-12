<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * ✅ ENQUEUE STYLES (with Elementor check)
 */
add_action('wp_enqueue_scripts', function() {
    // Parent theme style
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');

    // Elementor dependency (indien actief)
    $dependencies = ['parent-style'];
    if ( did_action('elementor/loaded') && wp_style_is('elementor-frontend', 'registered') ) {
        $dependencies[] = 'elementor-frontend';
    }

    // Child theme style
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', $dependencies, wp_get_theme()->get('Version'));

    // Fonts
    wp_enqueue_style('poppins-font', 'https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap', [], null);
    wp_enqueue_style('inter-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', [], null);
    wp_enqueue_style('custom-fonts', get_stylesheet_directory_uri() . '/fonts/fonts.css');

    // Gravity Forms custom styles — verwijderd
}, 10);

// Gravity Forms spinner uitschakelen
add_filter( 'gform_ajax_spinner_url', '__return_false' );



/**
 * ✅ WPJM AJAX-script en Select2 laden op vacaturespagina's
 */
add_action('wp_enqueue_scripts', function () {
    // Altijd nodig voor AJAX filters
    wp_enqueue_script('wp-job-manager-ajax-filters');

    // Alleen waar [jobs] staat of op je overzichtspagina('s)
    $load_on_this_page = false;
    if ( function_exists('get_the_ID') ) {
        $post_id = get_the_ID();
        $content = $post_id ? get_post_field('post_content', $post_id) : '';
        if ( has_shortcode($content, 'jobs') ) $load_on_this_page = true;
    }
    if ( is_page(array('vacatures','jobs','job-listings')) ) $load_on_this_page = true;

    if ( $load_on_this_page ) {
        // Select2 (zelfde als Sustainablejobs)
        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', [], '4.1.0-rc.0');
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], '4.1.0-rc.0', true);
    }
}, 20);

/**
 * ✅ WP JOB MANAGER: CUSTOM TEMPLATE OVERRIDES
 */
add_filter('job_manager_locate_template', function($template, $template_name) {
    $custom_templates = [
        'content-job_listing.php',
        'content-single-job_listing-company.php',
        'content-single-job_listing-meta.php',
        'content-single-job_listing.php',
        'content-widget-job_listing.php',
        'functions.php',
        'job-filter-job-types.php',
        'job-filters.php',
        'job-listings-end.php',
        'job-listings-start.php',
        'job-preview.php',
        'job-submit.php',
    ];
    $custom_template_path = get_stylesheet_directory() . '/wp-job-manager/' . $template_name;
    return (in_array($template_name, $custom_templates) && file_exists($custom_template_path)) ? $custom_template_path : $template;
}, 10, 2);

/**
 * ✅ CUSTOM TAXONOMIES (DeReceptionist)
 */
add_action('init', function() {
    // Organisaties
    register_taxonomy('job_company', 'job_listing', [
        'labels' => [
            'name' => __('Organisaties', 'textdomain'),
            'singular_name' => __('Organisatie', 'textdomain'),
            'menu_name' => __('Organisaties', 'textdomain'),
            'all_items' => __('Alle Organisaties', 'textdomain'),
            'add_new_item' => __('Nieuwe Organisatie toevoegen', 'textdomain'),
            'edit_item' => __('Bewerk Organisatie', 'textdomain'),
            'view_item' => __('Bekijk Organisatie', 'textdomain'),
            'search_items' => __('Zoek Organisaties', 'textdomain'),
        ],
        'hierarchical'    => true,
        'show_ui'         => true,
        'show_admin_column'=> true,
        'show_in_rest'    => true,
        'rewrite'         => ['slug' => 'organisatie'],
    ]);

    // Sectors
    register_taxonomy('job_sector', 'job_listing', [
        'labels' => [
            'name' => __('Sectors', 'textdomain'),
            'singular_name' => __('Sector', 'textdomain'),
            'menu_name' => __('Sectors', 'textdomain'),
            'all_items' => __('All Sectors', 'textdomain'),
            'add_new_item' => __('Add New Sector', 'textdomain'),
            'edit_item' => __('Edit Sector', 'textdomain'),
            'view_item' => __('View Sector', 'textdomain'),
            'search_items' => __('Search Sectors', 'textdomain'),
        ],
        'hierarchical'    => true,
        'show_ui'         => true,
        'show_admin_column'=> true,
        'show_in_rest'    => true,
        'rewrite'         => ['slug' => 'sector'],
    ]);

    // Regions
    register_taxonomy('job_regio', 'job_listing', [
        'labels' => [
            'name' => __('Regions', 'textdomain'),
            'singular_name' => __('Region', 'textdomain'),
            'menu_name' => __('Regions', 'textdomain'),
            'all_items' => __('All Regions', 'textdomain'),
            'add_new_item' => __('Add New Region', 'textdomain'),
            'edit_item' => __('Edit Region', 'textdomain'),
            'view_item' => __('View Region', 'textdomain'),
            'search_items' => __('Search Regions', 'textdomain'),
        ],
        'hierarchical'    => true,
        'show_ui'         => true,
        'show_admin_column'=> true,
        'show_in_rest'    => true,
        'rewrite'         => ['slug' => 'regio'],
    ]);

    // Job names
    register_taxonomy('job_name', 'job_listing', [
        'labels' => [
            'name' => __('Job Names', 'textdomain'),
            'singular_name' => __('Job Name', 'textdomain'),
            'menu_name' => __('Job Names', 'textdomain'),
            'all_items' => __('All Job Names', 'textdomain'),
            'add_new_item' => __('Add New Job Name', 'textdomain'),
            'edit_item' => __('Edit Job Name', 'textdomain'),
            'view_item' => __('View Job Name', 'textdomain'),
            'search_items' => __('Search Job Names', 'textdomain'),
        ],
        'hierarchical'    => true,
        'show_ui'         => true,
        'show_admin_column'=> true,
        'show_in_rest'    => true,
        'rewrite'         => ['slug' => 'functie'],
        'meta_box_cb'     => 'post_categories_meta_box',
    ]);
});

/**
 * ✅ SERVER-SIDE FILTERS (AJAX + GET + SHORTCODE)
 *  - AND tussen taxonomieën / OR binnen 1 taxonomie
 *  - Gebruikt 'job_listing_type' (WPJM) voor dienstverbanden; werkt met field 'filter_job_types'
 */
add_filter('get_job_listings_query_args', function ($query_args, $args) {

    // 1) AJAX: form_data -> $_POST
    if ( isset($_POST['form_data']) && is_string($_POST['form_data']) ) {
        $parsed = [];
        parse_str( wp_unslash($_POST['form_data']), $parsed );
        foreach ($parsed as $k => $v) { $_POST[$k] = $v; }
    }

    // 2) Ondersteunde taxonomieën
    $tax_map = [
        'job_listing_type' => 'job_listing_type', // WPJM Types
        'job_sector'       => 'job_sector',
        'job_regio'        => 'job_regio',
        'job_name'         => 'job_name',
        'job_company'      => 'job_company',
    ];

    // 3) Begin met lege tax_query
    if ( empty($query_args['tax_query']) || ! is_array($query_args['tax_query']) ) {
        $query_args['tax_query'] = [];
    }

    // 4) Helper: lees waarden uit POST/GET/shortcode
    $read_vals = function($key) use ($args) {
        $vals = [];

        // POST (accepteer zowel filter_key als key)
        foreach (['filter_' . $key, $key] as $k) {
            if ( ! empty($_POST[$k]) ) {
                $vals = array_merge($vals, (array) $_POST[$k]);
            }
        }

        // GET
        if ( empty($vals) && ! empty($_GET[$key]) ) {
            $vals = array_merge($vals, (array) $_GET[$key]);
        }

        // Shortcode-atts (alleen als niets anders is gezet)
        if ( empty($vals) && ! empty($args[$key]) ) {
            $vals = array_merge($vals, array_map('trim', explode(',', (string) $args[$key])));
        }

        // Opschonen
        $vals = array_values(array_unique(array_filter(array_map('sanitize_title', $vals))));
        return $vals;
    };

    // 5) Speciale key voor types: in je form heet het 'filter_job_types'
    //    We mappen die naar tax 'job_listing_type'
    $type_vals = [];
    foreach (['filter_job_types', 'job_listing_type'] as $key) {
        if ( ! empty($_POST[$key]) ) { $type_vals = array_merge($type_vals, (array) $_POST[$key]); }
        if ( empty($type_vals) && ! empty($_GET[$key]) ) { $type_vals = array_merge($type_vals, (array) $_GET[$key]); }
        if ( empty($type_vals) && ! empty($args['job_listing_type']) ) {
            $type_vals = array_merge($type_vals, array_map('trim', explode(',', (string) $args['job_listing_type'])));
        }
    }
    $type_vals = array_values(array_unique(array_filter(array_map('sanitize_title', $type_vals))));
    if ( ! empty($type_vals) ) {
        $query_args['tax_query'][] = [
            'taxonomy' => 'job_listing_type',
            'field'    => 'slug',
            'terms'    => $type_vals,
            'operator' => 'IN',
        ];
    }

    // 6) Overige taxonomieën
    foreach (['job_sector','job_regio','job_name','job_company'] as $key) {
        $vals = $read_vals($key);
        if ( ! empty($vals) ) {
            $query_args['tax_query'][] = [
                'taxonomy' => $tax_map[$key],
                'field'    => 'slug',
                'terms'    => $vals,
                'operator' => 'IN',
            ];
        }
    }

    if ( ! empty($query_args['tax_query']) && ! isset($query_args['tax_query']['relation']) ) {
        $query_args['tax_query'] = array_merge(['relation' => 'AND'], $query_args['tax_query']);
    }

    return $query_args;
}, 10, 2);

/**
 * ✅ Shortcode-atts doorgeven bij initial render
 *  Voorbeeld: [jobs job_company="acme,globex" job_regio="randstad" job_listing_type="full-time"]
 */
add_filter('job_manager_get_listings_shortcode_args', function($atts){
    $map = [
        'job_listing_type' => 'job_listing_type',
        'job_sector'       => 'job_sector',
        'job_regio'        => 'job_regio',
        'job_name'         => 'job_name',
        'job_company'      => 'job_company',
    ];

    $tx = [];
    foreach ($map as $shortcode_key => $taxonomy) {
        if ( ! empty($atts[$shortcode_key]) ) {
            $tx[] = [
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => array_map('sanitize_title', array_map('trim', explode(',', $atts[$shortcode_key]))),
                'operator' => 'IN',
            ];
        }
    }

    if ( ! empty($tx) ) {
        $atts['tax_query'] = $tx;
    }

    return $atts;
}, 10, 1);
