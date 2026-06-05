<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {
    add_management_page(
        'Fondsen — Job Alerts & Nieuwsbrief',
        'Fondsen — Mailings',
        'manage_options',
        'fn-newsletter',
        'fn_newsletter_admin_page'
    );
});

function fn_newsletter_admin_handle_actions(): ?string {
    if (!current_user_can('manage_options')) return null;
    if (empty($_POST['fn_nl_action']) || !check_admin_referer('fn_nl_admin_action')) return null;

    $action = sanitize_key($_POST['fn_nl_action']);

    if ($action === 'test_job_alert') {
        $email    = sanitize_email($_POST['ja_test_email'] ?? '');
        $voornaam = sanitize_text_field($_POST['ja_test_voornaam'] ?? 'Test');
        $sectors  = array_map('sanitize_title', (array) ($_POST['ja_test_sectors'] ?? []));

        if (!is_email($email)) return '<div class="notice notice-error"><p>Vul een geldig e-mailadres in.</p></div>';
        if (empty($sectors))   return '<div class="notice notice-error"><p>Selecteer minimaal één sector.</p></div>';

        $vacatures = fn_get_vacatures_for_subscriber($sectors);
        if (empty($vacatures)) {
            return '<div class="notice notice-warning"><p>Geen vacatures gevonden in de afgelopen 7 dagen voor de gekozen sectoren.</p></div>';
        }

        $token = 'test-fn-ja-' . wp_generate_password(8, false, false);
        $sent  = wp_mail(
            $email,
            '[TEST] Nieuwe vacatures in jouw vakgebied — Fondsen.org',
            fn_build_job_alert_email($voornaam, $vacatures, $token),
            ['Content-Type: text/html; charset=UTF-8', 'From: Fondsen.org <nieuwsbrief@fondsen.org>']
        );

        return $sent
            ? '<div class="notice notice-success"><p>Test job alert verstuurd naar <strong>' . esc_html($email) . '</strong> met ' . count($vacatures) . ' vacature(s).</p></div>'
            : '<div class="notice notice-error"><p>Verzenden mislukt. Controleer de SMTP-instellingen.</p></div>';
    }

    if ($action === 'run_job_alerts') {
        fn_send_weekly_job_alerts();
        return '<div class="notice notice-success"><p>Wekelijkse job alerts handmatig verstuurd naar alle actieve abonnees.</p></div>';
    }

    if ($action === 'test_newsletter') {
        $email    = sanitize_email($_POST['nl_test_email'] ?? '');
        $voornaam = sanitize_text_field($_POST['nl_test_voornaam'] ?? 'Test');

        if (!is_email($email)) return '<div class="notice notice-error"><p>Vul een geldig e-mailadres in.</p></div>';

        $vacatures = fn_get_recent_vacatures();
        if (empty($vacatures)) {
            return '<div class="notice notice-warning"><p>Geen vacatures gevonden in de afgelopen 14 dagen.</p></div>';
        }

        $token   = 'test-fn-token-' . wp_generate_password(8, false, false);
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: Fondsen.org <nieuwsbrief@fondsen.org>',
        ];

        $body = fn_build_newsletter_email_preview($voornaam, $vacatures, $token);

        $sent = wp_mail(
            $email,
            '[TEST] De nieuwste vacatures van de afgelopen twee weken — Fondsen.org',
            $body,
            $headers
        );

        return $sent
            ? '<div class="notice notice-success"><p>Test-nieuwsbrief verstuurd naar <strong>' . esc_html($email) . '</strong> met ' . count($vacatures) . ' vacature(s).</p></div>'
            : '<div class="notice notice-error"><p>Verzenden mislukt. Controleer de SMTP-instellingen.</p></div>';
    }

    if ($action === 'run_newsletter') {
        fn_send_biweekly_newsletter();
        return '<div class="notice notice-success"><p>Nieuwsbrief handmatig verstuurd via ActiveCampaign.</p></div>';
    }

    return null;
}

/**
 * Preview-versie van de e-mail (zonder AC merge tags) voor testemails via wp_mail.
 */
