<?php
if (!defined('ABSPATH')) {
    exit;
}

$page_id       = get_the_ID();
$hero_image    = get_the_post_thumbnail_url($page_id, 'full');
$title         = get_the_title($page_id);
$subtitle      = has_excerpt($page_id)
    ? get_the_excerpt($page_id)
    : 'Ontdek sport- en beweegmogelijkheden in Lansingerland.';
$primary_label = get_post_meta($page_id, 'landing_primary_button_text', true) ?: 'Meer informatie';
$primary_url   = get_post_meta($page_id, 'landing_primary_button_url', true)  ?: home_url('/');
$eyebrow       = get_post_meta($page_id, 'landing_eyebrow', true) ?: 'LansingerlandFit';
$eyebrow       = str_replace('Lansingerland Fit', 'LansingerlandFit', $eyebrow);

$bg_style = $hero_image ? ' style="background-image: url(\'' . esc_url($hero_image) . '\');"' : '';
?>

<div class="lf-lp">
    <div class="lf-job-hero"<?php echo $bg_style; ?>>
        <div class="lf-job-hero__inner">
            <span class="lf-job-hero__eyebrow"><?php echo esc_html($eyebrow); ?></span>
            <div class="lf-job-hero__title-wrap">
                <h1 class="lf-job-hero__title"><?php echo esc_html($title); ?></h1>
                <?php if ($subtitle) : ?>
                    <p class="lf-job-hero__subtitle"><?php echo esc_html($subtitle); ?></p>
                <?php endif; ?>
            </div>
            <?php if ($primary_label && $primary_url) : ?>
                <a href="<?php echo esc_url($primary_url); ?>" class="lf-lp__btn"><?php echo esc_html($primary_label); ?></a>
            <?php endif; ?>
        </div>
    </div>

    <?php the_content(); ?>
</div>
