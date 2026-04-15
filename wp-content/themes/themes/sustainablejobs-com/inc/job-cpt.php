<?php
if (!defined('ABSPATH')) exit;

/* ============================================================
   Custom Post Type: sc_job_submission
   Stores job form submissions in the database.
============================================================ */

add_action('init', function () {
    register_post_type('sc_job_submission', [
        'labels' => [
            'name'               => 'Job Submissions',
            'singular_name'      => 'Job Submission',
            'add_new'            => 'New Submission',
            'add_new_item'       => 'New Job Submission',
            'edit_item'          => 'View Submission',
            'view_item'          => 'View Submission',
            'all_items'          => 'All Submissions',
            'search_items'       => 'Search Submissions',
            'not_found'          => 'No submissions found.',
            'not_found_in_trash' => 'No submissions in trash.',
            'menu_name'          => 'Job Submissions',
        ],
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_icon'           => 'dashicons-portfolio',
        'menu_position'       => 25,
        'supports'            => ['title'],
        'capability_type'     => 'post',
        'capabilities'        => ['create_posts' => 'do_not_allow'],
        'map_meta_cap'        => true,
        'show_in_rest'        => false,
    ]);
});

/* ── Custom admin columns ───────────────────────────── */
add_filter('manage_sc_job_submission_posts_columns', function ($columns) {
    return [
        'cb'          => $columns['cb'],
        'title'       => 'Job title',
        'sc_package'  => 'Package',
        'sc_company'  => 'Company',
        'sc_email'    => 'Email',
        'sc_type'     => 'Job type',
        'sc_location' => 'Location',
        'sc_status'   => 'Status',
        'date'        => 'Date',
    ];
});

add_action('manage_sc_job_submission_posts_custom_column', function ($column, $post_id) {
    switch ($column) {
        case 'sc_package':
            $package = get_post_meta($post_id, '_sc_package', true);
            $label = $package ? explode(':', $package)[0] : '—';
            echo '<strong>' . esc_html($label) . '</strong>';
            break;
        case 'sc_company':
            $first   = get_post_meta($post_id, '_sc_first_name', true);
            $last    = get_post_meta($post_id, '_sc_last_name', true);
            $company = get_post_meta($post_id, '_sc_company_name', true);
            echo esc_html($company);
            echo '<br><span style="color:#777;font-size:12px;">' . esc_html(trim("$first $last")) . '</span>';
            break;
        case 'sc_email':
            $email = get_post_meta($post_id, '_sc_email', true);
            echo '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>';
            break;
        case 'sc_type':
            $types = get_post_meta($post_id, '_sc_job_type', true);
            echo esc_html(is_array($types) ? implode(', ', $types) : ($types ?: '—'));
            break;
        case 'sc_location':
            echo esc_html(get_post_meta($post_id, '_sc_location', true) ?: '—');
            break;
        case 'sc_status':
            $status = get_post_status($post_id);
            $labels = [
                'pending' => ['New',       '#92E9AB', '#0A6B8D'],
                'publish' => ['Published', '#d1fae5', '#065f46'],
                'draft'   => ['Draft',     '#e5e7eb', '#374151'],
                'trash'   => ['Deleted',   '#fee2e2', '#991b1b'],
            ];
            [$text, $bg, $color] = $labels[$status] ?? ['—', '#f3f4f6', '#374151'];
            printf(
                '<span style="display:inline-block;padding:3px 10px;border-radius:999px;background:%s;color:%s;font-size:12px;font-weight:700;">%s</span>',
                esc_attr($bg), esc_attr($color), esc_html($text)
            );
            break;
    }
}, 10, 2);

add_filter('manage_edit-sc_job_submission_sortable_columns', function ($columns) {
    $columns['sc_company'] = 'sc_company';
    $columns['sc_package'] = 'sc_package';
    return $columns;
});

/* ── Meta box: all submission details ───────────────── */
add_action('add_meta_boxes', function () {
    add_meta_box(
        'sc_job_submission_details',
        'Submission details',
        'sc_job_submission_details_meta_box',
        'sc_job_submission',
        'normal',
        'high'
    );
    add_meta_box(
        'sc_job_submission_description',
        'Job description',
        'sc_job_submission_description_meta_box',
        'sc_job_submission',
        'normal',
        'default'
    );
    add_meta_box(
        'sc_job_submission_logo',
        'Company logo',
        'sc_job_submission_logo_meta_box',
        'sc_job_submission',
        'side',
        'default'
    );
});

