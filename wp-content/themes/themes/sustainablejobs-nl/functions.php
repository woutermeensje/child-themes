<?php
if (!defined('ABSPATH')) exit;

require_once get_stylesheet_directory() . '/inc/shortcode-vacature-formulier.php';
require_once get_stylesheet_directory() . '/inc/shortcode-tarieven.php';
require_once get_stylesheet_directory() . '/includes/blog-meta.php';
require_once get_stylesheet_directory() . '/inc/shortcode-snel-plaatsen.php';
require_once get_stylesheet_directory() . '/inc/vacature-cpt.php';
require_once get_stylesheet_directory() . '/inc/job-listing-meta.php';

/**
 * Centrale afzender voor uitgaande mails vanuit het thema.
 */
add_filter('wp_mail_from', function ($from_email) {
    return 'support@sustainablejobs.nl';
});

add_filter('wp_mail_from_name', function ($from_name) {
    return 'Sustainablejobs.nl';
});

/**
 * Sta meerdere job types per vacature toe in WP Job Manager.
 */
add_filter('job_manager_multi_job_type', '__return_true');

/**
 * ✅ ENQUEUE STYLES (with Elementor check + cache busting)
 */
add_action('wp_enqueue_scripts', function () {
    $dependencies = ['parent-style'];

    if (did_action('elementor/loaded') && wp_style_is('elementor-frontend', 'registered')) {
        $dependencies[] = 'elementor-frontend';
    }

    // Parent theme CSS
    wp_enqueue_style(
        'parent-style',
        get_template_directory_uri() . '/style.css',
        [],
        filemtime(get_template_directory() . '/style.css')
    );

    // Child theme main CSS met automatische versie
    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        $dependencies,
        filemtime(get_stylesheet_directory() . '/style.css')
    );

    foreach (['elementor-icons-fa-solid', 'elementor-icons-fa-regular', 'elementor-icons-fa-brands'] as $handle) {
        if (wp_style_is($handle, 'registered')) {
            wp_enqueue_style($handle);
        }
    }

    // Google Fonts
    wp_enqueue_style('poppins-font', 'https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap', [], null);
    wp_enqueue_style('inter-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', [], null);

    // Custom fonts
    wp_enqueue_style(
        'custom-fonts',
        get_stylesheet_directory_uri() . '/fonts/fonts.css',
        [],
        filemtime(get_stylesheet_directory() . '/fonts/fonts.css')
    );

    // Gravity Forms styling
    wp_enqueue_style(
        'child-gf-styles',
        get_stylesheet_directory_uri() . '/css/gravity-forms.css',
        [],
        filemtime(get_stylesheet_directory() . '/css/gravity-forms.css')
    );

    // Header CSS
    if (file_exists(get_stylesheet_directory() . '/css/header.css')) {
        wp_enqueue_style(
            'sj-header',
            get_stylesheet_directory_uri() . '/css/header.css',
            ['child-style'],
            filemtime(get_stylesheet_directory() . '/css/header.css')
        );
    }

    // Elementor Forms styling
    wp_enqueue_style(
        'sj-elementor-forms',
        get_stylesheet_directory_uri() . '/css/elementor-forms.css',
        ['child-style'],
        filemtime(get_stylesheet_directory() . '/css/elementor-forms.css')
    );

    // Formulieren styling (vacature plaatsen, etc.)
    if (file_exists(get_stylesheet_directory() . '/css/forms.css')) {
        wp_enqueue_style(
            'sj-forms',
            get_stylesheet_directory_uri() . '/css/forms.css',
            ['child-style'],
            filemtime(get_stylesheet_directory() . '/css/forms.css')
        );
    }

    // Blog CSS (overzicht én single posts)
    if ( ( is_home() || is_archive() || is_singular( 'post' ) ) && file_exists( get_stylesheet_directory() . '/css/blog.css' ) ) {
        wp_enqueue_style(
            'sj-blog',
            get_stylesheet_directory_uri() . '/css/blog.css',
            ['child-style'],
            filemtime( get_stylesheet_directory() . '/css/blog.css' )
        );
    }

    // Quill.js rich text editor
    wp_enqueue_style('quill-snow', 'https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css', [], null);
    wp_enqueue_script('quill-js', 'https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js', [], null, true);
    wp_enqueue_script(
        'sj-elementor-forms',
        get_stylesheet_directory_uri() . '/js/elementor-forms.js',
        ['quill-js'],
        filemtime(get_stylesheet_directory() . '/js/elementor-forms.js'),
        true
    );
});

/**
 * Nav Walker voor uitklapbare navigatie
 */
if (!class_exists('SJ_Nav_Walker')) :
class SJ_Nav_Walker extends Walker_Nav_Menu {
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes    = empty($item->classes) ? [] : (array) $item->classes;
        $has_child  = in_array('menu-item-has-children', $classes, true);
        $is_active  = in_array('current-menu-item', $classes, true) || in_array('current-menu-ancestor', $classes, true);
        $li_class   = 'rn-nav__item';

