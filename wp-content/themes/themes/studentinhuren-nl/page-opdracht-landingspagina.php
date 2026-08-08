<?php
/*
Template Name: opdracht-landingspagina
*/

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) :
    while (have_posts()) : the_post();
        $si_landing_primary_label = 'Opdracht plaatsen';
        $si_landing_primary_url   = home_url('/opdracht-plaatsen/');
        $si_landing_secondary_label = '';
        $si_landing_secondary_url   = '';
        $si_landing_show_direct_link = false;
        $si_landing_page_class = 'fnd-page--opdracht';

        include get_stylesheet_directory() . '/template-parts/landing.php';
        the_content();
    endwhile;
endif;

get_footer();
