<?php
if (!defined('ABSPATH')) exit;

$page_id      = get_the_ID();
$image_url    = get_the_post_thumbnail_url($page_id, 'full');
$title_raw    = get_post_meta($page_id, 'split_hero_title', true)        ?: 'Flexibele inhuur voor jouw Project, Opdracht of Vacature.';
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
        <a class="omj-split-hero__bar-cta" href="https://platform.inhuren.com/werkzoekende/registreren" target="_blank" rel="noopener noreferrer">
            Ga aan de slag via Inhuren.com
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a>

        <a class="omj-split-hero__profiel-btn" href="https://platform.inhuren.com/werkzoekende/registreren" target="_blank" rel="noopener noreferrer">Profiel aanmaken</a>
    </div>
</section>
