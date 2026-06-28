<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// =========================================================
// 1) Styles en fonts
// =========================================================
add_action('wp_enqueue_scripts', function () {
    $dependencies = ['parent-style'];
    if (did_action('elementor/loaded') && wp_style_is('elementor-frontend', 'registered')) {
        $dependencies[] = 'elementor-frontend';
    }

    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', $dependencies, filemtime(get_stylesheet_directory() . '/style.css'));
    wp_enqueue_style('poppins-font', 'https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap', [], null);
    wp_enqueue_style('inter-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Roboto:wght@400;600&display=swap', [], null);
    wp_enqueue_style('work-sans-font', 'https://fonts.googleapis.com/css2?family=Work+Sans:wght@700;800;900&display=swap', [], null);
    wp_enqueue_style('custom-fonts', get_stylesheet_directory_uri() . '/fonts/fonts.css', [], filemtime(get_stylesheet_directory() . '/fonts/fonts.css'));
    wp_enqueue_style('rn-header', get_stylesheet_directory_uri() . '/css/header.css', ['child-style', 'work-sans-font'], filemtime(get_stylesheet_directory() . '/css/header.css'));
    wp_enqueue_style('rn-shortcodes', get_stylesheet_directory_uri() . '/css/shortcodes.css', ['child-style'], filemtime(get_stylesheet_directory() . '/css/shortcodes.css'));
    wp_enqueue_style('rn-elementor-forms', get_stylesheet_directory_uri() . '/css/elementor-forms.css', ['child-style'], filemtime(get_stylesheet_directory() . '/css/elementor-forms.css'));

    if (
        ( is_home() || is_category() || is_tag() || is_date() || is_author() || is_singular( 'post' ) ) &&
        file_exists( get_stylesheet_directory() . '/css/blog.css' )
    ) {
        wp_enqueue_style(
            'rn-blog',
            get_stylesheet_directory_uri() . '/css/blog.css',
            ['child-style'],
            filemtime( get_stylesheet_directory() . '/css/blog.css' )
        );
    }
});

add_theme_support('job-manager-templates');

add_action('pre_get_posts', function ($query) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }

    $raw_search = isset( $_GET['rn_s'] ) ? wp_unslash( $_GET['rn_s'] ) : '';
    $search     = is_array( $raw_search ) ? '' : sanitize_text_field( $raw_search );

    if (
        '' === $search ||
        ! ( $query->is_home() || $query->is_category() || $query->is_tag() || $query->is_date() || $query->is_author() )
    ) {
        return;
    }

    $query->set( 's', $search );
});


