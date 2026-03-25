<?php
if ( ! defined('ABSPATH') ) exit;

add_theme_support('job-manager-templates');

// =========================================================
// 1) Styles en fonts
// =========================================================
add_action('wp_enqueue_scripts', function () {

    wp_enqueue_style('parent-style',  get_template_directory_uri() . '/style.css', [], filemtime(get_template_directory() . '/style.css'));
    wp_enqueue_style('child-style',   get_stylesheet_directory_uri() . '/style.css', ['parent-style'], filemtime(get_stylesheet_directory() . '/style.css'));

    wp_enqueue_style('poppins-font',  'https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap', [], null);
    wp_enqueue_style('inter-font',    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', [], null);

    if (file_exists(get_stylesheet_directory() . '/css/header.css')) {
        wp_enqueue_style('ivb-header', get_stylesheet_directory_uri() . '/css/header.css', ['child-style'], filemtime(get_stylesheet_directory() . '/css/header.css'));
    }

    if (file_exists(get_stylesheet_directory() . '/css/forms.css')) {
        wp_enqueue_style('ivb-forms', get_stylesheet_directory_uri() . '/css/forms.css', ['child-style'], filemtime(get_stylesheet_directory() . '/css/forms.css'));
    }

    wp_enqueue_style('quill-snow', 'https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css', [], '2.0.2');
    wp_enqueue_script('quill-js', 'https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js', [], '2.0.2', true);
});

// =========================================================
// 2) Nav Walker
// =========================================================
if (!class_exists('IVB_Nav_Walker')) :
class IVB_Nav_Walker extends Walker_Nav_Menu {
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes    = empty($item->classes) ? [] : (array) $item->classes;
        $has_child  = in_array('menu-item-has-children', $classes, true);
        $is_active  = in_array('current-menu-item', $classes, true) || in_array('current-menu-ancestor', $classes, true);
        $li_class   = 'rn-nav__item' . ($has_child ? ' rn-nav__item--has-children' : '') . ($is_active ? ' is-active' : '');

        $output .= '<li class="' . esc_attr($li_class) . '">';

        $url        = !empty($item->url) ? $item->url : '#';
        $title      = apply_filters('the_title', $item->title, $item->ID);
        $attr_title = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $target     = !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $rel        = !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';

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

// =========================================================
// 3) Theme setup
// =========================================================
add_action('after_setup_theme', function () {
    add_theme_support('custom-logo', [
        'height'      => 120,
        'width'       => 320,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    register_nav_menus([
        'primary_nav' => 'Primaire navigatie',
        'footer_nav'  => 'Footer navigatie',
    ]);
});

// =========================================================
// 4) WP Job Manager template overrides
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

    $custom_path = get_stylesheet_directory() . '/job_manager/' . $template_name;

    return (in_array($template_name, $custom_templates, true) && file_exists($custom_path))
        ? $custom_path
        : $template;
}, 10, 2);

// =========================================================
// 5) Custom taxonomieën
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
        'label'             => 'Sector',
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'sector'],
    ]);

    register_taxonomy('vakgebied', ['job_listing'], [
        'label'             => 'Vakgebied',
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => ['slug' => 'vakgebied'],
    ]);
});

// =========================================================
// 6) WPJM shortcode defaults
// =========================================================
add_filter('job_manager_output_jobs_defaults', function($defaults) {
    $defaults['job_company']      = '';
    $defaults['job_sector']       = '';
    $defaults['vakgebied']        = '';
    $defaults['job_listing_type'] = '';
    return $defaults;
});

// =========================================================
// 7) Shortcode filters → tax_query
// =========================================================
add_filter('job_manager_get_listings_shortcode_args', function($atts) {
    global $ivb_job_shortcode_atts;
    $ivb_job_shortcode_atts = $atts;

    $custom_filters = [
        'job_company'      => 'job_company',
        'job_sector'       => 'job_sector',
        'vakgebied'        => 'vakgebied',
        'job_listing_type' => 'job_listing_type',
    ];

    $tax_query = [];
    foreach ($custom_filters as $attr => $taxonomy) {
        if (!empty($atts[$attr])) {
            $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => array_map('sanitize_title', explode(',', (string) $atts[$attr])),
                'operator' => 'IN',
            ];
        }
    }

    if (!empty($tax_query)) {
        $atts['tax_query'] = $tax_query;
    }

    return $atts;
}, 10, 1);

