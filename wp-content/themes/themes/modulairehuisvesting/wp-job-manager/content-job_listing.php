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
$job_location = function_exists('get_the_job_location') ? get_the_job_location($post) : '';
?>
<li <?php job_listing_class(); ?>
    data-longitude="<?php echo esc_attr($post->geolocation_long); ?>"
    data-latitude="<?php echo esc_attr($post->geolocation_lat); ?>"
>
    <article class="job-card">
        <a class="job-card__link" href="<?php the_job_permalink(); ?>">
            <div class="job-card__logo" aria-hidden="true">
                <?php the_company_logo('medium'); ?>
            </div>

            <div class="job-card__body">
                <h2 class="job-card__title"><?php wpjm_the_job_title(); ?></h2>

                <?php if ($job_location) : ?>
                    <p class="job-card__location">
                        <span class="job-card__location-icon" aria-hidden="true"></span>
                        <span><?php echo esc_html(wp_strip_all_tags($job_location)); ?></span>
                    </p>
                <?php endif; ?>
            </div>

            <span class="job-card__arrow" aria-hidden="true"></span>
        </a>
    </article>
</li>
