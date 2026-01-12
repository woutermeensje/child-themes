<?php
// Arcadis API import voor duurzame vacatures

add_action('arcadis_weekly_job_import', 'import_arcadis_jobs');
function import_arcadis_jobs() {
    $skills = ['Renewable Energy', 'Environmental Consulting', 'Sustainability'];
    $domain = 'arcadis.com';

    foreach ($skills as $skill) {
        $url = 'https://jobs.arcadis.com/api/apply/v2/jobs?domain=' . urlencode($domain) . '&location=netherlands&skill=' . urlencode($skill) . '&num=50';
        $response = wp_remote_get($url);

        if (is_wp_error($response)) continue;

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($data['positions'])) {
            foreach ($data['positions'] as $job) {
                $job_id = $job['id'];
                $existing = get_posts([
                    'post_type' => 'job_listing',
                    'meta_key' => '_arcadis_job_id',
                    'meta_value' => $job_id,
                    'posts_per_page' => 1,
                    'post_status' => ['draft', 'publish']
                ]);
                if ($existing) continue;

                $post_id = wp_insert_post([
                    'post_title' => wp_strip_all_tags($job['name']),
                    'post_type'  => 'job_listing',
                    'post_status' => 'draft',
                ]);

                update_post_meta($post_id, '_arcadis_job_id', $job_id);
                update_post_meta($post_id, '_job_location', $job['location']);
                update_post_meta($post_id, '_application', $job['canonicalPositionUrl']);
                update_post_meta($post_id, '_company_name', 'Arcadis');
                update_post_meta($post_id, '_job_sector', sanitize_title($skill));

                wp_update_post([
                    'ID' => $post_id,
                    'post_content' => 'Bekijk deze vacature op de website van Arcadis: ' . $job['canonicalPositionUrl']
                ]);
            }
        }
    }
}

if (!wp_next_scheduled('arcadis_weekly_job_import')) {
    wp_schedule_event(time(), 'weekly', 'arcadis_weekly_job_import');
}


// ✅ Adminpagina om Arcadis vacatures handmatig te importeren
add_action('admin_menu', function () {
    add_menu_page(
        'Import Arcadis Jobs',
        'Import Arcadis',
        'manage_options',
        'import-arcadis',
        function () {
            echo '<div class="wrap"><h1>Arcadis Vacatures Importeren</h1>';
            echo '<p>' . import_arcadis_jobs() . '</p>';
            echo '</div>';
        }
    );
});
