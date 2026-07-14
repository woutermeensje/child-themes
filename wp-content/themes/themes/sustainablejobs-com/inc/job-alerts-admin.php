<?php
if (!defined('ABSPATH')) exit;

/**
 * Register the admin page under Tools.
 */
add_action('admin_menu', function () {
    add_management_page(
        'Job Alerts & Newsletter',
        'Job Alerts & Newsletter',
        'manage_options',
        'sj-job-alerts',
        'sj_job_alerts_admin_page'
    );
});

/**
 * Handle actions before HTML output.
 */
function sj_job_alerts_handle_actions(): ?string {
    if (!current_user_can('manage_options')) return null;
    if (empty($_POST['sj_ja_action']) || !check_admin_referer('sj_ja_admin_action')) return null;

    $action = sanitize_key($_POST['sj_ja_action']);

    if ($action === 'test_email') {
        $email    = sanitize_email($_POST['test_email'] ?? '');
        $voornaam = sanitize_text_field($_POST['test_voornaam'] ?? 'Test');
        $sectors  = array_map('sanitize_title', (array) ($_POST['test_sectors'] ?? []));

        if (!is_email($email)) return '<div class="notice notice-error"><p>Please enter a valid email address.</p></div>';
        if (empty($sectors))   return '<div class="notice notice-error"><p>Select at least one sector.</p></div>';

        $jobs = sj_get_new_jobs_for_sectors($sectors);

        if (empty($jobs)) {
            return '<div class="notice notice-warning"><p>No jobs found in the last seven days for the selected sectors. Try other sectors or add a test publication first.</p></div>';
        }

        $token   = 'test-token-' . wp_generate_password(8, false, false);
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: Sustainablejobs.com <support@sustainablejobs.com>',
        ];

        $sent = wp_mail(
            $email,
            '[TEST] New jobs in your field — Sustainablejobs.com',
            sj_build_job_alert_email($voornaam, $jobs, $token),
            $headers
        );

        return $sent
            ? '<div class="notice notice-success"><p>Test email sent to <strong>' . esc_html($email) . '</strong> with ' . count($jobs) . ' job(s).</p></div>'
            : '<div class="notice notice-error"><p>Sending failed. Check the WordPress SMTP settings.</p></div>';
    }

    if ($action === 'run_full') {
        sj_send_weekly_job_alerts();
        return '<div class="notice notice-success"><p>Weekly send manually executed. Check your subscribers’ inboxes.</p></div>';
    }

    if ($action === 'test_newsletter') {
        $email    = sanitize_email($_POST['nl_test_email'] ?? '');
        $voornaam = sanitize_text_field($_POST['nl_test_voornaam'] ?? 'Test');

        if (!is_email($email)) return '<div class="notice notice-error"><p>Please enter a valid email address.</p></div>';

        $jobs = sj_get_all_new_jobs();
        if (empty($jobs)) {
            return '<div class="notice notice-warning"><p>No jobs found in the last period.</p></div>';
        }

        $token   = 'test-nl-token-' . wp_generate_password(8, false, false);
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: Sustainablejobs.com <support@sustainablejobs.com>',
        ];

        $sent = wp_mail(
            $email,
            "[TEST] This week's latest sustainable jobs — Sustainablejobs.com",
            sj_build_newsletter_email($voornaam, $jobs, $token),
            $headers
        );

        return $sent
            ? '<div class="notice notice-success"><p>Test newsletter sent to <strong>' . esc_html($email) . '</strong> with ' . count($jobs) . ' job(s).</p></div>'
            : '<div class="notice notice-error"><p>Sending failed. Check the SMTP settings.</p></div>';
    }

    if ($action === 'run_newsletter') {
        sj_send_weekly_newsletter();
        return '<div class="notice notice-success"><p>Newsletter manually sent to all active subscribers.</p></div>';
    }

    return null;
}

/**
 * Admin page HTML.
 */