// =========================================================
// RN_Nav_Walker – dropdown-indicator voor navigatie
// =========================================================
if ( ! class_exists('RN_Nav_Walker') ) :
class RN_Nav_Walker extends Walker_Nav_Menu {
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
// Shortcode: [rn_header]
// =========================================================
add_shortcode('rn_header', function() {
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
if ( ! function_exists('srmb_get_req_value') ) {
    function srmb_get_req_value($key) {
        $filter_key = 'filter_' . $key;

        if (!empty($_GET[$key]))        return (array) $_GET[$key];
        if (!empty($_GET[$filter_key])) return (array) $_GET[$filter_key];
        if (!empty($_POST[$filter_key])) return (array) $_POST[$filter_key];
        if (!empty($_POST[$key]))       return (array) $_POST[$key];

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

    register_taxonomy('organization_type', 'job_listing', [
        'label'             => 'Provincie',
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'provincie'],
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
// 5) Taxonomieën ook koppelen aan pages
// =========================================================
add_action('init', function () {
    register_taxonomy_for_object_type('job_company', 'page');
    register_taxonomy_for_object_type('job_tag', 'page');
    register_taxonomy_for_object_type('job_sector', 'page');
    register_taxonomy_for_object_type('certificering', 'page');
});


// =========================================================
// 6) WPJM shortcode defaults
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
// 7) Shortcode-atts -> tax_query
// =========================================================
add_filter('job_manager_get_listings_shortcode_args', function($atts){
    global $rn_job_shortcode_atts;
    $rn_job_shortcode_atts = $atts;

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
// 8) AJAX filterdata + shortcode tax_query
//    Elementor 500 fix: slaat elementor_ajax over
// =========================================================
add_filter('get_job_listings_query_args', function ($query_args, $args) {
    global $rn_job_shortcode_atts;

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
        'filter_job_tag'              => 'job_tag',
        'filter_job_sector'           => 'job_sector',
        'filter_job_company'          => 'job_company',
        'filter_job_types'            => 'job_listing_type',
        'filter_certificering'        => 'certificering',
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

    if ( ! empty($rn_job_shortcode_atts) && empty($_POST['form_data']) ) {
        foreach ($custom_taxonomies as $filter_key => $taxonomy) {
            $key = str_replace('filter_', '', $filter_key);
            if ( ! empty($rn_job_shortcode_atts[$key]) ) {
                $terms = array_map('sanitize_title', explode(',', sanitize_text_field($rn_job_shortcode_atts[$key])));
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

add_filter('job_manager_get_listings_result', function ($result, $jobs) {
    $result['found_count'] = isset($jobs->found_posts) ? (int) $jobs->found_posts : 0;

    return $result;
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

if ( ! function_exists('get_secondary_imageurl') ) {
    function get_secondary_imageurl($post_id) {
        $image_id = get_post_meta($post_id, '_uncode_secondary_thumbnail_id', true);
        return wp_get_attachment_image_url($image_id, 'large');
    }
}


// =========================================================
// 12) Extra velden: Indeed / Facebook / LinkedIn
// =========================================================
add_filter('submit_job_form_fields', function($fields){
    $fields['company']['company_indeed'] = [
        'label'       => __('Indeed', 'job_manager'),
        'type'        => 'text',
        'required'    => false,
        'placeholder' => 'Indeed link',
        'priority'    => 5,
    ];
    return $fields;
});
add_filter('job_manager_job_listing_data_fields', function($fields){
    $fields['_company_indeed'] = ['label' => __('Indeed', 'job_manager'), 'type' => 'text', 'placeholder' => 'Indeed link', 'description' => ''];
    return $fields;
});

add_filter('submit_job_form_fields', function($fields){
    $fields['company']['company_facebook'] = [
        'label'       => __('Facebook', 'job_manager'),
        'type'        => 'text',
        'required'    => false,
        'placeholder' => 'https://facebook.com/your-company',
        'priority'    => 5,
    ];
    return $fields;
});
add_filter('job_manager_job_listing_data_fields', function($fields){
    $fields['_company_facebook'] = ['label' => __('Facebook', 'job_manager'), 'type' => 'text', 'placeholder' => 'https://facebook.com/your-company', 'description' => ''];
    return $fields;
});

add_filter('submit_job_form_fields', function($fields){
    $fields['company']['company_linkedin'] = [
        'label'       => __('LinkedIn', 'job_manager'),
        'type'        => 'text',
        'required'    => false,
        'placeholder' => 'https://linkedin.com/your-company',
        'priority'    => 5,
    ];
    return $fields;
});
add_filter('job_manager_job_listing_data_fields', function($fields){
    $fields['_company_linkedin'] = ['label' => __('LinkedIn', 'job_manager'), 'type' => 'text', 'placeholder' => 'https://linkedin.com/in/your-company', 'description' => ''];
    return $fields;
});


// =========================================================
// 13) Cover image veld
// =========================================================
add_filter('submit_job_form_fields', function($fields){
    $fields['job']['cover_image'] = [
        'label'    => __('Cover afbeelding', 'job_manager'),
        'type'     => 'file',
        'accept'   => 'image/png, image/jpeg',
        'required' => false,
        'priority' => 7,
    ];
    return $fields;
});
add_filter('job_manager_job_listing_data_fields', function($fields){
    $fields['_cover_image'] = ['label' => __('Cover afbeelding', 'job_manager'), 'type' => 'file'];
    return $fields;
});


// =========================================================
// 14) Contactpersoon velden
// =========================================================
add_filter('submit_job_form_fields', function($fields){
    $fields['company']['contact_first_name'] = [
        'label' => __('Contactpersoon voornaam', 'job_manager'), 'type' => 'text',
        'required' => false, 'placeholder' => __('Bijv. Sophie', 'job_manager'), 'priority' => 35,
    ];
    $fields['company']['contact_last_name'] = [
        'label' => __('Contactpersoon achternaam', 'job_manager'), 'type' => 'text',
        'required' => false, 'placeholder' => __('Bijv. Jansen', 'job_manager'), 'priority' => 36,
    ];
    $fields['company']['contact_email'] = [
        'label' => __('Contactpersoon e-mailadres', 'job_manager'), 'type' => 'text',
        'required' => false, 'placeholder' => __('Bijv. sophie@bedrijf.nl', 'job_manager'), 'priority' => 37,
    ];
    return $fields;
});

add_filter('job_manager_job_listing_data_fields', function($fields){
    $fields['_contact_first_name'] = ['label' => __('Contactpersoon voornaam', 'job_manager'), 'type' => 'text', 'description' => '', 'priority' => 35];
    $fields['_contact_last_name']  = ['label' => __('Contactpersoon achternaam', 'job_manager'), 'type' => 'text', 'description' => '', 'priority' => 36];
    $fields['_contact_email']      = ['label' => __('Contactpersoon e-mail', 'job_manager'), 'type' => 'text', 'description' => '', 'priority' => 37];
    return $fields;
});

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

add_action('job_manager_update_job_data', function($job_id, $values){
    update_post_meta($job_id, '_contact_first_name', sanitize_text_field($values['company']['contact_first_name'] ?? ''));
    update_post_meta($job_id, '_contact_last_name',  sanitize_text_field($values['company']['contact_last_name'] ?? ''));
    update_post_meta($job_id, '_contact_email',      sanitize_email($values['company']['contact_email'] ?? ''));
}, 10, 2);

// =========================================================
// Inc: nieuwsbrief, job alerts, vacature plaatsen, AC
// =========================================================
require_once get_stylesheet_directory() . '/inc/activecampaign.php';
require_once get_stylesheet_directory() . '/inc/newsletter-cron.php';
require_once get_stylesheet_directory() . '/inc/shortcode-newsletter.php';
require_once get_stylesheet_directory() . '/inc/job-alerts-cron.php';
require_once get_stylesheet_directory() . '/inc/shortcode-job-alerts.php';
require_once get_stylesheet_directory() . '/inc/shortcode-vacature-plaatsen.php';
require_once get_stylesheet_directory() . '/inc/blog-meta.php';


// =========================================================
// Google Jobs – structured data fixes
// =========================================================

add_filter( 'wpjm_get_job_listing_structured_data', function ( $data, $post ) {

    // 1. @context moet https zijn (Google eist dit)
    $data['@context'] = 'https://schema.org/';

    // 2. validThrough: Google verwijdert listings zonder einddatum na verloop van tijd.
    //    Fallback: 6 maanden na plaatsingsdatum.
    if ( empty( $data['validThrough'] ) ) {
        $date_posted = get_post_datetime( $post );
        if ( $date_posted ) {
            $expires = clone $date_posted;
            $expires->modify( '+6 months' );
            $data['validThrough'] = $expires->format( 'c' );
        }
    }

    // 3. hiringOrganization.name mag nooit leeg zijn (Google verplicht veld).
    if ( empty( $data['hiringOrganization']['name'] ) ) {
        $data['hiringOrganization']['name'] = get_bloginfo( 'name' );
    }

    // 4. jobLocation.address: als geocoding niet ingesteld is valt WP Job Manager
    //    terug op een plain string. Upgrade die naar een PostalAddress object.
    if ( ! empty( $data['jobLocation']['address'] ) && is_string( $data['jobLocation']['address'] ) ) {
        $data['jobLocation']['address'] = [
            '@type'           => 'PostalAddress',
            'addressLocality' => $data['jobLocation']['address'],
            'addressCountry'  => 'NL',
        ];
    }

    // 5. jobLocation helemaal leeg maar locatietekst aanwezig: voeg fallback toe.
    if ( empty( $data['jobLocation'] ) ) {
        $location = get_the_job_location( $post );
        if ( ! empty( $location ) ) {
            $data['jobLocation'] = [
                '@type'   => 'Place',
                'address' => [
                    '@type'           => 'PostalAddress',
                    'addressLocality' => $location,
                    'addressCountry'  => 'NL',
                ],
            ];
        }
    }

    return $data;

}, 20, 2 );
