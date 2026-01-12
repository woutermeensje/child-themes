<?php
// ✅ Bowers XML Job Feed Importer (met logging)
add_action('bowers_weekly_job_import', 'import_bowers_jobs');

function import_bowers_jobs() {
    $url = 'https://bowers.nl/xml-jobs-feed/';
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        error_log('[BOWERS IMPORT] wp_remote_get error: ' . $response->get_error_message());
        return;
    }

    $body = wp_remote_retrieve_body($response);
    if (empty($body)) {
        error_log('[BOWERS IMPORT] Lege response body.');
        return;
    }

    $xml = simplexml_load_string($body);
    if (!$xml) {
        error_log('[BOWERS IMPORT] XML kon niet worden geladen.');
        return;
    }

    foreach ($xml->job as $job) {
        $external_id = (string) $job['id'];

        if (empty($external_id)) {
            error_log('[BOWERS IMPORT] Geen job ID gevonden.');
            continue;
        }

        $existing = get_posts([
            'post_type' => 'job_listing',
            'meta_key' => '_bowers_job_id',
            'meta_value' => $external_id,
            'posts_per_page' => 1,
            'post_status' => ['draft', 'publish']
        ]);

        if ($existing) {
            error_log("[BOWERS IMPORT] Vacature $external_id bestaat al, overslaan.");
            continue;
        }

        $post_title = wp_strip_all_tags((string) $job->name);
        $post_content = wp_kses_post((string) $job->description ?: 'Geen beschrijving beschikbaar.');

        $post_id = wp_insert_post([
            'post_title'   => $post_title,
            'post_content' => $post_content,
            'post_type'    => 'job_listing',
            'post_status'  => 'draft',
        ]);

        if (is_wp_error($post_id)) {
            error_log('[BOWERS IMPORT] Fout bij aanmaken vacature: ' . $post_id->get_error_message());
            continue;
        }

        error_log("[BOWERS IMPORT] Vacature toegevoegd: ID $post_id, Titel: $post_title");

        // Meta en taxonomieën
        update_post_meta($post_id, '_bowers_job_id', $external_id);
        update_post_meta($post_id, '_job_location', wp_strip_all_tags((string) $job->region));
        update_post_meta($post_id, '_application', esc_url_raw((string) $job->link));
        update_post_meta($post_id, '_company_name', 'Bowers');

        wp_set_object_terms($post_id, 'Bowers', 'job_company');
        wp_set_object_terms($post_id, 'uitgelichte werkgever', 'job_tag');
        wp_set_object_terms($post_id, ['techniek', 'bouw', 'energietransitie', 'inner smile certified', 'recruitment', 'detachering', 'bemiddeling'], 'job_sector');


        $jobtype = strtolower(trim((string) $job->jobtype));
        if (in_array($jobtype, ['fulltime', 'parttime', 'freelance'])) {
            wp_set_object_terms($post_id, $jobtype, 'job_type');
        }
    }
}

// ✅ Cronjob plannen
if (!wp_next_scheduled('bowers_weekly_job_import')) {
    wp_schedule_event(time(), 'weekly', 'bowers_weekly_job_import');
}


if (isset($_GET['run_bowers_import']) && current_user_can('manage_options')) {
    error_log('🔁 Start Bowers import...');
    import_bowers_jobs();
    error_log('✅ Bowers import klaar!');
    exit('✅ Import uitgevoerd. Check debug.log voor details.');
}


