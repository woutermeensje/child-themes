<?php
if (!defined('ABSPATH')) {
    exit;
}

$page_id        = get_the_ID();
$hero_image     = get_the_post_thumbnail_url($page_id, 'full');
$title          = get_the_title($page_id);
$subtitle       = has_excerpt($page_id)
    ? get_the_excerpt($page_id)
    : 'Plaats onbeperkt vacatures en bereik professionals die bewust kiezen voor werk met maatschappelijke impact.';
$primary_label  = get_post_meta($page_id, 'landing_primary_button_text', true) ?: 'Vacature plaatsen';
$primary_url    = get_post_meta($page_id, 'landing_primary_button_url', true)  ?: home_url('/vacature-plaatsen/');
$eyebrow        = get_post_meta($page_id, 'landing_eyebrow', true) ?: 'Werkgever';

if (!$hero_image) {
    $attachment = get_page_by_path('fondsen-org-non-profit-vacaturesite-en-platform', OBJECT, 'attachment');
    $hero_image = $attachment ? wp_get_attachment_url($attachment->ID) : '';
}

$bg_style = $hero_image ? ' style="background-image: url(\'' . esc_url($hero_image) . '\');"' : '';
?>

<div class="fondsen-lp">
    <div class="fondsen-job-hero"<?php echo $bg_style; ?>>
        <div class="fondsen-job-hero__inner">
            <span class="fondsen-job-hero__eyebrow"><?php echo esc_html($eyebrow); ?></span>
            <div class="fondsen-job-hero__title-wrap">
                <h1 class="fondsen-job-hero__title">Vind gemotiveerd talent voor jouw <span class="fondsen-job-hero__count">non-profit organisatie</span>.</h1>
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
