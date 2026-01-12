<?php
// ✅ Jackling XML Job Feed Importer
add_action('jackling_weekly_job_import', 'import_jackling_jobs');

function import_jackling_jobs() {
    $url = 'https://jackling.nl/xml-jobs-feed/';
    $response = wp_remote_get($url);

    if (is_wp_error($response)) return;

    $xml = simplexml_load_string(wp_remote_retrieve_body($response));
    if (!$xml) return;

    foreach ($xml->job as $job) {
        $external_id = (string) $job->id;

        // Skip als vacature al bestaat
        $existing = get_posts([
            'post_type' => 'job_listing',
            'meta_key' => '_jackling_job_id',
            'meta_value' => $external_id,
            'posts_per_page' => 1,
            'post_status' => ['draft', 'publish']
        ]);
        if ($existing) continue;

        $post_id = wp_insert_post([
            'post_title'   => wp_strip_all_tags((string) $job->title),
            'post_content' => wp_kses_post((string) $job->description),
            'post_type'    => 'job_listing',
            'post_status'  => 'draft',
        ]);

        if ($post_id && !is_wp_error($post_id)) {
            update_post_meta($post_id, '_jackling_job_id', $external_id);
            update_post_meta($post_id, '_job_location', (string) $job->location);
            update_post_meta($post_id, '_application', (string) $job->link);
            update_post_meta($post_id, '_company_name', 'Jackling');

            // Voeg taxonomieën toe
            wp_set_object_terms($post_id, 'Jackling', 'job_company');
            wp_set_object_terms($post_id, 'uitgelichte werkgever', 'job_tag');
            wp_set_object_terms($post_id, ['techniek', 'bouw'], 'job_sector');

            $type = strtolower((string) $job->type);
            if (in_array($type, ['fulltime', 'parttime', 'freelance'])) {
                wp_set_object_terms($post_id, $type, 'job_type');
            }
        }
    }
}

// ✅ Cronjob activeren als die nog niet bestaat
if (!wp_next_scheduled('jackling_weekly_job_import')) {
    wp_schedule_event(time(), 'weekly', 'jackling_weekly_job_import');
}
