<?php
/**
 * Custom product card for archive/shop/category loops.
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( 'pms-product-card', $product ); ?>>
	<a href="<?php the_permalink(); ?>" class="pms-product-card__link">
		<div class="pms-product-card__media">
			<?php
			if ( has_post_thumbnail() ) {
				echo woocommerce_get_product_thumbnail( 'woocommerce_thumbnail', array( 'class' => 'pms-product-card__image' ) );
			} else {
				echo wc_placeholder_img( 'woocommerce_thumbnail', array( 'class' => 'pms-product-card__image pms-product-card__image--placeholder' ) );
			}
			?>
		</div>
		<div class="pms-product-card__body">
			<h2 class="pms-product-card__title"><?php the_title(); ?></h2>
		</div>
	</a>
</li>
