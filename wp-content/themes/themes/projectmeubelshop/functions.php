<?php

require_once get_stylesheet_directory() . '/includes/quote.php';
require_once get_stylesheet_directory() . '/includes/information-request.php';

add_action( 'wp_enqueue_scripts', 'projectmeubelshop_child_assets' );
function projectmeubelshop_child_assets() {
	$child_style_version = file_exists( get_stylesheet_directory() . '/style.css' ) ? filemtime( get_stylesheet_directory() . '/style.css' ) : wp_get_theme()->get( 'Version' );
	$catalog_js_version  = file_exists( get_stylesheet_directory() . '/js/catalog-sidebar.js' ) ? filemtime( get_stylesheet_directory() . '/js/catalog-sidebar.js' ) : wp_get_theme()->get( 'Version' );
	$carousel_js_version = file_exists( get_stylesheet_directory() . '/js/homepage-carousel.js' ) ? filemtime( get_stylesheet_directory() . '/js/homepage-carousel.js' ) : wp_get_theme()->get( 'Version' );

	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'projectmeubelshop-child-style', get_stylesheet_uri(), array( 'parent-style' ), $child_style_version );
	wp_enqueue_script(
		'projectmeubelshop-catalog-sidebar',
		get_stylesheet_directory_uri() . '/js/catalog-sidebar.js',
		array(),
		$catalog_js_version,
		true
	);
	wp_register_script(
		'projectmeubelshop-homepage-carousel',
		get_stylesheet_directory_uri() . '/js/homepage-carousel.js',
		array(),
		$carousel_js_version,
		true
	);

	// Lucide icons
	wp_enqueue_script( 'lucide', 'https://unpkg.com/lucide@latest/dist/umd/lucide.min.js', array(), null, false );

	// Global CSS (kleuren, root vars)
	if ( file_exists( get_stylesheet_directory() . '/css/global.css' ) ) {
		wp_enqueue_style( 'pms-global', get_stylesheet_directory_uri() . '/css/global.css', array( 'projectmeubelshop-child-style' ), filemtime( get_stylesheet_directory() . '/css/global.css' ) );
	}

	// Header CSS
	if ( file_exists( get_stylesheet_directory() . '/css/header.css' ) ) {
		wp_enqueue_style( 'pms-header', get_stylesheet_directory_uri() . '/css/header.css', array( 'pms-global' ), filemtime( get_stylesheet_directory() . '/css/header.css' ) );
	}

	if ( file_exists( get_stylesheet_directory() . '/css/hero-homepage.css' ) ) {
		wp_enqueue_style( 'pms-hero-homepage', get_stylesheet_directory_uri() . '/css/hero-homepage.css', array( 'pms-global', 'pms-header' ), filemtime( get_stylesheet_directory() . '/css/hero-homepage.css' ) );
	}

	// Alleen de WooCommerce layout CSS verwijderen (float-based grid)
	// woocommerce-general blijft aan voor basisstijlen van knoppen/formulieren
	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_dequeue_style( 'woocommerce-layout' );
		wp_dequeue_style( 'woocommerce-smallscreen' );
	}
}

add_action( 'after_setup_theme', 'projectmeubelshop_child_setup' );
function projectmeubelshop_child_setup() {
	add_theme_support( 'woocommerce' );
	add_theme_support( 'custom-logo' );
	add_post_type_support( 'page', 'excerpt' );
	register_nav_menus( array(
		'primary_nav' => 'Primaire navigatie',
	) );
}

function pms_render_breadcrumbs( string $nav_class = '', string $wrapper_class = '' ): void {
	$nav_class_attr = $nav_class ? ' class="' . esc_attr( $nav_class ) . '"' : '';

	if ( $wrapper_class ) {
		echo '<div class="' . esc_attr( $wrapper_class ) . '">';
	}

	if ( function_exists( 'yoast_breadcrumb' ) ) {
		yoast_breadcrumb( '<nav' . $nav_class_attr . ' aria-label="Breadcrumb">', '</nav>' );
	} else {
		echo '<nav' . $nav_class_attr . ' aria-label="Breadcrumb">';
		echo '<a href="' . esc_url( home_url( '/' ) ) . '">Home</a>';
		echo '<span class="pms-breadcrumb-sep" aria-hidden="true"> / </span>';
		echo '<span>' . esc_html( get_the_title() ) . '</span>';
		echo '</nav>';
	}

	if ( $wrapper_class ) {
		echo '</div>';
	}
}

/**
 * Custom header: vervang Hello Elementor's standaard header
 * – hello_elementor_header_footer uitschakelen zodat de eigen site-header
 *   niet gerenderd wordt (ongeacht of het Elementor H&F experiment actief is)
 * – Onze header injecteren via wp_body_open, direct na <body>
 */
add_filter( 'hello_elementor_header_footer', '__return_false' );
add_action( 'wp_body_open', function () {
	include get_stylesheet_directory() . '/template-parts/header.php';
} );

/**
 * Nav Walker voor de primaire navigatie
 */
