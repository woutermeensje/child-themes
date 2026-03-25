<?php
/**
 * Ingenieurvacaturebank – Job listing card (fondsen stijl, SJ kleuren)
 */
if (!defined('ABSPATH')) exit;
global $post;

$cover_image      = get_post_meta($post->ID, '_cover_image', true);
$background_image = $cover_image ?: get_the_post_thumbnail_url($post->ID, 'full');
$job_types        = wpjm_get_the_job_types();
?>

<li <?php job_listing_class(); ?>
    data-longitude="<?php echo esc_attr($post->geolocation_long ?? ''); ?>"
    data-latitude="<?php echo esc_attr($post->geolocation_lat ?? ''); ?>"
>
    <div class="job-card" data-href="<?php the_job_permalink(); ?>">

        <!-- Desktop kaart -->
        <div class="job-card__desktop">

            <div class="job-card__media">
                <div class="background-wrapper">
                    <div class="company-logo-absolute hide_on_single">
                        <div class="company-logo-wrapper">
                            <?php the_post_thumbnail(); ?>
                        </div>
                    </div>

                    <?php if ($background_image) : ?>
                        <div class="background-inner" style="
                            background-image: url('<?php echo esc_url($background_image); ?>');
                            background-size: cover;
                            min-height: 100%;
                            display: block;
                            width: 100%;
                            background-repeat: no-repeat;
                            background-position: center center;
                        "></div>
                    <?php else : ?>
                        <div class="background-inner background-inner--empty"></div>
                    <?php endif; ?>

                    <div class="block-bg-overlay"></div>
                </div>
            </div>

            <div class="job-card__content">
                <div class="job_listing_content">
                    <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" class="title-link">
                        <h2><?php wpjm_the_job_title(); ?></h2>
                    </a>

                    <div class="job_text">
                        <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 15, '...')); ?></p>
                    </div>

                    <ul class="job-card-meta">
                        <li class="job-card-meta__item">
                            <span class="job-card-meta__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </span>
                            <div class="job-card-meta__textgroup">
                                <p class="job-card-meta__title"><?php the_company_name(); ?></p>
                                <?php the_company_tagline(); ?>
                            </div>
                        </li>

                        <?php if (get_the_job_location()) : ?>
                        <li class="job-card-meta__item">
                            <span class="job-card-meta__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M12 2a7 7 0 0 1 7 7c0 5.25-7 13-7 13S5 14.25 5 9a7 7 0 0 1 7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                            </span>
                            <p class="job-card-meta__text"><?php the_job_location(false); ?></p>
                        </li>
                        <?php endif; ?>

                        <?php if ($job_types) : ?>
                        <li class="job-card-meta__item">
                            <span class="job-card-meta__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                            </span>
                            <p class="job-card-meta__text"><?php echo esc_html(implode(', ', wp_list_pluck($job_types, 'name'))); ?></p>
                        </li>
                        <?php endif; ?>

                        <li class="job-card-meta__item">
                            <span class="job-card-meta__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            </span>
                            <p class="job-card-meta__text"><?php the_job_publish_date(); ?></p>
                        </li>
                    </ul>

                    <div class="jobs_buttons">
                        <a href="<?php the_job_permalink(); ?>">Vacature bekijken</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobiele kaart -->
        <div class="job-card__mobile">
            <a class="job-mobile__link" href="<?php the_job_permalink(); ?>">
                <div class="job-mobile__top">
                    <div class="job-mobile__logo"><?php the_post_thumbnail(); ?></div>
                    <div class="job-mobile__toptext">
                        <div class="job-mobile__company"><?php the_company_name(); ?></div>
                        <h2 class="job-mobile__title"><?php wpjm_the_job_title(); ?></h2>
                    </div>
                </div>

                <div class="job-mobile__excerpt">
                    <?php echo esc_html(wp_trim_words(get_the_excerpt(), 8, '...')); ?>
                </div>

                <ul class="job-mobile__meta">
                    <?php if (get_the_job_location()) : ?>
                    <li class="job-mobile__meta-item">
                        <span class="job-mobile__icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M12 2a7 7 0 0 1 7 7c0 5.25-7 13-7 13S5 14.25 5 9a7 7 0 0 1 7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                        </span>
                        <span class="job-mobile__text"><?php the_job_location(false); ?></span>
                    </li>
                    <?php endif; ?>

                    <?php if ($job_types) : ?>
                    <li class="job-mobile__meta-item">
                        <span class="job-mobile__icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                        </span>
                        <span class="job-mobile__text"><?php echo esc_html(implode(', ', wp_list_pluck($job_types, 'name'))); ?></span>
                    </li>
                    <?php endif; ?>

                    <li class="job-mobile__meta-item">
                        <span class="job-mobile__icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </span>
                        <span class="job-mobile__text"><?php the_job_publish_date(); ?></span>
                    </li>
                </ul>

                <div class="job-mobile__cta">Vacature bekijken</div>
            </a>
        </div>
    </div>
</li>

<style>
/* ==============================================
   Ingenieurvacaturebank – Job card
   ============================================== */
ul.job_listings {
    list-style: none !important;
    max-width: 1140px;
    margin: 30px auto !important;
    padding: 0 !important;
    border: none !important;
    display: flex;
    flex-direction: column;
}

div.job_listings ul.job_listings {
    padding-left: 0 !important;
    border: none !important;
}

ul.job_listings li.job_listing {
    padding: 15px 30px !important;
    border-bottom: none !important;
    list-style: none !important;
    margin: 0 !important;
}

