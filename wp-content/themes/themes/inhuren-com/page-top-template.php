<?php
/* Template Name: Landingpagina Top */
get_header();
?>

<style>
.landing-top-section {
    position: relative;
    width: 100vw;
    height: 375px;
    background-color: #0458AB; /* Hele achtergrond blauw */
    overflow: hidden;
    display: flex;
}

.landing-left {
    width: 50vw;
    color: white;
    padding: 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    z-index: 2;

}

.landing-left h1 {
    font-size: 2.2rem;
    line-height: 1.1;
    margin-bottom: 1.4rem;
}

.landing-left p {
    font-size: 1.1rem;
    line-height: 1.65;
    margin-bottom: 2rem;
    max-width: 90%;
}

.landing-left a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background-color: #6db81e;
    color: white;
    border: 2px solid #6db81e;
    padding: 8px 20px !important;
    font-family: 'Roboto', sans-serif !important;
    font-weight: 700 !important;
    text-decoration: none;
    border-radius: 7px;
    max-width: fit-content;
    line-height: var(--button-line-height, 1.5);
    transition: background-color .15s ease, border-color .15s ease, transform .15s ease;
}

.landing-left a * {
    font-family: 'Roboto', sans-serif !important;
    font-weight: 700 !important;
}

.landing-left a:hover {
    background-color: #80D424;
    border-color: #80D424;
    transform: translateY(-1px);
}

.landing-left a + a {
    margin-left: 16px;
    background-color: #FF8200;
    border-color: #FF8200;
}

.landing-left a + a:hover {
    background-color: #D96F00;
    border-color: #D96F00;
}

.landing-right {
    position: absolute;
    top: 0;
    right: 0;
    width: 50vw;
    height: 100%;
    background-image: url('<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>');
    background-size: cover;
    background-position: center;
    clip-path: polygon(10% 0, 100% 0, 100% 100%, 0% 100%);
    z-index: 3;
}
</style>

<section class="landing-top-section">
    <div class="landing-left">
        <h1><?php the_title(); ?></h1>
        <?php if ($subtekst = get_post_meta(get_the_ID(), 'subtekst', true)) : ?>
            <p><?php echo esc_html($subtekst); ?></p>
        <?php endif; ?>
        <a href="#cta">Bekijk meer</a>
    </div>
    <div class="landing-right"></div>
</section>

<?php get_footer(); ?>