if ( ! class_exists( 'PMS_Nav_Walker' ) ) :
class PMS_Nav_Walker extends Walker_Nav_Menu {
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes    = empty( $item->classes ) ? array() : (array) $item->classes;
		$has_child  = in_array( 'menu-item-has-children', $classes, true );
		$is_active  = in_array( 'current-menu-item', $classes, true ) || in_array( 'current-menu-ancestor', $classes, true );
		$li_class   = 'pms-nav__item';

		if ( $has_child ) {
			$li_class .= ' pms-nav__item--has-children';
		}
		if ( $is_active ) {
			$li_class .= ' is-active';
		}

		$output .= '<li class="' . esc_attr( $li_class ) . '">';

		$url         = ! empty( $item->url ) ? $item->url : '#';
		$title       = apply_filters( 'the_title', $item->title, $item->ID );
		$attr_title  = ! empty( $item->attr_title ) ? ' title="' . esc_attr( $item->attr_title ) . '"' : '';
		$target      = ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
		$rel         = ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : '';

		$output .= '<a class="pms-nav__link' . ( $is_active ? ' is-active' : '' ) . '" href="' . esc_url( $url ) . '"' . $attr_title . $target . $rel . '>';
		$output .= esc_html( $title );

		if ( $has_child ) {
			$output .= '<span class="pms-nav__chev" aria-hidden="true"></span>';
		}

		$output .= '</a>';
	}

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<ul class="pms-nav__dropdown">';
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul>';
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}
endif;

// Breadcrumbs bovenaan pagina's met [products] shortcode
add_filter( 'the_content', 'pms_prepend_breadcrumbs_on_products_page' );
function pms_prepend_breadcrumbs_on_products_page( $content ) {
	if ( is_admin() || ! is_page() || ! is_main_query() || ! in_the_loop() || ! function_exists( 'woocommerce_breadcrumb' ) ) {
		return $content;
	}
	if ( ! has_shortcode( $content, 'products' ) && ! has_shortcode( $content, 'product_category' ) ) {
		return $content;
	}

	remove_filter( 'the_content', 'pms_prepend_breadcrumbs_on_products_page' );
	ob_start();
	woocommerce_breadcrumb();
	$breadcrumbs = ob_get_clean();
	add_filter( 'the_content', 'pms_prepend_breadcrumbs_on_products_page' );
	return $breadcrumbs . $content;
}

add_filter( 'woocommerce_product_tabs', 'projectmeubelshop_remove_reviews_tab', 98 );
function projectmeubelshop_remove_reviews_tab( $tabs ) {
	if ( isset( $tabs['reviews'] ) ) {
		unset( $tabs['reviews'] );
	}
	return $tabs;
}

add_shortcode( 'pms_homepage_carousel', 'pms_render_homepage_carousel_shortcode' );
function pms_render_homepage_carousel_shortcode( $atts ) {
	if ( ! function_exists( 'pms_quote_get_room_builder_assets' ) ) {
		return '';
	}

	$atts = shortcode_atts(
		array(
			'limit'    => 12,
			'title'    => 'Meestverkochte kantoormeubels',
		),
		$atts,
		'pms_homepage_carousel'
	);

	$assets = pms_quote_get_room_builder_assets();
	$assets = array_slice( $assets, 0, max( 1, (int) $atts['limit'] ) );

	if ( empty( $assets ) ) {
		return '';
	}

	ob_start();
	?>
	<section class="pms-home-carousel" aria-label="Inrichting inspiratie">
		<?php if ( ! empty( $atts['title'] ) ) : ?>
			<h2 class="pms-home-carousel__title pms-home-carousel__heading"><?php echo esc_html( $atts['title'] ); ?></h2>
		<?php endif; ?>

		<div class="pms-home-carousel__viewport">
			<?php foreach ( $assets as $asset ) : ?>
				<?php
				$item_link = '';
				if ( ! empty( $asset['term_id'] ) ) {
					$term_link = get_term_link( (int) $asset['term_id'], 'product_cat' );
					if ( ! is_wp_error( $term_link ) ) {
						$item_link = (string) $term_link;
					}
				}
				?>
				<article class="pms-home-carousel__item">
					<?php if ( $item_link ) : ?>
						<a class="pms-home-carousel__card" href="<?php echo esc_url( $item_link ); ?>">
					<?php else : ?>
						<div class="pms-home-carousel__card">
					<?php endif; ?>
							<div class="pms-home-carousel__media">
								<img src="<?php echo esc_url( $asset['image_url'] ); ?>" alt="<?php echo esc_attr( $asset['label'] ); ?>" class="pms-home-carousel__image">
							</div>
							<div class="pms-home-carousel__body">
								<h3 class="pms-home-carousel__name"><?php echo esc_html( $asset['label'] ); ?></h3>
							</div>
					<?php if ( $item_link ) : ?>
						</a>
					<?php else : ?>
						</div>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	</section>
	<?php

	return ob_get_clean();
}