.job-card {
    background: #ffffff;
    border-radius: 6px;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
    border: 1px solid #E0E0E0;
    overflow: hidden;
    cursor: pointer;
    transition: box-shadow .2s ease, border-color .2s ease;
}

.job-card:hover {
    border-color: #0A6B8D;
    box-shadow: 0 4px 24px rgba(10, 107, 141, 0.16);
}

.job-card__desktop { display: flex; flex-wrap: nowrap; }
.job-card__mobile  { width: 100%; }

/* Media */
.job-card__media {
    position: relative;
    flex: 0 0 40%;
    max-width: 40%;
    display: flex;
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

.background-inner--empty {
    background: #0A6B8D;
    opacity: 0.12;
}

.block-bg-overlay {
    position: absolute;
    width: 100%; height: 100%;
    top: 0;
    background-color: #0A6B8D;
    opacity: 0.30;
}

/* Bedrijfslogo */
.company-logo-absolute {
    position: absolute;
    border-radius: 50%;
    width: 80px; height: 80px;
    background: white;
    z-index: 9;
    left: 30px; bottom: 30px;
}

ul.job_listings li.job_listing .company-logo-wrapper img {
    border-radius: 50%;
    width: 80px; height: 80px;
    border: 1px solid #eee;
    padding: 5px;
    object-fit: contain;
    background: #fff;
}

/* Content */
.job-card__content {
    flex: 0 0 60%;
    max-width: 60%;
    display: flex;
    align-items: center;
    min-width: 0;
    padding: 24px 0;
}

.job_listing .job_listing_content {
    padding: 0 40px;
    min-width: 0;
}

a.title-link {
    background-color: #ffffff !important;
    padding: 0 !important;
    color: #333333;
    text-decoration: none;
}

.job_listing .job_listing_content h2 {
    margin: 0 0 5px 0;
    font-family: 'Poppins', sans-serif;
    font-size: 20px;
    font-weight: 700;
    color: #111827;
    overflow-wrap: anywhere;
    word-break: break-word;
}

.job-card:hover a.title-link h2 { color: #0A6B8D; }

.job_listing .job_listing_content .job_text {
    margin: 14px 0;
}

.job_listing .job_listing_content .job_text p {
    margin: 0 0 10px 0;
    color: #333;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
}

/* Meta */
.job-card-meta {
    list-style: none !important;
    margin: 0; padding: 0;
    color: #333;
}

.job-card-meta__item {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 6px;
    flex-direction: row !important;
    list-style: none !important;
}

.job-card-meta__icon {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    color: #0A6B8D;
}

.job-card-meta__text,
.job-card-meta__title {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
}

.job-card-meta__title { font-weight: 600; }

.job-card-meta__textgroup {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

/* CTA knop */
.jobs_buttons { margin-top: 24px; }

.jobs_buttons a {
    display: inline-block !important;
    background: #0A6B8D !important;
    color: white !important;
    border: 1px solid #0A6B8D !important;
    padding: 0 30px !important;
    height: 48px !important;
    line-height: 48px !important;
    border-radius: 4px !important;
    text-decoration: none !important;
    font-family: 'Poppins', sans-serif !important;
    font-weight: 600 !important;
    font-size: 15px;
    transition: background .2s ease, border-color .2s ease;
    max-width: 60%;
    text-align: center;
}

.jobs_buttons a:hover {
    background: #085978 !important;
    border-color: #085978 !important;
}

/* Mobiele kaart */
.job-card__mobile { display: none; }

.job-mobile__link {
    display: block;
    text-decoration: none !important;
    color: inherit;
    padding: 18px;
}

.job-mobile__top {
    display: flex;
    gap: 14px;
    align-items: center;
}

.job-mobile__logo {
    flex: 0 0 auto;
    width: 56px; height: 56px;
    border-radius: 999px;
    background: #fff;
    border: 1px solid #eee;
    display: grid;
    place-items: center;
    overflow: hidden;
}

.job-mobile__logo img {
    width: 42px; height: 42px;
    object-fit: contain;
    border-radius: 999px;
}

.job-mobile__company {
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 14px;
    color: #111827;
    line-height: 1.2;
}

.job-mobile__title {
    margin: 4px 0 0 0 !important;
    font-family: 'Poppins', sans-serif !important;
    font-weight: 700 !important;
    font-size: 18px !important;
    color: #111827 !important;
    overflow-wrap: anywhere;
}

.job-mobile__excerpt {
    margin-top: 12px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    line-height: 1.6;
    color: #333;
}

.job-mobile__meta {
    list-style: none !important;
    padding: 0;
    margin: 14px 0 0 0;
    display: grid;
    gap: 8px;
}

.job-mobile__meta-item {
    display: flex;
    gap: 10px;
    align-items: center;
    list-style: none !important;
}

.job-mobile__icon {
    display: flex;
    align-items: center;
    flex: 0 0 auto;
    color: #0A6B8D;
}

.job-mobile__text {
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    color: #111827;
    overflow-wrap: anywhere;
}

.job-mobile__cta {
    display: inline-block;
    margin-top: 18px;
    background: #0A6B8D;
    color: #fff;
    padding: 10px 22px;
    border-radius: 4px;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 14px;
}

@media (max-width: 960px) {
    ul.job_listings li.job_listing { padding: 12px !important; }
    .job-card__desktop { display: none; }
    .job-card__mobile  { display: block; }
    .job_listing,
    .job_listing_content { padding: 0 !important; }
}
</style>