        if ($has_child) $li_class .= ' rn-nav__item--has-children';
        if ($is_active) $li_class .= ' is-active';

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

/**
 * Theme setup: logo support, menu registratie
 */
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

/**
 * Shortcode [sj_header] voor gebruik in Elementor
 */
add_shortcode('sj_header', function () {
    ob_start();
    include get_stylesheet_directory() . '/template-parts/header.php';
    return ob_get_clean();
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
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'tag'],
    ]);

    register_taxonomy('job_sector', 'job_listing', [
        'label' => 'Sectors',
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'sector'],
    ]);

    register_taxonomy('certificering', 'job_listing', [
        'label' => 'Certificeringen',
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'certificering'],
    ]);
});



/**
 * ✅ Koppel WP Job Manager taxonomieën aan pages
 */
add_action('init', function () {
    register_taxonomy_for_object_type('job_company', 'page');
    register_taxonomy_for_object_type('job_tag', 'page');
    register_taxonomy_for_object_type('job_sector', 'page');
    register_taxonomy_for_object_type('certificering', 'page');
});


/**
 * ✅ Shortcode filters: [jobs job_company="bowers" job_sector="klimaatadaptatie"]
 */
add_filter('job_manager_get_listings_shortcode_args', function($atts){
    global $sj_job_shortcode_atts;
    $sj_job_shortcode_atts = $atts;

    $custom_filters = [
        'job_company'       => 'job_company',
        'job_tag'           => 'job_tag',
        'job_sector'        => 'job_sector',
        'certificering'     => 'certificering',
        'job_listing_type'  => 'job_listing_type',
    ];

    $tax_query = [];

    foreach ($custom_filters as $attr => $taxonomy) {
        if (!empty($atts[$attr])) {
            $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => array_map('sanitize_title', explode(',', $atts[$attr])),
                'operator' => 'IN',
            ];
        }
    }

    if (!empty($tax_query)) {
        $atts['tax_query'] = $tax_query;
    }

    return $atts;
}, 10, 1);

/**
 * ✅ Combine AJAX filterdata + shortcode tax_query
 */
add_filter('get_job_listings_query_args', function ($query_args, $args) {
    global $sj_job_shortcode_atts;

    if (isset($_POST['form_data'])) {
        parse_str($_POST['form_data'], $parsed);
        foreach ($parsed as $key => $value) {
            $_POST[$key] = $value;
        }
        error_log('🧩 Parsed form_data: ' . print_r($parsed, true));
    }

    error_log('🔍 WPJM POST filterdata: ' . print_r($_POST, true));

    $custom_taxonomies = [
        'filter_job_tag'       => 'job_tag',
        'filter_job_sector'    => 'job_sector',
        'filter_job_company'   => 'job_company',
        'filter_job_types'     => 'job_listing_type',
        'filter_certificering' => 'certificering',
        'filter_job_listing_category' => 'job_listing_category',

    ];

    foreach ($custom_taxonomies as $filter_key => $taxonomy) {
        if (!empty($_POST[$filter_key])) {
            $terms = (array) $_POST[$filter_key];
            $terms = array_map('sanitize_title', $terms);

            $query_args['tax_query'][] = [
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $terms,
                'operator' => 'IN',
            ];
        }
    }

    if (!empty($sj_job_shortcode_atts) && empty($_POST['form_data'])) {
        foreach ($custom_taxonomies as $filter_key => $taxonomy) {
            $key = str_replace('filter_', '', $filter_key);
            if (!empty($sj_job_shortcode_atts[$key])) {
                $terms = explode(',', sanitize_text_field($sj_job_shortcode_atts[$key]));
                $query_args['tax_query'][] = [
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => $terms,
                    'operator' => 'IN',
                ];
            }
        }
    }

    if (!empty($query_args['tax_query'])) {
        error_log('📦 TAX_QUERY in get_job_listings_query_args: ' . print_r($query_args['tax_query'], true));
    } else {
        error_log('📭 Geen tax_query aanwezig in get_job_listings_query_args');
    }

    return $query_args;
}, 10, 2);


// Add custom default attributes to the jobs shortcode
add_filter('job_manager_output_jobs_defaults', function($defaults) {
    $defaults['job_company'] = '';
    $defaults['job_tag'] = '';
    $defaults['job_sector'] = '';
    $defaults['certificering'] = '';
    $defaults['job_listing_type'] = '';
    
    return $defaults;
});

/** Import functions */ 

require_once get_stylesheet_directory() . '/inc/bowers-import.php';
require_once get_stylesheet_directory() . '/inc/arcadis-import.php';
require_once get_stylesheet_directory() . '/inc/jackling-import.php';


require_once get_stylesheet_directory() . '/inc/bedrijfspagina-filters.php';
