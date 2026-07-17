<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

if (!function_exists('si_rich_text_has_content')) {
    function si_rich_text_has_content($value): bool {
        $text = html_entity_decode(wp_strip_all_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[\s\x{00A0}]+/u', '', $text);
        return is_string($text) && $text !== '';
    }
}

if (!function_exists('si_redirect_or_fallback')) {
    function si_redirect_or_fallback(string $url): void {
        $url = wp_validate_redirect($url, home_url('/'));

        if (!headers_sent()) {
            wp_safe_redirect($url);
            exit;
        }

        echo '<script>window.location.href=' . wp_json_encode($url) . ';</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . esc_url($url) . '"></noscript>';
        exit;
    }
}

if (!function_exists('si_is_duplicate_form_submission')) {
    function si_is_duplicate_form_submission(string $form_key, array $payload, int $ttl = 300): bool {
        $payload = array_filter($payload, static function ($value) {
            return $value !== null && $value !== '';
        });
        ksort($payload);

        $lock_key = 'si_form_' . md5($form_key . '|' . wp_json_encode($payload));

        if (get_transient($lock_key)) {
            return true;
        }

        set_transient($lock_key, time(), $ttl);
        return false;
    }
}

if (!function_exists('si_admin_mail_headers')) {
    function si_admin_mail_headers(string $reply_to = ''): array {
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: support@studentinhuren.nl',
        ];

        $reply_to = sanitize_email($reply_to);
        if ($reply_to && is_email($reply_to)) {
            $headers[] = 'Reply-To: ' . $reply_to;
        }

        return $headers;
    }
}

if (!function_exists('si_admin_email_value')) {
    function si_admin_email_value(string $value, string $type = 'text'): string {
        $value = trim($value);

        if ($value === '') {
            return '&mdash;';
        }

        if ($type === 'email' && is_email($value)) {
            return '<a href="mailto:' . esc_attr($value) . '" style="color:#2f5f80;text-decoration:none;">' . esc_html($value) . '</a>';
        }

        if ($type === 'tel') {
            $tel = preg_replace('/[^\d+]/', '', $value);
            return '<a href="tel:' . esc_attr($tel) . '" style="color:#2f5f80;text-decoration:none;">' . esc_html($value) . '</a>';
        }

        if ($type === 'url') {
            return '<a href="' . esc_url($value) . '" style="color:#2f5f80;text-decoration:none;">' . esc_html($value) . '</a>';
        }

        return esc_html($value);
    }
}

if (!function_exists('si_build_admin_email')) {
    function si_build_admin_email(string $title, string $intro, array $fields, string $message_label = '', string $message_html = '', int $post_id = 0): string {
        $rows = '';

        foreach ($fields as $field) {
            $label = isset($field['label']) ? (string) $field['label'] : '';
            $value = isset($field['value']) ? (string) $field['value'] : '';
            $type  = isset($field['type']) ? (string) $field['type'] : 'text';

            $rows .= '<tr>';
            $rows .= '<td style="padding:10px 14px;border-bottom:1px solid #e5e7eb;color:#6b7280;font-size:13px;width:150px;">' . esc_html($label) . '</td>';
            $rows .= '<td style="padding:10px 14px;border-bottom:1px solid #e5e7eb;color:#111827;font-size:14px;font-weight:600;">' . si_admin_email_value($value, $type) . '</td>';
            $rows .= '</tr>';
        }

        $message_block = '';
        if ($message_label && si_rich_text_has_content($message_html)) {
            $message_block = '
                <div style="margin-top:22px;">
                    <h2 style="margin:0 0 10px;font-size:15px;line-height:1.3;color:#111827;">' . esc_html($message_label) . '</h2>
                    <div style="padding:16px;border:1px solid #e5e7eb;border-radius:8px;background:#f9fafb;color:#111827;font-size:14px;line-height:1.6;">' . wp_kses_post($message_html) . '</div>
                </div>';
        }

        $admin_link = '';
        if ($post_id > 0) {
            $admin_link = '
                <p style="margin:22px 0 0;">
                    <a href="' . esc_url(admin_url('post.php?post=' . $post_id . '&action=edit')) . '" style="display:inline-block;padding:10px 14px;border-radius:6px;background:#2f5f80;color:#ffffff;text-decoration:none;font-size:14px;font-weight:700;">Bekijk in WordPress</a>
                </p>';
        }

        return '<!doctype html>
<html>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Inter,Arial,sans-serif;color:#111827;">
    <div style="padding:28px 16px;">
        <div style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;">
            <div style="padding:22px 26px;background:#2f5f80;color:#ffffff;">
                <p style="margin:0 0 6px;font-size:13px;font-weight:700;letter-spacing:.02em;text-transform:uppercase;">Studentinhuren.nl</p>
                <h1 style="margin:0;font-size:22px;line-height:1.25;color:#ffffff;">' . esc_html($title) . '</h1>
            </div>
            <div style="padding:24px 26px;">
                <p style="margin:0 0 18px;color:#374151;font-size:15px;line-height:1.6;">' . esc_html($intro) . '</p>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">' . $rows . '</table>
                ' . $message_block . '
                ' . $admin_link . '
            </div>
        </div>
    </div>
</body>
</html>';
    }
}

