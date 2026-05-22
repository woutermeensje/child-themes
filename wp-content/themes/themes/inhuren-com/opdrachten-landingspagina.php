<?php
/*
Template Name: opdrachten-landingspagina
*/

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();

    $hero_image_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
    $opdrachten_shortcode = trim((string) get_post_meta(get_the_ID(), 'opdrachten_shortcode', true));
    $opdrachten_shortcode = apply_filters('inhuren_opdrachten_landing_shortcode', $opdrachten_shortcode, get_the_ID());

    if (!$hero_image_url && file_exists(get_stylesheet_directory() . '/images/duurzame-vacatures.jpg')) {
        $hero_image_url = get_stylesheet_directory_uri() . '/images/duurzame-vacatures.jpg';
    }
    ?>

    <main id="content" <?php post_class('opdrachten-landing'); ?>>
        <section
            class="opdrachten-landing__hero"
            <?php if ($hero_image_url) : ?>
                style="background-image: url('<?php echo esc_url($hero_image_url); ?>');"
            <?php endif; ?>
        ></section>

        <section class="opdrachten-landing__content-wrap" aria-label="<?php echo esc_attr(get_the_title()); ?>">
            <div class="opdrachten-landing__content">
                <?php
                if ($opdrachten_shortcode) {
                    echo do_shortcode($opdrachten_shortcode);
                }
                ?>
            </div>
        </section>
    </main>

    <?php the_content(); ?>

<?php
endwhile;

get_footer();
