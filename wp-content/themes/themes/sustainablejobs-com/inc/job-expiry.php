<?php
if (!defined('ABSPATH')) exit;

// ── Register "expired" post status if WPJM is inactive ───────────────────────
add_action('init', function () {
    if (!get_post_status_object('expired')) {
        register_post_status('expired', [
            'label'                     => _x('Expired', 'post status'),
            'public'                    => false,
            'exclude_from_search'       => true,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop(
                'Expired <span class="count">(%s)</span>',
                'Expired <span class="count">(%s)</span>'
            ),
        ]);
    }
}, 12);

// ── Meta box ──────────────────────────────────────────────────────────────────
add_action('add_meta_boxes', function () {
    add_meta_box(
        'sj_job_expiry',
        'Expiration term',
        function ($post) {
            wp_nonce_field('sj_job_expiry', 'sj_job_expiry_nonce');
            $expires = get_post_meta($post->ID, '_job_expires', true);
            $value   = $expires ? date('Y-m-d', strtotime($expires)) : '';
            ?>
            <p>
                <label for="sj_job_expires" style="display:block;margin-bottom:5px;font-weight:600;">Expires on</label>
                <input type="date" id="sj_job_expires" name="sj_job_expires"
                       value="<?php echo esc_attr($value); ?>"
                       style="width:100%;padding:6px 8px;border:1px solid #ddd;border-radius:4px;font-size:13px;">
            </p>
            <p style="margin-top:8px;color:#777;font-size:12px;line-height:1.55;">
                The job automatically goes offline on the selected date.<br>
                Leave empty for no expiration date.
            </p>
            <?php
        },
        'job_listing',
        'side',
        'high'
    );
});

// ── Save meta ─────────────────────────────────────────────────────────────────
add_action('save_post_job_listing', function ($post_id) {
    if (
        !isset($_POST['sj_job_expiry_nonce']) ||
        !wp_verify_nonce($_POST['sj_job_expiry_nonce'], 'sj_job_expiry') ||
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
        !current_user_can('edit_post', $post_id)
    ) {
        return;
    }

    $raw = isset($_POST['sj_job_expires']) ? sanitize_text_field($_POST['sj_job_expires']) : '';

    if ($raw && ($ts = strtotime($raw)) !== false) {
        update_post_meta($post_id, '_job_expires', date('Y-m-d', $ts));
    } else {
        delete_post_meta($post_id, '_job_expires');
    }
});

// ── Admin column ──────────────────────────────────────────────────────────────
add_filter('manage_job_listing_posts_columns', function ($columns) {
    $columns['sj_expiry'] = 'Expires on';
    return $columns;
});

add_action('manage_job_listing_posts_custom_column', function ($column, $post_id) {
    if ($column !== 'sj_expiry') return;

    $expires = get_post_meta($post_id, '_job_expires', true);
    if (!$expires) {
        echo '<span style="color:#bbb;">—</span>';
        return;
    }

    $ts      = strtotime($expires);
    $expired = $ts < strtotime('today');
    $label   = date_i18n('d M Y', $ts);
    $style   = $expired ? 'color:#c0392b;font-weight:600;' : 'color:#2e7d32;';

    printf(
        '<span style="%s">%s%s</span>',
        esc_attr($style),
        esc_html($label),
        $expired ? ' ✕' : ''
    );
}, 10, 2);

add_filter('manage_edit-job_listing_sortable_columns', function ($columns) {
    $columns['sj_expiry'] = 'sj_expiry';
    return $columns;
});

add_action('pre_get_posts', function ($query) {
    if (!is_admin() || !$query->is_main_query()) return;
    if ($query->get('post_type') !== 'job_listing') return;
    if ($query->get('orderby') !== 'sj_expiry') return;
    $query->set('meta_key', '_job_expires');
    $query->set('orderby', 'meta_value');
});

// Send the contact person a notice once the expiration date has passed.
if (!function_exists('sj_job_expiry_date_has_passed')) {
    function sj_job_expiry_date_has_passed($post_id) {
        $expires = get_post_meta($post_id, '_job_expires', true);
        if (!$expires) {
            return false;
        }

        $expires_ts = strtotime($expires);
        if (!$expires_ts) {
            return false;
        }

        return date('Y-m-d', $expires_ts) < current_time('Y-m-d');
    }
}

