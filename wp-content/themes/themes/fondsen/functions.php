<?php
/**
 * Fondsen.org Child Theme – functions.php (opgeschoond + fixes)
 * - ✅ Dubbele WPJM filters verwijderd (stonden 2x in je file)
 * - ✅ Elementor publish/save 500 fix: WPJM query filter draait niet meer mee bij elementor_ajax
 * - ✅ tax_query altijd als array initialiseren
 * - ✅ Debug logging optioneel (uit te zetten)
 */

if ( ! defined('ABSPATH') ) exit;

require_once get_stylesheet_directory() . '/inc/shortcode-vacature-formulier.php';
require_once get_stylesheet_directory() . '/inc/vacature-cpt.php';

add_filter('wp_mail_from', function () {
    return 'informatie@fondsen.org';
});

add_filter('wp_mail_from_name', function () {
    return 'Fondsen.org';
});

add_filter('job_manager_multi_job_type', '__return_true');

// =========================================================
// 1) Include extra WPJM functions (als je die file gebruikt)
// =========================================================
$jm_functions = get_stylesheet_directory() . '/job_manager/functions.php';
if ( file_exists($jm_functions) ) {
    require_once $jm_functions;
}

require_once get_stylesheet_directory() . '/includes/blog-meta.php';

add_theme_support('job-manager-templates');


// =========================================================
// 2) Styles en fonts
// =========================================================
function fondsen_enqueue_styles() {

    // Parent theme CSS (Hello Elementor)
    wp_enqueue_style(
        'parent-style',
        get_template_directory_uri() . '/style.css',
        [],
        filemtime( get_template_directory() . '/style.css' )
    );

    // Child theme main CSS (laadt NA parent)
    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        ['parent-style'],
        filemtime( get_stylesheet_directory() . '/style.css' )
    );

    // Google Fonts
    wp_enqueue_style(
        'poppins-font',
        'https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap',
        [],
        null
    );

    wp_enqueue_style(
        'inter-font',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
        [],
        null
    );

    if (file_exists(get_stylesheet_directory() . '/css/header.css')) {
        wp_enqueue_style(
            'fondsen-header',
            get_stylesheet_directory_uri() . '/css/header.css',
            ['child-style'],
            filemtime(get_stylesheet_directory() . '/css/header.css')
        );
    }

    // Blog CSS (overzicht én single posts)
    if ( ( is_home() || is_archive() || is_singular( 'post' ) ) && file_exists( get_stylesheet_directory() . '/css/blog.css' ) ) {
        wp_enqueue_style(
            'fondsen-blog',
            get_stylesheet_directory_uri() . '/css/blog.css',
            ['child-style'],
            filemtime( get_stylesheet_directory() . '/css/blog.css' )
        );
    }

    wp_enqueue_style(
        'fondsen-elementor-forms',
        get_stylesheet_directory_uri() . '/css/elementor-forms.css',
        ['child-style'],
        filemtime(get_stylesheet_directory() . '/css/elementor-forms.css')
    );

    if (file_exists(get_stylesheet_directory() . '/css/forms.css')) {
        wp_enqueue_style(
            'fondsen-forms',
            get_stylesheet_directory_uri() . '/css/forms.css',
            ['child-style'],
            filemtime(get_stylesheet_directory() . '/css/forms.css')
        );
    }

    wp_enqueue_style(
        'fondsen-quill',
        'https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css',
        [],
        null
    );

    wp_enqueue_script(
        'fondsen-quill',
        'https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js',
        [],
        null,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'fondsen_enqueue_styles' );