function sj_job_alerts_admin_page(): void {
    global $wpdb;
    $table   = $wpdb->prefix . 'sj_job_alerts';
    $message = sj_job_alerts_handle_actions();

    $subscribers  = $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC", ARRAY_A) ?: [];
    $sector_terms = get_terms(['taxonomy' => 'job_sector', 'hide_empty' => false, 'orderby' => 'name']) ?: [];
    $next_cron    = wp_next_scheduled('sj_job_alerts_weekly');
    ?>
    <div class="wrap">
        <h1>Job Alerts — management & test</h1>
        <?php echo $message; ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:20px;align-items:start;">

            <!-- Test e-mail -->
            <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;">
                <h2 style="margin-top:0;">Send test email</h2>
                <p style="color:#555;font-size:13px;">Send a test email to a specific address based on selected sectors.</p>
                <form method="post">
                    <?php wp_nonce_field('sj_ja_admin_action'); ?>
                    <input type="hidden" name="sj_ja_action" value="test_email">

                    <table class="form-table" style="margin:0;">
                        <tr>
                            <th style="width:120px;padding:8px 0;"><label for="test_voornaam">First name</label></th>
                            <td style="padding:4px 0;"><input type="text" id="test_voornaam" name="test_voornaam" value="Test" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th style="padding:8px 0;"><label for="test_email">Email address</label></th>
                            <td style="padding:4px 0;"><input type="email" id="test_email" name="test_email" placeholder="you@email.com" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th style="padding:8px 0; vertical-align:top;">Sectors</th>
                            <td style="padding:4px 0;">
                                <div style="max-height:200px;overflow-y:auto;border:1px solid #ddd;border-radius:3px;padding:8px;background:#fafafa;">
                                    <?php foreach ($sector_terms as $term): ?>
                                    <label style="display:flex;align-items:center;gap:6px;padding:3px 0;font-size:13px;cursor:pointer;">
                                        <input type="checkbox" name="test_sectors[]" value="<?php echo esc_attr($term->slug); ?>">
                                        <?php echo esc_html($term->name); ?>
                                    </label>
                                    <?php endforeach; ?>
                                    <?php if (empty($sector_terms)): ?>
                                    <p style="color:#888;font-size:13px;margin:0;">No sectors found.</p>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <p style="margin-top:16px;">
                        <button type="submit" class="button button-primary">Send test email</button>
                    </p>
                </form>
            </div>

            <!-- Status & info -->
            <div>
                <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;margin-bottom:20px;">
                    <h2 style="margin-top:0;">Cron status</h2>
                    <table style="font-size:13px;border-collapse:collapse;width:100%;">
                        <tr>
                            <td style="padding:5px 0;color:#555;width:160px;">Next send</td>
                            <td style="padding:5px 0;font-weight:600;">
                                <?php echo $next_cron
                                    ? esc_html(wp_date('D d M Y \o\m H:i', $next_cron))
                                    : '<span style="color:#d63638;">Not scheduled</span>'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:5px 0;color:#555;">Active subscribers</td>
                            <td style="padding:5px 0;font-weight:600;"><?php echo (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE active = 1"); ?></td>
                        </tr>
                        <tr>
                            <td style="padding:5px 0;color:#555;">Total signups</td>
                            <td style="padding:5px 0;font-weight:600;"><?php echo (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}"); ?></td>
                        </tr>
                    </table>
                </div>

                <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;">
                    <h2 style="margin-top:0;">Run full send</h2>
                    <p style="color:#555;font-size:13px;">Sends the weekly email directly to <strong>all active subscribers</strong>, only when there are new jobs in their sectors.</p>
                    <form method="post" onsubmit="return confirm('Are you sure you want to send the email to all active subscribers?');">
                        <?php wp_nonce_field('sj_ja_admin_action'); ?>
                        <input type="hidden" name="sj_ja_action" value="run_full">
                        <button type="submit" class="button button-secondary">Run now for all subscribers</button>
                    </form>
                </div>
            </div>

        </div>

        <!-- Abonneelijst -->
        <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;margin-top:24px;">
            <h2 style="margin-top:0;">Subscribers (<?php echo count($subscribers); ?>)</h2>
            <?php if (empty($subscribers)): ?>
                <p style="color:#888;">No subscribers yet.</p>
            <?php else: ?>
            <table class="widefat striped" style="font-size:13px;">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email address</th>
                        <th>Sectors</th>
                        <th>Signed up</th>
                        <th>Last email</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscribers as $sub):
                        $sectors     = json_decode($sub['sectors'] ?? '[]', true) ?: [];
                        $sector_names = array_filter(array_map(function ($slug) {
                            $term = get_term_by('slug', $slug, 'job_sector');
                            return $term ? $term->name : null;
                        }, $sectors));
                    ?>
                    <tr>
                        <td><?php echo esc_html($sub['voornaam']); ?></td>
                        <td><?php echo esc_html($sub['email']); ?></td>
                        <td><?php echo esc_html(implode(', ', $sector_names) ?: '—'); ?></td>
                        <td><?php echo esc_html(wp_date('d M Y', strtotime($sub['created_at']))); ?></td>
                        <td><?php echo $sub['last_sent'] ? esc_html(wp_date('d M Y', strtotime($sub['last_sent']))) : '—'; ?></td>
                        <td>
                            <?php if ($sub['active']): ?>
                                <span style="color:#00a32a;font-weight:600;">Active</span>
                            <?php else: ?>
                                <span style="color:#d63638;">Unsubscribed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Newsletter sectie -->
        <hr style="margin:32px 0;border:none;border-top:1px solid #c3c4c7;">
        <h1>Newsletter — management & test</h1>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:20px;align-items:start;">

            <!-- Test newsletter -->
            <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;">
                <h2 style="margin-top:0;">Send test newsletter</h2>
                <p style="color:#555;font-size:13px;">Send a test newsletter with the jobs from this period to a specific address.</p>
                <form method="post">
                    <?php wp_nonce_field('sj_ja_admin_action'); ?>
                    <input type="hidden" name="sj_ja_action" value="test_newsletter">

                    <table class="form-table" style="margin:0;">
                        <tr>
                            <th style="width:120px;padding:8px 0;"><label for="nl_test_voornaam">First name</label></th>
                            <td style="padding:4px 0;"><input type="text" id="nl_test_voornaam" name="nl_test_voornaam" value="Test" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th style="padding:8px 0;"><label for="nl_test_email">Email address</label></th>
                            <td style="padding:4px 0;"><input type="email" id="nl_test_email" name="nl_test_email" placeholder="you@email.com" class="regular-text" required></td>
                        </tr>
                    </table>

                    <p style="margin-top:16px;">
                        <button type="submit" class="button button-primary">Send test newsletter</button>
                    </p>
                </form>
            </div>

            <!-- Newsletter status and full send. -->
            <div>
                <?php
                $nl_table    = $wpdb->prefix . 'sj_newsletter';
                $nl_next     = wp_next_scheduled('sj_newsletter_weekly');
                ?>
                <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;margin-bottom:20px;">
                    <h2 style="margin-top:0;">Cron status newsletter</h2>
                    <table style="font-size:13px;border-collapse:collapse;width:100%;">
                        <tr>
                            <td style="padding:5px 0;color:#555;width:160px;">Next send</td>
                            <td style="padding:5px 0;font-weight:600;">
                                <?php echo $nl_next
                                    ? esc_html(wp_date('D d M Y \o\m H:i', $nl_next))
                                    : '<span style="color:#d63638;">Not scheduled</span>'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:5px 0;color:#555;">Active subscribers</td>
                            <td style="padding:5px 0;font-weight:600;"><?php echo (int) $wpdb->get_var("SELECT COUNT(*) FROM {$nl_table} WHERE active = 1"); ?></td>
                        </tr>
                        <tr>
                            <td style="padding:5px 0;color:#555;">Total signups</td>
                            <td style="padding:5px 0;font-weight:600;"><?php echo (int) $wpdb->get_var("SELECT COUNT(*) FROM {$nl_table}"); ?></td>
                        </tr>
                    </table>
                </div>

                <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;">
                    <h2 style="margin-top:0;">Run full send</h2>
                    <p style="color:#555;font-size:13px;">Sends the newsletter directly to <strong>all active subscribers</strong>.</p>
                    <form method="post" onsubmit="return confirm('Are you sure you want to send the newsletter to all active subscribers?');">
                        <?php wp_nonce_field('sj_ja_admin_action'); ?>
                        <input type="hidden" name="sj_ja_action" value="run_newsletter">
                        <button type="submit" class="button button-secondary">Run now for all subscribers</button>
                    </form>
                </div>
            </div>

        </div>

        <!-- Newsletter abonneelijst -->
        <?php
        $nl_subscribers = $wpdb->get_results("SELECT * FROM {$nl_table} ORDER BY created_at DESC", ARRAY_A) ?: [];
        ?>
        <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;margin-top:24px;">
            <h2 style="margin-top:0;">Newsletter subscribers (<?php echo count($nl_subscribers); ?>)</h2>
            <?php if (empty($nl_subscribers)): ?>
                <p style="color:#888;">No subscribers yet.</p>
            <?php else: ?>
            <table class="widefat striped" style="font-size:13px;">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email address</th>
                        <th>Signed up</th>
                        <th>Last email</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($nl_subscribers as $sub): ?>
                    <tr>
                        <td><?php echo esc_html($sub['voornaam']); ?></td>
                        <td><?php echo esc_html($sub['email']); ?></td>
                        <td><?php echo esc_html(wp_date('d M Y', strtotime($sub['created_at']))); ?></td>
                        <td><?php echo $sub['last_sent'] ? esc_html(wp_date('d M Y', strtotime($sub['last_sent']))) : '—'; ?></td>
                        <td>
                            <?php if ($sub['active']): ?>
                                <span style="color:#00a32a;font-weight:600;">Active</span>
                            <?php else: ?>
                                <span style="color:#d63638;">Unsubscribed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

    </div>
    <?php
}
