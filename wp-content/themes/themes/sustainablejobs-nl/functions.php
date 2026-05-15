<?php
if (!defined('ABSPATH')) exit;

require_once get_stylesheet_directory() . '/inc/shortcode-vacature-formulier.php';
require_once get_stylesheet_directory() . '/inc/activecampaign.php';
require_once get_stylesheet_directory() . '/inc/shortcode-job-alerts.php';
require_once get_stylesheet_directory() . '/inc/job-alerts-cron.php';
require_once get_stylesheet_directory() . '/inc/job-alerts-admin.php';
require_once get_stylesheet_directory() . '/inc/newsletter-cron.php';
require_once get_stylesheet_directory() . '/inc/shortcode-newsletter.php';
require_once get_stylesheet_directory() . '/inc/shortcode-info-aanvraag.php';
require_once get_stylesheet_directory() . '/inc/shortcode-uitgelichte-werkgevers.php';
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

// Automatische filter-URL's (/vacatures/{slug}/) zijn uitgeschakeld.
// Rewrite rules worden geflushed zodat de oude regel verdwijnt.
add_action('init', function () {
    if (get_option('sj_rewrite_disabled_flush') === '2026-05-14') {
        return;
    }
    flush_rewrite_rules(false);
    update_option('sj_rewrite_disabled_flush', '2026-05-14', false);
}, 99);

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
 * WP Job Manager gebruikt de post thumbnail standaard als bedrijfslogo.
 * Voor de Fondsen-style vacaturekaart houden we logo en cover expliciet gescheiden.
 */
if (!function_exists('sj_get_image_url')) {
    function sj_get_image_url($image, $size = 'full') {
        if (empty($image)) {
            return '';
        }

        if (is_array($image)) {
            if (!empty($image['url'])) {
                return esc_url_raw($image['url']);
            }

            foreach ($image as $candidate) {
                $url = sj_get_image_url($candidate, $size);
                if ($url) {
                    return $url;
                }
            }

            return '';
        }

        if (is_numeric($image)) {
            return wp_get_attachment_image_url((int) $image, $size)
                ?: wp_get_attachment_image_url((int) $image, 'full')
                ?: '';
        }

        if (is_string($image)) {
            return esc_url_raw(trim($image));
        }

        return '';
    }
}

if (!function_exists('sj_get_company_logo_url')) {
    function sj_get_company_logo_url($post_id = null, $size = 'thumbnail') {
        $post_id = $post_id ?: get_the_ID();

        if (!$post_id) {
            return '';
        }

        $logo_url = sj_get_image_url(get_post_meta($post_id, '_company_logo', true), $size);

        if (!$logo_url && !sj_get_image_url(get_post_meta($post_id, '_cover_image', true), 'full')) {
            $logo_url = get_the_post_thumbnail_url($post_id, $size) ?: '';
        }

        return $logo_url;
    }
}

if (!function_exists('sj_the_company_logo')) {
    function sj_the_company_logo($post_id = null, $size = 'thumbnail') {
        $post_id  = $post_id ?: get_the_ID();
        $logo_url = sj_get_company_logo_url($post_id, $size);

        if (!$logo_url) {
            return;
        }

        $company_name = function_exists('get_the_company_name')
            ? get_the_company_name($post_id)
            : get_the_title($post_id);

        printf(
            '<img class="company_logo" src="%s" alt="%s" />',
            esc_url($logo_url),
            esc_attr($company_name)
        );
    }
}

add_filter('submit_job_form_fields', function ($fields) {
    $fields['job']['cover_image'] = [
        'label'       => __('Uitgelichte afbeelding / cover', 'job_manager'),
        'type'        => 'file',
        'accept'      => 'image/png, image/jpeg, image/webp',
        'required'    => false,
        'priority'    => 7,
        'description' => __('Gebruik dit veld voor de grote afbeelding op de vacaturekaart. Het bedrijfslogo blijft apart.', 'job_manager'),
    ];

    return $fields;
});

add_filter('job_manager_job_listing_data_fields', function ($fields) {
    $fields['_cover_image'] = [
        'label'       => __('Uitgelichte afbeelding / cover', 'job_manager'),
        'type'        => 'file',
        'description' => __('Gebruik dit veld voor de grote afbeelding op de vacaturekaart. Het bedrijfslogo blijft apart.', 'job_manager'),
    ];

    return $fields;
});

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

    register_taxonomy('organisatie_type', 'job_listing', [
        'label'             => 'Type organisatie',
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'organisatie-type'],
    ]);
});



/**
 * ✅ Koppel WP Job Manager taxonomieën aan pages
 */
add_action('init', function () {
    register_taxonomy_for_object_type('job_company', 'page');
    register_taxonomy_for_object_type('job_tag', 'page');
    register_taxonomy_for_object_type('job_sector', 'page');
    register_taxonomy_for_object_type('organisatie_type', 'page');
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
        'organisatie_type'  => 'organisatie_type',
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
        'filter_job_tag'       => 'job_tag',
        'filter_job_sector'    => 'job_sector',
        'filter_job_company'   => 'job_company',
        'filter_job_types'     => 'job_listing_type',
        'filter_organisatie_type' => 'organisatie_type',
        'filter_job_listing_category' => 'job_listing_category',

    ];

    foreach ($custom_taxonomies as $filter_key => $taxonomy) {
        $request_terms = [];

        if (!empty($_POST[$filter_key])) {
            $request_terms = (array) wp_unslash($_POST[$filter_key]);
        } elseif (!empty($_GET[$filter_key])) {
            $request_terms = (array) wp_unslash($_GET[$filter_key]);
        }

        if (!empty($request_terms)) {
            $terms = array_map('sanitize_title', $request_terms);

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
                $terms = array_map('sanitize_title', $terms);
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


// Add custom default attributes to the jobs shortcode
add_filter('job_manager_output_jobs_defaults', function($defaults) {
    $defaults['job_company'] = '';
    $defaults['job_tag'] = '';
    $defaults['job_sector'] = '';
    $defaults['organisatie_type'] = '';
    $defaults['job_listing_type'] = '';
    
    return $defaults;
});

/**
 * Seed default terms for organisatie_type (runs once).
 */
add_action('init', function () {
    if (get_option('sj_organisatie_type_seeded')) return;
    $terms = ['Stichting', 'NGO', 'Gemeente', 'Provincie', 'Overheid', 'Semi-Overheid', 'MKB', 'Fonds', 'Non Profit'];
    foreach ($terms as $term) {
        if (!term_exists($term, 'organisatie_type')) {
            wp_insert_term($term, 'organisatie_type');
        }
    }
    update_option('sj_organisatie_type_seeded', true);
});

// Rename WP JM "Load more listings" button to Dutch.
add_filter('gettext', function ($translated, $text, $domain) {
    if ($domain === 'wp-job-manager' && $text === 'Load more listings') {
        return 'Toon meer vacatures';
    }
    return $translated;
}, 10, 3);

/** Import functions */

require_once get_stylesheet_directory() . '/inc/bowers-import.php';
require_once get_stylesheet_directory() . '/inc/arcadis-import.php';
require_once get_stylesheet_directory() . '/inc/jackling-import.php';


require_once get_stylesheet_directory() . '/inc/bedrijfspagina-filters.php';

