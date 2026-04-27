<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// =========================================================
// 1) Styles en fonts
// =========================================================
add_action('wp_enqueue_scripts', function () {
    $theme_dir     = get_stylesheet_directory();
    $theme_uri     = get_stylesheet_directory_uri();
    $theme_version = wp_get_theme()->get('Version');
    $parent_style  = get_template_directory() . '/style.css';
    $child_style   = $theme_dir . '/style.css';
    $fonts_style   = $theme_dir . '/fonts/fonts.css';
    $header_style  = $theme_dir . '/css/header.css';
    $landing_style = $theme_dir . '/css/template.css';

    $dependencies = ['parent-style'];
    if (did_action('elementor/loaded') && wp_style_is('elementor-frontend', 'registered')) {
        $dependencies[] = 'elementor-frontend';
    }

    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css', [], file_exists($parent_style) ? filemtime($parent_style) : $theme_version);
    wp_enqueue_style('child-style', $theme_uri . '/style.css', $dependencies, file_exists($child_style) ? filemtime($child_style) : $theme_version);
    wp_enqueue_style('poppins-font', 'https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap', [], null);
    wp_enqueue_style('inter-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', [], null);

    if (file_exists($fonts_style)) {
        wp_enqueue_style('custom-fonts', $theme_uri . '/fonts/fonts.css', [], filemtime($fonts_style));
    }

    if (file_exists($header_style)) {
        wp_enqueue_style('rn-header', $theme_uri . '/css/header.css', ['child-style'], filemtime($header_style));
    }

    $page_template = is_page() ? basename(get_page_template()) : '';
    if (file_exists($landing_style) && (is_page_template('page-landingspagina.php') || $page_template === 'page-landingspagina.php')) {
        wp_enqueue_style('equitee-template', $theme_uri . '/css/template.css', ['child-style', 'rn-header'], filemtime($landing_style));
    }
});

add_theme_support('job-manager-templates');


// =========================================================
// Equitee_Nav_Walker – dropdown-indicator voor navigatie
// =========================================================
if ( ! class_exists('Equitee_Nav_Walker') ) :
class Equitee_Nav_Walker extends Walker_Nav_Menu {
    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $classes   = empty( $item->classes ) ? [] : (array) $item->classes;
        $has_child = in_array( 'menu-item-has-children', $classes, true );
        $is_active = in_array( 'current-menu-item', $classes, true )
                  || in_array( 'current-menu-ancestor', $classes, true );
        $li_class  = 'rn-nav__item';
        if ( $has_child ) $li_class .= ' rn-nav__item--has-children';
        if ( $is_active ) $li_class .= ' is-active';
        $output .= '<li class="' . esc_attr( $li_class ) . '">';
        $url        = ! empty( $item->url ) ? $item->url : '#';
        $title      = apply_filters( 'the_title', $item->title, $item->ID );
        $attr_title = ! empty( $item->attr_title ) ? ' title="' . esc_attr( $item->attr_title ) . '"' : '';
        $target     = ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
        $rel        = ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : '';
        $output .= '<a class="rn-nav__link' . ( $is_active ? ' is-active' : '' ) . '"'
                 . ' href="' . esc_url( $url ) . '"'
                 . $attr_title . $target . $rel . '>';
        $output .= esc_html( $title );
        if ( $has_child ) $output .= '<span class="rn-nav__chev" aria-hidden="true"></span>';
        $output .= '</a>';
    }
    public function start_lvl( &$output, $depth = 0, $args = null ) {
        $output .= '<ul class="rn-nav__dropdown">';
    }
    public function end_lvl( &$output, $depth = 0, $args = null ) {
        $output .= '</ul>';
    }
    public function end_el( &$output, $item, $depth = 0, $args = null ) {
        $output .= '</li>';
    }
}
endif;

// =========================================================
// Nav menu locaties registreren
// =========================================================
add_action('after_setup_theme', function() {
    register_nav_menus([
        'primary_nav' => 'Primaire navigatie',
        'footer_nav'  => 'Footer navigatie',
    ]);
});

// =========================================================
// =========================================================
// Shortcode: [equitee_header]
// =========================================================
add_shortcode('equitee_header', function() {
    ob_start();
    include get_stylesheet_directory() . '/template-parts/header.php';
    return ob_get_clean();
});


// =========================================================
// 2) WP Job Manager template overrides
// =========================================================
add_filter('job_manager_locate_template', function ($template, $template_name) {
    $custom_templates = [
        'content-job_listing.php',
        'content-single-job_listing.php',
        'job-filters.php',
        'job-filter-job-types.php',
        'job-listings-start.php',
        'job-listings-end.php',
    ];

    $custom_path = get_stylesheet_directory() . '/wp-job-manager/' . $template_name;

    return ( in_array($template_name, $custom_templates, true) && file_exists($custom_path) )
        ? $custom_path
        : $template;

}, 10, 2);


