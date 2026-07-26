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
require_once get_stylesheet_directory() . '/inc/shortcode-vacature-directory.php';
require_once get_stylesheet_directory() . '/includes/blog-meta.php';
require_once get_stylesheet_directory() . '/inc/shortcode-snel-plaatsen.php';
require_once get_stylesheet_directory() . '/inc/vacature-cpt.php';
require_once get_stylesheet_directory() . '/inc/job-listing-meta.php';
require_once get_stylesheet_directory() . '/inc/job-favorites.php';
require_once get_stylesheet_directory() . '/inc/job-expiry.php';
require_once get_stylesheet_directory() . '/inc/uitgelichte-werkgever.php';
require_once get_stylesheet_directory() . '/inc/laravel-vacancy-sync.php';

if (function_exists('sj_job_plaatsen_shortcode')) {
    add_shortcode('sc_post_job', 'sj_job_plaatsen_shortcode');
    add_shortcode('sj_post_job', 'sj_job_plaatsen_shortcode');
}

if (function_exists('sj_snel_plaatsen_shortcode')) {
    add_shortcode('sc_quick_post', 'sj_snel_plaatsen_shortcode');
    add_shortcode('sj_quick_post', 'sj_snel_plaatsen_shortcode');
}

if (function_exists('sj_pricing_shortcode')) {
    add_shortcode('sc_pricing', 'sj_pricing_shortcode');
}

if (function_exists('sj_job_directory_shortcode')) {
    add_shortcode('sj_job_categories', 'sj_job_directory_shortcode');
}

if (function_exists('sj_uitgelichte_employers_shortcode')) {
    add_shortcode('sj_featured_employers', 'sj_uitgelichte_employers_shortcode');
}

/**
 * Central sender for outgoing theme email.
 */
add_filter('wp_mail_from', function ($from_email) {
    return 'support@sustainablejobs.com';
});

add_filter('wp_mail_from_name', function ($from_name) {
    return 'Sustainablejobs.com';
});

if (!function_exists('sj_get_blog_category_colors')) {
    function sj_get_blog_category_colors($category) {
        $slug = '';

        if ($category instanceof WP_Term) {
            $slug = $category->slug;
        } elseif (is_numeric($category)) {
            $term = get_term((int) $category, 'category');
            $slug = ($term && !is_wp_error($term)) ? $term->slug : '';
        } elseif (is_string($category)) {
            $slug = $category;
        }

        $slug = sanitize_title($slug);

        $fixed_colors = [
            'event'         => ['bg' => '#FDE2DF', 'marker' => '#FFC9C3', 'text' => '#A92720', 'border' => '#F8B9B3'],
            'events'        => ['bg' => '#FDE2DF', 'marker' => '#FFC9C3', 'text' => '#A92720', 'border' => '#F8B9B3'],
            'interview'     => ['bg' => '#DDF2FF', 'marker' => '#BAE5FF', 'text' => '#075985', 'border' => '#A9D8F5'],
            'news'          => ['bg' => '#DDF7E7', 'marker' => '#BDF3D0', 'text' => '#086A3A', 'border' => '#AAE4BE'],
            'promotion'     => ['bg' => '#FFF0D8', 'marker' => '#FFDFA6', 'text' => '#9A4A00', 'border' => '#F7C982'],
            'work'          => ['bg' => '#EDE9FE', 'marker' => '#DCD3FF', 'text' => '#5B21B6', 'border' => '#C9BDFB'],
            'uncategorized' => ['bg' => '#DDEB9A', 'marker' => '#CFE57C', 'text' => '#073D4F', 'border' => '#C5D77F'],
        ];

        if (isset($fixed_colors[$slug])) {
            return $fixed_colors[$slug];
        }

        $hue = $slug !== '' ? abs(crc32($slug)) % 360 : 188;

        return [
            'bg'     => sprintf('hsl(%d, 78%%, 91%%)', $hue),
            'marker' => sprintf('hsl(%d, 84%%, 84%%)', $hue),
            'text'   => sprintf('hsl(%d, 70%%, 28%%)', $hue),
            'border' => sprintf('hsl(%d, 68%%, 78%%)', $hue),
        ];
    }
}

if (!function_exists('sj_get_blog_category_style')) {
    function sj_get_blog_category_style($category) {
        $colors = sj_get_blog_category_colors($category);

        return sprintf(
            '--sj-cat-bg:%s;--sj-cat-marker:%s;--sj-cat-text:%s;--sj-cat-border:%s;',
            esc_attr($colors['bg']),
            esc_attr($colors['marker'] ?? $colors['bg']),
            esc_attr($colors['text']),
            esc_attr($colors['border'])
        );
    }
}

/**
 * Allow multiple job types per listing in WP Job Manager.
 */
add_filter('job_manager_multi_job_type', '__return_true');

