<?php
if (!defined('ABSPATH')) exit;

$page_id      = get_the_ID();
$image_url    = get_the_post_thumbnail_url($page_id, 'full');
$title_raw    = get_post_meta($page_id, 'split_hero_title', true)        ?: 'De beste freelancers vinden via Inhuren.com.';
$accent_terms = get_post_meta($page_id, 'split_hero_accent_terms', true) ?: 'freelancers';
$description  = get_post_meta($page_id, 'split_hero_description', true)  ?: 'Inhuren.com is dé marktplaats voor freelancers en opdrachtgevers. Vind de juiste professional voor jouw opdracht.';
$social_proof = get_post_meta($page_id, 'split_hero_social_proof', true) ?: 'Bekijk alle openstaande opdrachten in ons netwerk.';
$btn1_label   = 'Freelancer inhuren';
$btn1_url     = home_url('/freelancer-inhuren/');
$btn2_label   = 'Bekijk opdrachten';
$btn2_url     = home_url('/opdrachten/');

$title_html = function_exists('inhuren_split_hero_highlight')
    ? inhuren_split_hero_highlight($title_raw, $accent_terms)
    : esc_html($title_raw);
?>

<section class="omj-split-hero inhuren-split-hero"
    aria-labelledby="split-hero-title"
    <?php if ($image_url) : ?>
        style="background-image: url('<?php echo esc_url($image_url); ?>');"
    <?php endif; ?>>

    <div class="omj-split-hero__inner">

        <h1 class="hero-title" id="split-hero-title"><?php echo $title_html; ?></h1>

        <?php if ($description) : ?>
            <p class="omj-split-hero__description"><?php echo esc_html($description); ?></p>
        <?php endif; ?>

        <div class="omj-split-hero__actions">
            <a class="omj-split-btn omj-split-btn--primary omj-split-btn--freelancer-inhuren" href="<?php echo esc_url($btn1_url); ?>">
                <?php echo esc_html($btn1_label); ?>
            </a>
            <a class="omj-split-btn omj-split-btn--secondary omj-split-btn--bekijk-opdrachten" href="<?php echo esc_url($btn2_url); ?>">
                <?php echo esc_html($btn2_label); ?>
            </a>
        </div>

        <?php if ($social_proof) : ?>
            <p class="omj-split-hero__social-proof">
                <svg class="omj-split-hero__social-proof-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                <?php echo esc_html($social_proof); ?>
            </p>
        <?php endif; ?>

    </div>

</section>
