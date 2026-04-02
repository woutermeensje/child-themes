<?php

require_once get_stylesheet_directory() . '/includes/quote.php';

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

add_action( 'product_cat_add_form_fields', 'pms_product_cat_extra_fields_add' );
function pms_product_cat_extra_fields_add() {
	?>
	<div class="form-field term-group">
		<label for="pms_product_cat_hero_image_id">Hero afbeelding</label>
		<input type="hidden" id="pms_product_cat_hero_image_id" name="pms_product_cat_hero_image_id" value="">
		<div class="pms-term-image-preview" style="margin: 10px 0;"></div>
		<button type="button" class="button pms-term-image-upload">Afbeelding kiezen</button>
		<button type="button" class="button pms-term-image-remove" style="display:none;">Afbeelding verwijderen</button>
		<p class="description">Deze afbeelding wordt gebruikt in de hero van de productcategorie-pagina.</p>
	</div>
	<div class="form-field term-group">
		<label for="pms_product_cat_hero_excerpt">Hero introtekst</label>
		<textarea id="pms_product_cat_hero_excerpt" name="pms_product_cat_hero_excerpt" rows="4" style="width:100%;"></textarea>
		<p class="description">Korte tekst onder de titel in de hero. Laat leeg om terug te vallen op de beschrijving.</p>
	</div>
	<?php
}

add_action( 'product_cat_edit_form_fields', 'pms_product_cat_extra_fields_edit' );
function pms_product_cat_extra_fields_edit( $term ) {
	$image_id    = (int) get_term_meta( $term->term_id, 'pms_hero_image_id', true );
	$hero_text   = get_term_meta( $term->term_id, 'pms_hero_excerpt', true );
	$image_html  = $image_id ? wp_get_attachment_image( $image_id, 'medium', false, array( 'style' => 'max-width:220px;height:auto;display:block;' ) ) : '';
	?>
	<tr class="form-field term-group-wrap">
		<th scope="row"><label for="pms_product_cat_hero_image_id">Hero afbeelding</label></th>
		<td>
			<input type="hidden" id="pms_product_cat_hero_image_id" name="pms_product_cat_hero_image_id" value="<?php echo esc_attr( $image_id ); ?>">
			<div class="pms-term-image-preview" style="margin: 0 0 10px;"><?php echo $image_html; ?></div>
			<button type="button" class="button pms-term-image-upload">Afbeelding kiezen</button>
			<button type="button" class="button pms-term-image-remove" <?php echo $image_id ? '' : 'style="display:none;"'; ?>>Afbeelding verwijderen</button>
			<p class="description">Deze afbeelding wordt gebruikt in de hero van de productcategorie-pagina.</p>
		</td>
	</tr>
	<tr class="form-field term-group-wrap">
		<th scope="row"><label for="pms_product_cat_hero_excerpt">Hero introtekst</label></th>
		<td>
			<textarea id="pms_product_cat_hero_excerpt" name="pms_product_cat_hero_excerpt" rows="4" style="width:100%;"><?php echo esc_textarea( $hero_text ); ?></textarea>
			<p class="description">Korte tekst onder de titel in de hero. Laat leeg om terug te vallen op de beschrijving.</p>
		</td>
	</tr>
	<?php
}

add_action( 'created_product_cat', 'pms_save_product_cat_extra_fields' );
add_action( 'edited_product_cat', 'pms_save_product_cat_extra_fields' );
function pms_save_product_cat_extra_fields( $term_id ) {
	if ( isset( $_POST['pms_product_cat_hero_image_id'] ) ) {
		update_term_meta( $term_id, 'pms_hero_image_id', absint( $_POST['pms_product_cat_hero_image_id'] ) );
	}

	if ( isset( $_POST['pms_product_cat_hero_excerpt'] ) ) {
		update_term_meta( $term_id, 'pms_hero_excerpt', sanitize_textarea_field( wp_unslash( $_POST['pms_product_cat_hero_excerpt'] ) ) );
	}
}