// Automatic filter URLs are disabled; flush once so the old rule disappears.
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

    // Child theme main CSS with automatic cache busting.
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
    wp_enqueue_style('roboto-font', 'https://fonts.googleapis.com/css2?family=Roboto:wght@500;700&display=swap', [], null);
    wp_enqueue_style('work-sans-font', 'https://fonts.googleapis.com/css2?family=Work+Sans:wght@700&display=swap', [], null);

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

    if (file_exists(get_stylesheet_directory() . '/css/job-favorites.css')) {
        wp_enqueue_style(
            'sj-job-favorites',
            get_stylesheet_directory_uri() . '/css/job-favorites.css',
            ['sj-header'],
            filemtime(get_stylesheet_directory() . '/css/job-favorites.css')
        );
    }

    // Elementor Forms styling
    wp_enqueue_style(
        'sj-elementor-forms',
        get_stylesheet_directory_uri() . '/css/elementor-forms.css',
        ['child-style'],
        filemtime(get_stylesheet_directory() . '/css/elementor-forms.css')
    );

    // Form styling (job posting, etc.).
    if (file_exists(get_stylesheet_directory() . '/css/forms.css')) {
        wp_enqueue_style(
            'sj-forms',
            get_stylesheet_directory_uri() . '/css/forms.css',
            ['child-style'],
            filemtime(get_stylesheet_directory() . '/css/forms.css')
        );
    }

    if (file_exists(get_stylesheet_directory() . '/css/vacature-directory.css')) {
        wp_enqueue_style(
            'sj-job-directory',
            get_stylesheet_directory_uri() . '/css/vacature-directory.css',
            ['child-style'],
            filemtime(get_stylesheet_directory() . '/css/vacature-directory.css')
        );
    }

    if (file_exists(get_stylesheet_directory() . '/js/vacature-directory.js')) {
        wp_enqueue_script(
            'sj-job-directory',
            get_stylesheet_directory_uri() . '/js/vacature-directory.js',
            [],
            filemtime(get_stylesheet_directory() . '/js/vacature-directory.js'),
            true
        );
    }

    // Blog CSS (overview and single posts).
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

    if (file_exists(get_stylesheet_directory() . '/js/job-favorites.js')) {
        wp_enqueue_script(
            'sj-job-favorites',
            get_stylesheet_directory_uri() . '/js/job-favorites.js',
            [],
            filemtime(get_stylesheet_directory() . '/js/job-favorites.js'),
            true
        );
        wp_localize_script('sj-job-favorites', 'SJJobFavoritesConfig', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
        ]);
    }
});

/**
 * Nav Walker for dropdown navigation.
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

add_filter('wp_nav_menu_objects', function ($items, $args) {
    if (empty($args->theme_location) || 'primary_nav' !== $args->theme_location) {
        return $items;
    }

    foreach ($items as $item) {
        if ('Log in' !== trim(wp_strip_all_tags($item->title))) {
            continue;
        }

        $item->title  = 'CV Database';
        $item->target = '_blank';

        $rel_parts = preg_split('/\s+/', (string) $item->xfn, -1, PREG_SPLIT_NO_EMPTY);
        $rel_parts = array_unique(array_merge($rel_parts, ['noopener', 'noreferrer']));
        $item->xfn = implode(' ', $rel_parts);
    }

    return $items;
}, 10, 2);

/**
 * Theme setup: logo support, menu registration.
 */
