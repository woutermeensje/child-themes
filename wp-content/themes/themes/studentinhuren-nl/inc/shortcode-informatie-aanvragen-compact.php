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
        $bericht_raw = sanitize_textarea_field(wp_unslash($_POST['iac_bericht'] ?? ''));
        $bericht     = wpautop($bericht_raw);

        if (!si_rich_text_has_content($bericht)) {
            $bericht = '';
        }

        if (!$voornaam)        $errors[] = 'Vul je voornaam in.';
        if (!$achternaam)      $errors[] = 'Vul je achternaam in.';
        if (!is_email($email)) $errors[] = 'Vul een geldig e-mailadres in.';

        if (empty($errors)) {
            $submission_id = sanitize_text_field($_POST['si_submission_id'] ?? '');

            if (si_is_duplicate_form_submission('informatie_aanvragen_compact', [
                'submission_id' => $submission_id,
                'voornaam'      => $voornaam,
                'achternaam'    => $achternaam,
                'email'         => strtolower($email),
                'telefoon'      => $telefoon,
                'bericht'       => wp_strip_all_tags($bericht),
            ])) {
                si_redirect_or_fallback(home_url('/bedankt-informatie-aanvraag/'));
            }

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
                update_post_meta($post_id, '_si_bericht',    $bericht);
            }

            // ── E-mailnotificatie ────────────────────────────────
            $body = si_build_admin_email(
                "Informatieaanvraag van $voornaam $achternaam",
                'Er is een nieuwe informatieaanvraag binnengekomen via het compacte formulier op Studentinhuren.nl.',
                [
                    ['label' => 'Naam', 'value' => "$voornaam $achternaam"],
                    ['label' => 'E-mail', 'value' => $email, 'type' => 'email'],
                    ['label' => 'Telefoon', 'value' => $telefoon, 'type' => 'tel'],
                ],
                'Bericht',
                $bericht,
                (int) $post_id
            );

            $mail_sent = wp_mail(
                si_admin_notification_recipients(),
                "Informatieaanvraag van $voornaam $achternaam",
                $body,
                si_admin_mail_headers($email)
            );

            if (!$mail_sent) {
                error_log('[SI formulier] Notificatie compacte informatieaanvraag kon niet worden verzonden.');
            }

            si_ac_subscribe_contact_to_list(STUDENTINHUREN_AC_AANVRAGEN_LIST_ID, [
                'email'      => $email,
                'first_name' => $voornaam,
                'last_name'  => $achternaam,
                'phone'      => $telefoon,
            ]);

            si_redirect_or_fallback(home_url('/bedankt-informatie-aanvraag/'));
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
        <h2 class="si-iac__title">Informatie aanvragen</h2>
        <form method="post" class="si-iac__form" novalidate>
            <?php wp_nonce_field('si_informatie_aanvragen_compact', 'si_iac_nonce'); ?>
            <input type="hidden" name="si_submission_id" value="<?php echo esc_attr($_POST['si_submission_id'] ?? wp_generate_uuid4()); ?>">

            <div class="si-iac__field">
                <label class="si-iac__label" for="iac_voornaam">Voornaam <span class="si-iac__req">*</span></label>
                <input type="text" name="iac_voornaam" id="iac_voornaam" class="si-iac__input"
                       value="<?php echo esc_attr($_POST['iac_voornaam'] ?? ''); ?>" required>
            </div>

            <div class="si-iac__field">
                <label class="si-iac__label" for="iac_achternaam">Achternaam <span class="si-iac__req">*</span></label>
                <input type="text" name="iac_achternaam" id="iac_achternaam" class="si-iac__input"
                       value="<?php echo esc_attr($_POST['iac_achternaam'] ?? ''); ?>" required>
            </div>

            <div class="si-iac__field">
                <label class="si-iac__label" for="iac_email">E-mailadres <span class="si-iac__req">*</span></label>
                <input type="email" name="iac_email" id="iac_email" class="si-iac__input"
                       value="<?php echo esc_attr($_POST['iac_email'] ?? ''); ?>" required>
            </div>

            <div class="si-iac__field">
                <label class="si-iac__label" for="iac_telefoon">Telefoonnummer</label>
                <input type="tel" name="iac_telefoon" id="iac_telefoon" class="si-iac__input"
                       value="<?php echo esc_attr($_POST['iac_telefoon'] ?? ''); ?>">
            </div>

            <div class="si-iac__field">
                <label class="si-iac__label" for="iac_bericht">Bericht</label>
                <textarea name="iac_bericht" id="iac_bericht" class="si-iac__input si-iac__textarea"><?php echo esc_textarea(wp_unslash($_POST['iac_bericht'] ?? '')); ?></textarea>
            </div>

            <div class="si-iac__footer">
                <button type="submit" class="si-iac__submit">Aanvraag versturen</button>
            </div>

        </form>
    </div>
    <?php
    return ob_get_clean();
}
