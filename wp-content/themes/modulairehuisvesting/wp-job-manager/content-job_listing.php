<?php
/**
 * Job listing in the loop.
 *
 * Template override: yourtheme/wp-job-manager/content-job_listing.php
 */

if (!defined('ABSPATH')) {
    exit;
}

global $post;

$background_image = get_the_post_thumbnail_url($post->ID, 'full') ?: '';
?>
<li <?php job_listing_class(); ?>
    data-longitude="<?php echo esc_attr($post->geolocation_long); ?>"
    data-latitude="<?php echo esc_attr($post->geolocation_lat); ?>"
>
    <div class="job-card" data-href="<?php the_job_permalink(); ?>">
        <div class="job-card__desktop">
            <div class="job-card__media">
                <div class="background-wrapper">
                    <div class="company-logo-absolute hide_on_single">
                        <div class="company-logo-wrapper">
                            <?php the_company_logo(); ?>
                        </div>
                    </div>

                    <div
                        class="background-inner"
                        style="
                            background-image: url('<?php echo esc_url($background_image); ?>');
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
                </div>
            </div>

            <div class="job-card__content">
                <div class="job_listing_content">
                    <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" class="title-link">
                        <h2><?php wpjm_the_job_title(); ?></h2>
                    </a>

                    <div class="job_text">
                        <p><?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?></p>
                    </div>

                    <ul class="job-card-meta">
                        <li class="job-card-meta__item job-card-meta__item--org">
                            <span class="job-card-meta__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </span>
                            <div class="job-card-meta__textgroup">
                                <p class="job-card-meta__title"><?php the_company_name(); ?></p>
                                <?php the_company_tagline(); ?>
                            </div>
                        </li>

                        <li class="job-card-meta__item job-card-meta__item--location">
                            <span class="job-card-meta__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true"><path d="M12 2a7 7 0 0 1 7 7c0 5.25-7 13-7 13S5 14.25 5 9a7 7 0 0 1 7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                            </span>
                            <p class="job-card-meta__text"><?php the_job_location(false); ?></p>
                        </li>

                        <?php if (function_exists('display_tax_terms') && display_tax_terms('job_listing_type', $post->ID)) : ?>
                            <li class="job-card-meta__item job-card-meta__item--type">
                                <span class="job-card-meta__icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                                </span>
                                <p class="job-card-meta__text"><?php echo display_tax_terms('job_listing_type', $post->ID); ?></p>
                            </li>
                        <?php endif; ?>

                        <li class="job-card-meta__item job-card-meta__item--date">
                            <span class="job-card-meta__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
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

        <div class="job-card__mobile">
            <a class="job-mobile__link" href="<?php the_job_permalink(); ?>">
                <div class="job-mobile__top">
                    <div class="job-mobile__logo">
                        <?php the_company_logo(); ?>
                    </div>

                    <div class="job-mobile__toptext">
                        <div class="job-mobile__company"><?php the_company_name(); ?></div>
                        <h2 class="job-mobile__title"><?php wpjm_the_job_title(); ?></h2>
                    </div>
                </div>

                <div class="job-mobile__excerpt">
                    <?php echo esc_html(wp_trim_words(get_the_excerpt(), 8, '...')); ?>
                </div>

                <ul class="job-mobile__meta">
                    <li class="job-mobile__meta-item">
                        <span class="job-mobile__icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16" aria-hidden="true"><path d="M12 2a7 7 0 0 1 7 7c0 5.25-7 13-7 13S5 14.25 5 9a7 7 0 0 1 7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                        </span>
                        <span class="job-mobile__text"><?php the_job_location(false); ?></span>
                    </li>

                    <?php if (function_exists('display_tax_terms') && display_tax_terms('job_listing_type', $post->ID)) : ?>
                        <li class="job-mobile__meta-item">
                            <span class="job-mobile__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                            </span>
                            <span class="job-mobile__text"><?php echo esc_html(display_tax_terms('job_listing_type', $post->ID)); ?></span>
                        </li>
                    <?php endif; ?>

                    <li class="job-mobile__meta-item">
                        <span class="job-mobile__icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </span>
                        <span class="job-mobile__text"><?php the_job_publish_date(); ?></span>
                    </li>
                </ul>

                <div class="job-mobile__cta">Vacature bekijken</div>
            </a>
        </div>
    </div>
</li>

<script>
(function () {
    if (window.__mhJobCardClick) return;
    window.__mhJobCardClick = true;
    document.addEventListener('click', function (e) {
        var card = e.target.closest('.job-card[data-href]');
        if (!card) return;
        if (e.target.closest('a, button, input, select, textarea, label')) return;
        var href = card.getAttribute('data-href');
        if (href) { window.location.href = href; }
    });
}());
</script>
