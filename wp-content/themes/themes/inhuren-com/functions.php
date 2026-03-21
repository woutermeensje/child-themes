<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * ✅ CUSTOM HEADER: vervang Hello Elementor's standaard header
 */
add_filter('hello_elementor_page_header_enabled', '__return_false');
add_action('hello_elementor_before_header', function () {
    include get_stylesheet_directory() . '/template-parts/header.php';
});

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
    if (file_exists(get_stylesheet_directory() . '/css/header.css')) {
        wp_enqueue_style('inhuren-header', get_stylesheet_directory_uri() . '/css/header.css', ['child-style'], wp_get_theme()->get('Version'));
    }
    if (file_exists(get_stylesheet_directory() . '/css/gravity-forms.css')) {
        wp_enqueue_style('child-gf-styles', get_stylesheet_directory_uri() . '/css/gravity-forms.css');
    }
    wp_enqueue_style('inhuren-elementor-forms', get_stylesheet_directory_uri() . '/css/elementor-forms.css', ['child-style'], filemtime(get_stylesheet_directory() . '/css/elementor-forms.css'));
});

if (!class_exists('RN_Nav_Walker')) :
class RN_Nav_Walker extends Walker_Nav_Menu {
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? [] : (array) $item->classes;
        $has_child = in_array('menu-item-has-children', $classes, true);
        $is_active = in_array('current-menu-item', $classes, true) || in_array('current-menu-ancestor', $classes, true);
        $li_class = 'rn-nav__item';

        if ($has_child) {
            $li_class .= ' rn-nav__item--has-children';
        }

        if ($is_active) {
            $li_class .= ' is-active';
        }

        $output .= '<li class="' . esc_attr($li_class) . '">';

        $url = !empty($item->url) ? $item->url : '#';
        $title = apply_filters('the_title', $item->title, $item->ID);
        $attr_title = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $target = !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $rel = !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';

        $output .= '<a class="rn-nav__link' . ($is_active ? ' is-active' : '') . '" href="' . esc_url($url) . '"' . $attr_title . $target . $rel . '>';
        $output .= esc_html($title);

        if ($has_child) {
            $output .= '<span class="rn-nav__chev" aria-hidden="true"></span>';
        }

        $output .= '</a>';
    }

    public function start_lvl(&$output, $depth = 0, $args = null) {
        $output .= '<ul class="rn-nav__dropdown">';
    }

    public function end_lvl(&$output, $depth = 0, $args = null) {
        $output .= '</ul>';
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= '</li>';
    }
}
endif;

add_action('after_setup_theme', function () {
    register_nav_menus([
        'primary_nav' => 'Primaire navigatie',
        'footer_nav'  => 'Footer navigatie',
    ]);
});

add_shortcode('rn_header', function () {
    ob_start();
    include get_stylesheet_directory() . '/template-parts/header.php';
    return ob_get_clean();
});

/**
 * ✅ WPJM HELPER FUNCTIES
 */
if ( ! function_exists('srmb_get_req_value') ) {
    function srmb_get_req_value($key) {
        $filter_key = 'filter_' . $key;

        if (!empty($_GET[$key]))         return (array) $_GET[$key];
        if (!empty($_GET[$filter_key]))  return (array) $_GET[$filter_key];
        if (!empty($_POST[$filter_key])) return (array) $_POST[$filter_key];
        if (!empty($_POST[$key]))        return (array) $_POST[$key];

        return [];
    }
}

if ( ! function_exists('display_tax_terms') ) {
    function display_tax_terms($tax, $post_id) {
        $terms = wp_get_post_terms($post_id, $tax, ['fields' => 'names']);
        return !empty($terms) && !is_wp_error($terms) ? implode(', ', $terms) : '';
    }
}

if ( ! function_exists('get_secondary_imageurl') ) {
    function get_secondary_imageurl($post_id) {
        $image_id = get_post_meta($post_id, '_uncode_secondary_thumbnail_id', true);
        return $image_id ? wp_get_attachment_image_url($image_id, 'large') : '';
    }
}

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
