<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [sj-nieuwsbrief]
 */
add_shortcode('sj-nieuwsbrief', 'sj_nieuwsbrief_shortcode');

function sj_nieuwsbrief_shortcode(): string {
    global $wpdb;
    $table  = $wpdb->prefix . 'sj_newsletter';
    $errors = [];
    $success = false;

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['sj_nl_nonce']) &&
        wp_verify_nonce($_POST['sj_nl_nonce'], 'sj_nieuwsbrief')
    ) {
        $voornaam = sanitize_text_field($_POST['voornaam'] ?? '');
        $email    = sanitize_email($_POST['email']    ?? '');

        if (!$voornaam)        $errors[] = 'Vul je voornaam in.';
        if (!is_email($email)) $errors[] = 'Vul een geldig e-mailadres in.';

        if (empty($errors)) {
            $token    = wp_generate_password(32, false, false);
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table} WHERE email = %s", $email
            ));

            if ($existing) {
                $wpdb->update(
                    $table,
                    ['voornaam' => $voornaam, 'active' => 1],
                    ['email'    => $email],
                    ['%s', '%d'],
                    ['%s']
                );
            } else {
                $wpdb->insert(
                    $table,
                    ['voornaam' => $voornaam, 'email' => $email, 'unsubscribe_token' => $token, 'active' => 1],
                    ['%s', '%s', '%s', '%d']
                );
            }

            // ActiveCampaign: toevoegen aan nieuwsbrief-lijst (ID 10)
            if (function_exists('sj_ac_request')) {
                $result = sj_ac_request('POST', 'contact/sync', [
                    'contact' => ['email' => $email, 'firstName' => $voornaam],
                ]);
                if (!empty($result['contact']['id'])) {
                    sj_ac_request('POST', 'contactLists', [
                        'contactList' => [
                            'list'    => 10,
                            'contact' => (int) $result['contact']['id'],
                            'status'  => 1,
                        ],
                    ]);
                }
            }

            // Admin-notificatie
            $admin_email = get_option('admin_email');
            $admin_body  = "Nieuwe nieuwsbrief aanmelding op Sustainablejobs.nl\n\n";
            $admin_body .= "Naam:   {$voornaam}\n";
            $admin_body .= "E-mail: {$email}\n";
            wp_mail($admin_email, 'Nieuwe nieuwsbrief aanmelding', $admin_body);

            $success = true;
        }
    }

    ob_start();

    if ($success): ?>

    <div class="sj-ja-notice sj-ja-notice--success">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M173.66,98.34a8,8,0,0,1,0,11.32l-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35A8,8,0,0,1,173.66,98.34ZM232,128A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"/></svg>
        <div>
            <strong>Je bent aangemeld!</strong>
            <p>Je ontvangt elke week de nieuwste duurzame vacatures in je inbox.</p>
        </div>
    </div>

    <?php else: ?>

    <?php if (!empty($errors)): ?>
    <div class="sj-ja-notice sj-ja-notice--error">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M236.8,188.09,149.35,36.22a24.76,24.76,0,0,0-42.7,0L19.2,188.09a23.51,23.51,0,0,0,0,23.72A24.35,24.35,0,0,0,40.55,224h174.9a24.35,24.35,0,0,0,21.33-12.19A23.51,23.51,0,0,0,236.8,188.09ZM120,104a8,8,0,0,1,16,0v40a8,8,0,0,1-16,0Zm8,88a12,12,0,1,1,12-12A12,12,0,0,1,128,192Z"/></svg>
        <div>
            <strong>Controleer de volgende velden:</strong>
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?php echo esc_html($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <div class="sj-ja">
        <div class="sj-ja__block">
            <header class="sj-ja__header">
                <h2 class="sj-ja__title">Wekelijkse vacaturenieuwsbrief</h2>
                <p class="sj-ja__subtitle">Ontvang elke week de nieuwste duurzame vacatures direct in je inbox.</p>
            </header>

            <form method="post" class="sj-ja__form" novalidate>
                <?php wp_nonce_field('sj_nieuwsbrief', 'sj_nl_nonce'); ?>

                <div class="sj-ja__fields">
                    <div class="sj-ja__field">
                        <label class="sj-ja__label" for="sj_nl_voornaam">Voornaam <span class="sj-ja__req">*</span></label>
                        <div class="sj-ja__input-wrap">
                            <svg class="sj-ja__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#168AAD" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <input type="text" name="voornaam" id="sj_nl_voornaam" class="sj-ja__input"
                                   value="<?php echo esc_attr($_POST['voornaam'] ?? ''); ?>"
                                   placeholder="Voornaam" required>
                        </div>
                    </div>

                    <div class="sj-ja__field">
                        <label class="sj-ja__label" for="sj_nl_email">E-mailadres <span class="sj-ja__req">*</span></label>
                        <div class="sj-ja__input-wrap">
                            <svg class="sj-ja__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#168AAD" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            <input type="email" name="email" id="sj_nl_email" class="sj-ja__input"
                                   value="<?php echo esc_attr($_POST['email'] ?? ''); ?>"
                                   placeholder="E-mailadres" required>
                        </div>
                    </div>
                </div>

                <footer class="sj-ja__footer">
                    <button type="submit" class="sj-ja__submit">Aanmelden</button>
                </footer>
            </form>
        </div>
    </div>

    <?php endif;
    return ob_get_clean();
}
