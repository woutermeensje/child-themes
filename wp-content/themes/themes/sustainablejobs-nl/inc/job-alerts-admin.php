<?php
if (!defined('ABSPATH')) exit;

/**
 * Registreer de admin-pagina onder Tools.
 */
add_action('admin_menu', function () {
    add_management_page(
        'Job Alerts & Nieuwsbrief',
        'Job Alerts & Nieuwsbrief',
        'manage_options',
        'sj-job-alerts',
        'sj_job_alerts_admin_page'
    );
});

/**
 * Verwerk acties vóór de HTML-output.
 */
function sj_job_alerts_handle_actions(): ?string {
    if (!current_user_can('manage_options')) return null;
    if (empty($_POST['sj_ja_action']) || !check_admin_referer('sj_ja_admin_action')) return null;

    $action = sanitize_key($_POST['sj_ja_action']);

    if ($action === 'test_email') {
        $email    = sanitize_email($_POST['test_email'] ?? '');
        $voornaam = sanitize_text_field($_POST['test_voornaam'] ?? 'Test');
        $sectors  = array_map('sanitize_title', (array) ($_POST['test_sectors'] ?? []));

        if (!is_email($email)) return '<div class="notice notice-error"><p>Vul een geldig e-mailadres in.</p></div>';
        if (empty($sectors))   return '<div class="notice notice-error"><p>Selecteer minimaal één sector.</p></div>';

        $vacatures = sj_get_new_vacatures_for_sectors($sectors);

        if (empty($vacatures)) {
            return '<div class="notice notice-warning"><p>Geen vacatures gevonden in de afgelopen 7 dagen voor de gekozen sectoren. Probeer andere sectoren of voeg eerst een testpublicatie toe.</p></div>';
        }

        $token   = 'test-token-' . wp_generate_password(8, false, false);
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: Sustainablejobs.nl <support@sustainablejobs.nl>',
        ];

        $sent = wp_mail(
            $email,
            '[TEST] Nieuwe vacatures in jouw vakgebied — Sustainablejobs.nl',
            sj_build_job_alert_email($voornaam, $vacatures, $token),
            $headers
        );

        return $sent
            ? '<div class="notice notice-success"><p>Test-e-mail verstuurd naar <strong>' . esc_html($email) . '</strong> met ' . count($vacatures) . ' vacature(s).</p></div>'
            : '<div class="notice notice-error"><p>Verzenden mislukt. Controleer de SMTP-instellingen van WordPress.</p></div>';
    }

    if ($action === 'run_full') {
        sj_send_weekly_job_alerts();
        return '<div class="notice notice-success"><p>Wekelijkse verzending handmatig uitgevoerd. Controleer de inboxen van je abonnees.</p></div>';
    }

    if ($action === 'test_newsletter') {
        $email    = sanitize_email($_POST['nl_test_email'] ?? '');
        $voornaam = sanitize_text_field($_POST['nl_test_voornaam'] ?? 'Test');

        if (!is_email($email)) return '<div class="notice notice-error"><p>Vul een geldig e-mailadres in.</p></div>';

        $vacatures = sj_get_all_new_vacatures();
        if (empty($vacatures)) {
            return '<div class="notice notice-warning"><p>Geen vacatures gevonden in de afgelopen 7 dagen.</p></div>';
        }

        $token   = 'test-nl-token-' . wp_generate_password(8, false, false);
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: Sustainablejobs.nl <support@sustainablejobs.nl>',
        ];

        $sent = wp_mail(
            $email,
            '[TEST] De nieuwste duurzame vacatures van deze week — Sustainablejobs.nl',
            sj_build_newsletter_email($voornaam, $vacatures, $token),
            $headers
        );

        return $sent
            ? '<div class="notice notice-success"><p>Test nieuwsbrief verstuurd naar <strong>' . esc_html($email) . '</strong> met ' . count($vacatures) . ' vacature(s).</p></div>'
            : '<div class="notice notice-error"><p>Verzenden mislukt. Controleer de SMTP-instellingen.</p></div>';
    }

    if ($action === 'run_newsletter') {
        sj_send_weekly_newsletter();
        return '<div class="notice notice-success"><p>Nieuwsbrief handmatig verstuurd naar alle actieve abonnees.</p></div>';
    }

    return null;
}

function sj_get_monthly_signup_counts(string $table, int $year): array {
    global $wpdb;

    $counts = array_fill(1, 12, 0);
    $start  = sprintf('%d-01-01 00:00:00', $year);
    $end    = sprintf('%d-01-01 00:00:00', $year + 1);

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT MONTH(created_at) AS signup_month, COUNT(*) AS signup_count
             FROM {$table}
             WHERE created_at >= %s AND created_at < %s
             GROUP BY signup_month",
            $start,
            $end
        ),
        ARRAY_A
    ) ?: [];

    foreach ($rows as $row) {
        $month = (int) ($row['signup_month'] ?? 0);
        if ($month >= 1 && $month <= 12) {
            $counts[$month] = (int) ($row['signup_count'] ?? 0);
        }
    }

    return $counts;
}

