<?php
/**
 * Impact Vacatures Child Theme – functions.php (opgeschoond + fixes)
 * - ✅ Dubbele WPJM filters verwijderd (stonden 2x in je file)
 * - ✅ Elementor publish/save 500 fix: WPJM query filter draait niet meer mee bij elementor_ajax
 * - ✅ tax_query altijd als array initialiseren
 * - ✅ Debug logging optioneel (uit te zetten)
 */

if ( ! defined('ABSPATH') ) exit;

require_once get_stylesheet_directory() . '/inc/job-favorites.php';
require_once get_stylesheet_directory() . '/inc/job-expiry.php';
require_once get_stylesheet_directory() . '/inc/uitgelichte-werkgever.php';
require_once get_stylesheet_directory() . '/inc/shortcode-hero.php';
require_once get_stylesheet_directory() . '/inc/activecampaign.php';
require_once get_stylesheet_directory() . '/inc/shortcode-job-alerts.php';
require_once get_stylesheet_directory() . '/inc/job-alerts-cron.php';
require_once get_stylesheet_directory() . '/inc/newsletter-cron.php';
require_once get_stylesheet_directory() . '/inc/shortcode-newsletter.php';
require_once get_stylesheet_directory() . '/inc/newsletter-admin.php';

// =========================================================
// Pretty filter URLs: /vacatures/{slug}/
// =========================================================
if ( ! defined('FONDSEN_REWRITE_VERSION') ) {
    define('FONDSEN_REWRITE_VERSION', '2026-04-24-listing-filter-links');
}

function fondsen_register_filter_rewrites() {
    add_rewrite_rule(
        '^vacatures/([^/]+)/?$',
        'index.php?pagename=vacatures&vacatures_filter=$matches[1]',
        'top'
    );

    // Backwards-compatible alias for the old singular company taxonomy base.
    add_rewrite_rule(
        '^organisatie/([^/]+)/?$',
        'index.php?job_company=$matches[1]',
        'top'
    );
}
add_action('init', 'fondsen_register_filter_rewrites', 9);

add_filter('query_vars', function ( $vars ) {
    $vars[] = 'vacatures_filter';
    return $vars;
});

if ( ! function_exists('fondsen_get_existing_vacatures_page') ) {
    function fondsen_get_existing_vacatures_page( $slug ) {
        $slug = sanitize_title((string) $slug);
        if ( $slug === '' ) {
            return null;
        }

        $page = get_page_by_path('vacatures/' . $slug, OBJECT, 'page');
        if ( ! $page instanceof WP_Post ) {
            return null;
        }

        $status          = get_post_status($page);
        $public_statuses = get_post_stati(['public' => true]);

        if ( in_array($status, $public_statuses, true) || $status === 'private' ) {
            return $page;
        }

        return null;
    }
}

// Let real pages like /vacatures/natuurmonumenten/ win over generated filter URLs.
add_filter('request', function ( $query_vars ) {
    if (
        empty($query_vars['vacatures_filter']) ||
        empty($query_vars['pagename']) ||
        trim((string) $query_vars['pagename'], '/') !== 'vacatures'
    ) {
        return $query_vars;
    }

    $slug = sanitize_title((string) $query_vars['vacatures_filter']);
    if ( ! fondsen_get_existing_vacatures_page($slug) ) {
        return $query_vars;
    }

    unset($query_vars['vacatures_filter']);
    $query_vars['pagename'] = 'vacatures/' . $slug;

    return $query_vars;
});

add_action('after_switch_theme', function () {
    fondsen_register_filter_rewrites();
    flush_rewrite_rules(false);
});

add_action('init', function () {
    if ( get_option('fondsen_rewrite_version') === FONDSEN_REWRITE_VERSION ) {
        return;
    }

    flush_rewrite_rules(false);
    update_option('fondsen_rewrite_version', FONDSEN_REWRITE_VERSION, false);
}, 99);

add_filter('redirect_canonical', function ( $redirect_url ) {
    return get_query_var('vacatures_filter') ? false : $redirect_url;
});

// Zet filter zodat WP Job Manager de initiële query én het formulier vult.
add_action('template_redirect', function () {
    $slug = get_query_var('vacatures_filter');
    if ( ! $slug ) return;

    $slug = sanitize_title($slug);

    if ( get_term_by('slug', $slug, 'job_listing_type') ) {
        $_GET['filter_job_types'] = [ $slug ];
        $_REQUEST['filter_job_types'] = [ $slug ];
        add_filter('job_manager_output_jobs_defaults', function ( $defaults ) use ( $slug ) {
            $defaults['selected_job_types'] = [ $slug ];
            return $defaults;
        });
    } elseif ( get_term_by('slug', $slug, 'job_company') ) {
        $_GET['filter_job_company'] = [ $slug ];
        $_REQUEST['filter_job_company'] = [ $slug ];
    } elseif ( get_term_by('slug', $slug, 'organization_type') ) {
        $_GET['filter_organization_type'] = [ $slug ];
        $_REQUEST['filter_organization_type'] = [ $slug ];
    } elseif ( get_term_by('slug', $slug, 'job_sector') ) {
        $_GET['filter_job_sector'] = [ $slug ];
        $_REQUEST['filter_job_sector'] = [ $slug ];
    } else {
        $location = str_replace('-', ' ', urldecode($slug));
        $_GET['search_location'] = $location;
        $_REQUEST['search_location'] = $location;
        add_filter('job_manager_output_jobs_defaults', function ( $defaults ) use ( $location ) {
            $defaults['selected_location'] = $location;
            return $defaults;
        });
    }
});

require_once get_stylesheet_directory() . '/inc/shortcode-vacature-formulier.php';
require_once get_stylesheet_directory() . '/inc/vacature-cpt.php';
require_once get_stylesheet_directory() . '/inc/locations.php';

add_filter('wp_mail_from', function () {
    return 'informatie@impactvacatures.nl';
});

