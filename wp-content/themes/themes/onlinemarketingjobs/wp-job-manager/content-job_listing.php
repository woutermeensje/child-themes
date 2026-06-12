<?php
/**
 * Job listing in the loop.
 *
 * Fondsen-style template adapted for Onlinemarketingjobs.
 */

if (!defined('ABSPATH')) {
    exit;
}

global $post;

$post_id = $post ? (int) $post->ID : get_the_ID();
$has_company_logo = has_post_thumbnail($post_id);

$job_company_terms = get_the_terms($post_id, 'job_company');
$job_company_term  = (!is_wp_error($job_company_terms) && !empty($job_company_terms)) ? $job_company_terms[0] : null;
$job_company_name  = $job_company_term ? $job_company_term->name : get_the_company_name($post);
$job_company_slug  = $job_company_term ? $job_company_term->slug : '';
$job_company_url   = $job_company_slug ? home_url('/vacatures/' . $job_company_slug . '/') : '';

$cover_image        = function_exists('sj_get_image_url')
    ? sj_get_image_url(get_post_meta($post_id, '_cover_image', true), 'large')
    : get_post_meta($post_id, '_cover_image', true);
$secondary_image_id = get_post_meta($post_id, '_uncode_secondary_thumbnail_id', true);
$secondary_image    = $secondary_image_id ? wp_get_attachment_image_url($secondary_image_id, 'large') : '';
$background_image   = $cover_image ?: $secondary_image;
$background_style   = $background_image
    ? "background-image: url('" . esc_url($background_image) . "');"
    : "background-image: linear-gradient(135deg, rgba(124, 92, 250, 0.92), rgba(132, 94, 194, 0.92));";

$job_location = get_the_job_location($post_id);
$job_types    = get_the_terms($post_id, 'job_listing_type');
$org_types    = get_the_terms($post_id, 'organisatie_type');

if ((empty($org_types) || is_wp_error($org_types)) && taxonomy_exists('organization_type')) {
    $org_types = get_the_terms($post_id, 'organization_type');
}

$location_links = [];
if ($job_location) {
    $location_parts = array_filter(array_map('trim', explode(',', $job_location)));
    foreach ($location_parts as $loc) {
        $location_links[] = '<a href="' . esc_url(home_url('/vacatures/' . sanitize_title($loc) . '/')) . '" class="job-card-meta__filter-link" onclick="event.stopPropagation();">' . esc_html($loc) . '</a>';
    }
}

$type_links = [];
$type_names = [];
if (!empty($job_types) && !is_wp_error($job_types)) {
    foreach ($job_types as $type) {
        $type_links[] = '<a href="' . esc_url(home_url('/vacatures/' . $type->slug . '/')) . '" class="job-card-meta__filter-link" onclick="event.stopPropagation();">' . esc_html($type->name) . '</a>';
        $type_names[] = $type->name;
    }
}

$org_type_links = [];
if (!empty($org_types) && !is_wp_error($org_types)) {
    foreach ($org_types as $ot) {
        $org_type_links[] = '<a href="' . esc_url(home_url('/vacatures/' . $ot->slug . '/')) . '" class="job-card-meta__filter-link" onclick="event.stopPropagation();">' . esc_html($ot->name) . '</a>';
    }
}

$geo_long = $post->geolocation_long ?? get_post_meta($post_id, '_geolocation_long', true);
$geo_lat  = $post->geolocation_lat ?? get_post_meta($post_id, '_geolocation_lat', true);
?>
<li <?php job_listing_class(); ?>
    data-longitude="<?php echo esc_attr($geo_long); ?>"
    data-latitude="<?php echo esc_attr($geo_lat); ?>"
