<?php
if (!defined('ABSPATH')) exit;

/**
 * Verwerk het vacature-plaatsen formulier vroeg genoeg voor wp_redirect.
 */
add_action('template_redirect', function () {
    if (
        $_SERVER['REQUEST_METHOD'] !== 'POST' ||
        empty($_POST['rn_vp_nonce']) ||
        !wp_verify_nonce($_POST['rn_vp_nonce'], 'rn_vacature_plaatsen')
    ) {
        return;
    }

    $voornaam     = sanitize_text_field($_POST['first_name']    ?? '');
    $achternaam   = sanitize_text_field($_POST['last_name']     ?? '');
    $pakket       = sanitize_text_field($_POST['package']       ?? '');
    $bedrijfsnaam = sanitize_text_field($_POST['company_name']  ?? '');
    $locatie      = sanitize_text_field($_POST['location']      ?? '');
    $titel        = sanitize_text_field($_POST['title']         ?? '');
    $omschrijving = wp_kses_post($_POST['job_description']      ?? '');
    $extra_info   = wp_kses_post($_POST['additional_info']      ?? '');
    $referral     = sanitize_text_field($_POST['referral']      ?? '');
    $email        = sanitize_email($_POST['email']              ?? '');

    $attachments = [];
    if (!empty($_FILES['company_logo']['tmp_name'])) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $upload = media_handle_upload('company_logo', 0);
        if (!is_wp_error($upload)) {
            $path = get_attached_file($upload);
            if ($path && file_exists($path)) $attachments[] = $path;
        }
    }

    $message  = "Nieuwe vacature ingediend via Recruiternext.nl\n\n";
    $message .= "Functietitel: {$titel}\n";
    $message .= "Voornaam: {$voornaam}\n";
    $message .= "Achternaam: {$achternaam}\n";
    $message .= "Pakket: {$pakket}\n";
    $message .= "Bedrijfsnaam: {$bedrijfsnaam}\n";
    $message .= "Locatie: {$locatie}\n";
    $message .= "E-mailadres: {$email}\n";
    $message .= "Hoe gevonden: {$referral}\n\n";
    $message .= "--- Vacaturetekst ---\n" . strip_tags($omschrijving) . "\n\n";
    $message .= "--- Aanvullende informatie ---\n" . strip_tags($extra_info) . "\n";

    wp_mail(
        'info@recruiternext.nl',
        'Nieuwe vacature ingediend via het formulier',
        $message,
        [
            'Content-Type: text/plain; charset=UTF-8',
            "Reply-To: {$voornaam} {$achternaam} <{$email}>",
        ],
        $attachments
    );

    wp_redirect(home_url('/vacature-geplaatst/'));
    exit;
});

/**
 * Shortcode: [rn-vacature-plaatsen]
 */
add_shortcode('rn-vacature-plaatsen', function (): string {
    ob_start();
    ?>
    <div class="rn-vp-wrapper">
        <div class="rn-vp-card">
            <h2 class="rn-vp-card__title">Vacature plaatsen</h2>
            <p class="rn-vp-card__intro">
                Vul de gegevens in voor je vacature. Je ontvangt pas een factuur per e-mail nadat de vacature is gepubliceerd.<br>
                <strong>Geen tijd?</strong> Stuur de vacaturetekst als link, PDF of Word-bestand naar
                <a href="mailto:info@recruiternext.nl">info@recruiternext.nl</a>.
            </p>

            <form method="post" action="" enctype="multipart/form-data" novalidate class="rn-vp-form">
                <?php wp_nonce_field('rn_vacature_plaatsen', 'rn_vp_nonce'); ?>

                <!-- Pakket -->
                <div class="rn-vp-section-title">Kies een pakket</div>
                <div class="rn-vp-grid rn-vp-grid--full">
                    <div class="rn-vp-field">
                        <label for="rn_vp_package">Pakket</label>
                        <select name="package" id="rn_vp_package" required>
                            <option value="">Selecteer een pakket</option>
                            <option value="Basis">Basis listing — gratis</option>
                            <option value="Standaard">Standaard listing — €195</option>
                            <option value="Premium">Uitgelichte listing — €295</option>
                        </select>
                    </div>
                </div>

                <!-- Contactgegevens -->
                <div class="rn-vp-section-title">Contactgegevens</div>
                <div class="rn-vp-grid">
                    <div class="rn-vp-field">
                        <label for="rn_vp_first_name">Voornaam</label>
                        <input type="text" name="first_name" id="rn_vp_first_name" required>
                    </div>
                    <div class="rn-vp-field">
                        <label for="rn_vp_last_name">Achternaam</label>
                        <input type="text" name="last_name" id="rn_vp_last_name" required>
                    </div>
                    <div class="rn-vp-field">
                        <label for="rn_vp_company_name">Bedrijfsnaam</label>
                        <input type="text" name="company_name" id="rn_vp_company_name" required>
                    </div>
                    <div class="rn-vp-field">
                        <label for="rn_vp_email">E-mailadres</label>
                        <input type="email" name="email" id="rn_vp_email" required>
                    </div>
                </div>

                <!-- Vacature-informatie -->
                <div class="rn-vp-section-title">Vacature-informatie</div>
                <div class="rn-vp-grid rn-vp-grid--full">
                    <div class="rn-vp-field">
                        <label for="rn_vp_title">Functietitel</label>
                        <input type="text" name="title" id="rn_vp_title" required>
                    </div>
                    <div class="rn-vp-field">
                        <label for="rn_vp_location">Locatie</label>
                        <input type="text" name="location" id="rn_vp_location" required>
                    </div>
                    <div class="rn-vp-field">
                        <label for="rn_vp_job_description">Vacaturetekst</label>
                        <textarea name="job_description" id="rn_vp_job_description" rows="10" class="rn-vp-textarea" placeholder="Beschrijf de vacature…"></textarea>
                    </div>
                    <div class="rn-vp-field">
                        <label for="rn_vp_additional_info">Aanvullende informatie</label>
                        <textarea name="additional_info" id="rn_vp_additional_info" rows="5" class="rn-vp-textarea" placeholder="Bijv. arbeidsvoorwaarden, sollicitatieprocedure…"></textarea>
                    </div>
                    <div class="rn-vp-field">
                        <label for="rn_vp_company_logo">Bedrijfslogo</label>
                        <input type="file" name="company_logo" id="rn_vp_company_logo" accept="image/*" class="rn-vp-file">
                        <span class="rn-vp-hint">PNG, JPG of SVG. Max. 5 MB.</span>
                    </div>
                </div>

                <!-- Hoe gevonden -->
                <div class="rn-vp-section-title">Hoe heb je ons gevonden?</div>
                <div class="rn-vp-grid rn-vp-grid--full">
                    <div class="rn-vp-field">
                        <label for="rn_vp_referral">Hoe ben je bij Recruiternext.nl terechtgekomen?</label>
                        <input type="text" name="referral" id="rn_vp_referral" placeholder="Bijv. Google, via een collega, LinkedIn…">
                    </div>
                </div>

                <button type="submit" class="rn-vp-submit">Vacature indienen</button>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
});
