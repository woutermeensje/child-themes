<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [sc_quick_post]
 * Quick job posting via email address + URL.
 */
add_shortcode('sc_quick_post', 'sc_quick_post_shortcode');

function sc_quick_post_shortcode(): string {

    $success = false;
    $errors  = [];

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['sc_qp_nonce']) &&
        wp_verify_nonce($_POST['sc_qp_nonce'], 'sc_quick_post')
    ) {
        $email   = sanitize_email($_POST['qp_email']   ?? '');
        $url     = esc_url_raw($_POST['qp_url']        ?? '');
        $package = sanitize_text_field($_POST['qp_package'] ?? '');

        if (!is_email($email)) $errors[] = 'Please enter a valid email address.';
        if (!$url)             $errors[] = 'Please enter the URL of the job listing.';

        if (empty($errors)) {
            $body  = "New quick post request:\n\n";
            $body .= "Package: $package\n";
            $body .= "Email: $email\n";
            $body .= "Job URL: $url\n";

            $headers = ['Content-Type: text/plain; charset=UTF-8'];
            $to = array_unique(array_filter([
                'support@sustainablejobs.com',
                get_option('admin_email'),
            ]));
            wp_mail($to, 'Quick post request', $body, $headers);

            $confirmation_body  = "Dear applicant,\n\n";
            $confirmation_body .= "Thank you for your job posting request via Sustainablejobs.com.\n\n";
            $confirmation_body .= "We have received your request:\n";
            $confirmation_body .= "Package: " . ($package ?: 'Quick post') . "\n";
            $confirmation_body .= "Job URL: $url\n\n";
            $confirmation_body .= "We will pick this up as soon as possible and contact you if we need anything else.\n\n";
            $confirmation_body .= "If you have questions, feel free to contact us at support@sustainablejobs.com.\n\n";
            $confirmation_body .= "Kind regards,\n";
            $confirmation_body .= "Sustainablejobs.com";

            wp_mail(
                $email,
                'Confirmation of your job posting request on Sustainablejobs.com',
                $confirmation_body,
                $headers
            );

            $post_id = wp_insert_post([
                'post_title'  => $url,
                'post_status' => 'pending',
                'post_type'   => 'sc_job_submission',
                'post_author' => 0,
            ]);

            if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, '_sc_email',   $email);
                update_post_meta($post_id, '_sc_package', $package ?: 'Quick post');
                update_post_meta($post_id, '_sc_job_url', $url);
            }

            wp_redirect(home_url('/job-posting-confirmation/'));
            exit;
        }
    }

    ob_start();

    if ($success): ?>

    <div class="sj-vp-notice sj-vp-notice--success">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M173.66,98.34a8,8,0,0,1,0,11.32l-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35A8,8,0,0,1,173.66,98.34ZM232,128A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"/></svg>
        <div>
            <strong>Request received!</strong>
            <p>We will publish your job as soon as possible. You will hear from us.</p>
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
                <h2 class="sj-vp__title">Quick Job Post</h2>
                <p class="sj-vp__subtitle">Already familiar with our platform (or don't want to fill in the full form)? Just share the link to your job listing and we'll take care of the rest.</p>
            </header>

            <form method="post" class="sj-vp__form" novalidate>
                <?php wp_nonce_field('sc_quick_post', 'sc_qp_nonce'); ?>

                <div class="sj-vp__section">
                    <div class="sj-vp__grid sj-vp__grid--1">

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="qp_package">Package <span class="sj-vp__req">*</span></label>
                            <select name="qp_package" id="qp_package" class="sj-vp__input" required>
                                <option value="" disabled <?php selected($_POST['qp_package'] ?? '', ''); ?>>Choose a package...</option>
                                <option value="Standard Job: €275 excl. VAT"               <?php selected($_POST['qp_package'] ?? '', 'Standard Job: €275 excl. VAT'); ?>>Standard — €275.00 excl. VAT</option>
                                <option value="Spotlight Job: €375 excl. VAT"              <?php selected($_POST['qp_package'] ?? '', 'Spotlight Job: €375 excl. VAT'); ?>>Spotlight — €375.00 excl. VAT</option>
                                <option value="Internship & Volunteering: Free"            <?php selected($_POST['qp_package'] ?? '', 'Internship & Volunteering: Free'); ?>>Internship & Volunteering — Free</option>
                                <option value="We are a member of Sustainablejobs.com: Free" <?php selected($_POST['qp_package'] ?? '', 'We are a member of Sustainablejobs.com: Free'); ?>>We are a member — Free for members</option>
                                <option value="We have a credit bundle"                    <?php selected($_POST['qp_package'] ?? '', 'We have a credit bundle'); ?>>Credit bundle — Via credit bundle</option>
                            </select>
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="qp_email">Email address <span class="sj-vp__req">*</span></label>
                            <input type="email" name="qp_email" id="qp_email" class="sj-vp__input"
                                   value="<?php echo esc_attr($_POST['qp_email'] ?? ''); ?>"
                                   placeholder="jane@company.com" required>
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="qp_url">Link to the job listing <span class="sj-vp__req">*</span></label>
                            <input type="url" name="qp_url" id="qp_url" class="sj-vp__input"
                                   value="<?php echo esc_attr($_POST['qp_url'] ?? ''); ?>"
                                   placeholder="https://yourcompany.com/jobs/job-title" required>
                        </div>

                    </div>
                </div>

                <footer class="sj-vp__footer">
                    <button type="submit" class="sj-vp__submit">Submit job listing</button>
                    <p class="sj-vp__footer-note">Even easier? Send the job directly by email to <a href="mailto:support@sustainablejobs.com">support@sustainablejobs.com</a>.</p>
                </footer>

            </form>

        </div>
    </div>

    <?php endif;

    return ob_get_clean();
}