function sc_job_submission_details_meta_box($post) {
    $fields = [
        'Package'      => get_post_meta($post->ID, '_sc_package', true),
        'First name'   => get_post_meta($post->ID, '_sc_first_name', true),
        'Last name'    => get_post_meta($post->ID, '_sc_last_name', true),
        'Company name' => get_post_meta($post->ID, '_sc_company_name', true),
        'Email'        => get_post_meta($post->ID, '_sc_email', true),
        'Location'     => get_post_meta($post->ID, '_sc_location', true),
        'Job type'     => implode(', ', (array) get_post_meta($post->ID, '_sc_job_type', true)),
        'How found'    => get_post_meta($post->ID, '_sc_referral', true),
    ];
    ?>
    <style>
    .sj-meta-table { width: 100%; border-collapse: collapse; font-family: -apple-system, sans-serif; }
    .sj-meta-table th { width: 160px; padding: 10px 12px; background: #f9fafb; border: 1px solid #e5e7eb; font-weight: 600; font-size: 13px; color: #374151; text-align: left; vertical-align: top; }
    .sj-meta-table td { padding: 10px 12px; border: 1px solid #e5e7eb; font-size: 13px; color: #111827; vertical-align: top; }
    .sj-meta-table td a { color: #0A6B8D; }
    .sj-meta-badge { display: inline-block; padding: 3px 10px; border-radius: 999px; background: #92E9AB; color: #0A6B8D; font-size: 12px; font-weight: 700; }
    </style>
    <table class="sj-meta-table">
        <?php foreach ($fields as $label => $value):
            $display = esc_html($value ?: '—');
            if ($label === 'Email' && $value) {
                $display = '<a href="mailto:' . esc_attr($value) . '">' . esc_html($value) . '</a>';
            }
            if ($label === 'Package' && $value) {
                $display = '<span class="sj-meta-badge">' . esc_html(explode(':', $value)[0]) . '</span> <span style="color:#6b7280;font-size:12px;">' . esc_html($value) . '</span>';
            }
        ?>
        <tr>
            <th><?php echo esc_html($label); ?></th>
            <td><?php echo $display; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php
}

function sc_job_submission_description_meta_box($post) {
    $description = get_post_meta($post->ID, '_sc_description', true);
    $extra_info  = get_post_meta($post->ID, '_sc_extra_info', true);
    ?>
    <style>
    .sj-rich-content { font-family: -apple-system, sans-serif; font-size: 14px; line-height: 1.7; color: #111827; padding: 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 5px; margin-bottom: 16px; }
    .sj-rich-content h4 { margin: 0 0 8px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; }
    </style>
    <?php if ($description): ?>
    <div class="sj-rich-content">
        <h4>Job description</h4>
        <?php echo wp_kses_post($description); ?>
    </div>
    <?php endif; ?>
    <?php if ($extra_info): ?>
    <div class="sj-rich-content">
        <h4>Additional information</h4>
        <?php echo wp_kses_post($extra_info); ?>
    </div>
    <?php endif; ?>
    <?php if (!$description && !$extra_info): ?>
        <p style="color:#9ca3af;font-size:13px;">No description available.</p>
    <?php endif; ?>
    <?php
}

function sc_job_submission_logo_meta_box($post) {
    $logo_id = get_post_meta($post->ID, '_sc_logo_id', true);
    if ($logo_id) {
        $logo_url = wp_get_attachment_image_url($logo_id, 'medium');
        if ($logo_url) {
            echo '<img src="' . esc_url($logo_url) . '" style="max-width:100%;border-radius:5px;border:1px solid #e5e7eb;">';
            echo '<p style="margin:8px 0 0;"><a href="' . esc_url(get_edit_post_link($logo_id)) . '" target="_blank" style="font-size:12px;color:#0A6B8D;">View in media library</a></p>';
        }
    } else {
        echo '<p style="color:#9ca3af;font-size:13px;margin:0;">No logo submitted.</p>';
    }
}

/* ── Hide "Publish" sidebar — read-only posts ───────── */
add_action('admin_head', function () {
    global $post_type;
    if ($post_type === 'sc_job_submission') {
        echo '<style>
        #submitdiv .misc-pub-section:not(.misc-pub-post-status) { display:none; }
        #submitdiv #publish { display:none; }
        #submitdiv #save-post { width:100%; text-align:center; }
        </style>';
    }
});

/* ── Admin notice: new submissions ─────────────────── */
add_action('admin_notices', function () {
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'sc_job_submission') return;

    $count   = wp_count_posts('sc_job_submission');
    $pending = $count->pending ?? 0;
    if ($pending > 0) {
        printf(
            '<div class="notice notice-info"><p><strong>%d new job submission%s</strong> awaiting review.</p></div>',
            $pending,
            $pending === 1 ? '' : 's'
        );
    }
});