add_filter('wp_mail_from_name', function () {
    return 'Impact Vacatures';
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
    wp_enqueue_style(
        'roboto-font',
        'https://fonts.googleapis.com/css2?family=Roboto:wght@700&display=swap',
        [],
        null
    );

    wp_enqueue_style(
        'work-sans-font',
        'https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;600;700;800&display=swap',
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

    if (file_exists(get_stylesheet_directory() . '/css/job-favorites.css')) {
        wp_enqueue_style(
            'fondsen-job-favorites',
            get_stylesheet_directory_uri() . '/css/job-favorites.css',
            ['fondsen-header'],
            filemtime(get_stylesheet_directory() . '/css/job-favorites.css')
        );
    }

    if (file_exists(get_stylesheet_directory() . '/css/shortcodes.css')) {
        wp_enqueue_style(
            'fondsen-shortcodes',
            get_stylesheet_directory_uri() . '/css/shortcodes.css',
            ['child-style'],
            filemtime(get_stylesheet_directory() . '/css/shortcodes.css')
        );
    }

    if (file_exists(get_stylesheet_directory() . '/js/job-favorites.js')) {
        wp_enqueue_script(
            'fondsen-job-favorites',
            get_stylesheet_directory_uri() . '/js/job-favorites.js',
            [],
            filemtime(get_stylesheet_directory() . '/js/job-favorites.js'),
            true
        );
        wp_localize_script('fondsen-job-favorites', 'SJJobFavoritesConfig', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
        ]);
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

    // Landingspagina CSS (Lidmaatschap, Werkgever, Werkzoekende)
    $lp_templates = ['page-lidmaatschap.php', 'page-werkgever.php', 'page-werkzoekende.php'];
    if ( is_page_template( $lp_templates ) && file_exists( get_stylesheet_directory() . '/css/landingspagina.css' ) ) {
        wp_enqueue_style(
            'fondsen-landingspagina',
            get_stylesheet_directory_uri() . '/css/landingspagina.css',
            ['child-style'],
            filemtime( get_stylesheet_directory() . '/css/landingspagina.css' )
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
        'rewrite'           => ['slug' => 'organisaties'],
    ]);

    register_taxonomy('organization_type', 'job_listing', [
        'label'             => 'Type organisatie',
        'labels'            => [
            'name'          => 'Type organisaties',
            'singular_name' => 'Type organisatie',
            'search_items'  => 'Type organisaties zoeken',
            'all_items'     => 'Alle type organisaties',
            'edit_item'     => 'Type organisatie bewerken',
            'update_item'   => 'Type organisatie bijwerken',
            'add_new_item'  => 'Nieuw type organisatie toevoegen',
            'new_item_name' => 'Nieuwe type organisatie',
            'menu_name'     => 'Type organisaties',
        ],
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'meta_box_cb'       => false,
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

});


// =========================================================
// 5) Taxonomieën ook koppelen aan pages (voor organisatie pagina’s etc.)
// =========================================================
add_action('init', function () {
    register_taxonomy_for_object_type('job_company', 'page');
    register_taxonomy_for_object_type('job_tag', 'page');
    register_taxonomy_for_object_type('job_sector', 'page');
});

// =========================================================
// 5b) Organisatie term-meta: standaard e-mailadres meldingen
// =========================================================
function fondsen_render_job_company_notification_email_add_field() {
    ?>
    <div class="form-field term-fondsen-notification-email-wrap">
        <label for="fondsen_notification_email">Standaard melding e-mailadres</label>
        <input type="email" name="fondsen_notification_email" id="fondsen_notification_email" value="">
        <p class="description">Ontvangt de vacaturemeldingen na 30, 60 en 90 dagen. Als dit leeg is, gebruikt Impact Vacatures het contactpersoon e-mailadres van de vacature.</p>
    </div>
    <?php
}
add_action('job_company_add_form_fields', 'fondsen_render_job_company_notification_email_add_field');

function fondsen_render_job_company_notification_email_edit_field($term) {
    $email = get_term_meta($term->term_id, '_fondsen_notification_email', true);
    ?>
    <tr class="form-field term-fondsen-notification-email-wrap">
        <th scope="row">
            <label for="fondsen_notification_email">Standaard melding e-mailadres</label>
        </th>
        <td>
            <input type="email" name="fondsen_notification_email" id="fondsen_notification_email" value="<?php echo esc_attr($email); ?>">
            <p class="description">Ontvangt de vacaturemeldingen na 30, 60 en 90 dagen. Als dit leeg is, gebruikt Impact Vacatures het contactpersoon e-mailadres van de vacature.</p>
        </td>
    </tr>
    <?php
}
add_action('job_company_edit_form_fields', 'fondsen_render_job_company_notification_email_edit_field');

function fondsen_save_job_company_notification_email($term_id) {
    if ( ! isset($_POST['fondsen_notification_email']) ) {
        return;
    }

    $email = sanitize_email(wp_unslash($_POST['fondsen_notification_email']));
    if ($email && is_email($email)) {
        update_term_meta($term_id, '_fondsen_notification_email', $email);
    } else {
        delete_term_meta($term_id, '_fondsen_notification_email');
    }
}
add_action('created_job_company', 'fondsen_save_job_company_notification_email');
add_action('edited_job_company', 'fondsen_save_job_company_notification_email');

// =========================================================
// 5c) Organisatie term-meta: type organisatie
// =========================================================
if ( ! defined('FONDSEN_ORG_TYPE_SYNC_VERSION') ) {
    define('FONDSEN_ORG_TYPE_SYNC_VERSION', '2026-06-24-company-organization-types');
}

function fondsen_get_all_organization_type_terms() {
    $terms = get_terms([
        'taxonomy'   => 'organization_type',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);

    return is_wp_error($terms) ? [] : $terms;
}

function fondsen_normalize_organization_type_ids($ids) {
    $ids = array_values(array_unique(array_filter(array_map('absint', (array) $ids))));
    if (empty($ids)) {
        return [];
    }

    $valid_ids = get_terms([
        'taxonomy'   => 'organization_type',
        'hide_empty' => false,
        'include'    => $ids,
        'fields'     => 'ids',
    ]);

    return is_wp_error($valid_ids)
        ? []
        : array_values(array_intersect($ids, array_map('absint', $valid_ids)));
}

function fondsen_get_job_company_organization_type_ids($company_term_id) {
    $ids = get_term_meta((int) $company_term_id, '_fondsen_organization_type_ids', true);
    return fondsen_normalize_organization_type_ids(is_array($ids) ? $ids : []);
}

function fondsen_get_organization_type_terms_by_ids($ids) {
    $ids = fondsen_normalize_organization_type_ids($ids);
    if (empty($ids)) {
        return [];
    }

    $terms = get_terms([
        'taxonomy'   => 'organization_type',
        'hide_empty' => false,
        'include'    => $ids,
        'orderby'    => 'include',
    ]);

    return is_wp_error($terms) ? [] : $terms;
}

function fondsen_get_job_listing_organization_type_ids_from_companies($job_id, &$has_company_config = null) {
    $has_company_config = false;
    $company_terms = get_the_terms((int) $job_id, 'job_company');
    if (is_wp_error($company_terms) || empty($company_terms)) {
        return [];
    }

    $type_ids = [];
    foreach ($company_terms as $company_term) {
        if (metadata_exists('term', $company_term->term_id, '_fondsen_organization_type_ids')) {
            $has_company_config = true;
        }

        $type_ids = array_merge(
            $type_ids,
            fondsen_get_job_company_organization_type_ids($company_term->term_id)
        );
    }

    return fondsen_normalize_organization_type_ids($type_ids);
}

function fondsen_get_job_listing_organization_type_terms($job_id) {
    $has_company_config = false;
    $type_ids = fondsen_get_job_listing_organization_type_ids_from_companies($job_id, $has_company_config);

    if (!empty($type_ids)) {
        return fondsen_get_organization_type_terms_by_ids($type_ids);
    }

    $legacy_terms = get_the_terms((int) $job_id, 'organization_type');
    return is_wp_error($legacy_terms) || empty($legacy_terms) ? [] : $legacy_terms;
}

function fondsen_render_organization_type_checkboxes($selected_ids = []) {
    $selected_ids = fondsen_normalize_organization_type_ids($selected_ids);
    $terms = fondsen_get_all_organization_type_terms();

    wp_nonce_field('fondsen_save_job_company_organization_types', 'fondsen_organization_type_nonce');

    if (empty($terms)) {
        echo '<p class="description">Maak eerst termen aan bij Type organisaties. Daarna kun je ze hier aan organisaties koppelen.</p>';
        return;
    }

    echo '<fieldset>';
    foreach ($terms as $term) {
        echo '<label style="display:block;margin:0 0 6px;">';
        echo '<input type="checkbox" name="fondsen_organization_type_ids[]" value="' . esc_attr($term->term_id) . '" ' . checked(in_array((int) $term->term_id, $selected_ids, true), true, false) . '> ';
        echo esc_html($term->name);
        echo '</label>';
    }
    echo '</fieldset>';
}

function fondsen_render_job_company_organization_types_add_field() {
    ?>
    <div class="form-field term-fondsen-organization-types-wrap">
        <label>Type organisatie</label>
        <?php fondsen_render_organization_type_checkboxes(); ?>
        <p class="description">Kies een of meerdere types. Deze worden automatisch toegepast op alle vacatures van deze organisatie.</p>
    </div>
    <?php
}
add_action('job_company_add_form_fields', 'fondsen_render_job_company_organization_types_add_field');

function fondsen_render_job_company_organization_types_edit_field($term) {
    $selected_ids = fondsen_get_job_company_organization_type_ids($term->term_id);
    ?>
    <tr class="form-field term-fondsen-organization-types-wrap">
        <th scope="row">
            <label>Type organisatie</label>
        </th>
        <td>
            <?php fondsen_render_organization_type_checkboxes($selected_ids); ?>
            <p class="description">Kies een of meerdere types. Deze worden automatisch getoond en gefilterd bij alle vacatures van deze organisatie.</p>
        </td>
    </tr>
    <?php
}
add_action('job_company_edit_form_fields', 'fondsen_render_job_company_organization_types_edit_field');

add_filter('manage_edit-job_company_columns', function ($columns) {
    $new_columns = [];

    foreach ($columns as $key => $label) {
        $new_columns[$key] = $label;

        if ($key === 'name') {
            $new_columns['fondsen_company_logo'] = 'Logo';
            $new_columns['fondsen_organization_types'] = 'Type organisatie';
            $new_columns['fondsen_job_sectors'] = 'Sectoren';
        }
    }

    return $new_columns;
});

add_filter('manage_job_company_custom_column', function ($content, $column_name, $term_id) {
    if ($column_name === 'fondsen_company_logo') {
        $logo_id = fondsen_get_job_company_logo_id((int) $term_id);
        return $logo_id
            ? wp_get_attachment_image($logo_id, 'thumbnail', false, ['style' => 'width:40px;height:40px;object-fit:contain;'])
            : '&mdash;';
    }

    if ($column_name === 'fondsen_organization_types') {
        $terms = fondsen_get_organization_type_terms_by_ids(
            fondsen_get_job_company_organization_type_ids((int) $term_id)
        );

        return empty($terms)
            ? '&mdash;'
            : esc_html(implode(', ', wp_list_pluck($terms, 'name')));
    }

    if ($column_name === 'fondsen_job_sectors') {
        $terms = fondsen_get_job_sector_terms_by_ids(
            fondsen_get_job_company_sector_ids((int) $term_id)
        );

        return empty($terms)
            ? '&mdash;'
            : esc_html(implode(', ', wp_list_pluck($terms, 'name')));
    }

    return $content;
}, 10, 3);

// =========================================================
// 5d) Organisatie term-meta: logo
// =========================================================
function fondsen_get_job_company_logo_id($company_term_id) {
    return absint(get_term_meta((int) $company_term_id, '_fondsen_company_logo_id', true));
}

function fondsen_get_job_listing_company_logo_id($job_id) {
    $post_logo_id = get_post_thumbnail_id((int) $job_id);
    if ($post_logo_id) {
        return (int) $post_logo_id;
    }

    $company_terms = get_the_terms((int) $job_id, 'job_company');
    if (is_wp_error($company_terms) || empty($company_terms)) {
        return 0;
    }

    foreach ($company_terms as $company_term) {
        $logo_id = fondsen_get_job_company_logo_id($company_term->term_id);
        if ($logo_id) {
            return $logo_id;
        }
    }

    return 0;
}

function fondsen_get_job_listing_company_logo_html($job_id, $size = 'thumbnail', $attr = []) {
    $default_attr = [
        'class' => 'company_logo fondsen-company-logo',
        'alt'   => function_exists('get_the_company_name')
            ? trim(wp_strip_all_tags(get_the_company_name((int) $job_id)))
            : trim(wp_strip_all_tags(get_the_title((int) $job_id))),
    ];

    $post_logo_id = get_post_thumbnail_id((int) $job_id);
    if ($post_logo_id) {
        return wp_get_attachment_image($post_logo_id, $size, false, array_merge($default_attr, $attr));
    }

    $meta_logo_url = get_post_meta((int) $job_id, '_company_logo', true);
    if ($meta_logo_url) {
        $image_attr = array_merge($default_attr, $attr);
        $attributes = '';
        foreach ($image_attr as $name => $value) {
            $attributes .= ' ' . esc_attr($name) . '="' . esc_attr($value) . '"';
        }

        return '<img src="' . esc_url($meta_logo_url) . '"' . $attributes . '>';
    }

    $logo_id = fondsen_get_job_listing_company_logo_id((int) $job_id);
    if (!$logo_id) {
        return '';
    }

    return wp_get_attachment_image($logo_id, $size, false, array_merge($default_attr, $attr));
}

function fondsen_render_company_logo_field($logo_id = 0) {
    $logo_id = absint($logo_id);
    $image = $logo_id ? wp_get_attachment_image($logo_id, 'thumbnail', false, ['class' => 'fondsen-company-logo-preview__image']) : '';

    wp_nonce_field('fondsen_save_job_company_logo', 'fondsen_company_logo_nonce');
    ?>
    <div class="fondsen-company-logo-field">
        <input type="hidden" name="fondsen_company_logo_id" class="fondsen-company-logo-id" value="<?php echo esc_attr($logo_id); ?>">
        <div class="fondsen-company-logo-preview" style="margin:0 0 8px;">
            <?php echo $image ?: '<span class="description">Nog geen logo gekozen.</span>'; ?>
        </div>
        <button type="button" class="button fondsen-company-logo-select">Logo kiezen</button>
        <button type="button" class="button fondsen-company-logo-remove" <?php disabled(!$logo_id); ?>>Logo verwijderen</button>
    </div>
    <?php
}

function fondsen_render_job_company_logo_add_field() {
    ?>
    <div class="form-field term-fondsen-company-logo-wrap">
        <label>Organisatielogo</label>
        <?php fondsen_render_company_logo_field(); ?>
        <p class="description">Dit logo wordt automatisch gebruikt bij vacatures van deze organisatie als de vacature zelf geen logo heeft.</p>
    </div>
    <?php
}
add_action('job_company_add_form_fields', 'fondsen_render_job_company_logo_add_field');

function fondsen_render_job_company_logo_edit_field($term) {
    $logo_id = fondsen_get_job_company_logo_id($term->term_id);
    ?>
    <tr class="form-field term-fondsen-company-logo-wrap">
        <th scope="row">
            <label>Organisatielogo</label>
        </th>
        <td>
            <?php fondsen_render_company_logo_field($logo_id); ?>
            <p class="description">Dit logo wordt automatisch gebruikt bij vacatures van deze organisatie als de vacature zelf geen logo heeft.</p>
        </td>
    </tr>
    <?php
}
add_action('job_company_edit_form_fields', 'fondsen_render_job_company_logo_edit_field');

function fondsen_save_job_company_logo($term_id) {
    if (
        !isset($_POST['fondsen_company_logo_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['fondsen_company_logo_nonce'])), 'fondsen_save_job_company_logo')
    ) {
        return;
    }

    if (!current_user_can('manage_categories')) {
        return;
    }

    $logo_id = isset($_POST['fondsen_company_logo_id'])
        ? absint(wp_unslash($_POST['fondsen_company_logo_id']))
        : 0;

    if ($logo_id) {
        update_term_meta((int) $term_id, '_fondsen_company_logo_id', $logo_id);
    } else {
        delete_term_meta((int) $term_id, '_fondsen_company_logo_id');
    }
}
add_action('created_job_company', 'fondsen_save_job_company_logo');
add_action('edited_job_company', 'fondsen_save_job_company_logo');

add_action('admin_enqueue_scripts', function ($hook_suffix) {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->taxonomy !== 'job_company') {
        return;
    }

    wp_enqueue_media();
    wp_add_inline_script('media-editor', "
        document.addEventListener('click', function(event) {
            const selectButton = event.target.closest('.fondsen-company-logo-select');
            const removeButton = event.target.closest('.fondsen-company-logo-remove');

            if (selectButton) {
                event.preventDefault();
                const field = selectButton.closest('.fondsen-company-logo-field');
                const input = field.querySelector('.fondsen-company-logo-id');
                const preview = field.querySelector('.fondsen-company-logo-preview');
                const remove = field.querySelector('.fondsen-company-logo-remove');
                const frame = wp.media({
                    title: 'Organisatielogo kiezen',
                    button: { text: 'Logo gebruiken' },
                    library: { type: 'image' },
                    multiple: false
                });

                frame.on('select', function() {
                    const attachment = frame.state().get('selection').first().toJSON();
                    const imageUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                    input.value = attachment.id;
                    preview.innerHTML = '<img src=\"' + imageUrl + '\" class=\"fondsen-company-logo-preview__image\" style=\"max-width:80px;height:auto;object-fit:contain;\" alt=\"\">';
                    remove.disabled = false;
                });

                frame.open();
                return;
            }

            if (removeButton) {
                event.preventDefault();
                const field = removeButton.closest('.fondsen-company-logo-field');
                field.querySelector('.fondsen-company-logo-id').value = '';
                field.querySelector('.fondsen-company-logo-preview').innerHTML = '<span class=\"description\">Nog geen logo gekozen.</span>';
                removeButton.disabled = true;
            }
        });
    ");

    wp_add_inline_style('common', '
        .fondsen-company-logo-preview__image {
            max-width: 80px;
            height: auto;
            object-fit: contain;
            display: block;
            padding: 6px;
            background: #fff;
            border: 1px solid #dcdcde;
            border-radius: 4px;
        }
    ');
});

// =========================================================
// 5e) Organisatie term-meta: basis sectoren
// =========================================================
if ( ! defined('FONDSEN_COMPANY_SECTOR_SYNC_VERSION') ) {
    define('FONDSEN_COMPANY_SECTOR_SYNC_VERSION', '2026-06-24-company-sectors');
}

function fondsen_get_all_job_sector_terms() {
    $terms = get_terms([
        'taxonomy'   => 'job_sector',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);

    return is_wp_error($terms) ? [] : $terms;
}

function fondsen_normalize_job_sector_ids($ids) {
    $ids = array_values(array_unique(array_filter(array_map('absint', (array) $ids))));
    if (empty($ids)) {
        return [];
    }

    $valid_ids = get_terms([
        'taxonomy'   => 'job_sector',
        'hide_empty' => false,
        'include'    => $ids,
        'fields'     => 'ids',
    ]);

    return is_wp_error($valid_ids)
        ? []
        : array_values(array_intersect($ids, array_map('absint', $valid_ids)));
}

function fondsen_get_job_company_sector_ids($company_term_id) {
    $ids = get_term_meta((int) $company_term_id, '_fondsen_job_sector_ids', true);
    return fondsen_normalize_job_sector_ids(is_array($ids) ? $ids : []);
}

function fondsen_get_job_sector_terms_by_ids($ids) {
    $ids = fondsen_normalize_job_sector_ids($ids);
    if (empty($ids)) {
        return [];
    }

    $terms = get_terms([
        'taxonomy'   => 'job_sector',
        'hide_empty' => false,
        'include'    => $ids,
        'orderby'    => 'include',
    ]);

    return is_wp_error($terms) ? [] : $terms;
}

function fondsen_get_job_listing_sector_ids_from_companies($job_id, &$has_company_config = null) {
    $has_company_config = false;
    $company_terms = get_the_terms((int) $job_id, 'job_company');
    if (is_wp_error($company_terms) || empty($company_terms)) {
        return [];
    }

    $sector_ids = [];
    foreach ($company_terms as $company_term) {
        if (metadata_exists('term', $company_term->term_id, '_fondsen_job_sector_ids')) {
            $has_company_config = true;
        }

        $sector_ids = array_merge(
            $sector_ids,
            fondsen_get_job_company_sector_ids($company_term->term_id)
        );
    }

    return fondsen_normalize_job_sector_ids($sector_ids);
}

function fondsen_get_job_listing_sector_terms($job_id) {
    $current_terms = get_the_terms((int) $job_id, 'job_sector');
    $current_ids = is_wp_error($current_terms) || empty($current_terms)
        ? []
        : wp_list_pluck($current_terms, 'term_id');

    $inherited_ids = fondsen_get_job_listing_sector_ids_from_companies($job_id);
    $sector_ids = fondsen_normalize_job_sector_ids(array_merge($current_ids, $inherited_ids));

    return fondsen_get_job_sector_terms_by_ids($sector_ids);
}

function fondsen_render_job_sector_checkboxes($selected_ids = []) {
    $selected_ids = fondsen_normalize_job_sector_ids($selected_ids);
    $terms = fondsen_get_all_job_sector_terms();

    wp_nonce_field('fondsen_save_job_company_sectors', 'fondsen_job_sector_nonce');

    if (empty($terms)) {
        echo '<p class="description">Maak eerst sectoren aan. Daarna kun je ze hier aan organisaties koppelen.</p>';
        return;
    }

    echo '<fieldset>';
    foreach ($terms as $term) {
        echo '<label style="display:block;margin:0 0 6px;">';
        echo '<input type="checkbox" name="fondsen_job_sector_ids[]" value="' . esc_attr($term->term_id) . '" ' . checked(in_array((int) $term->term_id, $selected_ids, true), true, false) . '> ';
        echo esc_html($term->name);
        echo '</label>';
    }
    echo '</fieldset>';
}

function fondsen_render_job_company_sectors_add_field() {
    ?>
    <div class="form-field term-fondsen-job-sectors-wrap">
        <label>Basis sectoren</label>
        <?php fondsen_render_job_sector_checkboxes(); ?>
        <p class="description">Deze sectoren worden automatisch toegepast op alle vacatures van deze organisatie. Extra sectoren kun je nog per vacature aanvinken.</p>
    </div>
    <?php
}
add_action('job_company_add_form_fields', 'fondsen_render_job_company_sectors_add_field');

function fondsen_render_job_company_sectors_edit_field($term) {
    $selected_ids = fondsen_get_job_company_sector_ids($term->term_id);
    ?>
    <tr class="form-field term-fondsen-job-sectors-wrap">
        <th scope="row">
            <label>Basis sectoren</label>
        </th>
        <td>
            <?php fondsen_render_job_sector_checkboxes($selected_ids); ?>
            <p class="description">Deze sectoren worden automatisch getoond en gefilterd bij alle vacatures van deze organisatie. Extra sectoren kun je nog per vacature aanvinken, bijvoorbeeld IT.</p>
        </td>
    </tr>
    <?php
}
add_action('job_company_edit_form_fields', 'fondsen_render_job_company_sectors_edit_field');

function fondsen_sync_sectors_for_job($job_id, $force = false) {
    $job_id = (int) $job_id;
    if (get_post_type($job_id) !== 'job_listing') {
        return;
    }

    $previous_inherited_ids = fondsen_normalize_job_sector_ids(
        get_post_meta($job_id, '_fondsen_inherited_job_sector_ids', true)
    );

    $has_company_config = false;
    $inherited_ids = fondsen_get_job_listing_sector_ids_from_companies($job_id, $has_company_config);

    if (!$force && !$has_company_config && empty($previous_inherited_ids)) {
        return;
    }

    $current_ids = wp_get_object_terms($job_id, 'job_sector', ['fields' => 'ids']);
    $current_ids = is_wp_error($current_ids) ? [] : array_map('absint', $current_ids);
    $direct_ids = array_values(array_diff($current_ids, $previous_inherited_ids));
    $merged_ids = fondsen_normalize_job_sector_ids(array_merge($direct_ids, $inherited_ids));

    wp_set_object_terms($job_id, $merged_ids, 'job_sector', false);

    if (!empty($inherited_ids)) {
        update_post_meta($job_id, '_fondsen_inherited_job_sector_ids', $inherited_ids);
    } else {
        delete_post_meta($job_id, '_fondsen_inherited_job_sector_ids');
    }
}

function fondsen_sync_sectors_for_company($company_term_id) {
    $job_ids = get_posts([
        'post_type'      => 'job_listing',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'tax_query'      => [
            [
                'taxonomy'         => 'job_company',
                'field'            => 'term_id',
                'terms'            => [(int) $company_term_id],
                'include_children' => false,
            ],
        ],
    ]);

    foreach ($job_ids as $job_id) {
        fondsen_sync_sectors_for_job((int) $job_id, true);
    }
}

function fondsen_save_job_company_sectors($term_id) {
    if (
        !isset($_POST['fondsen_job_sector_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['fondsen_job_sector_nonce'])), 'fondsen_save_job_company_sectors')
    ) {
        return;
    }

    if (!current_user_can('manage_categories')) {
        return;
    }

    $sector_ids = isset($_POST['fondsen_job_sector_ids'])
        ? fondsen_normalize_job_sector_ids(wp_unslash($_POST['fondsen_job_sector_ids']))
        : [];

    if (!empty($sector_ids)) {
        update_term_meta((int) $term_id, '_fondsen_job_sector_ids', $sector_ids);
    } else {
        delete_term_meta((int) $term_id, '_fondsen_job_sector_ids');
    }

    fondsen_sync_sectors_for_company((int) $term_id);
}
add_action('created_job_company', 'fondsen_save_job_company_sectors');
add_action('edited_job_company', 'fondsen_save_job_company_sectors');

function fondsen_sync_organization_types_for_job($job_id, $force = false) {
    $job_id = (int) $job_id;
    if (get_post_type($job_id) !== 'job_listing') {
        return;
    }

    $has_company_config = false;
    $type_ids = fondsen_get_job_listing_organization_type_ids_from_companies($job_id, $has_company_config);

    if (!$force && !$has_company_config) {
        return;
    }

    wp_set_object_terms($job_id, $type_ids, 'organization_type', false);
}

function fondsen_sync_organization_types_for_company($company_term_id) {
    $job_ids = get_posts([
        'post_type'      => 'job_listing',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'tax_query'      => [
            [
                'taxonomy'         => 'job_company',
                'field'            => 'term_id',
                'terms'            => [(int) $company_term_id],
                'include_children' => false,
            ],
        ],
    ]);

    foreach ($job_ids as $job_id) {
        fondsen_sync_organization_types_for_job((int) $job_id, true);
    }
}

function fondsen_save_job_company_organization_types($term_id) {
    if (
        !isset($_POST['fondsen_organization_type_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['fondsen_organization_type_nonce'])), 'fondsen_save_job_company_organization_types')
    ) {
        return;
    }

    if (!current_user_can('manage_categories')) {
        return;
    }

    $type_ids = isset($_POST['fondsen_organization_type_ids'])
        ? fondsen_normalize_organization_type_ids(wp_unslash($_POST['fondsen_organization_type_ids']))
        : [];

    if (!empty($type_ids)) {
        update_term_meta((int) $term_id, '_fondsen_organization_type_ids', $type_ids);
    } else {
        delete_term_meta((int) $term_id, '_fondsen_organization_type_ids');
    }

    fondsen_sync_organization_types_for_company((int) $term_id);
}
add_action('created_job_company', 'fondsen_save_job_company_organization_types');
add_action('edited_job_company', 'fondsen_save_job_company_organization_types');

add_action('save_post_job_listing', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    fondsen_sync_organization_types_for_job((int) $post_id);
    fondsen_sync_sectors_for_job((int) $post_id);
}, 100);

add_action('set_object_terms', function ($object_id, $terms, $tt_ids, $taxonomy) {
    if ($taxonomy !== 'job_company' || get_post_type((int) $object_id) !== 'job_listing') {
        return;
    }

    fondsen_sync_organization_types_for_job((int) $object_id);
    fondsen_sync_sectors_for_job((int) $object_id);
}, 10, 4);

function fondsen_backfill_job_company_organization_type_meta() {
    if (get_option('fondsen_org_type_sync_version') === FONDSEN_ORG_TYPE_SYNC_VERSION) {
        return;
    }

    $job_ids = get_posts([
        'post_type'      => 'job_listing',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ]);

    foreach ($job_ids as $job_id) {
        $company_terms = get_the_terms((int) $job_id, 'job_company');
        $org_terms = get_the_terms((int) $job_id, 'organization_type');

        if (is_wp_error($company_terms) || empty($company_terms) || is_wp_error($org_terms) || empty($org_terms)) {
            continue;
        }

        $org_type_ids = wp_list_pluck($org_terms, 'term_id');
        foreach ($company_terms as $company_term) {
            $existing_ids = fondsen_get_job_company_organization_type_ids($company_term->term_id);
            $merged_ids = fondsen_normalize_organization_type_ids(array_merge($existing_ids, $org_type_ids));

            if (!empty($merged_ids)) {
                update_term_meta((int) $company_term->term_id, '_fondsen_organization_type_ids', $merged_ids);
            }
        }
    }

    foreach ($job_ids as $job_id) {
        fondsen_sync_organization_types_for_job((int) $job_id);
    }

    update_option('fondsen_org_type_sync_version', FONDSEN_ORG_TYPE_SYNC_VERSION, false);
}
add_action('admin_init', 'fondsen_backfill_job_company_organization_type_meta');

function fondsen_backfill_job_company_sector_terms() {
    if (get_option('fondsen_company_sector_sync_version') === FONDSEN_COMPANY_SECTOR_SYNC_VERSION) {
        return;
    }

    $job_ids = get_posts([
        'post_type'      => 'job_listing',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ]);

    foreach ($job_ids as $job_id) {
        fondsen_sync_sectors_for_job((int) $job_id);
    }

    update_option('fondsen_company_sector_sync_version', FONDSEN_COMPANY_SECTOR_SYNC_VERSION, false);
}
add_action('admin_init', 'fondsen_backfill_job_company_sector_terms');

function fondsen_get_job_company_notification_email($job_id) {
    $terms = get_the_terms($job_id, 'job_company');
    if (is_wp_error($terms) || empty($terms)) {
        return '';
    }

    foreach ($terms as $term) {
        $email = sanitize_email(get_term_meta($term->term_id, '_fondsen_notification_email', true));
        if ($email && is_email($email)) {
            return $email;
        }
    }

    return '';
}

function fondsen_get_job_listing_reminder_recipient($job_id) {
    $recipient = fondsen_get_job_company_notification_email($job_id);
    if ($recipient) {
        return $recipient;
    }

    $contact_email = sanitize_email(get_post_meta($job_id, '_contact_email', true));
    if ($contact_email && is_email($contact_email)) {
        return $contact_email;
    }

    $company_email = sanitize_email(get_post_meta($job_id, '_company_email', true));
    if ($company_email && is_email($company_email)) {
        return $company_email;
    }

    return '';
}

function fondsen_get_job_listing_reminder_days() {
    return [30, 60, 90];
}

function fondsen_schedule_job_listing_reminders($job_id) {
    if (get_post_type($job_id) !== 'job_listing' || get_post_status($job_id) !== 'publish') {
        return;
    }

    $published_at = (int) get_post_time('U', true, $job_id);
    if ( ! $published_at ) {
        $published_at = time();
    }

    foreach (fondsen_get_job_listing_reminder_days() as $days) {
        $days = (int) $days;
        $timestamp = $published_at + ($days * DAY_IN_SECONDS);
        $args = [$job_id, $days];

        if ($timestamp <= time() || get_post_meta($job_id, '_fondsen_reminder_sent_' . $days, true)) {
            continue;
        }

        if ( ! wp_next_scheduled('fondsen_job_listing_reminder', $args) ) {
            wp_schedule_single_event($timestamp, 'fondsen_job_listing_reminder', $args);
        }
    }
}

function fondsen_clear_job_listing_reminders($job_id) {
    foreach (fondsen_get_job_listing_reminder_days() as $days) {
        $args = [$job_id, (int) $days];
        $timestamp = wp_next_scheduled('fondsen_job_listing_reminder', $args);
        while ($timestamp) {
            wp_unschedule_event($timestamp, 'fondsen_job_listing_reminder', $args);
            $timestamp = wp_next_scheduled('fondsen_job_listing_reminder', $args);
        }
    }
}

add_action('transition_post_status', function ($new_status, $old_status, $post) {
    if ( ! $post || $post->post_type !== 'job_listing' ) {
        return;
    }

    if ($new_status === 'publish') {
        fondsen_schedule_job_listing_reminders((int) $post->ID);
    } elseif ($old_status === 'publish') {
        fondsen_clear_job_listing_reminders((int) $post->ID);
    }
}, 10, 3);

add_action('before_delete_post', function ($post_id) {
    if (get_post_type($post_id) === 'job_listing') {
        fondsen_clear_job_listing_reminders((int) $post_id);
    }
});

add_action('fondsen_job_listing_reminder', function ($job_id, $days) {
    $job_id = (int) $job_id;
    $days = (int) $days;

    if (get_post_type($job_id) !== 'job_listing' || get_post_status($job_id) !== 'publish') {
        return;
    }

    if (get_post_meta($job_id, '_fondsen_reminder_sent_' . $days, true)) {
        return;
    }

    $recipient = fondsen_get_job_listing_reminder_recipient($job_id);
    if ( ! $recipient ) {
        return;
    }

    $title = get_the_title($job_id);
    $url = get_permalink($job_id);
    $subject = sprintf('Vacaturemelding: "%s" staat %d dagen online', $title, $days);

    $body  = '<!DOCTYPE html><html lang="nl"><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;">';
    $body .= '<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:32px 0;"><tr><td align="center">';
    $body .= '<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;">';
    $body .= '<tr><td style="background:#0884CC;padding:28px 40px;"><h1 style="margin:0;color:#ffffff;font-size:22px;">Impact Vacatures</h1><p style="margin:6px 0 0;color:rgba(255,255,255,.85);font-size:14px;">Vacaturemelding</p></td></tr>';
    $body .= '<tr><td style="padding:32px 40px;">';
    $body .= '<p style="margin:0 0 16px;font-size:15px;color:#333;line-height:1.6;">Je vacature <strong>' . esc_html($title) . '</strong> staat inmiddels ' . esc_html((string) $days) . ' dagen online op Impact Vacatures.</p>';
    $body .= '<p style="margin:0 0 24px;font-size:15px;color:#333;line-height:1.6;">Controleer of de vacature nog actueel is. Wil je de vacature aanpassen, verlengen of sluiten? Neem dan contact met ons op.</p>';
    if ($url) {
        $body .= '<p style="margin:0 0 24px;"><a href="' . esc_url($url) . '" style="display:inline-block;background:#0884CC;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:4px;font-size:14px;font-weight:700;">Bekijk vacature</a></p>';
    }
    $body .= '<p style="margin:0;font-size:15px;color:#333;line-height:1.6;">Met vriendelijke groet,<br><strong>Team Impact Vacatures</strong></p>';
    $body .= '</td></tr>';
    $body .= '<tr><td style="background:#f9f9f9;padding:20px 40px;text-align:center;font-size:12px;color:#999;">Impact Vacatures &mdash; <a href="mailto:informatie@impactvacatures.nl" style="color:#0884CC;">informatie@impactvacatures.nl</a></td></tr>';
    $body .= '</table></td></tr></table></body></html>';

    $sent = wp_mail($recipient, $subject, $body, ['Content-Type: text/html; charset=UTF-8']);
    if ($sent) {
        update_post_meta($job_id, '_fondsen_reminder_sent_' . $days, current_time('mysql'));
    }
}, 10, 2);


// =========================================================
// 6) WPJM shortcode defaults uitbreiden (zodat args netjes bestaan)
// =========================================================
add_filter('job_manager_output_jobs_defaults', function($defaults) {
    $defaults['job_company']      = '';
    $defaults['job_tag']          = '';
    $defaults['job_sector']       = '';
    $defaults['organization_type'] = '';
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
        'organization_type' => 'organization_type',
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
        'filter_organization_type'    => 'organization_type',
        'filter_job_types'            => 'job_listing_type',
        'filter_job_listing_category' => 'job_listing_category',
    ];

    // 1) Filters uit request. De pretty URLs vullen $_GET; AJAX vult $_POST.
    foreach ($custom_taxonomies as $filter_key => $taxonomy) {
        $request_terms = [];

        if ( ! empty($_POST[$filter_key]) ) {
            $request_terms = (array) wp_unslash($_POST[$filter_key]);
        } elseif ( ! empty($_GET[$filter_key]) ) {
            $request_terms = (array) wp_unslash($_GET[$filter_key]);
        }

        if ( ! empty($request_terms) ) {
            $terms = $request_terms;
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

// 19) Quill.js + MapLibre GL JS laden op single job listing pagina's
add_action('wp_enqueue_scripts', function() {
    if (!is_singular('job_listing')) return;

    wp_enqueue_style('quill-snow', 'https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css', [], null);
    wp_enqueue_script('quill-js', 'https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js', [], null, true);

    wp_enqueue_style('maplibre-gl', 'https://unpkg.com/maplibre-gl@4/dist/maplibre-gl.css', [], '4');
    wp_enqueue_script('maplibre-gl', 'https://unpkg.com/maplibre-gl@4/dist/maplibre-gl.js', [], '4', true);
});

// 21) Geocode locatietekst via Nominatim + cache in post meta
function fondsen_get_job_location_coords($post_id, $location) {
    $lat = get_post_meta($post_id, '_job_location_lat', true);
    $lng = get_post_meta($post_id, '_job_location_lng', true);

    if ($lat !== '' && $lng !== '') {
        return ['lat' => (float) $lat, 'lng' => (float) $lng];
    }

    if (empty($location)) return null;

    $response = wp_remote_get(
        'https://nominatim.openstreetmap.org/search?' . http_build_query([
            'q'      => $location . ', Nederland',
            'format' => 'json',
            'limit'  => 1,
        ]),
        [
            'timeout' => 5,
            'headers' => ['User-Agent' => 'impactvacatures.nl/1.0 (informatie@impactvacatures.nl)'],
        ]
    );

    if (is_wp_error($response)) return null;

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($body[0]['lat'])) return null;

    $lat = (float) $body[0]['lat'];
    $lng = (float) $body[0]['lon'];

    update_post_meta($post_id, '_job_location_lat', $lat);
    update_post_meta($post_id, '_job_location_lng', $lng);

    return ['lat' => $lat, 'lng' => $lng];
}

// 22) Admin: Photon locatie-autocomplete op job_listing edit scherm
add_action('admin_enqueue_scripts', function ($hook) {
    global $post;
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) return;
    if (!$post || $post->post_type !== 'job_listing') return;

    wp_enqueue_style(
        'fondsen-admin-location',
        get_stylesheet_directory_uri() . '/assets/admin-location.css',
        [],
        filemtime(get_stylesheet_directory() . '/assets/admin-location.css')
    );

    wp_enqueue_script(
        'fondsen-admin-location',
        get_stylesheet_directory_uri() . '/assets/admin-location.js',
        [],
        filemtime(get_stylesheet_directory() . '/assets/admin-location.js'),
        true
    );
});

// Hidden geo-velden + nonce in het job_listing edit scherm
add_action('add_meta_boxes', function () {
    add_meta_box(
        'fondsen_geo_coords',
        'Geocoördinaten (Photon)',
        function ($post) {
            wp_nonce_field('fondsen_geo_save_' . $post->ID, 'fondsen_geo_nonce');
            $lat = get_post_meta($post->ID, '_job_location_lat', true);
            $lng = get_post_meta($post->ID, '_job_location_lng', true);
            echo '<p style="color:#666;font-size:12px;margin:0 0 8px;">Worden automatisch ingevuld via de locatie-autocomplete.</p>';
            echo '<label style="display:block;margin-bottom:4px;font-size:12px;">Latitude</label>';
            echo '<input type="text" id="fn-geo-lat" name="fn_geo_lat" value="' . esc_attr($lat) . '" style="width:100%;margin-bottom:8px;" readonly>';
            echo '<label style="display:block;margin-bottom:4px;font-size:12px;">Longitude</label>';
            echo '<input type="text" id="fn-geo-lng" name="fn_geo_lng" value="' . esc_attr($lng) . '" style="width:100%;" readonly>';
        },
        'job_listing',
        'side',
        'default'
    );
});

// Sla geocoördinaten op na admin-save
add_action('save_post_job_listing', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (!isset($_POST['fondsen_geo_nonce'])) return;
    if (!wp_verify_nonce($_POST['fondsen_geo_nonce'], 'fondsen_geo_save_' . $post_id)) return;

    $lat_input = isset($_POST['fn_geo_lat']) ? trim($_POST['fn_geo_lat']) : '';
    $lng_input = isset($_POST['fn_geo_lng']) ? trim($_POST['fn_geo_lng']) : '';

    if ($lat_input !== '' && $lng_input !== '') {
        $lat = (float) $lat_input;
        $lng = (float) $lng_input;
        if ($lat && $lng) {
            update_post_meta($post_id, '_job_location_lat', $lat);
            update_post_meta($post_id, '_job_location_lng', $lng);
            update_post_meta($post_id, '_job_geolocation_lat', $lat);
            update_post_meta($post_id, '_job_geolocation_long', $lng);
        }
    } else {
        // Locatie handmatig getypt zonder autocomplete: cached coords wissen
        delete_post_meta($post_id, '_job_location_lat');
        delete_post_meta($post_id, '_job_location_lng');
    }
});

// 20) Elementor Pro theme builder uitschakelen voor single job listings
// (content-single-job_listing.php verzorgt de volledige opmaak)
add_filter('elementor/theme/need_override_location', function($need_override, $location) {
    if (is_singular('job_listing')) {
        return false;
    }
    return $need_override;
}, 10, 2);

// 21) Voeg found_count toe aan WP Job Manager AJAX-response
add_filter('job_manager_get_listings_result', function($result, $jobs) {
    $result['found_count'] = (int) $jobs->found_posts;
    return $result;
}, 10, 2);
