<?php
/**
 * Single Product title
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/title.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
$product_id = $product instanceof WC_Product ? $product->get_id() : get_the_ID();
$quote_url  = function_exists( 'pms_quote_get_page_url' ) ? pms_quote_get_page_url() : home_url( '/offerte-samenstellen/' );
$in_quote   = function_exists( 'pms_quote_has_product' ) ? pms_quote_has_product( $product_id ) : false;

the_title( '<h1 class="product_title entry-title">', '</h1>' );
?>
<div class="pms-title-quote-cta">
	<?php if ( $in_quote ) : ?>
		<a class="button pms-quote-view-button pms-single-quote-button" href="<?php echo esc_url( $quote_url ); ?>">
			<?php esc_html_e( 'Offerte bekijken', 'projectmeubelshop-child' ); ?>
		</a>
	<?php else : ?>
		<form method="post" class="pms-title-quote-form">
			<?php wp_nonce_field( 'pms_add_to_quote', 'pms_quote_nonce' ); ?>
			<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product_id ); ?>">
			<input type="hidden" name="quantity" value="1">
			<button type="submit" name="pms_add_to_quote" value="1" class="button pms-quote-add-button pms-single-quote-button">
				<?php esc_html_e( 'Voeg toe aan offerte', 'projectmeubelshop-child' ); ?>
			</button>
		</form>
	<?php endif; ?>
</div>
