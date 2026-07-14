<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [sj_job_plaatsen]
 * Job posting form with Quill rich text editor.
 */
add_shortcode('sj_job_plaatsen', 'sj_job_plaatsen_shortcode');

function sj_job_plaatsen_shortcode(): string {

    /* Processing */
    $success = false;
    $errors  = [];

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['sj_vp_nonce']) &&
        wp_verify_nonce($_POST['sj_vp_nonce'], 'sj_job_plaatsen')
    ) {
        $voornaam       = sanitize_text_field($_POST['voornaam']       ?? '');
        $achternaam     = sanitize_text_field($_POST['achternaam']      ?? '');
        $companysnaam   = sanitize_text_field($_POST['companysnaam']    ?? '');
        $email          = sanitize_email($_POST['email']                ?? '');
        $pakket         = sanitize_text_field($_POST['pakket']          ?? '');
        $jobtitel  = sanitize_text_field($_POST['jobtitel']   ?? '');
        $locatie        = sanitize_text_field($_POST['locatie']         ?? '');
        $type_baan      = array_map('sanitize_text_field', (array)($_POST['type_baan'] ?? []));
        $omschrijving   = wp_kses_post($_POST['omschrijving']           ?? '');
        $referral       = sanitize_text_field($_POST['referral']        ?? '');

        if (!$voornaam)       $errors[] = 'Please enter your first name.';
        if (!$achternaam)     $errors[] = 'Please enter your last name.';
        if (!$companysnaam)   $errors[] = 'Please enter your company name.';
        if (!is_email($email)) $errors[] = 'Please enter a valid email address.';
        if (!$jobtitel)  $errors[] = 'Please enter a job title.';
        if (!$omschrijving)   $errors[] = 'Please enter a job description.';

        if (empty($errors)) {
            $attachments = [];
            $upload = null;
            $upload_featured = null;

            if (
                !empty($_FILES['companyslogo']['tmp_name']) ||
                !empty($_FILES['uitgelichte_afbeelding']['tmp_name'])
            ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';
                require_once ABSPATH . 'wp-admin/includes/image.php';
            }

            if (!empty($_FILES['companyslogo']['tmp_name'])) {
                $upload = media_handle_upload('companyslogo', 0);
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
            $body  = "New job via the form:\n\n";
            $body .= "Package: $pakket\n";
            $body .= "Name: $voornaam $achternaam\n";
            $body .= "Company: $companysnaam\n";
            $body .= "Email: $email\n";
            $body .= "Job title: $jobtitel\n";
            $body .= "Location: $locatie\n";
            $body .= "Job type: $type_baan_str\n";
            $body .= "How did you find us: $referral\n\n";
            $body .= "--- Job description ---\n" . strip_tags($omschrijving) . "\n\n";

            $headers = ['Content-Type: text/plain; charset=UTF-8'];

            wp_mail(
                'support@sustainablejobs.com',
                "New job: $jobtitel",
                $body,
                $headers,
                $attachments
            );

            $confirmation_body  = "Dear $voornaam,\n\n";
            $confirmation_body .= "Thank you for posting your job on Sustainablejobs.com.\n\n";
            $confirmation_body .= "We have received your job:\n";
            $confirmation_body .= "Job title: $jobtitel\n";
            $confirmation_body .= "Company: $companysnaam\n";
            $confirmation_body .= "Location: $locatie\n";
            $confirmation_body .= "Package: $pakket\n\n";
            $confirmation_body .= "Our team will review your submission and contact you if needed. You will receive the invoice by email after publication.\n\n";
            $confirmation_body .= "If you have any questions in the meantime, feel free to reply to this email or contact us at support@sustainablejobs.com.\n\n";
            $confirmation_body .= "Kind regards,\n";
            $confirmation_body .= "Sustainablejobs.com";

            wp_mail(
                $email,
                'Confirmation of your job posting on Sustainablejobs.com',
                $confirmation_body,
                $headers
            );

            /* Save submission as CPT. */
            $post_id = wp_insert_post([
                'post_title'  => sanitize_text_field($jobtitel),
                'post_status' => 'pending',
                'post_type'   => 'sj_job',
                'post_author' => 0,
            ]);

            if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, '_sj_pakket',       $pakket);
                update_post_meta($post_id, '_sj_voornaam',     $voornaam);
                update_post_meta($post_id, '_sj_achternaam',   $achternaam);
                update_post_meta($post_id, '_sj_companysnaam', $companysnaam);
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

            /* Create draft job_listing in WP Job Manager. */
            $job_id = wp_insert_post([
                'post_title'   => sanitize_text_field($jobtitel),
                'post_content' => $omschrijving,
                'post_status'  => 'draft',
                'post_type'    => 'job_listing',
                'post_author'  => 1,
            ]);

            if ($job_id && !is_wp_error($job_id)) {
                update_post_meta($job_id, '_job_location',    $locatie);
                update_post_meta($job_id, '_company_name',    $companysnaam);
                update_post_meta($job_id, '_company_email',   $email);
                update_post_meta($job_id, '_job_salary',      '');
                update_post_meta($job_id, '_filled',          0);
                update_post_meta($job_id, '_featured',        0);
                update_post_meta($job_id, '_job_expires',     '');

                // Link company logo.
                if (!empty($upload) && !is_wp_error($upload)) {
                    update_post_meta($job_id, '_company_logo', wp_get_attachment_url($upload));
                }

                // Link featured image separately from the company logo.
                if (!empty($upload_featured) && !is_wp_error($upload_featured)) {
                    update_post_meta($job_id, '_cover_image', wp_get_attachment_url($upload_featured));
                }

                // Link job types.
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

                // Store package as a note.
                update_post_meta($job_id, '_sj_pakket', $pakket);
            }

            wp_redirect(home_url('/job-posting-confirmation/'));
            exit;
        }
    }

    /* Build HTML. */
    ob_start();

    if ($success): ?>

    <div class="sj-vp-notice sj-vp-notice--success">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M173.66,98.34a8,8,0,0,1,0,11.32l-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35A8,8,0,0,1,173.66,98.34ZM232,128A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"/></svg>
        <div>
            <strong>Job submitted successfully!</strong>
            <p>We will contact you as soon as possible.</p>
        </div>
    </div>

    <?php else: ?>

    <?php if (!empty($errors)): ?>
    <div class="sj-vp-notice sj-vp-notice--error">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M236.8,188.09,149.35,36.22a24.76,24.76,0,0,0-42.7,0L19.2,188.09a23.51,23.51,0,0,0,0,23.72A24.35,24.35,0,0,0,40.55,224h174.9a24.35,24.35,0,0,0,21.33-12.19A23.51,23.51,0,0,0,236.8,188.09ZM120,104a8,8,0,0,1,16,0v40a8,8,0,0,1-16,0Zm8,88a12,12,0,1,1,12-12A12,12,0,0,1,128,192Z"/></svg>
        <div>
            <strong>There are a few errors:</strong>
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
                <h2 class="sj-vp__title">Post a Job</h2>
                <p class="sj-vp__subtitle">Fill in the details and we will publish your job as soon as possible. You will receive an invoice by email after publication.</p>
            </header>

            <form method="post" class="sj-vp__form" enctype="multipart/form-data" novalidate>
                <?php wp_nonce_field('sj_job_plaatsen', 'sj_vp_nonce'); ?>

                <!-- Package -->
                <div class="sj-vp__section">
                    <p class="sj-vp__section-title">Choose your package</p>
                    <div class="sj-vp__grid sj-vp__grid--2">
                        <?php
                        $pakketten = [
                            'Standard Job: €275 excl. VAT'                  => ['label' => 'Standard', 'prijs' => '€275.00 excl. VAT'],
                            'Spotlight Job: €375 excl. VAT'                 => ['label' => 'Spotlight', 'prijs' => '€375.00 excl. VAT'],
                            'Internship & Volunteering: Free'               => ['label' => 'Internship & Volunteering', 'prijs' => 'Free'],
                            'We are a member of Sustainablejobs.com: Free'   => ['label' => 'We are a member', 'prijs' => 'Free for members'],
                            'We have a credit bundle'                       => ['label' => 'Job bundle', 'prijs' => 'Via credit bundle'],
                        ];
                        $selected_pakket = $_POST['pakket'] ?? 'Standard Job: €275 excl. VAT';
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

                <!-- Contact details -->
                <div class="sj-vp__section">
                    <p class="sj-vp__section-title">Contact details</p>
                    <div class="sj-vp__grid sj-vp__grid--2">
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_voornaam">First name <span class="sj-vp__req">*</span></label>
                            <input type="text" name="voornaam" id="sj_voornaam" class="sj-vp__input"
                                   value="<?php echo esc_attr($_POST['voornaam'] ?? ''); ?>" required>
                        </div>
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_achternaam">Last name <span class="sj-vp__req">*</span></label>
                            <input type="text" name="achternaam" id="sj_achternaam" class="sj-vp__input"
                                   value="<?php echo esc_attr($_POST['achternaam'] ?? ''); ?>" required>
                        </div>
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_companysnaam">Company name <span class="sj-vp__req">*</span></label>
                            <input type="text" name="companysnaam" id="sj_companysnaam" class="sj-vp__input"
                                   value="<?php echo esc_attr($_POST['companysnaam'] ?? ''); ?>" required>
                        </div>
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_email">Email address <span class="sj-vp__req">*</span></label>
                            <input type="email" name="email" id="sj_email" class="sj-vp__input"
                                   value="<?php echo esc_attr($_POST['email'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Job information -->
                <div class="sj-vp__section">
                    <p class="sj-vp__section-title">Job information</p>
                    <div class="sj-vp__grid sj-vp__grid--1">

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_jobtitel">Job title <span class="sj-vp__req">*</span></label>
                            <input type="text" name="jobtitel" id="sj_jobtitel" class="sj-vp__input"
                                   value="<?php echo esc_attr($_POST['jobtitel'] ?? ''); ?>" required>
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_locatie">Location</label>
                            <input type="text" name="locatie" id="sj_locatie" class="sj-vp__input"
                                   value="<?php echo esc_attr($_POST['locatie'] ?? ''); ?>">
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" id="sj_type_baan_label">Job type</label>
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
                                    <span class="sj-vp__ms-placeholder">Select type(s)...</span>
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
                            <label class="sj-vp__label" for="sj_omschrijving_hidden">Job description <span class="sj-vp__req">*</span></label>
                            <div class="sj-vp__quill-wrap">
                                <div id="sj_quill_omschrijving" class="sj-vp__quill-editor" style="min-height:220px;"></div>
                            </div>
                            <textarea name="omschrijving" id="sj_omschrijving_hidden" class="sj-vp__quill-hidden" aria-hidden="true"><?php echo esc_textarea($_POST['omschrijving'] ?? ''); ?></textarea>
                            <span class="sj-vp__hint">Describe the role, requirements and what your organization offers.</span>
                        </div>

                        <div class="sj-vp__grid sj-vp__grid--2 sj-vp__upload-grid">
                            <div class="sj-vp__field">
                                <label class="sj-vp__label" for="sj_companyslogo">Upload company logo <span class="sj-vp__opt">(optional)</span></label>
                                <label class="sj-vp__upload sj-vp__upload--square" for="sj_companyslogo">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M240,136v64a16,16,0,0,1-16,16H32a16,16,0,0,1-16-16V136a16,16,0,0,1,16-16H80a8,8,0,0,1,0,16H32v64H224V136H176a8,8,0,0,1,0-16h48A16,16,0,0,1,240,136ZM85.66,77.66,120,43.31V128a8,8,0,0,0,16,0V43.31l34.34,34.35a8,8,0,0,0,11.32-11.32l-48-48a8,8,0,0,0-11.32,0l-48,48A8,8,0,0,0,85.66,77.66Z"/></svg>
                                    <span class="sj-vp__upload-label">Choose file</span>
                                    <span class="sj-vp__upload-name">No file selected</span>
                                    <input type="file" name="companyslogo" id="sj_companyslogo" accept="image/*" class="sj-vp__upload-input">
                                </label>
                                <span class="sj-vp__hint">PNG or JPG, preferably square. Max. 2 MB.</span>
                            </div>

                            <div class="sj-vp__field">
                                <label class="sj-vp__label" for="sj_uitgelichte_afbeelding">Featured image <span class="sj-vp__opt">(optional)</span></label>
                                <label class="sj-vp__upload sj-vp__upload--square" for="sj_uitgelichte_afbeelding">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M216,40H40A16,16,0,0,0,24,56V200a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V56A16,16,0,0,0,216,40Zm0,16V158.75l-26.07-26.06a16,16,0,0,0-22.63,0l-20,20-44-44a16,16,0,0,0-22.62,0L40,149.37V56ZM40,200V172l52-52,44,44a8,8,0,0,0,11.31,0l24.38-24.37L216,184V200Z"/></svg>
                                    <span class="sj-vp__upload-label">Choose image</span>
                                    <span class="sj-vp__upload-name">No file selected</span>
                                    <input type="file" name="uitgelichte_afbeelding" id="sj_uitgelichte_afbeelding" accept="image/*" class="sj-vp__upload-input">
                                </label>
                                <span class="sj-vp__hint">Featured image on the job card. Preferably landscape, JPG or PNG.</span>
                            </div>
                        </div>

                    </div>
                </div>

                <footer class="sj-vp__footer">
                    <button type="submit" class="sj-vp__submit">Submit job</button>
                    <p class="sj-vp__footer-note">You will receive an invoice by email after publication. Questions? Email <a href="mailto:support@sustainablejobs.com">support@sustainablejobs.com</a>.</p>
                </footer>

            </form>

        </div>
    </div>

    <?php endif; ?>

    <!-- Quick post bar. -->
    <div class="sj-vp-snel" id="sj-snel-balk">
        <div class="sj-vp-snel__text">
            <h2 class="sj-vp-snel__title">Want to post even faster?</h2>
            <p class="sj-vp-snel__desc">Share only the job link and we will take care of the rest.</p>
        </div>
        <div class="sj-vp-snel__contact">
            <a href="<?php echo esc_url(home_url('/quick-post/')); ?>" class="sj-vp-snel__btn">Quick Post</a>
        </div>
        <button class="sj-vp-snel__close" aria-label="Close" onclick="document.getElementById('sj-snel-balk').classList.add('is-hidden')">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M205.66,194.34a8,8,0,0,1-11.32,11.32L128,139.31,61.66,205.66a8,8,0,0,1-11.32-11.32L116.69,128,50.34,61.66A8,8,0,0,1,61.66,50.34L128,116.69l66.34-66.35a8,8,0,0,1,11.32,11.32L139.31,128Z"/></svg>
        </button>
    </div>


    <script>
    (function () {
        /* Wait until Quill is available. */
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

            /* Description editor. */
            var omschrijvingHidden = document.getElementById('sj_omschrijving_hidden');
            var quillOmschrijving  = new Quill('#sj_quill_omschrijving', {
                theme: 'snow',
                modules: { toolbar: toolbarOptions },
                placeholder: 'Describe the role, responsibilities, requirements and what your organization offers...'
            });

            if (omschrijvingHidden && omschrijvingHidden.value) {
                quillOmschrijving.clipboard.dangerouslyPasteHTML(omschrijvingHidden.value);
            }

            quillOmschrijving.on('text-change', function () {
                if (omschrijvingHidden) {
                    omschrijvingHidden.value = quillOmschrijving.root.innerHTML;
                }
            });

            /* Sync hidden textarea before submit. */
            var form = document.querySelector('.sj-vp__form');
            if (form) {
                form.addEventListener('submit', function () {
                    if (omschrijvingHidden) omschrijvingHidden.value = quillOmschrijving.root.innerHTML;
                });
            }
        }

        initQuill();

        /* Package radio highlight. */
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

        /* Show selected file name. */
        document.querySelectorAll('.sj-vp__upload-input').forEach(function (fileInput) {
            var fileName = fileInput.closest('.sj-vp__upload').querySelector('.sj-vp__upload-name');
            if (!fileName) return;

            fileInput.addEventListener('change', function () {
                fileName.textContent = fileInput.files.length ? fileInput.files[0].name : 'No file selected';
            });
        });

        /* Job type multiselect. */
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
                rm.setAttribute('aria-label', 'Remove ' + opt.dataset.value);
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

        /* Initial sync after page reload with POST data. */
        msSync();
    })();
    </script>

    <?php
    return ob_get_clean();
}