function sj_render_monthly_signup_overview(string $title, array $counts, int $year): void {
    $months = [
        1  => 'januari',
        2  => 'februari',
        3  => 'maart',
        4  => 'april',
        5  => 'mei',
        6  => 'juni',
        7  => 'juli',
        8  => 'augustus',
        9  => 'september',
        10 => 'oktober',
        11 => 'november',
        12 => 'december',
    ];
    ?>
    <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;margin-bottom:20px;">
        <h2 style="margin-top:0;"><?php echo esc_html($title); ?></h2>
        <p style="color:#555;font-size:13px;margin-top:0;">Aantal nieuwe aanmeldingen per maand in <?php echo esc_html((string) $year); ?>.</p>
        <table style="font-size:13px;border-collapse:collapse;width:100%;">
            <?php foreach ($months as $month_number => $month_name): ?>
                <tr>
                    <td style="padding:5px 0;color:#555;width:160px;"><?php echo esc_html($month_name . ':'); ?></td>
                    <td style="padding:5px 0;font-weight:600;"><?php echo esc_html(number_format_i18n((int) ($counts[$month_number] ?? 0))); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php
}

/**
 * Admin-pagina HTML.
 */
function sj_job_alerts_admin_page(): void {
    global $wpdb;
    $table       = $wpdb->prefix . 'sj_job_alerts';
    $nl_table    = $wpdb->prefix . 'sj_newsletter';
    $message     = sj_job_alerts_handle_actions();
    $signup_year = (int) wp_date('Y');

    $subscribers               = $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC", ARRAY_A) ?: [];
    $job_alert_monthly_counts  = sj_get_monthly_signup_counts($table, $signup_year);
    $newsletter_monthly_counts = sj_get_monthly_signup_counts($nl_table, $signup_year);
    $sector_terms              = get_terms(['taxonomy' => 'job_sector', 'hide_empty' => false, 'orderby' => 'name']) ?: [];
    $next_cron                 = wp_next_scheduled('sj_job_alerts_weekly');
    ?>
    <div class="wrap">
        <h1>Job Alerts — beheer & test</h1>
        <?php echo $message; ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:20px;align-items:start;">

            <!-- Test e-mail -->
            <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;">
                <h2 style="margin-top:0;">Test-e-mail versturen</h2>
                <p style="color:#555;font-size:13px;">Stuur een testmail naar een specifiek adres op basis van gekozen sectoren.</p>
                <form method="post">
                    <?php wp_nonce_field('sj_ja_admin_action'); ?>
                    <input type="hidden" name="sj_ja_action" value="test_email">

                    <table class="form-table" style="margin:0;">
                        <tr>
                            <th style="width:120px;padding:8px 0;"><label for="test_voornaam">Voornaam</label></th>
                            <td style="padding:4px 0;"><input type="text" id="test_voornaam" name="test_voornaam" value="Test" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th style="padding:8px 0;"><label for="test_email">E-mailadres</label></th>
                            <td style="padding:4px 0;"><input type="email" id="test_email" name="test_email" placeholder="jouw@email.nl" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th style="padding:8px 0; vertical-align:top;">Sectoren</th>
                            <td style="padding:4px 0;">
                                <div style="max-height:200px;overflow-y:auto;border:1px solid #ddd;border-radius:3px;padding:8px;background:#fafafa;">
                                    <?php foreach ($sector_terms as $term): ?>
                                    <label style="display:flex;align-items:center;gap:6px;padding:3px 0;font-size:13px;cursor:pointer;">
                                        <input type="checkbox" name="test_sectors[]" value="<?php echo esc_attr($term->slug); ?>">
                                        <?php echo esc_html($term->name); ?>
                                    </label>
                                    <?php endforeach; ?>
                                    <?php if (empty($sector_terms)): ?>
                                    <p style="color:#888;font-size:13px;margin:0;">Geen sectoren gevonden.</p>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <p style="margin-top:16px;">
                        <button type="submit" class="button button-primary">Test-e-mail versturen</button>
                    </p>
                </form>
            </div>

            <!-- Status & info -->
            <div>
                <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;margin-bottom:20px;">
                    <h2 style="margin-top:0;">Cron status</h2>
                    <table style="font-size:13px;border-collapse:collapse;width:100%;">
                        <tr>
                            <td style="padding:5px 0;color:#555;width:160px;">Volgende verzending</td>
                            <td style="padding:5px 0;font-weight:600;">
                                <?php echo $next_cron
                                    ? esc_html(wp_date('D d M Y \o\m H:i', $next_cron))
                                    : '<span style="color:#d63638;">Niet gepland</span>'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:5px 0;color:#555;">Actieve abonnees</td>
                            <td style="padding:5px 0;font-weight:600;"><?php echo (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE active = 1"); ?></td>
                        </tr>
                        <tr>
                            <td style="padding:5px 0;color:#555;">Totaal inschrijvingen</td>
                            <td style="padding:5px 0;font-weight:600;"><?php echo (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}"); ?></td>
                        </tr>
                    </table>
                </div>

                <?php sj_render_monthly_signup_overview('Job alert aanmeldingen per maand', $job_alert_monthly_counts, $signup_year); ?>

                <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;">
                    <h2 style="margin-top:0;">Volledige verzending uitvoeren</h2>
                    <p style="color:#555;font-size:13px;">Stuurt de wekelijkse mail direct naar <strong>alle actieve abonnees</strong>. Alleen als er nieuwe vacatures zijn in hun sectoren.</p>
                    <form method="post" onsubmit="return confirm('Weet je zeker dat je de mail naar alle actieve abonnees wilt versturen?');">
                        <?php wp_nonce_field('sj_ja_admin_action'); ?>
                        <input type="hidden" name="sj_ja_action" value="run_full">
                        <button type="submit" class="button button-secondary">Nu uitvoeren voor alle abonnees</button>
                    </form>
                </div>
            </div>

        </div>

        <!-- Abonneelijst -->
        <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;margin-top:24px;">
            <h2 style="margin-top:0;">Abonnees (<?php echo count($subscribers); ?>)</h2>
            <?php if (empty($subscribers)): ?>
                <p style="color:#888;">Nog geen abonnees.</p>
            <?php else: ?>
            <table class="widefat striped" style="font-size:13px;">
                <thead>
                    <tr>
                        <th>Naam</th>
                        <th>E-mailadres</th>
                        <th>Sectoren</th>
                        <th>Aangemeld</th>
                        <th>Laatste mail</th>
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

        <!-- Nieuwsbrief sectie -->
        <hr style="margin:32px 0;border:none;border-top:1px solid #c3c4c7;">
        <h1>Nieuwsbrief — beheer & test</h1>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:20px;align-items:start;">

            <!-- Test nieuwsbrief -->
            <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;">
                <h2 style="margin-top:0;">Test-nieuwsbrief versturen</h2>
                <p style="color:#555;font-size:13px;">Stuur een test-nieuwsbrief met de vacatures van deze week naar een specifiek adres.</p>
                <form method="post">
                    <?php wp_nonce_field('sj_ja_admin_action'); ?>
                    <input type="hidden" name="sj_ja_action" value="test_newsletter">

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

            <!-- Nieuwsbrief status & volledige verzending -->
            <div>
                <?php
                $nl_next = wp_next_scheduled('sj_newsletter_weekly');
                ?>
                <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;margin-bottom:20px;">
                    <h2 style="margin-top:0;">Cron status nieuwsbrief</h2>
                    <table style="font-size:13px;border-collapse:collapse;width:100%;">
                        <tr>
                            <td style="padding:5px 0;color:#555;width:160px;">Volgende verzending</td>
                            <td style="padding:5px 0;font-weight:600;">
                                <?php echo $nl_next
                                    ? esc_html(wp_date('D d M Y \o\m H:i', $nl_next))
                                    : '<span style="color:#d63638;">Niet gepland</span>'; ?>
                            </td>
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

                <?php sj_render_monthly_signup_overview('Nieuwsbrief aanmeldingen per maand', $newsletter_monthly_counts, $signup_year); ?>

                <div style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:20px;">
                    <h2 style="margin-top:0;">Volledige verzending uitvoeren</h2>
                    <p style="color:#555;font-size:13px;">Stuurt de wekelijkse nieuwsbrief direct naar <strong>alle actieve abonnees</strong>.</p>
                    <form method="post" onsubmit="return confirm('Weet je zeker dat je de nieuwsbrief naar alle actieve abonnees wilt versturen?');">
                        <?php wp_nonce_field('sj_ja_admin_action'); ?>
                        <input type="hidden" name="sj_ja_action" value="run_newsletter">
                        <button type="submit" class="button button-secondary">Nu uitvoeren voor alle abonnees</button>
                    </form>
                </div>
            </div>

        </div>

        <!-- Nieuwsbrief abonneelijst -->
        <?php
        $nl_subscribers = $wpdb->get_results("SELECT * FROM {$nl_table} ORDER BY created_at DESC", ARRAY_A) ?: [];
        ?>
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
