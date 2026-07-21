<?php
if (!defined('ABSPATH')) {
    exit;
}

$page_id        = get_the_ID();
$hero_image     = get_the_post_thumbnail_url($page_id, 'full');
$title          = get_the_title($page_id);
$subtitle       = has_excerpt($page_id)
    ? get_the_excerpt($page_id)
    : 'Ontdek vacatures bij non-profit organisaties en vind een baan waar jouw talent echt het verschil maakt.';
$primary_label  = get_post_meta($page_id, 'landing_primary_button_text', true) ?: 'Ik zoek werk';
$primary_url    = get_post_meta($page_id, 'landing_primary_button_url', true)  ?: home_url('/vacatures/');
$eyebrow        = get_post_meta($page_id, 'landing_eyebrow', true) ?: 'Werkzoekende';

if (!$hero_image) {
    $hero_image = function_exists('impact_vacatures_get_default_hero_image_url')
        ? impact_vacatures_get_default_hero_image_url()
        : '';
}

$bg_style = $hero_image ? ' style="background-image: url(\'' . esc_url($hero_image) . '\');"' : '';
?>

<div class="fondsen-lp">
    <div class="fondsen-job-hero"<?php echo $bg_style; ?>>
        <div class="fondsen-job-hero__inner">
            <span class="fondsen-job-hero__eyebrow"><?php echo esc_html($eyebrow); ?></span>
            <div class="fondsen-job-hero__title-wrap">
                <h1 class="fondsen-job-hero__title">Ontdek vacatures met impact bij <span class="fondsen-job-hero__count">non-profit organisaties</span>.</h1>
                <?php if ($subtitle) : ?>
                    <p class="fondsen-job-hero__subtitle"><?php echo esc_html($subtitle); ?></p>
                <?php endif; ?>
            </div>
            <?php if ($primary_label && $primary_url) : ?>
                <a href="<?php echo esc_url($primary_url); ?>" class="fondsen-lp__btn"><?php echo esc_html($primary_label); ?></a>
            <?php endif; ?>
        </div>
    </div>

    <?php the_content(); ?>
</div>
