<?php
/*
Template Name: Landingspagina-template
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		include get_stylesheet_directory() . '/template-parts/hero-homepage.php';
		the_content();
	endwhile;
endif;

get_footer();
