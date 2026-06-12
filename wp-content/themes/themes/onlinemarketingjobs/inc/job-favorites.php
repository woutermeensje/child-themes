<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('sj_get_favorites_heart_svg')) {
    function sj_get_favorites_heart_svg(): string {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z"/></svg>';
    }
}

if (!function_exists('sj_get_job_favorite_data')) {
    function sj_get_job_favorite_data($post_id = null): array {
        $post_id = $post_id ?: get_the_ID();
        $post_id = (int) $post_id;
        $post    = $post_id ? get_post($post_id) : null;

        if (!$post || $post->post_type !== 'job_listing') {
            return [];
        }

        $company = '';
        $company_terms = get_the_terms($post_id, 'job_company');
        if (!is_wp_error($company_terms) && !empty($company_terms)) {
            $company = $company_terms[0]->name;
        }

        if (!$company && function_exists('get_the_company_name')) {
            $company = get_the_company_name($post_id);
        }

        if (!$company) {
            $company = get_post_meta($post_id, '_company_name', true);
        }

        $location = function_exists('get_the_job_location')
            ? get_the_job_location($post_id)
            : get_post_meta($post_id, '_job_location', true);

        return [
            'id'       => $post_id,
            'title'    => wp_specialchars_decode(get_the_title($post_id), ENT_QUOTES),
            'url'      => get_permalink($post_id),
            'company'  => wp_specialchars_decode((string) $company, ENT_QUOTES),
            'location' => wp_specialchars_decode((string) $location, ENT_QUOTES),
            'excerpt'  => wp_specialchars_decode(wp_trim_words(wp_strip_all_tags(get_the_excerpt($post_id)), 24, '...'), ENT_QUOTES),
            'date'     => get_the_date('d F Y', $post_id),
        ];
    }
}

if (!function_exists('sj_get_job_favorite_button')) {
    function sj_get_job_favorite_button($post_id = null, array $args = []): string {
        $args = wp_parse_args($args, [
            'context'    => 'card',
            'class'      => '',
            'show_label' => false,
            'label'      => 'Bewaar vacature',
        ]);

        $data = sj_get_job_favorite_data($post_id);
        if (empty($data)) {
            return '';
        }

        $context = sanitize_html_class($args['context']);
        $classes = trim('sj-favorite-button sj-favorite-button--' . $context . ' ' . $args['class']);
        $label   = $args['label'] ?: 'Bewaar vacature';

        $label_html = $args['show_label']
            ? '<span class="sj-favorite-button__label">' . esc_html($label) . '</span>'
            : '<span class="screen-reader-text">' . esc_html($label) . '</span>';

        return sprintf(
            '<button type="button" class="%1$s" aria-label="%2$s" aria-pressed="false" title="%2$s" data-sj-favorite-button data-job-id="%3$d" data-job-title="%4$s" data-job-url="%5$s" data-job-company="%6$s" data-job-location="%7$s" data-job-excerpt="%8$s" data-job-date="%9$s"><span class="sj-favorite-button__icon">%10$s</span>%11$s</button>',
            esc_attr($classes),
            esc_attr($label),
            (int) $data['id'],
            esc_attr($data['title']),
            esc_url($data['url']),
            esc_attr($data['company']),
            esc_attr($data['location']),
            esc_attr($data['excerpt']),
            esc_attr($data['date']),
            sj_get_favorites_heart_svg(),
            $label_html
        );
    }
}

if (!function_exists('sj_the_job_favorite_button')) {
    function sj_the_job_favorite_button($post_id = null, array $args = []): void {
        echo sj_get_job_favorite_button($post_id, $args);
    }
}

if (!function_exists('sj_get_job_favorites_nav_link')) {
    function sj_get_job_favorites_nav_link(string $class = ''): string {
        $classes = trim('sj-favorites-nav ' . $class);

        return sprintf(
            '<a href="%1$s" class="%2$s" data-sj-favorites-nav-link aria-label="Mijn vacatures"><span class="sj-favorites-nav__icon">%3$s</span><span class="sj-favorites-nav__label">Mijn vacatures</span><span class="sj-favorites-nav__count" data-sj-favorites-count hidden>0</span></a>',
            esc_url(home_url('/mijn-vacatures/')),
            esc_attr($classes),
            sj_get_favorites_heart_svg()
        );
    }
}

if (!function_exists('sj_the_job_favorites_nav_link')) {
    function sj_the_job_favorites_nav_link(string $class = ''): void {
        echo sj_get_job_favorites_nav_link($class);
    }
}

add_action('init', function () {
    add_rewrite_tag('%sj_mijn_vacatures%', '1');
    add_rewrite_rule('^mijn-vacatures/?$', 'index.php?sj_mijn_vacatures=1', 'top');

    if (get_option('sj_favorites_rewrite_flushed') !== '2026-05-16') {
        flush_rewrite_rules(false);
        update_option('sj_favorites_rewrite_flushed', '2026-05-16', false);
    }
}, 20);

add_filter('query_vars', function ($vars) {
    $vars[] = 'sj_mijn_vacatures';
    return $vars;
});

add_filter('template_include', function ($template) {
    if ((int) get_query_var('sj_mijn_vacatures') !== 1) {
        return $template;
    }

    $favorites_template = get_stylesheet_directory() . '/page-mijn-vacatures.php';
    return file_exists($favorites_template) ? $favorites_template : $template;
});

add_filter('document_title_parts', function ($title) {
    if ((int) get_query_var('sj_mijn_vacatures') === 1) {
        $title['title'] = 'Mijn vacatures';
    }

    return $title;
});

add_filter('body_class', function ($classes) {
    if ((int) get_query_var('sj_mijn_vacatures') === 1) {
        $classes[] = 'sj-mijn-vacatures-route';
    }

    return $classes;
});

add_action('wp_ajax_sj_get_favorite_jobs', 'sj_get_favorite_jobs_ajax');
add_action('wp_ajax_nopriv_sj_get_favorite_jobs', 'sj_get_favorite_jobs_ajax');

if (!function_exists('sj_get_favorite_jobs_ajax')) {
    function sj_get_favorite_jobs_ajax(): void {
        $ids_raw = isset($_GET['ids']) ? sanitize_text_field(wp_unslash($_GET['ids'])) : '';
        $ids     = array_filter(array_map('absint', explode(',', $ids_raw)));
        $ids     = array_values(array_unique(array_slice($ids, 0, 100)));

        if (empty($ids)) {
            wp_send_json_success(['jobs' => []]);
        }

        $jobs = get_posts([
            'post_type'      => 'job_listing',
            'post_status'    => 'publish',
            'post__in'       => $ids,
            'orderby'        => 'post__in',
            'posts_per_page' => count($ids),
        ]);

        $favorite_jobs = [];
        foreach ($jobs as $job) {
            $data = sj_get_job_favorite_data($job->ID);
            if (!empty($data)) {
                $favorite_jobs[] = $data;
            }
        }

        wp_send_json_success(['jobs' => $favorite_jobs]);
    }
}
