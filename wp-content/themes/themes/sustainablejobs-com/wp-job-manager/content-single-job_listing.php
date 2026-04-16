<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $post;

$post_id = isset( $post->ID ) ? (int) $post->ID : 0;
?>

<!-- TOP SECTION -->
<div class="update-header">
  <div class="opdrachten-update">
    <p>Stay up to date with the latest jobs!</p>
    <a href="/job-alerts/" class="update-link">Job Alert</a>
  </div>
</div>

<?php if ( $post_id && job_manager_user_can_view_job_listing( $post_id ) ) :

    $salary    = get_post_meta($post_id, '_job_salary_range', true);
    $hours     = get_post_meta($post_id, '_job_hours_per_week', true);
    $location  = get_post_meta($post_id, '_job_location', true);
    $cover_image = get_post_meta($post_id, '_cover_image', true);
    $company   = get_the_company_name();
    $con_first = get_post_meta($post_id, '_job_contact_firstname', true);
    $con_last  = get_post_meta($post_id, '_job_contact_lastname', true);
    $con_email = get_post_meta($post_id, '_job_contact_email', true);

    /* ── Ask a question: form processing ── */
    $vraag_success = false;
    $vraag_error   = '';
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['sc_vraag_nonce']) &&
        wp_verify_nonce($_POST['sc_vraag_nonce'], 'sc_ask_question_' . $post_id)
    ) {
        $v_first   = sanitize_text_field($_POST['vraag_firstname']  ?? '');
        $v_last    = sanitize_text_field($_POST['vraag_lastname']   ?? '');
        $v_email   = sanitize_email($_POST['vraag_email']           ?? '');
        $v_phone   = sanitize_text_field($_POST['vraag_phone']      ?? '');
        $v_message = sanitize_textarea_field($_POST['vraag_tekst']  ?? '');
        $to        = $con_email ?: 'support@sustainablejobs.com';

        $attachments = [];
        if (!empty($_FILES['vraag_cv']['tmp_name'])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $upload = media_handle_upload('vraag_cv', 0);
            if (!is_wp_error($upload)) {
                $path = get_attached_file($upload);
                if (file_exists($path)) $attachments[] = $path;
            }
        }

        if ($v_first && is_email($v_email) && $v_message) {
            $subject = 'Question about job: ' . get_the_title($post_id);
            $body    = "Question via the job listing page:\n\nFrom: $v_first $v_last <$v_email>";
            if ($v_phone) $body .= "\nPhone: $v_phone";
            $body   .= "\n\n$v_message";
            wp_mail($to, $subject, $body, [
                'Content-Type: text/plain; charset=UTF-8',
                "Reply-To: $v_first $v_last <$v_email>",
                'Bcc: support@sustainablejobs.com',
            ], $attachments);
            $vraag_success = true;
        } else {
            $vraag_error = 'Please fill in all required fields.';
        }
    }
