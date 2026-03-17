<?php
if (!defined('ABSPATH')) {
    exit;
}

$page_id = get_the_ID();
$hero_image_url = get_the_post_thumbnail_url($page_id, 'full');
$title = get_the_title($page_id);
$intro = has_excerpt($page_id)
    ? get_the_excerpt($page_id)
    : 'Ontdek vacatures, betekenisvolle organisaties en nieuwe kansen om met jouw talent impact te maken binnen non-profits.';
$primary_label = get_post_meta($page_id, 'landing_primary_button_text', true) ?: 'Ik zoek werk';
$primary_url = get_post_meta($page_id, 'landing_primary_button_url', true) ?: home_url('/vacatures/');
$secondary_label = get_post_meta($page_id, 'landing_secondary_button_text', true) ?: 'Bekijk organisaties';
$secondary_url = get_post_meta($page_id, 'landing_secondary_button_url', true) ?: home_url('/organisaties/');
?>

<main id="content" <?php post_class('fnd-template fnd-template--werkzoekende'); ?>>
    <section class="fnd-hero">
        <div class="fnd-hero__left">
            <div class="fnd-hero__inner">
                <h1 class="fnd-hero__title"><?php echo esc_html($title); ?></h1>

                <?php if ($intro) : ?>
                    <p class="fnd-hero__intro"><?php echo esc_html($intro); ?></p>
                <?php endif; ?>

                <div class="fnd-hero__actions">
                    <?php if ($primary_label && $primary_url) : ?>
                        <a class="fnd-hero__btn fnd-hero__btn--primary" href="<?php echo esc_url($primary_url); ?>">
                            <?php echo esc_html($primary_label); ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($secondary_label && $secondary_url) : ?>
                        <a class="fnd-hero__btn fnd-hero__btn--secondary" href="<?php echo esc_url($secondary_url); ?>">
                            <?php echo esc_html($secondary_label); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="fnd-hero__right<?php echo $hero_image_url ? '' : ' fnd-hero__right--placeholder'; ?>">
            <?php if ($hero_image_url) : ?>
                <img class="fnd-hero__image" src="<?php echo esc_url($hero_image_url); ?>" alt="<?php echo esc_attr($title); ?>">
            <?php endif; ?>
        </div>
    </section>
</main>