>
    <div class="job-card" data-href="<?php the_job_permalink(); ?>">
        <div class="job-card__desktop">
            <div class="job-card__media">
                <div class="background-wrapper">
                    <div
                        class="background-inner"
                        style="
                            <?php echo $background_style; ?>
                            background-size: cover;
                            min-height: 100%;
                            display: block;
                            width: 100%;
                            background-repeat: no-repeat;
                            background-position: center center;
                            background-attachment: scroll;
                        "
                    ></div>

                    <div class="block-bg-overlay" style="opacity: 0.5; height: 100%;"></div>

                    <?php if ($has_company_logo) : ?>
                    <div class="job-card__desktop-logo">
                        <?php echo get_the_post_thumbnail($post_id, 'thumbnail'); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="job-card__content">
                <div class="job-card__favorite">
                    <?php if (function_exists('sj_the_job_favorite_button')) sj_the_job_favorite_button($post_id, ['context' => 'card']); ?>
                </div>
                <div class="job_listing_content">
                    <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="title-link">
                        <h2 class="job-card__title"><?php wpjm_the_job_title(); ?></h2>
                    </a>

                    <div class="job_text">
                        <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 8, '.....')); ?></p>
                    </div>

                    <ul class="job-card-meta">
                        <?php if ($job_company_name) : ?>
                        <li class="job-card-meta__item job-card-meta__item--org">
                            <span class="job-card-meta__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </span>
                            <div class="job-card-meta__textgroup">
                                <?php if ($job_company_url) : ?>
                                    <a href="<?php echo esc_url($job_company_url); ?>" class="job-card-meta__title job-card-meta__company-link" onclick="event.stopPropagation();"><?php echo esc_html($job_company_name); ?></a>
                                <?php else : ?>
                                    <p class="job-card-meta__title"><?php echo esc_html($job_company_name); ?></p>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php endif; ?>

                        <?php if (!empty($location_links)) : ?>
                        <li class="job-card-meta__item job-card-meta__item--location">
                            <span class="job-card-meta__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true"><path d="M12 2a7 7 0 0 1 7 7c0 5.25-7 13-7 13S5 14.25 5 9a7 7 0 0 1 7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                            </span>
                            <p class="job-card-meta__text"><?php echo implode(', ', $location_links); ?></p>
                        </li>
                        <?php endif; ?>

                        <?php if (!empty($type_links)) : ?>
                        <li class="job-card-meta__item job-card-meta__item--type">
                            <span class="job-card-meta__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                            </span>
                            <p class="job-card-meta__text"><?php echo implode(', ', $type_links); ?></p>
                        </li>
                        <?php endif; ?>

                        <?php if (!empty($org_type_links)) : ?>
                        <li class="job-card-meta__item job-card-meta__item--orgtype">
                            <span class="job-card-meta__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
                            </span>
                            <p class="job-card-meta__text"><?php echo implode(', ', $org_type_links); ?></p>
                        </li>
                        <?php endif; ?>

                    </ul>

                    <div class="jobs_buttons_wrap">
                        <div class="jobs_buttons">
                            <a href="<?php the_job_permalink(); ?>">Vacature bekijken</a>
                            <a href="<?php echo esc_url(get_permalink($post_id) . '#sj-vraag'); ?>" class="jobs_buttons__contact" onclick="event.stopPropagation();">Contact opnemen</a>
                        </div>
                    </div>
                    </div>
                    <span class="job-card__date"><?php echo esc_html(get_the_date('j F Y', $post_id)); ?></span>
                </div>
            </div>

        <div class="job-card__mobile">
            <div class="job-mobile__link">
                <div class="job-mobile__top">
                    <a class="job-mobile__title-link" href="<?php the_job_permalink(); ?>">
                        <h2 class="job-mobile__title"><?php wpjm_the_job_title(); ?></h2>
                    </a>
                    <div class="job-mobile__favorite">
                        <?php if (function_exists('sj_the_job_favorite_button')) sj_the_job_favorite_button($post_id, ['context' => 'card']); ?>
                    </div>
                </div>

                <div class="job-mobile__body<?php echo $has_company_logo ? '' : ' job-mobile__body--no-logo'; ?>">
                    <?php if ($has_company_logo) : ?>
                    <div class="job-mobile__logo">
                        <?php echo get_the_post_thumbnail($post_id, 'thumbnail'); ?>
                    </div>
                    <?php endif; ?>

                    <div class="job-mobile__info">
                        <?php if ($job_company_name && $job_company_url) : ?>
                            <a href="<?php echo esc_url($job_company_url); ?>" class="job-mobile__info-org job-card-meta__company-link" onclick="event.stopPropagation();"><?php echo esc_html($job_company_name); ?></a>
                        <?php elseif ($job_company_name) : ?>
                            <span class="job-mobile__info-org"><?php echo esc_html($job_company_name); ?></span>
                        <?php endif; ?>

                        <?php if ($job_location) : ?>
                            <span class="job-mobile__info-location"><?php echo esc_html($job_location); ?></span>
                        <?php endif; ?>

                        <?php if ((!empty($org_types) && !is_wp_error($org_types)) || (!empty($job_types) && !is_wp_error($job_types))) : ?>
                        <div class="job-mobile__chips">
                            <?php if (!empty($org_types) && !is_wp_error($org_types)) : ?>
                                <?php foreach ($org_types as $ot) : ?>
                                    <a href="<?php echo esc_url(home_url('/vacatures/' . $ot->slug . '/')); ?>" class="job-mobile__chip" onclick="event.stopPropagation();"><?php echo esc_html($ot->name); ?></a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if (!empty($job_types) && !is_wp_error($job_types)) : ?>
                                <?php foreach ($job_types as $type) : ?>
                                    <a href="<?php echo esc_url(home_url('/vacatures/' . $type->slug . '/')); ?>" class="job-mobile__chip" onclick="event.stopPropagation();"><?php echo esc_html($type->name); ?></a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="job-mobile__cta">Vacature bekijken</div>
            </div>
        </div>
    </div>
