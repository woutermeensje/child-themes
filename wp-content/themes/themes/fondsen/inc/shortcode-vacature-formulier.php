<?php
if (!defined('ABSPATH')) exit;

add_shortcode('sj_vacature_plaatsen', 'fondsen_vacature_plaatsen_shortcode');

function fondsen_vacature_plaatsen_shortcode(): string {
    $success = isset($_GET['sj_vacature_sent']) && $_GET['sj_vacature_sent'] === '1';
    $errors  = [];

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['sj_vp_nonce']) &&
        wp_verify_nonce($_POST['sj_vp_nonce'], 'sj_vacature_plaatsen')
    ) {
        $voornaam      = sanitize_text_field($_POST['voornaam'] ?? '');
        $achternaam    = sanitize_text_field($_POST['achternaam'] ?? '');
        $bedrijfsnaam  = sanitize_text_field($_POST['bedrijfsnaam'] ?? '');
        $email         = sanitize_email($_POST['email'] ?? '');
        $pakket        = sanitize_text_field($_POST['pakket'] ?? '');
        $vacaturetitel = sanitize_text_field($_POST['vacaturetitel'] ?? '');
        $locatie       = sanitize_text_field($_POST['locatie'] ?? '');
        $type_baan     = array_map('sanitize_text_field', (array) ($_POST['type_baan'] ?? []));
        $omschrijving  = wp_kses_post($_POST['omschrijving'] ?? '');
        $extra_info    = wp_kses_post($_POST['extra_info'] ?? '');
        $referral      = sanitize_text_field($_POST['referral'] ?? '');

        if (!$voornaam) $errors[] = 'Vul je voornaam in.';
        if (!$achternaam) $errors[] = 'Vul je achternaam in.';
        if (!$bedrijfsnaam) $errors[] = 'Vul je bedrijfsnaam in.';
        if (!is_email($email)) $errors[] = 'Vul een geldig e-mailadres in.';
        if (!$vacaturetitel) $errors[] = 'Vul een vacaturetitel in.';
        if (!$omschrijving || trim(wp_strip_all_tags($omschrijving)) === '') $errors[] = 'Vul een vacatureomschrijving in.';

        if (empty($errors)) {
            $upload = null;

            if (!empty($_FILES['bedrijfslogo']['tmp_name'])) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';
                require_once ABSPATH . 'wp-admin/includes/image.php';

                $upload = media_handle_upload('bedrijfslogo', 0);
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
            $body .= "--- Vacature omschrijving ---\n" . wp_strip_all_tags($omschrijving) . "\n\n";
            $body .= "--- Aanvullende informatie ---\n" . wp_strip_all_tags($extra_info) . "\n";

            $headers     = ['Content-Type: text/plain; charset=UTF-8'];
            $attachments = [];

            if (!is_wp_error($upload)) {
                $path = get_attached_file($upload);
                if ($path && file_exists($path)) {
                    $attachments[] = $path;
                }
            }

            wp_mail(
                'informatie@fondsen.org',
                "Nieuwe vacature: $vacaturetitel",
                $body,
                $headers,
                $attachments
            );

            $confirmation_body  = "Beste $voornaam,\n\n";
            $confirmation_body .= "Bedankt voor het plaatsen van je vacature op Fondsen.org.\n\n";
            $confirmation_body .= "We hebben je vacature in goede orde ontvangen:\n";
            $confirmation_body .= "Vacaturetitel: $vacaturetitel\n";
            $confirmation_body .= "Bedrijf: $bedrijfsnaam\n";
            $confirmation_body .= "Locatie: $locatie\n";
            $confirmation_body .= "Pakket: $pakket\n\n";
            $confirmation_body .= "Ons team bekijkt je inzending en neemt indien nodig contact met je op.\n\n";
            $confirmation_body .= "Heb je in de tussentijd vragen? Mail gerust naar informatie@fondsen.org.\n\n";
            $confirmation_body .= "Met vriendelijke groet,\n";
            $confirmation_body .= "Fondsen.org";

            wp_mail(
                $email,
                'Bevestiging van je vacatureplaatsing op Fondsen.org',
                $confirmation_body,
                $headers
            );

            $post_id = wp_insert_post([
                'post_title'  => sanitize_text_field($vacaturetitel),
                'post_status' => 'pending',
                'post_type'   => 'sj_vacature',
                'post_author' => 0,
            ]);

            if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, '_sj_pakket', $pakket);
                update_post_meta($post_id, '_sj_voornaam', $voornaam);
                update_post_meta($post_id, '_sj_achternaam', $achternaam);
                update_post_meta($post_id, '_sj_bedrijfsnaam', $bedrijfsnaam);
                update_post_meta($post_id, '_sj_email', $email);
                update_post_meta($post_id, '_sj_locatie', $locatie);
                update_post_meta($post_id, '_sj_type_baan', $type_baan);
                update_post_meta($post_id, '_sj_omschrijving', $omschrijving);
                update_post_meta($post_id, '_sj_extra_info', $extra_info);
                update_post_meta($post_id, '_sj_referral', $referral);

                if (!is_wp_error($upload)) {
                    update_post_meta($post_id, '_sj_logo_id', $upload);
                }
            }

            $content = $omschrijving;
            if ($extra_info) {
                $content .= '<h3>Aanvullende informatie</h3>' . $extra_info;
            }

            $job_id = wp_insert_post([
                'post_title'   => sanitize_text_field($vacaturetitel),
                'post_content' => $content,
                'post_status'  => 'draft',
                'post_type'    => 'job_listing',
                'post_author'  => get_current_user_id() ?: 1,
            ]);

            if ($job_id && !is_wp_error($job_id)) {
                update_post_meta($job_id, '_job_location', $locatie);
                update_post_meta($job_id, '_company_name', $bedrijfsnaam);
                update_post_meta($job_id, '_company_email', $email);
                update_post_meta($job_id, '_job_salary', '');
                update_post_meta($job_id, '_filled', 0);
                update_post_meta($job_id, '_featured', 0);
                update_post_meta($job_id, '_job_expires', '');
                update_post_meta($job_id, '_sj_pakket', $pakket);

                if (!is_wp_error($upload)) {
                    update_post_meta($job_id, '_company_logo', wp_get_attachment_url($upload));
                    set_post_thumbnail($job_id, $upload);
                }

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
            }

            wp_safe_redirect(add_query_arg('sj_vacature_sent', '1', get_permalink()));
            exit;
        }
    }

    $types = ['Fulltime', 'Parttime', 'Project', 'Stage', 'Vrijwilligerswerk'];
    $pakketten = [
        'Standaard Vacature: €275 excl. btw'         => ['label' => 'Standaard', 'prijs' => '€275,00 excl. btw'],
        'Spotlight Vacature: €375 excl. btw'         => ['label' => 'Spotlight', 'prijs' => '€375,00 excl. btw'],
        'Stage & Vrijwilligerswerk: Gratis'          => ['label' => 'Stage & Vrijwilligerswerk', 'prijs' => 'Gratis'],
        'Wij zijn lid van Fondsen.org: Gratis'       => ['label' => 'Wij zijn lid', 'prijs' => 'Gratis voor leden'],
        'Wij hebben een strippenkaart'               => ['label' => 'Strippenkaart', 'prijs' => 'Via strippenkaart'],
    ];

    ob_start();
    ?>
    <?php if ($success) : ?>
    <div class="sj-vp-notice sj-vp-notice--success">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M173.66,98.34a8,8,0,0,1,0,11.32l-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35A8,8,0,0,1,173.66,98.34ZM232,128A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"/></svg>
        <div>
            <strong>Vacature succesvol verzonden!</strong>
            <p>We hebben je aanvraag ontvangen en sturen ook een bevestiging per e-mail.</p>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($errors)) : ?>
    <div class="sj-vp-notice sj-vp-notice--error">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M236.8,188.09,149.35,36.22a24.76,24.76,0,0,0-42.7,0L19.2,188.09a23.51,23.51,0,0,0,0,23.72A24.35,24.35,0,0,0,40.55,224h174.9a24.35,24.35,0,0,0,21.33-12.19A23.51,23.51,0,0,0,236.8,188.09ZM120,104a8,8,0,0,1,16,0v40a8,8,0,0,1-16,0Zm8,88a12,12,0,1,1,12-12A12,12,0,0,1,128,192Z"/></svg>
        <div>
            <strong>Er zijn een paar fouten:</strong>
            <ul>
                <?php foreach ($errors as $error) : ?>
                <li><?php echo esc_html($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <div class="sj-vp">
        <div class="sj-vp__block">
            <header class="sj-vp__header">
                <h2 class="sj-vp__title">Vacature plaatsen</h2>
                <p class="sj-vp__subtitle">Vul de gegevens in en we nemen je vacature in behandeling. Je ontvangt direct een bevestiging per e-mail.</p>
            </header>

            <form method="post" class="sj-vp__form" enctype="multipart/form-data" novalidate>
                <?php wp_nonce_field('sj_vacature_plaatsen', 'sj_vp_nonce'); ?>

                <div class="sj-vp__section">
                    <p class="sj-vp__section-title">Kies je pakket</p>
                    <div class="sj-vp__grid sj-vp__grid--2">
                        <?php $selected_pakket = $_POST['pakket'] ?? 'Standaard Vacature: €275 excl. btw'; ?>
                        <?php foreach ($pakketten as $value => $info) : ?>
                        <label class="sj-vp__pakket<?php echo $selected_pakket === $value ? ' is-selected' : ''; ?>">
                            <input type="radio" name="pakket" value="<?php echo esc_attr($value); ?>" <?php checked($selected_pakket, $value); ?> class="sj-vp__pakket-radio">
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
                    <p class="sj-vp__pakket-note">Vragen over pakketten of lidmaatschap? Mail naar <a href="mailto:informatie@fondsen.org">informatie@fondsen.org</a>.</p>
                </div>

                <div class="sj-vp__section">
                    <p class="sj-vp__section-title">Contactgegevens</p>
                    <div class="sj-vp__grid sj-vp__grid--2">
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_voornaam">Voornaam <span class="sj-vp__req">*</span></label>
                            <input type="text" name="voornaam" id="sj_voornaam" class="sj-vp__input" value="<?php echo esc_attr($_POST['voornaam'] ?? ''); ?>" placeholder="Jan" required>
                        </div>
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_achternaam">Achternaam <span class="sj-vp__req">*</span></label>
                            <input type="text" name="achternaam" id="sj_achternaam" class="sj-vp__input" value="<?php echo esc_attr($_POST['achternaam'] ?? ''); ?>" placeholder="de Vries" required>
                        </div>
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_bedrijfsnaam">Bedrijfsnaam <span class="sj-vp__req">*</span></label>
                            <input type="text" name="bedrijfsnaam" id="sj_bedrijfsnaam" class="sj-vp__input" value="<?php echo esc_attr($_POST['bedrijfsnaam'] ?? ''); ?>" placeholder="Jouw organisatie" required>
                        </div>
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_email">E-mailadres <span class="sj-vp__req">*</span></label>
                            <input type="email" name="email" id="sj_email" class="sj-vp__input" value="<?php echo esc_attr($_POST['email'] ?? ''); ?>" placeholder="jan@organisatie.nl" required>
                        </div>
                    </div>
                </div>

                <div class="sj-vp__section">
                    <p class="sj-vp__section-title">Vacature informatie</p>
                    <div class="sj-vp__grid sj-vp__grid--1">
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_vacaturetitel">Vacaturetitel <span class="sj-vp__req">*</span></label>
                            <input type="text" name="vacaturetitel" id="sj_vacaturetitel" class="sj-vp__input" value="<?php echo esc_attr($_POST['vacaturetitel'] ?? ''); ?>" placeholder="Bijv. Fondsenwerver" required>
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_locatie">Locatie</label>
                            <input type="text" name="locatie" id="sj_locatie" class="sj-vp__input" value="<?php echo esc_attr($_POST['locatie'] ?? ''); ?>" placeholder="Amsterdam, Hybride, Remote...">
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" id="sj_type_baan_label">Type baan</label>
                            <?php $selected_types = (array) ($_POST['type_baan'] ?? []); ?>
                            <div class="sj-vp__ms-hidden" aria-hidden="true">
                                <?php foreach ($types as $type) : ?>
                                <input type="checkbox" name="type_baan[]" value="<?php echo esc_attr($type); ?>" class="sj-vp__ms-cb" <?php checked(in_array($type, $selected_types, true)); ?>>
                                <?php endforeach; ?>
                            </div>
                            <div class="sj-vp__ms" id="sj_type_baan_ms" aria-labelledby="sj_type_baan_label" role="group">
                                <div class="sj-vp__ms-trigger" tabindex="0" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="sj-vp__ms-placeholder">Selecteer type(s)...</span>
                                    <span class="sj-vp__ms-tags"></span>
                                    <svg class="sj-vp__ms-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z"/></svg>
                                </div>
                                <ul class="sj-vp__ms-dropdown" role="listbox" aria-multiselectable="true">
                                    <?php foreach ($types as $type) : ?>
                                    <li class="sj-vp__ms-option<?php echo in_array($type, $selected_types, true) ? ' is-selected' : ''; ?>" role="option" aria-selected="<?php echo in_array($type, $selected_types, true) ? 'true' : 'false'; ?>" data-value="<?php echo esc_attr($type); ?>">
                                        <span class="sj-vp__ms-opt-text"><?php echo esc_html($type); ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_omschrijving_hidden">Vacature omschrijving <span class="sj-vp__req">*</span></label>
                            <div class="sj-vp__quill-wrap">
                                <div id="sj_quill_omschrijving" class="sj-vp__quill-editor" style="min-height:220px;"></div>
                            </div>
                            <textarea name="omschrijving" id="sj_omschrijving_hidden" class="sj-vp__quill-hidden" aria-hidden="true"><?php echo esc_textarea($_POST['omschrijving'] ?? ''); ?></textarea>
                            <span class="sj-vp__hint">Beschrijf de functie, vereisten en wat je organisatie biedt.</span>
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_extra_info_hidden">Aanvullende informatie <span class="sj-vp__opt">(optioneel)</span></label>
                            <div class="sj-vp__quill-wrap">
                                <div id="sj_quill_extra_info" class="sj-vp__quill-editor" style="min-height:140px;"></div>
                            </div>
                            <textarea name="extra_info" id="sj_extra_info_hidden" class="sj-vp__quill-hidden" aria-hidden="true"><?php echo esc_textarea($_POST['extra_info'] ?? ''); ?></textarea>
                            <span class="sj-vp__hint">Bijvoorbeeld arbeidsvoorwaarden, cultuur of aanvullende context.</span>
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_referral">Hoe heb je ons gevonden?</label>
                            <input type="text" name="referral" id="sj_referral" class="sj-vp__input" value="<?php echo esc_attr($_POST['referral'] ?? ''); ?>" placeholder="Bijvoorbeeld via nieuwsbrief of Google">
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_bedrijfslogo">Bedrijfslogo uploaden</label>
                            <label class="sj-vp__upload" for="sj_bedrijfslogo">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M240,136v64a16,16,0,0,1-16,16H32a16,16,0,0,1-16-16V136a16,16,0,0,1,16-16H80a8,8,0,0,1,0,16H32v64H224V136H176a8,8,0,0,1,0-16h48A16,16,0,0,1,240,136ZM85.66,77.66,120,43.31V128a8,8,0,0,0,16,0V43.31l34.34,34.35a8,8,0,0,0,11.32-11.32l-48-48a8,8,0,0,0-11.32,0l-48,48A8,8,0,0,0,85.66,77.66Z"/></svg>
                                <span class="sj-vp__upload-label">Kies bestand</span>
                                <span class="sj-vp__upload-name">Geen bestand gekozen</span>
                                <input type="file" name="bedrijfslogo" id="sj_bedrijfslogo" accept="image/*" class="sj-vp__upload-input">
                            </label>
                            <span class="sj-vp__hint">PNG of JPG, liefst vierkant.</span>
                        </div>
                    </div>
                </div>

                <footer class="sj-vp__footer">
                    <button type="submit" class="sj-vp__submit">Vacature versturen</button>
                    <p class="sj-vp__footer-note">Vragen? Mail naar <a href="mailto:informatie@fondsen.org">informatie@fondsen.org</a>.</p>
                </footer>
            </form>
        </div>
    </div>

    <script>
    (function () {
        function initQuill() {
            if (typeof Quill === 'undefined') {
                setTimeout(initQuill, 80);
                return;
            }

            var toolbarOptions = [
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link'],
                ['clean']
            ];

            var omschrijvingHidden = document.getElementById('sj_omschrijving_hidden');
            var extraInfoHidden = document.getElementById('sj_extra_info_hidden');
            var omschrijvingEditor = new Quill('#sj_quill_omschrijving', {
                theme: 'snow',
                modules: { toolbar: toolbarOptions },
                placeholder: 'Beschrijf de functie, taken, vereisten en wat je organisatie biedt...'
            });
            var extraInfoEditor = new Quill('#sj_quill_extra_info', {
                theme: 'snow',
                modules: { toolbar: toolbarOptions },
                placeholder: 'Aanvullende informatie...'
            });

            if (omschrijvingHidden && omschrijvingHidden.value) {
                omschrijvingEditor.clipboard.dangerouslyPasteHTML(omschrijvingHidden.value);
            }

            if (extraInfoHidden && extraInfoHidden.value) {
                extraInfoEditor.clipboard.dangerouslyPasteHTML(extraInfoHidden.value);
            }

            omschrijvingEditor.on('text-change', function () {
                omschrijvingHidden.value = omschrijvingEditor.root.innerHTML;
            });

            extraInfoEditor.on('text-change', function () {
                extraInfoHidden.value = extraInfoEditor.root.innerHTML;
            });

            var form = document.querySelector('.sj-vp__form');
            if (form) {
                form.addEventListener('submit', function () {
                    omschrijvingHidden.value = omschrijvingEditor.root.innerHTML;
                    extraInfoHidden.value = extraInfoEditor.root.innerHTML;
                });
            }
        }

        initQuill();

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

        var fileInput = document.getElementById('sj_bedrijfslogo');
        var fileName = document.querySelector('.sj-vp__upload-name');
        if (fileInput && fileName) {
            fileInput.addEventListener('change', function () {
                fileName.textContent = fileInput.files.length ? fileInput.files[0].name : 'Geen bestand gekozen';
            });
        }

        var ms = document.getElementById('sj_type_baan_ms');
        if (!ms) return;

        var trigger = ms.querySelector('.sj-vp__ms-trigger');
        var tagsEl = ms.querySelector('.sj-vp__ms-tags');
        var placeholder = ms.querySelector('.sj-vp__ms-placeholder');
        var options = Array.from(ms.querySelectorAll('.sj-vp__ms-option'));
        var checkboxes = Array.from(ms.closest('.sj-vp__field').querySelectorAll('.sj-vp__ms-cb'));

        function syncMultiSelect() {
            tagsEl.innerHTML = '';
            var selected = options.filter(function (option) {
                return option.classList.contains('is-selected');
            });

            placeholder.style.display = selected.length ? 'none' : '';

            selected.forEach(function (option) {
                var tag = document.createElement('span');
                tag.className = 'sj-vp__ms-tag';
                tag.textContent = option.dataset.value;

                var remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'sj-vp__ms-tag-remove';
                remove.setAttribute('aria-label', 'Verwijder ' + option.dataset.value);
                remove.textContent = 'x';
                remove.addEventListener('click', function (event) {
                    event.stopPropagation();
                    option.classList.remove('is-selected');
                    option.setAttribute('aria-selected', 'false');
                    var checkbox = checkboxes.find(function (item) {
                        return item.value === option.dataset.value;
                    });
                    if (checkbox) checkbox.checked = false;
                    syncMultiSelect();
                });

                tag.appendChild(remove);
                tagsEl.appendChild(tag);
            });
        }

        function openMultiSelect() {
            ms.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
        }

        function closeMultiSelect() {
            ms.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
        }

        trigger.addEventListener('click', function () {
            if (ms.classList.contains('is-open')) {
                closeMultiSelect();
            } else {
                openMultiSelect();
            }
        });

        options.forEach(function (option) {
            option.addEventListener('click', function () {
                var isSelected = option.classList.toggle('is-selected');
                option.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                var checkbox = checkboxes.find(function (item) {
                    return item.value === option.dataset.value;
                });
                if (checkbox) checkbox.checked = isSelected;
                syncMultiSelect();
            });
        });

        document.addEventListener('click', function (event) {
            if (!ms.contains(event.target)) {
                closeMultiSelect();
            }
        });

        syncMultiSelect();
    })();
    </script>
    <?php

    return ob_get_clean();
}
