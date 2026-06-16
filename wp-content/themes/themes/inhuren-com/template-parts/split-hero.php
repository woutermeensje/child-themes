<?php
if (!defined('ABSPATH')) exit;

$page_id      = get_the_ID();
$image_url    = get_the_post_thumbnail_url($page_id, 'full');
$title_raw    = get_post_meta($page_id, 'split_hero_title', true)        ?: 'Flexibele inhuur voor jouw project, opdracht of klus.';
$accent_terms = get_post_meta($page_id, 'split_hero_accent_terms', true) ?: 'freelancers';
$description  = get_post_meta($page_id, 'split_hero_description', true)  ?: 'Geef jouw opdracht, project of klus door, ontvang een offerte en geef akkoord! Wij regelen de inzet!';

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
        <span class="omj-split-hero__eyebrow">Inhuren.com</span>

        <div class="omj-split-hero__title-wrap">
            <h1 class="hero-title" id="split-hero-title"><?php echo $title_html; ?></h1>

            <?php if ($description) : ?>
                <p class="omj-split-hero__description"><?php echo esc_html($description); ?></p>
            <?php endif; ?>
        </div>
    </div>

</section>

<section class="omj-split-hero__bar inhuren-split-hero__bar" aria-label="Inhuren.com acties">
    <div class="omj-split-hero__bar-inner">
        <div class="omj-split-hero__facts">
            <a class="omj-split-hero__fact" href="mailto:team@inhuren.com">
                <span class="omj-split-hero__fact-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                </span>
                <span class="omj-split-hero__fact-text">team@inhuren.com</span>
            </a>

            <a class="omj-split-hero__fact" href="tel:+31852392040">
                <span class="omj-split-hero__fact-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.86 19.86 0 0 1 11.19 19a19.5 19.5 0 0 1-6-6A19.86 19.86 0 0 1 2.08 4.18 2 2 0 0 1 4.06 2h3a2 2 0 0 1 2 1.72c.13.96.35 1.89.66 2.78a2 2 0 0 1-.45 2.11L8 9.88a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.89.31 1.82.53 2.78.66A2 2 0 0 1 22 16.92z"/></svg>
                </span>
                <span class="omj-split-hero__fact-text">085-2392040</span>
            </a>

            <span class="omj-split-hero__fact">
                <span class="omj-split-hero__fact-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
                <span class="omj-split-hero__fact-text">35 werkenden</span>
            </span>

            <span class="omj-split-hero__fact">
                <span class="omj-split-hero__fact-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9h1"/><path d="M9 13h1"/><path d="M9 17h1"/><path d="M15 13h1"/><path d="M15 17h1"/></svg>
                </span>
                <span class="omj-split-hero__fact-text">134 opdrachtgevers</span>
            </span>
        </div>
    </div>
</section>