add_action('after_setup_theme', function () {
    add_theme_support('custom-logo', [
        'height'      => 120,
        'width'       => 320,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    register_nav_menus([
        'primary_nav' => 'Primary navigation',
        'footer_nav'  => 'Footer navigation',
    ]);
});

/**
 * Shortcode [sj_header] for use in Elementor.
 */
add_shortcode('sj_header', function () {
    ob_start();
    include get_stylesheet_directory() . '/template-parts/header.php';
    return ob_get_clean();
});

add_shortcode('sc_header', function () {
    return do_shortcode('[sj_header]');
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
 * Keep expired jobs offline, regardless of WP Job Manager settings.
 */
add_filter('option_job_manager_hide_expired', '__return_true');
add_filter('default_option_job_manager_hide_expired', '__return_true');
add_filter('option_job_manager_hide_expired_content', '__return_true');
add_filter('default_option_job_manager_hide_expired_content', '__return_true');

if (!function_exists('sj_get_open_job_listing_count')) {
    function sj_get_open_job_listing_count($args = []) {
        if (!function_exists('get_job_listings')) {
            return 0;
        }

        $jobs = get_job_listings(wp_parse_args($args, [
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ]));

        return $jobs instanceof WP_Query ? (int) $jobs->found_posts : 0;
    }
}

add_filter('job_manager_get_listings_result', function ($result, $jobs) {
    if ($jobs instanceof WP_Query) {
        $result['found_count'] = (int) $jobs->found_posts;
    }

    return $result;
}, 10, 2);

/**
 * WP Job Manager uses the post thumbnail as a company logo by default.
 * Keep logo and cover image separate for the Sustainablejobs job card.
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

        if (!$logo_url) {
            $logo_url = get_the_post_thumbnail_url($post_id, $size) ?: '';
        }

        if (!$logo_url && function_exists('sj_get_job_listing_company_logo_id')) {
            $logo_id = sj_get_job_listing_company_logo_id($post_id);
            if ($logo_id) {
                $logo_url = wp_get_attachment_image_url($logo_id, $size)
                    ?: wp_get_attachment_image_url($logo_id, 'full')
                    ?: '';
            }
        }

        return $logo_url;
    }
}

if (!function_exists('sj_get_company_logo_html')) {
    function sj_get_company_logo_html($post_id = null, $size = 'thumbnail', $attr = []) {
        $post_id  = $post_id ?: get_the_ID();
        $logo_url = sj_get_company_logo_url($post_id, $size);

        if (!$logo_url) {
            return '';
        }

        $company_name = function_exists('get_the_company_name')
            ? get_the_company_name($post_id)
            : get_the_title($post_id);

        $attr = array_merge([
            'class' => 'company_logo sj-company-logo',
            'alt'   => trim(wp_strip_all_tags($company_name)),
        ], $attr);

        $attributes = '';
        foreach ($attr as $name => $value) {
            $attributes .= ' ' . esc_attr($name) . '="' . esc_attr($value) . '"';
        }

        return '<img src="' . esc_url($logo_url) . '"' . $attributes . '>';
    }
}

if (!function_exists('sj_the_company_logo')) {
    function sj_the_company_logo($post_id = null, $size = 'thumbnail') {
        echo sj_get_company_logo_html($post_id, $size);
    }
}

add_filter('submit_job_form_fields', function ($fields) {
    $fields['job']['cover_image'] = [
        'label'       => __('Featured image / cover', 'job_manager'),
        'type'        => 'file',
        'accept'      => 'image/png, image/jpeg, image/webp',
        'required'    => false,
        'priority'    => 7,
        'description' => __('Use this field for the large image on the job card. The company logo remains separate.', 'job_manager'),
    ];

    return $fields;
});

add_filter('job_manager_job_listing_data_fields', function ($fields) {
    $fields['_cover_image'] = [
        'label'       => __('Featured image / cover', 'job_manager'),
        'type'        => 'file',
        'description' => __('Use this field for the large image on the job card. The company logo remains separate.', 'job_manager'),
    ];

    return $fields;
});

/**
 * ✅ REGISTER CUSTOM TAXONOMIES
 */
add_action('init', function () {
    register_taxonomy('job_company', 'job_listing', [
        'label' => 'Organizations',
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'organizations'],
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

    register_taxonomy('job_country', 'job_listing', [
        'label'             => 'Countries',
        'labels'            => [
            'name'                       => 'Countries',
            'singular_name'              => 'Country',
            'search_items'               => 'Search countries',
            'popular_items'              => 'Popular countries',
            'all_items'                  => 'All countries',
            'edit_item'                  => 'Edit country',
            'update_item'                => 'Update country',
            'add_new_item'               => 'Add new country',
            'new_item_name'              => 'New country name',
            'separate_items_with_commas' => 'Separate countries with commas',
            'add_or_remove_items'        => 'Add or remove countries',
            'choose_from_most_used'      => 'Choose from the most used countries',
            'not_found'                  => 'No countries found',
            'menu_name'                  => 'Countries',
        ],
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'country'],
    ]);

    register_taxonomy('organisatie_type', 'job_listing', [
        'label'             => 'Organization type',
        'labels'            => [
            'name'          => 'Organization types',
            'singular_name' => 'Organization type',
            'search_items'  => 'Search organization types',
            'all_items'     => 'All organization types',
            'edit_item'     => 'Edit organization type',
            'update_item'   => 'Update organization type',
            'add_new_item'  => 'Add organization type',
            'new_item_name' => 'New organization type',
            'menu_name'     => 'Organization types',
        ],
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'meta_box_cb'       => false,
        'rewrite'           => ['slug' => 'organization-type'],
    ]);
});



/**
 * Link WP Job Manager taxonomies to pages.
 */
add_action('init', function () {
    register_taxonomy_for_object_type('job_company', 'page');
    register_taxonomy_for_object_type('job_tag', 'page');
    register_taxonomy_for_object_type('job_sector', 'page');
    register_taxonomy_for_object_type('job_country', 'page');
    register_taxonomy_for_object_type('organisatie_type', 'page');
});

add_action('init', function () {
    if (get_option('sj_job_country_rewrite_flush_version') === '2026-07-26-job-country') {
        return;
    }

    flush_rewrite_rules(false);
    update_option('sj_job_country_rewrite_flush_version', '2026-07-26-job-country', false);
}, 100);

// =========================================================
// Organization term meta: logo, organization type and base sectors.
// =========================================================
if (!defined('SJ_ORGANISATIE_TYPE_SYNC_VERSION')) {
    define('SJ_ORGANISATIE_TYPE_SYNC_VERSION', '2026-06-24-company-organisation-types');
}

if (!defined('SJ_COMPANY_SECTOR_SYNC_VERSION')) {
    define('SJ_COMPANY_SECTOR_SYNC_VERSION', '2026-06-24-company-sectors');
}

function sj_get_job_company_logo_id($company_term_id) {
    return absint(get_term_meta((int) $company_term_id, '_sj_company_logo_id', true));
}

function sj_get_job_listing_company_logo_id($job_id) {
    $company_terms = get_the_terms((int) $job_id, 'job_company');
    if (is_wp_error($company_terms) || empty($company_terms)) {
        return 0;
    }

    foreach ($company_terms as $company_term) {
        $logo_id = sj_get_job_company_logo_id($company_term->term_id);
        if ($logo_id) {
            return $logo_id;
        }
    }

    return 0;
}

function sj_get_all_organisatie_type_terms() {
    $terms = get_terms([
        'taxonomy'   => 'organisatie_type',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);

    return is_wp_error($terms) ? [] : $terms;
}

function sj_normalize_organisatie_type_ids($ids) {
    $ids = array_values(array_unique(array_filter(array_map('absint', (array) $ids))));
    if (empty($ids)) {
        return [];
    }

    $valid_ids = get_terms([
        'taxonomy'   => 'organisatie_type',
        'hide_empty' => false,
        'include'    => $ids,
        'fields'     => 'ids',
    ]);

    return is_wp_error($valid_ids)
        ? []
        : array_values(array_intersect($ids, array_map('absint', $valid_ids)));
}

function sj_get_job_company_organisatie_type_ids($company_term_id) {
    $ids = get_term_meta((int) $company_term_id, '_sj_organisatie_type_ids', true);
    return sj_normalize_organisatie_type_ids(is_array($ids) ? $ids : []);
}

function sj_get_organisatie_type_terms_by_ids($ids) {
    $ids = sj_normalize_organisatie_type_ids($ids);
    if (empty($ids)) {
        return [];
    }

    $terms = get_terms([
        'taxonomy'   => 'organisatie_type',
        'hide_empty' => false,
        'include'    => $ids,
        'orderby'    => 'include',
    ]);

    return is_wp_error($terms) ? [] : $terms;
}

function sj_get_job_listing_organisatie_type_ids_from_companies($job_id, &$has_company_config = null) {
    $has_company_config = false;
    $company_terms = get_the_terms((int) $job_id, 'job_company');
    if (is_wp_error($company_terms) || empty($company_terms)) {
        return [];
    }

    $type_ids = [];
    foreach ($company_terms as $company_term) {
        if (metadata_exists('term', $company_term->term_id, '_sj_organisatie_type_ids')) {
            $has_company_config = true;
        }

        $type_ids = array_merge(
            $type_ids,
            sj_get_job_company_organisatie_type_ids($company_term->term_id)
        );
    }

    return sj_normalize_organisatie_type_ids($type_ids);
}

function sj_get_job_listing_organisatie_type_terms($job_id) {
    $type_ids = sj_get_job_listing_organisatie_type_ids_from_companies($job_id);

    if (!empty($type_ids)) {
        return sj_get_organisatie_type_terms_by_ids($type_ids);
    }

    $legacy_terms = get_the_terms((int) $job_id, 'organisatie_type');
    return is_wp_error($legacy_terms) || empty($legacy_terms) ? [] : $legacy_terms;
}

function sj_get_all_job_sector_terms() {
    $terms = get_terms([
        'taxonomy'   => 'job_sector',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);

    return is_wp_error($terms) ? [] : $terms;
}

function sj_normalize_job_sector_ids($ids) {
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

function sj_get_job_company_sector_ids($company_term_id) {
    $ids = get_term_meta((int) $company_term_id, '_sj_job_sector_ids', true);
    return sj_normalize_job_sector_ids(is_array($ids) ? $ids : []);
}

function sj_get_job_sector_terms_by_ids($ids) {
    $ids = sj_normalize_job_sector_ids($ids);
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

function sj_get_job_listing_sector_ids_from_companies($job_id, &$has_company_config = null) {
    $has_company_config = false;
    $company_terms = get_the_terms((int) $job_id, 'job_company');
    if (is_wp_error($company_terms) || empty($company_terms)) {
        return [];
    }

    $sector_ids = [];
    foreach ($company_terms as $company_term) {
        if (metadata_exists('term', $company_term->term_id, '_sj_job_sector_ids')) {
            $has_company_config = true;
        }

        $sector_ids = array_merge(
            $sector_ids,
            sj_get_job_company_sector_ids($company_term->term_id)
        );
    }

    return sj_normalize_job_sector_ids($sector_ids);
}

function sj_get_job_listing_sector_terms($job_id) {
    $current_terms = get_the_terms((int) $job_id, 'job_sector');
    $current_ids = is_wp_error($current_terms) || empty($current_terms)
        ? []
        : wp_list_pluck($current_terms, 'term_id');

    $inherited_ids = sj_get_job_listing_sector_ids_from_companies($job_id);
    $sector_ids = sj_normalize_job_sector_ids(array_merge($current_ids, $inherited_ids));

    return sj_get_job_sector_terms_by_ids($sector_ids);
}

function sj_render_company_logo_field($logo_id = 0) {
    $logo_id = absint($logo_id);
    $image = $logo_id ? wp_get_attachment_image($logo_id, 'thumbnail', false, ['class' => 'sj-company-logo-preview__image']) : '';

    wp_nonce_field('sj_save_job_company_logo', 'sj_company_logo_nonce');
    ?>
    <div class="sj-company-logo-field">
        <input type="hidden" name="sj_company_logo_id" class="sj-company-logo-id" value="<?php echo esc_attr($logo_id); ?>">
        <div class="sj-company-logo-preview" style="margin:0 0 8px;">
            <?php echo $image ?: '<span class="description">No logo selected yet.</span>'; ?>
        </div>
        <button type="button" class="button sj-company-logo-select">Choose logo</button>
        <button type="button" class="button sj-company-logo-remove" <?php disabled(!$logo_id); ?>>Remove logo</button>
    </div>
    <?php
}

function sj_render_organisatie_type_checkboxes($selected_ids = []) {
    $selected_ids = sj_normalize_organisatie_type_ids($selected_ids);
    $terms = sj_get_all_organisatie_type_terms();

    wp_nonce_field('sj_save_job_company_organisatie_types', 'sj_organisatie_type_nonce');

    if (empty($terms)) {
        echo '<p class="description">Create organization types first. Then you can link them to organizations here.</p>';
        return;
    }

    echo '<fieldset>';
    foreach ($terms as $term) {
        echo '<label style="display:block;margin:0 0 6px;">';
        echo '<input type="checkbox" name="sj_organisatie_type_ids[]" value="' . esc_attr($term->term_id) . '" ' . checked(in_array((int) $term->term_id, $selected_ids, true), true, false) . '> ';
        echo esc_html($term->name);
        echo '</label>';
    }
    echo '</fieldset>';
}

function sj_render_job_sector_checkboxes($selected_ids = []) {
    $selected_ids = sj_normalize_job_sector_ids($selected_ids);
    $terms = sj_get_all_job_sector_terms();

    wp_nonce_field('sj_save_job_company_sectors', 'sj_job_sector_nonce');

    if (empty($terms)) {
        echo '<p class="description">Create sectors first. Then you can link them to organizations here.</p>';
        return;
    }

    echo '<fieldset>';
    foreach ($terms as $term) {
        echo '<label style="display:block;margin:0 0 6px;">';
        echo '<input type="checkbox" name="sj_job_sector_ids[]" value="' . esc_attr($term->term_id) . '" ' . checked(in_array((int) $term->term_id, $selected_ids, true), true, false) . '> ';
        echo esc_html($term->name);
        echo '</label>';
    }
    echo '</fieldset>';
}

function sj_render_job_company_logo_add_field() {
    ?>
    <div class="form-field term-sj-company-logo-wrap">
        <label>Organization logo</label>
        <?php sj_render_company_logo_field(); ?>
        <p class="description">This logo is used automatically for jobs from this organization when the job itself has no logo.</p>
    </div>
    <?php
}
add_action('job_company_add_form_fields', 'sj_render_job_company_logo_add_field');

function sj_render_job_company_logo_edit_field($term) {
    ?>
    <tr class="form-field term-sj-company-logo-wrap">
        <th scope="row">
            <label>Organization logo</label>
        </th>
        <td>
            <?php sj_render_company_logo_field(sj_get_job_company_logo_id($term->term_id)); ?>
            <p class="description">This logo is used automatically for jobs from this organization when the job itself has no logo.</p>
        </td>
    </tr>
    <?php
}
add_action('job_company_edit_form_fields', 'sj_render_job_company_logo_edit_field');

function sj_render_job_company_organisatie_types_add_field() {
    ?>
    <div class="form-field term-sj-organisation-types-wrap">
        <label>Organization type</label>
        <?php sj_render_organisatie_type_checkboxes(); ?>
        <p class="description">Choose one or more types. These are applied automatically to all jobs from this organization.</p>
    </div>
    <?php
}
add_action('job_company_add_form_fields', 'sj_render_job_company_organisatie_types_add_field');

function sj_render_job_company_organisatie_types_edit_field($term) {
    ?>
    <tr class="form-field term-sj-organisation-types-wrap">
        <th scope="row">
            <label>Organization type</label>
        </th>
        <td>
            <?php sj_render_organisatie_type_checkboxes(sj_get_job_company_organisatie_type_ids($term->term_id)); ?>
            <p class="description">Choose one or more types. These are shown and filtered automatically on all jobs from this organization.</p>
        </td>
    </tr>
    <?php
}
add_action('job_company_edit_form_fields', 'sj_render_job_company_organisatie_types_edit_field');

function sj_render_job_company_sectors_add_field() {
    ?>
    <div class="form-field term-sj-job-sectors-wrap">
        <label>Base sectors</label>
        <?php sj_render_job_sector_checkboxes(); ?>
        <p class="description">These sectors are applied automatically to all jobs from this organization. You can still select extra sectors per job.</p>
    </div>
    <?php
}
add_action('job_company_add_form_fields', 'sj_render_job_company_sectors_add_field');

function sj_render_job_company_sectors_edit_field($term) {
    ?>
    <tr class="form-field term-sj-job-sectors-wrap">
        <th scope="row">
            <label>Base sectors</label>
        </th>
        <td>
            <?php sj_render_job_sector_checkboxes(sj_get_job_company_sector_ids($term->term_id)); ?>
            <p class="description">These sectors are shown and filtered automatically on all jobs from this organization. You can still select extra sectors per job, for example IT.</p>
        </td>
    </tr>
    <?php
}
add_action('job_company_edit_form_fields', 'sj_render_job_company_sectors_edit_field');

add_filter('manage_edit-job_company_columns', function ($columns) {
    $new_columns = [];

    foreach ($columns as $key => $label) {
        $new_columns[$key] = $label;

        if ($key === 'name') {
            $new_columns['sj_company_logo'] = 'Logo';
            $new_columns['sj_organisatie_types'] = 'Organization type';
            $new_columns['sj_job_sectors'] = 'Sectors';
        }
    }

    return $new_columns;
});

add_filter('manage_job_company_custom_column', function ($content, $column_name, $term_id) {
    if ($column_name === 'sj_company_logo') {
        $logo_id = sj_get_job_company_logo_id((int) $term_id);
        return $logo_id
            ? wp_get_attachment_image($logo_id, 'thumbnail', false, ['style' => 'width:40px;height:40px;object-fit:contain;'])
            : '&mdash;';
    }

    if ($column_name === 'sj_organisatie_types') {
        $terms = sj_get_organisatie_type_terms_by_ids(
            sj_get_job_company_organisatie_type_ids((int) $term_id)
        );

        return empty($terms)
            ? '&mdash;'
            : esc_html(implode(', ', wp_list_pluck($terms, 'name')));
    }

    if ($column_name === 'sj_job_sectors') {
        $terms = sj_get_job_sector_terms_by_ids(
            sj_get_job_company_sector_ids((int) $term_id)
        );

        return empty($terms)
            ? '&mdash;'
            : esc_html(implode(', ', wp_list_pluck($terms, 'name')));
    }

    return $content;
}, 10, 3);

function sj_sync_organisatie_types_for_job($job_id, $force = false) {
    $job_id = (int) $job_id;
    if (get_post_type($job_id) !== 'job_listing') {
        return;
    }

    $has_company_config = false;
    $type_ids = sj_get_job_listing_organisatie_type_ids_from_companies($job_id, $has_company_config);

    if (!$force && !$has_company_config) {
        return;
    }

    wp_set_object_terms($job_id, $type_ids, 'organisatie_type', false);
}

function sj_sync_sectors_for_job($job_id, $force = false) {
    $job_id = (int) $job_id;
    if (get_post_type($job_id) !== 'job_listing') {
        return;
    }

    $previous_inherited_ids = sj_normalize_job_sector_ids(
        get_post_meta($job_id, '_sj_inherited_job_sector_ids', true)
    );

    $has_company_config = false;
    $inherited_ids = sj_get_job_listing_sector_ids_from_companies($job_id, $has_company_config);

    if (!$force && !$has_company_config && empty($previous_inherited_ids)) {
        return;
    }

    $current_ids = wp_get_object_terms($job_id, 'job_sector', ['fields' => 'ids']);
    $current_ids = is_wp_error($current_ids) ? [] : array_map('absint', $current_ids);
    $direct_ids = array_values(array_diff($current_ids, $previous_inherited_ids));
    $merged_ids = sj_normalize_job_sector_ids(array_merge($direct_ids, $inherited_ids));

    wp_set_object_terms($job_id, $merged_ids, 'job_sector', false);

    if (!empty($inherited_ids)) {
        update_post_meta($job_id, '_sj_inherited_job_sector_ids', $inherited_ids);
    } else {
        delete_post_meta($job_id, '_sj_inherited_job_sector_ids');
    }
}

function sj_get_job_ids_for_company($company_term_id) {
    return get_posts([
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
}

function sj_sync_organisatie_types_for_company($company_term_id) {
    foreach (sj_get_job_ids_for_company((int) $company_term_id) as $job_id) {
        sj_sync_organisatie_types_for_job((int) $job_id, true);
    }
}

function sj_sync_sectors_for_company($company_term_id) {
    foreach (sj_get_job_ids_for_company((int) $company_term_id) as $job_id) {
        sj_sync_sectors_for_job((int) $job_id, true);
    }
}

function sj_save_job_company_logo($term_id) {
    if (
        !isset($_POST['sj_company_logo_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sj_company_logo_nonce'])), 'sj_save_job_company_logo')
    ) {
        return;
    }

    if (!current_user_can('manage_categories')) {
        return;
    }

    $logo_id = isset($_POST['sj_company_logo_id'])
        ? absint(wp_unslash($_POST['sj_company_logo_id']))
        : 0;

    if ($logo_id) {
        update_term_meta((int) $term_id, '_sj_company_logo_id', $logo_id);
    } else {
        delete_term_meta((int) $term_id, '_sj_company_logo_id');
    }
}
add_action('created_job_company', 'sj_save_job_company_logo');
add_action('edited_job_company', 'sj_save_job_company_logo');

function sj_save_job_company_organisatie_types($term_id) {
    if (
        !isset($_POST['sj_organisatie_type_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sj_organisatie_type_nonce'])), 'sj_save_job_company_organisatie_types')
    ) {
        return;
    }

    if (!current_user_can('manage_categories')) {
        return;
    }

    $type_ids = isset($_POST['sj_organisatie_type_ids'])
        ? sj_normalize_organisatie_type_ids(wp_unslash($_POST['sj_organisatie_type_ids']))
        : [];

    if (!empty($type_ids)) {
        update_term_meta((int) $term_id, '_sj_organisatie_type_ids', $type_ids);
    } else {
        delete_term_meta((int) $term_id, '_sj_organisatie_type_ids');
    }

    sj_sync_organisatie_types_for_company((int) $term_id);
}
add_action('created_job_company', 'sj_save_job_company_organisatie_types');
add_action('edited_job_company', 'sj_save_job_company_organisatie_types');

function sj_save_job_company_sectors($term_id) {
    if (
        !isset($_POST['sj_job_sector_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sj_job_sector_nonce'])), 'sj_save_job_company_sectors')
    ) {
        return;
    }

    if (!current_user_can('manage_categories')) {
        return;
    }

    $sector_ids = isset($_POST['sj_job_sector_ids'])
        ? sj_normalize_job_sector_ids(wp_unslash($_POST['sj_job_sector_ids']))
        : [];

    if (!empty($sector_ids)) {
        update_term_meta((int) $term_id, '_sj_job_sector_ids', $sector_ids);
    } else {
        delete_term_meta((int) $term_id, '_sj_job_sector_ids');
    }

    sj_sync_sectors_for_company((int) $term_id);
}
add_action('created_job_company', 'sj_save_job_company_sectors');
add_action('edited_job_company', 'sj_save_job_company_sectors');

add_action('save_post_job_listing', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    sj_sync_organisatie_types_for_job((int) $post_id);
    sj_sync_sectors_for_job((int) $post_id);
}, 100);

add_action('set_object_terms', function ($object_id, $terms, $tt_ids, $taxonomy) {
    if ($taxonomy !== 'job_company' || get_post_type((int) $object_id) !== 'job_listing') {
        return;
    }

    sj_sync_organisatie_types_for_job((int) $object_id);
    sj_sync_sectors_for_job((int) $object_id);
}, 10, 4);

function sj_backfill_job_company_organisatie_type_meta() {
    if (get_option('sj_organisatie_type_sync_version') === SJ_ORGANISATIE_TYPE_SYNC_VERSION) {
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
        $type_terms = get_the_terms((int) $job_id, 'organisatie_type');

        if (is_wp_error($company_terms) || empty($company_terms) || is_wp_error($type_terms) || empty($type_terms)) {
            continue;
        }

        $type_ids = wp_list_pluck($type_terms, 'term_id');
        foreach ($company_terms as $company_term) {
            $existing_ids = sj_get_job_company_organisatie_type_ids($company_term->term_id);
            $merged_ids = sj_normalize_organisatie_type_ids(array_merge($existing_ids, $type_ids));

            if (!empty($merged_ids)) {
                update_term_meta((int) $company_term->term_id, '_sj_organisatie_type_ids', $merged_ids);
            }
        }
    }

    foreach ($job_ids as $job_id) {
        sj_sync_organisatie_types_for_job((int) $job_id);
    }

    update_option('sj_organisatie_type_sync_version', SJ_ORGANISATIE_TYPE_SYNC_VERSION, false);
}
add_action('admin_init', 'sj_backfill_job_company_organisatie_type_meta');

function sj_backfill_job_company_sector_terms() {
    if (get_option('sj_company_sector_sync_version') === SJ_COMPANY_SECTOR_SYNC_VERSION) {
        return;
    }

    $job_ids = get_posts([
        'post_type'      => 'job_listing',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ]);

    foreach ($job_ids as $job_id) {
        sj_sync_sectors_for_job((int) $job_id);
    }

    update_option('sj_company_sector_sync_version', SJ_COMPANY_SECTOR_SYNC_VERSION, false);
}
add_action('admin_init', 'sj_backfill_job_company_sector_terms');

add_action('admin_enqueue_scripts', function () {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->taxonomy !== 'job_company') {
        return;
    }

    wp_enqueue_media();
    wp_add_inline_script('media-editor', "
        document.addEventListener('click', function(event) {
            const selectButton = event.target.closest('.sj-company-logo-select');
            const removeButton = event.target.closest('.sj-company-logo-remove');

            if (selectButton) {
                event.preventDefault();
                const field = selectButton.closest('.sj-company-logo-field');
                const input = field.querySelector('.sj-company-logo-id');
                const preview = field.querySelector('.sj-company-logo-preview');
                const remove = field.querySelector('.sj-company-logo-remove');
                const frame = wp.media({
                    title: 'Choose organization logo',
                    button: { text: 'Use logo' },
                    library: { type: 'image' },
                    multiple: false
                });

                frame.on('select', function() {
                    const attachment = frame.state().get('selection').first().toJSON();
                    const imageUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                    input.value = attachment.id;
                    preview.innerHTML = '<img src=\"' + imageUrl + '\" class=\"sj-company-logo-preview__image\" style=\"max-width:80px;height:auto;object-fit:contain;\" alt=\"\">';
                    remove.disabled = false;
                });

                frame.open();
                return;
            }

            if (removeButton) {
                event.preventDefault();
                const field = removeButton.closest('.sj-company-logo-field');
                field.querySelector('.sj-company-logo-id').value = '';
                field.querySelector('.sj-company-logo-preview').innerHTML = '<span class=\"description\">No logo selected yet.</span>';
                removeButton.disabled = true;
            }
        });
    ");

    wp_add_inline_style('common', '
        .sj-company-logo-preview__image {
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
        'job_country'       => 'job_country',
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
        'filter_job_country'   => 'job_country',
        'filter_job_company'   => 'job_company',
        'filter_job_type'      => 'job_listing_type',
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


/**
 * Location radius search.
 * A city search also matches jobs nearby while keeping WPJM's exact text match
 * as a fallback. Geocoding currently uses PDOK, matching the NL implementation.
 */
add_filter('job_manager_geolocation_enabled', '__return_false');

if (!function_exists('sj_pdok_geocode')) {
    function sj_pdok_geocode($query_text, $type_filter = null) {
        $query_text = trim((string) $query_text);
        if ($query_text === '') {
            return false;
        }

        $url = add_query_arg([
            'q'    => rawurlencode($query_text),
            'rows' => 1,
        ], 'https://api.pdok.nl/bzk/locatieserver/search/v3_1/free');

        if ($type_filter) {
            $url = add_query_arg('fq', 'type:' . rawurlencode($type_filter), $url);
        }

        $response = wp_remote_get($url, ['timeout' => 5]);
        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $doc  = $body['response']['docs'][0] ?? null;
        if (!$doc || empty($doc['centroide_ll']) || !preg_match('/POINT\(([\-0-9.]+) ([\-0-9.]+)\)/', $doc['centroide_ll'], $m)) {
            return false;
        }

        return [
            'lat'               => (float) $m[2],
            'lng'               => (float) $m[1],
            'formatted_address' => $doc['weergavenaam'] ?? '',
            'city'              => $doc['woonplaatsnaam'] ?? '',
            'state_long'        => $doc['provincienaam'] ?? '',
            'postcode'          => $doc['postcode'] ?? '',
        ];
    }
}

if (!function_exists('sj_geocode_job_search_location')) {
    function sj_geocode_job_search_location($location) {
        $location = trim((string) $location);
        if ($location === '') {
            return false;
        }

        $cache_key = 'sj_geo_center_' . md5(strtolower($location));
        $cached    = get_transient($cache_key);
        if (is_array($cached)) {
            return empty($cached['failed']) ? $cached : false;
        }

        // Try city search first, then fall back to free search.
        $data = sj_pdok_geocode($location, 'woonplaats') ?: sj_pdok_geocode($location);
        if (!$data) {
            set_transient($cache_key, ['failed' => true], HOUR_IN_SECONDS);
            return false;
        }

        $center = [
            'lat' => $data['lat'],
            'lng' => $data['lng'],
        ];

        set_transient($cache_key, $center, WEEK_IN_SECONDS);
        return $center;
    }
}

/**
 * Geocode a job address through PDOK and store geolocation_* meta.
 */
if (!function_exists('sj_pdok_geolocate_job')) {
    function sj_pdok_geolocate_job($job_id, $raw_address) {
        $raw_address = trim((string) $raw_address);
        if ($raw_address === '') {
            return false;
        }

        $data = sj_pdok_geocode($raw_address);
        if (!$data) {
            return false;
        }

        update_post_meta($job_id, 'geolocation_lat', $data['lat']);
        update_post_meta($job_id, 'geolocation_long', $data['lng']);

        foreach (['formatted_address', 'city', 'state_long', 'postcode'] as $field) {
            if (!empty($data[$field])) {
                update_post_meta($job_id, 'geolocation_' . $field, $data[$field]);
            }
        }

        update_post_meta($job_id, 'geolocated', 1);
        return true;
    }
}

add_action('job_manager_update_job_data', function ($job_id, $values) {
    if (!empty($values['job']['job_location'])) {
        sj_pdok_geolocate_job($job_id, $values['job']['job_location']);
    }
}, 20, 2);

add_action('job_manager_job_location_edited', function ($job_id, $new_location) {
    sj_pdok_geolocate_job($job_id, $new_location);
}, 20, 2);

/**
 * One-time backfill for existing jobs without geodata.
 */
if (!function_exists('sj_pdok_jobs_missing_geo_query')) {
    function sj_pdok_jobs_missing_geo_query($posts_per_page) {
        return new WP_Query([
            'post_type'      => 'job_listing',
            'post_status'    => 'any',
            'posts_per_page' => $posts_per_page,
            'fields'         => 'ids',
            'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                [
                    'key'     => 'geolocated',
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ]);
    }
}

add_action('admin_menu', function () {
    add_submenu_page(
        'edit.php?post_type=job_listing',
        'Geocoding backfill (PDOK)',
        'Geocoding backfill',
        'manage_options',
        'sj-pdok-geocode-backfill',
        function () {
            if (!current_user_can('manage_options')) {
                wp_die('Access denied.');
            }

            $result = null;
            if (isset($_POST['sj_pdok_backfill_nonce']) && wp_verify_nonce($_POST['sj_pdok_backfill_nonce'], 'sj_pdok_backfill')) {
                $geocoded = 0;
                $failed   = 0;

                foreach (sj_pdok_jobs_missing_geo_query(100)->posts as $job_id) {
                    $location = get_post_meta($job_id, '_job_location', true);
                    if (sj_pdok_geolocate_job($job_id, $location)) {
                        $geocoded++;
                    } else {
                        $failed++;
                    }
                    usleep(200000);
                }

                $result = ['geocoded' => $geocoded, 'failed' => $failed];
            }

            $missing_count = sj_pdok_jobs_missing_geo_query(-1)->found_posts;
            ?>
            <div class="wrap">
                <h1>Geocode job locations (PDOK)</h1>
                <p>Jobs without location data: <strong><?php echo esc_html($missing_count); ?></strong>.</p>
                <?php if (null !== $result) : ?>
                    <div class="notice notice-success">
                        <p>
                            <?php echo esc_html($result['geocoded']); ?> job(s) geocoded,
                            <?php echo esc_html($result['failed']); ?> failed or skipped because no usable address was found.
                        </p>
                    </div>
                <?php endif; ?>
                <form method="post">
                    <?php wp_nonce_field('sj_pdok_backfill', 'sj_pdok_backfill_nonce'); ?>
                    <?php submit_button('Start backfill (max. 100 per run)'); ?>
                </form>
                <p class="description">Are there more than 100 jobs without location data? Click the button again to process the next batch.</p>
            </div>
            <?php
        }
    );
});

add_filter('get_job_listings_query_args', function ($query_args, $args) {
    $location = isset($args['search_location']) ? trim((string) $args['search_location']) : '';
    if ($location === '') {
        return $query_args;
    }

    // Remove WPJM's exact location match and add it back below with the radius match.
    if (!empty($query_args['meta_query'][0]['relation'])) {
        array_shift($query_args['meta_query']);
    }

    $query_args['sj_geo_search_location'] = $location;

    $center = sj_geocode_job_search_location($location);
    if ($center) {
        $query_args['sj_geo_lat']    = $center['lat'];
        $query_args['sj_geo_lng']    = $center['lng'];
        $query_args['sj_geo_radius'] = apply_filters('sj_job_location_radius_km', 50);
    }

    return $query_args;
}, 20, 2);

add_filter('posts_clauses', function ($clauses, $query) {
    $location = $query->get('sj_geo_search_location');
    if (empty($location) || 'job_listing' !== $query->get('post_type')) {
        return $clauses;
    }

    global $wpdb;

    $text_conditions = [];
    foreach (['_job_location', 'geolocation_formatted_address', 'geolocation_state_long'] as $i => $meta_key) {
        $alias              = "sj_loc_{$i}";
        $clauses['join']   .= " LEFT JOIN {$wpdb->postmeta} {$alias} ON {$alias}.post_id = {$wpdb->posts}.ID AND {$alias}.meta_key = '{$meta_key}'";
        $text_conditions[]  = $wpdb->prepare("{$alias}.meta_value LIKE %s", '%' . $wpdb->esc_like($location) . '%');
    }

    $match_conditions = ['(' . implode(' OR ', $text_conditions) . ')'];

    $lat = $query->get('sj_geo_lat');
    $lng = $query->get('sj_geo_lng');
    if ('' !== $lat && '' !== $lng) {
        $radius = (float) $query->get('sj_geo_radius');

        $clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} sj_geo_lat ON sj_geo_lat.post_id = {$wpdb->posts}.ID AND sj_geo_lat.meta_key = 'geolocation_lat'";
        $clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} sj_geo_lng ON sj_geo_lng.post_id = {$wpdb->posts}.ID AND sj_geo_lng.meta_key = 'geolocation_long'";

        $distance_sql = $wpdb->prepare(
            '(6371 * acos( LEAST(1, cos(radians(%f)) * cos(radians(sj_geo_lat.meta_value)) * cos(radians(sj_geo_lng.meta_value) - radians(%f)) + sin(radians(%f)) * sin(radians(sj_geo_lat.meta_value)) ) ))',
            $lat,
            $lng,
            $lat
        );

        $match_conditions[] = $wpdb->prepare(
            "(sj_geo_lat.meta_value IS NOT NULL AND sj_geo_lng.meta_value IS NOT NULL AND {$distance_sql} <= %f)",
            $radius
        );

        $clauses['orderby'] = "{$distance_sql} ASC" . ($clauses['orderby'] ? ", {$clauses['orderby']}" : '');
    }

    $clauses['where'] .= ' AND (' . implode(' OR ', $match_conditions) . ')';

    return $clauses;
}, 10, 2);

// Add custom default attributes to the jobs shortcode
add_filter('job_manager_output_jobs_defaults', function($defaults) {
    $defaults['job_company'] = '';
    $defaults['job_tag'] = '';
    $defaults['job_sector'] = '';
    $defaults['job_country'] = '';
    $defaults['organisatie_type'] = '';
    $defaults['job_listing_type'] = '';
    
    return $defaults;
});

/**
 * Seed default terms for organization types (runs once).
 */
add_action('init', function () {
    if (get_option('sj_organisatie_type_seeded')) return;
    $terms = ['Foundation', 'NGO', 'Municipality', 'Province', 'Government', 'Semi-government', 'SME', 'Fund', 'Non-profit'];
    foreach ($terms as $term) {
        if (!term_exists($term, 'organisatie_type')) {
            wp_insert_term($term, 'organisatie_type');
        }
    }
    update_option('sj_organisatie_type_seeded', true);
});

// Rename WP JM "Load more listings" button.
add_filter('gettext', function ($translated, $text, $domain) {
    if ($domain === 'wp-job-manager' && $text === 'Load more listings') {
        return 'Show more jobs';
    }
    return $translated;
}, 10, 3);

/**
 * Blog search: filter the main query when ?sj_s= is provided.
 */
add_action( 'pre_get_posts', function ( WP_Query $q ) {
    if ( ! $q->is_main_query() || ! is_home() ) {
        return;
    }
    $term = isset( $_GET['sj_s'] ) ? sanitize_text_field( wp_unslash( $_GET['sj_s'] ) ) : '';
    if ( $term !== '' ) {
        $q->set( 's', $term );
    }
} );

/**
 * Hide articles from listing pages without taking them offline.
 * Add the tag "hidden-overview" to exclude a post from blog listings.
 */
add_action( 'pre_get_posts', function ( WP_Query $q ) {
    if ( is_admin() || ! $q->is_main_query() ) {
        return;
    }

    if ( ! $q->is_home() && ! $q->is_category() && ! $q->is_tag() && ! $q->is_date() ) {
        return;
    }

    $tax_query   = (array) $q->get( 'tax_query' );
    $tax_query[] = [
        'taxonomy' => 'post_tag',
        'field'    => 'slug',
        'terms'    => [ 'hidden-overview' ],
        'operator' => 'NOT IN',
    ];
    $q->set( 'tax_query', $tax_query );
} );

/** Import functions */

require_once get_stylesheet_directory() . '/inc/bowers-import.php';
require_once get_stylesheet_directory() . '/inc/arcadis-import.php';
require_once get_stylesheet_directory() . '/inc/jackling-import.php';


require_once get_stylesheet_directory() . '/inc/bedrijfspagina-filters.php';
