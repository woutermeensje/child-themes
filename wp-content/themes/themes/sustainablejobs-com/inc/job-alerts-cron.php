<?php
if (!defined('ABSPATH')) exit;

/**
 * DB migration: add last_sent and unsubscribe_token to the job alerts table.
 */
add_action('init', function () {
    if (get_option('sj_job_alerts_table_v2')) return;

    global $wpdb;
    $table   = $wpdb->prefix . 'sj_job_alerts';
    $columns = $wpdb->get_col("DESCRIBE {$table}", 0);

    if (!in_array('last_sent', $columns, true)) {
        $wpdb->query("ALTER TABLE {$table} ADD COLUMN last_sent datetime NULL DEFAULT NULL");
    }

    if (!in_array('unsubscribe_token', $columns, true)) {
        $wpdb->query("ALTER TABLE {$table} ADD COLUMN unsubscribe_token varchar(64) NOT NULL DEFAULT ''");
    }

    // Generate tokens for existing subscribers without a token.
    $wpdb->query(
        "UPDATE {$table} SET unsubscribe_token = SUBSTRING(MD5(RAND()), 1, 32) WHERE unsubscribe_token = ''"
    );

    update_option('sj_job_alerts_table_v2', true);
});

/**
 * Add a weekly interval to WP Cron.
 */
add_filter('cron_schedules', function (array $schedules): array {
    if (!isset($schedules['weekly'])) {
        $schedules['weekly'] = [
            'interval' => 7 * DAY_IN_SECONDS,
            'display'  => 'Once a week',
        ];
    }
    return $schedules;
});

/**
 * Schedule the weekly cron task if it is not scheduled yet.
 */
add_action('init', function () {
    if (!wp_next_scheduled('sj_job_alerts_weekly')) {
        // Every Monday at 08:00 server time.
        $next_monday = strtotime('next monday 08:00:00');
        wp_schedule_event($next_monday, 'weekly', 'sj_job_alerts_weekly');
    }
});

add_action('after_switch_theme', function () {
    if (!wp_next_scheduled('sj_job_alerts_weekly')) {
        wp_schedule_event(strtotime('next monday 08:00:00'), 'weekly', 'sj_job_alerts_weekly');
    }
});

/**
 * Link the weekly send action to the cron hook.
 */
add_action('sj_job_alerts_weekly', 'sj_send_weekly_job_alerts');

/**
 * Send weekly job alerts to all active subscribers.
 */
function sj_send_weekly_job_alerts(): void {
    global $wpdb;
    $table       = $wpdb->prefix . 'sj_job_alerts';
    $subscribers = $wpdb->get_results("SELECT * FROM {$table} WHERE active = 1", ARRAY_A);

    if (empty($subscribers)) return;

    foreach ($subscribers as $subscriber) {
        $sectors = json_decode($subscriber['sectors'] ?? '[]', true);
        if (empty($sectors)) continue;

        $jobs = sj_get_new_jobs_for_sectors($sectors);
        if (empty($jobs)) continue;

        // Zorg dat er altijd een token is
        $token = $subscriber['unsubscribe_token'] ?: wp_generate_password(32, false, false);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: Sustainablejobs.com <support@sustainablejobs.com>',
        ];

        $sent = wp_mail(
            $subscriber['email'],
            'New jobs in your field — Sustainablejobs.com',
            sj_build_job_alert_email($subscriber['voornaam'], $jobs, $token),
            $headers
        );

        if ($sent) {
            $wpdb->update(
                $table,
                ['last_sent' => current_time('mysql'), 'unsubscribe_token' => $token],
                ['id'        => (int) $subscriber['id']],
                ['%s', '%s'],
                ['%d']
            );
        }
    }
}

/**
 * Get jobs published in the last seven days for the selected sectors.
 */
function sj_get_new_jobs_for_sectors(array $sector_slugs): array {
    $query = new WP_Query([
        'post_type'      => 'job_listing',
        'post_status'    => 'publish',
        'posts_per_page' => 10,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'date_query'     => [
            ['after' => '7 days ago', 'inclusive' => true],
        ],
        'tax_query'      => [
            [
                'taxonomy' => 'job_sector',
                'field'    => 'slug',
                'terms'    => $sector_slugs,
                'operator' => 'IN',
            ],
        ],
    ]);

    return $query->posts ?: [];
}

/**
 * Build the HTML email for one subscriber.
 */
