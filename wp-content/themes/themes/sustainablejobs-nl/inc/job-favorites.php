<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('sj_get_favorites_heart_svg')) {
    function sj_get_favorites_heart_svg(): string {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z"/></svg>';
    }
}

if (!function_exists('sj_job_listing_supports_favorites')) {
    function sj_job_listing_supports_favorites($post_id): bool {
        $post_id = (int) $post_id;

        if ($post_id <= 0 || get_post_type($post_id) !== 'job_listing') {
            return false;
        }

        if (function_exists('sj_job_listing_is_freemium') && sj_job_listing_is_freemium($post_id)) {
            return false;
        }

        return true;
    }
}

if (!function_exists('sj_job_favorites_table_name')) {
    function sj_job_favorites_table_name(): string {
        global $wpdb;
        return $wpdb->prefix . 'sj_job_favorites';
    }
}

if (!function_exists('sj_job_favorites_create_table')) {
    function sj_job_favorites_create_table(): void {
        global $wpdb;

        $table   = sj_job_favorites_table_name();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            job_id bigint(20) unsigned NOT NULL,
            visitor_hash char(64) NOT NULL DEFAULT '',
            favorited_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            unfavorited_at datetime DEFAULT NULL,
            last_activity_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            save_count int(10) unsigned NOT NULL DEFAULT 1,
            remove_count int(10) unsigned NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY job_visitor (job_id, visitor_hash),
            KEY job_active (job_id, is_active),
            KEY last_activity_at (last_activity_at)
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}

add_action('init', function () {
    if (!get_option('sj_job_favorites_table_v1')) {
        sj_job_favorites_create_table();
        update_option('sj_job_favorites_table_v1', true);
    }
});

if (!function_exists('sj_job_favorites_visitor_hash')) {
    function sj_job_favorites_visitor_hash($visitor_id): string {
        if (is_array($visitor_id)) {
            return '';
        }

        $visitor_id = sanitize_text_field(wp_unslash((string) $visitor_id));
        $visitor_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $visitor_id);

        if (!$visitor_id || strlen($visitor_id) < 12 || strlen($visitor_id) > 120) {
            return '';
        }

        return hash_hmac('sha256', $visitor_id, wp_salt('nonce'));
    }
}

if (!function_exists('sj_get_job_favorite_stats')) {
    function sj_get_job_favorite_stats(int $job_id): array {
        global $wpdb;

        $table = sj_job_favorites_table_name();
        $stats = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT
                    COALESCE(SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END), 0) AS active_count,
                    COALESCE(SUM(save_count), 0) AS save_count,
                    COALESCE(SUM(remove_count), 0) AS remove_count,
                    MAX(last_activity_at) AS last_activity_at
                 FROM {$table}
                 WHERE job_id = %d",
                $job_id
            ),
            ARRAY_A
        ) ?: [];

        return [
            'active_count'     => (int) ($stats['active_count'] ?? 0),
            'save_count'       => (int) ($stats['save_count'] ?? 0),
            'remove_count'     => (int) ($stats['remove_count'] ?? 0),
            'last_activity_at' => (string) ($stats['last_activity_at'] ?? ''),
        ];
    }
}

if (!function_exists('sj_record_job_favorite_event')) {
    function sj_record_job_favorite_event(int $job_id, string $visitor_hash, string $state): array {
        global $wpdb;

        $table = sj_job_favorites_table_name();
        $now   = current_time('mysql');
        $row   = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, is_active FROM {$table} WHERE job_id = %d AND visitor_hash = %s LIMIT 1",
                $job_id,
                $visitor_hash
            ),
            ARRAY_A
        );

        if ($state === 'favorite') {
            if ($row) {
                if ((int) $row['is_active'] === 1) {
                    $wpdb->update(
                        $table,
                        ['last_activity_at' => $now],
                        ['id' => (int) $row['id']],
                        ['%s'],
                        ['%d']
                    );
                } else {
                    $wpdb->query(
                        $wpdb->prepare(
                            "UPDATE {$table}
                             SET is_active = 1,
                                 favorited_at = %s,
                                 unfavorited_at = NULL,
                                 last_activity_at = %s,
                                 save_count = save_count + 1
                             WHERE id = %d",
                            $now,
                            $now,
                            (int) $row['id']
                        )
                    );
                }
            } else {
                $wpdb->insert(
                    $table,
                    [
                        'job_id'           => $job_id,
                        'visitor_hash'     => $visitor_hash,
                        'favorited_at'     => $now,
                        'last_activity_at' => $now,
                        'is_active'        => 1,
                        'save_count'       => 1,
                        'remove_count'     => 0,
                    ],
                    ['%d', '%s', '%s', '%s', '%d', '%d', '%d']
                );
            }
        }

        if ($state === 'unfavorite' && $row && (int) $row['is_active'] === 1) {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$table}
                     SET is_active = 0,
                         unfavorited_at = %s,
                         last_activity_at = %s,
                         remove_count = remove_count + 1
                     WHERE id = %d",
                    $now,
                    $now,
                    (int) $row['id']
                )
            );
        }

        return sj_get_job_favorite_stats($job_id);
    }
}

