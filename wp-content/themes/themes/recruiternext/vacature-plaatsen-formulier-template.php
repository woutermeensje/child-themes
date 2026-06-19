<?php
/**
 * Template Name: Vacature Plaatsen Formulier
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rn_vp_nonce'])) {
    if (!wp_verify_nonce($_POST['rn_vp_nonce'], 'rn_vacature_plaatsen')) {
        wp_die('Ongeldige beveiligingstoken.', 'Fout', ['response' => 403]);
    }

    $voornaam       = sanitize_text_field($_POST['first_name']       ?? '');
    $achternaam     = sanitize_text_field($_POST['last_name']         ?? '');
    $pakket         = sanitize_text_field($_POST['package']           ?? '');
    $bedrijfsnaam   = sanitize_text_field($_POST['company_name']      ?? '');
    $locatie        = sanitize_text_field($_POST['location']          ?? '');
    $titel          = sanitize_text_field($_POST['title']             ?? '');
    $omschrijving   = wp_kses_post($_POST['job_description']          ?? '');
    $extra_info     = wp_kses_post($_POST['additional_info']          ?? '');
    $referral       = sanitize_text_field($_POST['referral']          ?? '');
    $email          = sanitize_email($_POST['email']                  ?? '');

    $attachments = [];
    if (!empty($_FILES['company_logo']['tmp_name'])) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $upload = media_handle_upload('company_logo', 0);
        if (!is_wp_error($upload)) {
            $path = get_attached_file($upload);
            if (file_exists($path)) $attachments[] = $path;
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

    $to      = 'info@recruiternext.nl';
    $subject = 'Nieuwe vacature ingediend via het formulier';
    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        "Reply-To: {$voornaam} {$achternaam} <{$email}>",
    ];

    wp_mail($to, $subject, $message, $headers, $attachments);

    wp_redirect(home_url('/vacature-geplaatst/'));
    exit;
}

get_header();
?>

<style>
.rn-vp-wrapper {
    max-width: 720px;
    margin: 48px auto 64px;
    padding: 0 20px;
    box-sizing: border-box;
}

.rn-vp-card {
    background: #fff;
    border: 1px solid #DEDEDE;
    border-radius: 5px;
    padding: 40px 36px;
    box-shadow: 0 4px 24px rgba(0,0,0,.06);
}

.rn-vp-card h1 {
    font-family: 'Balgin-Bold', serif;
    font-size: 26px;
    font-weight: 700;
    color: var(--color-primary, #007BA7);
    margin: 0 0 8px;
    line-height: 1.2;
}

.rn-vp-card__intro {
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    font-weight: 400;
    color: #555;
    line-height: 1.65;
    margin: 0 0 28px;
    padding-bottom: 24px;
    border-bottom: 1px solid #DEDEDE;
}

.rn-vp-card__intro a {
    color: var(--color-primary, #007BA7);
    text-decoration: underline;
}

.rn-vp-section-title {
    font-family: 'Balgin-Bold', serif;
    font-size: 17px;
    font-weight: 700;
    color: var(--color-primary, #007BA7);
    margin: 28px 0 16px;
    padding-bottom: 8px;
    border-bottom: 1px solid #DEDEDE;
}

.rn-vp-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.rn-vp-grid--full {
    grid-template-columns: 1fr;
}

.rn-vp-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.rn-vp-field label {
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    font-weight: 400;
    color: #333;
    margin: 0;
}

.rn-vp-field input[type="text"],
.rn-vp-field input[type="email"],
.rn-vp-field input[type="url"],
.rn-vp-field select {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid #DEDEDE;
    border-radius: 5px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    font-weight: 300;
    color: #333;
    background: #fff;
    box-sizing: border-box;
    outline: none;
    transition: border-color .2s ease, box-shadow .2s ease;
}

.rn-vp-field input:focus,
.rn-vp-field select:focus {
    border-color: var(--color-primary, #007BA7);
    box-shadow: 0 0 0 3px rgba(0, 123, 167, .12);
}

.rn-vp-field input[type="file"] {
    padding: 6px 0;
    border: none;
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    color: #555;
}

.rn-vp-field .rn-vp-hint {
    font-family: 'Poppins', sans-serif;
    font-size: 12px;
    color: #888;
}

.rn-vp-editor-wrap {
    border: 1px solid #DEDEDE;
    border-radius: 5px;
    overflow: hidden;
}

.rn-vp-submit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    margin-top: 28px;
    padding: 13px 28px;
    background: var(--color-primary, #007BA7);
    border: 2px solid var(--color-primary, #007BA7);
    border-radius: 5px;
    font-family: 'Balgin-Bold', serif;
    font-size: 16px;
    font-weight: 700;
    color: #ffffff;
    cursor: pointer;
    transition: background .18s ease, border-color .18s ease;
}

.rn-vp-submit:hover {
    background: var(--color-primary-dk, #005f82);
    border-color: var(--color-primary-dk, #005f82);
}

@media (max-width: 600px) {
    .rn-vp-card { padding: 28px 20px; }
    .rn-vp-grid { grid-template-columns: 1fr; }
}
</style>

<div class="rn-vp-wrapper">
    <div class="rn-vp-card">
        <h1>Vacature plaatsen</h1>
        <p class="rn-vp-card__intro">
            Vul de gegevens in voor je vacature. Je ontvangt pas een factuur per e-mail nadat de vacature is gepubliceerd.<br>
            <strong>Geen tijd?</strong> Stuur de vacaturetekst als link, PDF of Word-bestand naar
            <a href="mailto:info@recruiternext.nl">info@recruiternext.nl</a>.
        </p>

        <form method="post" action="" enctype="multipart/form-data" novalidate>
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
            <div class="rn-vp-grid rn-vp-grid--full" style="gap:16px;">
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
                    <div class="rn-vp-editor-wrap">
                        <?php wp_editor('', 'job_description', [
                            'textarea_name' => 'job_description',
                            'media_buttons' => false,
                            'textarea_rows' => 12,
                            'editor_class'  => 'rn-vp-editor',
                        ]); ?>
                    </div>
                </div>
                <div class="rn-vp-field">
                    <label for="rn_vp_additional_info">Aanvullende informatie</label>
                    <div class="rn-vp-editor-wrap">
                        <?php wp_editor('', 'additional_info', [
                            'textarea_name' => 'additional_info',
                            'media_buttons' => false,
                            'textarea_rows' => 6,
                            'editor_class'  => 'rn-vp-editor',
                        ]); ?>
                    </div>
                </div>
                <div class="rn-vp-field">
                    <label for="rn_vp_company_logo">Bedrijfslogo</label>
                    <input type="file" name="company_logo" id="rn_vp_company_logo" accept="image/*">
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

<?php get_footer(); ?>