?>

    <div class="sj-single-layout">

        <!-- Left column: job content -->
        <div class="sj-single-layout__main">
            <div class="single_job_listing">
                <?php if ( get_option( 'job_manager_hide_expired_content', 1 ) && 'expired' === $post->post_status ) : ?>

                    <div class="job-manager-info">
                        <?php _e( 'This listing has expired.', 'wp-job-manager' ); ?>
                    </div>

                <?php else : ?>

                    <div class="content-part-job-description">
                        <div class="top-div">

                            <?php if ( ! empty( $cover_image ) ) : ?>
                            <div class="sj-single-featured-image">
                                <img
                                    src="<?php echo esc_url( $cover_image ); ?>"
                                    alt="<?php echo esc_attr( wpjm_get_the_job_title( $post_id ) ); ?>"
                                    class="sj-single-featured-image__img"
                                >
                            </div>
                            <?php endif; ?>

                            <div class="job-title">
                                <h1><?php wpjm_the_job_title(); ?></h1>
                            </div>

                            <div class="job_description">
                                <?php wpjm_the_job_description(); ?>
                            </div>

                            <?php $company_website = get_post_meta( $post_id, '_company_website', true ); ?>
                            <?php if ( ! empty( $company_website ) ) : ?>
                                <div class="job-apply-button">
                                    <a href="<?php echo esc_url( $company_website ); ?>" class="apply-button" target="_blank" rel="noopener">
                                        Apply for this job!
                                    </a>
                                </div>
                            <?php else : ?>
                                <p>No employer link available</p>
                            <?php endif; ?>

                            <?php do_action( 'single_job_listing_end' ); ?>

                        </div>
                    </div>

                <?php endif; ?>
            </div>

            <!-- Ask a question block -->
            <div class="sj-vraag-blok">
                <h3 class="sj-vraag-blok__title">Ask a question to the contact person of this job listing.</h3>

                <?php if ($vraag_success): ?>
                    <p class="sj-sidebar__vraag-success">Your question has been sent! We will get back to you as soon as possible.</p>
                <?php else: ?>
                    <?php if ($vraag_error): ?>
                    <p class="sj-sidebar__vraag-error"><?php echo esc_html($vraag_error); ?></p>
                    <?php endif; ?>
                    <form method="post" class="sj-vraag-blok__form" enctype="multipart/form-data" novalidate>
                        <?php wp_nonce_field('sc_ask_question_' . $post_id, 'sc_vraag_nonce'); ?>

                        <div class="sj-vraag-blok__row">
                            <div class="sj-vraag-blok__field">
                                <label class="sj-vraag-blok__label" for="vraag_firstname">First name <span class="sj-vraag-req">*</span></label>
                                <input type="text" name="vraag_firstname" id="vraag_firstname"
                                       class="sj-vraag-blok__input"
                                       placeholder="First name" required
                                       value="<?php echo esc_attr($_POST['vraag_firstname'] ?? ''); ?>">
                            </div>
                            <div class="sj-vraag-blok__field">
                                <label class="sj-vraag-blok__label" for="vraag_lastname">Last name</label>
                                <input type="text" name="vraag_lastname" id="vraag_lastname"
                                       class="sj-vraag-blok__input"
                                       placeholder="Last name"
                                       value="<?php echo esc_attr($_POST['vraag_lastname'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="sj-vraag-blok__row">
                            <div class="sj-vraag-blok__field">
                                <label class="sj-vraag-blok__label" for="vraag_email">Email address <span class="sj-vraag-req">*</span></label>
                                <input type="email" name="vraag_email" id="vraag_email"
                                       class="sj-vraag-blok__input"
                                       placeholder="your@email.com" required
                                       value="<?php echo esc_attr($_POST['vraag_email'] ?? ''); ?>">
                            </div>
                            <div class="sj-vraag-blok__field">
                                <label class="sj-vraag-blok__label" for="vraag_phone">Phone number</label>
                                <input type="tel" name="vraag_phone" id="vraag_phone"
                                       class="sj-vraag-blok__input"
                                       placeholder="+44 7700 900000"
                                       value="<?php echo esc_attr($_POST['vraag_phone'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="sj-vraag-blok__field">
                            <label class="sj-vraag-blok__label">Your question or motivation <span class="sj-vraag-req">*</span></label>
                            <div class="sj-vraag-blok__quill-wrap">
                                <div id="sc_vraag_quill" style="min-height:160px;"></div>
                            </div>
                            <textarea name="vraag_tekst" id="sc_vraag_hidden" class="sj-vraag-blok__hidden" aria-hidden="true"><?php echo esc_textarea($_POST['vraag_tekst'] ?? ''); ?></textarea>
                        </div>

                        <div class="sj-vraag-blok__field">
                            <label class="sj-vraag-blok__label" for="vraag_cv">Upload CV <span style="font-weight:400;color:#6b7280;font-size:12px;">(optional)</span></label>
                            <label class="sj-vraag-blok__upload" for="vraag_cv">
                                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M213.66,82.34l-56-56A8,8,0,0,0,152,24H56A16,16,0,0,0,40,40V216a16,16,0,0,0,16,16H200a16,16,0,0,0,16-16V88A8,8,0,0,0,213.66,82.34ZM160,51.31,188.69,80H160ZM200,216H56V40h88V88a8,8,0,0,0,8,8h48V216Zm-72-96a8,8,0,0,1,8,8v16h16a8,8,0,0,1,0,16H136v16a8,8,0,0,1-16,0V160H104a8,8,0,0,1,0-16h16V128A8,8,0,0,1,128,120Z"/></svg>
                                <span class="sj-vraag-blok__upload-label">Choose your CV</span>
                                <span class="sj-vraag-blok__upload-name" id="sc_vraag_cv_name">No file chosen</span>
                                <input type="file" name="vraag_cv" id="vraag_cv" accept=".pdf,.doc,.docx"
                                       class="sj-vraag-blok__upload-input"
                                       onchange="document.getElementById('sc_vraag_cv_name').textContent = this.files[0]?.name || 'No file chosen'">
                            </label>
                            <span class="sj-vraag-blok__hint">PDF, Word. Max. 5 MB.</span>
                        </div>

                        <button type="submit" class="sj-vraag-blok__submit">Send</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right column: sidebar -->
        <aside class="sj-single-layout__sidebar">

            <!-- Block 1: Job details -->
            <div class="sj-single-sidebar">
                <p class="sj-sidebar__block-title">Job details</p>
                <div class="sj-sidebar__details">
                    <?php if ($company): ?>
                    <div class="sj-sidebar__detail-row">
                        <span class="sj-sidebar__detail-label">Company</span>
                        <span class="sj-sidebar__detail-value"><?php echo esc_html($company); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php
                    $types = wpjm_get_the_job_types();
                    if (!empty($types)):
                    ?>
                    <div class="sj-sidebar__detail-row">
                        <span class="sj-sidebar__detail-label">Job type</span>
                        <span class="sj-sidebar__detail-value">
                            <?php foreach ($types as $type): ?>
                            <span class="sj-sidebar__chip"><?php echo esc_html($type->name); ?></span>
                            <?php endforeach; ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    <?php if ($salary): ?>
                    <div class="sj-sidebar__detail-row">
                        <span class="sj-sidebar__detail-label">Salary</span>
                        <span class="sj-sidebar__detail-value"><?php echo esc_html($salary); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($location): ?>
                    <div class="sj-sidebar__detail-row">
                        <span class="sj-sidebar__detail-label">Location</span>
                        <span class="sj-sidebar__detail-value"><?php echo esc_html($location); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($hours): ?>
                    <div class="sj-sidebar__detail-row">
                        <span class="sj-sidebar__detail-label">Hours/week</span>
                        <span class="sj-sidebar__detail-value"><?php echo esc_html($hours); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php
                    $sectors = wp_get_post_terms($post_id, 'job_sector', ['fields' => 'names']);
                    if (!empty($sectors) && !is_wp_error($sectors)):
                    ?>
                    <div class="sj-sidebar__detail-row">
                        <span class="sj-sidebar__detail-label">Sector</span>
                        <span class="sj-sidebar__detail-value"><?php echo esc_html(implode(', ', $sectors)); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php
                    $countries = wp_get_post_terms($post_id, 'job_country', ['fields' => 'names']);
                    if (!empty($countries) && !is_wp_error($countries)):
                    ?>
                    <div class="sj-sidebar__detail-row">
                        <span class="sj-sidebar__detail-label">Country / Region</span>
                        <span class="sj-sidebar__detail-value"><?php echo esc_html(implode(', ', $countries)); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($con_first || $con_last || $con_email): ?>
            <!-- Block 2: Contact person -->
            <div class="sj-single-sidebar">
                <p class="sj-sidebar__block-title">Contact person</p>
                <div class="sj-sidebar__contact">
                    <?php if ($con_first || $con_last): ?>
                    <p class="sj-sidebar__contact-name"><?php echo esc_html(trim("$con_first $con_last")); ?></p>
                    <?php endif; ?>
                    <?php if ($con_email): ?>
                    <a href="mailto:<?php echo esc_attr($con_email); ?>" class="sj-sidebar__contact-email"><?php echo esc_html($con_email); ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </aside>

    </div>

