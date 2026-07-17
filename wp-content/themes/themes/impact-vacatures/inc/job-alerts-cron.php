<?php
if (!defined('ABSPATH')) exit;

/**
 * Voeg wekelijks interval toe aan WP Cron.
 */
add_filter('cron_schedules', function (array $schedules): array {
    if (!isset($schedules['fn_weekly'])) {
        $schedules['fn_weekly'] = [
            'interval' => 7 * DAY_IN_SECONDS,
            'display'  => 'Eén keer per week (Impact Vacatures)',
        ];
    }
    return $schedules;
});

/**
 * Plan de wekelijkse job alert cron (maandag 08:00).
 */
add_action('init', function () {
    if (!wp_next_scheduled('fn_job_alerts_weekly')) {
        wp_schedule_event(strtotime('next monday 08:00:00'), 'fn_weekly', 'fn_job_alerts_weekly');
    }
});

add_action('after_switch_theme', function () {
    if (!wp_next_scheduled('fn_job_alerts_weekly')) {
        wp_schedule_event(strtotime('next monday 08:00:00'), 'fn_weekly', 'fn_job_alerts_weekly');
    }
});

add_action('fn_job_alerts_weekly', 'fn_send_weekly_job_alerts');

/**
 * Verstuur wekelijkse job alerts naar alle actieve abonnees.
 */