require_once get_stylesheet_directory() . '/inc/activecampaign.php';
require_once get_stylesheet_directory() . '/inc/shortcode-informatie-aanvragen.php';
require_once get_stylesheet_directory() . '/inc/shortcode-informatie-aanvragen-compact.php';
require_once get_stylesheet_directory() . '/inc/shortcode-latest-opdrachten.php';
require_once get_stylesheet_directory() . '/inc/shortcode-opdracht-plaatsen.php';
require_once get_stylesheet_directory() . '/inc/shortcode-tarieven.php';
require_once get_stylesheet_directory() . '/inc/shortcode-vacature-plaatsen.php';
require_once get_stylesheet_directory() . '/inc/split-hero-meta.php';

/**
 * ✅ CUSTOM HEADER: geregeld via child theme header.php (overschrijft Hello Elementor).
 * De navigatie wordt direct ingeladen in header.php zodat deze overal verschijnt.
 * Geen wp_body_open hook nodig; Hello Elementor's eigen header wordt niet geladen.
 */

/**
 * ✅ NAV WALKER (behouden voor eventueel toekomstig gebruik)
 */
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
    ]);
});

function si_render_breadcrumbs(string $nav_class = '', string $wrapper_class = ''): void
{
    $nav_class_attr = $nav_class ? ' class="' . esc_attr($nav_class) . '"' : '';

    if ($wrapper_class) {
        echo '<div class="' . esc_attr($wrapper_class) . '">';
    }

    if (function_exists('yoast_breadcrumb')) {
        yoast_breadcrumb('<nav' . $nav_class_attr . ' aria-label="Breadcrumb">', '</nav>');
    } else {
        echo '<nav' . $nav_class_attr . ' aria-label="Breadcrumb">';
        echo '<a href="' . esc_url(home_url('/')) . '">Home</a>';
        echo '<span class="si-breadcrumb-sep" aria-hidden="true"> / </span>';
        echo '<span>' . esc_html(get_the_title()) . '</span>';
        echo '</nav>';
    }

    if ($wrapper_class) {
        echo '</div>';
    }
}

/**
 * ✅ ENQUEUE STYLES (with Elementor check) + Select2
 */
