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
<li <?php wc_product_class( 'mh-product-card', $product ); ?> style="padding:0; overflow:hidden; border-radius:5px; border:1px solid #DEDEDE;">
		<a href="<?php the_permalink(); ?>" class="mh-product-card__link">
			<div class="mh-product-card__media" style="display:flex; align-items:flex-start; justify-content:center; width:100%; aspect-ratio:4 / 3; padding:0; margin:0; overflow:hidden; line-height:0; border:0; border-radius:5px 5px 0 0; background:linear-gradient(180deg, var(--color-surface-alt, #F1F8EE) 0%, var(--color-bg, #F7FBF7) 100%);">
			<?php
			if ( has_post_thumbnail() ) {
				echo wp_get_attachment_image(
					$product->get_image_id(),
					'large',
					false,
					array(
						'class'   => 'mh-product-card__image',
						'loading' => 'lazy',
							'style'   => 'display:block; width:100%; height:100%; max-width:100%; max-height:100%; margin:0; border:0; border-radius:0; box-shadow:none; background:linear-gradient(180deg, var(--color-surface-alt, #F1F8EE) 0%, var(--color-bg, #F7FBF7) 100%); object-fit:contain; object-position:center top; vertical-align:top;',
					)
				);
			} else {
				echo wc_placeholder_img(
					'large',
					array(
						'class' => 'mh-product-card__image mh-product-card__image--placeholder',
							'style' => 'display:block; width:100%; height:100%; max-width:100%; max-height:100%; margin:0; border:0; border-radius:0; box-shadow:none; background:linear-gradient(180deg, var(--color-surface-alt, #F1F8EE) 0%, var(--color-bg, #F7FBF7) 100%); object-fit:contain; object-position:center top; vertical-align:top;',
					)
				);
			}
			?>
		</div>
		<div class="mh-product-card__body" style="padding:18px 22px 22px;">
			<h2 class="mh-product-card__title mh-product-card__title--shortcode"><?php the_title(); ?></h2>
		</div>
	</a>
</li>
