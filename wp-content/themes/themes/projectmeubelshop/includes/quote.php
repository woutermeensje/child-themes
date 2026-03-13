<?php
/**
 * Projectmeubelshop – Quote tool
 * Functionaliteit, shortcode en styling voor /offerte-samenstellen/
 */

defined( 'ABSPATH' ) || exit;

// ─── Helpers ────────────────────────────────────────────────────────────────

function pms_quote_ready() {
	return class_exists( 'WooCommerce' ) && function_exists( 'WC' ) && is_object( WC() );
}

function pms_quote_get_items() {
	if ( ! pms_quote_ready() ) return array();
	$wc = WC();
	if ( empty( $wc->session ) ) return array();
	$items = $wc->session->get( 'pms_quote_items', array() );
	return is_array( $items ) ? $items : array();
}

function pms_quote_has_product( $product_id ) {
	$product_id = absint( $product_id );
	if ( ! $product_id ) return false;
	foreach ( pms_quote_get_items() as $item ) {
		$base_id = isset( $item['product_id'] )   ? absint( $item['product_id'] )   : 0;
		$var_id  = isset( $item['variation_id'] ) ? absint( $item['variation_id'] ) : 0;
		if ( $base_id === $product_id || $var_id === $product_id ) return true;
	}
	return false;
}

function pms_quote_set_items( $items ) {
	if ( ! pms_quote_ready() ) return;
	$wc = WC();
	if ( empty( $wc->session ) ) return;
	$wc->session->set( 'pms_quote_items', $items );
}

function pms_quote_item_key( $product_id, $variation_id, $variation_data ) {
	ksort( $variation_data );
	return md5( $product_id . '|' . $variation_id . '|' . wp_json_encode( $variation_data ) );
}

function pms_quote_add_item( $product_id, $quantity = 1, $variation_id = 0, $variation_data = array() ) {
	$product = wc_get_product( $variation_id ? $variation_id : $product_id );
	if ( ! $product ) return false;
	$qty   = max( 1, absint( $quantity ) );
	$key   = pms_quote_item_key( (int) $product_id, (int) $variation_id, (array) $variation_data );
	$items = pms_quote_get_items();
	if ( isset( $items[ $key ] ) ) {
		$items[ $key ]['quantity'] += $qty;
	} else {
		$items[ $key ] = array(
			'product_id'     => (int) $product_id,
			'variation_id'   => (int) $variation_id,
			'variation_data' => (array) $variation_data,
			'quantity'       => $qty,
		);
	}
	pms_quote_set_items( $items );
	return true;
}

function pms_quote_collect_variation_data() {
	$variation_data = array();
	foreach ( $_POST as $key => $value ) {
		if ( 0 === strpos( (string) $key, 'attribute_' ) ) {
			$variation_data[ sanitize_key( $key ) ] = sanitize_text_field( wp_unslash( $value ) );
		}
	}
	return $variation_data;
}

function pms_quote_get_page_url() {
	return home_url( '/offerte-samenstellen/' );
}

// ─── Action handlers ─────────────────────────────────────────────────────────

add_action( 'template_redirect', 'pms_quote_handle_add', 1 );
function pms_quote_handle_add() {
	if ( ! pms_quote_ready() || empty( $_POST['pms_add_to_quote'] ) ) return;
	if ( function_exists( 'wc_clear_notices' ) ) wc_clear_notices();
	$nonce = isset( $_POST['pms_quote_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['pms_quote_nonce'] ) ) : '';
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'pms_add_to_quote' ) ) return;
	$product_id   = isset( $_POST['pms_product_id'] ) ? absint( $_POST['pms_product_id'] ) : 0;
	if ( ! $product_id && isset( $_POST['add-to-cart'] ) ) $product_id = absint( $_POST['add-to-cart'] );
	$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
	$quantity     = isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : 1;
	$variation    = pms_quote_collect_variation_data();
	if ( $variation_id > 0 ) {
		$parent = wp_get_post_parent_id( $variation_id );
		if ( $parent ) $product_id = (int) $parent;
	}
	if ( $product_id && pms_quote_add_item( $product_id, $quantity, $variation_id, $variation ) ) {
		wc_add_notice( __( 'Product toegevoegd aan offerteaanvraag.', 'projectmeubelshop-child' ), 'success' );
	} else {
		wc_add_notice( __( 'Kon product niet toevoegen aan offerteaanvraag.', 'projectmeubelshop-child' ), 'error' );
	}
	$redirect_url = wp_get_referer();
	if ( ! $redirect_url && $product_id ) $redirect_url = get_permalink( $product_id );
	if ( ! $redirect_url ) $redirect_url = home_url( '/' );
	wp_safe_redirect( $redirect_url );
	exit;
}