if (!function_exists('sj_get_job_favorite_data')) {
    function sj_get_job_favorite_data($post_id = null): array {
        $post_id = $post_id ?: get_the_ID();
        $post_id = (int) $post_id;
        $post    = $post_id ? get_post($post_id) : null;

        if (!$post || $post->post_type !== 'job_listing' || !sj_job_listing_supports_favorites($post_id)) {
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

add_action('wp_ajax_sj_track_job_favorite', 'sj_track_job_favorite_ajax');
add_action('wp_ajax_nopriv_sj_track_job_favorite', 'sj_track_job_favorite_ajax');
add_action('wp_ajax_sj_get_favorite_jobs', 'sj_get_favorite_jobs_ajax');
add_action('wp_ajax_nopriv_sj_get_favorite_jobs', 'sj_get_favorite_jobs_ajax');

if (!function_exists('sj_track_job_favorite_ajax')) {
    function sj_track_job_favorite_ajax(): void {
        if (!check_ajax_referer('sj_job_favorite_tracking', 'nonce', false)) {
            wp_send_json_error(['message' => 'Ongeldige beveiligingscontrole.'], 403);
        }

        $job_id_raw       = $_POST['job_id'] ?? 0;
        $state_raw        = $_POST['favorite_state'] ?? '';
        $visitor_id_raw   = $_POST['visitor_id'] ?? '';
        $job_id           = is_array($job_id_raw) ? 0 : absint(wp_unslash($job_id_raw));
        $state            = is_array($state_raw) ? '' : sanitize_key(wp_unslash($state_raw));
        $visitor_hash     = sj_job_favorites_visitor_hash($visitor_id_raw);

        if (!$job_id || !in_array($state, ['favorite', 'unfavorite'], true) || !$visitor_hash) {
            wp_send_json_error(['message' => 'Onvolledige favorietdata.'], 400);
        }

        if (!sj_job_listing_supports_favorites($job_id)) {
            wp_send_json_error(['message' => 'Deze vacature ondersteunt geen favorieten.'], 400);
        }

        wp_send_json_success([
            'job_id' => $job_id,
            'stats'  => sj_record_job_favorite_event($job_id, $visitor_hash, $state),
        ]);
    }
}

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

if (!function_exists('sj_get_job_favorites_overview_stats')) {
    function sj_get_job_favorites_overview_stats(): array {
        global $wpdb;

        $table = sj_job_favorites_table_name();
        $stats = $wpdb->get_row(
            "SELECT
                COALESCE(SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END), 0) AS active_count,
                COALESCE(SUM(save_count), 0) AS save_count,
                COALESCE(SUM(remove_count), 0) AS remove_count,
                COUNT(DISTINCT job_id) AS tracked_jobs,
                MAX(last_activity_at) AS last_activity_at
             FROM {$table}",
            ARRAY_A
        ) ?: [];

        return [
            'active_count'     => (int) ($stats['active_count'] ?? 0),
            'save_count'       => (int) ($stats['save_count'] ?? 0),
            'remove_count'     => (int) ($stats['remove_count'] ?? 0),
            'tracked_jobs'     => (int) ($stats['tracked_jobs'] ?? 0),
            'last_activity_at' => (string) ($stats['last_activity_at'] ?? ''),
        ];
    }
}

if (!function_exists('sj_get_job_favorites_admin_rows')) {
    function sj_get_job_favorites_admin_rows(int $limit = 200): array {
        global $wpdb;

        $table = sj_job_favorites_table_name();
        $limit = max(1, min(500, $limit));

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    f.job_id,
                    p.post_title,
                    p.post_status,
                    COALESCE(SUM(CASE WHEN f.is_active = 1 THEN 1 ELSE 0 END), 0) AS active_count,
                    COALESCE(SUM(f.save_count), 0) AS save_count,
                    COALESCE(SUM(f.remove_count), 0) AS remove_count,
                    MAX(f.last_activity_at) AS last_activity_at
                 FROM {$table} f
                 INNER JOIN {$wpdb->posts} p ON p.ID = f.job_id
                 WHERE p.post_type = %s
                 GROUP BY f.job_id, p.post_title, p.post_status
                 ORDER BY active_count DESC, save_count DESC, last_activity_at DESC
                 LIMIT %d",
                'job_listing',
                $limit
            ),
            ARRAY_A
        ) ?: [];
    }
}

