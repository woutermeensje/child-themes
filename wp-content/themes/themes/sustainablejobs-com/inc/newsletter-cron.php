<?php
if (!defined('ABSPATH')) exit;

/**
 * Create the newsletter subscriber table.
 */
function sj_newsletter_create_table(): void {
    global $wpdb;
    $table   = $wpdb->prefix . 'sj_newsletter';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        voornaam varchar(100) NOT NULL DEFAULT '',
        email varchar(200) NOT NULL DEFAULT '',
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        last_sent datetime NULL DEFAULT NULL,
        unsubscribe_token varchar(64) NOT NULL DEFAULT '',
        active tinyint(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (id),
        UNIQUE KEY email (email)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

add_action('init', function () {
    if (!get_option('sj_newsletter_table_v1')) {
        sj_newsletter_create_table();
        update_option('sj_newsletter_table_v1', true);
    }
});

/**
 * Add a biweekly interval to WP Cron.
 */
add_filter('cron_schedules', function (array $schedules): array {
    if (!isset($schedules['biweekly'])) {
        $schedules['biweekly'] = [
            'interval' => 14 * DAY_IN_SECONDS,
            'display'  => 'Once every two weeks',
        ];
    }
    return $schedules;
});

/**
 * Migration: remove the weekly schedule and reschedule biweekly on Thursday at 14:00.
 */
add_action('init', function () {
    if (!get_option('sj_newsletter_biweekly_v1')) {
        wp_clear_scheduled_hook('sj_newsletter_weekly');
        wp_schedule_event(strtotime('next thursday 14:00:00'), 'biweekly', 'sj_newsletter_weekly');
        update_option('sj_newsletter_biweekly_v1', true);
    } elseif (!wp_next_scheduled('sj_newsletter_weekly')) {
        wp_schedule_event(strtotime('next thursday 14:00:00'), 'biweekly', 'sj_newsletter_weekly');
    }
});

add_action('after_switch_theme', function () {
    if (!wp_next_scheduled('sj_newsletter_weekly')) {
        wp_schedule_event(strtotime('next thursday 14:00:00'), 'biweekly', 'sj_newsletter_weekly');
    }
});

add_action('sj_newsletter_weekly', 'sj_send_weekly_newsletter');

/**
 * Send the newsletter through an ActiveCampaign campaign to list 10.
 */
function sj_send_weekly_newsletter(): void {
    $jobs = sj_get_all_new_jobs();

    if (empty($jobs)) {
        error_log('[Newsletter] No new jobs this period, send skipped.');
        return;
    }

    sj_ac_send_newsletter_campaign($jobs);
}

/**
 * Get all jobs published in the last 14 days.
 */
function sj_get_all_new_jobs(int $max = 15): array {
    $query = new WP_Query([
        'post_type'      => 'job_listing',
        'post_status'    => 'publish',
        'posts_per_page' => $max,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'date_query'     => [
            ['after' => '14 days ago', 'inclusive' => true],
        ],
    ]);

    return $query->posts ?: [];
}

/**
 * Build the HTML newsletter email.
 */
function sj_build_newsletter_email(string $voornaam, array $jobs, string $unsubscribe_token): string {
    $unsubscribe_url = add_query_arg('sj_nl_unsubscribe', rawurlencode($unsubscribe_token), home_url('/'));
    $alle_jobs  = esc_url(home_url('/jobs/'));
    $aanhef          = $voornaam ? "Hi {$voornaam}!" : 'Hi!';

    $job_rows = '';
    foreach ($jobs as $post) {
        $title      = esc_html(get_the_title($post));
        $link       = esc_url(get_permalink($post));
        $company    = esc_html(get_post_meta($post->ID, '_company_name', true));
        $location   = esc_html(get_post_meta($post->ID, '_job_location', true));
        $terms      = wp_get_post_terms($post->ID, 'job_sector', ['fields' => 'names']);
        $sector_str = !empty($terms) && !is_wp_error($terms) ? esc_html(implode(', ', $terms)) : '';

        $meta      = array_filter([$company, $location, $sector_str]);
        $meta_html = $meta
            ? '<div style="margin-top:4px;font-size:13px;color:#666;">' . implode(' &nbsp;·&nbsp; ', $meta) . '</div>'
            : '';

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

    $week_nr   = date('W');
    $jaar      = date('Y');

    return "<!DOCTYPE html>
<html lang=\"en\">
<head>
  <meta charset=\"UTF-8\">
  <meta name=\"viewport\" content=\"width=device-width,initial-scale=1.0\">
  <title>Jobnewsletter week {$week_nr} — Sustainablejobs.com</title>
</head>
<body style=\"margin:0;padding:0;background:#f2f6f4;font-family:Arial,sans-serif;\">
<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#f2f6f4;padding:32px 16px;\">
  <tr>
    <td align=\"center\">
      <table width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #dceae4;\">

        <!-- Header -->
        <tr>
          <td style=\"background:#168AAD;padding:24px 32px;\">
            <span style=\"font-family:Arial,sans-serif;font-size:20px;font-weight:700;color:#ffffff;\">Sustainablejobs.com</span>
            <span style=\"display:block;font-family:Arial,sans-serif;font-size:13px;color:rgba(255,255,255,0.75);margin-top:4px;\">Jobnewsletter &mdash; week {$week_nr}, {$jaar}</span>
          </td>
        </tr>

        <!-- Intro -->
        <tr>
          <td style=\"padding:28px 32px 8px;\">
            <p style=\"margin:0 0 10px;font-size:20px;font-weight:700;color:#168AAD;font-family:Arial,sans-serif;\">{$aanhef}</p>
            <p style=\"margin:0;font-size:15px;color:#444444;line-height:1.65;font-family:Arial,sans-serif;\">These are this week's latest sustainable jobs. Discover where you can make a difference.</p>
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
          <td style=\"background:#f2f6f4;padding:20px 32px;text-align:center;\">
            <p style=\"margin:0 0 6px;font-size:12px;color:#999999;font-family:Arial,sans-serif;\">You are receiving this newsletter because you signed up on Sustainablejobs.com.</p>
            <p style=\"margin:0;font-size:12px;font-family:Arial,sans-serif;\">
              <a href=\"{$unsubscribe_url}\" style=\"color:#168AAD;\">Unsubscribe from the newsletter</a>
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
 * Handle unsubscribe requests via ?sj_nl_unsubscribe={token}.
 */
add_action('init', function () {
    if (empty($_GET['sj_nl_unsubscribe'])) return;

    global $wpdb;
    $token   = sanitize_text_field($_GET['sj_nl_unsubscribe']);
    $table   = $wpdb->prefix . 'sj_newsletter';
    $updated = $wpdb->update(
        $table,
        ['active' => 0],
        ['unsubscribe_token' => $token],
        ['%d'],
        ['%s']
    );

    $message = $updated
        ? 'You have successfully unsubscribed from the Sustainablejobs.com newsletter.'
        : 'Unsubscribe link not recognized. You may already be unsubscribed.';

    wp_die(
        '<div style="font-family:Arial,sans-serif;max-width:480px;margin:80px auto;text-align:center;">'
        . '<p style="font-size:16px;color:#333;">' . esc_html($message) . '</p>'
        . '<a href="' . esc_url(home_url('/')) . '" style="color:#168AAD;font-size:14px;">Back to the homepage</a>'
        . '</div>',
        'Unsubscribe newsletter',
        ['response' => 200]
    );
});