function fn_send_weekly_job_alerts(): void {
    global $wpdb;
    $table       = $wpdb->prefix . 'fn_job_alerts';
    $subscribers = $wpdb->get_results("SELECT * FROM {$table} WHERE active = 1", ARRAY_A);

    if (empty($subscribers)) return;

    foreach ($subscribers as $subscriber) {
        $sectors    = json_decode($subscriber['sectors']    ?? '[]', true) ?: [];
        $work_types = json_decode($subscriber['work_types'] ?? '[]', true) ?: [];
        $location   = $subscriber['location'] ?? '';

        if (empty($sectors)) continue;

        $vacatures = fn_get_vacatures_for_subscriber($sectors, $work_types, $location);
        if (empty($vacatures)) continue;

        $token = $subscriber['unsubscribe_token'] ?: wp_generate_password(32, false, false);

        $sent = wp_mail(
            $subscriber['email'],
            'Nieuwe vacatures in jouw vakgebied — Impact Vacatures',
            fn_build_job_alert_email($subscriber['voornaam'], $vacatures, $token),
            [
                'Content-Type: text/html; charset=UTF-8',
                'From: Impact Vacatures <nieuwsbrief@impactvacatures.nl>',
            ]
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
 * Haal vacatures op voor een abonnee op basis van sector, werktype en locatie.
 */
function fn_get_vacatures_for_subscriber(array $sectors, array $work_types = [], string $location = ''): array {
    $args = [
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
                'terms'    => $sectors,
                'operator' => 'IN',
            ],
        ],
    ];

    if (!empty($work_types)) {
        $args['tax_query'][] = [
            'taxonomy' => 'job_listing_type',
            'field'    => 'slug',
            'terms'    => $work_types,
            'operator' => 'IN',
        ];
        $args['tax_query']['relation'] = 'AND';
    }

    if ($location) {
        $args['meta_query'] = [
            [
                'key'     => '_job_location',
                'value'   => $location,
                'compare' => 'LIKE',
            ],
        ];
    }

    $query = new WP_Query($args);
    return $query->posts ?: [];
}

/**
 * Haal alle vacatures op van de afgelopen 7 dagen (voor testmail in admin).
 */
function fn_get_recent_vacatures_for_alert(int $max = 10): array {
    $query = new WP_Query([
        'post_type'      => 'job_listing',
        'post_status'    => 'publish',
        'posts_per_page' => $max,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'date_query'     => [
            ['after' => '7 days ago', 'inclusive' => true],
        ],
    ]);
    return $query->posts ?: [];
}

/**
 * Bouw de HTML e-mail op voor een job alert.
 */
function fn_build_job_alert_email(string $voornaam, array $vacatures, string $unsubscribe_token): string {
    $unsubscribe_url = add_query_arg('fn_unsubscribe', rawurlencode($unsubscribe_token), home_url('/'));
    $alle_vacatures  = esc_url(home_url('/vacatures/'));

    $vacature_rows = '';
    foreach ($vacatures as $post) {
        $title    = esc_html(get_the_title($post));
        $link     = esc_url(get_permalink($post));
        $company  = esc_html(get_post_meta($post->ID, '_company_name', true));
        $location = esc_html(get_post_meta($post->ID, '_job_location',  true));
        $terms    = wp_get_post_terms($post->ID, 'job_sector', ['fields' => 'names']);
        $sector   = !empty($terms) && !is_wp_error($terms) ? esc_html(implode(', ', $terms)) : '';

        $meta      = array_filter([$company, $location, $sector]);
        $meta_html = $meta
            ? '<div style="margin-top:4px;font-size:13px;color:#666;">' . implode(' &nbsp;·&nbsp; ', $meta) . '</div>'
            : '';

        $vacature_rows .= "
        <tr>
          <td style=\"padding:18px 0;border-bottom:1px solid #E0E0E0;\">
            <a href=\"{$link}\" style=\"font-size:16px;font-weight:700;color:#0884CC;text-decoration:none;font-family:Arial,sans-serif;\">{$title}</a>
            {$meta_html}
            <div style=\"margin-top:12px;\">
              <a href=\"{$link}\" style=\"display:inline-block;padding:8px 18px;background:#FF8C2C;color:#ffffff;font-size:13px;font-weight:600;border-radius:4px;text-decoration:none;font-family:Arial,sans-serif;\">Bekijk vacature &rarr;</a>
            </div>
          </td>
        </tr>";
    }

    return "<!DOCTYPE html>
<html lang=\"nl\">
<head>
  <meta charset=\"UTF-8\">
  <meta name=\"viewport\" content=\"width=device-width,initial-scale=1.0\">
  <title>Job Alert — Impact Vacatures</title>
</head>
<body style=\"margin:0;padding:0;background:#f5f5f5;font-family:Arial,sans-serif;\">
<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#f5f5f5;padding:32px 16px;\">
  <tr>
    <td align=\"center\">
      <table width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #E0E0E0;\">

        <tr>
          <td style=\"background:#0884CC;padding:24px 32px;\">
            <span style=\"font-family:Arial,sans-serif;font-size:20px;font-weight:700;color:#ffffff;letter-spacing:-0.3px;\">Impact Vacatures</span>
          </td>
        </tr>

        <tr>
          <td style=\"padding:28px 32px 8px;\">
            <p style=\"margin:0 0 10px;font-size:20px;font-weight:700;color:#0884CC;font-family:Arial,sans-serif;\">Hoi {$voornaam}!</p>
            <p style=\"margin:0;font-size:15px;color:#333333;line-height:1.65;font-family:Arial,sans-serif;\">Er staan nieuwe vacatures klaar in jouw vakgebied. Bekijk ze hieronder en reageer snel &mdash; de beste vacatures gaan snel!</p>
          </td>
        </tr>

        <tr>
          <td style=\"padding:8px 32px 24px;\">
            <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">
              {$vacature_rows}
            </table>
          </td>
        </tr>

        <tr>
          <td style=\"padding:0 32px 32px;text-align:center;\">
            <a href=\"{$alle_vacatures}\" style=\"display:inline-block;padding:13px 28px;background:#FF8C2C;color:#ffffff;font-size:15px;font-weight:700;border-radius:6px;text-decoration:none;font-family:Arial,sans-serif;\">Bekijk alle vacatures</a>
          </td>
        </tr>

        <tr>
          <td style=\"padding:0 32px;\"><hr style=\"border:none;border-top:1px solid #E0E0E0;margin:0;\"></td>
        </tr>

        <tr>
          <td style=\"background:#f5f5f5;padding:20px 32px;text-align:center;\">
            <p style=\"margin:0 0 6px;font-size:12px;color:#999999;font-family:Arial,sans-serif;\">Je ontvangt deze mail omdat je een job alert hebt ingesteld op Impact Vacatures.</p>
            <p style=\"margin:0;font-size:12px;font-family:Arial,sans-serif;\">
              <a href=\"{$unsubscribe_url}\" style=\"color:#0884CC;\">Afmelden voor job alerts</a>
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
 * Verwerk afmeldverzoeken via ?fn_unsubscribe={token}
 */
add_action('init', function () {
    if (empty($_GET['fn_unsubscribe'])) return;

    global $wpdb;
    $token   = sanitize_text_field($_GET['fn_unsubscribe']);
    $table   = $wpdb->prefix . 'fn_job_alerts';
    $updated = $wpdb->update(
        $table,
        ['active' => 0],
        ['unsubscribe_token' => $token],
        ['%d'],
        ['%s']
    );

    $message = $updated
        ? 'Je bent succesvol afgemeld voor job alerts van Impact Vacatures.'
        : 'Afmeldlink niet herkend. Mogelijk ben je al afgemeld.';

    wp_die(
        '<div style="font-family:Arial,sans-serif;max-width:480px;margin:80px auto;text-align:center;">'
        . '<p style="font-size:16px;color:#333;">' . esc_html($message) . '</p>'
        . '<a href="' . esc_url(home_url('/')) . '" style="color:#0884CC;font-size:14px;">Terug naar de homepage</a>'
        . '</div>',
        'Afmelden job alerts',
        ['response' => 200]
    );
});
