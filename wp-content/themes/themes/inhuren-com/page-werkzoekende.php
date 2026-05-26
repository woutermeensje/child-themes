<?php
/*
Template Name: Landingspagina Werkzoekende
*/

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) :
    while (have_posts()) : the_post();
        ?>
        <main id="content" <?php post_class('fnd-page'); ?>>
            <?php include get_stylesheet_directory() . '/template-parts/landing-werkzoekende.php'; ?>

            <div class="fnd-page__elementor-content">
                <?php the_content(); ?>
            </div>
        </main>
        <?php
    endwhile;
endif;

get_footer();
