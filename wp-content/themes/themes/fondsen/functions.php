<?php



require_once get_stylesheet_directory() . '/job_manager/functions.php';


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

//register the Shortcode handler
add_shortcode('include', 'include_file');
//END amberpanther.com code
//shortcode with sample query string:
//[include filepath="/get-posts.php?format=grid&taxonomy=testing&term=stuff&posttype=work"]






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


// Vacature Plaatsing na invullen formulier automatisch aanmaken. 


add_action('gform_after_submission_14', 'create_job_listing_from_gravity_forms_14', 10, 2);
function create_job_listing_from_gravity_forms_14($entry, $form) {
    // Controleer of de functie bestaat
    if (!function_exists('wp_insert_post')) {
        return;
    }

    // Haal gegevens op uit Gravity Forms
    $job_title = rgar($entry, '8'); // Vacaturetitel
    $job_location = rgar($entry, '22'); // Locatie
    $job_description = rgar($entry, '16'); // Vacaturebeschrijving
    $job_url = rgar($entry, '18'); // Link naar Vacature
    $job_logo = rgar($entry, '29'); // Logo URL
    $job_image = rgar($entry, '30'); // Afbeelding bij vacature

    // Vacature aanmaken in WP Job Manager
    $job_post = array(
        'post_title'    => wp_strip_all_tags($job_title),
        'post_content'  => $job_description,
        'post_status'   => 'pending', // Of 'pending' als je goedkeuring wilt
        'post_type'     => 'job_listing',
    );

    $post_id = wp_insert_post($job_post);

    if ($post_id) {
        // Koppel custom meta-velden aan de vacature
        update_post_meta($post_id, '_job_location', $job_location);
        update_post_meta($post_id, '_application_url', $job_url);

        // Bedrijfslogo opslaan (indien aanwezig)
        if (!empty($job_logo)) {
            update_post_meta($post_id, '_company_logo', esc_url($job_logo));
        }

        // Afbeelding bij vacature instellen als uitgelichte afbeelding (featured image)
        if (!empty($job_image)) {
            $image_id = media_sideload_image($job_image, $post_id, '', 'id');
            if (!is_wp_error($image_id)) {
                set_post_thumbnail($post_id, $image_id);
            }
        }
    }
}


// Media upload vanuit Gravity Forms 


// Voeg een actie toe voor het verzenden van het formulier
add_action("gform_after_submission", "process_uploaded_media", 10, 2);

function process_uploaded_media($entry, $form) {
    // Hier vervang je 1 met het ID van je formulier.
    if ($form["id"] == 3) {
        // Haal het bestandsveld op aan de hand van het veld-ID (vervang 1 door het werkelijke veld-ID).
        $file_field_id = 1;
        
        // Haal het bestand op uit het formulierinzending.
        $file_url = rgar($entry, "14" . $file_field_id);
        
        // Haal de bestandsnaam uit de URL.
        $file_name = basename($file_url);
        
        // Bouw het pad naar de uploadmap in WordPress.
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir["/applications/your_app/public_html"] . "/" . $file_name;
        
        // Download het bestand en sla het op in de uploadmap.
        if (copy($file_url, $upload_path)) {
            // Voeg het bestand toe aan de mediabibliotheek.
            $attachment = array(
                "post_title" => $file_name,
                "post_content" => "",
                "post_status" => "inherit"
            );
            $attachment_id = wp_insert_attachment($attachment, $upload_path);
            
            // Genereer metadata voor het bijgevoegde bestand en sla het op.
            require_once(ABSPATH . "wp-admin/includes/image.php");
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload_path);
            wp_update_attachment_metadata($attachment_id, $attachment_data);
            
            // Optioneel: koppel het bijgevoegde bestand aan een specifieke post of pagina.
            // Vervang 0 door de post-ID waaraan je het wilt koppelen.
            // update_post_meta(0, "_thumbnail_id", $attachment_id);
        }
    }
}
