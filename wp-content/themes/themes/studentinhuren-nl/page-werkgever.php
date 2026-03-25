<?php
/*
Template Name: Landingspagina Werkgever
*/

if (!defined('ABSPATH')) {
    exit;
}

get_header();
include get_stylesheet_directory() . '/template-parts/landing-werkgever.php';

if ( have_posts() ) :
    while ( have_posts() ) : the_post();
        the_content();
    endwhile;
endif;

get_footer();
