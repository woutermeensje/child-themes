<?php
/**
 * Inhuren.com – Single job listing
 * Layout overgenomen van sustainablejobs-nl, aangepast aan de velden/taxonomieën van dit theme.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $post;

$post_id = isset( $post->ID ) ? (int) $post->ID : 0;
?>

<?php if ( $post_id && job_manager_user_can_view_job_listing( $post_id ) ) :

    $company   = get_the_company_name();
    $location  = get_the_job_location();
    $con_first = get_post_meta($post_id, '_contact_first_name', true);
    $con_last  = get_post_meta($post_id, '_contact_last_name', true);
    $con_email = get_post_meta($post_id, '_contact_email', true);

    $job_company_terms = get_the_terms($post_id, 'job_company');
    $job_company_term  = (!is_wp_error($job_company_terms) && !empty($job_company_terms)) ? $job_company_terms[0] : null;
    $job_company_url   = '';
    if ($job_company_term) {
        $term_link       = get_term_link($job_company_term);
        $job_company_url = is_wp_error($term_link) ? '' : $term_link;
    }
    $company_logo = has_post_thumbnail($post_id) ? get_the_post_thumbnail($post_id, 'thumbnail') : '';

    $vacancy_count = 0;
    if ($job_company_term) {
        $vacancy_q = new WP_Query([
            'post_type'      => 'job_listing',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'tax_query'      => [[
                'taxonomy' => 'job_company',
                'field'    => 'term_id',
                'terms'    => $job_company_term->term_id,
            ]],
        ]);
        $vacancy_count = $vacancy_q->found_posts;
        wp_reset_postdata();
    }

    /* ── Vraag-formulier verwerking ── */
    $vraag_success = false;
    $vraag_error   = '';
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['ih_vraag_nonce']) &&
        wp_verify_nonce($_POST['ih_vraag_nonce'], 'ih_stel_vraag_' . $post_id)
    ) {
        $v_naam    = sanitize_text_field($_POST['vraag_voornaam']   ?? '');
        $v_ach     = sanitize_text_field($_POST['vraag_achternaam'] ?? '');
        $v_email   = sanitize_email($_POST['vraag_email']           ?? '');
        $v_tel     = sanitize_text_field($_POST['vraag_telefoon']   ?? '');
        $v_vraag   = sanitize_textarea_field($_POST['vraag_tekst']  ?? '');
        $to        = $con_email ?: 'team@inhuren.com';

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

        if ($v_naam && is_email($v_email) && $v_vraag) {
            $subject = 'Vraag over vacature: ' . get_the_title($post_id);
            $body    = "Vraag via de vacaturepagina:\n\nVan: $v_naam $v_ach <$v_email>";
            if ($v_tel) $body .= "\nTelefoon: $v_tel";
            $body   .= "\n\n$v_vraag";
            wp_mail($to, $subject, $body, [
                'Content-Type: text/plain; charset=UTF-8',
                "Reply-To: $v_naam $v_ach <$v_email>",
            ], $attachments);
            $vraag_success = true;
        } else {
            $vraag_error = 'Vul alle verplichte velden in.';
        }
    }
