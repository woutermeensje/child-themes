<?php
/*
Template Name: Landingspagina Template
*/

if (!defined('ABSPATH')) {
    exit;
}

$hero_image = get_the_post_thumbnail_url(get_the_ID(), 'full');

if (!$hero_image) {
    $attachment = get_page_by_path('opdrachtbevestiging', OBJECT, 'attachment');
    $hero_image = $attachment ? wp_get_attachment_url($attachment->ID) : '';
}

$bg_style = $hero_image ? ' style="background-image: url(\'' . esc_url($hero_image) . '\');"' : '';

get_header();
?>

<div class="fondsen-lp">
    <div class="fondsen-job-hero"<?php echo $bg_style; ?>>
        <div class="fondsen-job-hero__inner">
            <span class="fondsen-job-hero__eyebrow">Opdrachtbevestiging.nl</span>
            <div class="fondsen-job-hero__title-wrap">
                <h1 class="fondsen-job-hero__title">Professionele opdrachtbevestigingen in <span class="fondsen-job-hero__count">één platform</span>.</h1>
                <p class="fondsen-job-hero__subtitle">Stel in minuten een juridisch correcte opdrachtbevestiging op, verstuur hem digitaal en houd alle bevestigingen overzichtelijk bij.</p>
                <a href="https://beheer.opdrachtbevestiging.nl/registreren" class="fondsen-lp__btn" target="_blank" rel="noopener noreferrer">Aan de slag</a>
            </div>
        </div>
    </div>
</div>

<?php the_content(); ?>

<?php
get_footer();
