<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [sj_vacature_plaatsen]
 * Vacature-plaatsingsformulier met Quill rich text editor.
 */
add_shortcode('sj_vacature_plaatsen', 'sj_vacature_plaatsen_shortcode');

function sj_vacature_plaatsen_shortcode(): string {

    /* ── Verwerking ─────────────────────────────────────────── */
    $success = false;
    $errors  = [];

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['sj_vp_nonce']) &&
        wp_verify_nonce($_POST['sj_vp_nonce'], 'sj_vacature_plaatsen')
    ) {
        $voornaam       = sanitize_text_field($_POST['voornaam']       ?? '');
        $achternaam     = sanitize_text_field($_POST['achternaam']      ?? '');
        $bedrijfsnaam   = sanitize_text_field($_POST['bedrijfsnaam']    ?? '');
        $email          = sanitize_email($_POST['email']                ?? '');
        $pakket         = sanitize_text_field($_POST['pakket']          ?? '');
        $vacaturetitel  = sanitize_text_field($_POST['vacaturetitel']   ?? '');
        $locatie        = sanitize_text_field($_POST['locatie']         ?? '');
        $type_baan      = array_map('sanitize_text_field', (array)($_POST['type_baan'] ?? []));
        $omschrijving   = wp_kses_post($_POST['omschrijving']           ?? '');
        $referral       = sanitize_text_field($_POST['referral']        ?? '');

        if (!$voornaam)       $errors[] = 'Vul je voornaam in.';
        if (!$achternaam)     $errors[] = 'Vul je achternaam in.';
        if (!$bedrijfsnaam)   $errors[] = 'Vul je bedrijfsnaam in.';
        if (!is_email($email)) $errors[] = 'Vul een geldig e-mailadres in.';
        if (!$vacaturetitel)  $errors[] = 'Vul een vacaturetitel in.';
        if (!$omschrijving)   $errors[] = 'Vul een vacatureomschrijving in.';

        if (empty($errors)) {
            $attachments = [];
            $upload = null;
            $upload_featured = null;

            if (
                !empty($_FILES['bedrijfslogo']['tmp_name']) ||
                !empty($_FILES['uitgelichte_afbeelding']['tmp_name'])
            ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';
                require_once ABSPATH . 'wp-admin/includes/image.php';
            }

            if (!empty($_FILES['bedrijfslogo']['tmp_name'])) {
                $upload = media_handle_upload('bedrijfslogo', 0);
                if (!is_wp_error($upload)) {
                    $path = get_attached_file($upload);
                    if ($path && file_exists($path)) {
                        $attachments[] = $path;
                    }
                }
            }

            if (!empty($_FILES['uitgelichte_afbeelding']['tmp_name'])) {
                $upload_featured = media_handle_upload('uitgelichte_afbeelding', 0);
            }

            $type_baan_str = implode(', ', $type_baan);
            $body  = "Nieuwe vacature via het formulier:\n\n";
            $body .= "Pakket: $pakket\n";
            $body .= "Naam: $voornaam $achternaam\n";
            $body .= "Bedrijf: $bedrijfsnaam\n";
            $body .= "E-mail: $email\n";
            $body .= "Vacaturetitel: $vacaturetitel\n";
            $body .= "Locatie: $locatie\n";
            $body .= "Type baan: $type_baan_str\n";
            $body .= "Hoe gevonden: $referral\n\n";
            $body .= "--- Vacature omschrijving ---\n" . strip_tags($omschrijving) . "\n\n";

            $headers = ['Content-Type: text/plain; charset=UTF-8'];

            wp_mail(
                'support@sustainablejobs.nl',
                "Nieuwe vacature: $vacaturetitel",
                $body,
                $headers,
                $attachments
            );

            $confirmation_body  = "Beste $voornaam,\n\n";
            $confirmation_body .= "Bedankt voor het plaatsen van je vacature op Sustainablejobs.nl.\n\n";
            $confirmation_body .= "We hebben je vacature in goede orde ontvangen:\n";
            $confirmation_body .= "Vacaturetitel: $vacaturetitel\n";
            $confirmation_body .= "Bedrijf: $bedrijfsnaam\n";
            $confirmation_body .= "Locatie: $locatie\n";
            $confirmation_body .= "Pakket: $pakket\n\n";
            $confirmation_body .= "Ons team bekijkt je inzending en neemt indien nodig contact met je op. Je ontvangt de factuur per e-mail na publicatie.\n\n";
            $confirmation_body .= "Heb je in de tussentijd vragen? Reageer gerust op deze e-mail of mail naar support@sustainablejobs.nl.\n\n";
            $confirmation_body .= "Met vriendelijke groet,\n";
            $confirmation_body .= "Sustainablejobs.nl";

            wp_mail(
                $email,
                'Bevestiging van je vacatureplaatsing op Sustainablejobs.nl',
                $confirmation_body,
                $headers
            );

            /* ── Sla inzending op als CPT ──────────────────── */
            $post_id = wp_insert_post([
                'post_title'  => sanitize_text_field($vacaturetitel),
                'post_status' => 'pending',
                'post_type'   => 'sj_vacature',
                'post_author' => 0,
            ]);

            if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, '_sj_pakket',       $pakket);
                update_post_meta($post_id, '_sj_voornaam',     $voornaam);
                update_post_meta($post_id, '_sj_achternaam',   $achternaam);
                update_post_meta($post_id, '_sj_bedrijfsnaam', $bedrijfsnaam);
                update_post_meta($post_id, '_sj_email',        $email);
                update_post_meta($post_id, '_sj_locatie',      $locatie);
                update_post_meta($post_id, '_sj_type_baan',    $type_baan);
                update_post_meta($post_id, '_sj_omschrijving', $omschrijving);
                update_post_meta($post_id, '_sj_referral',     $referral);

                if (!empty($upload) && !is_wp_error($upload)) {
                    update_post_meta($post_id, '_sj_logo_id', $upload);
                }

                if (!empty($upload_featured) && !is_wp_error($upload_featured)) {
                    update_post_meta($post_id, '_sj_featured_image_id', $upload_featured);
                }
            }

            /* ── Maak concept job_listing aan in WP Job Manager ── */
            $job_id = wp_insert_post([
                'post_title'   => sanitize_text_field($vacaturetitel),
                'post_content' => $omschrijving,
                'post_status'  => 'draft',
                'post_type'    => 'job_listing',
                'post_author'  => 1,
            ]);

            if ($job_id && !is_wp_error($job_id)) {
                update_post_meta($job_id, '_job_location',    $locatie);
                update_post_meta($job_id, '_company_name',    $bedrijfsnaam);
                update_post_meta($job_id, '_company_email',   $email);
                update_post_meta($job_id, '_job_salary',      '');
                update_post_meta($job_id, '_filled',          0);
                update_post_meta($job_id, '_featured',        0);
                update_post_meta($job_id, '_job_expires',     '');

                // Koppel bedrijfslogo
                if (!empty($upload) && !is_wp_error($upload)) {
                    update_post_meta($job_id, '_company_logo', wp_get_attachment_url($upload));
                }

                // Koppel uitgelichte afbeelding los van het bedrijfslogo.
                if (!empty($upload_featured) && !is_wp_error($upload_featured)) {
                    update_post_meta($job_id, '_cover_image', wp_get_attachment_url($upload_featured));
                }

                // Koppel job types (taxonomie)
                if (!empty($type_baan)) {
                    $term_ids = [];
                    foreach ($type_baan as $type_name) {
                        $term = get_term_by('name', $type_name, 'job_listing_type');
                        if ($term) {
                            $term_ids[] = $term->term_id;
                        }
                    }
                    if (!empty($term_ids)) {
                        wp_set_post_terms($job_id, $term_ids, 'job_listing_type');
                    }
                }

                // Sla pakket op als notitie
                update_post_meta($job_id, '_sj_pakket', $pakket);
            }

            wp_redirect('https://sustainablejobs.nl/bevestiging-vacature-plaatsing/');
            exit;
        }
    }

    /* ── HTML opbouwen ──────────────────────────────────────── */
    ob_start();

    if ($success): ?>

    <div class="sj-vp-notice sj-vp-notice--success">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M173.66,98.34a8,8,0,0,1,0,11.32l-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35A8,8,0,0,1,173.66,98.34ZM232,128A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"/></svg>
        <div>
            <strong>Vacature succesvol verzonden!</strong>
            <p>We nemen zo snel mogelijk contact met je op.</p>
        </div>
    </div>

    <?php else: ?>

    <?php if (!empty($errors)): ?>
    <div class="sj-vp-notice sj-vp-notice--error">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M236.8,188.09,149.35,36.22a24.76,24.76,0,0,0-42.7,0L19.2,188.09a23.51,23.51,0,0,0,0,23.72A24.35,24.35,0,0,0,40.55,224h174.9a24.35,24.35,0,0,0,21.33-12.19A23.51,23.51,0,0,0,236.8,188.09ZM120,104a8,8,0,0,1,16,0v40a8,8,0,0,1-16,0Zm8,88a12,12,0,1,1,12-12A12,12,0,0,1,128,192Z"/></svg>
        <div>
            <strong>Er zijn een paar fouten:</strong>
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?php echo esc_html($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <div class="sj-vp">
        <div class="sj-vp__block">

            <!-- Titel -->
            <header class="sj-vp__header">
                <h2 class="sj-vp__title">Vacature plaatsen</h2>
                <p class="sj-vp__subtitle">Vul de gegevens in en we publiceren je vacature zo snel mogelijk. Je ontvangt een factuur per e-mail na publicatie.</p>
            </header>

            <form method="post" class="sj-vp__form" enctype="multipart/form-data" novalidate>
                <?php wp_nonce_field('sj_vacature_plaatsen', 'sj_vp_nonce'); ?>

                <!-- Pakket -->
                <div class="sj-vp__section">
                    <p class="sj-vp__section-title">Kies je pakket</p>
                    <div class="sj-vp__grid sj-vp__grid--2">
                        <?php
                        $pakketten = [
                            'Standaard Vacature: €275 excl. btw'          => ['label' => 'Standaard', 'prijs' => '€275,00 excl. btw'],
                            'Spotlight Vacature: €375 excl. btw'          => ['label' => 'Spotlight', 'prijs' => '€375,00 excl. btw'],
                            'Stage & Vrijwilligerswerk: Gratis'            => ['label' => 'Stage & Vrijwilligerswerk', 'prijs' => 'Gratis'],
                            'Wij zijn lid van Sustainablejobs.nl: Gratis'  => ['label' => 'Wij zijn lid', 'prijs' => 'Gratis voor leden'],
                            'Wij hebben een strippenkaart'                 => ['label' => 'Strippenkaart', 'prijs' => 'Via strippenkaart'],
                        ];
                        $selected_pakket = $_POST['pakket'] ?? 'Standaard Vacature: €275 excl. btw';
                        foreach ($pakketten as $value => $info): ?>
                        <label class="sj-vp__pakket<?php echo ($selected_pakket === $value) ? ' is-selected' : ''; ?>">
                            <input type="radio" name="pakket" value="<?php echo esc_attr($value); ?>"
                                   <?php checked($selected_pakket, $value); ?> class="sj-vp__pakket-radio">
                            <span class="sj-vp__pakket-inner">
                                <span class="sj-vp__pakket-name"><?php echo esc_html($info['label']); ?></span>
                                <span class="sj-vp__pakket-prijs"><?php echo esc_html($info['prijs']); ?></span>
                            </span>
                            <span class="sj-vp__pakket-check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 256 256" fill="currentColor"><path d="M229.66,77.66l-128,128a8,8,0,0,1-11.32,0l-56-56a8,8,0,0,1,11.32-11.32L96,188.69,218.34,66.34a8,8,0,0,1,11.32,11.32Z"/></svg>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Contactgegevens -->
                <div class="sj-vp__section">
                    <p class="sj-vp__section-title">Contactgegevens</p>
                    <div class="sj-vp__grid sj-vp__grid--2">
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_voornaam">Voornaam <span class="sj-vp__req">*</span></label>
                            <input type="text" name="voornaam" id="sj_voornaam" class="sj-vp__input"
                                   value="<?php echo esc_attr($_POST['voornaam'] ?? ''); ?>" required>
                        </div>
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_achternaam">Achternaam <span class="sj-vp__req">*</span></label>
                            <input type="text" name="achternaam" id="sj_achternaam" class="sj-vp__input"
                                   value="<?php echo esc_attr($_POST['achternaam'] ?? ''); ?>" required>
                        </div>
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_bedrijfsnaam">Bedrijfsnaam <span class="sj-vp__req">*</span></label>
                            <input type="text" name="bedrijfsnaam" id="sj_bedrijfsnaam" class="sj-vp__input"
                                   value="<?php echo esc_attr($_POST['bedrijfsnaam'] ?? ''); ?>" required>
                        </div>
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_email">E-mailadres <span class="sj-vp__req">*</span></label>
                            <input type="email" name="email" id="sj_email" class="sj-vp__input"
                                   value="<?php echo esc_attr($_POST['email'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Vacature informatie -->
                <div class="sj-vp__section">
                    <p class="sj-vp__section-title">Vacature informatie</p>
                    <div class="sj-vp__grid sj-vp__grid--1">

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_vacaturetitel">Vacaturetitel <span class="sj-vp__req">*</span></label>
                            <input type="text" name="vacaturetitel" id="sj_vacaturetitel" class="sj-vp__input"
                                   value="<?php echo esc_attr($_POST['vacaturetitel'] ?? ''); ?>" required>
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_locatie">Locatie</label>
                            <input type="text" name="locatie" id="sj_locatie" class="sj-vp__input"
                                   value="<?php echo esc_attr($_POST['locatie'] ?? ''); ?>">
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" id="sj_type_baan_label">Type baan</label>
                            <?php
                            $job_listing_types = function_exists('get_job_listing_types') ? get_job_listing_types() : [];
                            $selected_types    = (array)($_POST['type_baan'] ?? []);
                            ?>
                            <div class="sj-vp__ms-hidden" aria-hidden="true">
                                <?php foreach ($job_listing_types as $term): ?>
                                <input type="checkbox" name="type_baan[]" value="<?php echo esc_attr($term->name); ?>"
                                       class="sj-vp__ms-cb"
                                       <?php checked(in_array($term->name, $selected_types)); ?>>
                                <?php endforeach; ?>
                            </div>
                            <div class="sj-vp__ms" id="sj_type_baan_ms" aria-labelledby="sj_type_baan_label" role="group">
                                <div class="sj-vp__ms-trigger" tabindex="0" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="sj-vp__ms-placeholder">Selecteer type(s)...</span>
                                    <span class="sj-vp__ms-tags"></span>
                                    <svg class="sj-vp__ms-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z"/></svg>
                                </div>
                                <ul class="sj-vp__ms-dropdown" role="listbox" aria-multiselectable="true">
                                    <?php foreach ($job_listing_types as $term): ?>
                                    <li class="sj-vp__ms-option<?php echo in_array($term->name, $selected_types) ? ' is-selected' : ''; ?>"
                                        role="option"
                                        aria-selected="<?php echo in_array($term->name, $selected_types) ? 'true' : 'false'; ?>"
                                        data-value="<?php echo esc_attr($term->name); ?>">
                                        <span class="sj-vp__ms-opt-check" aria-hidden="true">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 256 256" fill="currentColor"><path d="M229.66,77.66l-128,128a8,8,0,0,1-11.32,0l-56-56a8,8,0,0,1,11.32-11.32L96,188.69,218.34,66.34a8,8,0,0,1,11.32,11.32Z"/></svg>
                                        </span>
                                        <span class="sj-vp__ms-opt-text"><?php echo esc_html($term->name); ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>

                        <!-- Quill: omschrijving -->
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_omschrijving_hidden">Vacature omschrijving <span class="sj-vp__req">*</span></label>
                            <div class="sj-vp__quill-wrap">
                                <div id="sj_quill_omschrijving" class="sj-vp__quill-editor" style="min-height:220px;"></div>
                            </div>
                            <textarea name="omschrijving" id="sj_omschrijving_hidden" class="sj-vp__quill-hidden" aria-hidden="true"><?php echo esc_textarea($_POST['omschrijving'] ?? ''); ?></textarea>
                            <span class="sj-vp__hint">Beschrijf de functie, vereisten en wat je organisatie biedt.</span>
                        </div>

                        <div class="sj-vp__grid sj-vp__grid--2 sj-vp__upload-grid">
                            <div class="sj-vp__field">
                                <label class="sj-vp__label" for="sj_bedrijfslogo">Bedrijfslogo uploaden <span class="sj-vp__opt">(optioneel)</span></label>
                                <label class="sj-vp__upload sj-vp__upload--square" for="sj_bedrijfslogo">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M240,136v64a16,16,0,0,1-16,16H32a16,16,0,0,1-16-16V136a16,16,0,0,1,16-16H80a8,8,0,0,1,0,16H32v64H224V136H176a8,8,0,0,1,0-16h48A16,16,0,0,1,240,136ZM85.66,77.66,120,43.31V128a8,8,0,0,0,16,0V43.31l34.34,34.35a8,8,0,0,0,11.32-11.32l-48-48a8,8,0,0,0-11.32,0l-48,48A8,8,0,0,0,85.66,77.66Z"/></svg>
                                    <span class="sj-vp__upload-label">Kies bestand</span>
                                    <span class="sj-vp__upload-name">Geen bestand gekozen</span>
                                    <input type="file" name="bedrijfslogo" id="sj_bedrijfslogo" accept="image/*" class="sj-vp__upload-input">
                                </label>
                                <span class="sj-vp__hint">PNG of JPG, liefst vierkant. Max. 2 MB.</span>
                            </div>

                            <div class="sj-vp__field">
                                <label class="sj-vp__label" for="sj_uitgelichte_afbeelding">Uitgelichte afbeelding <span class="sj-vp__opt">(optioneel)</span></label>
                                <label class="sj-vp__upload sj-vp__upload--square" for="sj_uitgelichte_afbeelding">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M216,40H40A16,16,0,0,0,24,56V200a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V56A16,16,0,0,0,216,40Zm0,16V158.75l-26.07-26.06a16,16,0,0,0-22.63,0l-20,20-44-44a16,16,0,0,0-22.62,0L40,149.37V56ZM40,200V172l52-52,44,44a8,8,0,0,0,11.31,0l24.38-24.37L216,184V200Z"/></svg>
                                    <span class="sj-vp__upload-label">Kies afbeelding</span>
                                    <span class="sj-vp__upload-name">Geen bestand gekozen</span>
                                    <input type="file" name="uitgelichte_afbeelding" id="sj_uitgelichte_afbeelding" accept="image/*" class="sj-vp__upload-input">
                                </label>
                                <span class="sj-vp__hint">Uitgelichte afbeelding op de vacaturekaart. Liefst liggend, JPG of PNG.</span>
                            </div>
                        </div>

                    </div>
                </div>

                <footer class="sj-vp__footer">
                    <button type="submit" class="sj-vp__submit">Vacature versturen</button>
                    <p class="sj-vp__footer-note">Je ontvangt een factuur per e-mail na publicatie. Vragen? Mail naar <a href="mailto:support@sustainablejobs.nl">support@sustainablejobs.nl</a>.</p>
                </footer>

            </form>

        </div>
    </div>

    <?php endif; ?>

    <!-- Snel plaatsen balk — sticky onderaan de pagina -->
    <div class="sj-vp-snel" id="sj-snel-balk">
        <div class="sj-vp-snel__text">
            <h2 class="sj-vp-snel__title">Nog sneller plaatsen?</h2>
            <p class="sj-vp-snel__desc">Geef alleen de vacature-link door en wij doen de rest.</p>
        </div>
        <div class="sj-vp-snel__contact">
            <a href="<?php echo esc_url(home_url('/snel-plaatsen/')); ?>" class="sj-vp-snel__btn">Snel Plaatsen</a>
        </div>
        <button class="sj-vp-snel__close" aria-label="Sluiten" onclick="document.getElementById('sj-snel-balk').classList.add('is-hidden')">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M205.66,194.34a8,8,0,0,1-11.32,11.32L128,139.31,61.66,205.66a8,8,0,0,1-11.32-11.32L116.69,128,50.34,61.66A8,8,0,0,1,61.66,50.34L128,116.69l66.34-66.35a8,8,0,0,1,11.32,11.32L139.31,128Z"/></svg>
        </button>
    </div>


    <script>
    (function () {
        /* Wacht tot Quill beschikbaar is */
        function initQuill() {
            if (typeof Quill === 'undefined') {
                setTimeout(initQuill, 80);
                return;
            }

            var toolbarOptions = [
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['link'],
                ['clean']
            ];

            /* ── Omschrijving ── */
            var omschrijvingHidden = document.getElementById('sj_omschrijving_hidden');
            var quillOmschrijving  = new Quill('#sj_quill_omschrijving', {
                theme: 'snow',
                modules: { toolbar: toolbarOptions },
                placeholder: 'Beschrijf de functie, taken, vereisten en wat je organisatie biedt...'
            });

            if (omschrijvingHidden && omschrijvingHidden.value) {
                quillOmschrijving.clipboard.dangerouslyPasteHTML(omschrijvingHidden.value);
            }

            quillOmschrijving.on('text-change', function () {
                if (omschrijvingHidden) {
                    omschrijvingHidden.value = quillOmschrijving.root.innerHTML;
                }
            });

            /* Sync hidden textareas vlak voor submit */
            var form = document.querySelector('.sj-vp__form');
            if (form) {
                form.addEventListener('submit', function () {
                    if (omschrijvingHidden) omschrijvingHidden.value = quillOmschrijving.root.innerHTML;
                });
            }
        }

        initQuill();

        /* ── Pakket radio highlight ── */
        document.querySelectorAll('.sj-vp__pakket-radio').forEach(function (radio) {
            radio.addEventListener('change', function () {
                document.querySelectorAll('.sj-vp__pakket').forEach(function (el) {
                    el.classList.remove('is-selected');
                });
                if (radio.checked) {
                    radio.closest('.sj-vp__pakket').classList.add('is-selected');
                }
            });
        });

        /* ── Bestandsnaam tonen bij upload ── */
        document.querySelectorAll('.sj-vp__upload-input').forEach(function (fileInput) {
            var fileName = fileInput.closest('.sj-vp__upload').querySelector('.sj-vp__upload-name');
            if (!fileName) return;

            fileInput.addEventListener('change', function () {
                fileName.textContent = fileInput.files.length ? fileInput.files[0].name : 'Geen bestand gekozen';
            });
        });

        /* ── Multiselect: type baan ── */
        var ms        = document.getElementById('sj_type_baan_ms');
        var trigger   = ms && ms.querySelector('.sj-vp__ms-trigger');
        var tagsEl    = ms && ms.querySelector('.sj-vp__ms-tags');
        var placeholder = ms && ms.querySelector('.sj-vp__ms-placeholder');
        var options   = ms ? Array.from(ms.querySelectorAll('.sj-vp__ms-option')) : [];
        var checkboxes = ms ? Array.from(ms.closest('.sj-vp__field').querySelectorAll('.sj-vp__ms-cb')) : [];

        function msSync() {
            if (!tagsEl) return;
            tagsEl.innerHTML = '';
            var selected = options.filter(function(o){ return o.classList.contains('is-selected'); });
            placeholder.style.display = selected.length ? 'none' : '';
            selected.forEach(function (opt) {
                var tag = document.createElement('span');
                tag.className = 'sj-vp__ms-tag';
                tag.textContent = opt.dataset.value;
                var rm = document.createElement('button');
                rm.type = 'button';
                rm.className = 'sj-vp__ms-tag-remove';
                rm.setAttribute('aria-label', 'Verwijder ' + opt.dataset.value);
                rm.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 256 256" fill="currentColor"><path d="M205.66,194.34a8,8,0,0,1-11.32,11.32L128,139.31,61.66,205.66a8,8,0,0,1-11.32-11.32L116.69,128,50.34,61.66A8,8,0,0,1,61.66,50.34L128,116.69l66.34-66.35a8,8,0,0,1,11.32,11.32L139.31,128Z"/></svg>';
                rm.addEventListener('click', function (e) {
                    e.stopPropagation();
                    opt.classList.remove('is-selected');
                    opt.setAttribute('aria-selected', 'false');
                    var cb = checkboxes.find(function(c){ return c.value === opt.dataset.value; });
                    if (cb) cb.checked = false;
                    msSync();
                });
                tag.appendChild(rm);
                tagsEl.appendChild(tag);
            });
        }

        function msOpen() {
            ms.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
        }
        function msClose() {
            ms.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
        }

        if (trigger) {
            trigger.addEventListener('click', function () {
                ms.classList.contains('is-open') ? msClose() : msOpen();
            });
            trigger.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); ms.classList.contains('is-open') ? msClose() : msOpen(); }
                if (e.key === 'Escape') msClose();
            });
        }

        options.forEach(function (opt) {
            opt.addEventListener('click', function () {
                var isSelected = opt.classList.toggle('is-selected');
                opt.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                var cb = checkboxes.find(function(c){ return c.value === opt.dataset.value; });
                if (cb) cb.checked = isSelected;
                msSync();
            });
        });

        document.addEventListener('click', function (e) {
            if (ms && !ms.contains(e.target)) msClose();
        });

        /* Initieel synchroniseren (na page-reload met POST data) */
        msSync();
    })();
    </script>

    <?php
    return ob_get_clean();
}
