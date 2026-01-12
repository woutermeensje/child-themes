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
    margin-bottom: 1rem;
}

.landing-left p {
    font-size: 1.1rem;
    line-height: 1.5;
    margin-bottom: 1.5rem;
    max-width: 90%;
}

.landing-left a {
    background-color: #80D424;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    font-weight: bold;
    border-radius: 4px;
    max-width: fit-content;
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