add_action( 'admin_enqueue_scripts', 'pms_product_cat_admin_media' );
function pms_product_cat_admin_media( $hook ) {
	if ( 'edit-tags.php' !== $hook && 'term.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'edit-product_cat' !== $screen->id ) {
		return;
	}

	wp_enqueue_media();

	$script = <<<'JS'
document.addEventListener('DOMContentLoaded', function () {
  var uploadButtons = document.querySelectorAll('.pms-term-image-upload');

  uploadButtons.forEach(function (button) {
    button.addEventListener('click', function (event) {
      event.preventDefault();

      var wrapper = button.closest('.form-field, .term-group-wrap, td');
      if (!wrapper) return;

      var input = wrapper.querySelector('#pms_product_cat_hero_image_id');
      var preview = wrapper.querySelector('.pms-term-image-preview');
      var removeButton = wrapper.querySelector('.pms-term-image-remove');
      var frame = wp.media({
        title: 'Kies hero afbeelding',
        button: { text: 'Gebruik afbeelding' },
        multiple: false
      });

      frame.on('select', function () {
        var attachment = frame.state().get('selection').first().toJSON();
        if (input) input.value = attachment.id;
        if (preview) preview.innerHTML = '<img src="' + attachment.url + '" style="max-width:220px;height:auto;display:block;" alt="">';
        if (removeButton) removeButton.style.display = 'inline-block';
      });

      frame.open();
    });
  });

  document.querySelectorAll('.pms-term-image-remove').forEach(function (button) {
    button.addEventListener('click', function (event) {
      event.preventDefault();
      var wrapper = button.closest('.form-field, .term-group-wrap, td');
      if (!wrapper) return;

      var input = wrapper.querySelector('#pms_product_cat_hero_image_id');
      var preview = wrapper.querySelector('.pms-term-image-preview');
      if (input) input.value = '';
      if (preview) preview.innerHTML = '';
      button.style.display = 'none';
    });
  });
});
JS;

	wp_add_inline_script( 'jquery-core', $script );
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
	if ( ! class_exists( 'WooCommerce' ) ) {
		return '';
	}

	$atts = shortcode_atts(
		array(
			'category' => 'carousel-homepage',
			'limit'    => 12,
			'title'    => 'Het beste ergonische kantoormeubilair',
		),
		$atts,
		'pms_homepage_carousel'
	);

	$query = new WP_Query(
		array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'posts_per_page'      => max( 1, (int) $atts['limit'] ),
			'ignore_sticky_posts' => true,
			'tax_query'           => array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => sanitize_title( $atts['category'] ),
				),
			),
		)
	);

	if ( ! $query->have_posts() ) {
		wp_reset_postdata();
		return '';
	}

	wp_enqueue_script( 'projectmeubelshop-homepage-carousel' );

	$carousel_id = wp_unique_id( 'pms-home-carousel-' );

	ob_start();
	?>
	<section class="pms-home-carousel" aria-label="Populaire producten">
		<?php if ( ! empty( $atts['title'] ) ) : ?>
			<h2 class="pms-home-carousel__title"><?php echo esc_html( $atts['title'] ); ?></h2>
		<?php endif; ?>

		<div class="pms-home-carousel__stage">
			<div class="pms-home-carousel__controls pms-home-carousel__controls--prev" aria-label="Carousel navigatie">
				<button type="button" class="pms-home-carousel__arrow" data-carousel-prev="<?php echo esc_attr( $carousel_id ); ?>" aria-label="Vorige producten">
					<span aria-hidden="true">&larr;</span>
				</button>
			</div>

			<div class="pms-home-carousel__viewport" id="<?php echo esc_attr( $carousel_id ); ?>" data-carousel-track tabindex="0">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					global $product;

					if ( ! $product instanceof WC_Product || ! $product->is_visible() ) {
						continue;
					}
					?>
					<article <?php wc_product_class( 'pms-home-carousel__item', $product ); ?>>
						<a href="<?php the_permalink(); ?>" class="pms-home-carousel__card">
							<div class="pms-home-carousel__media">
								<?php
								if ( has_post_thumbnail() ) {
									echo woocommerce_get_product_thumbnail( 'woocommerce_thumbnail', array( 'class' => 'pms-home-carousel__image' ) );
								} else {
									echo wc_placeholder_img( 'woocommerce_thumbnail', array( 'class' => 'pms-home-carousel__image pms-home-carousel__image--placeholder' ) );
								}
								?>
							</div>
							<div class="pms-home-carousel__body">
								<h3 class="pms-home-carousel__name"><?php the_title(); ?></h3>
							</div>
						</a>
					</article>
				<?php endwhile; ?>
			</div>

			<div class="pms-home-carousel__controls pms-home-carousel__controls--next" aria-label="Carousel navigatie">
				<button type="button" class="pms-home-carousel__arrow" data-carousel-next="<?php echo esc_attr( $carousel_id ); ?>" aria-label="Volgende producten">
					<span aria-hidden="true">&rarr;</span>
				</button>
			</div>
		</div>
	</section>
	<?php

	wp_reset_postdata();

	return ob_get_clean();
}
