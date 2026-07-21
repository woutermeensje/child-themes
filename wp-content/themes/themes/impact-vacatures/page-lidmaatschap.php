<?php
/*
Template Name: Impact Vacatures Lidmaatschap
*/

if (!defined('ABSPATH')) {
    exit;
}

$hero_image = get_the_post_thumbnail_url(get_the_ID(), 'full');

if (!$hero_image) {
    $hero_image = function_exists('impact_vacatures_get_default_hero_image_url')
        ? impact_vacatures_get_default_hero_image_url()
        : '';
}

$bg_style = $hero_image ? ' style="background-image: url(\'' . esc_url($hero_image) . '\');"' : '';

get_header();
?>

<div class="fondsen-lp">
    <div class="fondsen-job-hero"<?php echo $bg_style; ?>>
        <div class="fondsen-job-hero__inner">
            <span class="fondsen-job-hero__eyebrow">Lidmaatschap</span>
            <div class="fondsen-job-hero__title-wrap">
                <h1 class="fondsen-job-hero__title">Bekijk waarom <span class="fondsen-job-hero__count">37 Non Profits</span> gebruikmaken van een op maat gemaakt <span class="fondsen-job-hero__count">lidmaatschap</span>.</h1>
                <p class="fondsen-job-hero__subtitle">Het lidmaatschap biedt een aantal voordelen, onder meer het onbeperkt plaatsen van vacatures en nieuwsberichten.</p>
                <a href="#informatie-aanvragen" class="fondsen-lp__btn">Informatie aanvragen</a>
            </div>
        </div>
    </div>

</div>

<?php the_content(); ?>

<?php
get_footer();
