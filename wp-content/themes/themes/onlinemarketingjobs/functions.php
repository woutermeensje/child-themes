<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// =========================================================
// ActiveCampaign configuratie — credentials uit .env
// =========================================================
(function () {
    $env_file = ABSPATH . '.env';
    if (file_exists($env_file)) {
        foreach (file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
            [$key, $val] = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($val));
        }
    }
})();

if (!defined('ACTIVECAMPAIGN_BASE_URL'))            define('ACTIVECAMPAIGN_BASE_URL',            getenv('ACTIVECAMPAIGN_API_URL') ?: '');
if (!defined('ACTIVECAMPAIGN_API_KEY'))             define('ACTIVECAMPAIGN_API_KEY',             getenv('ACTIVECAMPAIGN_API_KEY') ?: '');
if (!defined('ACTIVECAMPAIGN_ENABLED'))             define('ACTIVECAMPAIGN_ENABLED',             true);
if (!defined('ACTIVECAMPAIGN_LIST_ID'))             define('ACTIVECAMPAIGN_LIST_ID',             21); // Job alerts - Onlinemarketingjobs.nl
if (!defined('ACTIVECAMPAIGN_NEWSLETTER_LIST_ID'))  define('ACTIVECAMPAIGN_NEWSLETTER_LIST_ID',  27); // Nieuwsbrief - Onlinemarketingjobs.nl
if (!defined('ACTIVECAMPAIGN_WERKGEVER_LIST_ID'))   define('ACTIVECAMPAIGN_WERKGEVER_LIST_ID',   28); // Vacature Plaatsingen - Onlinemarketingjobs.nl

require_once get_stylesheet_directory() . '/inc/activecampaign.php';
require_once get_stylesheet_directory() . '/inc/shortcode-job-alerts.php';
require_once get_stylesheet_directory() . '/inc/job-alerts-cron.php';
require_once get_stylesheet_directory() . '/inc/newsletter-cron.php';
require_once get_stylesheet_directory() . '/inc/shortcode-newsletter.php';
require_once get_stylesheet_directory() . '/inc/job-listing-meta.php';
require_once get_stylesheet_directory() . '/inc/job-favorites.php';
require_once get_stylesheet_directory() . '/inc/vacature-cpt.php';
require_once get_stylesheet_directory() . '/inc/shortcode-vacature-formulier.php';
require_once get_stylesheet_directory() . '/inc/shortcode-snel-plaatsen.php';
require_once get_stylesheet_directory() . '/inc/shortcode-tarieven.php';

add_shortcode('omj-job-alerts', 'sj_job_alerts_shortcode');
add_shortcode('omj-job-alerts-sidebar', 'sj_job_alerts_sidebar_shortcode');
add_shortcode('omj-nieuwsbrief', 'sj_nieuwsbrief_shortcode');

// =========================================================
// 1) Styles en fonts
// =========================================================
add_action('wp_enqueue_scripts', function () {
    $dependencies = ['parent-style'];
    if (did_action('elementor/loaded') && wp_style_is('elementor-frontend', 'registered')) {
        $dependencies[] = 'elementor-frontend';
    }

    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', $dependencies, wp_get_theme()->get('Version'));
    wp_enqueue_style('poppins-font', 'https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap', [], null);
    wp_enqueue_style('inter-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', [], null);
    wp_enqueue_style('roboto-font', 'https://fonts.googleapis.com/css2?family=Roboto:wght@500;700&display=swap', [], null);
    wp_enqueue_style('custom-fonts', get_stylesheet_directory_uri() . '/fonts/fonts.css');
    wp_enqueue_style('rn-header', get_stylesheet_directory_uri() . '/css/header.css', ['child-style'], wp_get_theme()->get('Version'));
    wp_enqueue_style('child-gf-styles', get_stylesheet_directory_uri() . '/css/gravity-forms.css');
    wp_enqueue_style('omj-elementor-forms', get_stylesheet_directory_uri() . '/css/elementor-forms.css', ['child-style'], filemtime(get_stylesheet_directory() . '/css/elementor-forms.css'));

    if (file_exists(get_stylesheet_directory() . '/css/forms.css')) {
        wp_enqueue_style(
            'omj-forms',
            get_stylesheet_directory_uri() . '/css/forms.css',
            ['child-style'],
            filemtime(get_stylesheet_directory() . '/css/forms.css')
        );
    }

    if (file_exists(get_stylesheet_directory() . '/css/job-favorites.css')) {
        wp_enqueue_style(
            'omj-job-favorites',
            get_stylesheet_directory_uri() . '/css/job-favorites.css',
            ['rn-header'],
            filemtime(get_stylesheet_directory() . '/css/job-favorites.css')
        );
    }

    wp_enqueue_style('quill-snow', 'https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css', [], null);
    wp_enqueue_script('quill-js', 'https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js', [], null, true);

    if (file_exists(get_stylesheet_directory() . '/js/job-favorites.js')) {
        wp_enqueue_script(
            'omj-job-favorites',
            get_stylesheet_directory_uri() . '/js/job-favorites.js',
            [],
            filemtime(get_stylesheet_directory() . '/js/job-favorites.js'),
            true
        );
        wp_localize_script('omj-job-favorites', 'SJJobFavoritesConfig', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
        ]);
    }
});

add_theme_support('job-manager-templates');
add_filter('job_manager_multi_job_type', '__return_true');

add_filter('wp_mail_from', function ($from_email) {
    return 'support@onlinemarketingjobs.nl';
});

add_filter('wp_mail_from_name', function ($from_name) {
    return 'Onlinemarketingjobs.nl';
});


