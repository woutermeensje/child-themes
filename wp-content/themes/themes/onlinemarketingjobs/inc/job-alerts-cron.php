<?php
if (!defined('ABSPATH')) exit;

/**
 * DB-migratie: voeg last_sent en unsubscribe_token toe aan de job alerts tabel.
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

    // Genereer tokens voor bestaande abonnees zonder token
    $wpdb->query(
        "UPDATE {$table} SET unsubscribe_token = SUBSTRING(MD5(RAND()), 1, 32) WHERE unsubscribe_token = ''"
    );

    update_option('sj_job_alerts_table_v2', true);
});

/**
 * Voeg wekelijks interval toe aan WP Cron.
 */
add_filter('cron_schedules', function (array $schedules): array {
    if (!isset($schedules['weekly'])) {
        $schedules['weekly'] = [
            'interval' => 7 * DAY_IN_SECONDS,
            'display'  => 'Eén keer per week',
        ];
    }
    return $schedules;
});

/**
 * Plan de wekelijkse cron-taak als die nog niet gepland staat.
 */
add_action('init', function () {
    if (!wp_next_scheduled('sj_job_alerts_weekly')) {
        // Elke maandag om 08:00 (servertijd)
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
 * Koppel de wekelijkse verzendactie aan de cron-hook.
 */
add_action('sj_job_alerts_weekly', 'sj_send_weekly_job_alerts');

/**
 * Verstuur wekelijkse job alerts naar alle actieve abonnees.
 */
function sj_send_weekly_job_alerts(): void {
    global $wpdb;
    $table       = $wpdb->prefix . 'sj_job_alerts';
    $subscribers = $wpdb->get_results("SELECT * FROM {$table} WHERE active = 1", ARRAY_A);

    if (empty($subscribers)) return;

    foreach ($subscribers as $subscriber) {
        $sectors = json_decode($subscriber['sectors'] ?? '[]', true);
        if (empty($sectors)) continue;

        $vacatures = sj_get_new_vacatures_for_sectors($sectors);
        if (empty($vacatures)) continue;

        // Zorg dat er altijd een token is
        $token = $subscriber['unsubscribe_token'] ?: wp_generate_password(32, false, false);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: Onlinemarketingjobs.nl <support@onlinemarketingjobs.nl>',
        ];

        $sent = wp_mail(
            $subscriber['email'],
            'Nieuwe vacatures in jouw vakgebied — Onlinemarketingjobs.nl',
            sj_build_job_alert_email($subscriber['voornaam'], $vacatures, $token),
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
 * Haal vacatures op die de afgelopen 7 dagen zijn gepubliceerd in de opgegeven sectoren.
 */
function sj_get_new_vacatures_for_sectors(array $sector_slugs): array {
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
 * Bouw de HTML e-mail op voor één abonnee.
 */
function sj_build_job_alert_email(string $voornaam, array $vacatures, string $unsubscribe_token): string {
    $unsubscribe_url = add_query_arg('sj_unsubscribe', rawurlencode($unsubscribe_token), home_url('/'));
    $alle_vacatures  = esc_url(home_url('/vacatures/'));

    $vacature_rows = '';
    foreach ($vacatures as $post) {
        $title      = esc_html(get_the_title($post));
        $link       = esc_url(get_permalink($post));
        $company    = esc_html(get_post_meta($post->ID, '_company_name', true));
        $location   = esc_html(get_post_meta($post->ID, '_job_location', true));
        $terms      = wp_get_post_terms($post->ID, 'job_sector', ['fields' => 'names']);
        $sector_str = !empty($terms) && !is_wp_error($terms) ? esc_html(implode(', ', $terms)) : '';

        $meta = array_filter([$company, $location, $sector_str]);
        $meta_html = $meta ? '<div style="margin-top:4px;font-size:13px;color:#666;">' . implode(' &nbsp;·&nbsp; ', $meta) . '</div>' : '';

        $vacature_rows .= "
        <tr>
          <td style=\"padding:18px 0;border-bottom:1px solid #e4ede9;\">
            <a href=\"{$link}\" style=\"font-size:16px;font-weight:700;color:#7C5CFA;text-decoration:none;font-family:Arial,sans-serif;\">{$title}</a>
            {$meta_html}
            <div style=\"margin-top:12px;\">
              <a href=\"{$link}\" style=\"display:inline-block;padding:8px 18px;background:#7C5CFA;color:#ffffff;font-size:13px;font-weight:600;border-radius:4px;text-decoration:none;font-family:Arial,sans-serif;\">Bekijk vacature &rarr;</a>
            </div>
          </td>
        </tr>";
    }

    return "<!DOCTYPE html>
<html lang=\"nl\">
<head>
  <meta charset=\"UTF-8\">
  <meta name=\"viewport\" content=\"width=device-width,initial-scale=1.0\">
  <title>Job Alert — Onlinemarketingjobs.nl</title>
</head>
<body style=\"margin:0;padding:0;background:#f2f6f4;font-family:Arial,sans-serif;\">
<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#f2f6f4;padding:32px 16px;\">
  <tr>
    <td align=\"center\">
      <table width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #dceae4;\">

        <!-- Header -->
        <tr>
          <td style=\"background:#7C5CFA;padding:24px 32px;\">
            <span style=\"font-family:Arial,sans-serif;font-size:20px;font-weight:700;color:#ffffff;letter-spacing:-0.3px;\">Onlinemarketingjobs.nl</span>
          </td>
        </tr>

        <!-- Intro -->
        <tr>
          <td style=\"padding:28px 32px 8px;\">
            <p style=\"margin:0 0 10px;font-size:20px;font-weight:700;color:#7C5CFA;font-family:Arial,sans-serif;\">Hoi {$voornaam}!</p>
            <p style=\"margin:0;font-size:15px;color:#444444;line-height:1.65;font-family:Arial,sans-serif;\">Er staan nieuwe online marketing vacatures klaar in jouw vakgebied. Bekijk ze hieronder en reageer snel &mdash; de beste vacatures gaan snel!</p>
          </td>
        </tr>

        <!-- Vacatures -->
        <tr>
          <td style=\"padding:8px 32px 24px;\">
            <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">
              {$vacature_rows}
            </table>
          </td>
        </tr>

        <!-- CTA -->
        <tr>
          <td style=\"padding:0 32px 32px;text-align:center;\">
            <a href=\"{$alle_vacatures}\" style=\"display:inline-block;padding:13px 28px;background:#7C5CFA;color:#ffffff;font-size:15px;font-weight:700;border-radius:6px;text-decoration:none;font-family:Arial,sans-serif;\">Bekijk alle vacatures</a>
          </td>
        </tr>

        <!-- Divider -->
        <tr>
          <td style=\"padding:0 32px;\"><hr style=\"border:none;border-top:1px solid #e4ede9;margin:0;\"></td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style=\"padding:20px 32px;text-align:center;\">
            <p style=\"margin:0 0 6px;font-size:12px;color:#999999;font-family:Arial,sans-serif;\">Je ontvangt deze mail omdat je een job alert hebt ingesteld op Onlinemarketingjobs.nl.</p>
            <p style=\"margin:0;font-size:12px;font-family:Arial,sans-serif;\">
              <a href=\"{$unsubscribe_url}\" style=\"color:#7C5CFA;\">Afmelden voor job alerts</a>
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
 * Verwerk afmeldverzoeken via ?sj_unsubscribe={token}
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
        ? 'Je bent succesvol afgemeld voor job alerts van Onlinemarketingjobs.nl.'
        : 'Afmeldlink niet herkend. Mogelijk ben je al afgemeld.';

    wp_die(
        '<div style="font-family:Arial,sans-serif;max-width:480px;margin:80px auto;text-align:center;">'
        . '<p style="font-size:16px;color:#333;">' . esc_html($message) . '</p>'
        . '<a href="' . esc_url(home_url('/')) . '" style="color:#7C5CFA;font-size:14px;">Terug naar de homepage</a>'
        . '</div>',
        'Afmelden job alerts',
        ['response' => 200]
    );
});
