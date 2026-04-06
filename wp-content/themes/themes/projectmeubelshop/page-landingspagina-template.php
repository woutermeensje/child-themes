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
		$content               = get_the_content();
		$products_shortcodes   = '';

		if ( preg_match_all( '/\[products[^\]]*\]/i', $content, $matches ) ) {
			$products_shortcodes  = implode( "\n", $matches[0] );
		}

		include get_stylesheet_directory() . '/template-parts/hero-homepage.php';

		if ( '' !== $products_shortcodes ) {
			echo do_shortcode( $products_shortcodes );
		} else {
			the_content();
		}
	endwhile;
endif;

get_footer();