</li>

<style>
ul.job_listings {
    max-width: 1140px;
    margin: 30px auto !important;
}

div.job_listings ul.job_listings {
    padding-left: 0;
    border: none;
}

ul.job_listings li.job_listing {
    padding: 15px 30px;
    border-bottom: none !important;
}

.job-card {
    background: #ffffff;
    border-radius: 6px;
    box-shadow: none;
    padding: 0;
    border: 1px solid #DEDEDE;
    margin-top: 4px;
    margin-bottom: 4px;
    overflow: hidden;
    cursor: pointer;
    transition: transform .18s ease, border-color .18s ease;
}

.job-card:hover {
    transform: translateY(-1px);
    border-color: #DEDEDE;
    box-shadow: none;
}

a.title-link {
    background-color: #ffffff !important;
}

.job-card,
.job-card * {
    max-width: 100%;
    box-sizing: border-box;
}

.job-card__desktop,
.job-card__mobile {
    width: 100%;
}

.job-card__desktop {
    display: flex;
    flex-wrap: nowrap;
    position: relative;
}

.job-card__media {
    position: relative;
    flex: 0 0 40%;
    max-width: 40%;
    display: flex;
}

.job-card__content {
    flex: 0 0 60%;
    max-width: 60%;
    display: flex;
    align-items: center;
    min-width: 0;
    padding: 24px 0;
    position: relative;
}

ul.job_listings li.job_listing .company-logo-wrapper,
.single_job_listing .company-logo-wrapper,
.company-logo-wrapper {
    height: 80px;
    width: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
}

ul.job_listings li.job_listing .company-logo-wrapper img,
.single_job_listing .company-logo-wrapper img,
.company-logo-wrapper img {
    border-radius: 50%;
    width: 80px;
    height: 80px;
    border: 1px solid var(--color-border-light);
    padding: 5px;
    object-fit: contain;
    background: #fff;
}

.company-logo-absolute {
    position: absolute;
    width: 80px;
    height: 80px;
    z-index: 9;
    left: 30px;
    bottom: 30px;
}

.company-logo-placeholder {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #fff;
    border: 1px solid var(--color-border-light);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #bbb;
}

.job_listing .job_listing_content {
    padding: 0 40px;
    min-width: 0;
}

h2.job-card__title {
    margin: 0 !important;
    padding: 0 !important;
    font-family: 'Inter', sans-serif !important;
    font-weight: 700 !important;
    font-size: 22px !important;
    color: var(--color-text) !important;
    overflow-wrap: anywhere;
    word-break: break-word;
    line-height: 1.25 !important;
}

.job_listing .job_listing_content .job_text {
    margin: 10px 0 20px;
}

.job_listing .job_listing_content .job_text p {
    margin-top: 0;
    margin-bottom: 10px;
    color: var(--color-text);
    font-family: Poppins, sans-serif;
    font-size: 14px;
    line-height: 1.7;
    font-weight: 300;
}

