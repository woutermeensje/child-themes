<?php
if (!defined('ABSPATH')) exit;

/**
 * Maak de nieuwsbrief-abonneetabel aan.
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
 * Plan de wekelijkse nieuwsbrief-cron (maandag 08:00).
 */
add_action('init', function () {
    if (!wp_next_scheduled('sj_newsletter_weekly')) {
        wp_schedule_event(strtotime('next monday 08:00:00'), 'weekly', 'sj_newsletter_weekly');
    }
});

add_action('after_switch_theme', function () {
    if (!wp_next_scheduled('sj_newsletter_weekly')) {
        wp_schedule_event(strtotime('next monday 08:00:00'), 'weekly', 'sj_newsletter_weekly');
    }
});

add_action('sj_newsletter_weekly', 'sj_send_weekly_newsletter');

/**
 * Verstuur de wekelijkse nieuwsbrief naar alle actieve abonnees.
 */
function sj_send_weekly_newsletter(): void {
    global $wpdb;
    $table       = $wpdb->prefix . 'sj_newsletter';
    $subscribers = $wpdb->get_results("SELECT * FROM {$table} WHERE active = 1", ARRAY_A);

    if (empty($subscribers)) return;

    $vacatures = sj_get_all_new_vacatures();
    if (empty($vacatures)) return;

    foreach ($subscribers as $subscriber) {
        $token = $subscriber['unsubscribe_token'] ?: wp_generate_password(32, false, false);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: Sustainablejobs.nl <support@sustainablejobs.nl>',
        ];

        $sent = wp_mail(
            $subscriber['email'],
            'De nieuwste duurzame vacatures van deze week — Sustainablejobs.nl',
            sj_build_newsletter_email($subscriber['voornaam'], $vacatures, $token),
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
 * Haal alle vacatures op die de afgelopen 7 dagen zijn gepubliceerd.
 */
function sj_get_all_new_vacatures(int $max = 15): array {
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
 * Bouw de HTML nieuwsbrief-e-mail op.
 */
function sj_build_newsletter_email(string $voornaam, array $vacatures, string $unsubscribe_token): string {
    $unsubscribe_url = add_query_arg('sj_nl_unsubscribe', rawurlencode($unsubscribe_token), home_url('/'));
    $alle_vacatures  = esc_url(home_url('/vacatures/'));
    $aanhef          = $voornaam ? "Hoi {$voornaam}!" : 'Hoi!';

    $vacature_rows = '';
    foreach ($vacatures as $post) {
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

        $vacature_rows .= "
        <tr>
          <td style=\"padding:18px 0;border-bottom:1px solid #e4ede9;\">
            <a href=\"{$link}\" style=\"font-size:16px;font-weight:700;color:#168AAD;text-decoration:none;font-family:Arial,sans-serif;\">{$title}</a>
            {$meta_html}
            <div style=\"margin-top:12px;\">
              <a href=\"{$link}\" style=\"display:inline-block;padding:8px 18px;background:#168AAD;color:#ffffff;font-size:13px;font-weight:600;border-radius:4px;text-decoration:none;font-family:Arial,sans-serif;\">Bekijk vacature &rarr;</a>
            </div>
          </td>
        </tr>";
    }

    $week_nr   = date('W');
    $jaar      = date('Y');

    return "<!DOCTYPE html>
<html lang=\"nl\">
<head>
  <meta charset=\"UTF-8\">
  <meta name=\"viewport\" content=\"width=device-width,initial-scale=1.0\">
  <title>Vacaturenieuwsbrief week {$week_nr} — Sustainablejobs.nl</title>
</head>
<body style=\"margin:0;padding:0;background:#f2f6f4;font-family:Arial,sans-serif;\">
<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#f2f6f4;padding:32px 16px;\">
  <tr>
    <td align=\"center\">
      <table width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #dceae4;\">

        <!-- Header -->
        <tr>
          <td style=\"background:#168AAD;padding:24px 32px;\">
            <span style=\"font-family:Arial,sans-serif;font-size:20px;font-weight:700;color:#ffffff;\">Sustainablejobs.nl</span>
            <span style=\"display:block;font-family:Arial,sans-serif;font-size:13px;color:rgba(255,255,255,0.75);margin-top:4px;\">Vacaturenieuwsbrief &mdash; week {$week_nr}, {$jaar}</span>
          </td>
        </tr>

        <!-- Intro -->
        <tr>
          <td style=\"padding:28px 32px 8px;\">
            <p style=\"margin:0 0 10px;font-size:20px;font-weight:700;color:#168AAD;font-family:Arial,sans-serif;\">{$aanhef}</p>
            <p style=\"margin:0;font-size:15px;color:#444444;line-height:1.65;font-family:Arial,sans-serif;\">Dit zijn de nieuwste duurzame vacatures van deze week. Ontdek waar jij het verschil kunt maken.</p>
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
            <a href=\"{$alle_vacatures}\" style=\"display:inline-block;padding:13px 28px;background:#168AAD;color:#ffffff;font-size:15px;font-weight:700;border-radius:6px;text-decoration:none;font-family:Arial,sans-serif;\">Bekijk alle vacatures</a>
          </td>
        </tr>

        <!-- Divider -->
        <tr>
          <td style=\"padding:0 32px;\"><hr style=\"border:none;border-top:1px solid #e4ede9;margin:0;\"></td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style=\"background:#f2f6f4;padding:20px 32px;text-align:center;\">
            <p style=\"margin:0 0 6px;font-size:12px;color:#999999;font-family:Arial,sans-serif;\">Je ontvangt deze nieuwsbrief omdat je je hebt aangemeld op Sustainablejobs.nl.</p>
            <p style=\"margin:0;font-size:12px;font-family:Arial,sans-serif;\">
              <a href=\"{$unsubscribe_url}\" style=\"color:#168AAD;\">Afmelden voor de nieuwsbrief</a>
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
 * Verwerk afmeldverzoeken via ?sj_nl_unsubscribe={token}
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
        ? 'Je bent succesvol afgemeld voor de nieuwsbrief van Sustainablejobs.nl.'
        : 'Afmeldlink niet herkend. Mogelijk ben je al afgemeld.';

    wp_die(
        '<div style="font-family:Arial,sans-serif;max-width:480px;margin:80px auto;text-align:center;">'
        . '<p style="font-size:16px;color:#333;">' . esc_html($message) . '</p>'
        . '<a href="' . esc_url(home_url('/')) . '" style="color:#168AAD;font-size:14px;">Terug naar de homepage</a>'
        . '</div>',
        'Afmelden nieuwsbrief',
        ['response' => 200]
    );
});