function sj_build_job_alert_email(string $voornaam, array $jobs, string $unsubscribe_token): string {
    $unsubscribe_url = add_query_arg('sj_unsubscribe', rawurlencode($unsubscribe_token), home_url('/'));
    $alle_jobs  = esc_url(home_url('/jobs/'));

    $job_rows = '';
    foreach ($jobs as $post) {
        $title      = esc_html(get_the_title($post));
        $link       = esc_url(get_permalink($post));
        $company    = esc_html(get_post_meta($post->ID, '_company_name', true));
        $location   = esc_html(get_post_meta($post->ID, '_job_location', true));
        $terms      = wp_get_post_terms($post->ID, 'job_sector', ['fields' => 'names']);
        $sector_str = !empty($terms) && !is_wp_error($terms) ? esc_html(implode(', ', $terms)) : '';

        $meta = array_filter([$company, $location, $sector_str]);
        $meta_html = $meta ? '<div style="margin-top:4px;font-size:13px;color:#666;">' . implode(' &nbsp;·&nbsp; ', $meta) . '</div>' : '';

        $job_rows .= "
        <tr>
          <td style=\"padding:18px 0;border-bottom:1px solid #e4ede9;\">
            <a href=\"{$link}\" style=\"font-size:16px;font-weight:700;color:#168AAD;text-decoration:none;font-family:Arial,sans-serif;\">{$title}</a>
            {$meta_html}
            <div style=\"margin-top:12px;\">
              <a href=\"{$link}\" style=\"display:inline-block;padding:8px 18px;background:#168AAD;color:#ffffff;font-size:13px;font-weight:600;border-radius:4px;text-decoration:none;font-family:Arial,sans-serif;\">View job &rarr;</a>
            </div>
          </td>
        </tr>";
    }

    return "<!DOCTYPE html>
<html lang=\"en\">
<head>
  <meta charset=\"UTF-8\">
  <meta name=\"viewport\" content=\"width=device-width,initial-scale=1.0\">
  <title>Job Alert — Sustainablejobs.com</title>
</head>
<body style=\"margin:0;padding:0;background:#f2f6f4;font-family:Arial,sans-serif;\">
<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#f2f6f4;padding:32px 16px;\">
  <tr>
    <td align=\"center\">
      <table width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #dceae4;\">

        <!-- Header -->
        <tr>
          <td style=\"background:#168AAD;padding:24px 32px;\">
            <span style=\"font-family:Arial,sans-serif;font-size:20px;font-weight:700;color:#ffffff;letter-spacing:-0.3px;\">Sustainablejobs.com</span>
          </td>
        </tr>

        <!-- Intro -->
        <tr>
          <td style=\"padding:28px 32px 8px;\">
            <p style=\"margin:0 0 10px;font-size:20px;font-weight:700;color:#168AAD;font-family:Arial,sans-serif;\">Hi {$voornaam}!</p>
            <p style=\"margin:0;font-size:15px;color:#444444;line-height:1.65;font-family:Arial,sans-serif;\">New sustainable jobs are available in your field. View them below and respond quickly; the best jobs move fast.</p>
          </td>
        </tr>

        <!-- Jobs -->
        <tr>
          <td style=\"padding:8px 32px 24px;\">
            <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">
              {$job_rows}
            </table>
          </td>
        </tr>

        <!-- CTA -->
        <tr>
          <td style=\"padding:0 32px 32px;text-align:center;\">
            <a href=\"{$alle_jobs}\" style=\"display:inline-block;padding:13px 28px;background:#168AAD;color:#ffffff;font-size:15px;font-weight:700;border-radius:6px;text-decoration:none;font-family:Arial,sans-serif;\">View all jobs</a>
          </td>
        </tr>

        <!-- Divider -->
        <tr>
          <td style=\"padding:0 32px;\"><hr style=\"border:none;border-top:1px solid #e4ede9;margin:0;\"></td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style=\"padding:20px 32px;text-align:center;\">
            <p style=\"margin:0 0 6px;font-size:12px;color:#999999;font-family:Arial,sans-serif;\">You are receiving this email because you set up a job alert on Sustainablejobs.com.</p>
            <p style=\"margin:0;font-size:12px;font-family:Arial,sans-serif;\">
              <a href=\"{$unsubscribe_url}\" style=\"color:#168AAD;\">Unsubscribe from job alerts</a>
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>";
}

/**
 * Handle unsubscribe requests via ?sj_unsubscribe={token}.
 */
add_action('init', function () {
    if (empty($_GET['sj_unsubscribe'])) return;

    global $wpdb;
    $token   = sanitize_text_field($_GET['sj_unsubscribe']);
    $table   = $wpdb->prefix . 'sj_job_alerts';
    $updated = $wpdb->update(
        $table,
        ['active' => 0],
        ['unsubscribe_token' => $token],
        ['%d'],
        ['%s']
    );

    $message = $updated
        ? 'You have successfully unsubscribed from Sustainablejobs.com job alerts.'
        : 'Unsubscribe link not recognized. You may already be unsubscribed.';

    wp_die(
        '<div style="font-family:Arial,sans-serif;max-width:480px;margin:80px auto;text-align:center;">'
        . '<p style="font-size:16px;color:#333;">' . esc_html($message) . '</p>'
        . '<a href="' . esc_url(home_url('/')) . '" style="color:#168AAD;font-size:14px;">Back to the homepage</a>'
        . '</div>',
        'Unsubscribe job alerts',
        ['response' => 200]
    );
});