// =========================================================
// OMJ_Nav_Walker – dropdown-indicator voor navigatie
// =========================================================
if ( ! class_exists('OMJ_Nav_Walker') ) :
class OMJ_Nav_Walker extends Walker_Nav_Menu {
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
// Shortcode: [omj_header]
// =========================================================
add_shortcode('omj_header', function() {
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

if ( ! function_exists('sj_get_image_url') ) {
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

if ( ! function_exists('sj_get_company_logo_url') ) {
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

if ( ! function_exists('sj_the_company_logo') ) {
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

// Verwijder de ingebouwde WP Job Manager 'Categorieën'-taxonomie (job_listing_category)
add_filter('option_job_manager_enable_categories', '__return_false');
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

    register_taxonomy('organisatie_type', 'job_listing', [
        'label'             => 'Type organisatie',
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'organisatie-type'],
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
    register_taxonomy_for_object_type('organisatie_type', 'page');
    register_taxonomy_for_object_type('certificering', 'page');
});


// =========================================================
// 6) WPJM shortcode defaults
// =========================================================
add_filter('job_manager_output_jobs_defaults', function($defaults) {
    $defaults['job_company']      = '';
    $defaults['job_tag']          = '';
    $defaults['job_sector']       = '';
    $defaults['organisatie_type']  = '';
    $defaults['certificering']    = '';
    $defaults['job_listing_type'] = '';
    return $defaults;
});


// =========================================================
// 7) Shortcode-atts -> tax_query
// =========================================================
add_filter('job_manager_get_listings_shortcode_args', function($atts){
    global $omj_job_shortcode_atts;
    $omj_job_shortcode_atts = $atts;

    $custom_filters = [
        'job_company'       => 'job_company',
        'job_tag'           => 'job_tag',
        'job_sector'        => 'job_sector',
        'organisatie_type'   => 'organisatie_type',
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
    global $omj_job_shortcode_atts;

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
        'filter_organisatie_type'      => 'organisatie_type',
        'filter_job_types'            => 'job_listing_type',
        'filter_certificering'        => 'certificering',
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

    if ( ! empty($omj_job_shortcode_atts) && empty($_POST['form_data']) ) {
        foreach ($custom_taxonomies as $filter_key => $taxonomy) {
            $key = str_replace('filter_', '', $filter_key);
            if ( ! empty($omj_job_shortcode_atts[$key]) ) {
                $terms = array_map('sanitize_title', explode(',', sanitize_text_field($omj_job_shortcode_atts[$key])));
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

add_action('init', function () {
    if (get_option('omj_organisatie_type_seeded')) {
        return;
    }

    $terms = ['Bureau', 'Agency', 'Adverteerder', 'SaaS', 'E-commerce', 'Corporate', 'MKB', 'Startup', 'Scale-up', 'Non-profit'];
    foreach ($terms as $term) {
        if (!term_exists($term, 'organisatie_type')) {
            wp_insert_term($term, 'organisatie_type');
        }
    }

    update_option('omj_organisatie_type_seeded', true);
});

add_filter('gettext', function ($translated, $text, $domain) {
    if ($domain === 'wp-job-manager' && $text === 'Load more listings') {
        return 'Toon meer vacatures';
    }

    return $translated;
}, 10, 3);


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
    $fields['job']['job_salary_range'] = [
        'label'       => __('Salarisrange', 'job_manager'),
        'type'        => 'text',
        'required'    => false,
        'placeholder' => __('Bijv. 3000 - 4500 per maand', 'job_manager'),
        'priority'    => 8,
    ];
    $fields['job']['job_hours_per_week'] = [
        'label'       => __('Uren per week', 'job_manager'),
        'type'        => 'text',
        'required'    => false,
        'placeholder' => __('Bijv. 32-40 uur', 'job_manager'),
        'priority'    => 9,
    ];
    return $fields;
});

add_filter('job_manager_job_listing_data_fields', function($fields){
    $fields['_job_salary_range']   = ['label' => __('Salarisrange', 'job_manager'), 'type' => 'text', 'description' => '', 'priority' => 8];
    $fields['_job_hours_per_week'] = ['label' => __('Uren per week', 'job_manager'), 'type' => 'text', 'description' => '', 'priority' => 9];
    return $fields;
});

add_action('job_manager_update_job_data', function($job_id, $values){
    update_post_meta($job_id, '_job_salary_range', sanitize_text_field($values['job']['job_salary_range'] ?? ''));
    update_post_meta($job_id, '_job_hours_per_week', sanitize_text_field($values['job']['job_hours_per_week'] ?? ''));
}, 10, 2);

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
    $first_name = sanitize_text_field($values['company']['contact_first_name'] ?? '');
    $last_name  = sanitize_text_field($values['company']['contact_last_name'] ?? '');
    $email      = sanitize_email($values['company']['contact_email'] ?? '');

    update_post_meta($job_id, '_contact_first_name', $first_name);
    update_post_meta($job_id, '_contact_last_name',  $last_name);
    update_post_meta($job_id, '_contact_email',      $email);

    update_post_meta($job_id, '_job_contact_firstname', $first_name);
    update_post_meta($job_id, '_job_contact_lastname',  $last_name);
    update_post_meta($job_id, '_job_contact_email',     $email);
}, 10, 2);

// Voeg found_count toe aan WP Job Manager AJAX-response
add_filter('job_manager_get_listings_result', function($result, $jobs) {
    $result['found_count'] = (int) $jobs->found_posts;
    return $result;
}, 10, 2);