// =========================================================
// 8) AJAX filters + shortcode tax_query combineren
// =========================================================
add_filter('get_job_listings_query_args', function ($query_args, $args) {
    global $ivb_job_shortcode_atts;

    if (wp_doing_ajax() && isset($_REQUEST['action']) && $_REQUEST['action'] === 'elementor_ajax') {
        return $query_args;
    }

    if (wp_doing_ajax()) {
        $ajax_action = $_REQUEST['action'] ?? '';
        if ($ajax_action && $ajax_action !== 'job_manager_get_listings') {
            return $query_args;
        }
    }

    if (!isset($query_args['tax_query']) || !is_array($query_args['tax_query'])) {
        $query_args['tax_query'] = [];
    }

    if (isset($_POST['form_data'])) {
        parse_str($_POST['form_data'], $parsed);
        if (is_array($parsed)) {
            foreach ($parsed as $key => $value) {
                $_POST[$key] = $value;
            }
        }
    }

    $custom_taxonomies = [
        'filter_job_company'  => 'job_company',
        'filter_job_sector'   => 'job_sector',
        'filter_vakgebied'    => 'vakgebied',
        'filter_job_types'    => 'job_listing_type',
    ];

    foreach ($custom_taxonomies as $filter_key => $taxonomy) {
        if (!empty($_POST[$filter_key])) {
            $terms = array_map('sanitize_title', (array) $_POST[$filter_key]);
            $query_args['tax_query'][] = [
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $terms,
                'operator' => 'IN',
            ];
        }
    }

    if (!empty($ivb_job_shortcode_atts) && empty($_POST['form_data'])) {
        foreach ($custom_taxonomies as $filter_key => $taxonomy) {
            $key = str_replace('filter_', '', $filter_key);
            if (!empty($ivb_job_shortcode_atts[$key])) {
                $terms = array_map('sanitize_title', explode(',', sanitize_text_field($ivb_job_shortcode_atts[$key])));
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
// 9) Helper functies
// =========================================================
if (!function_exists('srmb_get_req_value')) {
    function srmb_get_req_value($key) {
        $filter_key = 'filter_' . $key;
        if (!empty($_GET[$key]))         return (array) $_GET[$key];
        if (!empty($_GET[$filter_key]))  return (array) $_GET[$filter_key];
        if (!empty($_POST[$filter_key])) return (array) $_POST[$filter_key];
        if (!empty($_POST[$key]))        return (array) $_POST[$key];
        return [];
    }
}

if (!function_exists('display_tax_terms')) {
    function display_tax_terms($tax, $post_id) {
        $terms = wp_get_post_terms($post_id, $tax, ['fields' => 'names']);
        return implode(', ', $terms);
    }
}

// =========================================================
// 10) Cover image veld
// =========================================================
add_filter('submit_job_form_fields', function($fields) {
    $fields['job']['cover_image'] = [
        'label'    => __('Cover afbeelding', 'job_manager'),
        'type'     => 'file',
        'accept'   => 'image/png, image/jpeg',
        'required' => false,
        'priority' => 7,
    ];
    return $fields;
});

add_filter('job_manager_job_listing_data_fields', function($fields) {
    $fields['_cover_image'] = [
        'label' => __('Cover afbeelding', 'job_manager'),
        'type'  => 'file',
    ];
    return $fields;
});

// =========================================================
// 11) Shortcodes & CPTs
// =========================================================
require_once get_stylesheet_directory() . '/inc/vacature-cpt.php';
require_once get_stylesheet_directory() . '/inc/shortcode-vacature-plaatsen.php';

// =========================================================
// 12) Yoast breadcrumb separator
// =========================================================
add_filter('wpseo_breadcrumb_separator', function($sep) {
    return ' / ';
});