if (!function_exists('sj_get_job_expiry_notice_recipient')) {
    function sj_get_job_expiry_notice_recipient($post_id) {
        $recipient = '';

        foreach (['_job_contact_email', '_company_email', '_application'] as $meta_key) {
            $candidate = trim((string) get_post_meta($post_id, $meta_key, true));

            if ($meta_key === '_application' && stripos($candidate, 'mailto:') === 0) {
                $candidate = strtok(substr($candidate, 7), '?');
            }

            if (is_email($candidate)) {
                $recipient = sanitize_email($candidate);
                break;
            }
        }

        $recipient = apply_filters('sj_job_expiry_notice_recipient', $recipient, $post_id);
        return is_email($recipient) ? sanitize_email($recipient) : '';
    }
}

if (!function_exists('sj_get_job_expiry_notice_name')) {
    function sj_get_job_expiry_notice_name($post_id) {
        $firstname = trim((string) get_post_meta($post_id, '_job_contact_firstname', true));
        $lastname  = trim((string) get_post_meta($post_id, '_job_contact_lastname', true));
        $name      = trim($firstname . ' ' . $lastname);

        return $name ?: 'contact person';
    }
}

if (!function_exists('sj_build_job_expiry_notice_body')) {
    function sj_build_job_expiry_notice_body($post_id) {
        $title      = wp_specialchars_decode(get_the_title($post_id), ENT_QUOTES);
        $expires    = get_post_meta($post_id, '_job_expires', true);
        $expires_ts = $expires ? strtotime($expires) : false;
        $date_label = $expires_ts ? date_i18n(get_option('date_format'), $expires_ts) : '';
        $name       = sj_get_job_expiry_notice_name($post_id);

        $body  = "Dear {$name},\n\n";
        $body .= "The expiration date of your job has been reached. The job is therefore no longer open on Sustainablejobs.com.\n\n";
        $body .= "Job: {$title}\n";
        if ($date_label) {
            $body .= "End date: {$date_label}\n";
        }
        $body .= "\n";
        $body .= "Would you like to keep the job open for longer or repost it? Let us know by replying to this email.\n\n";
        $body .= "Kind regards,\n";
        $body .= "Sustainablejobs.com\n";
        $body .= "support@sustainablejobs.com";

        return apply_filters('sj_job_expiry_notice_body', $body, $post_id);
    }
}

if (!function_exists('sj_send_job_expiry_notice')) {
    function sj_send_job_expiry_notice($post_id) {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'job_listing') {
            return false;
        }

        if (get_post_meta($post_id, '_sj_expiry_notice_sent', true)) {
            return false;
        }

        if (!sj_job_expiry_date_has_passed($post_id)) {
            return false;
        }

        $recipient = sj_get_job_expiry_notice_recipient($post_id);
        if (!$recipient) {
            return false;
        }

        $title   = wp_specialchars_decode(get_the_title($post_id), ENT_QUOTES);
        $subject = sprintf('Expiration term reached: %s', $title);
        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'Reply-To: Sustainablejobs.com <support@sustainablejobs.com>',
        ];

        $sent = wp_mail(
            $recipient,
            $subject,
            sj_build_job_expiry_notice_body($post_id),
            apply_filters('sj_job_expiry_notice_headers', $headers, $post_id)
        );

        if ($sent) {
            update_post_meta($post_id, '_sj_expiry_notice_sent', current_time('mysql'));
            update_post_meta($post_id, '_sj_expiry_notice_recipient', $recipient);
        }

        return $sent;
    }
}

add_action('transition_post_status', function ($new_status, $old_status, $post) {
    if (!$post || $post->post_type !== 'job_listing') {
        return;
    }

    if ($new_status === 'expired' && $old_status !== 'expired') {
        sj_send_job_expiry_notice($post->ID);
        return;
    }

    if ($new_status === 'publish' && $old_status === 'expired') {
        delete_post_meta($post->ID, '_sj_expiry_notice_sent');
        delete_post_meta($post->ID, '_sj_expiry_notice_recipient');
    }
}, 20, 3);

// Cron: take expired jobs offline daily.
add_action('init', function () {
    if (!wp_next_scheduled('sj_expire_job_listings')) {
        wp_schedule_event(time(), 'daily', 'sj_expire_job_listings');
    }
});

add_action('sj_expire_job_listings', function () {
    $today = current_time('Y-m-d');

    $ids = get_posts([
        'post_type'      => 'job_listing',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => [[
            'key'     => '_job_expires',
            'value'   => $today,
            'compare' => '<',
            'type'    => 'DATE',
        ]],
    ]);

    foreach ($ids as $id) {
        wp_update_post(['ID' => $id, 'post_status' => 'expired']);
        sj_send_job_expiry_notice($id);
    }
});
