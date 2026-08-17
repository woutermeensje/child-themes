<?php
if (!defined('ABSPATH')) exit;

// Schakel alleen de WP Job Manager admin-waarschuwing voor bijna verlopen vacatures uit.
add_filter('job_manager_email_is_email_notification_enabled', function ($enabled, $email_notification_key) {
    if ($email_notification_key === 'admin_expiring_job') {
        return false;
    }

    return $enabled;
}, 10, 2);

// ── Register "expired" post status if WPJM is inactive ───────────────────────
add_action('init', function () {
    if (!get_post_status_object('expired')) {
        register_post_status('expired', [
            'label'                     => _x('Verlopen', 'post status'),
            'public'                    => false,
            'exclude_from_search'       => true,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop(
                'Verlopen <span class="count">(%s)</span>',
                'Verlopen <span class="count">(%s)</span>'
            ),
        ]);
    }
}, 12);

// ── Verwijder het native WPJM vervaldatum-veld uit de "Vacaturegegevens" box ──
// Zonder dit staan er twee losse velden voor dezelfde _job_expires meta:
// het native WPJM-veld en de custom "Verlooptermijn" box hieronder. Omdat de
// custom box op save_post_job_listing (na WPJM's eigen save_post-handler)
// altijd de waarde terugschrijft die bij het laden van de pagina zichtbaar
// was, overschrijft hij stilletjes elke wijziging die via het native veld is
// gedaan. Door het native veld hier te verbergen is er nog maar één plek om
// de vervaldatum te zetten, en verdwijnt die race condition.
add_filter('job_manager_job_listing_data_fields', function ($fields) {
    unset($fields['_job_expires']);
    return $fields;
});

// ── Meta box ──────────────────────────────────────────────────────────────────
add_action('add_meta_boxes', function () {
    add_meta_box(
        'sj_job_expiry',
        'Verlooptermijn',
        function ($post) {
            wp_nonce_field('sj_job_expiry', 'sj_job_expiry_nonce');
            $expires = get_post_meta($post->ID, '_job_expires', true);
            $value   = $expires ? date('Y-m-d', strtotime($expires)) : '';
            ?>
            <p>
                <label for="sj_job_expires" style="display:block;margin-bottom:5px;font-weight:600;">Verloopt op</label>
                <input type="date" id="sj_job_expires" name="sj_job_expires"
                       value="<?php echo esc_attr($value); ?>"
                       style="width:100%;padding:6px 8px;border:1px solid #ddd;border-radius:4px;font-size:13px;">
            </p>
            <p style="margin-top:8px;color:#777;font-size:12px;line-height:1.55;">
                De vacature gaat automatisch offline op de ingestelde datum.<br>
                Laat leeg voor geen vervaldatum.
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
    $columns['sj_expiry'] = 'Verloopt op';
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

// ── Spotlight: maximaal 30 dagen uitgelicht ─────────────────────────────────
if (!function_exists('sj_job_spotlight_is_enabled')) {
    function sj_job_spotlight_is_enabled($value): bool {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}

if (!function_exists('sj_sync_job_spotlight_started_at')) {
    function sj_sync_job_spotlight_started_at($post_id, $featured_value = null): void {
        $post_id = (int) $post_id;

        if (get_post_type($post_id) !== 'job_listing') {
            return;
        }

        $is_featured = sj_job_spotlight_is_enabled(
            $featured_value === null ? get_post_meta($post_id, '_featured', true) : $featured_value
        );

        if ($is_featured) {
            if (!get_post_meta($post_id, '_sj_spotlight_started_at', true)) {
                update_post_meta($post_id, '_sj_spotlight_started_at', current_time('mysql'));
            }
            return;
        }

        delete_post_meta($post_id, '_sj_spotlight_started_at');
        delete_post_meta($post_id, '_sj_spotlight_expired_at');
    }
}

if (!function_exists('sj_get_legacy_job_spotlight_started_at')) {
    function sj_get_legacy_job_spotlight_started_at($post_id): string {
        $post = get_post((int) $post_id);

        if (!$post) {
            return current_time('mysql');
        }

        foreach ([$post->post_modified, $post->post_date] as $date) {
            if ($date && $date !== '0000-00-00 00:00:00') {
                return $date;
            }
        }

        return current_time('mysql');
    }
}

add_action('added_post_meta', function ($meta_id, $post_id, $meta_key, $meta_value) {
    if ($meta_key === '_featured') {
        sj_sync_job_spotlight_started_at($post_id, $meta_value);
    }
}, 10, 4);

add_action('updated_post_meta', function ($meta_id, $post_id, $meta_key, $meta_value) {
    if ($meta_key === '_featured') {
        sj_sync_job_spotlight_started_at($post_id, $meta_value);
    }
}, 10, 4);

add_action('deleted_post_meta', function ($meta_ids, $post_id, $meta_key) {
    if ($meta_key === '_featured' && get_post_type((int) $post_id) === 'job_listing') {
        delete_post_meta((int) $post_id, '_sj_spotlight_started_at');
        delete_post_meta((int) $post_id, '_sj_spotlight_expired_at');
    }
}, 10, 3);

add_action('save_post_job_listing', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    sj_sync_job_spotlight_started_at((int) $post_id);
}, 999);

if (!function_exists('sj_expire_spotlight_job_listings')) {
    function sj_expire_spotlight_job_listings(): void {
        $cutoff = current_time('timestamp') - (30 * DAY_IN_SECONDS);

        $ids = get_posts([
            'post_type'      => 'job_listing',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_key'       => '_featured',
            'meta_value'     => '1',
        ]);

        foreach ($ids as $id) {
            $started_at = get_post_meta((int) $id, '_sj_spotlight_started_at', true);

            if (!$started_at) {
                $started_at = sj_get_legacy_job_spotlight_started_at((int) $id);
                update_post_meta((int) $id, '_sj_spotlight_started_at', $started_at);
            }

            $started_ts = strtotime($started_at);
            if (!$started_ts || $started_ts > $cutoff) {
                continue;
            }

            update_post_meta((int) $id, '_featured', '0');
            update_post_meta((int) $id, '_sj_spotlight_expired_at', current_time('mysql'));
        }
    }
}

// ── Cron: dagelijks spotlight (featured) status laten verlopen ──────────────
// Het daadwerkelijk offline zetten van verlopen vacatures wordt al door WPJM
// zelf gedaan (native hourly cron 'job_manager_check_for_expired_jobs', zie
// WP_Job_Manager_Post_Types::check_for_expired_jobs()). Die draait vaker
// (elk uur i.p.v. elke dag) en rekent correct met de site-tijdzone via
// current_datetime(), dus een eigen duplicaat hiervan is overbodig en voegde
// alleen een extra (tijdzone-onveilige) foutbron toe.
add_action('init', function () {
    if (!wp_next_scheduled('sj_expire_job_listings')) {
        wp_schedule_event(time(), 'daily', 'sj_expire_job_listings');
    }
});

add_action('sj_expire_job_listings', 'sj_expire_spotlight_job_listings');