?>

    <div class="ih-single-layout">

        <!-- Linker kolom: vacature inhoud -->
        <div class="ih-single-layout__main">
            <div class="single_job_listing">
                <?php if ( get_option( 'job_manager_hide_expired_content', 1 ) && 'expired' === $post->post_status ) : ?>

                    <div class="ih-expired">
                        <div class="ih-expired__notice">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            Deze vacature is verlopen
                        </div>

                        <h1 class="ih-expired__title"><?php echo esc_html(get_the_title($post_id)); ?></h1>

                        <?php if ($company || $location): ?>
                        <p class="ih-expired__meta">
                            <?php echo implode(' &nbsp;·&nbsp; ', array_filter([esc_html($company), esc_html($location)])); ?>
                        </p>
                        <?php endif; ?>

                        <p class="ih-expired__intro">Helaas is deze vacature niet meer beschikbaar. Bekijk het actuele aanbod voor vergelijkbare vacatures en opdrachten.</p>

                        <div class="ih-expired__all-link">
                            <a href="<?php echo esc_url(home_url('/vacatures/')); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                                Bekijk alle openstaande vacatures
                            </a>
                        </div>
                    </div>

                <?php elseif ('expired' === $post->post_status): ?>

                    <div class="ih-expired-banner">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Let op: deze vacature is verlopen en staat mogelijk niet meer open.
                        <a href="<?php echo esc_url(home_url('/vacatures/')); ?>">Bekijk actuele vacatures →</a>
                    </div>

                <?php else : ?>

                    <div class="content-part-job-description">
                        <div class="top-div">

                            <div class="job-title ih-single-title-row">
                                <h1><?php wpjm_the_job_title(); ?></h1>
                            </div>

                            <div class="job_description">
                                <?php wpjm_the_job_description(); ?>
                            </div>

                            <?php $company_website = get_post_meta( $post_id, '_company_website', true ); ?>
                            <?php if ( ! empty( $company_website ) ) : ?>
                                <div class="job-apply-button">
                                    <a href="<?php echo esc_url( $company_website ); ?>" class="apply-button" target="_blank" rel="noopener">
                                        Solliciteren op deze vacature!
                                    </a>
                                </div>
                            <?php else : ?>
                                <p>Geen link naar werkgever</p>
                            <?php endif; ?>

                            <?php do_action( 'single_job_listing_end' ); ?>

                        </div>
                    </div>

                <?php endif; ?>
            </div>
            <!-- Stel een vraag blok -->
            <div class="ih-vraag-blok" id="ih-vraag">
                <h3 class="ih-vraag-blok__title">Stel een vraag aan de contactpersoon van deze vacature.</h3>

                <?php if ($vraag_success): ?>
                    <p class="ih-vraag-blok__success">Je vraag is verstuurd! We nemen zo snel mogelijk contact met je op.</p>
                <?php else: ?>
                    <?php if ($vraag_error): ?>
                    <p class="ih-vraag-blok__error"><?php echo esc_html($vraag_error); ?></p>
                    <?php endif; ?>
                    <form method="post" class="ih-vraag-blok__form" enctype="multipart/form-data" novalidate>
                        <?php wp_nonce_field('ih_stel_vraag_' . $post_id, 'ih_vraag_nonce'); ?>

                        <div class="ih-vraag-blok__row">
                            <div class="ih-vraag-blok__field">
                                <label class="ih-vraag-blok__label" for="vraag_voornaam">Voornaam <span class="ih-vraag-req">*</span></label>
                                <input type="text" name="vraag_voornaam" id="vraag_voornaam"
                                       class="ih-vraag-blok__input"
                                       placeholder="Voornaam" required
                                       value="<?php echo esc_attr($_POST['vraag_voornaam'] ?? ''); ?>">
                            </div>
                            <div class="ih-vraag-blok__field">
                                <label class="ih-vraag-blok__label" for="vraag_achternaam">Achternaam</label>
                                <input type="text" name="vraag_achternaam" id="vraag_achternaam"
                                       class="ih-vraag-blok__input"
                                       placeholder="Achternaam"
                                       value="<?php echo esc_attr($_POST['vraag_achternaam'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="ih-vraag-blok__row">
                            <div class="ih-vraag-blok__field">
                                <label class="ih-vraag-blok__label" for="vraag_email">E-mailadres <span class="ih-vraag-req">*</span></label>
                                <input type="email" name="vraag_email" id="vraag_email"
                                       class="ih-vraag-blok__input"
                                       placeholder="jouw@emailadres.nl" required
                                       value="<?php echo esc_attr($_POST['vraag_email'] ?? ''); ?>">
                            </div>
                            <div class="ih-vraag-blok__field">
                                <label class="ih-vraag-blok__label" for="vraag_telefoon">Telefoonnummer</label>
                                <input type="tel" name="vraag_telefoon" id="vraag_telefoon"
                                       class="ih-vraag-blok__input"
                                       placeholder="+31 6 12345678"
                                       value="<?php echo esc_attr($_POST['vraag_telefoon'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="ih-vraag-blok__field">
                            <label class="ih-vraag-blok__label">Je vraag <span class="ih-vraag-req">*</span></label>
                            <div class="ih-vraag-blok__quill-wrap">
                                <div id="ih_vraag_quill" style="min-height:160px;"></div>
                            </div>
                            <textarea name="vraag_tekst" id="ih_vraag_hidden" class="ih-vraag-blok__hidden" aria-hidden="true"><?php echo esc_textarea($_POST['vraag_tekst'] ?? ''); ?></textarea>
                        </div>

                        <div class="ih-vraag-blok__field">
                            <label class="ih-vraag-blok__label" for="vraag_cv">CV uploaden <span style="font-weight:400;color:#6b7280;font-size:12px;">(optioneel)</span></label>
                            <label class="ih-vraag-blok__upload" for="vraag_cv">
                                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M213.66,82.34l-56-56A8,8,0,0,0,152,24H56A16,16,0,0,0,40,40V216a16,16,0,0,0,16,16H200a16,16,0,0,0,16-16V88A8,8,0,0,0,213.66,82.34ZM160,51.31,188.69,80H160ZM200,216H56V40h88V88a8,8,0,0,0,8,8h48V216Zm-72-96a8,8,0,0,1,8,8v16h16a8,8,0,0,1,0,16H136v16a8,8,0,0,1-16,0V160H104a8,8,0,0,1,0-16h16V128A8,8,0,0,1,128,120Z"/></svg>
                                <span class="ih-vraag-blok__upload-label">Kies je CV</span>
                                <span class="ih-vraag-blok__upload-name" id="ih_vraag_cv_name">Geen bestand gekozen</span>
                                <input type="file" name="vraag_cv" id="vraag_cv" accept=".pdf,.doc,.docx"
                                       class="ih-vraag-blok__upload-input"
                                       onchange="document.getElementById('ih_vraag_cv_name').textContent = this.files[0]?.name || 'Geen bestand gekozen'">
                            </label>
                            <span class="ih-vraag-blok__hint">PDF, Word. Max. 5 MB.</span>
                        </div>

                        <button type="submit" class="ih-vraag-blok__submit">Versturen</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Rechter kolom: sidebar -->
        <aside class="ih-single-layout__sidebar">

            <?php if ($company || $company_logo): ?>
            <!-- Blok 0: Bedrijf -->
            <div class="ih-single-sidebar">
                <p class="ih-sidebar__block-title">Over het bedrijf</p>
                <div class="ih-company-blok">
                    <?php if ($company_logo): ?>
                    <div class="ih-company-blok__logo">
                        <?php echo $company_logo; ?>
                    </div>
                    <?php endif; ?>
                    <div class="ih-company-blok__info">
                        <?php if ($job_company_url && $company): ?>
                            <a href="<?php echo esc_url($job_company_url); ?>" class="ih-company-blok__name"><?php echo esc_html($company); ?></a>
                        <?php elseif ($company): ?>
                            <span class="ih-company-blok__name"><?php echo esc_html($company); ?></span>
                        <?php endif; ?>
                        <?php if ($vacancy_count > 0): ?>
                            <a href="<?php echo esc_url($job_company_url); ?>" class="ih-company-blok__count">
                                <?php echo $vacancy_count; ?> openstaande <?php echo $vacancy_count === 1 ? 'vacature' : 'vacatures'; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Blok 1: Vacature details -->
            <div class="ih-single-sidebar">
                <p class="ih-sidebar__block-title">Vacature details</p>
                <div class="ih-sidebar__details">
                    <?php if ($company): ?>
                    <div class="ih-sidebar__detail-row">
                        <span class="ih-sidebar__detail-label">Bedrijf</span>
                        <span class="ih-sidebar__detail-value"><?php echo esc_html($company); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php
                    $types = wpjm_get_the_job_types();
                    if (!empty($types)):
                    ?>
                    <div class="ih-sidebar__detail-row">
                        <span class="ih-sidebar__detail-label">Type baan</span>
                        <span class="ih-sidebar__detail-value">
                            <?php foreach ($types as $type): ?>
                            <span class="ih-sidebar__chip"><?php echo esc_html($type->name); ?></span>
                            <?php endforeach; ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    <?php if ($location): ?>
                    <div class="ih-sidebar__detail-row">
                        <span class="ih-sidebar__detail-label">Standplaats</span>
                        <span class="ih-sidebar__detail-value"><?php echo esc_html($location); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($con_first || $con_last || $con_email): ?>
            <!-- Blok 2: Contactpersoon -->
            <div class="ih-single-sidebar">
                <p class="ih-sidebar__block-title">Contactpersoon</p>
                <div class="ih-sidebar__contact">
                    <?php if ($con_first || $con_last): ?>
                    <p class="ih-sidebar__contact-name"><?php echo esc_html(trim("$con_first $con_last")); ?></p>
                    <?php endif; ?>
                    <?php if ($con_email): ?>
                    <a href="mailto:<?php echo esc_attr($con_email); ?>" class="ih-sidebar__contact-email"><?php echo esc_html($con_email); ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </aside>

    </div>