// =========================================================
// 3) Helper: haal filterwaarde op uit GET/POST
// =========================================================
if ( ! function_exists('equitee_get_req_value') ) {
    function equitee_get_req_value($key) {
        $filter_key = 'filter_' . $key;

        if (!empty($_GET[$key]))         return (array) $_GET[$key];
        if (!empty($_GET[$filter_key]))  return (array) $_GET[$filter_key];
        if (!empty($_POST[$filter_key])) return (array) $_POST[$filter_key];
        if (!empty($_POST[$key]))        return (array) $_POST[$key];

        return [];
    }
}


// =========================================================
// 4) Custom taxonomieën (job_listing)
// =========================================================
add_action('init', function () {

    register_taxonomy('job_company', 'job_listing', [
        'label'             => 'Organisaties',
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'organisatie'],
    ]);

    register_taxonomy('job_sector', 'job_listing', [
        'label'             => 'Sectoren',
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'sector'],
    ]);

    register_taxonomy('job_regio', 'job_listing', [
        'label'             => 'Regio',
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'regio'],
    ]);

});


// =========================================================
// 5) Taxonomieën ook koppelen aan pages
// =========================================================
add_action('init', function () {
    register_taxonomy_for_object_type('job_company', 'page');
    register_taxonomy_for_object_type('job_sector', 'page');
    register_taxonomy_for_object_type('job_regio', 'page');
});


// =========================================================
// 6) WPJM shortcode defaults
// =========================================================
add_filter('job_manager_output_jobs_defaults', function($defaults) {
    $defaults['job_company']      = '';
    $defaults['job_sector']       = '';
    $defaults['job_regio']        = '';
    $defaults['job_listing_type'] = '';
    return $defaults;
});


// =========================================================
// 7) Shortcode-atts -> tax_query
// =========================================================
add_filter('job_manager_get_listings_shortcode_args', function($atts){
    global $equitee_job_shortcode_atts;
    $equitee_job_shortcode_atts = $atts;

    $custom_filters = [
        'job_company'      => 'job_company',
        'job_sector'       => 'job_sector',
        'job_regio'        => 'job_regio',
        'job_listing_type' => 'job_listing_type',
    ];

    $tax_query = [];

    foreach ($custom_filters as $attr => $taxonomy) {
        if ( ! empty($atts[$attr]) ) {
            $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => array_map('sanitize_title', explode(',', (string) $atts[$attr])),
                'operator' => 'IN',
            ];
        }
    }

    if ( ! empty($tax_query) ) {
        $atts['tax_query'] = $tax_query;
    }

    return $atts;
}, 10, 1);


// =========================================================
// 8) AJAX filterdata + shortcode tax_query
// =========================================================
add_filter('get_job_listings_query_args', function ($query_args, $args) {
    global $equitee_job_shortcode_atts;

    if ( wp_doing_ajax() && isset($_REQUEST['action']) && $_REQUEST['action'] === 'elementor_ajax' ) {
        return $query_args;
    }

    if ( wp_doing_ajax() ) {
        $ajax_action = $_REQUEST['action'] ?? '';
        if ( $ajax_action && $ajax_action !== 'job_manager_get_listings' ) {
            return $query_args;
        }
    }

    if ( ! isset($query_args['tax_query']) || ! is_array($query_args['tax_query']) ) {
        $query_args['tax_query'] = [];
    }

    if ( isset($_POST['form_data']) ) {
        parse_str($_POST['form_data'], $parsed);
        if ( is_array($parsed) ) {
            foreach ($parsed as $key => $value) {
                $_POST[$key] = $value;
            }
        }
    }

    $custom_taxonomies = [
        'filter_job_sector'           => 'job_sector',
        'filter_job_company'          => 'job_company',
        'filter_job_types'            => 'job_listing_type',
        'filter_job_regio'            => 'job_regio',
        'filter_job_listing_category' => 'job_listing_category',
    ];

    foreach ($custom_taxonomies as $filter_key => $taxonomy) {
        if ( ! empty($_POST[$filter_key]) ) {
            $terms = array_map('sanitize_title', (array) $_POST[$filter_key]);
            $query_args['tax_query'][] = [
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $terms,
                'operator' => 'IN',
            ];
        }
    }

    if ( ! empty($equitee_job_shortcode_atts) && empty($_POST['form_data']) ) {
        foreach ($custom_taxonomies as $filter_key => $taxonomy) {
            $key = str_replace('filter_', '', $filter_key);
            if ( ! empty($equitee_job_shortcode_atts[$key]) ) {
                $terms = array_map('sanitize_title', explode(',', sanitize_text_field($equitee_job_shortcode_atts[$key])));
                $query_args['tax_query'][] = [
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => $terms,
                    'operator' => 'IN',
                ];
            }
        }
    }

    return $query_args;
}, 10, 2);


// =========================================================
// 9) Breadcrumb separator (Yoast)
// =========================================================
add_filter('wpseo_breadcrumb_separator', function($separator) {
    return ' / ';
});


// =========================================================
// 10) Geolocation radius
// =========================================================
add_filter('job_manager_geolocation_default_radius', function() {
    return 50;
});


// =========================================================
// 11) Helper functies
// =========================================================
if ( ! function_exists('display_tax_terms') ) {
    function display_tax_terms($tax, $post_id) {
        $terms = wp_get_post_terms($post_id, $tax, ['fields' => 'names']);
        return implode(', ', $terms);
    }
}
