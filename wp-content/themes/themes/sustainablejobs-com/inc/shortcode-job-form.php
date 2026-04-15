<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [sc_post_job]
 * Job posting form with Quill rich text editor.
 */
add_shortcode('sc_post_job', 'sc_post_job_shortcode');

function sc_post_job_shortcode(): string {

    /* ── Processing ─────────────────────────────────────── */
    $success = false;
    $errors  = [];

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['sc_pj_nonce']) &&
        wp_verify_nonce($_POST['sc_pj_nonce'], 'sc_post_job')
    ) {
        $first_name   = sanitize_text_field($_POST['first_name']    ?? '');
        $last_name    = sanitize_text_field($_POST['last_name']      ?? '');
        $company_name = sanitize_text_field($_POST['company_name']   ?? '');
        $email        = sanitize_email($_POST['email']               ?? '');
        $package      = sanitize_text_field($_POST['package']        ?? '');
        $job_title    = sanitize_text_field($_POST['job_title']      ?? '');
        $location     = sanitize_text_field($_POST['location']       ?? '');
        $job_type     = array_map('sanitize_text_field', (array)($_POST['job_type'] ?? []));
        $description  = wp_kses_post($_POST['description']          ?? '');
        $extra_info   = wp_kses_post($_POST['extra_info']            ?? '');
        $referral     = sanitize_text_field($_POST['referral']       ?? '');

        if (!$first_name)     $errors[] = 'Please enter your first name.';
        if (!$last_name)      $errors[] = 'Please enter your last name.';
        if (!$company_name)   $errors[] = 'Please enter your company name.';
        if (!is_email($email)) $errors[] = 'Please enter a valid email address.';
        if (!$job_title)      $errors[] = 'Please enter a job title.';
        if (!$description)    $errors[] = 'Please enter a job description.';

        if (empty($errors)) {
            $attachments = [];

            if (!empty($_FILES['company_logo']['tmp_name'])) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';
                require_once ABSPATH . 'wp-admin/includes/image.php';

                $upload = media_handle_upload('company_logo', 0);
                if (!is_wp_error($upload)) {
                    $path = get_attached_file($upload);
                    if (file_exists($path)) {
                        $attachments[] = $path;
                    }
                }
            }

            $job_type_str = implode(', ', $job_type);
            $body  = "New job posting via the form:\n\n";
            $body .= "Package: $package\n";
            $body .= "Name: $first_name $last_name\n";
            $body .= "Company: $company_name\n";
            $body .= "Email: $email\n";
            $body .= "Job title: $job_title\n";
            $body .= "Location: $location\n";
            $body .= "Job type: $job_type_str\n";
            $body .= "How did you find us: $referral\n\n";
            $body .= "--- Job description ---\n" . strip_tags($description) . "\n\n";
            $body .= "--- Additional information ---\n" . strip_tags($extra_info) . "\n";

            $headers = ['Content-Type: text/plain; charset=UTF-8'];

            wp_mail(
                'support@sustainablejobs.com',
                "New job posting: $job_title",
                $body,
                $headers,
                $attachments
            );

            $confirmation_body  = "Dear $first_name,\n\n";
            $confirmation_body .= "Thank you for posting your job on Sustainablejobs.com.\n\n";
            $confirmation_body .= "We have received your job posting:\n";
            $confirmation_body .= "Job title: $job_title\n";
            $confirmation_body .= "Company: $company_name\n";
            $confirmation_body .= "Location: $location\n";
            $confirmation_body .= "Package: $package\n\n";
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

            /* ── Save submission as CPT ──────────────────── */
            $post_id = wp_insert_post([
                'post_title'  => sanitize_text_field($job_title),
                'post_status' => 'pending',
                'post_type'   => 'sc_job_submission',
                'post_author' => 0,
            ]);

            if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, '_sc_package',      $package);
                update_post_meta($post_id, '_sc_first_name',   $first_name);
                update_post_meta($post_id, '_sc_last_name',    $last_name);
                update_post_meta($post_id, '_sc_company_name', $company_name);
                update_post_meta($post_id, '_sc_email',        $email);
                update_post_meta($post_id, '_sc_location',     $location);
                update_post_meta($post_id, '_sc_job_type',     $job_type);
                update_post_meta($post_id, '_sc_description',  $description);
                update_post_meta($post_id, '_sc_extra_info',   $extra_info);
                update_post_meta($post_id, '_sc_referral',     $referral);

                if (!empty($upload) && !is_wp_error($upload)) {
                    update_post_meta($post_id, '_sc_logo_id', $upload);
                }
            }

            /* ── Create draft job_listing in WP Job Manager ── */
            $content = $description;
            if ($extra_info) {
                $content .= '<h3>Additional information</h3>' . $extra_info;
            }

            $job_id = wp_insert_post([
                'post_title'   => sanitize_text_field($job_title),
                'post_content' => $content,
                'post_status'  => 'draft',
                'post_type'    => 'job_listing',
                'post_author'  => 1,
            ]);

            if ($job_id && !is_wp_error($job_id)) {
                update_post_meta($job_id, '_job_location',    $location);
                update_post_meta($job_id, '_company_name',    $company_name);
                update_post_meta($job_id, '_company_email',   $email);
                update_post_meta($job_id, '_job_salary',      '');
                update_post_meta($job_id, '_filled',          0);
                update_post_meta($job_id, '_featured',        0);
                update_post_meta($job_id, '_job_expires',     '');

                if (!empty($upload) && !is_wp_error($upload)) {
                    update_post_meta($job_id, '_company_logo', wp_get_attachment_url($upload));
                    set_post_thumbnail($job_id, $upload);
                }

                if (!empty($job_type)) {
                    $term_ids = [];
                    foreach ($job_type as $type_name) {
                        $term = get_term_by('name', $type_name, 'job_listing_type');
                        if ($term) {
                            $term_ids[] = $term->term_id;
                        }
                    }
                    if (!empty($term_ids)) {
                        wp_set_post_terms($job_id, $term_ids, 'job_listing_type');
                    }
                }

                update_post_meta($job_id, '_sc_package', $package);
            }

            wp_redirect(home_url('/job-posting-confirmation/'));
            exit;
        }
    }

    /* ── Build HTML ──────────────────────────────────────── */
    ob_start();

    if ($success): ?>

    <div class="sj-vp-notice sj-vp-notice--success">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M173.66,98.34a8,8,0,0,1,0,11.32l-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35A8,8,0,0,1,173.66,98.34ZM232,128A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"/></svg>
        <div>
            <strong>Job posted successfully!</strong>
            <p>We will get back to you as soon as possible.</p>
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

            <header class="sj-vp__header">
                <h2 class="sj-vp__title">Post a job</h2>
                <p class="sj-vp__subtitle">Fill in the details and we will publish your job as soon as possible. You will receive an invoice by email after publication.</p>
            </header>

            <form method="post" class="sj-vp__form" enctype="multipart/form-data" novalidate>
                <?php wp_nonce_field('sc_post_job', 'sc_pj_nonce'); ?>

                <!-- Package -->
                <div class="sj-vp__section">
                    <p class="sj-vp__section-title">Choose your package</p>
                    <div class="sj-vp__grid sj-vp__grid--2">
                        <?php
                        $packages = [
                            'Standard Job: €275 excl. VAT'         => ['label' => 'Standard',          'price' => '€275.00 excl. VAT'],
                            'Spotlight Job: €375 excl. VAT'         => ['label' => 'Spotlight',         'price' => '€375.00 excl. VAT'],
                            'Internship & Volunteering: Free'       => ['label' => 'Internship & Volunteering', 'price' => 'Free'],
                            'We are a member of Sustainablejobs.com: Free' => ['label' => 'We are a member', 'price' => 'Free for members'],
                            'We have a credit bundle'               => ['label' => 'Credit bundle',     'price' => 'Via credit bundle'],
                        ];
                        $selected_package = $_POST['package'] ?? 'Standard Job: €275 excl. VAT';
                        foreach ($packages as $value => $info): ?>
                        <label class="sj-vp__pakket<?php echo ($selected_package === $value) ? ' is-selected' : ''; ?>">
                            <input type="radio" name="package" value="<?php echo esc_attr($value); ?>"
                                   <?php checked($selected_package, $value); ?> class="sj-vp__pakket-radio">
                            <span class="sj-vp__pakket-inner">
                                <span class="sj-vp__pakket-name"><?php echo esc_html($info['label']); ?></span>
                                <span class="sj-vp__pakket-prijs"><?php echo esc_html($info['price']); ?></span>
                            </span>
                            <span class="sj-vp__pakket-check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 256 256" fill="currentColor"><path d="M229.66,77.66l-128,128a8,8,0,0,1-11.32,0l-56-56a8,8,0,0,1,11.32-11.32L96,188.69,218.34,66.34a8,8,0,0,1,11.32,11.32Z"/></svg>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="sj-vp__pakket-note">Interested in a credit bundle or membership? View <a href="<?php echo esc_url(home_url('/pricing/')); ?>">our pricing</a>.</p>
                </div>

                <!-- Contact details -->
                <div class="sj-vp__section">
                    <p class="sj-vp__section-title">Contact details</p>
                    <div class="sj-vp__grid sj-vp__grid--2">
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sc_first_name">First name <span class="sj-vp__req">*</span></label>
                            <input type="text" name="first_name" id="sc_first_name" class="sj-vp__input"
                                   value="<?php echo esc_attr($_POST['first_name'] ?? ''); ?>" placeholder="Jane" required>
                        </div>
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sc_last_name">Last name <span class="sj-vp__req">*</span></label>
                            <input type="text" name="last_name" id="sc_last_name" class="sj-vp__input"
                                   value="<?php echo esc_attr($_POST['last_name'] ?? ''); ?>" placeholder="Smith" required>
                        </div>
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sc_company_name">Company name <span class="sj-vp__req">*</span></label>
                            <input type="text" name="company_name" id="sc_company_name" class="sj-vp__input"
                                   value="<?php echo esc_attr($_POST['company_name'] ?? ''); ?>" placeholder="Your organisation" required>
                        </div>
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sc_email">Email address <span class="sj-vp__req">*</span></label>
                            <input type="email" name="email" id="sc_email" class="sj-vp__input"
                                   value="<?php echo esc_attr($_POST['email'] ?? ''); ?>" placeholder="jane@company.com" required>
                        </div>
                    </div>
                </div>

                <!-- Job information -->
                <div class="sj-vp__section">
                    <p class="sj-vp__section-title">Job information</p>
                    <div class="sj-vp__grid sj-vp__grid--1">

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sc_job_title">Job title <span class="sj-vp__req">*</span></label>
                            <input type="text" name="job_title" id="sc_job_title" class="sj-vp__input"
                                   value="<?php echo esc_attr($_POST['job_title'] ?? ''); ?>"
                                   placeholder="E.g. Sustainability Manager" required>
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sc_location">Location</label>
                            <input type="text" name="location" id="sc_location" class="sj-vp__input"
                                   value="<?php echo esc_attr($_POST['location'] ?? ''); ?>"
                                   placeholder="Amsterdam, Hybrid, Remote...">
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" id="sc_job_type_label">Job type</label>
                            <?php
                            $types = ['Full-time', 'Part-time', 'Project', 'Internship', 'Volunteering'];
                            $selected_types = (array)($_POST['job_type'] ?? []);
                            ?>
                            <div class="sj-vp__ms-hidden" aria-hidden="true">
                                <?php foreach ($types as $t): ?>
                                <input type="checkbox" name="job_type[]" value="<?php echo esc_attr($t); ?>"
                                       class="sj-vp__ms-cb"
                                       <?php checked(in_array($t, $selected_types)); ?>>
                                <?php endforeach; ?>
                            </div>
                            <div class="sj-vp__ms" id="sc_job_type_ms" aria-labelledby="sc_job_type_label" role="group">
                                <div class="sj-vp__ms-trigger" tabindex="0" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="sj-vp__ms-placeholder">Select type(s)...</span>
                                    <span class="sj-vp__ms-tags"></span>
                                    <svg class="sj-vp__ms-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z"/></svg>
                                </div>
                                <ul class="sj-vp__ms-dropdown" role="listbox" aria-multiselectable="true">
                                    <?php foreach ($types as $t): ?>
                                    <li class="sj-vp__ms-option<?php echo in_array($t, $selected_types) ? ' is-selected' : ''; ?>"
                                        role="option"
                                        aria-selected="<?php echo in_array($t, $selected_types) ? 'true' : 'false'; ?>"
                                        data-value="<?php echo esc_attr($t); ?>">
                                        <span class="sj-vp__ms-opt-check" aria-hidden="true">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 256 256" fill="currentColor"><path d="M229.66,77.66l-128,128a8,8,0,0,1-11.32,0l-56-56a8,8,0,0,1,11.32-11.32L96,188.69,218.34,66.34a8,8,0,0,1,11.32,11.32Z"/></svg>
                                        </span>
                                        <span class="sj-vp__ms-opt-text"><?php echo esc_html($t); ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>

                        <!-- Quill: description -->
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sc_description_hidden">Job description <span class="sj-vp__req">*</span></label>
                            <div class="sj-vp__quill-wrap">
                                <div id="sc_quill_description" class="sj-vp__quill-editor" style="min-height:220px;"></div>
                            </div>
                            <textarea name="description" id="sc_description_hidden" class="sj-vp__quill-hidden" aria-hidden="true"><?php echo esc_textarea($_POST['description'] ?? ''); ?></textarea>
                            <span class="sj-vp__hint">Describe the role, requirements, and what your organisation offers.</span>
                        </div>

                        <!-- Quill: additional information -->
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sc_extra_info_hidden">Additional information <span class="sj-vp__opt">(optional)</span></label>
                            <div class="sj-vp__quill-wrap">
                                <div id="sc_quill_extra_info" class="sj-vp__quill-editor" style="min-height:140px;"></div>
                            </div>
                            <textarea name="extra_info" id="sc_extra_info_hidden" class="sj-vp__quill-hidden" aria-hidden="true"><?php echo esc_textarea($_POST['extra_info'] ?? ''); ?></textarea>
                            <span class="sj-vp__hint">Secondary benefits, company culture, or other relevant information.</span>
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sc_company_logo">Upload company logo</label>
                            <label class="sj-vp__upload" for="sc_company_logo">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M240,136v64a16,16,0,0,1-16,16H32a16,16,0,0,1-16-16V136a16,16,0,0,1,16-16H80a8,8,0,0,1,0,16H32v64H224V136H176a8,8,0,0,1,0-16h48A16,16,0,0,1,240,136ZM85.66,77.66,120,43.31V128a8,8,0,0,0,16,0V43.31l34.34,34.35a8,8,0,0,0,11.32-11.32l-48-48a8,8,0,0,0-11.32,0l-48,48A8,8,0,0,0,85.66,77.66Z"/></svg>
                                <span class="sj-vp__upload-label">Choose file</span>
                                <span class="sj-vp__upload-name">No file chosen</span>
                                <input type="file" name="company_logo" id="sc_company_logo" accept="image/*" class="sj-vp__upload-input">
                            </label>
                            <span class="sj-vp__hint">PNG or JPG, preferably square. Max. 2 MB.</span>
                        </div>

                    </div>
                </div>

                <footer class="sj-vp__footer">
                    <button type="submit" class="sj-vp__submit">Submit job posting</button>
                    <p class="sj-vp__footer-note">You will receive an invoice by email after publication. Questions? Email <a href="mailto:support@sustainablejobs.com">support@sustainablejobs.com</a>.</p>
                </footer>

            </form>

        </div>
    </div>

    <?php endif; ?>

    <!-- Quick post bar — sticky bottom -->
    <div class="sj-vp-snel" id="sc-quick-bar">
        <div class="sj-vp-snel__text">
            <h2 class="sj-vp-snel__title">Want to post even faster?</h2>
            <p class="sj-vp-snel__desc">Just share the job link and we'll take care of the rest.</p>
        </div>
        <div class="sj-vp-snel__contact">
            <a href="<?php echo esc_url(home_url('/quick-post/')); ?>" class="sj-vp-snel__btn">Quick Post</a>
        </div>
        <button class="sj-vp-snel__close" aria-label="Close" onclick="document.getElementById('sc-quick-bar').classList.add('is-hidden')">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M205.66,194.34a8,8,0,0,1-11.32,11.32L128,139.31,61.66,205.66a8,8,0,0,1-11.32-11.32L116.69,128,50.34,61.66A8,8,0,0,1,61.66,50.34L128,116.69l66.34-66.35a8,8,0,0,1,11.32,11.32L139.31,128Z"/></svg>
        </button>
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
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['link'],
                ['clean']
            ];

            var descriptionHidden = document.getElementById('sc_description_hidden');
            var quillDescription  = new Quill('#sc_quill_description', {
                theme: 'snow',
                modules: { toolbar: toolbarOptions },
                placeholder: 'Describe the role, responsibilities, requirements, and what your organisation offers...'
            });

            if (descriptionHidden && descriptionHidden.value) {
                quillDescription.clipboard.dangerouslyPasteHTML(descriptionHidden.value);
            }

            quillDescription.on('text-change', function () {
                if (descriptionHidden) {
                    descriptionHidden.value = quillDescription.root.innerHTML;
                }
            });

            var extraInfoHidden = document.getElementById('sc_extra_info_hidden');
            var quillExtraInfo  = new Quill('#sc_quill_extra_info', {
                theme: 'snow',
                modules: { toolbar: toolbarOptions },
                placeholder: 'Secondary benefits, company culture, or other relevant information...'
            });

            if (extraInfoHidden && extraInfoHidden.value) {
                quillExtraInfo.clipboard.dangerouslyPasteHTML(extraInfoHidden.value);
            }

            quillExtraInfo.on('text-change', function () {
                if (extraInfoHidden) {
                    extraInfoHidden.value = quillExtraInfo.root.innerHTML;
                }
            });

            var form = document.querySelector('.sj-vp__form');
            if (form) {
                form.addEventListener('submit', function () {
                    if (descriptionHidden) descriptionHidden.value = quillDescription.root.innerHTML;
                    if (extraInfoHidden)   extraInfoHidden.value   = quillExtraInfo.root.innerHTML;
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

        var fileInput = document.getElementById('sc_company_logo');
        var fileName  = document.querySelector('.sj-vp__upload-name');
        if (fileInput && fileName) {
            fileInput.addEventListener('change', function () {
                fileName.textContent = fileInput.files.length ? fileInput.files[0].name : 'No file chosen';
            });
        }

        var ms          = document.getElementById('sc_job_type_ms');
        var trigger     = ms && ms.querySelector('.sj-vp__ms-trigger');
        var tagsEl      = ms && ms.querySelector('.sj-vp__ms-tags');
        var placeholder = ms && ms.querySelector('.sj-vp__ms-placeholder');
        var options     = ms ? Array.from(ms.querySelectorAll('.sj-vp__ms-option')) : [];
        var checkboxes  = ms ? Array.from(ms.closest('.sj-vp__field').querySelectorAll('.sj-vp__ms-cb')) : [];

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

        function msOpen() { ms.classList.add('is-open'); trigger.setAttribute('aria-expanded', 'true'); }
        function msClose() { ms.classList.remove('is-open'); trigger.setAttribute('aria-expanded', 'false'); }

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

        msSync();
    })();
    </script>

    <?php
    return ob_get_clean();
}