<?php else : ?>

    <?php get_job_manager_template_part( 'access-denied', 'single-job_listing' ); ?>

<?php endif; ?>

<style>

/* ── Layout wrapper ──────────────────────────────────────── */
.ih-single-layout {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 24px;
    max-width: 1100px;
    width: 100%;
    margin: 56px auto 24px;
    padding: 0 24px;
    box-sizing: border-box;
}

.ih-single-layout__main {
    min-width: 0;
}

/* ── Hoofd vacature blok ─────────────────────────────────── */
.single_job_listing {
    background: #fff;
    border-radius: 5px;
    box-shadow: none;
    border: 1px solid #DEDEDE;
    padding: 24px;
}

/* ── Sidebar kolom ───────────────────────────────────────── */
.ih-single-layout__sidebar {
    display: flex;
    flex-direction: column;
    gap: 16px;
    position: sticky;
    top: 24px;
    align-self: start;
}

/* ── Sidebar blokken ─────────────────────────────────────── */
.ih-single-sidebar {
    background: #fff;
    border-radius: 5px;
    box-shadow: none;
    border: 1px solid #DEDEDE;
    padding: 20px;
}

.ih-sidebar__block-title {
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 14px;
    padding-bottom: 10px;
    border-bottom: 1px solid #DEDEDE;
}