if (!class_exists('Fondsen_Nav_Walker')) :
class Fondsen_Nav_Walker extends Walker_Nav_Menu {
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

add_shortcode('fondsen_header', function () {
    ob_start();
    include get_stylesheet_directory() . '/template-parts/header.php';
    return ob_get_clean();
});


if ( ! function_exists('srmb_get_req_value') ) {
    function srmb_get_req_value($key) {
        $filter_key = 'filter_' . $key;

        if (!empty($_GET[$key])) {
            return (array) $_GET[$key];
        }

        if (!empty($_GET[$filter_key])) {
            return (array) $_GET[$filter_key];
        }

        if (!empty($_POST[$filter_key])) {
            return (array) $_POST[$filter_key];
        }

        if (!empty($_POST[$key])) {
            return (array) $_POST[$key];
        }

        return [];
    }
}




// =========================================================
// 3) WP Job Manager template overrides
// =========================================================
add_filter('job_manager_locate_template', function ($template, $template_name) {
    $custom_templates = [
        'content-job_listing.php',
        'content-single-job_listing.php',
        'job-filters.php',
        'job-filter-job-types.php',
        'job-listings-start.php',
        'job-listings-end.php',
        'job-submit.php',
        // 'functions.php',  // <- dit is géén WPJM template; beter NIET overriden
    ];

    $candidate_paths = [
        get_stylesheet_directory() . '/wp-job-manager/' . $template_name,
        get_stylesheet_directory() . '/job_manager/' . $template_name,
    ];

    $custom_path = '';
    foreach ($candidate_paths as $candidate_path) {
        if (file_exists($candidate_path)) {
            $custom_path = $candidate_path;
            break;
        }
    }

    return ( in_array($template_name, $custom_templates, true) && $custom_path )
        ? $custom_path
        : $template;

}, 10, 2);


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

    register_taxonomy('organization_type', 'job_listing', [
        'label'             => 'Organization Type',
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'organization_type'],
    ]);

    register_taxonomy('job_sector', 'job_listing', [
        'label'             => 'Sectors',
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'sector'],
    ]);

    register_taxonomy('certificering', 'job_listing', [
        'label'             => 'Certificeringen',
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'certificering'],
    ]);

});


// =========================================================
// 5) Taxonomieën ook koppelen aan pages (voor organisatie pagina’s etc.)
// =========================================================
add_action('init', function () {
    register_taxonomy_for_object_type('job_company', 'page');
    register_taxonomy_for_object_type('job_tag', 'page');
    register_taxonomy_for_object_type('job_sector', 'page');
    register_taxonomy_for_object_type('certificering', 'page');
});


// =========================================================
// 6) WPJM shortcode defaults uitbreiden (zodat args netjes bestaan)
// =========================================================
add_filter('job_manager_output_jobs_defaults', function($defaults) {
    $defaults['job_company']      = '';
    $defaults['job_tag']          = '';
    $defaults['job_sector']       = '';
    $defaults['certificering']    = '';
    $defaults['job_listing_type'] = '';
    return $defaults;
});


