<?php



$jm_functions = get_stylesheet_directory() . '/job_manager/functions.php';
if (file_exists($jm_functions)) {
    require_once $jm_functions;
}


add_theme_support('job-manager-templates');

// // Exit if accessed directly
if (!defined('ABSPATH')) exit;




function fondsen_enqueue_styles() {

    // 1. Parent theme CSS (Hello Elementor)
    wp_enqueue_style(
        'parent-style',
        get_template_directory_uri() . '/style.css',
        [],
        filemtime( get_template_directory() . '/style.css' )
    );

    // 2. Child theme main CSS (laadt NA parent)
    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        ['parent-style'], // important: afhankelijk van parent
        filemtime( get_stylesheet_directory() . '/style.css' )
    );

    // 3. Google Fonts
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
}
add_action( 'wp_enqueue_scripts', 'fondsen_enqueue_styles' );




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

    register_taxonomy('organization_type', 'job_listing', [
        'label' => 'Organization Type',
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'organization_type'],
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


add_filter( 'wpseo_breadcrumb_separator', function( $separator ) {
    return ' / ';
});

// hier komt de code voor de fondsen oproepen en de shortcode voor de goede doelen lijst



// hieronder komt de shortcode voor de fondsenwervingen


add_filter('job_manager_geolocation_default_radius', function() {
    return 50; // Default radius in km or miles depending on settings
});


function add_categories_to_pages() {
    register_taxonomy_for_object_type('category', 'page');
}
add_action('init', 'add_categories_to_pages');

function fondsen_shortcode($atts) {
    ob_start();

    $atts = shortcode_atts(array(
        'category' => 'fondsen-werven',
    ), $atts);

    $fondsen_query = new WP_Query(array(
        'post_type' => 'page',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
        'category_name' => sanitize_text_field($atts['category']),
    ));

    if ($fondsen_query->have_posts()) {
        echo '<div class="fondsen-oproepen">';
        while ($fondsen_query->have_posts()) {
            $fondsen_query->the_post();
            $excerpt = wp_trim_words(strip_tags(get_the_content()), 25);
            $logo = get_the_post_thumbnail(get_the_ID(), 'medium', array('class' => 'fonds-logo'));

            echo '<div class="fonds-item">';
                echo '<div class="fonds-header">';
                    if ($logo) {
                        echo '<div class="fonds-logo-wrapper">' . $logo . '</div>';
                    }
                    echo '<div class="fonds-text">';
                        echo '<h2 class="fonds-title">' . get_the_title() . '</h2>';
                        echo '<p class="fonds-excerpt">' . $excerpt . '</p>';
                        echo '<a class="fonds-button" href="' . get_permalink() . '">Oproep bekijken</a>';
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




// functionaliteit voor header-template.php

/**
 * Customizer instellingen voor header-knoppen
 */
function fondsenorg_customize_register( $wp_customize ) {

    // Sectie voor de header-knoppen
    $wp_customize->add_section( 'fondsenorg_header_buttons', array(
        'title'       => __( 'Header knoppen', 'fondsenorg' ),
        'priority'    => 30,
        'description' => __( 'Stel hier de links en teksten in voor de knoppen in de header.', 'fondsenorg' ),
    ) );

    // Linker knop - URL
    $wp_customize->add_setting( 'fondsenorg_header_left_url', array(
        'default'           => '/#vacatures',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'fondsenorg_header_left_url_control', array(
        'label'    => __( 'Linker knop URL', 'fondsenorg' ),
        'section'  => 'fondsenorg_header_buttons',
        'settings' => 'fondsenorg_header_left_url',
        'type'     => 'url',
    ) );

    // Linker knop - tekst
    $wp_customize->add_setting( 'fondsenorg_header_left_text', array(
        'default'           => 'Vacatures',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'fondsenorg_header_left_text_control', array(
        'label'    => __( 'Linker knop tekst', 'fondsenorg' ),
        'section'  => 'fondsenorg_header_buttons',
        'settings' => 'fondsenorg_header_left_text',
        'type'     => 'text',
    ) );

    // Rechter knop - URL
    $wp_customize->add_setting( 'fondsenorg_header_right_url', array(
        'default'           => '/job-alerts/',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'fondsenorg_header_right_url_control', array(
        'label'    => __( 'Rechter knop URL', 'fondsenorg' ),
        'section'  => 'fondsenorg_header_buttons',
        'settings' => 'fondsenorg_header_right_url',
        'type'     => 'url',
    ) );

    // Rechter knop - tekst
    $wp_customize->add_setting( 'fondsenorg_header_right_text', array(
        'default'           => 'Job alerts',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'fondsenorg_header_right_text_control', array(
        'label'    => __( 'Rechter knop tekst', 'fondsenorg' ),
        'section'  => 'fondsenorg_header_buttons',
        'settings' => 'fondsenorg_header_right_text',
        'type'     => 'text',
    ) );

}
add_action( 'customize_register', 'fondsenorg_customize_register' );


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

/**
 * ✅ Debug WP_Query inhoud
 */
add_action('pre_get_posts', function($query) {
    if (!is_admin() && $query->is_main_query() && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'job_listing') {
        error_log('👉 WP_Query tax_query: ' . print_r($query->query_vars['tax_query'], true));
    }
});

// Add custom default attributes to the jobs shortcode
add_filter('job_manager_output_jobs_defaults', function($defaults) {
    $defaults['job_company'] = '';
    $defaults['job_tag'] = '';
    $defaults['job_sector'] = '';
    $defaults['certificering'] = '';
    $defaults['job_listing_type'] = '';
    
    return $defaults;
});




/**
 * WPJM extras (verplaatst uit /job_manager/functions.php)
 */

if (!function_exists('display_tax_terms')) {
    function display_tax_terms($tax, $post_id) {
        $terms = wp_get_post_terms($post_id, $tax, array('fields' => 'names'));
        return implode(',', $terms);
    }
}

if (!function_exists('get_secondary_imageurl')) {
    function get_secondary_imageurl($post_id) {
        $image_id = get_post_meta($post_id, '_uncode_secondary_thumbnail_id', true);
        return wp_get_attachment_image_url($image_id, 'large');
    }
}

if (!function_exists('create_thankyou_page_pageview')) {
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

/** Indeed */
add_filter('submit_job_form_fields', function($fields){
    $fields['company']['company_indeed'] = array(
        'label'       => __('Indeed', 'job_manager'),
        'type'        => 'text',
        'required'    => false,
        'placeholder' => 'Indeed link',
        'priority'    => 5
    );
    return $fields;
});

add_filter('job_manager_job_listing_data_fields', function($fields){
    $fields['_company_indeed'] = array(
        'label'       => __('Indeed', 'job_manager'),
        'type'        => 'text',
        'placeholder' => 'Indeed link',
        'description' => ''
    );
    return $fields;
});

/** Facebook */
add_filter('submit_job_form_fields', function($fields){
    $fields['company']['company_facebook'] = array(
        'label'       => __('Facebook', 'job_manager'),
        'type'        => 'text',
        'required'    => false,
        'placeholder' => 'https://facebook.com/your-company',
        'priority'    => 5
    );
    return $fields;
});

add_filter('job_manager_job_listing_data_fields', function($fields){
    $fields['_company_facebook'] = array(
        'label'       => __('Facebook', 'job_manager'),
        'type'        => 'text',
        'placeholder' => 'https://facebook.com/your-company',
        'description' => ''
    );
    return $fields;
});

/** LinkedIn */
add_filter('submit_job_form_fields', function($fields){
    $fields['company']['company_linkedin'] = array(
        'label'       => __('LinkedIn', 'job_manager'),
        'type'        => 'text',
        'required'    => false,
        'placeholder' => 'https://linkedin.com/your-company',
        'priority'    => 5
    );
    return $fields;
});

add_filter('job_manager_job_listing_data_fields', function($fields){
    $fields['_company_linkedin'] = array(
        'label'       => __('LinkedIn', 'job_manager'),
        'type'        => 'text',
        'placeholder' => 'https://linkedin.com/in/your-company',
        'description' => ''
    );
    return $fields;
});

/** Cover image */
add_filter('submit_job_form_fields', function($fields){
    $fields['job']['cover_image'] = array(
        'label'    => __('Cover afbeelding', 'job_manager'),
        'type'     => 'file',
        'accept'   => 'image/png, image/jpeg',
        'required' => false,
        'priority' => 7
    );
    return $fields;
});

add_filter('job_manager_job_listing_data_fields', function($fields){
    $fields['_cover_image'] = array(
        'label' => __('Cover afbeelding', 'job_manager'),
        'type'  => 'file',
    );
    return $fields;
});







// Front-end submit form (plaats vacature)
add_filter('submit_job_form_fields', function($fields){

  // Voeg een sectie/velden toe onder "company" (of kies 'job' als je wil)
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
    'type'        => 'text', // WPJM heeft ook 'text' + we valideren hieronder
    'required'    => false,
    'placeholder' => __('Bijv. sophie@organisatie.nl', 'job_manager'),
    'priority'    => 37,
  ];

  return $fields;
});

// Admin velden (in wp-admin bij vacature bewerken)
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

// Valideer email op front-end submit
add_filter('submit_job_form_validate_fields', function($passed, $fields, $values){

  if (!empty($values['company']['contact_email'])) {
    $email = trim($values['company']['contact_email']);
    if (!is_email($email)) {
      $passed = false;
      // WPJM toont errors via wp_die of notices; deze werkt in de praktijk goed
      if (function_exists('wpjm_add_error')) {
        wpjm_add_error(__('Vul een geldig e-mailadres in voor de contactpersoon.', 'job_manager'));
      }
    }
  }

  return $passed;
}, 10, 3);

// Opslaan naar meta (front-end)
add_action('job_manager_update_job_data', function($job_id, $values){

  // Values komen uit submit_job_form_fields (company sectie)
  $fn = $values['company']['contact_first_name'] ?? '';
  $ln = $values['company']['contact_last_name'] ?? '';
  $em = $values['company']['contact_email'] ?? '';

  update_post_meta($job_id, '_contact_first_name', sanitize_text_field($fn));
  update_post_meta($job_id, '_contact_last_name', sanitize_text_field($ln));
  update_post_meta($job_id, '_contact_email', sanitize_email($em));

}, 10, 2);