.jobs_buttons_wrap {
    margin-top: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.job-card__date {
    position: absolute;
    bottom: 24px;
    right: 40px;
    font-family: 'Poppins', sans-serif;
    font-size: 12px;
    font-weight: 300;
    color: #333;
    white-space: nowrap;
}

.jobs_buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.job-card__desktop-logo {
    position: absolute;
    right: 16px;
    bottom: 16px;
    width: 82px;
    height: 82px;
    border-radius: 5px;
    border: 1px solid #dedede;
    background: #fff;
    display: grid;
    place-items: center;
    overflow: hidden;
    z-index: 10;
}

.job-card__desktop-logo img {
    width: 70px;
    height: 70px;
    object-fit: contain;
    border-radius: 0;
}

.jobs_buttons a {
    display: inline-block !important;
    background: var(--color-primary) !important;
    color: #ffffff !important;
    border: 1px solid var(--color-primary) !important;
    padding: 0 24px !important;
    height: 48px !important;
    line-height: 48px !important;
    border-radius: 0 !important;
    text-decoration: none !important;
    font-family: Balgin-Bold, serif !important;
    font-size: 15px;
    box-shadow: none;
    transition: background .18s ease, border-color .18s ease, color .18s ease;
    text-align: center;
    white-space: nowrap;
}

.jobs_buttons a:hover {
    background: var(--color-primary-dk) !important;
    border-color: var(--color-primary-dk) !important;
}

.jobs_buttons a.jobs_buttons__contact {
    background: transparent !important;
    color: var(--color-primary) !important;
    border: 1.5px solid var(--color-primary) !important;
}

.jobs_buttons a.jobs_buttons__contact:hover {
    background: var(--color-primary) !important;
    color: #ffffff !important;
    border-color: var(--color-primary) !important;
}

a.title-link {
    padding: 0 !important;
    color: var(--color-text);
    text-decoration: none;
    font-family: Balgin-Bold, serif !important;
}

a.title-link:hover h2 {
    color: var(--color-primary);
}

.job_listing .background-wrapper .title {
    display: none;
}

.background-wrapper {
    width: 100%;
    flex: 1 1 auto;
    position: relative;
    min-height: 318px;
    overflow: hidden;
}

.background-inner {
    height: 100%;
    min-height: 318px;
}

.block-bg-overlay {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    background:
        linear-gradient(135deg, rgba(124, 92, 250, 0.32), rgba(132, 94, 194, 0.58)),
        var(--color-primary);
    opacity: 0.38 !important;
}

.job-card-meta {
    list-style: none;
    margin: 0;
    padding: 0;
    color: var(--color-text);
}

.job-card-meta__item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px !important;
    margin-top: 0 !important;
    flex-direction: row !important;
}

.job-card-meta__icon {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    color: var(--color-primary);
}

.job-card-meta__text,
.job-card-meta__title {
    margin: 0 !important;
    font-family: Poppins, sans-serif;
    font-size: 14px;
    line-height: 1.45;
}

.job-card-meta__title {
    font-weight: 600;
}

a.job-card-meta__company-link {
    display: inline;
    background: transparent !important;
    color: inherit;
    text-decoration: none;
    font-weight: 600;
    margin: 0;
    padding: 0 !important;
}

a.job-card-meta__company-link:hover {
    color: var(--color-primary);
    text-decoration: underline;
}

a.job-card-meta__location-link,
a.job-card-meta__filter-link {
    display: inline !important;
    background: transparent !important;
    color: inherit;
    text-decoration: none;
    margin: 0;
    padding: 0 !important;
    width: auto !important;
    float: none !important;
}

a.job-card-meta__location-link:hover,
a.job-card-meta__filter-link:hover {
    color: var(--color-primary);
    text-decoration: underline;
}

.job-card-meta__textgroup {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

/* ── Mobiele kaart — globale stijlen (altijd geladen) ──────── */
.job-card__mobile {
    display: none;
}

.job-mobile__link {
    display: block;
    text-decoration: none !important;
    color: inherit;
}

ul.job_listings li.job_listing .job-card__mobile a {
    background: transparent !important;
    border: 0 !important;
    display: inline-flex;
    float: none !important;
    line-height: inherit;
    margin: 0;
    overflow: visible;
    padding: 0 !important;
    position: static;
    text-decoration: none !important;
    width: auto !important;
}

ul.job_listings li.job_listing .job-card__mobile a:hover,
ul.job_listings li.job_listing .job-card__mobile a:focus {
    background: transparent !important;
}

.job-mobile__top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
    min-width: 0;
    margin-top: 4px;
}

.job-mobile__top .job-mobile__title-link {
    display: block !important;
    flex: 1 1 auto;
    min-width: 0;
    width: auto !important;
}

.job-mobile__top .job-mobile__favorite {
    flex-shrink: 0;
    margin-top: 2px;
}

.job-card__mobile .job-mobile__title,
.job-card__mobile .job-mobile__title-link .job-mobile__title {
    margin: 0 !important;
    padding: 0 !important;
    text-indent: 0 !important;
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 19px;
    line-height: 1.3;
    color: var(--color-text);
    overflow-wrap: anywhere;
    word-break: break-word;
}

.job-card__mobile .job-mobile__title-link {
    display: block;
    margin: 0 !important;
    padding: 0 !important;
    color: inherit;
    text-decoration: none !important;
}

.job-mobile__body {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 88px;
    gap: 14px;
    align-items: flex-start;
    margin-top: 8px;
}

.job-mobile__body--no-logo {
    grid-template-columns: minmax(0, 1fr);
}

