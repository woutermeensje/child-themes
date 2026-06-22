<?php
if (!defined('ABSPATH')) exit;

/**
 * REST-koppeling voor vacatures vanuit de Laravel werkgeversomgeving.
 */

add_action('rest_api_init', function () {
    register_rest_route('sustainablejobs/v1', '/vacancies', [
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'sj_laravel_upsert_vacancy',
        'permission_callback' => 'sj_laravel_can_manage_vacancies',
    ]);

    register_rest_route('sustainablejobs/v1', '/vacancies/(?P<id>\d+)', [
        'methods'             => WP_REST_Server::EDITABLE,
        'callback'            => 'sj_laravel_upsert_vacancy',
        'permission_callback' => 'sj_laravel_can_manage_vacancies',
        'args'                => [
            'id' => [
                'required'          => true,
                'validate_callback' => static fn ($value) => absint($value) > 0,
            ],
        ],
    ]);
});

function sj_laravel_can_manage_vacancies(): bool {
    return current_user_can('edit_posts');
}

function sj_laravel_upsert_vacancy(WP_REST_Request $request) {
    $payload    = (array) $request->get_json_params();
    $route_id   = absint($request->get_param('id'));
    $laravel_id = absint($payload['laravel_id'] ?? $payload['id'] ?? 0);

    if ($laravel_id <= 0 && $route_id <= 0) {
        return new WP_Error(
            'sj_missing_laravel_id',
            'laravel_id is verplicht voor nieuwe vacatures.',
            ['status' => 422]
        );
    }

    $post_id = sj_laravel_find_existing_job_id($route_id, $laravel_id);
    $is_new  = $post_id <= 0;

    $title = sanitize_text_field($payload['title'] ?? '');
    if ($title === '') {
        return new WP_Error('sj_missing_title', 'title is verplicht.', ['status' => 422]);
    }

    $allowed_statuses = ['draft', 'pending', 'publish'];
    $requested_status = sanitize_key($payload['status'] ?? '');
    $post_status      = in_array($requested_status, $allowed_statuses, true)
        ? $requested_status
        : ($is_new ? 'draft' : get_post_status($post_id));

    $post_data = [
        'post_title'   => $title,
        'post_content' => wp_kses_post((string) ($payload['content'] ?? '')),
        'post_status'  => $post_status ?: 'draft',
        'post_type'    => 'job_listing',
    ];

    if ($is_new) {
        $post_data['post_author'] = get_current_user_id() ?: 1;
        $post_id = wp_insert_post($post_data, true);
    } else {
        $post_data['ID'] = $post_id;
        $post_id = wp_update_post($post_data, true);
    }

    if (is_wp_error($post_id)) {
        return $post_id;
    }

    sj_laravel_update_vacancy_meta((int) $post_id, $payload, $laravel_id);
    sj_laravel_update_vacancy_terms((int) $post_id, $payload);

    return rest_ensure_response([
        'id'          => (int) $post_id,
        'status'      => get_post_status($post_id),
        'link'        => get_edit_post_link($post_id, 'raw') ?: get_permalink($post_id),
        'public_url'  => get_permalink($post_id),
        'laravel_id'  => $laravel_id,
        'created'     => $is_new,
    ]);
}

function sj_laravel_find_existing_job_id(int $route_id, int $laravel_id): int {
    if ($route_id > 0 && get_post_type($route_id) === 'job_listing') {
        return $route_id;
    }

    if ($laravel_id <= 0) {
        return 0;
    }

    $existing = get_posts([
        'post_type'      => 'job_listing',
        'post_status'    => ['draft', 'pending', 'publish', 'expired', 'private'],
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => '_laravel_vacature_id',
        'meta_value'     => (string) $laravel_id,
    ]);

    return !empty($existing) ? (int) $existing[0] : 0;
}

function sj_laravel_update_vacancy_meta(int $post_id, array $payload, int $laravel_id): void {
    $meta = is_array($payload['meta'] ?? null) ? $payload['meta'] : [];

    $company_name = sanitize_text_field($payload['company_name'] ?? ($meta['_company_name'] ?? ''));
    $location     = sanitize_text_field($payload['location'] ?? ($meta['_job_location'] ?? ''));
    $apply_url    = esc_url_raw($payload['apply_url'] ?? $payload['url'] ?? ($meta['_application'] ?? $meta['_company_website'] ?? ''));

    $base_meta = [
        '_laravel_vacature_id'      => $laravel_id > 0 ? (string) $laravel_id : '',
        '_laravel_last_synced_at'   => current_time('mysql'),
        '_company_name'             => $company_name,
        '_job_location'             => $location,
        '_application'              => $apply_url,
        '_company_website'          => $apply_url,
        '_job_contact_firstname'    => sanitize_text_field($payload['contact_first_name'] ?? ($meta['_job_contact_firstname'] ?? '')),
        '_job_contact_lastname'     => sanitize_text_field($payload['contact_last_name'] ?? ($meta['_job_contact_lastname'] ?? '')),
        '_job_contact_email'        => sanitize_email($payload['contact_email'] ?? ($meta['_job_contact_email'] ?? '')),
        '_job_contact_phone'        => sanitize_text_field($payload['contact_phone'] ?? ($meta['_job_contact_phone'] ?? '')),
        '_job_salary_range'         => sanitize_text_field($payload['salary_range'] ?? ($meta['_job_salary_range'] ?? '')),
        '_job_hours_per_week'       => sanitize_text_field($payload['hours_per_week'] ?? ($meta['_job_hours_per_week'] ?? '')),
        '_sj_pakket'                => sanitize_text_field($payload['pakket'] ?? ($meta['_sj_pakket'] ?? '')),
        '_filled'                   => '0',
        '_featured'                 => !empty($payload['featured']) ? '1' : '0',
    ];

    foreach ($base_meta as $key => $value) {
        if ($value === '') {
            delete_post_meta($post_id, $key);
        } else {
            update_post_meta($post_id, $key, $value);
        }
    }

    $logo_url = esc_url_raw($payload['company_logo_url'] ?? ($meta['_company_logo'] ?? ''));
    if ($logo_url !== '') {
        update_post_meta($post_id, '_company_logo', $logo_url);
    }
}

