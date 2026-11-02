<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $post;

$post_id = isset( $post->ID ) ? (int) $post->ID : 0;
?>

<?php if ( $post_id && job_manager_user_can_view_job_listing( $post_id ) ) :

    $salary    = get_post_meta($post_id, '_job_salary_range', true);
    $hours     = get_post_meta($post_id, '_job_hours_per_week', true);
    $location  = get_post_meta($post_id, '_job_location', true);
    $company   = get_the_company_name();
    $con_first = get_post_meta($post_id, '_job_contact_firstname', true);
    $con_last  = get_post_meta($post_id, '_job_contact_lastname', true);
    $con_email = get_post_meta($post_id, '_job_contact_email', true);

    $job_company_terms = get_the_terms($post_id, 'job_company');
    $job_company_term  = (!is_wp_error($job_company_terms) && !empty($job_company_terms)) ? $job_company_terms[0] : null;
    $job_company_link  = $job_company_term ? get_term_link($job_company_term) : '';
    $job_company_url   = is_wp_error($job_company_link) ? '' : $job_company_link;
    $company_logo      = has_post_thumbnail($post_id) ? get_the_post_thumbnail($post_id, 'thumbnail') : '';

    $vacancy_count = $job_company_term ? (int) $job_company_term->count : 0;
?>

    <div class="dr-single-layout">

        <!-- Linker kolom: vacature inhoud -->
        <div class="dr-single-layout__main">
            <div class="single_job_listing">
                <?php if ( get_option( 'job_manager_hide_expired_content', 1 ) && 'expired' === $post->post_status ) : ?>

                    <div class="dr-expired">
                        <div class="dr-expired__notice">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            Deze vacature is verlopen
                        </div>

                        <h1 class="dr-expired__title"><?php echo esc_html(get_the_title($post_id)); ?></h1>

                        <?php if ($company || $location): ?>
                        <p class="dr-expired__meta">
                            <?php echo implode(' &nbsp;·&nbsp; ', array_filter([esc_html($company), esc_html($location)])); ?>
                        </p>
                        <?php endif; ?>

                        <p class="dr-expired__intro">Helaas is deze vacature niet meer beschikbaar. Bekijk onze andere openstaande vacatures.</p>

                        <div class="dr-expired__all-link">
                            <a class="dr-expired__link" href="<?php echo esc_url(get_post_type_archive_link('job_listing') ?: home_url('/vacatures/')); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                                Bekijk alle openstaande vacatures
                            </a>
                        </div>
                    </div>

                <?php elseif ('expired' === $post->post_status): ?>

                    <div class="dr-expired-banner">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Let op: deze vacature is verlopen en staat mogelijk niet meer open.
                        <a class="dr-expired-banner__link" href="<?php echo esc_url(get_post_type_archive_link('job_listing') ?: home_url('/vacatures/')); ?>">Bekijk actuele vacatures →</a>
                    </div>

                <?php endif; ?>

                <?php if ( ! ( get_option( 'job_manager_hide_expired_content', 1 ) && 'expired' === $post->post_status ) ) : ?>

                    <div class="content-part-job-description">
                        <div class="job-title dr-single-title-row">
                            <h1 class="dr-single__title"><?php wpjm_the_job_title(); ?></h1>
                            <time class="dr-single-posted-date" datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>">
                                Geplaatst op <?php echo esc_html(get_the_date('j F Y', $post_id)); ?>
                            </time>
                        </div>

                        <div class="job_description">
                            <?php wpjm_the_job_description(); ?>
                        </div>

                        <?php $company_website = get_post_meta( $post_id, '_company_website', true ); ?>
                        <?php if ( ! empty( $company_website ) ) : ?>
                            <div class="job-apply-button">
                                <a href="<?php echo esc_url( $company_website ); ?>" class="apply-button dr-single__apply-button" target="_blank" rel="noopener">
                                    Solliciteren op deze vacature!
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php do_action( 'single_job_listing_end' ); ?>
                    </div>

                <?php endif; ?>
            </div>
        </div>

        <!-- Rechter kolom: sidebar -->
        <aside class="dr-single-layout__sidebar">

            <?php if ($company || $company_logo): ?>
            <!-- Blok 0: Bedrijf -->
            <div class="dr-single-sidebar">
                <p class="dr-sidebar__block-title">Over het bedrijf</p>
                <div class="dr-company-blok">
                    <?php if ($company_logo): ?>
                    <div class="dr-company-blok__logo">
                        <?php echo $company_logo; ?>
                    </div>
                    <?php endif; ?>
                    <div class="dr-company-blok__info">
                        <?php if ($job_company_url && $company): ?>
                            <a href="<?php echo esc_url($job_company_url); ?>" class="dr-company-blok__name"><?php echo esc_html($company); ?></a>
                        <?php elseif ($company): ?>
                            <span class="dr-company-blok__name"><?php echo esc_html($company); ?></span>
                        <?php endif; ?>
                        <?php if ($job_company_url && $vacancy_count > 0): ?>
                            <a href="<?php echo esc_url($job_company_url); ?>" class="dr-company-blok__count">
                                <?php echo $vacancy_count; ?> openstaande <?php echo $vacancy_count === 1 ? 'vacature' : 'vacatures'; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Blok 1: Vacature details -->
            <div class="dr-single-sidebar">
                <p class="dr-sidebar__block-title">Vacature details</p>
                <div class="dr-sidebar__details">
                    <?php if ($company): ?>
                    <div class="dr-sidebar__detail-row">
                        <span class="dr-sidebar__detail-label">Bedrijf</span>
                        <span class="dr-sidebar__detail-value"><?php echo esc_html($company); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php
                    $types = wpjm_get_the_job_types();
                    if (!empty($types)):
                    ?>
                    <div class="dr-sidebar__detail-row">
                        <span class="dr-sidebar__detail-label">Type baan</span>
                        <span class="dr-sidebar__detail-value">
                            <?php foreach ($types as $type): ?>
                            <span class="dr-sidebar__chip"><?php echo esc_html($type->name); ?></span>
                            <?php endforeach; ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    <?php if ($salary): ?>
                    <div class="dr-sidebar__detail-row">
                        <span class="dr-sidebar__detail-label">Salaris</span>
                        <span class="dr-sidebar__detail-value"><?php echo esc_html($salary); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($location): ?>
                    <div class="dr-sidebar__detail-row">
                        <span class="dr-sidebar__detail-label">Standplaats</span>
                        <span class="dr-sidebar__detail-value"><?php echo esc_html($location); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($hours): ?>
                    <div class="dr-sidebar__detail-row">
                        <span class="dr-sidebar__detail-label">Uren/week</span>
                        <span class="dr-sidebar__detail-value"><?php echo esc_html($hours); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php
                    $sectors = get_the_terms($post_id, 'job_sector');
                    if (!is_wp_error($sectors) && !empty($sectors)):
                    ?>
                    <div class="dr-sidebar__detail-row">
                        <span class="dr-sidebar__detail-label">Sector</span>
                        <span class="dr-sidebar__detail-value">
                            <?php foreach ($sectors as $sector): ?>
                            <span class="dr-sidebar__chip"><?php echo esc_html($sector->name); ?></span>
                            <?php endforeach; ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($con_first || $con_last || $con_email): ?>
            <!-- Blok 2: Contactpersoon -->
            <div class="dr-single-sidebar">
                <p class="dr-sidebar__block-title">Contactpersoon</p>
                <div class="dr-sidebar__contact">
                    <?php if ($con_first || $con_last): ?>
                    <p class="dr-sidebar__contact-name"><?php echo esc_html(trim("$con_first $con_last")); ?></p>
                    <?php endif; ?>
                    <?php if ($con_email): ?>
                    <a href="mailto:<?php echo esc_attr($con_email); ?>" class="dr-sidebar__contact-email"><?php echo esc_html($con_email); ?></a>
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