<?php else : ?>

    <?php get_job_manager_template_part( 'access-denied', 'single-job_listing' ); ?>

<?php endif; ?>

<!-- Sticky bottom bar -->
<div class="sj-vp-snel" id="sj-profiel-balk">
    <div class="sj-vp-snel__text">
        <h2 class="sj-vp-snel__title">Create a profile</h2>
        <p class="sj-vp-snel__desc">Did you know that with a profile on our platform you can also be approached by employers?</p>
    </div>
    <div class="sj-vp-snel__contact">
        <a href="https://platform.sustainablejobs.com/sign-up" class="sj-vp-snel__btn">Create a profile</a>
    </div>
    <button class="sj-vp-snel__close" aria-label="Close" onclick="document.getElementById('sj-profiel-balk').classList.add('is-hidden')">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M205.66,194.34a8,8,0,0,1-11.32,11.32L128,139.31,61.66,205.66a8,8,0,0,1-11.32-11.32L116.69,128,50.34,61.66A8,8,0,0,1,61.66,50.34L128,116.69l66.34-66.35a8,8,0,0,1,11.32,11.32L139.31,128Z"/></svg>
    </button>
</div>


<style>

/* ── Layout wrapper ──────────────────────────────────────── */
.sj-single-layout {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 24px;
    max-width: 1100px;
    width: 100%;
    margin: 24px auto;
    padding: 0 24px;
    box-sizing: border-box;
}