add_action('wp_enqueue_scripts', function () {
    $dependencies = ['parent-style'];

    if (did_action('elementor/loaded') && wp_style_is('elementor-frontend', 'registered')) {
        $dependencies[] = 'elementor-frontend';
    }

    // Parent & child styles
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', $dependencies, wp_get_theme()->get('Version'));

    // Fonts
    wp_enqueue_style('poppins-font', 'https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap', [], null);
    wp_enqueue_style('inter-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', [], null);
    wp_enqueue_style('roboto-font', 'https://fonts.googleapis.com/css2?family=Roboto:wght@500&display=swap', [], null);
    wp_enqueue_style('custom-fonts', get_stylesheet_directory_uri() . '/fonts/fonts.css');

    // Globale knoppen
    if (file_exists(get_stylesheet_directory() . '/css/buttons.css')) {
        wp_enqueue_style('studentinhuren-buttons', get_stylesheet_directory_uri() . '/css/buttons.css', ['child-style'], filemtime(get_stylesheet_directory() . '/css/buttons.css'));
    }

    // Werkzoekende knoppen (eigen classes, hardcoded kleuren)
    if (file_exists(get_stylesheet_directory() . '/css/buttons-werkzoekende.css')) {
        wp_enqueue_style('studentinhuren-buttons-werkzoekende', get_stylesheet_directory_uri() . '/css/buttons-werkzoekende.css', ['child-style'], filemtime(get_stylesheet_directory() . '/css/buttons-werkzoekende.css'));
    }

    // Header CSS
    if (file_exists(get_stylesheet_directory() . '/css/header.css')) {
        wp_enqueue_style('studentinhuren-header', get_stylesheet_directory_uri() . '/css/header.css', ['child-style'], filemtime(get_stylesheet_directory() . '/css/header.css'));
    }

    // Footer CSS
    if (file_exists(get_stylesheet_directory() . '/css/footer.css')) {
        wp_enqueue_style('studentinhuren-footer', get_stylesheet_directory_uri() . '/css/footer.css', ['child-style'], filemtime(get_stylesheet_directory() . '/css/footer.css'));
    }

    // Landing page CSS
    if (file_exists(get_stylesheet_directory() . '/css/landing.css')) {
        wp_enqueue_style('studentinhuren-landing', get_stylesheet_directory_uri() . '/css/landing.css', ['child-style'], filemtime(get_stylesheet_directory() . '/css/landing.css'));
    }

    // Split hero page template CSS
    if (is_page_template('page-split-hero.php') && file_exists(get_stylesheet_directory() . '/css/split-hero.css')) {
        wp_enqueue_style('work-sans-font', 'https://fonts.googleapis.com/css2?family=Work+Sans:wght@700;800;900&display=swap', [], null);
        wp_enqueue_style('studentinhuren-split-hero', get_stylesheet_directory_uri() . '/css/split-hero.css', ['child-style'], filemtime(get_stylesheet_directory() . '/css/split-hero.css'));
    }

    // Homepage hero CSS
    if (file_exists(get_stylesheet_directory() . '/css/hero-homepage.css')) {
        wp_enqueue_style('studentinhuren-hero', get_stylesheet_directory_uri() . '/css/hero-homepage.css', ['child-style'], filemtime(get_stylesheet_directory() . '/css/hero-homepage.css'));
    }

    // Phosphor Icons (gebruikt in hero & navigation)
    wp_enqueue_style('phosphor-icons', 'https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css', [], null);

    // ✅ Select2 (nodig omdat je het in de job-filters initieert)
    wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', [], null);
    wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], null, true);

    // Forms styling (informatie aanvragen, etc.)
    if (file_exists(get_stylesheet_directory() . '/css/forms.css')) {
        wp_enqueue_style('si-forms', get_stylesheet_directory_uri() . '/css/forms.css', ['child-style'], filemtime(get_stylesheet_directory() . '/css/forms.css'));
    }

    // Quill.js rich text editor
    wp_enqueue_style('quill-snow', 'https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css', [], null);
    wp_enqueue_script('quill-js', 'https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js', [], null, true);
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
    // Organisaties
    register_taxonomy('job_company', 'job_listing', [
        'labels' => ['name' => 'Organisaties'],
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'organisatie'],
    ]);

    // Regions
    register_taxonomy('job_regio', 'job_listing', [
        'labels' => ['name' => 'Regions'],
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'regio'],
    ]);

    // Job names
    register_taxonomy('job_name', 'job_listing', [
        'labels' => ['name' => 'Job Names'],
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'functie'],
        'meta_box_cb' => 'post_categories_meta_box',
    ]);

    // Salary range
    register_taxonomy('salary_range', 'job_listing', [
        'labels' => ['name' => 'Salary Ranges'],
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'salaris'],
    ]);

    // Vakgebied
    register_taxonomy('vakgebied', ['job_listing'], [
        'label' => 'Vakgebied',
        'hierarchical' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'rewrite' => ['slug' => 'vakgebied'],
    ]);

    // ✅ Job Tags (niet-hiërarchisch)
    register_taxonomy('job_tag', 'job_listing', [
        'labels' => [
            'name'          => __('Job Tags', 'textdomain'),
            'singular_name' => __('Job Tag', 'textdomain'),
            'all_items'     => __('All Tags', 'textdomain'),
            'add_new_item'  => __('Add New Tag', 'textdomain'),
            'edit_item'     => __('Edit Tag', 'textdomain'),
        ],
        'hierarchical'      => false,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'job-tag'],
    ]);

    // ✅ Skills (vervangt 'certificering')
    register_taxonomy('skills', 'job_listing', [
        'labels' => [
            'name'          => 'Skills',
            'singular_name' => 'Skill',
            'all_items'     => 'Alle skills',
            'edit_item'     => 'Bewerk skill',
            'add_new_item'  => 'Nieuwe skill toevoegen',
        ],
        'hierarchical'      => true,   // wil je tags-achtig gedrag? zet op false.
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'skills'],
    ]);
});

