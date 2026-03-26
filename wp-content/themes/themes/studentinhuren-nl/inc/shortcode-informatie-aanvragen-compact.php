<?php
if (!defined('ABSPATH')) exit;

/* ============================================================
   SHORTCODE: [si_informatie_aanvragen_compact]
   Compact formulier voor smalle blokken (max ~300px).
   Velden: voornaam, achternaam, email, telefoon — gestapeld.
   ============================================================ */
add_shortcode('si_informatie_aanvragen_compact', 'si_informatie_aanvragen_compact_shortcode');

function si_informatie_aanvragen_compact_shortcode(): string {

    $errors = [];

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['si_iac_nonce']) &&
        wp_verify_nonce($_POST['si_iac_nonce'], 'si_informatie_aanvragen_compact')
    ) {
        $voornaam   = sanitize_text_field($_POST['iac_voornaam']   ?? '');
        $achternaam = sanitize_text_field($_POST['iac_achternaam'] ?? '');
        $email      = sanitize_email($_POST['iac_email']           ?? '');
        $telefoon   = sanitize_text_field($_POST['iac_telefoon']   ?? '');

        if (!$voornaam)        $errors[] = 'Vul je voornaam in.';
        if (!$achternaam)      $errors[] = 'Vul je achternaam in.';
        if (!is_email($email)) $errors[] = 'Vul een geldig e-mailadres in.';

        if (empty($errors)) {

            // ── Opslaan in de database ──────────────────────────
            $post_id = wp_insert_post([
                'post_type'   => 'si_aanvraag',
                'post_title'  => sanitize_text_field("$voornaam $achternaam"),
                'post_status' => 'publish',
                'post_author' => 0,
            ]);

            if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, '_si_voornaam',   $voornaam);
                update_post_meta($post_id, '_si_achternaam', $achternaam);
                update_post_meta($post_id, '_si_email',      $email);
                update_post_meta($post_id, '_si_telefoon',   $telefoon);
            }

            // ── E-mailnotificatie ────────────────────────────────
            $body  = "Nieuwe informatieaanvraag (compact formulier):\n\n";
            $body .= "Naam: $voornaam $achternaam\n";
            $body .= "E-mail: $email\n";
            $body .= "Telefoon: $telefoon\n";

            wp_mail(
                get_option('admin_email'),
                "Informatieaanvraag van $voornaam $achternaam",
                $body,
                [
                    'Content-Type: text/plain; charset=UTF-8',
                    'Reply-To: ' . $email,
                ]
            );

            wp_redirect(home_url('/bedankt-informatie-aanvraag/'));
            exit;
        }
    }

    /* ── HTML ───────────────────────────────────────────────── */
    ob_start();

    if (!empty($errors)): ?>
    <div class="si-iac-notice si-iac-notice--error">
        <strong>Fout:</strong>
        <ul>
            <?php foreach ($errors as $e): ?>
                <li><?php echo esc_html($e); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="si-iac">
        <form method="post" class="si-iac__form" novalidate>
            <?php wp_nonce_field('si_informatie_aanvragen_compact', 'si_iac_nonce'); ?>

            <div class="si-iac__field">
                <label class="si-iac__label" for="iac_voornaam">Voornaam <span class="si-iac__req">*</span></label>
                <input type="text" name="iac_voornaam" id="iac_voornaam" class="si-iac__input"
                       value="<?php echo esc_attr($_POST['iac_voornaam'] ?? ''); ?>" placeholder="Jan" required>
            </div>

            <div class="si-iac__field">
                <label class="si-iac__label" for="iac_achternaam">Achternaam <span class="si-iac__req">*</span></label>
                <input type="text" name="iac_achternaam" id="iac_achternaam" class="si-iac__input"
                       value="<?php echo esc_attr($_POST['iac_achternaam'] ?? ''); ?>" placeholder="de Vries" required>
            </div>

            <div class="si-iac__field">
                <label class="si-iac__label" for="iac_email">E-mailadres <span class="si-iac__req">*</span></label>
                <input type="email" name="iac_email" id="iac_email" class="si-iac__input"
                       value="<?php echo esc_attr($_POST['iac_email'] ?? ''); ?>" placeholder="jan@bedrijf.nl" required>
            </div>

            <div class="si-iac__field">
                <label class="si-iac__label" for="iac_telefoon">Telefoonnummer</label>
                <input type="tel" name="iac_telefoon" id="iac_telefoon" class="si-iac__input"
                       value="<?php echo esc_attr($_POST['iac_telefoon'] ?? ''); ?>" placeholder="+31 6 00000000">
            </div>

            <div class="si-iac__footer">
                <button type="submit" class="si-iac__submit">Aanvraag versturen</button>
            </div>

        </form>
    </div>

    <?php
    return ob_get_clean();
}
