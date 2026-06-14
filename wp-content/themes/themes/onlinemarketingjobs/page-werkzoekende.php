<?php
/*
Template Name: Onlinemarketingjobs Werkzoekende Landing
*/
if (!defined('ABSPATH')) exit;
get_header();

if (have_posts()) :
    while (have_posts()) : the_post();
        ?>
        <main id="content" <?php post_class('fnd-page omj-landing omj-landing--werkzoekende'); ?>>
            <?php include get_stylesheet_directory() . '/template-parts/landing-werkzoekende.php'; ?>

            <?php if (trim(get_the_content()) !== '') : ?>
                <div class="fnd-page__elementor-content">
                    <?php the_content(); ?>
                </div>
            <?php endif; ?>
        </main>
        <?php
    endwhile;
endif;

get_footer();
