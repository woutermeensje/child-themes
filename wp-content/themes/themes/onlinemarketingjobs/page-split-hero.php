<?php
/*
Template Name: Onlinemarketingjobs Split Hero
*/
if (!defined('ABSPATH')) exit;

get_header();

if (have_posts()) :
    while (have_posts()) : the_post();
        ?>
        <main id="content" <?php post_class('omj-split-page'); ?>>
            <?php include get_stylesheet_directory() . '/template-parts/split-hero.php'; ?>

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