.sj-single-layout__main {
    min-width: 0;
}

/* ── Featured image ──────────────────────────────────────── */
.sj-single-featured-image {
    margin: -24px -24px 20px;
    overflow: hidden;
    border-radius: 5px 5px 0 0;
}

.sj-single-featured-image__img {
    width: 100%;
    height: auto;
    display: block;
    max-height: 360px;
    object-fit: cover;
}

/* ── Main job block ──────────────────────────────────────── */
.single_job_listing {
    background: #fff;
    border-radius: 5px;
    box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
    border: 1px solid #DEDEDE;
    padding: 24px;
}

/* ── Sidebar column ──────────────────────────────────────── */
.sj-single-layout__sidebar {
    display: flex;
    flex-direction: column;
    gap: 16px;
    position: sticky;
    top: 24px;
    align-self: start;
}

/* ── Sidebar blocks ──────────────────────────────────────── */
.sj-single-sidebar {
    background: #fff;
    border-radius: 5px;
    box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
    border: 1px solid #DEDEDE;
    padding: 20px;
}

.sj-sidebar__block-title {
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 14px;
    padding-bottom: 10px;
    border-bottom: 1px solid #DEDEDE;
}

/* ── Block 1: details ────────────────────────────────────── */
.sj-sidebar__details {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.sj-sidebar__detail-row {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.sj-sidebar__detail-label {
    font-family: 'Poppins', sans-serif;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #6b7280;
}

.sj-sidebar__detail-value {
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: #111827;
}

.sj-sidebar__chip {
    display: inline-block;
    padding: 3px 10px;
    background: #EEF3F0;
    border: 1px solid #c8ddd4;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    font-family: 'Poppins', sans-serif;
    color: #0A6B8D;
    margin-right: 4px;
}

/* ── Block 2: contact person ─────────────────────────────── */
.sj-sidebar__contact-name {
    font-family: 'Poppins', sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 4px;
}

.sj-sidebar__contact-email {
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    font-weight: 400;
    color: #0A6B8D;
    text-decoration: none;
}

.sj-sidebar__contact-email:hover { text-decoration: underline; }

/* ── Top banner ──────────────────────────────────────────── */
.update-header {
    max-width: 1100px;
    width: 100%;
    margin: 0 auto;
    padding: 0 24px;
    box-sizing: border-box;
}

.opdrachten-update {
    padding: 24px;
    margin: 24px 0;
    border: 1px solid #DEDEDE;
    border-radius: 5px;
    box-shadow: 0px 10px 40px -5px rgba(0,0,0,0.15);
    background-color: #ffffff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    box-sizing: border-box;
}

.opdrachten-update p {
    color: #333;
    margin: 0;
    font-weight: 700;
    font-size: 18px;
}

.update-link {
    color: #0A6B8D !important;
    background: #E0D0E1;
    font-family: Poppins;
    font-weight: 700;
    padding: 8px;
    border: 1px solid #0A6B8D !important;
    border-radius: 5px;
    text-decoration: none !important;
    white-space: nowrap;
}

.update-link:hover {
    background: #0A6B8D !important;
    color: #B9D1B3 !important;
}

/* ── Job title ───────────────────────────────────────────── */
.job-title h1 {
    padding-bottom: 10px;
    border-bottom: 1px solid #DEDEDE;
    font-family: Inter, sans-serif;
    font-weight: 700;
    font-size: 20px;
    padding-top: 20px;
}

/* ── Description ─────────────────────────────────────────── */
.job_description {
    font-family: Poppins;
    font-size: 14px;
    font-weight: 400;
    line-height: 1.6;
    color: var(--color-text);
    margin-top: 20px;
}

.job-manager-info {
    background-color: #ffdddd;
    color: #cc0000;
    border: 1px solid #cc0000;
    padding: 10px 15px;
    border-radius: 4px;
    text-align: center;
    margin-bottom: 20px;
    font-weight: 600;
}

/* ── Apply button ────────────────────────────────────────── */
.job-apply-button a {
    padding: 12px;
    color: var(--color-bg);
    background-color: var(--color-primary);
    border-radius: 5px;
    font-family: Balgin-Bold;
    text-decoration: none;
    display: inline-block;
    margin-top: 20px;
    border: 1px solid var(--color-primary);
}

.job-apply-button a:hover {
    background: var(--color-bg);
    color: var(--color-primary);
    border: 1px solid var(--color-primary);
}

/* ── Job card (list view) ────────────────────────────────── */
.job-listing-simple {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 16px;
    margin: 20px auto;
    border: 1px solid var(--color-bg);
    background-color: var(--color-bg);
    border-radius: 5px;
    box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
    transition: all 0.2s ease-in-out;
}

.job-listing-simple:hover { border: 1px solid var(--color-primary); }

.job-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100px;
    height: 100px;
    margin-left: -50px;
    background-color: var(--color-bg);
}

.job-logo img {
    width: 100px;
    height: 100px;
    object-fit: contain;
    border-radius: 5px;
    padding: 6px;
    box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
    border: 1px solid var(--color-border);
    transition: all 0.2s ease-in-out;
    background-color: var(--color-bg);
}

.job-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.job-title a {
    color: var(--color-text);
    text-decoration: none;
    transition: color 0.2s ease-in-out;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
}

.job-title a:hover {
    color: var(--color-primary);
    text-decoration: none;
}

.job-meta { margin-bottom: 5px; margin-top: 5px; }

.company-name {
    font-family: Poppins, sans-serif;
    font-weight: 700;
    font-size: 12px;
    color: var(--color-primary);
    border: 1px solid var(--color-primary);
    background-color: var(--color-accent);
    border-radius: 5px;
    padding: 5px 10px;
    cursor: pointer;
    margin-right: 5px;
    text-decoration: none;
}

a.google_map_link {
    font-family: Poppins, sans-serif;
    font-weight: 700;
    font-size: 12px;
    color: var(--color-primary);
    border: 1px solid var(--color-primary);
    background-color: var(--color-tertiary);
    border-radius: 5px;
    padding: 5px 10px;
    cursor: pointer;
    margin-right: 5px;
    text-decoration: none;
}

.job-manager .job-type, .job-types .job-type, .job_listing .job-type {
    font-family: Poppins, sans-serif;
    font-weight: 700;
    font-size: 12px;
    color: var(--color-bg);
    border: 1px solid var(--color-primary);
    background-color: var(--color-primary);
    border-radius: 5px;
    padding: 5px 10px;
    cursor: pointer;
    margin-right: 5px;
    text-decoration: none;
}

.job-description {
    font-size: 14px;
    line-height: 1.7;
    color: var(--color-text);
    font-family: Poppins, sans-serif;
    max-width: 100%;
    font-weight: 200;
}

.job-title-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-right: 10px;
}