.job-mobile__logo {
    grid-column: 2;
    grid-row: 1;
    width: 88px;
    height: 88px;
    border-radius: 5px;
    background: #fff;
    border: 1px solid #DEDEDE;
    display: grid;
    place-items: center;
    overflow: hidden;
    flex-shrink: 0;
}

.job-mobile__logo img {
    width: 74px;
    height: 74px;
    object-fit: contain;
    border-radius: 0;
}

.job-mobile__info {
    grid-column: 1;
    grid-row: 1;
    flex: 1 1 auto;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.job-mobile__info-org {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 13px;
    color: var(--color-text);
    text-decoration: none !important;
    line-height: 1.3;
}

a.job-mobile__info-org:hover {
    color: var(--color-primary);
}

.job-mobile__info-location {
    font-family: 'Poppins', sans-serif;
    font-size: 12px;
    color: var(--color-text-muted, #777);
    line-height: 1.3;
}

.job-mobile__chips {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
    margin-top: 12px;
    min-width: 0;
}

ul.job_listings li.job_listing .job-card__mobile a.job-mobile__chip,
.job-mobile__chip {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    flex: 0 1 auto;
    min-width: 0;
    max-width: 100%;
    min-height: 28px;
    padding: 5px 12px !important;
    border-radius: 999px;
    background: #ffffff !important;
    border: 1px solid #DEDEDE !important;
    box-shadow: none !important;
    font-family: 'Poppins', sans-serif;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.15;
    color: var(--color-text) !important;
    text-decoration: none !important;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

ul.job_listings li.job_listing .job-card__mobile a.job-mobile__chip:hover,
ul.job_listings li.job_listing .job-card__mobile a.job-mobile__chip:focus {
    background: rgba(124, 92, 250, 0.06) !important;
    border-color: rgba(124, 92, 250, 0.32) !important;
    color: var(--color-primary) !important;
}

.job-mobile__cta {
    display: none;
}

/* ── Mobiele toggle ──────────────────────────────────────── */
@media (max-width: 960px) {
    ul.job_listings li.job_listing {
        padding: 0 !important;
    }

    .job-card {
        padding: 16px;
        margin-left: 12px;
        margin-right: 12px;
    }

    .job-card__desktop {
        display: none;
    }

    .job-card__mobile {
        display: block;
    }

    .job_listing,
    .job_listing_content {
        padding: 0 !important;
    }
}

@media (max-width: 480px) {
    ul.job_listings {
        margin: 20px auto !important;
    }

    .job-card {
        padding: 14px;
    }

    .job-mobile__top {
        gap: 8px;
    }

    .job-mobile__body {
        grid-template-columns: minmax(0, 1fr) 72px;
        gap: 12px;
        margin-top: 6px;
    }

    .job-mobile__body--no-logo {
        grid-template-columns: minmax(0, 1fr);
    }

    .job-mobile__logo {
        width: 72px;
        height: 72px;
    }

    .job-mobile__logo img {
        width: 60px;
        height: 60px;
    }

    .job-mobile__favorite .sj-favorite-button {
        width: 36px;
        height: 36px;
    }

    .job-card__mobile .job-mobile__title,
    .job-card__mobile .job-mobile__title-link .job-mobile__title {
        font-size: 18px;
        line-height: 1.28;
    }

    .job-mobile__info-org {
        font-size: 12px;
    }

    .job-mobile__info-location {
        font-size: 11px;
    }

    .job-mobile__chips {
        gap: 6px;
        margin-top: 10px;
    }

    ul.job_listings li.job_listing .job-card__mobile a.job-mobile__chip,
    .job-mobile__chip {
        min-height: 26px;
        padding: 4px 10px !important;
        font-size: 11px;
    }
}

input[type='text']::placeholder {
    font-size: 13px;
}
</style>

<script>
(function () {
    if (!window.__sjJobCardLinkGuard) {
        window.__sjJobCardLinkGuard = true;
        document.addEventListener('click', function (e) {
            var link = e.target.closest(
                'a.job-card-meta__filter-link, a.job-card-meta__location-link, a.job-card-meta__company-link'
            );
            if (!link) return;
            e.stopPropagation();
            e.stopImmediatePropagation();
            window.location.href = link.href;
        }, true);
    }

    if (window.__sjJobCardClick) return;
    window.__sjJobCardClick = true;

    document.addEventListener('click', function (e) {
        var card = e.target.closest('.job-card[data-href]');
        if (!card) return;
        if (e.target.closest('a, button, input, select, textarea, label')) return;

        var href = card.getAttribute('data-href');
        if (href) {
            window.location.href = href;
        }
    });
}());
</script>
