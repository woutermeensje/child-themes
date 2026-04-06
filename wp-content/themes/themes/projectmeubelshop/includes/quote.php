<?php
/**
 * Projectmeubelshop – Offerte tool
 * Slaat items op in de WooCommerce sessie en rendert de offertepagina via [pms_quote_page].
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'pms_register_quote_request_post_type' );
function pms_register_quote_request_post_type() {
	register_post_type(
		'pms_quote_request',
		array(
			'labels' => array(
				'name'               => 'Offerte aanvragen',
				'singular_name'      => 'Offerte aanvraag',
				'menu_name'          => 'Offerte aanvragen',
				'name_admin_bar'     => 'Offerte aanvraag',
				'add_new'            => 'Nieuwe aanvraag',
				'add_new_item'       => 'Nieuwe offerte aanvraag',
				'new_item'           => 'Nieuwe offerte aanvraag',
				'edit_item'          => 'Offerte aanvraag bekijken',
				'view_item'          => 'Offerte aanvraag bekijken',
				'all_items'          => 'Alle offerte aanvragen',
				'search_items'       => 'Zoek offerte aanvragen',
				'not_found'          => 'Geen offerte aanvragen gevonden',
				'not_found_in_trash' => 'Geen offerte aanvragen in prullenbak',
			),
			'public'             => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-email-alt',
			'supports'           => array( 'title' ),
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'has_archive'        => false,
			'rewrite'            => false,
			'query_var'          => false,
		)
	);
}

function pms_quote_build_items_snapshot( array $items ): array {
	$snapshot = array();

	foreach ( $items as $pid => $item ) {
		$product = wc_get_product( $pid );
		if ( ! $product ) {
			continue;
		}

		$snapshot[] = array(
			'product_id' => (int) $pid,
			'name'       => $product->get_name(),
			'quantity'   => isset( $item['quantity'] ) ? max( 1, (int) $item['quantity'] ) : 1,
			'permalink'  => get_permalink( $pid ),
			'sku'        => $product->get_sku(),
		);
	}

	return $snapshot;
}

function pms_quote_build_category_snapshot( array $selected_categories ): array {
	$snapshot = array();

	foreach ( $selected_categories as $term_id => $quantity ) {
		$term_id  = (int) $term_id;
		$quantity = max( 1, (int) $quantity );
		$term     = get_term( $term_id, 'product_cat' );

		if ( ! $term || is_wp_error( $term ) ) {
			continue;
		}

		$snapshot[] = array(
			'product_id' => 0,
			'name'       => $term->name,
			'quantity'   => $quantity,
			'permalink'  => get_term_link( $term ),
			'sku'        => 'Categorie',
		);
	}

	return $snapshot;
}

function pms_quote_build_room_builder_snapshot( array $selected_items ): array {
	$snapshot = array();

	foreach ( $selected_items as $item ) {
		$quantity = isset( $item['quantity'] ) ? max( 1, (int) $item['quantity'] ) : 0;
		if ( $quantity < 1 ) {
			continue;
		}

		$term_id = isset( $item['term_id'] ) ? (int) $item['term_id'] : 0;
		$label   = isset( $item['label'] ) ? sanitize_text_field( (string) $item['label'] ) : '';

		if ( $term_id > 0 ) {
			$term = get_term( $term_id, 'product_cat' );
			if ( $term && ! is_wp_error( $term ) ) {
				$snapshot[] = array(
					'product_id' => 0,
					'name'       => $term->name,
					'quantity'   => $quantity,
					'permalink'  => get_term_link( $term ),
					'sku'        => 'Categorie',
				);
				continue;
			}
		}

		if ( '' === $label ) {
			continue;
		}

		$snapshot[] = array(
			'product_id' => 0,
			'name'       => $label,
			'quantity'   => $quantity,
			'permalink'  => '',
			'sku'        => 'Categorie',
		);
	}

	return $snapshot;
}

function pms_quote_normalize_asset_key( string $value ): string {
	$value = remove_accents( wp_strip_all_tags( $value ) );
	$value = strtolower( $value );
	$value = preg_replace( '/[^a-z0-9]+/', '', $value );

	return is_string( $value ) ? $value : '';
}

function pms_quote_candidate_asset_keys( WP_Term $term ): array {
	$keys = array(
		pms_quote_normalize_asset_key( $term->slug ),
		pms_quote_normalize_asset_key( $term->name ),
	);

	foreach ( array_values( $keys ) as $key ) {
		if ( str_ends_with( $key, 'en' ) ) {
			$keys[] = substr( $key, 0, -2 );
		}
		if ( str_ends_with( $key, 's' ) ) {
			$keys[] = substr( $key, 0, -1 );
		}
	}

	return array_values( array_filter( array_unique( $keys ) ) );
}

function pms_quote_find_matching_term_for_asset( string $asset_name ): ?WP_Term {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return null;
	}

	$asset_key = pms_quote_normalize_asset_key( $asset_name );

	foreach ( $terms as $term ) {
		foreach ( pms_quote_candidate_asset_keys( $term ) as $key ) {
			if ( $key === $asset_key ) {
				return $term;
			}
		}
	}

	return null;
}

function pms_quote_get_room_builder_assets(): array {
	$asset_dir = trailingslashit( get_stylesheet_directory() ) . 'offerte-samenstellen/';
	$asset_url = trailingslashit( get_stylesheet_directory_uri() ) . 'offerte-samenstellen/';
	$patterns  = array( '*.png', '*.jpg', '*.jpeg', '*.webp' );
	$files     = array();

	foreach ( $patterns as $pattern ) {
		$matched = glob( $asset_dir . $pattern );
		if ( is_array( $matched ) ) {
			$files = array_merge( $files, $matched );
		}
	}

	natcasesort( $files );

	$assets = array();

	foreach ( $files as $file ) {
		$basename = pathinfo( $file, PATHINFO_FILENAME );
		$term     = pms_quote_find_matching_term_for_asset( $basename );

		$assets[] = array(
			'key'         => pms_quote_normalize_asset_key( $basename ),
			'label'       => trim( preg_replace( '/\s+/', ' ', str_replace( array( '-', '_' ), ' ', $basename ) ) ),
			'image_url'   => $asset_url . basename( $file ),
			'term_id'     => $term instanceof WP_Term ? (int) $term->term_id : 0,
			'description' => $term instanceof WP_Term ? trim( wp_strip_all_tags( term_description( $term->term_id, 'product_cat' ) ) ) : '',
		);
	}

	return $assets;
}

function pms_quote_get_room_builder_image_url( WP_Term $term ): string {
	$asset_dir = trailingslashit( get_stylesheet_directory() ) . 'offerte-samenstellen/';
	$asset_url = trailingslashit( get_stylesheet_directory_uri() ) . 'offerte-samenstellen/';
	$patterns  = array( '*.png', '*.jpg', '*.jpeg', '*.webp' );
	$files     = array();

	foreach ( $patterns as $pattern ) {
		$matched = glob( $asset_dir . $pattern );
		if ( is_array( $matched ) ) {
			$files = array_merge( $files, $matched );
		}
	}

	if ( ! empty( $files ) ) {
		$lookup = array();
		foreach ( $files as $file ) {
			$base_name           = pathinfo( $file, PATHINFO_FILENAME );
			$lookup[ pms_quote_normalize_asset_key( $base_name ) ] = basename( $file );
		}

		foreach ( pms_quote_candidate_asset_keys( $term ) as $key ) {
			if ( isset( $lookup[ $key ] ) ) {
				return $asset_url . $lookup[ $key ];
			}
		}
	}

	$term_image_id = (int) get_term_meta( $term->term_id, 'pms_hero_image_id', true );
	$term_image_id = $term_image_id ?: (int) get_term_meta( $term->term_id, 'thumbnail_id', true );

	return $term_image_id ? (string) wp_get_attachment_image_url( $term_image_id, 'large' ) : '';
}

function pms_quote_store_request( array $request_data ): int {
	$created_at = current_time( 'mysql' );
	$name       = trim( (string) ( $request_data['name'] ?? '' ) );
	$email      = (string) ( $request_data['email'] ?? '' );
	$phone      = (string) ( $request_data['phone'] ?? '' );
	$message    = (string) ( $request_data['message'] ?? '' );
	$items      = isset( $request_data['items'] ) && is_array( $request_data['items'] ) ? $request_data['items'] : array();
	$title_name = $name ?: 'Onbekende aanvrager';

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'pms_quote_request',
			'post_status' => 'publish',
			'post_title'  => sprintf( '%s - %s', $title_name, wp_date( 'd-m-Y H:i', strtotime( $created_at ) ) ),
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		return 0;
	}

	update_post_meta( $post_id, '_pms_quote_name', $name );
	update_post_meta( $post_id, '_pms_quote_firstname', (string) ( $request_data['firstname'] ?? '' ) );
	update_post_meta( $post_id, '_pms_quote_lastname', (string) ( $request_data['lastname'] ?? '' ) );
	update_post_meta( $post_id, '_pms_quote_email', $email );
	update_post_meta( $post_id, '_pms_quote_phone', $phone );
	update_post_meta( $post_id, '_pms_quote_message', $message );
	update_post_meta( $post_id, '_pms_quote_items', $items );
	update_post_meta( $post_id, '_pms_quote_created_at', $created_at );
	update_post_meta( $post_id, '_pms_quote_source', sanitize_key( (string) ( $request_data['source'] ?? 'quote' ) ) );

	return (int) $post_id;
}

function pms_quote_build_email_html( array $request_data ): string {
	$name    = trim( (string) ( $request_data['name'] ?? '' ) );
	$email   = (string) ( $request_data['email'] ?? '' );
	$phone   = (string) ( $request_data['phone'] ?? '' );
	$message = (string) ( $request_data['message'] ?? '' );
	$items   = isset( $request_data['items'] ) && is_array( $request_data['items'] ) ? $request_data['items'] : array();

	$item_rows = '';
	foreach ( $items as $item ) {
		$item_name = esc_html( $item['name'] ?? '' );
		$item_qty  = esc_html( (string) ( $item['quantity'] ?? 1 ) );
		$item_link = ! empty( $item['permalink'] ) ? esc_url( $item['permalink'] ) : '';

		$item_rows .= '<tr>';
		$item_rows .= '<td style="padding:14px 16px;border-bottom:1px solid #e7e2d7;font-size:14px;color:#1f2a24;font-weight:600;">' . $item_name . '</td>';
		$item_rows .= '<td style="padding:14px 16px;border-bottom:1px solid #e7e2d7;font-size:14px;color:#1f2a24;text-align:center;">' . $item_qty . '</td>';
		$item_rows .= '<td style="padding:14px 16px;border-bottom:1px solid #e7e2d7;font-size:14px;text-align:right;">';
		if ( $item_link ) {
			$item_rows .= '<a href="' . $item_link . '" style="color:#4A3728;text-decoration:none;font-weight:600;">Bekijk product</a>';
		} else {
			$item_rows .= '<span style="color:#68736d;">-</span>';
		}
		$item_rows .= '</td>';
		$item_rows .= '</tr>';
	}

	if ( '' === $item_rows ) {
		$item_rows = '<tr><td colspan="3" style="padding:14px 16px;font-size:14px;color:#68736d;">Geen producten gevonden in deze aanvraag.</td></tr>';
	}

	$message_html = $message
		? nl2br( esc_html( $message ) )
		: '<span style="color:#68736d;">Geen extra opmerkingen opgegeven.</span>';

	return '
		<!doctype html>
		<html lang="nl">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Nieuwe offerte aanvraag</title>
		</head>
		<body style="margin:0;padding:0;background:#f6f3ed;font-family:Inter,Arial,sans-serif;color:#1f2a24;">
			<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f3ed;padding:32px 16px;">
				<tr>
					<td align="center">
						<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:760px;background:#ffffff;border:1px solid #e7e2d7;border-radius:12px;overflow:hidden;">
							<tr>
								<td style="padding:28px 32px;background:#4A3728;">
									<div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#e7dcc7;font-weight:700;">Projectmeubelshop</div>
									<h1 style="margin:10px 0 0;font-size:28px;line-height:1.2;color:#ffffff;">Nieuwe offerte aanvraag</h1>
								</td>
							</tr>
							<tr>
								<td style="padding:28px 32px;">
									<p style="margin:0 0 24px;font-size:15px;line-height:1.7;color:#4b5563;">Er is een nieuwe offerteaanvraag binnengekomen via de website.</p>

									<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 24px;border-collapse:separate;border-spacing:0;">
										<tr>
											<td colspan="2" style="padding:0 0 10px;font-size:13px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#68736d;">Klantgegevens</td>
										</tr>
										<tr>
											<td style="padding:12px 14px;background:#fbfaf8;border:1px solid #e7e2d7;font-size:13px;font-weight:700;color:#68736d;width:180px;">Naam</td>
											<td style="padding:12px 14px;background:#fbfaf8;border:1px solid #e7e2d7;border-left:none;font-size:14px;color:#1f2a24;">' . esc_html( $name ?: '-' ) . '</td>
										</tr>
										<tr>
											<td style="padding:12px 14px;background:#fbfaf8;border:1px solid #e7e2d7;border-top:none;font-size:13px;font-weight:700;color:#68736d;">E-mail</td>
											<td style="padding:12px 14px;background:#fbfaf8;border:1px solid #e7e2d7;border-left:none;border-top:none;font-size:14px;color:#1f2a24;"><a href="mailto:' . esc_attr( $email ) . '" style="color:#4A3728;text-decoration:none;">' . esc_html( $email ?: '-' ) . '</a></td>
										</tr>
										<tr>
											<td style="padding:12px 14px;background:#fbfaf8;border:1px solid #e7e2d7;border-top:none;font-size:13px;font-weight:700;color:#68736d;">Telefoon</td>
											<td style="padding:12px 14px;background:#fbfaf8;border:1px solid #e7e2d7;border-left:none;border-top:none;font-size:14px;color:#1f2a24;">' . esc_html( $phone ?: '-' ) . '</td>
										</tr>
									</table>

									<div style="margin:0 0 24px;">
										<div style="margin:0 0 10px;font-size:13px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#68736d;">Opmerkingen of vragen</div>
										<div style="padding:16px 18px;background:#fbfaf8;border:1px solid #e7e2d7;border-radius:10px;font-size:14px;line-height:1.7;color:#1f2a24;">' . $message_html . '</div>
									</div>

									<div style="margin:0;">
										<div style="margin:0 0 10px;font-size:13px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#68736d;">Aangevraagde producten</div>
										<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:separate;border-spacing:0;border:1px solid #e7e2d7;border-radius:10px;overflow:hidden;">
											<thead>
												<tr>
													<th align="left" style="padding:14px 16px;background:#C5B17D;color:#ffffff;font-size:12px;letter-spacing:.05em;text-transform:uppercase;">Product</th>
													<th align="center" style="padding:14px 16px;background:#C5B17D;color:#ffffff;font-size:12px;letter-spacing:.05em;text-transform:uppercase;">Aantal</th>
													<th align="right" style="padding:14px 16px;background:#C5B17D;color:#ffffff;font-size:12px;letter-spacing:.05em;text-transform:uppercase;">Link</th>
												</tr>
											</thead>
											<tbody>' . $item_rows . '</tbody>
										</table>
									</div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</body>
		</html>';
}

add_filter( 'manage_pms_quote_request_posts_columns', 'pms_quote_request_admin_columns' );
function pms_quote_request_admin_columns( $columns ) {
	return array(
		'cb'             => $columns['cb'] ?? '<input type="checkbox" />',
		'title'          => 'Aanvraag',
		'pms_customer'   => 'Klant',
		'pms_email'      => 'E-mail',
		'pms_phone'      => 'Telefoon',
		'pms_item_count' => 'Producten',
		'date'           => 'Datum',
	);
}

add_action( 'manage_pms_quote_request_posts_custom_column', 'pms_quote_request_admin_column_content', 10, 2 );
function pms_quote_request_admin_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'pms_customer':
			echo esc_html( get_post_meta( $post_id, '_pms_quote_name', true ) ?: '-' );
			break;
		case 'pms_email':
			$email = get_post_meta( $post_id, '_pms_quote_email', true );
			if ( $email ) {
				echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
			} else {
				echo '-';
			}
			break;
		case 'pms_phone':
			echo esc_html( get_post_meta( $post_id, '_pms_quote_phone', true ) ?: '-' );
			break;
		case 'pms_item_count':
			$items = get_post_meta( $post_id, '_pms_quote_items', true );
			echo esc_html( is_array( $items ) ? count( $items ) : 0 );
			break;
	}
}

add_action( 'add_meta_boxes', 'pms_quote_request_meta_boxes' );
function pms_quote_request_meta_boxes() {
	add_meta_box(
		'pms-quote-request-details',
		'Offerte aanvraag details',
		'pms_render_quote_request_meta_box',
		'pms_quote_request',
		'normal',
		'high'
	);
}

function pms_render_quote_request_meta_box( WP_Post $post ) {
	$name       = get_post_meta( $post->ID, '_pms_quote_name', true );
	$email      = get_post_meta( $post->ID, '_pms_quote_email', true );
	$phone      = get_post_meta( $post->ID, '_pms_quote_phone', true );
	$message    = get_post_meta( $post->ID, '_pms_quote_message', true );
	$created_at = get_post_meta( $post->ID, '_pms_quote_created_at', true );
	$items      = get_post_meta( $post->ID, '_pms_quote_items', true );

	if ( ! is_array( $items ) ) {
		$items = array();
	}
	?>
	<style>
		.pms-quote-admin-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 16px 24px;
			margin-bottom: 24px;
		}
		.pms-quote-admin-field strong {
			display: block;
			margin-bottom: 4px;
		}
		.pms-quote-admin-message {
			margin: 24px 0;
			padding: 16px;
			background: #fbfaf8;
			border: 1px solid #dedede;
			border-radius: 8px;
			white-space: pre-wrap;
		}
		.pms-quote-admin-items table {
			width: 100%;
			border-collapse: collapse;
		}
		.pms-quote-admin-items th,
		.pms-quote-admin-items td {
			padding: 10px 12px;
			border: 1px solid #e5e5e5;
			text-align: left;
			vertical-align: top;
		}
		@media (max-width: 782px) {
			.pms-quote-admin-grid {
				grid-template-columns: 1fr;
			}
		}
	</style>
	<div class="pms-quote-admin-grid">
		<div class="pms-quote-admin-field">
			<strong>Klant</strong>
			<span><?php echo esc_html( $name ?: '-' ); ?></span>
		</div>
		<div class="pms-quote-admin-field">
			<strong>Ingekomen op</strong>
			<span><?php echo esc_html( $created_at ? wp_date( 'd-m-Y H:i', strtotime( $created_at ) ) : '-' ); ?></span>
		</div>
		<div class="pms-quote-admin-field">
			<strong>E-mail</strong>
			<span>
				<?php if ( $email ) : ?>
					<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
				<?php else : ?>
					-
				<?php endif; ?>
			</span>
		</div>
		<div class="pms-quote-admin-field">
			<strong>Telefoon</strong>
			<span><?php echo esc_html( $phone ?: '-' ); ?></span>
		</div>
	</div>

	<div class="pms-quote-admin-field">
		<strong>Opmerkingen of vragen</strong>
		<div class="pms-quote-admin-message"><?php echo esc_html( $message ?: 'Geen extra opmerkingen opgegeven.' ); ?></div>
	</div>

	<div class="pms-quote-admin-items">
		<strong>Aangevraagde producten</strong>
		<table>
			<thead>
				<tr>
					<th>Product</th>
					<th>Aantal</th>
					<th>SKU</th>
					<th>Link</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $items ) ) : ?>
					<tr>
						<td colspan="4">Geen producten opgeslagen.</td>
					</tr>
				<?php else : ?>
					<?php foreach ( $items as $item ) : ?>
						<tr>
							<td><?php echo esc_html( $item['name'] ?? '' ); ?></td>
							<td><?php echo esc_html( $item['quantity'] ?? 1 ); ?></td>
							<td><?php echo esc_html( $item['sku'] ?? '-' ); ?></td>
							<td>
								<?php if ( ! empty( $item['permalink'] ) ) : ?>
									<a href="<?php echo esc_url( $item['permalink'] ); ?>" target="_blank" rel="noopener noreferrer">Bekijk product</a>
								<?php else : ?>
									-
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}

// ── Helpers ────────────────────────────────────────────────────────────────

function pms_quote_log( string $event, array $context = array() ): void {
	$server_host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$referer     = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
	$session_id  = '';
	$has_session = false;
	$item_count  = null;

	if ( function_exists( 'WC' ) && WC() && isset( WC()->session ) && WC()->session ) {
		if ( method_exists( WC()->session, 'get_customer_id' ) ) {
			$session_id = (string) WC()->session->get_customer_id();
		}

		if ( method_exists( WC()->session, 'has_session' ) ) {
			$has_session = (bool) WC()->session->has_session();
		}

		$items = WC()->session->get( 'pms_quote_items' );
		if ( is_array( $items ) ) {
			$item_count = count( $items );
		}
	}

	$payload = array_merge(
		array(
			'timestamp'   => current_time( 'mysql' ),
			'event'       => $event,
			'host'        => $server_host,
			'request_uri' => $request_uri,
			'referer'     => $referer,
			'session_id'  => $session_id,
			'has_session' => $has_session,
			'item_count'  => $item_count,
		),
		$context
	);

	$stored_logs   = get_option( 'pms_quote_debug_entries', array() );
	$stored_logs   = is_array( $stored_logs ) ? $stored_logs : array();
	$stored_logs[] = $payload;

	if ( count( $stored_logs ) > 200 ) {
		$stored_logs = array_slice( $stored_logs, -200 );
	}

	update_option( 'pms_quote_debug_entries', $stored_logs, false );
	error_log( 'PMS_QUOTE_DEBUG ' . wp_json_encode( $payload ) );
}

add_action( 'admin_menu', 'pms_quote_register_debug_page' );
function pms_quote_register_debug_page(): void {
	add_submenu_page(
		'edit.php?post_type=pms_quote_request',
		'Offerte debug log',
		'Debug log',
		'manage_options',
		'pms-quote-debug-log',
		'pms_quote_render_debug_page'
	);
}

function pms_quote_get_debug_log_path(): string {
	return trailingslashit( WP_CONTENT_DIR ) . 'debug.log';
}

function pms_quote_get_debug_log_entries( int $limit = 200 ): array {
	$path = pms_quote_get_debug_log_path();

	if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
		return array();
	}

	$lines = file( $path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
	if ( ! is_array( $lines ) || empty( $lines ) ) {
		return array();
	}

	$entries = array();

	foreach ( array_reverse( $lines ) as $line ) {
		if ( false === strpos( $line, 'PMS_QUOTE_DEBUG ' ) ) {
			continue;
		}

		$json = substr( $line, strpos( $line, 'PMS_QUOTE_DEBUG ' ) + strlen( 'PMS_QUOTE_DEBUG ' ) );
		$data = json_decode( trim( $json ), true );

		$entries[] = array(
			'raw'  => $line,
			'data' => is_array( $data ) ? $data : array( 'unparsed' => $json ),
		);

		if ( count( $entries ) >= $limit ) {
			break;
		}
	}

	return $entries;
}

function pms_quote_render_debug_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Je hebt geen rechten om deze pagina te bekijken.', 'projectmeubelshop' ) );
	}

	if ( isset( $_GET['pms_clear_quote_debug'] ) && '1' === $_GET['pms_clear_quote_debug'] && check_admin_referer( 'pms_clear_quote_debug' ) ) {
		delete_option( 'pms_quote_debug_entries' );
		echo '<div class="notice notice-success"><p>De offerte debug log is geleegd.</p></div>';
	}

	$entries = pms_quote_get_debug_log_entries( 150 );
	$db_logs  = get_option( 'pms_quote_debug_entries', array() );
	$db_logs  = is_array( $db_logs ) ? array_reverse( $db_logs ) : array();
	?>
	<div class="wrap">
		<h1>Offerte debug log</h1>
		<p>Hier zie je de meest recente offerte-debugregels. Eerst uit de interne database-log, en daarna eventueel uit <code>wp-content/debug.log</code>.</p>

		<p>
			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'pms_clear_quote_debug', '1' ), 'pms_clear_quote_debug' ) ); ?>" class="button button-secondary">Debug log legen</a>
		</p>

		<?php if ( ! empty( $db_logs ) ) : ?>
			<h2>Interne debug log</h2>
			<table class="widefat striped" style="margin-top:16px;margin-bottom:28px;">
				<thead>
					<tr>
						<th style="width:160px;">Tijd</th>
						<th style="width:160px;">Event</th>
						<th style="width:180px;">Host</th>
						<th style="width:220px;">Request</th>
						<th style="width:180px;">Session ID</th>
						<th style="width:90px;">Session</th>
						<th style="width:90px;">Items</th>
						<th>Details</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $db_logs as $data ) : ?>
						<tr>
							<td><?php echo esc_html( (string) ( $data['timestamp'] ?? '-' ) ); ?></td>
							<td><strong><?php echo esc_html( (string) ( $data['event'] ?? '-' ) ); ?></strong></td>
							<td><?php echo esc_html( (string) ( $data['host'] ?? '-' ) ); ?></td>
							<td><code><?php echo esc_html( (string) ( $data['request_uri'] ?? '-' ) ); ?></code></td>
							<td><code><?php echo esc_html( (string) ( $data['session_id'] ?? '-' ) ); ?></code></td>
							<td><?php echo ! empty( $data['has_session'] ) ? 'ja' : 'nee'; ?></td>
							<td><?php echo isset( $data['item_count'] ) ? esc_html( (string) $data['item_count'] ) : '-'; ?></td>
							<td>
								<details>
									<summary>Toon JSON</summary>
									<pre style="white-space:pre-wrap;word-break:break-word;margin-top:8px;"><?php echo esc_html( wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ?: '' ); ?></pre>
								</details>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<?php if ( empty( $entries ) ) : ?>
			<div class="notice notice-warning">
				<p>Er zijn geen regels gevonden in <code>wp-content/debug.log</code>. Dat is niet erg als de interne debug log hierboven wel gevuld wordt.</p>
			</div>
		<?php else : ?>
			<h2>debug.log regels</h2>
			<table class="widefat striped" style="margin-top:16px;">
				<thead>
					<tr>
						<th style="width:160px;">Event</th>
						<th style="width:180px;">Host</th>
						<th style="width:220px;">Request</th>
						<th style="width:180px;">Session ID</th>
						<th style="width:90px;">Session</th>
						<th style="width:90px;">Items</th>
						<th>Details</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $entries as $entry ) : ?>
						<?php $data = $entry['data']; ?>
						<tr>
							<td><strong><?php echo esc_html( (string) ( $data['event'] ?? '-' ) ); ?></strong></td>
							<td><?php echo esc_html( (string) ( $data['host'] ?? '-' ) ); ?></td>
							<td><code><?php echo esc_html( (string) ( $data['request_uri'] ?? '-' ) ); ?></code></td>
							<td><code><?php echo esc_html( (string) ( $data['session_id'] ?? '-' ) ); ?></code></td>
							<td><?php echo ! empty( $data['has_session'] ) ? 'ja' : 'nee'; ?></td>
							<td><?php echo isset( $data['item_count'] ) ? esc_html( (string) $data['item_count'] ) : '-'; ?></td>
							<td>
								<details>
									<summary>Toon JSON</summary>
									<pre style="white-space:pre-wrap;word-break:break-word;margin-top:8px;"><?php echo esc_html( wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ?: $entry['raw'] ); ?></pre>
								</details>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<?php
}

function pms_quote_ensure_session() {
	if ( ! function_exists( 'WC' ) || ! WC() ) {
		pms_quote_log( 'ensure_session_wc_missing' );
		return false;
	}

	if ( null === WC()->session && method_exists( WC(), 'initialize_session' ) ) {
		WC()->initialize_session();
	}

	if ( ! isset( WC()->session ) || ! WC()->session ) {
		pms_quote_log( 'ensure_session_failed' );
		return false;
	}

	if ( method_exists( WC()->session, 'set_customer_session_cookie' ) && ! WC()->session->has_session() ) {
		WC()->session->set_customer_session_cookie( true );
		pms_quote_log( 'session_cookie_initialized' );
	}

	pms_quote_log( 'ensure_session_ready' );
	return true;
}

function pms_quote_ready() {
	return pms_quote_ensure_session();
}

function pms_quote_get_items() {
	if ( ! pms_quote_ready() ) {
		return array();
	}
	$items = WC()->session->get( 'pms_quote_items' );
	return is_array( $items ) ? $items : array();
}

function pms_quote_set_items( $items ) {
	if ( ! pms_quote_ready() ) {
		return;
	}
	WC()->session->set( 'pms_quote_items', $items );
	if ( method_exists( WC()->session, 'save_data' ) ) {
		WC()->session->save_data();
	}
}

function pms_quote_has_product( $product_id ) {
	$items = pms_quote_get_items();
	return isset( $items[ (int) $product_id ] );
}

function pms_quote_get_count() {
	$items = pms_quote_get_items();
	$count = 0;

	foreach ( $items as $item ) {
		$count += isset( $item['quantity'] ) ? max( 1, (int) $item['quantity'] ) : 1;
	}

	return $count;
}

function pms_quote_add_item( $product_id, $quantity = 1, $variation_id = 0, $variation_data = array() ) {
	$items      = pms_quote_get_items();
	$product_id = (int) $product_id;
	if ( isset( $items[ $product_id ] ) ) {
		$items[ $product_id ]['quantity'] = max( 1, (int) $items[ $product_id ]['quantity'] + (int) $quantity );
	} else {
		$items[ $product_id ] = array(
			'quantity'       => max( 1, (int) $quantity ),
			'variation_id'   => (int) $variation_id,
			'variation_data' => (array) $variation_data,
		);
	}
	pms_quote_set_items( $items );
	pms_quote_log(
		'item_added',
		array(
			'product_id'   => $product_id,
			'quantity'     => (int) $quantity,
			'variation_id' => (int) $variation_id,
			'items_keys'   => array_map( 'intval', array_keys( $items ) ),
		)
	);
}

function pms_quote_get_page_url() {
	return home_url( '/offerte-samenstellen/' );
}

function pms_quote_is_page_request(): bool {
	if ( is_admin() ) {
		return false;
	}

	$quote_page_path = wp_parse_url( pms_quote_get_page_url(), PHP_URL_PATH );
	$current_path    = wp_parse_url( home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) ), PHP_URL_PATH );

	if ( is_string( $quote_page_path ) && is_string( $current_path ) ) {
		$quote_page_path = untrailingslashit( $quote_page_path );
		$current_path    = untrailingslashit( $current_path );

		if ( $quote_page_path === $current_path ) {
			return true;
		}
	}

	return function_exists( 'is_page' ) && is_page( 'offerte-samenstellen' );
}

function pms_quote_is_dynamic_request(): bool {
	if ( isset( $_POST['pms_action'] ) ) {
		return true;
	}

	if ( isset( $_GET['pms_added'] ) || isset( $_GET['pms_sent'] ) ) {
		return true;
	}

	return pms_quote_is_page_request();
}

add_action( 'template_redirect', 'pms_quote_disable_cache_for_dynamic_requests', 0 );
function pms_quote_disable_cache_for_dynamic_requests() {
	if ( ! pms_quote_is_dynamic_request() ) {
		return;
	}

	if ( function_exists( 'wc_nocache_headers' ) ) {
		wc_nocache_headers();
	} else {
		nocache_headers();
	}

	// Force the WooCommerce session to exist before the page is rendered,
	// otherwise a cached/offloaded response can make the quote appear empty.
	pms_quote_ensure_session();
}

// ── Action handlers (template_redirect) ────────────────────────────────────

add_action( 'template_redirect', 'pms_quote_handle_add' );
function pms_quote_handle_add() {
	if ( ! isset( $_POST['pms_action'] ) || $_POST['pms_action'] !== 'add_to_quote' ) {
		return;
	}
	if ( ! isset( $_POST['pms_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pms_nonce'] ) ), 'pms_add_to_quote' ) ) {
		return;
	}
	if ( ! function_exists( 'WC' ) ) {
		return;
	}
	if ( ! pms_quote_ensure_session() ) {
		return;
	}
	$product_id   = isset( $_POST['product_id'] ) ? (int) $_POST['product_id'] : 0;
	$quantity     = isset( $_POST['quantity'] ) ? max( 1, (int) $_POST['quantity'] ) : 1;
	$variation_id = isset( $_POST['variation_id'] ) ? (int) $_POST['variation_id'] : 0;
	if ( $product_id < 1 ) {
		pms_quote_log( 'add_rejected_invalid_product' );
		return;
	}
	pms_quote_add_item( $product_id, $quantity, $variation_id );
	$redirect = add_query_arg( 'pms_added', $product_id, wp_get_referer() ?: get_permalink( $product_id ) );
	pms_quote_log(
		'add_redirect',
		array(
			'product_id' => $product_id,
			'redirect'   => $redirect,
		)
	);
	wp_safe_redirect( $redirect );
	exit;
}

add_action( 'template_redirect', 'pms_quote_handle_update_or_remove' );
function pms_quote_handle_update_or_remove() {
	if ( ! isset( $_POST['pms_action'] ) ) {
		return;
	}
	$action = sanitize_text_field( wp_unslash( $_POST['pms_action'] ) );
	if ( ! in_array( $action, array( 'pms_update_quote', 'pms_remove_from_quote' ), true ) ) {
		return;
	}
	if ( ! isset( $_POST['pms_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pms_nonce'] ) ), 'pms_quote_page' ) ) {
		return;
	}
	$items = pms_quote_get_items();
	if ( $action === 'pms_remove_from_quote' ) {
		$product_id = isset( $_POST['product_id'] ) ? (int) $_POST['product_id'] : 0;
		unset( $items[ $product_id ] );
		pms_quote_set_items( $items );
	} elseif ( $action === 'pms_update_quote' && isset( $_POST['quantities'] ) && is_array( $_POST['quantities'] ) ) {
		foreach ( $_POST['quantities'] as $pid => $qty ) {
			$pid = (int) $pid;
			$qty = max( 1, (int) $qty );
			if ( isset( $items[ $pid ] ) ) {
				$items[ $pid ]['quantity'] = $qty;
			}
		}
		pms_quote_set_items( $items );
	}
	wp_safe_redirect( pms_quote_get_page_url() );
	exit;
}

add_action( 'template_redirect', 'pms_quote_handle_submit' );
function pms_quote_handle_submit() {
	if ( ! isset( $_POST['pms_action'] ) || $_POST['pms_action'] !== 'pms_submit_quote' ) {
		return;
	}
	if ( ! isset( $_POST['pms_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pms_nonce'] ) ), 'pms_quote_page' ) ) {
		return;
	}
	$firstname = sanitize_text_field( wp_unslash( $_POST['pms_firstname'] ?? '' ) );
	$lastname  = sanitize_text_field( wp_unslash( $_POST['pms_lastname'] ?? '' ) );
	$name      = trim( $firstname . ' ' . $lastname );
	$email     = sanitize_email( wp_unslash( $_POST['pms_email'] ?? '' ) );
	$phone     = sanitize_text_field( wp_unslash( $_POST['pms_phone'] ?? '' ) );
	$message   = sanitize_textarea_field( wp_unslash( $_POST['pms_message'] ?? '' ) );
	$items     = pms_quote_get_items();
	$item_data = pms_quote_build_items_snapshot( $items );

	$body  = "Offerte aanvraag van {$name}\n\n";
	$body .= "E-mail: {$email}\n";
	if ( $phone ) {
		$body .= "Telefoon: {$phone}\n";
	}
	if ( $message ) {
		$body .= "\nBericht:\n{$message}\n";
	}
	$body .= "\n--- Gevraagde producten ---\n\n";
	foreach ( $item_data as $item ) {
		$body .= '- ' . $item['name'] . ' (aantal: ' . $item['quantity'] . ")\n";
		$body .= '  ' . $item['permalink'] . "\n\n";
	}

	$html_body = pms_quote_build_email_html(
		array(
			'name'    => $name,
			'email'   => $email,
			'phone'   => $phone,
			'message' => $message,
			'items'   => $item_data,
		)
	);

	pms_quote_store_request(
		array(
			'firstname' => $firstname,
			'lastname'  => $lastname,
			'name'      => $name,
			'email'     => $email,
			'phone'     => $phone,
			'message'   => $message,
			'items'     => $item_data,
		)
	);

	$to      = 'support@projectmeubelshop.nl';
	$subject = 'Nieuwe offerte aanvraag – ' . $name;
	$headers = array(
		'From: Projectmeubelshop <support@projectmeubelshop.nl>',
		'Reply-To: ' . $email,
		'Content-Type: text/html; charset=UTF-8',
	);
	wp_mail( $to, $subject, $html_body, $headers );

	pms_quote_set_items( array() );

	wp_safe_redirect( add_query_arg( 'pms_sent', '1', pms_quote_get_page_url() ) );
	exit;
}

add_action( 'template_redirect', 'pms_quote_handle_standalone_form_submit' );
function pms_quote_handle_standalone_form_submit() {
	if ( ! isset( $_POST['pms_action'] ) || 'pms_submit_standalone_quote' !== $_POST['pms_action'] ) {
		return;
	}

	if ( ! isset( $_POST['pms_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pms_nonce'] ) ), 'pms_standalone_quote_form' ) ) {
		return;
	}

	$firstname = sanitize_text_field( wp_unslash( $_POST['pms_firstname'] ?? '' ) );
	$lastname  = sanitize_text_field( wp_unslash( $_POST['pms_lastname'] ?? '' ) );
	$name      = trim( $firstname . ' ' . $lastname );
	$email     = sanitize_email( wp_unslash( $_POST['pms_email'] ?? '' ) );
	$phone     = sanitize_text_field( wp_unslash( $_POST['pms_phone'] ?? '' ) );
	$message   = sanitize_textarea_field( wp_unslash( $_POST['pms_message'] ?? '' ) );

	pms_quote_store_request(
		array(
			'firstname' => $firstname,
			'lastname'  => $lastname,
			'name'      => $name,
			'email'     => $email,
			'phone'     => $phone,
			'message'   => $message,
			'items'     => array(),
		)
	);

	$html_body = pms_quote_build_email_html(
		array(
			'name'    => $name,
			'email'   => $email,
			'phone'   => $phone,
			'message' => $message,
			'items'   => array(),
		)
	);

	$to      = 'support@projectmeubelshop.nl';
	$subject = 'Nieuwe offerte aanvraag – ' . $name;
	$headers = array(
		'From: Projectmeubelshop <support@projectmeubelshop.nl>',
		'Reply-To: ' . $email,
		'Content-Type: text/html; charset=UTF-8',
	);

	wp_mail( $to, $subject, $html_body, $headers );

	$redirect = wp_get_referer() ?: home_url( '/' );
	$redirect = remove_query_arg( 'pms_quote_form_sent', $redirect );
	$redirect = add_query_arg( 'pms_quote_form_sent', '1', $redirect );
	wp_safe_redirect( $redirect );
	exit;
}

add_action( 'template_redirect', 'pms_quote_handle_room_builder_submit' );
function pms_quote_handle_room_builder_submit() {
	if ( ! isset( $_POST['pms_action'] ) || 'pms_submit_room_builder_quote' !== $_POST['pms_action'] ) {
		return;
	}

	if ( ! isset( $_POST['pms_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pms_nonce'] ) ), 'pms_room_builder_quote_form' ) ) {
		return;
	}

	$firstname = sanitize_text_field( wp_unslash( $_POST['pms_firstname'] ?? '' ) );
	$lastname  = sanitize_text_field( wp_unslash( $_POST['pms_lastname'] ?? '' ) );
	$name      = trim( $firstname . ' ' . $lastname );
	$email     = sanitize_email( wp_unslash( $_POST['pms_email'] ?? '' ) );
	$phone     = sanitize_text_field( wp_unslash( $_POST['pms_phone'] ?? '' ) );
	$message   = sanitize_textarea_field( wp_unslash( $_POST['pms_message'] ?? '' ) );
	$submitted_items = isset( $_POST['pms_room_builder_items'] ) && is_array( $_POST['pms_room_builder_items'] )
		? wp_unslash( $_POST['pms_room_builder_items'] )
		: array();

	$selected_items = array();
	foreach ( $submitted_items as $item_key => $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}

		$selected_items[ sanitize_key( (string) $item_key ) ] = array(
			'term_id'  => isset( $item['term_id'] ) ? (int) $item['term_id'] : 0,
			'label'    => isset( $item['label'] ) ? sanitize_text_field( (string) $item['label'] ) : '',
			'quantity' => isset( $item['quantity'] ) ? (int) $item['quantity'] : 0,
		);
	}

	$item_data = pms_quote_build_room_builder_snapshot( $selected_items );

	pms_quote_store_request(
		array(
			'firstname' => $firstname,
			'lastname'  => $lastname,
			'name'      => $name,
			'email'     => $email,
			'phone'     => $phone,
			'message'   => $message,
			'items'     => $item_data,
			'source'    => 'room_builder',
		)
	);

	$html_body = pms_quote_build_email_html(
		array(
			'name'    => $name,
			'email'   => $email,
			'phone'   => $phone,
			'message' => $message,
			'items'   => $item_data,
		)
	);

	$to      = 'support@projectmeubelshop.nl';
	$subject = 'Nieuwe inrichting aanvraag – ' . $name;
	$headers = array(
		'From: Projectmeubelshop <support@projectmeubelshop.nl>',
		'Reply-To: ' . $email,
		'Content-Type: text/html; charset=UTF-8',
	);

	wp_mail( $to, $subject, $html_body, $headers );

	$redirect = wp_get_referer() ?: home_url( '/inrichting-samenstellen/' );
	$redirect = remove_query_arg( 'pms_room_builder_sent', $redirect );
	$redirect = add_query_arg( 'pms_room_builder_sent', '1', $redirect );
	wp_safe_redirect( $redirect );
	exit;
}

// ── Shortcode [pms_quote_page] ──────────────────────────────────────────────

add_shortcode( 'pms_quote_page', 'pms_quote_render_page' );
function pms_quote_render_page() {
	if ( ! function_exists( 'WC' ) ) {
		return '<p>WooCommerce is niet actief.</p>';
	}

	$items = pms_quote_get_items();
	pms_quote_log(
		'render_page',
		array(
			'items_keys' => array_map( 'intval', array_keys( $items ) ),
		)
	);
	$nonce = wp_create_nonce( 'pms_quote_page' );

	// SVG iconen
	$icon_minus = '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/></svg>';
	$icon_plus  = '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>';
	$icon_trash = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>';

	ob_start();
	?>
	<style>
	/* ── Reset & base ── */
	.pms-qp {
		font-family: Inter, sans-serif;
		color: #333;
		max-width: 860px;
		margin: 0 auto;
	}
	.pms-qp *, .pms-qp *::before, .pms-qp *::after {
		box-sizing: border-box;
	}

	/* ── Bevestigingsbanner ── */
	.pms-qp__sent {
		display: flex;
		align-items: flex-start;
		gap: 14px;
		background: #f0fdf4;
		border: 1px solid #86efac;
		border-left: 4px solid #16a34a;
		border-radius: 10px;
		padding: 20px 24px;
		margin-bottom: 32px;
	}
	.pms-qp__sent-icon {
		flex-shrink: 0;
		color: #16a34a;
		margin-top: 1px;
	}
	.pms-qp__sent-title {
		font-size: 15px;
		font-weight: 700;
		color: #15803d;
		margin: 0 0 2px;
	}
	.pms-qp__sent-sub {
		font-size: 13px;
		color: #166534;
		margin: 0;
	}

	/* ── Lege staat ── */
	.pms-qp__empty {
		text-align: center;
		padding: 64px 24px;
		color: #888;
	}
	.pms-qp__empty-icon {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 64px;
		height: 64px;
		background: #f5f5f5;
		border-radius: 50%;
		margin: 0 auto 20px;
		color: #bbb;
	}
	.pms-qp__empty h3 {
		font-size: 18px;
		font-weight: 700;
		color: #444;
		margin: 0 0 6px;
	}
	.pms-qp__empty p { margin: 0 0 20px; font-size: 14px; }
	.pms-qp__empty a {
		display: inline-flex;
		align-items: center;
		height: 44px;
		padding: 0 24px;
		background: var(--color-primary, #4A3728);
		color: #fff;
		border-radius: 8px;
		font-weight: 700;
		font-size: 14px;
		text-decoration: none;
		transition: background 0.15s;
	}
	.pms-qp__empty a:hover { background: #3d2d20; }

	/* ── Sectie-kaarten ── */
	.pms-qp__card {
		background: #fff;
		border: 1px solid var(--color-border, #dedede);
		border-radius: 5px;
		overflow: hidden;
		margin-bottom: 24px;
		box-shadow: 0 2px 12px rgba(0,0,0,0.05);
	}
	.pms-qp__card-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		padding: 18px 24px;
		border-bottom: 1px solid var(--color-border, #dedede);
		background: #fafaf9;
	}
	.pms-qp__card-title {
		font-size: 14px;
		font-weight: 700;
		color: #222;
		text-transform: uppercase;
		letter-spacing: 0.06em;
		margin: 0;
	}
	.pms-qp__item-count {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-width: 22px;
		height: 22px;
		padding: 0 6px;
		background: var(--color-primary, #4A3728);
		color: #fff;
		border-radius: 999px;
		font-size: 11px;
		font-weight: 700;
	}
	.pms-qp__card-body { padding: 0; }

	/* ── Product rijen ── */
	.pms-qp__product-row {
		display: flex;
		align-items: center;
		gap: 16px;
		padding: 16px 24px;
		border-bottom: 1px solid var(--color-border, #dedede);
		transition: background 0.1s;
	}
	.pms-qp__product-row:last-child { border-bottom: none; }
	.pms-qp__product-row:hover { background: #fafaf9; }

	.pms-qp__thumb {
		width: 64px;
		height: 64px;
		object-fit: cover;
		border-radius: 8px;
		border: 1px solid var(--color-border, #dedede);
		flex-shrink: 0;
		display: block;
	}
	.pms-qp__thumb-placeholder {
		width: 64px;
		height: 64px;
		border-radius: 8px;
		border: 1px solid var(--color-border, #dedede);
		background: #f5f5f5;
		flex-shrink: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		color: #ccc;
	}

	.pms-qp__product-info {
		flex: 1 1 0;
		min-width: 0;
	}
	.pms-qp__product-name {
		font-size: 14px;
		font-weight: 600;
		color: #222;
		text-decoration: none;
		display: block;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		margin-bottom: 2px;
	}
	.pms-qp__product-name:hover { color: var(--color-primary, #4A3728); }
	.pms-qp__product-cat {
		font-size: 12px;
		color: #999;
	}

	.pms-qp__product-actions {
		display: flex;
		align-items: center;
		gap: 10px;
		margin-left: auto;
		flex-shrink: 0;
	}

	/* Stepper ── */
	.pms-qp__stepper {
		display: flex;
		align-items: center;
		border: 1px solid var(--color-border, #dedede);
		border-radius: 8px;
		overflow: hidden;
		background: #fff;
		height: 38px;
		flex-shrink: 0;
	}
	.pms-qp__qty-btn {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 34px;
		height: 100%;
		background: none;
		border: none;
		padding: 0;
		color: #666;
		cursor: pointer;
		transition: background 0.15s, color 0.15s;
		flex-shrink: 0;
	}
	.pms-qp__qty-btn:hover {
		background: var(--color-bg, #f5f5f5);
		color: var(--color-primary, #4A3728);
	}
	.pms-qp__qty-btn svg { display: block; pointer-events: none; }
	.pms-qp__qty-input {
		-webkit-appearance: none;
		appearance: none;
		width: 42px;
		height: 100%;
		border: none;
		border-left: 1px solid var(--color-border, #dedede);
		border-right: 1px solid var(--color-border, #dedede);
		padding: 0;
		font-family: Inter, sans-serif;
		font-size: 13px;
		font-weight: 600;
		color: #333;
		background: #fff;
		text-align: center;
		box-shadow: none;
		outline: none;
	}
	.pms-qp__qty-input::-webkit-inner-spin-button,
	.pms-qp__qty-input::-webkit-outer-spin-button { -webkit-appearance: none; }

	/* Verwijderknop */
	.pms-qp__remove-btn {
		display: inline-flex;
		align-items: center;
		gap: 5px;
		height: 34px;
		padding: 0 12px;
		background: #fff5f5;
		border: 1px solid #fca5a5;
		border-radius: 8px;
		color: #dc2626;
		font-family: Inter, sans-serif;
		font-size: 12px;
		font-weight: 600;
		cursor: pointer;
		transition: background 0.15s, border-color 0.15s;
		flex-shrink: 0;
		white-space: nowrap;
	}
	.pms-qp__remove-btn:hover {
		background: #fee2e2;
		border-color: #f87171;
	}
	.pms-qp__remove-btn svg { display: block; pointer-events: none; flex-shrink: 0; }

	/* Kaartfooter: bijwerken-knop */
	.pms-qp__card-footer {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		padding: 14px 24px;
		background: #fafaf9;
		border-top: 1px solid var(--color-border, #dedede);
		flex-wrap: wrap;
	}
	.pms-qp__footer-left { display: flex; gap: 10px; flex-wrap: wrap; }

	/* Knoppen */
	.pms-qp__btn {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		height: 40px;
		padding: 0 20px;
		border: 1.5px solid transparent;
		border-radius: 8px;
		font-family: Inter, sans-serif;
		font-size: 13px;
		font-weight: 700;
		cursor: pointer;
		text-decoration: none;
		transition: background 0.15s, color 0.15s, border-color 0.15s;
		white-space: nowrap;
	}
	.pms-qp__btn--primary {
		background: var(--color-primary, #4A3728);
		color: #fff;
		border-color: var(--color-primary, #4A3728);
	}
	.pms-qp__btn--primary:hover { background: #3d2d20; border-color: #3d2d20; color: #fff; }
	.pms-qp__btn--outline {
		background: #fff;
		color: var(--color-primary, #4A3728);
		border-color: var(--color-border, #dedede);
	}
	.pms-qp__btn--outline:hover { border-color: var(--color-primary, #4A3728); }

	/* ── Formulier ── */
	.pms-qp__fields {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 20px;
		padding: 24px;
	}
	.pms-qp__field {
		display: flex;
		flex-direction: column;
		gap: 6px;
	}
	.pms-qp__field--full { grid-column: 1 / -1; }
	.pms-qp__label {
		font-size: 12px;
		font-weight: 600;
		color: #666;
		text-transform: uppercase;
		letter-spacing: 0.05em;
	}
	.pms-qp__label abbr { text-decoration: none; color: #b91c1c; margin-left: 2px; }
	.pms-qp__input,
	.pms-qp__textarea {
		width: 100%;
		border: 1px solid #DEDEDE;
		border-radius: 5px;
		padding: 11px 14px;
		font-family: Inter, sans-serif;
		font-size: 14px;
		color: #333;
		background: #fff;
		box-shadow: none;
		transition: border-color 0.15s, box-shadow 0.15s;
	}
	.pms-qp__input::placeholder,
	.pms-qp__textarea::placeholder { color: #bbb; }
	.pms-qp__input:focus,
	.pms-qp__textarea:focus {
		outline: none;
		border-color: var(--color-secondary, #C5B17D);
		box-shadow: 0 0 0 3px rgba(197,177,125,0.18);
	}
	.pms-qp__textarea { resize: vertical; min-height: 100px; }

	.pms-qp__form-footer {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 16px;
		padding: 16px 24px 24px;
		flex-wrap: wrap;
	}
	.pms-qp__disclaimer {
		font-size: 12px;
		color: #aaa;
		margin: 0;
		flex: 1 1 200px;
	}
	.pms-qp__btn--submit {
		height: 48px;
		padding: 0 32px;
		font-size: 15px;
	}

	@media (max-width: 767px) {
		.pms-qp {
			max-width: 100%;
		}

		.pms-qp__sent {
			padding: 16px 18px;
			margin-bottom: 24px;
		}

		.pms-qp__empty {
			padding: 44px 18px;
		}

		.pms-qp__empty a {
			width: 100%;
			justify-content: center;
		}

		.pms-qp__card {
			margin-bottom: 18px;
		}

		.pms-qp__card-header,
		.pms-qp__card-footer {
			padding: 14px 16px;
		}

		.pms-qp__card-title {
			font-size: 13px;
		}

		.pms-qp__product-row {
			display: grid;
			grid-template-columns: 56px minmax(0, 1fr);
			gap: 12px;
			padding: 14px 16px;
			align-items: start;
		}

		.pms-qp__thumb,
		.pms-qp__thumb-placeholder {
			width: 56px;
			height: 56px;
			border-radius: 6px;
		}

		.pms-qp__product-info {
			min-width: 0;
			padding-top: 2px;
		}

		.pms-qp__product-name {
			white-space: normal;
			overflow: visible;
			text-overflow: initial;
			line-height: 1.4;
			margin-bottom: 4px;
		}

		.pms-qp__product-cat {
			display: block;
			line-height: 1.4;
		}

		.pms-qp__product-actions {
			grid-column: 1 / -1;
			margin-left: 0;
			flex-wrap: wrap;
			width: 100%;
			padding-top: 4px;
		}

		.pms-qp__stepper {
			flex: 1 1 100%;
			width: 100%;
			height: 42px;
		}

		.pms-qp__qty-btn {
			width: 40px;
		}

		.pms-qp__qty-input {
			width: 56px;
			font-size: 14px;
		}

		.pms-qp__remove-btn {
			flex: 1 1 100%;
			width: 100%;
			justify-content: center;
			height: 40px;
			font-size: 13px;
		}

		.pms-qp__footer-left {
			width: 100%;
			flex-direction: column;
		}

		.pms-qp__btn {
			width: 100%;
			justify-content: center;
		}

		.pms-qp__fields {
			grid-template-columns: 1fr;
			gap: 16px;
			padding: 16px;
		}

		.pms-qp__label {
			font-size: 11px;
		}

		.pms-qp__input,
		.pms-qp__textarea {
			font-size: 16px;
			padding: 12px 14px;
		}

		.pms-qp__textarea {
			min-height: 120px;
		}

		.pms-qp__form-footer {
			padding: 12px 16px 18px;
			flex-direction: column;
			align-items: stretch;
		}

		.pms-qp__disclaimer {
			font-size: 11px;
			line-height: 1.5;
		}

		.pms-qp__btn--submit {
			width: 100%;
			height: 46px;
			padding: 0 20px;
			font-size: 14px;
			justify-content: center;
		}
	}

	@media (max-width: 420px) {
		.pms-qp__sent {
			padding: 14px 16px;
		}

		.pms-qp__card-header,
		.pms-qp__card-footer,
		.pms-qp__product-row,
		.pms-qp__fields,
		.pms-qp__form-footer {
			padding-left: 14px;
			padding-right: 14px;
		}

		.pms-qp__product-row {
			grid-template-columns: 48px minmax(0, 1fr);
			gap: 10px;
		}

		.pms-qp__thumb,
		.pms-qp__thumb-placeholder {
			width: 48px;
			height: 48px;
		}

		.pms-qp__product-name {
			font-size: 13px;
		}

		.pms-qp__product-cat {
			font-size: 11px;
		}
	}
	</style>

	<div class="pms-qp">

	<?php if ( isset( $_GET['pms_sent'] ) ) : ?>
		<div class="pms-qp__sent">
			<div class="pms-qp__sent-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
			</div>
			<div>
				<p class="pms-qp__sent-title">Offerte aanvraag verstuurd!</p>
				<p class="pms-qp__sent-sub">Bedankt voor je aanvraag. We nemen zo snel mogelijk contact met je op.</p>
			</div>
		</div>

	<?php elseif ( empty( $items ) ) : ?>
		<div class="pms-qp__empty">
			<div class="pms-qp__empty-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
			</div>
			<h3>Je offerte is leeg</h3>
			<p>Voeg producten toe via de productpagina's.</p>
			<a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>">Bekijk ons aanbod</a>
		</div>

	<?php else : ?>

		<!-- ── Productenkaart ── -->
		<form method="post" action="">
			<?php wp_nonce_field( 'pms_quote_page', 'pms_nonce' ); ?>
			<input type="hidden" name="product_id" value="" id="pms-remove-pid">

			<div class="pms-qp__card">
				<div class="pms-qp__card-header">
					<h2 class="pms-qp__card-title">Geselecteerde producten</h2>
					<span class="pms-qp__item-count"><?php echo count( $items ); ?></span>
				</div>
				<div class="pms-qp__card-body">
					<?php foreach ( $items as $product_id => $item ) :
						$product = wc_get_product( $product_id );
						if ( ! $product ) continue;
						$thumb_id = $product->get_image_id();
						$cats     = get_the_terms( $product_id, 'product_cat' );
						$cat_name = ( ! empty( $cats ) && ! is_wp_error( $cats ) ) ? esc_html( $cats[0]->name ) : '';
					?>
					<div class="pms-qp__product-row">
						<?php if ( $thumb_id ) : ?>
							<?php echo wp_get_attachment_image( $thumb_id, array( 64, 64 ), false, array( 'class' => 'pms-qp__thumb' ) ); ?>
						<?php else : ?>
							<div class="pms-qp__thumb-placeholder">
								<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
							</div>
						<?php endif; ?>

						<div class="pms-qp__product-info">
							<a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>" class="pms-qp__product-name">
								<?php echo esc_html( $product->get_name() ); ?>
							</a>
							<?php if ( $cat_name ) : ?>
								<span class="pms-qp__product-cat"><?php echo $cat_name; ?></span>
							<?php endif; ?>
						</div>

						<div class="pms-qp__product-actions">
							<div class="pms-qp__stepper" data-stepper>
								<button type="button" class="pms-qp__qty-btn pms-qp__qty-btn--minus" aria-label="Minder">
									<?php echo $icon_minus; ?>
								</button>
								<input type="number" class="pms-qp__qty-input"
									name="quantities[<?php echo esc_attr( $product_id ); ?>]"
									value="<?php echo esc_attr( $item['quantity'] ); ?>"
									min="1" aria-label="Aantal">
								<button type="button" class="pms-qp__qty-btn pms-qp__qty-btn--plus" aria-label="Meer">
									<?php echo $icon_plus; ?>
								</button>
							</div>

							<button type="submit"
								class="pms-qp__remove-btn"
								name="pms_action"
								value="pms_remove_from_quote"
								onclick="document.getElementById('pms-remove-pid').value='<?php echo esc_js( $product_id ); ?>'">
								<?php echo $icon_trash; ?>
								Verwijderen
							</button>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
				<div class="pms-qp__card-footer">
					<div class="pms-qp__footer-left">
						<button type="submit" name="pms_action" value="pms_update_quote" class="pms-qp__btn pms-qp__btn--outline">
							Aantallen bijwerken
						</button>
						<a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>" class="pms-qp__btn pms-qp__btn--outline">
							+ Meer producten toevoegen
						</a>
					</div>
				</div>
			</div>
		</form>

		<!-- ── Contactformulier ── -->
		<form method="post" action="">
			<?php wp_nonce_field( 'pms_quote_page', 'pms_nonce' ); ?>
			<input type="hidden" name="pms_action" value="pms_submit_quote">

			<div class="pms-qp__card">
				<div class="pms-qp__card-header">
					<h2 class="pms-qp__card-title">Jouw gegevens</h2>
				</div>
				<div class="pms-qp__fields">
					<div class="pms-qp__field">
						<label class="pms-qp__label" for="pms_firstname">Voornaam <abbr title="verplicht">*</abbr></label>
						<input class="pms-qp__input" type="text" id="pms_firstname" name="pms_firstname" required placeholder="Voornaam">
					</div>
					<div class="pms-qp__field">
						<label class="pms-qp__label" for="pms_lastname">Achternaam <abbr title="verplicht">*</abbr></label>
						<input class="pms-qp__input" type="text" id="pms_lastname" name="pms_lastname" required placeholder="Achternaam">
					</div>
					<div class="pms-qp__field">
						<label class="pms-qp__label" for="pms_email">E-mailadres <abbr title="verplicht">*</abbr></label>
						<input class="pms-qp__input" type="email" id="pms_email" name="pms_email" required placeholder="naam@bedrijf.nl">
					</div>
					<div class="pms-qp__field">
						<label class="pms-qp__label" for="pms_phone">Telefoonnummer</label>
						<input class="pms-qp__input" type="tel" id="pms_phone" name="pms_phone" placeholder="06 – 12 34 56 78">
					</div>
					<div class="pms-qp__field pms-qp__field--full">
						<label class="pms-qp__label" for="pms_message">Opmerkingen of vragen</label>
						<textarea class="pms-qp__textarea" id="pms_message" name="pms_message" placeholder="Bijv. leveringsadres, gewenste datum of specifieke wensen..."></textarea>
					</div>
				</div>
				<div class="pms-qp__form-footer">
					<p class="pms-qp__disclaimer">Je gegevens worden uitsluitend gebruikt voor het verwerken van je offerte aanvraag.</p>
					<button type="submit" class="pms-qp__btn pms-qp__btn--primary pms-qp__btn--submit">
						Offerte aanvragen
					</button>
				</div>
			</div>
		</form>

	<?php endif; ?>

	</div>

	<script>
	(function () {
		document.querySelectorAll('[data-stepper]').forEach(function (stepper) {
			var input = stepper.querySelector('.pms-qp__qty-input');
			var minus = stepper.querySelector('.pms-qp__qty-btn--minus');
			var plus  = stepper.querySelector('.pms-qp__qty-btn--plus');
			if (!input) return;
			minus.addEventListener('click', function () {
				var v = parseInt(input.value, 10) || 1;
				if (v > 1) input.value = v - 1;
			});
			plus.addEventListener('click', function () {
				var v = parseInt(input.value, 10) || 1;
				input.value = v + 1;
			});
		});
	})();
	</script>
	<?php
	return ob_get_clean();
}

add_shortcode( 'pms_quote_form', 'pms_quote_render_standalone_form' );
function pms_quote_render_standalone_form() {
	$nonce = wp_create_nonce( 'pms_standalone_quote_form' );

	ob_start();
	?>
	<style>
	.pms-sqf {
		max-width: 860px;
		margin: 0 auto;
		font-family: Inter, sans-serif;
		color: #333;
	}
	.pms-sqf *, .pms-sqf *::before, .pms-sqf *::after {
		box-sizing: border-box;
	}
	.pms-sqf__sent {
		display: flex;
		align-items: flex-start;
		gap: 14px;
		margin-bottom: 24px;
		padding: 18px 20px;
		border: 1px solid #86efac;
		border-left: 4px solid #16a34a;
		border-radius: 8px;
		background: #f0fdf4;
	}
	.pms-sqf__sent strong {
		display: block;
		margin-bottom: 4px;
		font-size: 15px;
		color: #15803d;
	}
	.pms-sqf__sent p {
		margin: 0;
		font-size: 13px;
		color: #166534;
	}
	.pms-sqf__card {
		background: #fff;
		border: 1px solid var(--color-border, #dedede);
		border-radius: 5px;
		overflow: hidden;
		box-shadow: 0 2px 12px rgba(0,0,0,0.05);
	}
	.pms-sqf__header {
		padding: 18px 24px;
		border-bottom: 1px solid var(--color-border, #dedede);
		background: #fafaf9;
	}
	.pms-sqf__title {
		margin: 0;
		font-size: 14px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: .06em;
		color: #222;
	}
	.pms-sqf__fields {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 20px;
		padding: 24px;
	}
	.pms-sqf__field {
		display: flex;
		flex-direction: column;
		gap: 6px;
	}
	.pms-sqf__field--full {
		grid-column: 1 / -1;
	}
	.pms-sqf__label {
		font-size: 12px;
		font-weight: 600;
		color: #666;
		text-transform: uppercase;
		letter-spacing: .05em;
	}
	.pms-sqf__label abbr {
		margin-left: 2px;
		color: #b91c1c;
		text-decoration: none;
	}
	.pms-sqf__input,
	.pms-sqf__textarea {
		width: 100%;
		padding: 11px 14px;
		border: 1px solid #DEDEDE;
		border-radius: 5px;
		background: #fff;
		color: #333;
		font-family: Inter, sans-serif;
		font-size: 14px;
		box-shadow: none;
		transition: border-color .15s ease, box-shadow .15s ease;
	}
	.pms-sqf__input::placeholder,
	.pms-sqf__textarea::placeholder {
		color: #bbb;
	}
	.pms-sqf__input:focus,
	.pms-sqf__textarea:focus {
		outline: none;
		border-color: var(--color-secondary, #C5B17D);
		box-shadow: 0 0 0 3px rgba(197,177,125,0.18);
	}
	.pms-sqf__textarea {
		min-height: 140px;
		resize: vertical;
	}
	.pms-sqf__footer {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 16px;
		flex-wrap: wrap;
		padding: 0 24px 24px;
	}
	.pms-sqf__disclaimer {
		flex: 1 1 240px;
		margin: 0;
		font-size: 12px;
		line-height: 1.6;
		color: #8a8a8a;
	}
	.pms-sqf__submit {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-height: 48px;
		padding: 0 28px;
		border: 1px solid var(--color-primary, #4A3728);
		border-radius: 6px;
		background: var(--color-primary, #4A3728);
		color: #fff;
		font-family: Inter, sans-serif;
		font-size: 15px;
		font-weight: 700;
		cursor: pointer;
	}
	.pms-sqf__submit:hover {
		background: #3d2d20;
		border-color: #3d2d20;
	}
	@media (max-width: 767px) {
		.pms-sqf__header {
			padding: 16px 18px;
		}
		.pms-sqf__fields {
			grid-template-columns: 1fr;
			gap: 16px;
			padding: 18px;
		}
		.pms-sqf__input,
		.pms-sqf__textarea {
			font-size: 16px;
			padding: 12px 14px;
		}
		.pms-sqf__footer {
			padding: 0 18px 18px;
			flex-direction: column;
			align-items: stretch;
		}
		.pms-sqf__submit {
			width: 100%;
		}
	}
	</style>

	<div class="pms-sqf">
		<?php if ( isset( $_GET['pms_quote_form_sent'] ) ) : ?>
			<div class="pms-sqf__sent">
				<div>
					<strong>Offerte aanvraag verstuurd</strong>
					<p>Bedankt voor je aanvraag. We nemen zo snel mogelijk contact met je op.</p>
				</div>
			</div>
		<?php endif; ?>

		<form method="post" action="">
			<?php wp_nonce_field( 'pms_standalone_quote_form', 'pms_nonce' ); ?>
			<input type="hidden" name="pms_action" value="pms_submit_standalone_quote">

			<div class="pms-sqf__card">
				<div class="pms-sqf__header">
					<h2 class="pms-sqf__title">Offerte aanvraag</h2>
				</div>

				<div class="pms-sqf__fields">
					<div class="pms-sqf__field">
						<label class="pms-sqf__label" for="pms-sqf-firstname">Voornaam <abbr title="verplicht">*</abbr></label>
						<input class="pms-sqf__input" type="text" id="pms-sqf-firstname" name="pms_firstname" required placeholder="Voornaam">
					</div>
					<div class="pms-sqf__field">
						<label class="pms-sqf__label" for="pms-sqf-lastname">Achternaam <abbr title="verplicht">*</abbr></label>
						<input class="pms-sqf__input" type="text" id="pms-sqf-lastname" name="pms_lastname" required placeholder="Achternaam">
					</div>
					<div class="pms-sqf__field">
						<label class="pms-sqf__label" for="pms-sqf-email">E-mailadres <abbr title="verplicht">*</abbr></label>
						<input class="pms-sqf__input" type="email" id="pms-sqf-email" name="pms_email" required placeholder="naam@bedrijf.nl">
					</div>
					<div class="pms-sqf__field">
						<label class="pms-sqf__label" for="pms-sqf-phone">Telefoonnummer</label>
						<input class="pms-sqf__input" type="tel" id="pms-sqf-phone" name="pms_phone" placeholder="06 – 12 34 56 78">
					</div>
					<div class="pms-sqf__field pms-sqf__field--full">
						<label class="pms-sqf__label" for="pms-sqf-message">Opmerkingen of vragen</label>
						<textarea class="pms-sqf__textarea" id="pms-sqf-message" name="pms_message" placeholder="Omschrijf hier je aanvraag of wensen..."></textarea>
					</div>
				</div>

				<div class="pms-sqf__footer">
					<p class="pms-sqf__disclaimer">Je gegevens worden uitsluitend gebruikt voor het verwerken van je offerte aanvraag.</p>
					<button type="submit" class="pms-sqf__submit">Offerte aanvragen</button>
				</div>
			</div>
		</form>
	</div>
	<?php

	return ob_get_clean();
}

add_shortcode( 'pms_inrichting_samenstellen', 'pms_quote_render_room_builder' );
function pms_quote_render_room_builder( array $atts = array() ) {
	$atts = shortcode_atts(
		array(
			'title'      => 'Kies hieronder waar jij (ongeveer) naar zoekt:',
			'categories' => '',
		),
		$atts,
		'pms_inrichting_samenstellen'
	);

	$assets = pms_quote_get_room_builder_assets();

	if ( '' !== trim( (string) $atts['categories'] ) ) {
		$allowed_keys = array_filter( array_map( 'sanitize_title', array_map( 'trim', explode( ',', (string) $atts['categories'] ) ) ) );
		if ( ! empty( $allowed_keys ) ) {
			$assets = array_values(
				array_filter(
					$assets,
					static function ( array $asset ) use ( $allowed_keys ): bool {
						return in_array( sanitize_title( $asset['label'] ), $allowed_keys, true ) || in_array( $asset['key'], $allowed_keys, true );
					}
				)
			);
		}
	}

	ob_start();
	?>
	<style>
	.pms-rb {
		max-width: 1200px;
		margin: 0 auto;
		font-family: Inter, sans-serif;
		color: #2f2a24;
	}
	.pms-rb *, .pms-rb *::before, .pms-rb *::after {
		box-sizing: border-box;
	}
	.pms-rb__sent {
		margin-bottom: 24px;
		padding: 18px 20px;
		border: 1px solid #86efac;
		border-left: 4px solid #16a34a;
		border-radius: 8px;
		background: #f0fdf4;
	}
	.pms-rb__sent strong {
		display: block;
		margin-bottom: 4px;
		font-size: 16px;
		color: #15803d;
	}
	.pms-rb__sent p {
		margin: 0;
		font-size: 14px;
		color: #166534;
	}
	.pms-rb__headline {
		margin: 0 0 22px;
		font-family: Inter, sans-serif;
		font-size: 24px;
		font-weight: 700;
		line-height: 1.15;
		color: #2d241b;
	}
	.pms-rb__grid {
		display: grid;
		grid-template-columns: repeat(4, minmax(0, 1fr));
		gap: 20px;
		margin-bottom: 28px;
	}
	.pms-rb__card {
		display: flex;
		flex-direction: column;
		min-height: 100%;
		cursor: pointer;
		transition: transform .15s ease;
	}
	.pms-rb__card:hover {
		transform: translateY(-2px);
	}
	.pms-rb__media {
		position: relative;
		aspect-ratio: 4 / 3;
		background: linear-gradient(135deg, #f2ece3 0%, #ded1bc 100%);
		border: 1px solid #DEDEDE;
		border-radius: 5px;
		overflow: hidden;
		transition: border-color .15s ease, box-shadow .15s ease;
	}
	.pms-rb__card:hover .pms-rb__media {
		border-color: #C5B17D;
		box-shadow: none;
	}
	.pms-rb__card.is-active .pms-rb__media {
		border-color: #2F7C7A;
		box-shadow: none;
	}
	.pms-rb__media img {
		width: 100%;
		height: 100%;
		object-fit: cover;
		display: block;
	}
	.pms-rb__media-placeholder {
		width: 100%;
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		color: #6b5d4e;
		font-family: Poppins, sans-serif;
		font-size: 18px;
		font-weight: 500;
		padding: 16px;
		text-align: center;
	}
	.pms-rb__body {
		display: flex;
		flex: 1 1 auto;
		flex-direction: column;
		gap: 14px;
		padding: 18px;
		background: transparent;
	}
	.pms-rb__product-title {
		margin: 0;
		font-family: Inter, sans-serif;
		font-size: 20px;
		font-weight: 700;
		line-height: 1.2;
		color: #2d241b;
	}
	.pms-rb__desc {
		margin: 0;
		font-size: 14px;
		line-height: 1.6;
		color: #62574c;
	}
	.pms-rb__stepper {
		display: inline-flex;
		align-items: center;
		height: 46px;
		border: 1px solid #DEDEDE;
		border-radius: 8px;
		overflow: hidden;
		width: fit-content;
		margin-top: auto;
		background: #fff;
	}
	.pms-rb__qty-btn {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 36px;
		height: 100%;
		border: 0;
		background: transparent;
		color: #555;
		cursor: pointer;
		padding: 0;
		flex-shrink: 0;
		transition: background .15s ease, color .15s ease;
	}
	.pms-rb__qty-btn:hover {
		background: #f5f5f5;
		color: #4A3728;
	}
	.pms-rb__qty-btn:focus,
	.pms-rb__qty-btn:active,
	.pms-rb__qty-btn:focus-visible,
	.pms-rb__qty-btn:hover:active {
		background: #f5f5f5 !important;
		color: #4A3728 !important;
		outline: none !important;
		box-shadow: none !important;
	}
	.pms-rb__qty-btn svg {
		display: block;
		pointer-events: none;
	}
	.pms-rb__qty-input {
		-webkit-appearance: none;
		appearance: none;
		width: 64px;
		height: 100%;
		border: 0;
		border-left: 1px solid #DEDEDE;
		border-right: 1px solid #DEDEDE;
		text-align: center;
		font: inherit;
		font-size: 16px;
		font-weight: 700;
		color: #2d241b;
		background: #fff;
	}
	.pms-rb__qty-input::-webkit-inner-spin-button,
	.pms-rb__qty-input::-webkit-outer-spin-button {
		-webkit-appearance: none;
	}
	.pms-rb__form {
		margin-top: 8px;
		padding: 28px;
		background: #fff;
		border: 1px solid #DEDEDE;
		border-radius: 10px;
	}
	.pms-rb__form-title {
		margin: 0 0 18px;
		font-size: 22px;
		color: #2d241b;
	}
	.pms-rb__fields {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: 18px 20px;
	}
	.pms-rb__field--full {
		grid-column: 1 / -1;
	}
	.pms-rb__label {
		display: block;
		margin-bottom: 8px;
		font-family: Poppins, sans-serif;
		font-size: 15px;
		font-weight: 300;
		color: #5d5144;
	}
	.pms-rb__input,
	.pms-rb__textarea {
		width: 100%;
		padding: 12px 14px;
		border: 1px solid #DEDEDE;
		border-radius: 8px;
		font: inherit;
		font-size: 15px;
		color: #2f2a24;
		background: #fff;
	}
	.pms-rb__textarea {
		min-height: 150px;
		resize: vertical;
	}
	.pms-rb__input:focus,
	.pms-rb__textarea:focus,
	.pms-rb__qty-input:focus {
		outline: none;
		border-color: #C5B17D;
		box-shadow: 0 0 0 3px rgba(197,177,125,0.18);
	}
	.pms-rb__footer {
		display: flex;
		justify-content: space-between;
		align-items: center;
		gap: 18px;
		flex-wrap: wrap;
		margin-top: 20px;
	}
	.pms-rb__disclaimer {
		margin: 0;
		font-size: 12px;
		line-height: 1.6;
		color: #8b8175;
	}
	.pms-rb__submit {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-height: 48px;
		padding: 0 30px;
		border: 1px solid #2F7C7A;
		border-radius: 8px;
		background: #2F7C7A;
		color: #fff;
		font: inherit;
		font-size: 15px;
		font-weight: 700;
		cursor: pointer;
		transition: background-color .15s ease, border-color .15s ease;
	}
	.pms-rb__submit:hover {
		background: #245F5D;
		border-color: #245F5D;
	}
	@media (max-width: 991px) {
		.pms-rb__grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}
	@media (max-width: 767px) {
		.pms-rb__headline {
			font-size: 28px;
		}
		.pms-rb__grid,
		.pms-rb__fields {
			grid-template-columns: 1fr;
		}
		.pms-rb__form {
			padding: 20px;
		}
		.pms-rb__footer {
			align-items: stretch;
		}
		.pms-rb__submit {
			width: 100%;
		}
	}
	</style>

	<div class="pms-rb">
		<?php if ( isset( $_GET['pms_room_builder_sent'] ) ) : ?>
			<div class="pms-rb__sent">
				<strong>Aanvraag verstuurd</strong>
				<p>Bedankt! We hebben je globale inrichtingseisen ontvangen en nemen snel contact met je op.</p>
			</div>
		<?php endif; ?>

		<h2 class="pms-rb__headline"><?php echo esc_html( $atts['title'] ); ?></h2>

		<form method="post" class="pms-rb__builder-form">
			<?php wp_nonce_field( 'pms_room_builder_quote_form', 'pms_nonce' ); ?>
			<input type="hidden" name="pms_action" value="pms_submit_room_builder_quote">

			<?php if ( empty( $assets ) ) : ?>
				<p>Er zijn nog geen onderdelen gevonden in de map <code>offerte-samenstellen</code>.</p>
			<?php else : ?>
				<div class="pms-rb__grid">
					<?php foreach ( $assets as $asset ) : ?>
						<div class="pms-rb__card" data-category-card>
							<div class="pms-rb__media">
								<img src="<?php echo esc_url( $asset['image_url'] ); ?>" alt="<?php echo esc_attr( $asset['label'] ); ?>">
							</div>
							<div class="pms-rb__body">
								<h3 class="pms-rb__product-title"><?php echo esc_html( $asset['label'] ); ?></h3>
								<input type="hidden" name="pms_room_builder_items[<?php echo esc_attr( $asset['key'] ); ?>][label]" value="<?php echo esc_attr( $asset['label'] ); ?>">
								<input type="hidden" name="pms_room_builder_items[<?php echo esc_attr( $asset['key'] ); ?>][term_id]" value="<?php echo esc_attr( $asset['term_id'] ); ?>">
								<div class="pms-rb__stepper" data-stepper>
									<button type="button" class="pms-rb__qty-btn pms-rb__qty-btn--minus" aria-label="Minder">
										<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/></svg>
									</button>
									<input type="number" class="pms-rb__qty-input" name="pms_room_builder_items[<?php echo esc_attr( $asset['key'] ); ?>][quantity]" value="0" min="0" inputmode="numeric" aria-label="<?php echo esc_attr( $asset['label'] ); ?> aantal">
									<button type="button" class="pms-rb__qty-btn pms-rb__qty-btn--plus" aria-label="Meer">
										<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
									</button>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="pms-rb__form">
				<h3 class="pms-rb__form-title">Jouw gegevens</h3>
				<div class="pms-rb__fields">
					<div class="pms-rb__field">
						<label class="pms-rb__label" for="pms-rb-firstname">Voornaam</label>
						<input class="pms-rb__input" type="text" id="pms-rb-firstname" name="pms_firstname" required placeholder="Voornaam">
					</div>
					<div class="pms-rb__field">
						<label class="pms-rb__label" for="pms-rb-lastname">Achternaam</label>
						<input class="pms-rb__input" type="text" id="pms-rb-lastname" name="pms_lastname" required placeholder="Achternaam">
					</div>
					<div class="pms-rb__field">
						<label class="pms-rb__label" for="pms-rb-email">E-mailadres</label>
						<input class="pms-rb__input" type="email" id="pms-rb-email" name="pms_email" required placeholder="naam@bedrijf.nl">
					</div>
					<div class="pms-rb__field">
						<label class="pms-rb__label" for="pms-rb-phone">Telefoonnummer</label>
						<input class="pms-rb__input" type="tel" id="pms-rb-phone" name="pms_phone" placeholder="06 12 34 56 78">
					</div>
					<div class="pms-rb__field pms-rb__field--full">
						<label class="pms-rb__label" for="pms-rb-message">Aanvullende toelichting</label>
						<textarea class="pms-rb__textarea" id="pms-rb-message" name="pms_message" placeholder="Bijvoorbeeld gewenste stijl, ruimte, levermoment of andere wensen..."></textarea>
					</div>
				</div>
				<div class="pms-rb__footer">
					<p class="pms-rb__disclaimer">Je gegevens worden uitsluitend gebruikt om je aanvraag te beoordelen en contact met je op te nemen.</p>
					<button type="submit" class="pms-rb__submit">Aanvraag samenstellen</button>
				</div>
			</div>
		</form>
	</div>

	<script>
	(function () {
		document.querySelectorAll('.pms-rb [data-category-card]').forEach(function (card) {
			var stepper = card.querySelector('[data-stepper]');
			if (!stepper) return;

			var input = stepper.querySelector('.pms-rb__qty-input');
			var minus = stepper.querySelector('.pms-rb__qty-btn--minus');
			var plus = stepper.querySelector('.pms-rb__qty-btn--plus');

			if (!input || !minus || !plus) return;

			var syncCardState = function () {
				var value = parseInt(input.value, 10);
				if (isNaN(value) || value < 0) value = 0;
				input.value = value;
				card.classList.toggle('is-active', value > 0);
			};

			card.addEventListener('click', function (event) {
				if (event.target.closest('.pms-rb__stepper')) return;

				var value = parseInt(input.value, 10);
				if (isNaN(value) || value < 1) {
					input.value = 1;
				}

				syncCardState();
				input.focus();
				input.select();
			});

			minus.addEventListener('click', function () {
				var value = parseInt(input.value, 10);
				if (isNaN(value)) value = 0;
				input.value = Math.max(0, value - 1);
				syncCardState();
			});

			plus.addEventListener('click', function () {
				var value = parseInt(input.value, 10);
				if (isNaN(value)) value = 0;
				input.value = value + 1;
				syncCardState();
			});

			input.addEventListener('input', syncCardState);
			input.addEventListener('change', syncCardState);

			syncCardState();
		});
	})();
	</script>
	<?php

	return ob_get_clean();
}
