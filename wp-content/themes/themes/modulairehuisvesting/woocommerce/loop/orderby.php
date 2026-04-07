<?php
/**
 * Custom ordering markup for Projectmeubelshop archives.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$id_suffix = wp_unique_id();
?>
<form class="mh-tax-toolbar__ordering" method="get">
	<label class="mh-tax-toolbar__label" for="mh-orderby-<?php echo esc_attr( $id_suffix ); ?>">
		<?php esc_html_e( 'Sorteren op', 'woocommerce' ); ?>
	</label>
	<select
		name="orderby"
		id="mh-orderby-<?php echo esc_attr( $id_suffix ); ?>"
		class="mh-tax-toolbar__select"
		aria-label="<?php esc_attr_e( 'Shop order', 'woocommerce' ); ?>"
	>
		<?php foreach ( $catalog_orderby_options as $id => $name ) : ?>
			<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $orderby, $id ); ?>><?php echo esc_html( $name ); ?></option>
		<?php endforeach; ?>
	</select>
	<input type="hidden" name="paged" value="1" />
	<?php wc_query_string_form_fields( null, array( 'orderby', 'submit', 'paged', 'product-page' ) ); ?>
</form>
