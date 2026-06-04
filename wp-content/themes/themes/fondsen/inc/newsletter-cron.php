<?php
if (!defined('ABSPATH')) exit;

/**
 * Maak de nieuwsbrief-abonneetabel aan.
 */
function fn_newsletter_create_table(): void {
    global $wpdb;
    $table   = $wpdb->prefix . 'fn_newsletter';
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
    if (!get_option('fn_newsletter_table_v1')) {
        fn_newsletter_create_table();
        update_option('fn_newsletter_table_v1', true);
    }
});

/**
 * Voeg tweewekelijks interval toe aan WP Cron.
 */
add_filter('cron_schedules', function (array $schedules): array {
    if (!isset($schedules['fn_biweekly'])) {
        $schedules['fn_biweekly'] = [
            'interval' => 14 * DAY_IN_SECONDS,
            'display'  => 'Eén keer per twee weken (Fondsen)',
        ];
    }
    return $schedules;
});

/**
 * Plan de tweewekelijkse nieuwsbrief-cron (donderdag 14:00).
 */
add_action('init', function () {
    if (!wp_next_scheduled('fn_newsletter_biweekly')) {
        wp_schedule_event(strtotime('next thursday 14:00:00'), 'fn_biweekly', 'fn_newsletter_biweekly');
    }
});

add_action('after_switch_theme', function () {
    if (!wp_next_scheduled('fn_newsletter_biweekly')) {
        wp_schedule_event(strtotime('next thursday 14:00:00'), 'fn_biweekly', 'fn_newsletter_biweekly');
    }
});

add_action('fn_newsletter_biweekly', 'fn_send_biweekly_newsletter');

/**
 * Verstuur de tweewekelijkse nieuwsbrief via ActiveCampaign.
 */
function fn_send_biweekly_newsletter(): void {
    $vacatures = fn_get_recent_vacatures();

    if (empty($vacatures)) {
        error_log('[Fondsen Newsletter] Geen nieuwe vacatures de afgelopen 14 dagen, verzending overgeslagen.');
        return;
    }

    fn_ac_send_newsletter_campaign($vacatures);
}

/**
 * Haal vacatures op die de afgelopen 14 dagen zijn gepubliceerd.
 */
function fn_get_recent_vacatures(int $max = 15): array {
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
 * Verwerk afmeldverzoeken via ?fn_nl_unsubscribe={token}
 */
add_action('init', function () {
    if (empty($_GET['fn_nl_unsubscribe'])) return;

    global $wpdb;
    $token   = sanitize_text_field($_GET['fn_nl_unsubscribe']);
    $table   = $wpdb->prefix . 'fn_newsletter';
    $updated = $wpdb->update(
        $table,
        ['active' => 0],
        ['unsubscribe_token' => $token],
        ['%d'],
        ['%s']
    );

    $message = $updated
        ? 'Je bent succesvol afgemeld voor de nieuwsbrief van Fondsen.org.'
        : 'Afmeldlink niet herkend. Mogelijk ben je al afgemeld.';

    wp_die(
        '<div style="font-family:Arial,sans-serif;max-width:480px;margin:80px auto;text-align:center;">'
        . '<p style="font-size:16px;color:#333;">' . esc_html($message) . '</p>'
        . '<a href="' . esc_url(home_url('/')) . '" style="color:#0884CC;font-size:14px;">Terug naar de homepage</a>'
        . '</div>',
        'Afmelden nieuwsbrief',
        ['response' => 200]
    );
});
