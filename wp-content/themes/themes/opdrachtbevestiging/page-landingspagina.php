<?php
/*
Template Name: Homepage-template
*/

if (!defined('ABSPATH')) {
    exit;
}

$hero_image_id  = get_post_thumbnail_id(get_the_ID());
$hero_image     = $hero_image_id ? wp_get_attachment_image_url($hero_image_id, 'full') : '';
$hero_image     = ''; // TEMP: afbeelding uitgezet als test, verwijder deze regel om terug te zetten
$hero_image_alt = '';

if ($hero_image_id) {
    $hero_image_alt = get_post_meta($hero_image_id, '_wp_attachment_image_alt', true);
}

if ('' === $hero_image_alt) {
    $hero_image_alt = get_the_title();
}

get_header();
?>

<div class="ob-home">
    <section class="ob-home-hero<?php echo $hero_image ? ' ob-home-hero--has-image' : ''; ?>" aria-labelledby="ob-home-hero-title">
        <div class="ob-home-hero__inner">
            <div class="ob-home-hero__content">
                <span class="ob-home-hero__eyebrow">Opdrachtbevestiging.nl</span>
                <h1 id="ob-home-hero-title" class="ob-home-hero__title"><span class="ob-home-hero__title-highlight">Opdrachtbevestigingen</span> versturen en beheren!</h1>
                <p class="ob-home-hero__subtitle">Stel eenvoudig een professionele opdrachtbevestiging op die jouw klant binnen een paar seconden per e-mail kan accorderen.</p>
                <div class="ob-home-hero__actions">
                    <a href="https://beheer.opdrachtbevestiging.nl/registreren" class="ob-home-hero__btn ob-home-hero__btn--primary" target="_blank" rel="noopener noreferrer">Gratis proberen</a>
                    <a href="https://beheer.opdrachtbevestiging.nl/tarieven/" class="ob-home-hero__btn ob-home-hero__btn--secondary" target="_blank" rel="noopener noreferrer">Wat kost het</a>
                </div>

                <ul class="ob-home-hero__features">
                    <li class="ob-home-hero__feature">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>Incl. Kamer van Koophandel API</span>
                    </li>
                    <li class="ob-home-hero__feature">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>Accordering trails</span>
                    </li>
                    <li class="ob-home-hero__feature">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>Domein extensies</span>
                    </li>
                    <li class="ob-home-hero__feature">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>Geen juridische kennis nodig</span>
                    </li>
                </ul>
            </div>

            <?php if ($hero_image) : ?>
                <div class="ob-home-hero__visual">
                    <div class="ob-home-hero__image-wrap">
                        <img src="<?php echo esc_url($hero_image); ?>" alt="<?php echo esc_attr($hero_image_alt); ?>" class="ob-home-hero__image" loading="eager" decoding="async">
                    </div>

                    <div class="ob-home-hero__pager" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php the_content(); ?>

<?php
get_footer();