function fn_build_newsletter_email_preview(string $voornaam, array $vacatures, string $token): string {
    $unsubscribe_url = add_query_arg('fn_nl_unsubscribe', rawurlencode($token), home_url('/'));
    $alle_vacatures  = esc_url(home_url('/vacatures/'));
    $week_nr         = date('W');
    $jaar            = date('Y');

    $vacature_rows = '';
    foreach ($vacatures as $post) {
        $title     = esc_html(get_the_title($post));
        $link      = esc_url(get_permalink($post));
        $company   = esc_html(get_post_meta($post->ID, '_company_name', true));
        $location  = esc_html(get_post_meta($post->ID, '_job_location', true));
        $meta      = array_filter([$company, $location]);
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
<head><meta charset=\"UTF-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1.0\"></head>
<body style=\"margin:0;padding:0;background:#f5f5f5;font-family:Arial,sans-serif;\">
<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#f5f5f5;padding:32px 16px;\">
  <tr><td align=\"center\">
    <table width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #E0E0E0;\">
      <tr><td style=\"background:#0884CC;padding:24px 32px;\">
        <span style=\"font-family:Arial,sans-serif;font-size:20px;font-weight:700;color:#ffffff;\">Fondsen.org</span>
        <span style=\"display:block;font-family:Arial,sans-serif;font-size:13px;color:rgba(255,255,255,0.75);margin-top:4px;\">Vacaturenieuwsbrief &mdash; week {$week_nr}, {$jaar}</span>
      </td></tr>
      <tr><td style=\"padding:28px 32px 8px;\">
        <p style=\"margin:0 0 10px;font-size:20px;font-weight:700;color:#0884CC;font-family:Arial,sans-serif;\">Hoi {$voornaam}!</p>
        <p style=\"margin:0;font-size:15px;color:#333333;line-height:1.65;font-family:Arial,sans-serif;\">Dit zijn de nieuwste vacatures van de afgelopen twee weken op Fondsen.org.</p>
      </td></tr>
      <tr><td style=\"padding:8px 32px 24px;\">
        <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">{$vacature_rows}</table>
      </td></tr>
      <tr><td style=\"padding:0 32px 32px;text-align:center;\">
        <a href=\"{$alle_vacatures}\" style=\"display:inline-block;padding:13px 28px;background:#FF8C2C;color:#ffffff;font-size:15px;font-weight:700;border-radius:6px;text-decoration:none;font-family:Arial,sans-serif;\">Bekijk alle vacatures</a>
      </td></tr>
      <tr><td style=\"padding:0 32px;\"><hr style=\"border:none;border-top:1px solid #E0E0E0;margin:0;\"></td></tr>
      <tr><td style=\"background:#f5f5f5;padding:20px 32px;text-align:center;\">
        <p style=\"margin:0 0 6px;font-size:12px;color:#999999;font-family:Arial,sans-serif;\">Je ontvangt deze nieuwsbrief omdat je je hebt aangemeld op Fondsen.org.</p>
        <p style=\"margin:0;font-size:12px;font-family:Arial,sans-serif;\"><a href=\"{$unsubscribe_url}\" style=\"color:#0884CC;\">Afmelden</a></p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>";
}

function fn_newsletter_admin_page(): void {
    global $wpdb;
    $nl_table    = $wpdb->prefix . 'fn_newsletter';
    $ja_table    = $wpdb->prefix . 'fn_job_alerts';
    $message     = fn_newsletter_admin_handle_actions();

    $nl_subscribers = $wpdb->get_results("SELECT * FROM {$nl_table} ORDER BY created_at DESC", ARRAY_A) ?: [];
    $ja_subscribers = $wpdb->get_results("SELECT * FROM {$ja_table} ORDER BY created_at DESC", ARRAY_A) ?: [];
    $next_nl_cron   = wp_next_scheduled('fn_newsletter_biweekly');
    $next_ja_cron   = wp_next_scheduled('fn_job_alerts_weekly');
    $ac_list_id     = defined('FONDSEN_AC_NEWSLETTER_LIST_ID') ? (int) FONDSEN_AC_NEWSLETTER_LIST_ID : 8;
    $sector_terms   = get_terms(['taxonomy' => 'job_sector', 'hide_empty' => false, 'orderby' => 'name']) ?: [];
    ?>
    <div class="wrap">
        <h1>Fondsen.org — Job Alerts &amp; Nieuwsbrief</h1>
        <?php echo $message; ?>

        <!-- ===== JOB ALERTS ===== -->
        <h2 style="margin-top:24px;">Job Alerts</h2>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:16px;align-items:start;">

            <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;">
                <h3 style="margin-top:0;">Test job alert versturen</h3>
                <p style="color:#555;font-size:13px;">Stuur een testmail op basis van gekozen sectoren.</p>
                <form method="post">
                    <?php wp_nonce_field('fn_nl_admin_action'); ?>
                    <input type="hidden" name="fn_nl_action" value="test_job_alert">
                    <table class="form-table" style="margin:0;">
                        <tr>
                            <th style="width:120px;padding:8px 0;"><label for="ja_test_voornaam">Voornaam</label></th>
                            <td style="padding:4px 0;"><input type="text" id="ja_test_voornaam" name="ja_test_voornaam" value="Test" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th style="padding:8px 0;"><label for="ja_test_email">E-mailadres</label></th>
                            <td style="padding:4px 0;"><input type="email" id="ja_test_email" name="ja_test_email" placeholder="jouw@email.nl" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th style="padding:8px 0;vertical-align:top;">Sectoren</th>
                            <td style="padding:4px 0;">
                                <div style="max-height:180px;overflow-y:auto;border:1px solid #ddd;border-radius:3px;padding:8px;background:#fafafa;">
                                    <?php foreach ($sector_terms as $term): ?>
                                    <label style="display:flex;align-items:center;gap:6px;padding:3px 0;font-size:13px;cursor:pointer;">
                                        <input type="checkbox" name="ja_test_sectors[]" value="<?php echo esc_attr($term->slug); ?>">
                                        <?php echo esc_html($term->name); ?>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <p style="margin-top:16px;">
                        <button type="submit" class="button button-primary">Test job alert versturen</button>
                    </p>
                </form>
            </div>

            <div>
                <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;margin-bottom:20px;">
                    <h3 style="margin-top:0;">Cron status</h3>
                    <table style="font-size:13px;border-collapse:collapse;width:100%;">
                        <tr>
                            <td style="padding:5px 0;color:#555;width:160px;">Frequentie</td>
                            <td style="padding:5px 0;font-weight:600;">Wekelijks (maandag 08:00)</td>
                        </tr>
                        <tr>
                            <td style="padding:5px 0;color:#555;">Volgende verzending</td>
                            <td style="padding:5px 0;font-weight:600;">
                                <?php echo $next_ja_cron
                                    ? esc_html(wp_date('D d M Y \o\m H:i', $next_ja_cron))
                                    : '<span style="color:#d63638;">Niet gepland</span>'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:5px 0;color:#555;">Actieve abonnees</td>
                            <td style="padding:5px 0;font-weight:600;"><?php echo (int) $wpdb->get_var("SELECT COUNT(*) FROM {$ja_table} WHERE active = 1"); ?></td>
                        </tr>
                        <tr>
                            <td style="padding:5px 0;color:#555;">Totaal inschrijvingen</td>
                            <td style="padding:5px 0;font-weight:600;"><?php echo (int) $wpdb->get_var("SELECT COUNT(*) FROM {$ja_table}"); ?></td>
                        </tr>
                    </table>
                </div>
                <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;">
                    <h3 style="margin-top:0;">Volledige verzending uitvoeren</h3>
                    <p style="color:#555;font-size:13px;">Stuurt de wekelijkse job alert naar alle actieve abonnees die nieuwe vacatures hebben in hun sectoren.</p>
                    <form method="post" onsubmit="return confirm('Job alerts nu versturen naar alle actieve abonnees?');">
                        <?php wp_nonce_field('fn_nl_admin_action'); ?>
                        <input type="hidden" name="fn_nl_action" value="run_job_alerts">
                        <button type="submit" class="button button-secondary">Nu uitvoeren voor alle abonnees</button>
                    </form>
                </div>
            </div>
        </div>

        <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;margin-top:24px;">
            <h3 style="margin-top:0;">Job Alert abonnees (<?php echo count($ja_subscribers); ?>)</h3>
            <?php if (empty($ja_subscribers)): ?>
                <p style="color:#888;">Nog geen abonnees.</p>
            <?php else: ?>
            <table class="widefat striped" style="font-size:13px;">
                <thead>
                    <tr>
                        <th>Naam</th><th>E-mailadres</th><th>Sectoren</th><th>Locatie</th><th>Werktype</th><th>Aangemeld</th><th>Laatste mail</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ja_subscribers as $sub):
                        $sectors    = json_decode($sub['sectors']    ?? '[]', true) ?: [];
                        $work_types = json_decode($sub['work_types'] ?? '[]', true) ?: [];
                        $sec_names  = array_filter(array_map(function($slug) {
                            $t = get_term_by('slug', $slug, 'job_sector'); return $t ? $t->name : null;
                        }, $sectors));
                        $wt_names   = array_filter(array_map(function($slug) {
                            $t = get_term_by('slug', $slug, 'job_listing_type'); return $t ? $t->name : null;
                        }, $work_types));
                    ?>
                    <tr>
                        <td><?php echo esc_html($sub['voornaam']); ?></td>
                        <td><?php echo esc_html($sub['email']); ?></td>
                        <td><?php echo esc_html(implode(', ', $sec_names) ?: '—'); ?></td>
                        <td><?php echo esc_html($sub['location'] ?: '—'); ?></td>
                        <td><?php echo esc_html(implode(', ', $wt_names) ?: 'Alle'); ?></td>
                        <td><?php echo esc_html(wp_date('d M Y', strtotime($sub['created_at']))); ?></td>
                        <td><?php echo $sub['last_sent'] ? esc_html(wp_date('d M Y', strtotime($sub['last_sent']))) : '—'; ?></td>
                        <td><?php echo $sub['active']
                            ? '<span style="color:#00a32a;font-weight:600;">Actief</span>'
                            : '<span style="color:#d63638;">Afgemeld</span>'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <hr style="margin:32px 0;border:none;border-top:1px solid #c3c4c7;">

        <!-- ===== NIEUWSBRIEF ===== -->
        <h2>Nieuwsbrief</h2>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:20px;align-items:start;">

            <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;">
                <h2 style="margin-top:0;">Test-nieuwsbrief versturen</h2>
                <p style="color:#555;font-size:13px;">Stuur een preview rechtstreeks via WordPress (niet via ActiveCampaign).</p>
                <form method="post">
                    <?php wp_nonce_field('fn_nl_admin_action'); ?>
                    <input type="hidden" name="fn_nl_action" value="test_newsletter">
                    <table class="form-table" style="margin:0;">
                        <tr>
                            <th style="width:120px;padding:8px 0;"><label for="nl_test_voornaam">Voornaam</label></th>
                            <td style="padding:4px 0;"><input type="text" id="nl_test_voornaam" name="nl_test_voornaam" value="Test" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th style="padding:8px 0;"><label for="nl_test_email">E-mailadres</label></th>
                            <td style="padding:4px 0;"><input type="email" id="nl_test_email" name="nl_test_email" placeholder="jouw@email.nl" class="regular-text" required></td>
                        </tr>
                    </table>
                    <p style="margin-top:16px;">
                        <button type="submit" class="button button-primary">Test-nieuwsbrief versturen</button>
                    </p>
                </form>
            </div>

            <div>
                <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;margin-bottom:20px;">
                    <h2 style="margin-top:0;">Cron status</h2>
                    <table style="font-size:13px;border-collapse:collapse;width:100%;">
                        <tr>
                            <td style="padding:5px 0;color:#555;width:160px;">Frequentie</td>
                            <td style="padding:5px 0;font-weight:600;">Elke 2 weken (donderdag 14:00)</td>
                        </tr>
                        <tr>
                            <td style="padding:5px 0;color:#555;">Volgende verzending</td>
                            <td style="padding:5px 0;font-weight:600;">
                                <?php echo $next_nl_cron
                                    ? esc_html(wp_date('D d M Y \o\m H:i', $next_nl_cron))
                                    : '<span style="color:#d63638;">Niet gepland</span>'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:5px 0;color:#555;">AC lijst ID</td>
                            <td style="padding:5px 0;font-weight:600;"><?php echo $ac_list_id ? esc_html($ac_list_id) : '<span style="color:#d63638;">Niet ingesteld</span>'; ?></td>
                        </tr>
                        <tr>
                            <td style="padding:5px 0;color:#555;">Actieve abonnees</td>
                            <td style="padding:5px 0;font-weight:600;"><?php echo (int) $wpdb->get_var("SELECT COUNT(*) FROM {$nl_table} WHERE active = 1"); ?></td>
                        </tr>
                        <tr>
                            <td style="padding:5px 0;color:#555;">Totaal inschrijvingen</td>
                            <td style="padding:5px 0;font-weight:600;"><?php echo (int) $wpdb->get_var("SELECT COUNT(*) FROM {$nl_table}"); ?></td>
                        </tr>
                    </table>
                </div>

                <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;">
                    <h2 style="margin-top:0;">Volledige verzending uitvoeren</h2>
                    <p style="color:#555;font-size:13px;">Stuurt de nieuwsbrief direct via ActiveCampaign naar alle abonnees op de gekoppelde lijst.</p>
                    <form method="post" onsubmit="return confirm('Weet je zeker dat je de nieuwsbrief nu wilt versturen via ActiveCampaign?');">
                        <?php wp_nonce_field('fn_nl_admin_action'); ?>
                        <input type="hidden" name="fn_nl_action" value="run_newsletter">
                        <button type="submit" class="button button-secondary">Nu uitvoeren via ActiveCampaign</button>
                    </form>
                </div>
            </div>

        </div>

        <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;margin-top:24px;">
            <h2 style="margin-top:0;">Nieuwsbrief abonnees (<?php echo count($nl_subscribers); ?>)</h2>
            <?php if (empty($nl_subscribers)): ?>
                <p style="color:#888;">Nog geen abonnees.</p>
            <?php else: ?>
            <table class="widefat striped" style="font-size:13px;">
                <thead>
                    <tr>
                        <th>Naam</th>
                        <th>E-mailadres</th>
                        <th>Aangemeld</th>
                        <th>Laatste mail</th>
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
                                <span style="color:#00a32a;font-weight:600;">Actief</span>
                            <?php else: ?>
                                <span style="color:#d63638;">Afgemeld</span>
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
