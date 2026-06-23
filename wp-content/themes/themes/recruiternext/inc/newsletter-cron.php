<?php
if (!defined('ABSPATH')) exit;

/**
 * Maak de nieuwsbrief-abonneetabel aan.
 */
function rn_newsletter_create_table(): void {
    global $wpdb;
    $table   = $wpdb->prefix . 'rn_newsletter';
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
    if (!get_option('rn_newsletter_table_v1')) {
        rn_newsletter_create_table();
        update_option('rn_newsletter_table_v1', true);
    }
});

/**
 * Voeg tweewekelijks interval toe aan WP Cron.
 */
add_filter('cron_schedules', function (array $schedules): array {
    if (!isset($schedules['rn_biweekly'])) {
        $schedules['rn_biweekly'] = [
            'interval' => 14 * DAY_IN_SECONDS,
            'display'  => 'Eén keer per twee weken',
        ];
    }
    return $schedules;
});

add_action('init', function () {
    if (!get_option('rn_newsletter_scheduled_v1')) {
        wp_schedule_event(strtotime('next thursday 14:00:00'), 'rn_biweekly', 'rn_newsletter_weekly');
        update_option('rn_newsletter_scheduled_v1', true);
    } elseif (!wp_next_scheduled('rn_newsletter_weekly')) {
        wp_schedule_event(strtotime('next thursday 14:00:00'), 'rn_biweekly', 'rn_newsletter_weekly');
    }
});

add_action('after_switch_theme', function () {
    if (!wp_next_scheduled('rn_newsletter_weekly')) {
        wp_schedule_event(strtotime('next thursday 14:00:00'), 'rn_biweekly', 'rn_newsletter_weekly');
    }
});

add_action('rn_newsletter_weekly', 'rn_send_weekly_newsletter');

/**
 * Verstuur de nieuwsbrief via ActiveCampaign.
 */
function rn_send_weekly_newsletter(): void {
    $vacatures = rn_get_all_new_vacatures();

    if (empty($vacatures)) {
        error_log('[RN Newsletter] Geen nieuwe vacatures, verzending overgeslagen.');
        return;
    }

    rn_ac_send_newsletter_campaign($vacatures);
}

/**
 * Haal vacatures op die de afgelopen 14 dagen zijn gepubliceerd.
 */
function rn_get_all_new_vacatures(int $max = 15): array {
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
 * Bouw de HTML nieuwsbrief-e-mail op voor directe wp_mail verzending.
 */
function rn_build_newsletter_email(string $voornaam, array $vacatures, string $unsubscribe_token): string {
    $unsubscribe_url = add_query_arg('rn_nl_unsubscribe', rawurlencode($unsubscribe_token), home_url('/'));
    $alle_vacatures  = esc_url(home_url('/vacatures/'));
    $aanhef          = $voornaam ? "Hoi {$voornaam}!" : 'Hoi!';
    $week_nr         = date('W');
    $jaar            = date('Y');

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
          <td style=\"padding:18px 0;border-bottom:1px solid #e0eaf0;\">
            <a href=\"{$link}\" style=\"font-size:16px;font-weight:700;color:#0458ab;text-decoration:none;font-family:Arial,sans-serif;\">{$title}</a>
            {$meta_html}
            <div style=\"margin-top:12px;\">
              <a href=\"{$link}\" style=\"display:inline-block;padding:8px 18px;background:#0458ab;color:#ffffff;font-size:13px;font-weight:600;border-radius:4px;text-decoration:none;font-family:Arial,sans-serif;\">Bekijk vacature &rarr;</a>
            </div>
          </td>
        </tr>";
    }

    return "<!DOCTYPE html>
<html lang=\"nl\">
<head>
  <meta charset=\"UTF-8\">
  <meta name=\"viewport\" content=\"width=device-width,initial-scale=1.0\">
  <title>Vacaturenieuwsbrief week {$week_nr} — Recruiternext.nl</title>
</head>
<body style=\"margin:0;padding:0;background:#f5f7fa;font-family:Arial,sans-serif;\">
<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#f5f7fa;padding:32px 16px;\">
  <tr>
    <td align=\"center\">
      <table width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #d0e4ec;\">
        <tr>
          <td style=\"background:#0458ab;padding:24px 32px;\">
            <span style=\"font-family:Arial,sans-serif;font-size:20px;font-weight:700;color:#ffffff;\">Recruiternext.nl</span>
            <span style=\"display:block;font-family:Arial,sans-serif;font-size:13px;color:rgba(255,255,255,0.75);margin-top:4px;\">Vacaturenieuwsbrief &mdash; week {$week_nr}, {$jaar}</span>
          </td>
        </tr>
        <tr>
          <td style=\"padding:28px 32px 8px;\">
            <p style=\"margin:0 0 10px;font-size:20px;font-weight:700;color:#0458ab;font-family:Arial,sans-serif;\">{$aanhef}</p>
            <p style=\"margin:0;font-size:15px;color:#444444;line-height:1.65;font-family:Arial,sans-serif;\">Dit zijn de nieuwste vacatures van deze week. Ontdek jouw volgende carrièrestap.</p>
          </td>
        </tr>
        <tr>
          <td style=\"padding:8px 32px 24px;\">
            <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">{$vacature_rows}</table>
          </td>
        </tr>
        <tr>
          <td style=\"padding:0 32px 32px;text-align:center;\">
            <a href=\"{$alle_vacatures}\" style=\"display:inline-block;padding:13px 28px;background:#0458ab;color:#ffffff;font-size:15px;font-weight:700;border-radius:6px;text-decoration:none;font-family:Arial,sans-serif;\">Bekijk alle vacatures</a>
          </td>
        </tr>
        <tr>
          <td style=\"padding:0 32px;\"><hr style=\"border:none;border-top:1px solid #e0eaf0;margin:0;\"></td>
        </tr>
        <tr>
          <td style=\"background:#f5f7fa;padding:20px 32px;text-align:center;\">
            <p style=\"margin:0 0 6px;font-size:12px;color:#999999;font-family:Arial,sans-serif;\">Je ontvangt deze nieuwsbrief omdat je je hebt aangemeld op Recruiternext.nl.</p>
            <p style=\"margin:0;font-size:12px;font-family:Arial,sans-serif;\">
              <a href=\"{$unsubscribe_url}\" style=\"color:#0458ab;\">Afmelden voor de nieuwsbrief</a>
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
 * Verwerk afmeldverzoeken via ?rn_nl_unsubscribe={token}
 */
add_action('init', function () {
    if (empty($_GET['rn_nl_unsubscribe'])) return;

    global $wpdb;
    $token   = sanitize_text_field($_GET['rn_nl_unsubscribe']);
    $table   = $wpdb->prefix . 'rn_newsletter';
    $updated = $wpdb->update(
        $table,
        ['active' => 0],
        ['unsubscribe_token' => $token],
        ['%d'],
        ['%s']
    );

    $message = $updated
        ? 'Je bent succesvol afgemeld voor de nieuwsbrief van Recruiternext.nl.'
        : 'Afmeldlink niet herkend. Mogelijk ben je al afgemeld.';

    wp_die(
        '<div style="font-family:Arial,sans-serif;max-width:480px;margin:80px auto;text-align:center;">'
        . '<p style="font-size:16px;color:#333;">' . esc_html($message) . '</p>'
        . '<a href="' . esc_url(home_url('/')) . '" style="color:#0458ab;font-size:14px;">Terug naar de homepage</a>'
        . '</div>',
        'Afmelden nieuwsbrief',
        ['response' => 200]
    );
});