/* ── Blok 0: bedrijf ─────────────────────────────────────── */
.ih-company-blok {
    display: flex;
    align-items: center;
    gap: 14px;
}

.ih-company-blok__logo {
    flex: 0 0 auto;
}

.ih-company-blok__logo img {
    width: 64px;
    height: 64px;
    object-fit: contain;
    border-radius: 50%;
    border: 1px solid #dedede;
    padding: 5px;
    background: #fff;
    display: block;
}

.ih-company-blok__info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.ih-company-blok__name {
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    text-decoration: none;
    display: block;
    overflow-wrap: anywhere;
}

a.ih-company-blok__name:hover {
    color: var(--color-primary, #0458AB);
    text-decoration: underline;
}

.ih-company-blok__count {
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    font-weight: 400;
    color: var(--color-primary, #0458AB);
    text-decoration: none;
    display: block;
}

.ih-company-blok__count:hover {
    text-decoration: underline;
}

/* ── Blok 1: details ─────────────────────────────────────── */
.ih-sidebar__details {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.ih-sidebar__detail-row {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.ih-sidebar__detail-label {
    font-family: 'Poppins', sans-serif;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #6b7280;
}

.ih-sidebar__detail-value {
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: #111827;
}

.ih-sidebar__chip {
    display: inline-block;
    padding: 3px 10px;
    background: rgba(4, 88, 171, 0.08);
    border: 1px solid rgba(4, 88, 171, 0.25);
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    font-family: 'Poppins', sans-serif;
    color: var(--color-primary, #0458AB);
    margin-right: 4px;
}

/* ── Blok 2: contactpersoon ──────────────────────────────── */
.ih-sidebar__contact-name {
    font-family: 'Poppins', sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 4px;
}

.ih-sidebar__contact-email {
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    font-weight: 400;
    color: var(--color-primary, #0458AB);
    text-decoration: none;
}

.ih-sidebar__contact-email:hover { text-decoration: underline; }

/* ── Titel ───────────────────────────────────────────────── */
.job-title h1 {
    padding-bottom: 10px;
    border-bottom: 1px solid #DEDEDE;
    font-family: Inter, sans-serif;
    font-weight: 700;
    font-size: 20px;
    padding-top: 20px;
}

/* ── Beschrijving ────────────────────────────────────────── */
.job_description {
    font-family: Poppins;
    font-size: 14px;
    font-weight: 400;
    line-height: 1.6;
    color: #333;
    margin-top: 28px;
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

/* ── Solliciteer knop ────────────────────────────────────── */
.job-apply-button a {
    padding: 12px;
    color: #fff;
    background-color: var(--color-primary, #0458AB);
    border-radius: 5px;
    font-family: 'Work Sans', sans-serif;
    text-decoration: none;
    display: inline-block;
    margin-top: 20px;
    border: 1px solid var(--color-primary, #0458AB);
}

.job-apply-button a:hover {
    background: #fff;
    color: var(--color-primary, #0458AB);
    border: 1px solid var(--color-primary, #0458AB);
}

h1.entry-title { display: none; }

/* ── Stel een vraag blok ─────────────────────────────────── */
.ih-vraag-blok {
    scroll-margin-top: 100px;
    margin-top: 20px;
    background: #fff;
    border: 1px solid #DEDEDE;
    border-radius: 5px;
    box-shadow: none;
    padding: 28px;
}

.ih-vraag-blok__title {
    font-family: 'Inter', sans-serif;
    font-size: 20px;
    font-weight: 700;
    color: #333333;
    margin: 0 0 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #DEDEDE;
    line-height: 1.3;
}

.ih-vraag-blok__form {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.ih-vraag-blok__row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.ih-vraag-blok__field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.ih-vraag-blok__label {
    font-family: 'Poppins', sans-serif;
    font-size: 15px;
    font-weight: 400;
    color: #333333;
    line-height: 1.4;
}

.ih-vraag-req { color: var(--color-primary, #0458AB); margin-left: 2px; }

.ih-vraag-blok__input {
    width: 100% !important;
    padding: 8px 12px !important;
    font-family: 'Poppins', sans-serif !important;
    font-size: 15px !important;
    font-weight: 400 !important;
    color: #333333 !important;
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

.ih-vraag-blok__input:focus {
    border-color: var(--color-primary, #0458AB) !important;
    box-shadow: 0 0 0 3px rgba(4, 88, 171, .15) !important;
}

.ih-vraag-blok__input::placeholder {
    font-family: 'Poppins', sans-serif !important;
    font-size: 14px !important;
    font-weight: 300 !important;
    color: #777777 !important;
}

.ih-vraag-blok__hidden { display: none !important; }

.ih-vraag-blok__quill-wrap {
    border: 1px solid #DEDEDE;
    border-radius: 5px;
    overflow: hidden;
    transition: border-color .2s ease, box-shadow .2s ease;
}

.ih-vraag-blok__quill-wrap:focus-within {
    border-color: var(--color-primary, #0458AB);
    box-shadow: 0 0 0 3px rgba(4, 88, 171, .15);
}

.ih-vraag-blok__quill-wrap .ql-toolbar {
    border: none !important;
    border-bottom: 1px solid #DEDEDE !important;
    background: #f7f8fa;
    padding: 8px 12px !important;
}

.ih-vraag-blok__quill-wrap .ql-container {
    border: none !important;
    font-family: 'Poppins', sans-serif !important;
    font-size: 15px !important;
}

.ih-vraag-blok__quill-wrap .ql-editor {
    padding: 14px !important;
    color: #333333 !important;
    line-height: 1.7 !important;
}

.ih-vraag-blok__quill-wrap .ql-editor.ql-blank::before {
    color: #777777 !important;
    font-style: normal !important;
    font-weight: 300 !important;
}

.ih-vraag-blok__quill-wrap .ql-toolbar button:hover,
.ih-vraag-blok__quill-wrap .ql-toolbar button.ql-active { color: var(--color-primary, #0458AB) !important; }
.ih-vraag-blok__quill-wrap .ql-toolbar button:hover .ql-stroke,
.ih-vraag-blok__quill-wrap .ql-toolbar button.ql-active .ql-stroke { stroke: var(--color-primary, #0458AB) !important; }
.ih-vraag-blok__quill-wrap .ql-toolbar button:hover .ql-fill,
.ih-vraag-blok__quill-wrap .ql-toolbar button.ql-active .ql-fill { fill: var(--color-primary, #0458AB) !important; }

/* CV Upload */
.ih-vraag-blok__upload {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    min-height: 110px;
    padding: 24px 20px;
    background: #f7f8fa;
    border: 2px dashed var(--color-primary, #0458AB);
    border-radius: 5px;
    cursor: pointer;
    text-align: center;
    position: relative;
    transition: border-color .2s ease, background .2s ease;
    box-sizing: border-box;
}

.ih-vraag-blok__upload:hover {
    border-color: var(--color-primary, #0458AB);
    background: #eef2f7;
}

.ih-vraag-blok__upload svg {
    color: var(--color-primary, #0458AB);
    flex-shrink: 0;
}

.ih-vraag-blok__upload-label {
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: var(--color-primary, #0458AB);
}

.ih-vraag-blok__upload-name {
    font-family: 'Poppins', sans-serif;
    font-size: 12px;
    font-weight: 400;
    color: #777777;
}

.ih-vraag-blok__upload-input {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 2;
}

.ih-vraag-blok__hint {
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    font-weight: 400;
    color: #777777;
    line-height: 1.5;
}

.ih-vraag-blok__submit {
    align-self: flex-start;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: 'Work Sans', sans-serif;
    font-size: 15px;
    font-weight: 600;
    background-color: var(--color-primary, #0458AB);
    border: 2px solid var(--color-primary, #0458AB);
    color: #fff !important;
    padding: 10px 28px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color .15s ease, border-color .15s ease, color .15s ease;
    text-decoration: none;
}

.ih-vraag-blok__submit:hover {
    background-color: #fff;
    color: var(--color-primary, #0458AB) !important;
}

.ih-vraag-blok__success {
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    color: #065f46;
    background: #ecfdf5;
    border: 1px solid #6ee7b7;
    border-radius: 5px;
    padding: 10px 14px;
    margin: 0;
}

.ih-vraag-blok__error {
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
    .ih-vraag-blok__row { grid-template-columns: 1fr; }
    .ih-vraag-blok__submit { width: 100%; align-self: stretch; }
}

/* ── Verlopen vacature ───────────────────────────────────── */
.ih-expired {
    padding: 32px 28px 28px;
}

.ih-expired__notice {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 14px;
    background: #FFF7ED;
    border: 1px solid #FED7AA;
    border-radius: 999px;
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: #C2410C;
    margin-bottom: 24px;
}

.ih-expired__notice svg {
    flex-shrink: 0;
    color: #C2410C;
}

.ih-expired__title {
    font-family: 'Inter', sans-serif;
    font-size: 22px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 10px;
    line-height: 1.3;
}

.ih-expired__meta {
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    font-weight: 400;
    color: #6b7280;
    margin: 0 0 20px;
}

.ih-expired__intro {
    font-family: 'Poppins', sans-serif;
    font-size: 15px;
    font-weight: 400;
    color: #374151;
    line-height: 1.65;
    margin: 0 0 28px;
    padding-bottom: 28px;
    border-bottom: 1px solid #DEDEDE;
}

.ih-expired__all-link {
    margin-top: 8px;
}

.ih-expired__all-link a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: var(--color-primary, #0458AB);
    text-decoration: none;
    transition: color .18s ease;
}

.ih-expired__all-link a:hover {
    text-decoration: underline;
}

.ih-expired__all-link a svg {
    flex-shrink: 0;
    color: currentColor;
}

/* ── Verlopen banner (content zichtbaar) ─────────────────── */
.ih-expired-banner {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    margin-bottom: 20px;
    background: #FFF7ED;
    border: 1px solid #FED7AA;
    border-radius: 5px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    font-weight: 500;
    color: #92400E;
    flex-wrap: wrap;
}

.ih-expired-banner svg {
    flex-shrink: 0;
    color: #C2410C;
}

.ih-expired-banner a {
    margin-left: auto;
    font-weight: 600;
    color: var(--color-primary, #0458AB);
    text-decoration: none;
    white-space: nowrap;
}

.ih-expired-banner a:hover {
    text-decoration: underline;
}

/* ── Responsive ──────────────────────────────────────────── */
@media (max-width: 900px) {
    .ih-single-layout {
        grid-template-columns: 1fr;
    }

    .ih-single-layout__sidebar {
        order: -1;
        position: static;
    }

    .ih-single-layout__sidebar .ih-single-sidebar {
        position: static;
    }
}

@media (max-width: 768px) {
    .ih-single-layout {
        padding: 0 12px;
        margin: 16px auto;
    }
}

</style>

<script>
(function () {
    function initVraagQuill() {
        if (typeof Quill === 'undefined') { setTimeout(initVraagQuill, 80); return; }

        var hidden = document.getElementById('ih_vraag_hidden');
        if (!hidden) return;

        var quill = new Quill('#ih_vraag_quill', {
            theme: 'snow',
            placeholder: 'Stel je vraag of schrijf een korte motivatie...',
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