// =========================================================
// 7) Shortcode filters -> tax_query (voor [jobs job_company="x"] etc.)
// =========================================================
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
// 8) Combine AJAX filterdata + shortcode tax_query (WPJM listings)
//    ✅ Elementor 500 fix: dit filter draait NIET bij elementor_ajax
// =========================================================
add_filter('get_job_listings_query_args', function ($query_args, $args) {
    global $sj_job_shortcode_atts;

    // ✅ Fix 1: Elementor publish/save calls overslaan (action=elementor_ajax)
    if ( wp_doing_ajax() && isset($_REQUEST['action']) && $_REQUEST['action'] === 'elementor_ajax' ) {
        return $query_args;
    }

    // ✅ Fix 2: Alleen uitvoeren bij WPJM listings AJAX (optioneel maar aanbevolen)
    if ( wp_doing_ajax() ) {
        $ajax_action = $_REQUEST['action'] ?? '';
        if ( $ajax_action && $ajax_action !== 'job_manager_get_listings' ) {
            return $query_args;
        }
    }

    // ✅ Fix 3: tax_query altijd als array
    if ( ! isset($query_args['tax_query']) || ! is_array($query_args['tax_query']) ) {
        $query_args['tax_query'] = [];
    }

    // (OPTIONEEL) Debug logs aan/uit
    $debug = false;

    // WPJM stuurt vaak form_data (serialized)
    if ( isset($_POST['form_data']) ) {
        parse_str($_POST['form_data'], $parsed);

        if ( is_array($parsed) ) {
            foreach ($parsed as $key => $value) {
                $_POST[$key] = $value;
            }
            if ($debug) error_log('🧩 Parsed form_data: ' . print_r($parsed, true));
        }
    }

    if ($debug) error_log('🔍 WPJM POST filterdata: ' . print_r($_POST, true));

    $custom_taxonomies = [
        'filter_job_tag'              => 'job_tag',
        'filter_job_sector'           => 'job_sector',
        'filter_job_company'          => 'job_company',
        'filter_job_types'            => 'job_listing_type',
        'filter_certificering'        => 'certificering',
        'filter_job_listing_category' => 'job_listing_category',
    ];

    // 1) AJAX filters uit POST
    foreach ($custom_taxonomies as $filter_key => $taxonomy) {
        if ( ! empty($_POST[$filter_key]) ) {
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

    // 2) Shortcode-atts toepassen als er géén AJAX form_data is
    if ( ! empty($sj_job_shortcode_atts) && empty($_POST['form_data']) ) {
        foreach ($custom_taxonomies as $filter_key => $taxonomy) {
            $key = str_replace('filter_', '', $filter_key);

            if ( ! empty($sj_job_shortcode_atts[$key]) ) {
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

    if ($debug) {
        if ( ! empty($query_args['tax_query']) ) {
            error_log('📦 TAX_QUERY in get_job_listings_query_args: ' . print_r($query_args['tax_query'], true));
        } else {
            error_log('📭 Geen tax_query aanwezig in get_job_listings_query_args');
        }
    }

    return $query_args;
}, 10, 2);


// =========================================================
// 9) Breadcrumb separator (Yoast)
// =========================================================
add_filter( 'wpseo_breadcrumb_separator', function( $separator ) {
    return ' / ';
});


// =========================================================
// 10) Geolocation radius (WPJM)
// =========================================================
add_filter('job_manager_geolocation_default_radius', function() {
    return 50;
});


// =========================================================
// 11) Category taxonomie aan pages (voor fondsen shortcode)
// =========================================================
function add_categories_to_pages() {
    register_taxonomy_for_object_type('category', 'page');
}
add_action('init', 'add_categories_to_pages');


// =========================================================
// 12) Shortcode [fondsen] (fondsen-oproepen)
// =========================================================
function fondsen_shortcode($atts) {
    ob_start();

    $atts = shortcode_atts([
        'category' => 'fondsen-werven',
    ], $atts);

    $fondsen_query = new WP_Query([
        'post_type'      => 'page',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'category_name'  => sanitize_text_field($atts['category']),
    ]);

    if ($fondsen_query->have_posts()) {
        echo '<div class="fondsen-oproepen">';
        while ($fondsen_query->have_posts()) {
            $fondsen_query->the_post();

            $excerpt = wp_trim_words( wp_strip_all_tags(get_the_content()), 25 );
            $logo    = get_the_post_thumbnail(get_the_ID(), 'medium', ['class' => 'fonds-logo']);

            echo '<div class="fonds-item">';
                echo '<div class="fonds-header">';
                    if ($logo) {
                        echo '<div class="fonds-logo-wrapper">' . $logo . '</div>';
                    }
                    echo '<div class="fonds-text">';
                        echo '<h2 class="fonds-title">' . esc_html(get_the_title()) . '</h2>';
                        echo '<p class="fonds-excerpt">' . esc_html($excerpt) . '</p>';
                        echo '<a class="fonds-button" href="' . esc_url(get_permalink()) . '">Oproep bekijken</a>';
                    echo '</div>';
                echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<p>Er zijn momenteel geen oproepen voor fondsenwerving.</p>';
    }

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('fondsen', 'fondsen_shortcode');


// =========================================================
// 13) Customizer: header knoppen
// =========================================================
function fondsenorg_customize_register( $wp_customize ) {

    $wp_customize->add_section( 'fondsenorg_header_buttons', [
        'title'       => __( 'Header knoppen', 'fondsenorg' ),
        'priority'    => 30,
        'description' => __( 'Stel hier de links en teksten in voor de knoppen in de header.', 'fondsenorg' ),
    ] );

    // Linker knop - URL
    $wp_customize->add_setting( 'fondsenorg_header_left_url', [
        'default'           => '/#vacatures',
        'sanitize_callback' => 'esc_url_raw',
    ] );

    $wp_customize->add_control( 'fondsenorg_header_left_url_control', [
        'label'    => __( 'Linker knop URL', 'fondsenorg' ),
        'section'  => 'fondsenorg_header_buttons',
        'settings' => 'fondsenorg_header_left_url',
        'type'     => 'url',
    ] );

    // Linker knop - tekst
    $wp_customize->add_setting( 'fondsenorg_header_left_text', [
        'default'           => 'Vacatures',
        'sanitize_callback' => 'sanitize_text_field',
    ] );

    $wp_customize->add_control( 'fondsenorg_header_left_text_control', [
        'label'    => __( 'Linker knop tekst', 'fondsenorg' ),
        'section'  => 'fondsenorg_header_buttons',
        'settings' => 'fondsenorg_header_left_text',
        'type'     => 'text',
    ] );

    // Rechter knop - URL
    $wp_customize->add_setting( 'fondsenorg_header_right_url', [
        'default'           => '/job-alerts/',
        'sanitize_callback' => 'esc_url_raw',
    ] );

    $wp_customize->add_control( 'fondsenorg_header_right_url_control', [
        'label'    => __( 'Rechter knop URL', 'fondsenorg' ),
        'section'  => 'fondsenorg_header_buttons',
        'settings' => 'fondsenorg_header_right_url',
        'type'     => 'url',
    ] );

    // Rechter knop - tekst
    $wp_customize->add_setting( 'fondsenorg_header_right_text', [
        'default'           => 'Job alerts',
        'sanitize_callback' => 'sanitize_text_field',
    ] );

    $wp_customize->add_control( 'fondsenorg_header_right_text_control', [
        'label'    => __( 'Rechter knop tekst', 'fondsenorg' ),
        'section'  => 'fondsenorg_header_buttons',
        'settings' => 'fondsenorg_header_right_text',
        'type'     => 'text',
    ] );
}
add_action( 'customize_register', 'fondsenorg_customize_register' );


// =========================================================
// 14) WPJM extras (helpers)
// =========================================================
if ( ! function_exists('display_tax_terms') ) {
    function display_tax_terms($tax, $post_id) {
        $terms = wp_get_post_terms($post_id, $tax, ['fields' => 'names']);
        return implode(',', $terms);
    }
}

if ( ! function_exists('get_secondary_imageurl') ) {
    function get_secondary_imageurl($post_id) {
        $image_id = get_post_meta($post_id, '_uncode_secondary_thumbnail_id', true);
        return wp_get_attachment_image_url($image_id, 'large');
    }
}


// =========================================================
// 15) Tracking: thankyou page view (na vacature geplaatst)
// =========================================================
if ( ! function_exists('create_thankyou_page_pageview') ) {
    add_action('job_manager_job_submitted_content_after', 'create_thankyou_page_pageview');
    function create_thankyou_page_pageview() {
        echo "<script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('event', 'page_view',{
                'page_title': 'Vacature geplaatst',
                'page_location': '/plaats-een-vacature/',
                'page_path': '/plaats-een-vacature/bedankt',
                'send_to': 'G-G3HL6WW75F'
            });
        </script>";
    }
}


// =========================================================
// 16) Extra velden: Indeed / Facebook / LinkedIn
// =========================================================
/** Indeed */
add_filter('submit_job_form_fields', function($fields){
    $fields['company']['company_indeed'] = [
        'label'       => __('Indeed', 'job_manager'),
        'type'        => 'text',
        'required'    => false,
        'placeholder' => 'Indeed link',
        'priority'    => 5
    ];
    return $fields;
});

add_filter('job_manager_job_listing_data_fields', function($fields){
    $fields['_company_indeed'] = [
        'label'       => __('Indeed', 'job_manager'),
        'type'        => 'text',
        'placeholder' => 'Indeed link',
        'description' => ''
    ];
    return $fields;
});

/** Facebook */
add_filter('submit_job_form_fields', function($fields){
    $fields['company']['company_facebook'] = [
        'label'       => __('Facebook', 'job_manager'),
        'type'        => 'text',
        'required'    => false,
        'placeholder' => 'https://facebook.com/your-company',
        'priority'    => 5
    ];
    return $fields;
});

add_filter('job_manager_job_listing_data_fields', function($fields){
    $fields['_company_facebook'] = [
        'label'       => __('Facebook', 'job_manager'),
        'type'        => 'text',
        'placeholder' => 'https://facebook.com/your-company',
        'description' => ''
    ];
    return $fields;
});

/** LinkedIn */
add_filter('submit_job_form_fields', function($fields){
    $fields['company']['company_linkedin'] = [
        'label'       => __('LinkedIn', 'job_manager'),
        'type'        => 'text',
        'required'    => false,
        'placeholder' => 'https://linkedin.com/your-company',
        'priority'    => 5
    ];
    return $fields;
});

add_filter('job_manager_job_listing_data_fields', function($fields){
    $fields['_company_linkedin'] = [
        'label'       => __('LinkedIn', 'job_manager'),
        'type'        => 'text',
        'placeholder' => 'https://linkedin.com/in/your-company',
        'description' => ''
    ];
    return $fields;
});


// =========================================================
// 17) Cover image veld
// =========================================================
add_filter('submit_job_form_fields', function($fields){
    $fields['job']['cover_image'] = [
        'label'    => __('Cover afbeelding', 'job_manager'),
        'type'     => 'file',
        'accept'   => 'image/png, image/jpeg',
        'required' => false,
        'priority' => 7
    ];
    return $fields;
});

add_filter('job_manager_job_listing_data_fields', function($fields){
    $fields['_cover_image'] = [
        'label' => __('Cover afbeelding', 'job_manager'),
        'type'  => 'file',
    ];
    return $fields;
});


// =========================================================
// 18) Contactpersoon velden (front-end + admin + opslag + validatie)
// =========================================================

// Front-end submit form
add_filter('submit_job_form_fields', function($fields){

    $fields['company']['contact_first_name'] = [
        'label'       => __('Contactpersoon voornaam', 'job_manager'),
        'type'        => 'text',
        'required'    => false,
        'placeholder' => __('Bijv. Sophie', 'job_manager'),
        'priority'    => 35,
    ];

    $fields['company']['contact_last_name'] = [
        'label'       => __('Contactpersoon achternaam', 'job_manager'),
        'type'        => 'text',
        'required'    => false,
        'placeholder' => __('Bijv. Jansen', 'job_manager'),
        'priority'    => 36,
    ];

    $fields['company']['contact_email'] = [
        'label'       => __('Contactpersoon e-mailadres', 'job_manager'),
        'type'        => 'text',
        'required'    => false,
        'placeholder' => __('Bijv. sophie@organisatie.nl', 'job_manager'),
        'priority'    => 37,
    ];

    return $fields;
});

// Admin velden
add_filter('job_manager_job_listing_data_fields', function($fields){

    $fields['_contact_first_name'] = [
        'label'       => __('Contactpersoon voornaam', 'job_manager'),
        'type'        => 'text',
        'description' => '',
        'priority'    => 35,
    ];

    $fields['_contact_last_name'] = [
        'label'       => __('Contactpersoon achternaam', 'job_manager'),
        'type'        => 'text',
        'description' => '',
        'priority'    => 36,
    ];

    $fields['_contact_email'] = [
        'label'       => __('Contactpersoon e-mail', 'job_manager'),
        'type'        => 'text',
        'description' => '',
        'priority'    => 37,
    ];

    return $fields;
});

// Valideer email
add_filter('submit_job_form_validate_fields', function($passed, $fields, $values){

    if ( ! empty($values['company']['contact_email']) ) {
        $email = trim($values['company']['contact_email']);
        if ( ! is_email($email) ) {
            $passed = false;
            if ( function_exists('wpjm_add_error') ) {
                wpjm_add_error(__('Vul een geldig e-mailadres in voor de contactpersoon.', 'job_manager'));
            }
        }
    }

    return $passed;
}, 10, 3);

// Opslaan naar meta
add_action('job_manager_update_job_data', function($job_id, $values){

    $fn = $values['company']['contact_first_name'] ?? '';
    $ln = $values['company']['contact_last_name'] ?? '';
    $em = $values['company']['contact_email'] ?? '';

    update_post_meta($job_id, '_contact_first_name', sanitize_text_field($fn));
    update_post_meta($job_id, '_contact_last_name',  sanitize_text_field($ln));
    update_post_meta($job_id, '_contact_email',      sanitize_email($em));

}, 10, 2);