main#content {
    width: 100%;
}

h1.entry-title { display: none; }

/*
 * Links, titels en knoppen zijn gescoped op .dr-single-layout zodat de globale
 * Elementor-styling (.elementor-kit-N a/h1/button) ze niet overschrijft.
 */

/* ── Layout wrapper ──────────────────────────────────────── */
.dr-single-layout {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 24px;
    max-width: 1140px;
    width: 100%;
    margin: 56px auto 24px;
    padding: 0 30px;
    box-sizing: border-box;
}

.dr-single-layout__main {
    min-width: 0;
}

/* ── Hoofd vacature blok ─────────────────────────────────── */
.single_job_listing {
    background: #fff;
    border-radius: 6px;
    border: 1px solid #e0e0e0;
    padding: 24px 32px 32px;
}

/* ── Sidebar kolom ───────────────────────────────────────── */
.dr-single-layout__sidebar {
    display: flex;
    flex-direction: column;
    gap: 16px;
    position: sticky;
    top: 24px;
    align-self: start;
}

/* ── Sidebar blokken ─────────────────────────────────────── */
.dr-single-sidebar {
    background: #fff;
    border-radius: 6px;
    border: 1px solid #e0e0e0;
    padding: 20px;
}

.dr-sidebar__block-title {
    font-family: 'Work Sans', sans-serif;
    font-size: 16px;
    font-weight: 700;
    color: #2C2C31;
    margin: 0 0 14px;
    padding-bottom: 10px;
    border-bottom: 2px solid #FCAF2B;
}