if (!function_exists('sj_job_favorites_company_label')) {
    function sj_job_favorites_company_label(int $job_id): string {
        $company_terms = get_the_terms($job_id, 'job_company');
        if (!is_wp_error($company_terms) && !empty($company_terms)) {
            return (string) $company_terms[0]->name;
        }

        $company = get_post_meta($job_id, '_company_name', true);
        return $company ? (string) $company : '';
    }
}

if (!function_exists('sj_job_favorites_format_datetime')) {
    function sj_job_favorites_format_datetime(string $datetime): string {
        if (!$datetime || $datetime === '0000-00-00 00:00:00') {
            return '-';
        }

        return mysql2date('d M Y H:i', $datetime);
    }
}

add_action('admin_menu', function () {
    add_submenu_page(
        'edit.php?post_type=job_listing',
        'Vacature favorieten',
        'Favorieten',
        'manage_options',
        'sj-job-favorites',
        'sj_job_favorites_admin_page'
    );
});

if (!function_exists('sj_job_favorites_admin_page')) {
    function sj_job_favorites_admin_page(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Je hebt geen rechten om deze pagina te bekijken.', 'sustainablejobs-nl'));
        }

        $stats = sj_get_job_favorites_overview_stats();
        $rows  = sj_get_job_favorites_admin_rows(200);
        ?>
        <div class="wrap">
            <h1>Vacature favorieten</h1>
            <p style="max-width:760px;">
                Dit overzicht toont anonieme favorieten-activiteit vanaf het moment dat deze meting actief is.
                De telling is gebaseerd op browsers, niet op persoonsgegevens.
            </p>

            <div style="display:grid;grid-template-columns:repeat(4,minmax(160px,1fr));gap:16px;margin:20px 0;">
                <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:16px;">
                    <div style="color:#646970;font-size:13px;">Actieve favorieten</div>
                    <strong style="display:block;font-size:28px;line-height:1.2;margin-top:6px;"><?php echo esc_html(number_format_i18n($stats['active_count'])); ?></strong>
                </div>
                <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:16px;">
                    <div style="color:#646970;font-size:13px;">Totaal opgeslagen</div>
                    <strong style="display:block;font-size:28px;line-height:1.2;margin-top:6px;"><?php echo esc_html(number_format_i18n($stats['save_count'])); ?></strong>
                </div>
                <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:16px;">
                    <div style="color:#646970;font-size:13px;">Verwijderd</div>
                    <strong style="display:block;font-size:28px;line-height:1.2;margin-top:6px;"><?php echo esc_html(number_format_i18n($stats['remove_count'])); ?></strong>
                </div>
                <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:16px;">
                    <div style="color:#646970;font-size:13px;">Vacatures met activiteit</div>
                    <strong style="display:block;font-size:28px;line-height:1.2;margin-top:6px;"><?php echo esc_html(number_format_i18n($stats['tracked_jobs'])); ?></strong>
                    <div style="color:#646970;font-size:12px;margin-top:8px;">Laatst: <?php echo esc_html(sj_job_favorites_format_datetime($stats['last_activity_at'])); ?></div>
                </div>
            </div>

            <table class="widefat striped" style="margin-top:20px;">
                <thead>
                    <tr>
                        <th>Vacature</th>
                        <th>Organisatie</th>
                        <th>Status</th>
                        <th style="width:120px;">Actief</th>
                        <th style="width:150px;">Totaal opgeslagen</th>
                        <th style="width:120px;">Verwijderd</th>
                        <th style="width:170px;">Laatste activiteit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="7">Nog geen favorieten-activiteit gemeten.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row):
                            $job_id      = (int) $row['job_id'];
                            $edit_link   = get_edit_post_link($job_id);
                            $view_link   = get_permalink($job_id);
                            $status_obj  = get_post_status_object((string) $row['post_status']);
                            $status_name = $status_obj ? $status_obj->label : (string) $row['post_status'];
                            ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php if ($edit_link): ?>
                                            <a href="<?php echo esc_url($edit_link); ?>"><?php echo esc_html($row['post_title']); ?></a>
                                        <?php else: ?>
                                            <?php echo esc_html($row['post_title']); ?>
                                        <?php endif; ?>
                                    </strong>
                                    <?php if ($view_link): ?>
                                        <div style="font-size:12px;margin-top:4px;"><a href="<?php echo esc_url($view_link); ?>" target="_blank" rel="noopener">Bekijk vacature</a></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html(sj_job_favorites_company_label($job_id) ?: '-'); ?></td>
                                <td><?php echo esc_html($status_name); ?></td>
                                <td><strong><?php echo esc_html(number_format_i18n((int) $row['active_count'])); ?></strong></td>
                                <td><?php echo esc_html(number_format_i18n((int) $row['save_count'])); ?></td>
                                <td><?php echo esc_html(number_format_i18n((int) $row['remove_count'])); ?></td>
                                <td><?php echo esc_html(sj_job_favorites_format_datetime((string) $row['last_activity_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
