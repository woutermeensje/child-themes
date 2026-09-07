<?php
/*
Template Name: Landingspagina
*/

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if ( have_posts() ) :
    while ( have_posts() ) : the_post();
        include get_stylesheet_directory() . '/template-parts/landing.php';
        the_content();
    endwhile;
endif;

get_footer();
