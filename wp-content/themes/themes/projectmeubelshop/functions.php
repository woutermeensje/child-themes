<?php

// Quote tool – aparte file
require_once get_stylesheet_directory() . '/includes/quote.php';

add_action( 'wp_enqueue_scripts', 'projectmeubelshop_child_assets' );
function projectmeubelshop_child_assets() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'projectmeubelshop-child-style', get_stylesheet_uri(), array( 'parent-style' ), wp_get_theme()->get( 'Version' ) );
	wp_enqueue_script(
		'projectmeubelshop-catalog-sidebar',
		get_stylesheet_directory_uri() . '/js/catalog-sidebar.js',
		array(),
		wp_get_theme()->get( 'Version' ),
		true
	);

	// Lucide icons
	wp_enqueue_script( 'lucide', 'https://unpkg.com/lucide@latest/dist/umd/lucide.min.js', array(), null, false );

	// Alleen de WooCommerce layout CSS verwijderen (float-based grid)
	// woocommerce-general blijft aan voor basisstijlen van knoppen/formulieren
	if ( is_product() ) {
		wp_dequeue_style( 'woocommerce-layout' );
		wp_dequeue_style( 'woocommerce-smallscreen' );
	}
}

add_action( 'after_setup_theme', 'projectmeubelshop_child_setup' );
function projectmeubelshop_child_setup() {
	add_theme_support( 'woocommerce' );
}

add_filter( 'woocommerce_product_tabs', 'projectmeubelshop_remove_reviews_tab', 98 );
function projectmeubelshop_remove_reviews_tab( $tabs ) {
	if ( isset( $tabs['reviews'] ) ) {
		unset( $tabs['reviews'] );
	}
	return $tabs;
}