add_action( 'template_redirect', 'pms_quote_handle_update_or_remove', 2 );
function pms_quote_handle_update_or_remove() {
	if ( ! pms_quote_ready() || ( empty( $_POST['pms_quote_update'] ) && empty( $_POST['pms_quote_remove'] ) ) ) return;
	$nonce = isset( $_POST['pms_quote_page_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['pms_quote_page_nonce'] ) ) : '';
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'pms_quote_page_action' ) ) return;
	$items = pms_quote_get_items();
	if ( ! empty( $_POST['pms_quote_remove'] ) ) {
		$remove_key = sanitize_text_field( wp_unslash( $_POST['pms_quote_remove'] ) );
		unset( $items[ $remove_key ] );
		pms_quote_set_items( $items );
	}
	if ( ! empty( $_POST['pms_quote_qty'] ) && is_array( $_POST['pms_quote_qty'] ) ) {
		foreach ( $_POST['pms_quote_qty'] as $key => $qty ) {
			$item_key = sanitize_text_field( wp_unslash( $key ) );
			if ( ! isset( $items[ $item_key ] ) ) continue;
			$new_qty = max( 0, absint( $qty ) );
			if ( 0 === $new_qty ) {
				unset( $items[ $item_key ] );
			} else {
				$items[ $item_key ]['quantity'] = $new_qty;
			}
		}
		pms_quote_set_items( $items );
	}
	if ( empty( $items ) ) {
		wp_safe_redirect( home_url( '/producten/' ) );
	} else {
		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : home_url( '/' ) );
	}
	exit;
}

add_action( 'template_redirect', 'pms_quote_handle_submit', 3 );
function pms_quote_handle_submit() {
	if ( ! pms_quote_ready() || empty( $_POST['pms_quote_submit'] ) ) return;
	$nonce = isset( $_POST['pms_quote_page_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['pms_quote_page_nonce'] ) ) : '';
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'pms_quote_page_action' ) ) return;
	$items = pms_quote_get_items();
	if ( empty( $items ) ) {
		wp_safe_redirect( add_query_arg( 'pms_quote_error', 'empty', wp_get_referer() ? wp_get_referer() : home_url( '/' ) ) );
		exit;
	}
	$firstname = isset( $_POST['pms_firstname'] ) ? sanitize_text_field( wp_unslash( $_POST['pms_firstname'] ) ) : '';
	$lastname  = isset( $_POST['pms_lastname'] )  ? sanitize_text_field( wp_unslash( $_POST['pms_lastname'] ) )  : '';
	$name      = trim( $firstname . ' ' . $lastname );
	$email     = isset( $_POST['pms_email'] )     ? sanitize_email( wp_unslash( $_POST['pms_email'] ) )           : '';
	$phone     = isset( $_POST['pms_phone'] )     ? sanitize_text_field( wp_unslash( $_POST['pms_phone'] ) )      : '';
	$message   = isset( $_POST['pms_message'] )   ? sanitize_textarea_field( wp_unslash( $_POST['pms_message'] ) ) : '';
	if ( ! $firstname || ! $email || ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'pms_quote_error', 'contact', wp_get_referer() ? wp_get_referer() : home_url( '/' ) ) );
		exit;
	}
	$lines = array( 'Nieuwe offerteaanvraag', '', 'Voornaam: ' . $firstname, 'Achternaam: ' . $lastname, 'E-mail: ' . $email, 'Telefoon: ' . ( $phone ?: '-' ), '', 'Producten:' );
	foreach ( $items as $item ) {
		$product = wc_get_product( ! empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'] );
		if ( ! $product ) continue;
		$lines[] = '- ' . $product->get_name() . ' x ' . (int) $item['quantity'];
	}
	if ( $message ) { $lines[] = ''; $lines[] = 'Opmerking:'; $lines[] = $message; }
	$subject = sprintf( __( 'Offerteaanvraag van %s', 'projectmeubelshop-child' ), $name );
	$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );
	$sent    = wp_mail( get_option( 'admin_email' ), $subject, implode( "\n", $lines ), $headers );
	if ( $sent ) {
		pms_quote_set_items( array() );
		wp_safe_redirect( add_query_arg( 'pms_quote_sent', '1', wp_get_referer() ? wp_get_referer() : home_url( '/' ) ) );
		exit;
	}
	wp_safe_redirect( add_query_arg( 'pms_quote_error', 'mail', wp_get_referer() ? wp_get_referer() : home_url( '/' ) ) );
	exit;
}