/* ── Blok 0: bedrijf ─────────────────────────────────────── */
.dr-company-blok {
    display: flex;
    align-items: center;
    gap: 14px;
}

.dr-company-blok__logo {
    flex: 0 0 auto;
}

.dr-company-blok__logo img {
    width: 64px;
    height: 64px;
    object-fit: contain;
    border-radius: 50%;
    border: 1px solid #e0e0e0;
    padding: 5px;
    background: #fff;
    display: block;
}

.dr-company-blok__info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.dr-single-layout .dr-company-blok__name {
    font-family: 'Work Sans', sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: #2C2C31;
    text-decoration: none;
    display: block;
    overflow-wrap: anywhere;
}

.dr-single-layout a.dr-company-blok__name:hover {
    color: #E09B1A;
    text-decoration: underline;
}

.dr-single-layout .dr-company-blok__count {
    font-family: 'Work Sans', sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: #E09B1A;
    text-decoration: none;
    display: block;
}

.dr-single-layout .dr-company-blok__count:hover {
    color: #E09B1A;
    text-decoration: underline;
}

/* ── Blok 1: details ─────────────────────────────────────── */
.dr-sidebar__details {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.dr-sidebar__detail-row {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.dr-sidebar__detail-label {
    font-family: 'Work Sans', sans-serif;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #6b6b72;
}

.dr-sidebar__detail-value {
    font-family: 'Work Sans', sans-serif;
    font-size: 15px;
    font-weight: 600;
    color: #2C2C31;
}

.dr-sidebar__chip {
    display: inline-block;
    padding: 3px 10px;
    background: #FEEAD5;
    border: 1px solid #FCAF2B;
    border-radius: 999px;
    font-family: 'Work Sans', sans-serif;
    font-size: 12px;
    font-weight: 600;
    color: #2C2C31;
    margin: 0 4px 4px 0;
}

/* ── Blok 2: contactpersoon ──────────────────────────────── */
.dr-sidebar__contact-name {
    font-family: 'Work Sans', sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: #2C2C31;
    margin: 0 0 4px;
}

.dr-single-layout .dr-sidebar__contact-email {
    font-family: 'Work Sans', sans-serif;
    font-size: 14px;
    color: #E09B1A;
    text-decoration: none;
    overflow-wrap: anywhere;
}

.dr-single-layout .dr-sidebar__contact-email:hover {
    color: #E09B1A;
    text-decoration: underline;
}

/* ── Titel ───────────────────────────────────────────────── */
.dr-single-title-row {
    margin-bottom: 32px;
    padding-bottom: 16px;
    border-bottom: 2px solid #FCAF2B;
}

.dr-single-layout .dr-single__title {
    font-family: 'Work Sans', sans-serif;
    font-weight: 700;
    font-size: 26px;
    line-height: 1.3;
    color: #2C2C31;
    margin: 0;
    padding: 0;
    border: 0;
}

.dr-single-posted-date {
    display: block;
    margin-top: 8px;
    font-family: 'Work Sans', sans-serif;
    font-size: 13px;
    line-height: 1.4;
    color: #6b6b72;
}

/* ── Beschrijving ────────────────────────────────────────── */
.dr-single-layout .job_description {
    font-family: 'Work Sans', sans-serif;
    font-size: 16px;
    font-weight: 400;
    line-height: 1.65;
    color: #2C2C31;
}

.dr-single-layout .job_description h2,
.dr-single-layout .job_description h3,
.dr-single-layout .job_description h4 {
    font-family: 'Work Sans', sans-serif;
    font-weight: 700;
    color: #2C2C31;
    margin: 28px 0 10px;
}

.dr-single-layout .job_description ul,
.dr-single-layout .job_description ol {
    padding-left: 20px;
}

.dr-single-layout .job_description a {
    color: #E09B1A;
    text-decoration: underline;
}

.dr-single-layout .job_description a:hover {
    color: #2C2C31;
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
.job-apply-button {
    margin-top: 28px;
}

.dr-single-layout .dr-single__apply-button {
    display: inline-block;
    background: #FCAF2B;
    color: #2C2C31;
    border: 1px solid #FCAF2B;
    padding: 0 30px;
    height: 48px;
    line-height: 48px;
    border-radius: 0;
    font-family: 'Work Sans', sans-serif;
    font-size: 16px;
    font-weight: 700;
    text-decoration: none;
    transition: all .2s ease;
}

.dr-single-layout .dr-single__apply-button:hover,
.dr-single-layout .dr-single__apply-button:focus {
    background: #E09B1A;
    border-color: #E09B1A;
    color: #2C2C31;
}

.dr-single-layout .single_job_listing .application_button {
    display: inline-block;
    background-color: #FCAF2B;
    color: #2C2C31 !important;
    padding: 12px 24px;
    border-radius: 0;
    border: 1px solid #FCAF2B;
    font-family: 'Work Sans', sans-serif;
    font-weight: 700;
    margin-top: 20px;
    cursor: pointer;
}

.dr-single-layout .single_job_listing .application_button:hover,
.dr-single-layout .single_job_listing .application_button:focus {
    background-color: #E09B1A;
    border-color: #E09B1A;
}

/* ── Verlopen vacature ───────────────────────────────────── */
.dr-expired {
    padding: 8px 0;
}

.dr-expired__notice {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 14px;
    background: #FEEAD5;
    border: 1px solid #FCAF2B;
    border-radius: 999px;
    font-family: 'Work Sans', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: #2C2C31;
    margin-bottom: 24px;
}

.dr-expired__notice svg {
    flex-shrink: 0;
    color: #E09B1A;
}

.dr-single-layout .dr-expired__title {
    font-family: 'Work Sans', sans-serif;
    font-size: 24px;
    font-weight: 700;
    color: #2C2C31;
    margin: 0 0 10px;
    line-height: 1.3;
}

.dr-expired__meta {
    font-family: 'Work Sans', sans-serif;
    font-size: 14px;
    color: #6b6b72;
    margin: 0 0 20px;
}

.dr-expired__intro {
    font-family: 'Work Sans', sans-serif;
    font-size: 16px;
    color: #2C2C31;
    line-height: 1.65;
    margin: 0 0 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid #e0e0e0;
}

.dr-single-layout .dr-expired__link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-family: 'Work Sans', sans-serif;
    font-size: 15px;
    font-weight: 600;
    color: #E09B1A;
    text-decoration: none;
}

.dr-single-layout .dr-expired__link:hover {
    color: #E09B1A;
    text-decoration: underline;
}

.dr-expired__link svg {
    flex-shrink: 0;
}

/* ── Verlopen banner (content zichtbaar) ─────────────────── */
.dr-expired-banner {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    margin-bottom: 20px;
    background: #FEEAD5;
    border: 1px solid #FCAF2B;
    border-radius: 6px;
    font-family: 'Work Sans', sans-serif;
    font-size: 14px;
    font-weight: 500;
    color: #2C2C31;
    flex-wrap: wrap;
}

.dr-expired-banner svg {
    flex-shrink: 0;
    color: #E09B1A;
}

.dr-single-layout .dr-expired-banner__link {
    margin-left: auto;
    font-weight: 600;
    color: #2C2C31;
    white-space: nowrap;
}

/* ── Responsive ──────────────────────────────────────────── */
@media (max-width: 900px) {
    .dr-single-layout {
        grid-template-columns: 1fr;
    }

    .dr-single-layout__sidebar {
        order: -1;
        position: static;
    }
}

@media (max-width: 768px) {
    .dr-single-layout {
        padding: 0 12px;
        margin: 16px auto;
    }

    .single_job_listing {
        padding: 20px;
    }

    .dr-single-layout .dr-single__title {
        font-size: 22px;
    }
}

</style>