function sj_laravel_update_vacancy_terms(int $post_id, array $payload): void {
    $company_name = sanitize_text_field($payload['company_name'] ?? '');
    if ($company_name !== '') {
        wp_set_object_terms($post_id, $company_name, 'job_company', false);
    }

    $taxonomies = is_array($payload['taxonomies'] ?? null) ? $payload['taxonomies'] : [];
    $mapping = [
        'job_listing_type' => $payload['job_types'] ?? $payload['employment_type'] ?? ($taxonomies['job_listing_type'] ?? []),
        'job_sector'       => $payload['sectors'] ?? ($taxonomies['job_sector'] ?? []),
        'organisatie_type'  => $payload['organisation_types'] ?? $payload['organisatie_type'] ?? ($taxonomies['organisatie_type'] ?? []),
        'job_tag'          => $payload['tags'] ?? ($taxonomies['job_tag'] ?? []),
    ];

    foreach ($mapping as $taxonomy => $terms) {
        $terms = array_values(array_filter(array_map('sanitize_text_field', (array) $terms)));
        if (!empty($terms) && taxonomy_exists($taxonomy)) {
            wp_set_object_terms($post_id, $terms, $taxonomy, false);
        }
    }
}

add_action('transition_post_status', 'sj_laravel_notify_vacancy_published', 10, 3);

function sj_laravel_notify_vacancy_published(string $new_status, string $old_status, WP_Post $post): void {
    if ($new_status !== 'publish' || $old_status === 'publish' || $post->post_type !== 'job_listing') {
        return;
    }

    $laravel_id = absint(get_post_meta($post->ID, '_laravel_vacature_id', true));
    if ($laravel_id <= 0) {
        return;
    }

    if (get_post_meta($post->ID, '_laravel_published_notified_at', true)) {
        return;
    }

    $webhook_url = sj_laravel_webhook_url();
    $secret      = sj_laravel_webhook_secret();
    if ($webhook_url === '' || $secret === '') {
        update_post_meta($post->ID, '_laravel_last_webhook_error', 'Laravel webhook URL/secret ontbreekt.');
        return;
    }

    $body = wp_json_encode([
        'event'       => 'vacancy.published',
        'laravel_id'  => $laravel_id,
        'wp_job_id'   => (int) $post->ID,
        'wp_job_url'  => get_permalink($post),
        'title'       => get_the_title($post),
        'published_at'=> current_time('mysql'),
    ]);

    $signature = hash_hmac('sha256', (string) $body, $secret);
    $response = wp_remote_post($webhook_url, [
        'timeout' => 15,
        'headers' => [
            'Content-Type'   => 'application/json',
            'X-SJ-Event'     => 'vacancy.published',
            'X-SJ-Signature' => $signature,
        ],
        'body' => $body,
    ]);

    if (is_wp_error($response)) {
        update_post_meta($post->ID, '_laravel_last_webhook_error', $response->get_error_message());
        return;
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    if ($code >= 200 && $code < 300) {
        update_post_meta($post->ID, '_laravel_published_notified_at', current_time('mysql'));
        delete_post_meta($post->ID, '_laravel_last_webhook_error');
        return;
    }

    update_post_meta(
        $post->ID,
        '_laravel_last_webhook_error',
        'HTTP ' . $code . ' - ' . wp_remote_retrieve_body($response)
    );
}

function sj_laravel_webhook_url(): string {
    if (defined('SJ_LARAVEL_VACANCY_WEBHOOK_URL')) {
        return esc_url_raw((string) constant('SJ_LARAVEL_VACANCY_WEBHOOK_URL'));
    }

    return esc_url_raw((string) apply_filters('sj_laravel_vacancy_webhook_url', ''));
}

function sj_laravel_webhook_secret(): string {
    if (defined('SJ_LARAVEL_WEBHOOK_SECRET')) {
        return (string) constant('SJ_LARAVEL_WEBHOOK_SECRET');
    }

    return (string) apply_filters('sj_laravel_webhook_secret', '');
}