// ─── Shortcode [pms_quote_page] ──────────────────────────────────────────────

add_shortcode( 'pms_quote_page', 'pms_quote_render_page' );
function pms_quote_render_page() {
	if ( ! pms_quote_ready() ) {
		return '<div class="pms-quote-shell"><p class="pms-quote-notice pms-quote-notice-error">' . esc_html__( 'WooCommerce is niet actief.', 'projectmeubelshop-child' ) . '</p></div>';
	}

	$items    = pms_quote_get_items();
	$shop_url = wc_get_page_permalink( 'shop' );

	ob_start();
	?>
	<style>
	/* ── Shell ─────────────────────────────────────────────── */
	.pms-quote-shell {
		max-width: 1200px !important;
		margin: 40px auto !important;
		border: none !important;
		box-shadow: none !important;
		background: none !important;
		padding: 0 !important;
		border-radius: 0 !important;
	}

	/* ── Paginatitel ────────────────────────────────────────── */
	.pms-quote-title {
		font-family: Inter, sans-serif !important;
		font-size: 28px !important;
		font-weight: 800 !important;
		color: #111 !important;
		margin: 0 0 28px !important;
		letter-spacing: -0.3px !important;
	}

	/* ── Kaart ──────────────────────────────────────────────── */
	.pms-quote-card {
		max-width: 600px !important;
		background: #fff !important;
		border: 1px solid #DEDEDE !important;
		border-radius: 10px !important;
		box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15) !important;
		padding: 32px !important;
		box-sizing: border-box !important;
	}

	/* ── Sectie-kopjes ─────────────────────────────────────── */
	.pms-quote-card h3 {
		font-family: Inter, sans-serif !important;
		font-size: 16px !important;
		font-weight: 700 !important;
		color: #333 !important;
		margin: 0 0 16px !important;
		text-transform: none !important;
		letter-spacing: 0 !important;
	}

	/* ── Scheidingslijn tussen secties ─────────────────────── */
	.pms-quote-divider {
		border: none !important;
		border-top: 1px solid #EBEBEB !important;
		margin: 28px 0 !important;
	}

	/* ── Tabel ──────────────────────────────────────────────── */
	.pms-quote-table {
		width: 100% !important;
		border-collapse: collapse !important;
		margin-bottom: 0 !important;
	}

	.pms-quote-table th {
		text-align: left !important;
		font-family: Inter, sans-serif !important;
		font-size: 12px !important;
		font-weight: 700 !important;
		color: #888 !important;
		text-transform: none !important;
		padding: 0 0 10px !important;
		border-bottom: 1px solid #DEDEDE !important;
	}

	.pms-quote-table th:not(:first-child),
	.pms-quote-table td:not(:first-child) {
		padding-left: 12px !important;
	}

	.pms-quote-table td {
		padding: 13px 0 !important;
		border-bottom: 1px solid #F0F0F0 !important;
		font-family: Inter, sans-serif !important;
		font-size: 14px !important;
		color: #333 !important;
		vertical-align: middle !important;
	}

	.pms-quote-table td:first-child {
		font-weight: 600 !important;
	}

	/* ── Labels ─────────────────────────────────────────────── */
	.pms-quote-card label {
		display: block !important;
		font-family: Poppins, sans-serif !important;
		font-size: 15px !important;
		font-weight: 300 !important;
		color: #333 !important;
		margin-bottom: 6px !important;
	}

	.pms-quote-card p {
		margin: 0 0 16px !important;
	}

	/* ── Inputs & textarea ──────────────────────────────────── */
	.pms-input {
		-webkit-appearance: none !important;
		appearance: none !important;
		display: block !important;
		width: 100% !important;
		border: 1px solid #DEDEDE !important;
		border-radius: 0 !important;
		padding: 9px 14px !important;
		font-family: Inter, sans-serif !important;
		font-size: 14px !important;
		color: #333 !important;
		background: #fff !important;
		box-shadow: none !important;
		outline: none !important;
		box-sizing: border-box !important;
		transition: border-color 0.15s !important;
	}

	.pms-input:focus {
		border-color: #C5B17D !important;
		box-shadow: 0 0 0 3px rgba(197,177,125,0.15) !important;
	}

	.pms-input::placeholder {
		color: #bbb !important;
	}

	textarea.pms-input {
		resize: vertical !important;
		min-height: 110px !important;
	}

	/* ── Aantal input ───────────────────────────────────────── */
	.pms-qty-input {
		-webkit-appearance: none !important;
		appearance: none !important;
		width: 68px !important;
		border: 1px solid #DEDEDE !important;
		border-radius: 0 !important;
		padding: 8px 10px !important;
		font-family: Inter, sans-serif !important;
		font-size: 14px !important;
		color: #333 !important;
		background: #fff !important;
		box-shadow: none !important;
		outline: none !important;
		text-align: center !important;
		box-sizing: border-box !important;
		transition: border-color 0.15s !important;
	}

	.pms-qty-input:focus {
		border-color: #C5B17D !important;
		box-shadow: 0 0 0 3px rgba(197,177,125,0.15) !important;
	}

	/* ── Knoppen ─────────────────────────────────────────────── */
	.pms-btn {
		display: inline-flex !important;
		align-items: center !important;
		justify-content: center !important;
		border-radius: 999px !important;
		font-family: Inter, sans-serif !important;
		font-weight: 700 !important;
		cursor: pointer !important;
		text-decoration: none !important;
		transition: background-color 0.15s, border-color 0.15s, color 0.15s !important;
		box-sizing: border-box !important;
	}

	/* Primaire knop – offerte verzenden */
	.pms-btn-primary,
	.pms-btn-primary:hover,
	.pms-btn-primary:focus,
	.pms-btn-primary:active,
	.pms-btn-primary:visited {
		background: #C5B17D !important;
		background-color: #C5B17D !important;
		background-image: none !important;
		color: #fff !important;
		border: none !important;
		padding: 13px 24px !important;
		font-size: 15px !important;
		width: 100% !important;
		text-align: center !important;
		box-shadow: none !important;
	}

	.pms-btn-primary:hover {
		background: #b3a06e !important;
		background-color: #b3a06e !important;
	}

	/* Secundaire knop – bekijk producten */
	.pms-btn-secondary,
	.pms-btn-secondary:hover,
	.pms-btn-secondary:focus,
	.pms-btn-secondary:active,
	.pms-btn-secondary:visited {
		background: #C5B17D !important;
		background-color: #C5B17D !important;
		background-image: none !important;
		color: #fff !important;
		border: none !important;
		padding: 13px 24px !important;
		font-size: 15px !important;
		box-shadow: none !important;
	}

	.pms-btn-secondary:hover {
		background: #b3a06e !important;
		background-color: #b3a06e !important;
	}

	/* Ghost knop – verwijderen / update */
	.pms-btn-ghost,
	.pms-btn-ghost:hover,
	.pms-btn-ghost:focus,
	.pms-btn-ghost:active,
	.pms-btn-ghost:visited {
		background: #fff !important;
		background-color: #fff !important;
		background-image: none !important;
		border: 1px solid #DEDEDE !important;
		color: #333 !important;
		padding: 5px 14px !important;
		font-size: 12px !important;
		box-shadow: none !important;
	}

	.pms-btn-ghost:hover {
		border-color: #C5B17D !important;
		color: #C5B17D !important;
	}

	/* ── Notices ─────────────────────────────────────────────── */
	.pms-quote-notice {
		max-width: 600px !important;
		padding: 14px 18px !important;
		border-radius: 8px !important;
		margin-bottom: 24px !important;
		font-family: Inter, sans-serif !important;
		font-size: 14px !important;
		font-weight: 600 !important;
	}

	.pms-quote-notice-success {
		background: #ebfff1 !important;
		border: 1px solid #bce9c9 !important;
		color: #1a6635 !important;
	}

	.pms-quote-notice-error {
		background: #fff2ee !important;
		border: 1px solid #f1c5bc !important;
		color: #a33 !important;
	}

	/* ── Lege staat ─────────────────────────────────────────── */
	.pms-quote-card > p:first-child {
		font-family: Inter, sans-serif !important;
		font-size: 14px !important;
		color: #555 !important;
		margin-bottom: 20px !important;
	}
	</style>

	<section class="pms-quote-shell">
		<h1 class="pms-quote-title">Offerte aanvragen</h1>

		<?php if ( isset( $_GET['pms_quote_sent'] ) && '1' === $_GET['pms_quote_sent'] ) : ?>
			<p class="pms-quote-notice pms-quote-notice-success"><?php esc_html_e( 'Bedankt, je offerteaanvraag is verzonden.', 'projectmeubelshop-child' ); ?></p>
		<?php endif; ?>
		<?php if ( isset( $_GET['pms_quote_error'] ) && in_array( $_GET['pms_quote_error'], array( 'contact', 'mail' ), true ) ) : ?>
			<p class="pms-quote-notice pms-quote-notice-error"><?php esc_html_e( 'Er ging iets mis. Controleer je gegevens en probeer opnieuw.', 'projectmeubelshop-child' ); ?></p>
		<?php endif; ?>

		<?php if ( empty( $items ) ) : ?>
			<div class="pms-quote-card">
				<p><?php esc_html_e( 'Er staan nog geen producten in je offerteaanvraag.', 'projectmeubelshop-child' ); ?></p>
				<a class="pms-btn pms-btn-secondary" href="<?php echo esc_url( home_url( '/producten/' ) ); ?>"><?php esc_html_e( 'Bekijk producten', 'projectmeubelshop-child' ); ?></a>
			</div>
		<?php else : ?>
			<form method="post" class="pms-quote-card">
				<?php wp_nonce_field( 'pms_quote_page_action', 'pms_quote_page_nonce' ); ?>

				<h3><?php esc_html_e( 'Producten', 'projectmeubelshop-child' ); ?></h3>
				<table class="pms-quote-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Product', 'projectmeubelshop-child' ); ?></th>
							<th><?php esc_html_e( 'Aantal', 'projectmeubelshop-child' ); ?></th>
							<th><?php esc_html_e( 'Actie', 'projectmeubelshop-child' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $items as $key => $item ) :
							$product = wc_get_product( ! empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'] );
							if ( ! $product ) continue;
						?>
							<tr>
								<td><?php echo esc_html( $product->get_name() ); ?></td>
								<td><input class="pms-qty-input" type="number" min="1" name="pms_quote_qty[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $item['quantity'] ); ?>"></td>
								<td><button class="pms-btn pms-btn-ghost" type="submit" name="pms_quote_remove" value="<?php echo esc_attr( $key ); ?>" formnovalidate><?php esc_html_e( 'Verwijderen', 'projectmeubelshop-child' ); ?></button></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<p style="margin-top:16px;margin-bottom:0;"><button class="pms-btn pms-btn-ghost" type="submit" name="pms_quote_update" value="1" formnovalidate><?php esc_html_e( 'Update aantallen', 'projectmeubelshop-child' ); ?></button></p>

				<hr class="pms-quote-divider">

				<h3><?php esc_html_e( 'Je gegevens', 'projectmeubelshop-child' ); ?></h3>
				<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
					<p style="margin:0;"><label for="pms_firstname"><?php esc_html_e( 'Voornaam', 'projectmeubelshop-child' ); ?></label><input class="pms-input" id="pms_firstname" type="text" name="pms_firstname" required></p>
					<p style="margin:0;"><label for="pms_lastname"><?php esc_html_e( 'Achternaam', 'projectmeubelshop-child' ); ?></label><input class="pms-input" id="pms_lastname" type="text" name="pms_lastname"></p>
				</div>
				<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
					<p style="margin:0;"><label for="pms_email"><?php esc_html_e( 'Emailadres', 'projectmeubelshop-child' ); ?></label><input class="pms-input" id="pms_email" type="email" name="pms_email" required></p>
					<p style="margin:0;"><label for="pms_phone"><?php esc_html_e( 'Telefoonnummer', 'projectmeubelshop-child' ); ?></label><input class="pms-input" id="pms_phone" type="tel" name="pms_phone"></p>
				</div>
				<p><label for="pms_message"><?php esc_html_e( 'Eventuele toelichting', 'projectmeubelshop-child' ); ?></label><textarea class="pms-input" id="pms_message" name="pms_message" rows="5"></textarea></p>
				<p><button class="pms-btn pms-btn-primary" type="submit" name="pms_quote_submit" value="1"><?php esc_html_e( 'Offerte aanvragen', 'projectmeubelshop-child' ); ?></button></p>
			</form>
		<?php endif; ?>
	</section>
	<?php

	return ob_get_clean();
}
