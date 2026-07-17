<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [fondsen-nieuwsbrief]
 */
foreach (['fondsen-nieuwsbrief', 'fondsen_nieuwsbrief', 'sj-nieuwsbrief'] as $tag) {
    add_shortcode($tag, 'fn_nieuwsbrief_shortcode');
}

function fn_nieuwsbrief_shortcode(): string {
    global $wpdb;
    $table  = $wpdb->prefix . 'fn_newsletter';
    $errors = [];
    $success = false;

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['fn_nl_nonce']) &&
        wp_verify_nonce($_POST['fn_nl_nonce'], 'fn_nieuwsbrief')
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
            $saved = false;

            if ($existing) {
                $updated = $wpdb->update(
                    $table,
                    ['voornaam' => $voornaam, 'active' => 1],
                    ['email'    => $email],
                    ['%s', '%d'],
                    ['%s']
                );
                $saved = ($updated !== false);
            } else {
                $inserted = $wpdb->insert(
                    $table,
                    ['voornaam' => $voornaam, 'email' => $email, 'unsubscribe_token' => $token, 'active' => 1],
                    ['%s', '%s', '%s', '%d']
                );
                $saved = ($inserted !== false);
            }

            if (!$saved) {
                error_log('[Impact Vacatures Nieuwsbrief] Aanmelding opslaan mislukt: ' . $wpdb->last_error);
                $errors[] = 'Je aanmelding kon niet worden opgeslagen. Probeer het later opnieuw.';
            } else {
                fn_ac_subscribe_newsletter($voornaam, $email);

                $admin_email = get_option('admin_email');
                wp_mail(
                    $admin_email,
                    'Nieuwe nieuwsbrief aanmelding — Impact Vacatures',
                    "Nieuwe aanmelding:\n\nNaam:  {$voornaam}\nE-mail: {$email}"
                );

                $success = true;
            }
        }
    }

    ob_start();

    if ($success): ?>

    <div style="max-width:600px;background:#ffffff;border:1px solid #dedede;border-radius:5px;padding:32px;">
        <div style="background:#E7F4FB;border:1px solid #0884CC;border-radius:5px;padding:20px 24px;display:flex;gap:12px;align-items:flex-start;">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256" fill="#0884CC" aria-hidden="true" style="flex-shrink:0;margin-top:2px;"><path d="M173.66,98.34a8,8,0,0,1,0,11.32l-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35A8,8,0,0,1,173.66,98.34ZM232,128A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"/></svg>
            <div>
                <strong style="color:#0884CC;">Je bent aangemeld!</strong>
                <p style="margin:4px 0 0;color:#333;font-size:14px;">Je ontvangt elke twee weken de nieuwste vacatures op Impact Vacatures. Check ook je spammap.</p>
            </div>
        </div>
    </div>

    <?php else: ?>

    <div class="fn-ja fn-ja__block">

        <h3 class="fn-ja__title">Vacature Nieuwsbrief — Impact Vacatures</h3>
        <p class="fn-ja__subtitle">Ontvang elke twee weken de nieuwste vacatures in je inbox.</p>

        <?php if (!empty($errors)): ?>
        <div style="background:#fff3f3;border:1px solid #d63638;border-radius:5px;padding:16px 20px;margin-bottom:20px;">
            <strong style="color:#d63638;">Controleer de volgende velden:</strong>
            <ul style="margin:8px 0 0;padding-left:18px;color:#333;font-size:14px;">
                <?php foreach ($errors as $e): ?>
                    <li><?php echo esc_html($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <?php wp_nonce_field('fn_nieuwsbrief', 'fn_nl_nonce'); ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label class="fn-nl__label" for="fn_nl_voornaam">Voornaam <span class="fn-ja__req">*</span></label>
                    <input type="text" name="voornaam" id="fn_nl_voornaam"
                           class="fn-ja__input"
                           value="<?php echo esc_attr($_POST['voornaam'] ?? ''); ?>"
                           required>
                </div>
                <div>
                    <label class="fn-nl__label" for="fn_nl_email">E-mailadres <span class="fn-ja__req">*</span></label>
                    <input type="email" name="email" id="fn_nl_email"
                           class="fn-ja__input"
                           value="<?php echo esc_attr($_POST['email'] ?? ''); ?>"
                           required>
                </div>
            </div>

            <button type="submit" class="fn-ja__submit">
                Bevestigen
            </button>
        </form>

    </div>

    <?php endif;

    return ob_get_clean();
}