/**
 * ✅ SHORTCODE FILTERS: [jobs job_company="..." job_sector="..." job_tag="..." skills="..." job_listing_type="..."]
 *    Zet tax_query alvast vanuit shortcode-attributen.
 */
add_filter('job_manager_get_listings_shortcode_args', function($atts){
    global $sj_job_shortcode_atts;
    $sj_job_shortcode_atts = $atts;

    $custom_filters = [
        'job_company'       => 'job_company',
        'job_tag'           => 'job_tag',
        'job_sector'        => 'job_sector',
        'skills'            => 'skills',             // <— vervangen
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
 * ✅ DEFAULT SHORTCODE ATTRIBUTES
 */
add_filter('job_manager_output_jobs_defaults', function($defaults) {
    $defaults['job_company']      = '';
    $defaults['job_tag']          = '';
    $defaults['job_sector']       = '';
    $defaults['skills']           = ''; // <— vervangen
    $defaults['job_listing_type'] = '';
    return $defaults;
});

/**
 * ✅ CENTRALE SERVER-HOOK: combineer AJAX form_data + (indien nodig) shortcode filters
 *    Map alle filter_* selects naar tax_query (met skills i.p.v. certificering).
 */
add_filter('get_job_listings_query_args', function ($query_args, $args) {
    global $sj_job_shortcode_atts;

    // 1) AJAX form_data uitpakken (WPJM stuurt het formulier zo mee)
    if (isset($_POST['form_data'])) {
        parse_str($_POST['form_data'], $parsed);
        foreach ($parsed as $key => $value) {
            $_POST[$key] = $value;
        }
        error_log('🧩 Parsed form_data: ' . print_r($parsed, true));
    }

    // 2) Mapping van POST keys naar taxonomieën
    $custom_taxonomies = [
        'filter_job_tag'                 => 'job_tag',
        'filter_job_sector'              => 'job_sector',
        'filter_job_company'             => 'job_company',
        'filter_skills'                  => 'skills',               // <— vervangen
        'filter_job_types'               => 'job_listing_type',
        // 'filter_job_listing_category'  => 'job_listing_category', // alleen toevoegen als je deze select ook echt hebt
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

    // 3) Als er géén AJAX form_data is maar wel shortcode-atts, vul die in (failsafe)
    if (!empty($sj_job_shortcode_atts) && empty($_POST['form_data'])) {
        foreach ($custom_taxonomies as $filter_key => $taxonomy) {
            $key = str_replace('filter_', '', $filter_key); // bv. 'skills'
            if (!empty($sj_job_shortcode_atts[$key])) {
                $terms = array_map('sanitize_title', explode(',', $sj_job_shortcode_atts[$key]));
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
        $query_args['tax_query']['relation'] = 'AND';
        error_log('📦 TAX_QUERY in get_job_listings_query_args: ' . print_r($query_args['tax_query'], true));
    } else {
        error_log('📭 Geen tax_query aanwezig in get_job_listings_query_args');
    }

    return $query_args;
}, 10, 2);

/**
 * ✅ DEBUG: laat zien wat er in de main WP_Query terecht komt
 */
add_action('pre_get_posts', function($query) {
    if (!is_admin() && $query->is_main_query() && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'job_listing') {
        error_log('👉 WP_Query tax_query: ' . print_r($query->query_vars['tax_query'], true));
    }
});

/**
 * ✅ Admin bewerk-scherm: extra meta-veld
 */
add_filter('job_manager_job_listing_data_fields', function($fields){
    $fields['_intro_text'] = [
        'label'       => __('Inleidende tekst', 'job_manager'),
        'type'        => 'textarea',
        'placeholder' => __('Korte intro (1–2 zinnen)…', 'job_manager'),
        'description' => __('Wordt getoond in het vacature-overzicht.', 'job_manager'),
    ];
    return $fields;
});

/**
 * ✅ Front-end submit job form: extra veld
 */
add_filter('submit_job_form_fields', function($fields){
    $fields['job']['intro_text'] = [
        'label'       => __('Inleidende tekst', 'job_manager'),
        'type'        => 'textarea',
        'required'    => false,
        'placeholder' => __('Korte intro (1–2 zinnen)…', 'job_manager'),
        'priority'    => 7,
    ];
    return $fields;
});

/**
 * ✅ Opslaan front-end -> naar meta _intro_text
 */
add_action('job_manager_update_job_data', function($job_id, $values){
    if ( isset($values['job']['intro_text']) ) {
        update_post_meta($job_id, '_intro_text', wp_kses_post($values['job']['intro_text']));
    }
}, 10, 2);