.job-date {
    font-family: Poppins, sans-serif;
    font-size: 12px;
    color: var(--color-primary);
    font-weight: 200;
}

h1.entry-title { display: none; }

/* ── Ask a question block ────────────────────────────────── */
.sj-vraag-blok {
    margin-top: 20px;
    background: #fff;
    border: 1px solid #DEDEDE;
    border-radius: 5px;
    box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
    padding: 28px;
}

.sj-vraag-blok__title {
    font-family: 'Inter', sans-serif;
    font-size: 20px;
    font-weight: 700;
    color: var(--color-text, #333333);
    margin: 0 0 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #DEDEDE;
    line-height: 1.3;
}

.sj-vraag-blok__form {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.sj-vraag-blok__row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.sj-vraag-blok__field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.sj-vraag-blok__label {
    font-family: 'Poppins', sans-serif;
    font-size: 15px;
    font-weight: 400;
    color: #333333;
    line-height: 1.4;
}

.sj-vraag-req { color: var(--color-primary, #0A6B8D); margin-left: 2px; }

.sj-vraag-blok__input {
    width: 100% !important;
    padding: 8px 12px !important;
    font-family: 'Poppins', sans-serif !important;
    font-size: 15px !important;
    font-weight: 400 !important;
    color: var(--color-text, #333333) !important;
    background: #fff !important;
    border: 1px solid #DEDEDE !important;
    border-radius: 5px !important;
    outline: none !important;
    box-shadow: none !important;
    transition: border-color .2s ease, box-shadow .2s ease;
    box-sizing: border-box;
    height: auto !important;
    line-height: 1.5 !important;
}

.sj-vraag-blok__input:focus {
    border-color: var(--color-primary, #0A6B8D) !important;
    box-shadow: 0 0 0 3px rgba(10,107,141,.15) !important;
}

.sj-vraag-blok__input::placeholder {
    font-family: 'Poppins', sans-serif !important;
    font-size: 14px !important;
    font-weight: 300 !important;
    color: var(--color-text-muted, #777777) !important;
}

.sj-vraag-blok__hidden { display: none !important; }

.sj-vraag-blok__quill-wrap {
    border: 1px solid #DEDEDE;
    border-radius: 5px;
    overflow: hidden;
    transition: border-color .2s ease, box-shadow .2s ease;
}

.sj-vraag-blok__quill-wrap:focus-within {
    border-color: var(--color-primary, #0A6B8D);
    box-shadow: 0 0 0 3px rgba(10,107,141,.15);
}

.sj-vraag-blok__quill-wrap .ql-toolbar {
    border: none !important;
    border-bottom: 1px solid #DEDEDE !important;
    background: var(--color-bg-filter, #EEF3F0);
    padding: 8px 12px !important;
}

.sj-vraag-blok__quill-wrap .ql-container {
    border: none !important;
    font-family: 'Poppins', sans-serif !important;
    font-size: 15px !important;
}

.sj-vraag-blok__quill-wrap .ql-editor {
    padding: 14px !important;
    color: var(--color-text, #333333) !important;
    line-height: 1.7 !important;
}

.sj-vraag-blok__quill-wrap .ql-editor.ql-blank::before {
    color: var(--color-text-muted, #777777) !important;
    font-style: normal !important;
    font-weight: 300 !important;
}

.sj-vraag-blok__quill-wrap .ql-toolbar button:hover,
.sj-vraag-blok__quill-wrap .ql-toolbar button.ql-active { color: var(--color-primary, #0A6B8D) !important; }
.sj-vraag-blok__quill-wrap .ql-toolbar button:hover .ql-stroke,
.sj-vraag-blok__quill-wrap .ql-toolbar button.ql-active .ql-stroke { stroke: var(--color-primary, #0A6B8D) !important; }
.sj-vraag-blok__quill-wrap .ql-toolbar button:hover .ql-fill,
.sj-vraag-blok__quill-wrap .ql-toolbar button.ql-active .ql-fill { fill: var(--color-primary, #0A6B8D) !important; }

/* CV Upload */
.sj-vraag-blok__upload {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    min-height: 110px;
    padding: 24px 20px;
    background: #f0f7fb;
    border: 2px dashed var(--color-primary, #0A6B8D);
    border-radius: 5px;
    cursor: pointer;
    text-align: center;
    position: relative;
    transition: border-color .2s ease, background .2s ease;
    box-sizing: border-box;
}

.sj-vraag-blok__upload:hover {
    border-color: var(--color-secondary, #92E9AB);
    background: #e4f0f5;
}

.sj-vraag-blok__upload svg {
    color: var(--color-primary, #0A6B8D);
    flex-shrink: 0;
}

.sj-vraag-blok__upload-label {
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: var(--color-primary, #0A6B8D);
}

.sj-vraag-blok__upload-name {
    font-family: 'Poppins', sans-serif;
    font-size: 12px;
    font-weight: 400;
    color: var(--color-text-muted, #777777);
}

.sj-vraag-blok__upload-input {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 2;
}

.sj-vraag-blok__hint {
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    font-weight: 400;
    color: var(--color-text-muted, #777777);
    line-height: 1.5;
}

.sj-vraag-blok__submit {
    align-self: flex-start;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: 'Balgin-Bold', serif;
    font-size: 15px;
    font-weight: 600;
    background-color: var(--color-secondary, #92E9AB);
    border: 2px solid var(--color-secondary, #92E9AB);
    color: var(--color-primary, #0A6B8D) !important;
    padding: 10px 28px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color .15s ease, border-color .15s ease;
    text-decoration: none;
}

.sj-vraag-blok__submit:hover {
    background-color: var(--color-accent, #b9d1b3);
    border-color: var(--color-accent, #b9d1b3);
}

.sj-sidebar__vraag-success {
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    color: #065f46;
    background: #ecfdf5;
    border: 1px solid #6ee7b7;
    border-radius: 5px;
    padding: 10px 14px;
    margin: 0;
}

.sj-sidebar__vraag-error {
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    color: #991b1b;
    background: #fef2f2;
    border: 1px solid #fca5a5;
    border-radius: 5px;
    padding: 8px 12px;
    margin: 0 0 8px;
}

@media (max-width: 640px) {
    .sj-vraag-blok__row { grid-template-columns: 1fr; }
    .sj-vraag-blok__submit { width: 100%; align-self: stretch; }
}

/* ── Responsive ──────────────────────────────────────────── */
@media (max-width: 900px) {
    .sj-single-layout {
        grid-template-columns: 1fr;
    }

    .sj-single-layout__sidebar {
        order: -1;
        position: static;
    }

    .sj-single-layout__sidebar .sj-single-sidebar {
        position: static;
    }
}

@media (max-width: 768px) {
    .sj-single-layout {
        padding: 0 12px;
        margin: 16px auto;
    }

    .update-header {
        padding: 0 12px;
    }

    .opdrachten-update {
        flex-direction: column;
        align-items: stretch;
        gap: 16px;
        padding: 20px;
        text-align: left;
        margin: 16px 0;
    }

    .opdrachten-update p { font-size: 16px; line-height: 1.4; }

    .update-link {
        display: block;
        width: 100%;
        text-align: center;
        padding: 12px 16px;
        font-size: 16px;
    }
}

</style>

<script>
(function () {
    function initVraagQuill() {
        if (typeof Quill === 'undefined') { setTimeout(initVraagQuill, 80); return; }

        var hidden = document.getElementById('sc_vraag_hidden');
        if (!hidden) return;

        var quill = new Quill('#sc_vraag_quill', {
            theme: 'snow',
            placeholder: 'Ask your question or write a short motivation...',
            modules: { toolbar: [['bold','italic','underline'], [{'list':'ordered'},{'list':'bullet'}], ['link'], ['clean']] }
        });

        if (hidden.value) quill.root.innerHTML = hidden.value;

        quill.on('text-change', function () { hidden.value = quill.root.innerHTML; });

        var form = hidden.closest('form');
        if (form) form.addEventListener('submit', function () { hidden.value = quill.root.innerHTML; });
    }
    initVraagQuill();
})();
</script>
