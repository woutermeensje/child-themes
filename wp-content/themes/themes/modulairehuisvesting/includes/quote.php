<?php
/**
 * Modulairehuisvesting – Offerte tool
 * Slaat items op in de WooCommerce sessie en rendert de offertepagina via [mh_quote_page].
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'mh_register_quote_request_post_type' );
function mh_register_quote_request_post_type() {
	register_post_type(
		'mh_quote_request',
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

function mh_quote_get_supported_item_types(): array {
	return array( 'product', 'mh_unit' );
}

function mh_quote_resolve_item_type( int $item_id, array $item = array() ): string {
	$stored_type = isset( $item['item_type'] ) ? sanitize_key( (string) $item['item_type'] ) : '';
	if ( in_array( $stored_type, mh_quote_get_supported_item_types(), true ) ) {
		return $stored_type;
	}

	$post_type = get_post_type( $item_id );
	if ( is_string( $post_type ) && in_array( $post_type, mh_quote_get_supported_item_types(), true ) ) {
		return $post_type;
	}

	if ( function_exists( 'wc_get_product' ) && wc_get_product( $item_id ) ) {
		return 'product';
	}

	return '';
}

function mh_quote_get_item_snapshot_data( int $item_id, array $item = array() ): ?array {
	$item_type = mh_quote_resolve_item_type( $item_id, $item );
	$quantity  = isset( $item['quantity'] ) ? max( 1, (int) $item['quantity'] ) : 1;

	if ( 'product' === $item_type && function_exists( 'wc_get_product' ) ) {
		$product = wc_get_product( $item_id );
		if ( ! $product ) {
			return null;
		}

			return array(
				'product_id' => $item_id,
				'item_type'  => 'product',
				'name'       => $product->get_name(),
				'quantity'   => $quantity,
				'permalink'  => get_permalink( $item_id ),
				'sku'        => $product->get_sku(),
			);
		}

	if ( 'mh_unit' === $item_type ) {
		$post = get_post( $item_id );
		if ( ! $post instanceof WP_Post ) {
			return null;
		}

			return array(
				'product_id' => $item_id,
				'item_type'  => 'mh_unit',
				'name'       => get_the_title( $item_id ),
				'quantity'   => $quantity,
				'permalink'  => get_permalink( $item_id ),
				'sku'        => 'Unit',
			);
		}

	return null;
}

function mh_quote_get_item_display_data( int $item_id, array $item = array() ): ?array {
	$snapshot = mh_quote_get_item_snapshot_data( $item_id, $item );
	if ( ! $snapshot ) {
		return null;
	}

	$item_type = $snapshot['item_type'];
	$thumb_id  = 0;
	$meta      = '';

	if ( 'product' === $item_type && function_exists( 'wc_get_product' ) ) {
		$product = wc_get_product( $item_id );
		if ( ! $product ) {
			return null;
		}

		$thumb_id = (int) $product->get_image_id();
		$cats     = get_the_terms( $item_id, 'product_cat' );
		if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
			$meta = (string) $cats[0]->name;
		}
	} elseif ( 'mh_unit' === $item_type ) {
		$thumb_id   = (int) get_post_thumbnail_id( $item_id );
		$meta_parts = array();
		$aanbod     = get_the_terms( $item_id, 'mh_unit_aanbod' );
		$types      = get_the_terms( $item_id, 'mh_unit_type' );
		$condities  = get_the_terms( $item_id, 'mh_unit_conditie' );

		if ( ! empty( $aanbod ) && ! is_wp_error( $aanbod ) ) {
			$meta_parts[] = $aanbod[0]->name;
		}
		if ( ! empty( $types ) && ! is_wp_error( $types ) ) {
			$meta_parts[] = $types[0]->name;
		}
		if ( ! empty( $condities ) && ! is_wp_error( $condities ) ) {
			$meta_parts[] = $condities[0]->name;
		}

		$meta = implode( ' • ', array_filter( array_unique( $meta_parts ) ) );
	}

	$snapshot['thumb_id'] = $thumb_id;
	$snapshot['meta']     = $meta;

	return $snapshot;
}

function mh_quote_build_items_snapshot( array $items ): array {
	$snapshot = array();

	foreach ( $items as $pid => $item ) {
		$item_snapshot = mh_quote_get_item_snapshot_data( (int) $pid, (array) $item );
		if ( $item_snapshot ) {
			$snapshot[] = $item_snapshot;
		}
	}

	return $snapshot;
}

function mh_quote_build_category_snapshot( array $selected_categories ): array {
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

function mh_quote_build_room_builder_snapshot( array $selected_items ): array {
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

function mh_quote_normalize_asset_key( string $value ): string {
	$value = remove_accents( wp_strip_all_tags( $value ) );
	$value = strtolower( $value );
	$value = preg_replace( '/[^a-z0-9]+/', '', $value );

	return is_string( $value ) ? $value : '';
}

function mh_quote_candidate_asset_keys( WP_Term $term ): array {
	$keys = array(
		mh_quote_normalize_asset_key( $term->slug ),
		mh_quote_normalize_asset_key( $term->name ),
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

function mh_quote_find_matching_term_for_asset( string $asset_name ): ?WP_Term {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return null;
	}

	$asset_key = mh_quote_normalize_asset_key( $asset_name );

	foreach ( $terms as $term ) {
		foreach ( mh_quote_candidate_asset_keys( $term ) as $key ) {
			if ( $key === $asset_key ) {
				return $term;
			}
		}
	}

	return null;
}

function mh_quote_get_room_builder_asset_paths(): array {
	$candidates = array(
		'offerte/',
		'offerte-samenstellen/',
	);

	foreach ( $candidates as $relative_path ) {
		$asset_dir = trailingslashit( get_stylesheet_directory() ) . $relative_path;
		if ( is_dir( $asset_dir ) ) {
			return array(
				'dir' => $asset_dir,
				'url' => trailingslashit( get_stylesheet_directory_uri() ) . $relative_path,
			);
		}
	}

	return array(
		'dir' => trailingslashit( get_stylesheet_directory() ) . 'offerte/',
		'url' => trailingslashit( get_stylesheet_directory_uri() ) . 'offerte/',
	);
}

function mh_quote_get_room_builder_assets(): array {
	$asset_paths = mh_quote_get_room_builder_asset_paths();
	$asset_dir   = $asset_paths['dir'];
	$asset_url   = $asset_paths['url'];
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
		$term     = mh_quote_find_matching_term_for_asset( $basename );

		$assets[] = array(
			'key'         => mh_quote_normalize_asset_key( $basename ),
			'label'       => trim( preg_replace( '/\s+/', ' ', str_replace( array( '-', '_' ), ' ', $basename ) ) ),
			'image_url'   => $asset_url . basename( $file ),
			'term_id'     => $term instanceof WP_Term ? (int) $term->term_id : 0,
			'description' => $term instanceof WP_Term ? trim( wp_strip_all_tags( term_description( $term->term_id, 'product_cat' ) ) ) : '',
		);
	}

	return $assets;
}

function mh_quote_get_room_builder_image_url( WP_Term $term ): string {
	$asset_paths = mh_quote_get_room_builder_asset_paths();
	$asset_dir   = $asset_paths['dir'];
	$asset_url   = $asset_paths['url'];
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
			$lookup[ mh_quote_normalize_asset_key( $base_name ) ] = basename( $file );
		}

		foreach ( mh_quote_candidate_asset_keys( $term ) as $key ) {
			if ( isset( $lookup[ $key ] ) ) {
				return $asset_url . $lookup[ $key ];
			}
		}
	}

	$term_image_id = (int) get_term_meta( $term->term_id, 'mh_hero_image_id', true );
	$term_image_id = $term_image_id ?: (int) get_term_meta( $term->term_id, 'thumbnail_id', true );

	return $term_image_id ? (string) wp_get_attachment_image_url( $term_image_id, 'large' ) : '';
}

function mh_quote_store_request( array $request_data ): int {
	$created_at = current_time( 'mysql' );
	$name       = trim( (string) ( $request_data['name'] ?? '' ) );
	$email      = (string) ( $request_data['email'] ?? '' );
	$phone      = (string) ( $request_data['phone'] ?? '' );
	$message    = (string) ( $request_data['message'] ?? '' );
	$items      = isset( $request_data['items'] ) && is_array( $request_data['items'] ) ? $request_data['items'] : array();
	$title_name = $name ?: 'Onbekende aanvrager';

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'mh_quote_request',
			'post_status' => 'publish',
			'post_title'  => sprintf( '%s - %s', $title_name, wp_date( 'd-m-Y H:i', strtotime( $created_at ) ) ),
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		return 0;
	}

	update_post_meta( $post_id, '_mh_quote_name', $name );
	update_post_meta( $post_id, '_mh_quote_firstname', (string) ( $request_data['firstname'] ?? '' ) );
	update_post_meta( $post_id, '_mh_quote_lastname', (string) ( $request_data['lastname'] ?? '' ) );
	update_post_meta( $post_id, '_mh_quote_email', $email );
	update_post_meta( $post_id, '_mh_quote_phone', $phone );
	update_post_meta( $post_id, '_mh_quote_message', $message );
	update_post_meta( $post_id, '_mh_quote_items', $items );
	update_post_meta( $post_id, '_mh_quote_created_at', $created_at );
	update_post_meta( $post_id, '_mh_quote_source', sanitize_key( (string) ( $request_data['source'] ?? 'quote' ) ) );

	return (int) $post_id;
}

function mh_quote_build_email_html( array $request_data ): string {
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
			$item_rows .= '<a href="' . $item_link . '" style="color:#25476B;text-decoration:none;font-weight:600;">Bekijk product</a>';
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
		<body style="margin:0;padding:0;background:#f7fbf7;font-family:Inter,Arial,sans-serif;color:#1f2a24;">
			<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f7fbf7;padding:32px 16px;">
				<tr>
					<td align="center">
						<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:760px;background:#ffffff;border:1px solid #BFE396;border-radius:12px;overflow:hidden;">
							<tr>
								<td style="padding:28px 32px;background:#25476B;">
									<div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#CCDA91;font-weight:700;">Modulairehuisvesting</div>
									<h1 style="margin:10px 0 0;font-size:28px;line-height:1.2;color:#ffffff;">Nieuwe offerte aanvraag</h1>
								</td>
							</tr>
							<tr>
								<td style="padding:28px 32px;">
									<p style="margin:0 0 24px;font-size:15px;line-height:1.7;color:#39749B;">Er is een nieuwe offerteaanvraag binnengekomen via de website.</p>

									<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 24px;border-collapse:separate;border-spacing:0;">
										<tr>
											<td colspan="2" style="padding:0 0 10px;font-size:13px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#39749B;">Klantgegevens</td>
										</tr>
										<tr>
											<td style="padding:12px 14px;background:#F1F8EE;border:1px solid #BFE396;font-size:13px;font-weight:700;color:#39749B;width:180px;">Naam</td>
											<td style="padding:12px 14px;background:#F1F8EE;border:1px solid #BFE396;border-left:none;font-size:14px;color:#1f2a24;">' . esc_html( $name ?: '-' ) . '</td>
										</tr>
										<tr>
											<td style="padding:12px 14px;background:#F1F8EE;border:1px solid #BFE396;border-top:none;font-size:13px;font-weight:700;color:#39749B;">E-mail</td>
											<td style="padding:12px 14px;background:#F1F8EE;border:1px solid #BFE396;border-left:none;border-top:none;font-size:14px;color:#1f2a24;"><a href="mailto:' . esc_attr( $email ) . '" style="color:#25476B;text-decoration:none;">' . esc_html( $email ?: '-' ) . '</a></td>
										</tr>
										<tr>
											<td style="padding:12px 14px;background:#F1F8EE;border:1px solid #BFE396;border-top:none;font-size:13px;font-weight:700;color:#39749B;">Telefoon</td>
											<td style="padding:12px 14px;background:#F1F8EE;border:1px solid #BFE396;border-left:none;border-top:none;font-size:14px;color:#1f2a24;">' . esc_html( $phone ?: '-' ) . '</td>
										</tr>
									</table>

									<div style="margin:0 0 24px;">
										<div style="margin:0 0 10px;font-size:13px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#39749B;">Opmerkingen of vragen</div>
										<div style="padding:16px 18px;background:#F1F8EE;border:1px solid #BFE396;border-radius:10px;font-size:14px;line-height:1.7;color:#1f2a24;">' . $message_html . '</div>
									</div>

									<div style="margin:0;">
										<div style="margin:0 0 10px;font-size:13px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#39749B;">Aangevraagde items</div>
										<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:separate;border-spacing:0;border:1px solid #BFE396;border-radius:10px;overflow:hidden;">
											<thead>
												<tr>
													<th align="left" style="padding:14px 16px;background:#39749B;color:#ffffff;font-size:12px;letter-spacing:.05em;text-transform:uppercase;">Product</th>
													<th align="center" style="padding:14px 16px;background:#39749B;color:#ffffff;font-size:12px;letter-spacing:.05em;text-transform:uppercase;">Aantal</th>
													<th align="right" style="padding:14px 16px;background:#39749B;color:#ffffff;font-size:12px;letter-spacing:.05em;text-transform:uppercase;">Link</th>
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

add_filter( 'manage_mh_quote_request_posts_columns', 'mh_quote_request_admin_columns' );
function mh_quote_request_admin_columns( $columns ) {
	return array(
		'cb'             => $columns['cb'] ?? '<input type="checkbox" />',
		'title'          => 'Aanvraag',
		'mh_customer'   => 'Klant',
		'mh_email'      => 'E-mail',
		'mh_phone'      => 'Telefoon',
		'mh_item_count' => 'Producten',
		'date'           => 'Datum',
	);
}

add_action( 'manage_mh_quote_request_posts_custom_column', 'mh_quote_request_admin_column_content', 10, 2 );
function mh_quote_request_admin_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'mh_customer':
			echo esc_html( get_post_meta( $post_id, '_mh_quote_name', true ) ?: '-' );
			break;
		case 'mh_email':
			$email = get_post_meta( $post_id, '_mh_quote_email', true );
			if ( $email ) {
				echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
			} else {
				echo '-';
			}
			break;
		case 'mh_phone':
			echo esc_html( get_post_meta( $post_id, '_mh_quote_phone', true ) ?: '-' );
			break;
		case 'mh_item_count':
			$items = get_post_meta( $post_id, '_mh_quote_items', true );
			echo esc_html( is_array( $items ) ? count( $items ) : 0 );
			break;
	}
}

add_action( 'add_meta_boxes', 'mh_quote_request_meta_boxes' );
function mh_quote_request_meta_boxes() {
	add_meta_box(
		'mh-quote-request-details',
		'Offerte aanvraag details',
		'mh_render_quote_request_meta_box',
		'mh_quote_request',
		'normal',
		'high'
	);
}

function mh_render_quote_request_meta_box( WP_Post $post ) {
	$name       = get_post_meta( $post->ID, '_mh_quote_name', true );
	$email      = get_post_meta( $post->ID, '_mh_quote_email', true );
	$phone      = get_post_meta( $post->ID, '_mh_quote_phone', true );
	$message    = get_post_meta( $post->ID, '_mh_quote_message', true );
	$created_at = get_post_meta( $post->ID, '_mh_quote_created_at', true );
	$items      = get_post_meta( $post->ID, '_mh_quote_items', true );

	if ( ! is_array( $items ) ) {
		$items = array();
	}
	?>
	<style>
		.mh-quote-admin-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 16px 24px;
			margin-bottom: 24px;
		}
		.mh-quote-admin-field strong {
			display: block;
			margin-bottom: 4px;
		}
		.mh-quote-admin-message {
			margin: 24px 0;
			padding: 16px;
			background: #fbfaf8;
			border: 1px solid #dedede;
			border-radius: 8px;
			white-space: pre-wrap;
		}
		.mh-quote-admin-items table {
			width: 100%;
			border-collapse: collapse;
		}
		.mh-quote-admin-items th,
		.mh-quote-admin-items td {
			padding: 10px 12px;
			border: 1px solid #e5e5e5;
			text-align: left;
			vertical-align: top;
		}
		@media (max-width: 782px) {
			.mh-quote-admin-grid {
				grid-template-columns: 1fr;
			}
		}
	</style>
	<div class="mh-quote-admin-grid">
		<div class="mh-quote-admin-field">
			<strong>Klant</strong>
			<span><?php echo esc_html( $name ?: '-' ); ?></span>
		</div>
		<div class="mh-quote-admin-field">
			<strong>Ingekomen op</strong>
			<span><?php echo esc_html( $created_at ? wp_date( 'd-m-Y H:i', strtotime( $created_at ) ) : '-' ); ?></span>
		</div>
		<div class="mh-quote-admin-field">
			<strong>E-mail</strong>
			<span>
				<?php if ( $email ) : ?>
					<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
				<?php else : ?>
					-
				<?php endif; ?>
			</span>
		</div>
		<div class="mh-quote-admin-field">
			<strong>Telefoon</strong>
			<span><?php echo esc_html( $phone ?: '-' ); ?></span>
		</div>
	</div>

	<div class="mh-quote-admin-field">
		<strong>Opmerkingen of vragen</strong>
		<div class="mh-quote-admin-message"><?php echo esc_html( $message ?: 'Geen extra opmerkingen opgegeven.' ); ?></div>
	</div>

	<div class="mh-quote-admin-items">
		<strong>Aangevraagde items</strong>
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

function mh_quote_log( string $event, array $context = array() ): void {
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

		$items = WC()->session->get( 'mh_quote_items' );
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

	$stored_logs   = get_option( 'mh_quote_debug_entries', array() );
	$stored_logs   = is_array( $stored_logs ) ? $stored_logs : array();
	$stored_logs[] = $payload;

	if ( count( $stored_logs ) > 200 ) {
		$stored_logs = array_slice( $stored_logs, -200 );
	}

	update_option( 'mh_quote_debug_entries', $stored_logs, false );
	error_log( 'PMS_QUOTE_DEBUG ' . wp_json_encode( $payload ) );
}

add_action( 'admin_menu', 'mh_quote_register_debug_page' );
function mh_quote_register_debug_page(): void {
	add_submenu_page(
		'edit.php?post_type=mh_quote_request',
		'Offerte debug log',
		'Debug log',
		'manage_options',
		'mh-quote-debug-log',
		'mh_quote_render_debug_page'
	);
}

function mh_quote_get_debug_log_path(): string {
	return trailingslashit( WP_CONTENT_DIR ) . 'debug.log';
}

function mh_quote_get_debug_log_entries( int $limit = 200 ): array {
	$path = mh_quote_get_debug_log_path();

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

function mh_quote_render_debug_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Je hebt geen rechten om deze pagina te bekijken.', 'modulairehuisvesting' ) );
	}

	if ( isset( $_GET['mh_clear_quote_debug'] ) && '1' === $_GET['mh_clear_quote_debug'] && check_admin_referer( 'mh_clear_quote_debug' ) ) {
		delete_option( 'mh_quote_debug_entries' );
		echo '<div class="notice notice-success"><p>De offerte debug log is geleegd.</p></div>';
	}

	$entries = mh_quote_get_debug_log_entries( 150 );
	$db_logs  = get_option( 'mh_quote_debug_entries', array() );
	$db_logs  = is_array( $db_logs ) ? array_reverse( $db_logs ) : array();
	?>
	<div class="wrap">
		<h1>Offerte debug log</h1>
		<p>Hier zie je de meest recente offerte-debugregels. Eerst uit de interne database-log, en daarna eventueel uit <code>wp-content/debug.log</code>.</p>

		<p>
			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'mh_clear_quote_debug', '1' ), 'mh_clear_quote_debug' ) ); ?>" class="button button-secondary">Debug log legen</a>
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

function mh_quote_ensure_session() {
	if ( ! function_exists( 'WC' ) || ! WC() ) {
		mh_quote_log( 'ensure_session_wc_missing' );
		return false;
	}

	if ( null === WC()->session && method_exists( WC(), 'initialize_session' ) ) {
		WC()->initialize_session();
	}

	if ( ! isset( WC()->session ) || ! WC()->session ) {
		mh_quote_log( 'ensure_session_failed' );
		return false;
	}

	if ( method_exists( WC()->session, 'set_customer_session_cookie' ) && ! WC()->session->has_session() ) {
		WC()->session->set_customer_session_cookie( true );
		mh_quote_log( 'session_cookie_initialized' );
	}

	mh_quote_log( 'ensure_session_ready' );
	return true;
}

function mh_quote_ready() {
	return mh_quote_ensure_session();
}

function mh_quote_get_items() {
	if ( ! mh_quote_ready() ) {
		return array();
	}
	$items = WC()->session->get( 'mh_quote_items' );
	return is_array( $items ) ? $items : array();
}

function mh_quote_set_items( $items ) {
	if ( ! mh_quote_ready() ) {
		return;
	}
	WC()->session->set( 'mh_quote_items', $items );
	if ( method_exists( WC()->session, 'save_data' ) ) {
		WC()->session->save_data();
	}
}

function mh_quote_has_product( $product_id ) {
	$items = mh_quote_get_items();
	return isset( $items[ (int) $product_id ] );
}

function mh_quote_get_count() {
	$items = mh_quote_get_items();
	$count = 0;

	foreach ( $items as $item ) {
		$count += isset( $item['quantity'] ) ? max( 1, (int) $item['quantity'] ) : 1;
	}

	return $count;
}

function mh_quote_add_item( $product_id, $quantity = 1, $variation_id = 0, $variation_data = array(), $item_type = '' ) {
	$items      = mh_quote_get_items();
	$product_id = (int) $product_id;
	$item_type  = sanitize_key( (string) $item_type );
	if ( isset( $items[ $product_id ] ) ) {
		$items[ $product_id ]['quantity'] = max( 1, (int) $items[ $product_id ]['quantity'] + (int) $quantity );
		if ( $item_type ) {
			$items[ $product_id ]['item_type'] = $item_type;
		}
	} else {
		$items[ $product_id ] = array(
			'quantity'       => max( 1, (int) $quantity ),
			'variation_id'   => (int) $variation_id,
			'variation_data' => (array) $variation_data,
			'item_type'      => $item_type,
		);
	}
	mh_quote_set_items( $items );
	mh_quote_log(
		'item_added',
		array(
			'product_id'   => $product_id,
				'quantity'     => (int) $quantity,
				'variation_id' => (int) $variation_id,
				'item_type'    => $item_type,
				'items_keys'   => array_map( 'intval', array_keys( $items ) ),
			)
		);
}

function mh_quote_get_page_url() {
	return home_url( '/mijn-offerte/' );
}

function mh_quote_is_page_request(): bool {
	if ( is_admin() ) {
		return false;
	}

	$quote_page_path = wp_parse_url( mh_quote_get_page_url(), PHP_URL_PATH );
	$current_path    = wp_parse_url( home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) ), PHP_URL_PATH );

	if ( is_string( $quote_page_path ) && is_string( $current_path ) ) {
		$quote_page_path = untrailingslashit( $quote_page_path );
		$current_path    = untrailingslashit( $current_path );

		if ( $quote_page_path === $current_path ) {
			return true;
		}
	}

	return function_exists( 'is_page' ) && ( is_page( 'mijn-offerte' ) || is_page( 'offerte' ) );
}

function mh_quote_is_dynamic_request(): bool {
	if ( isset( $_POST['mh_action'] ) ) {
		return true;
	}

	if ( isset( $_GET['mh_added'] ) || isset( $_GET['mh_sent'] ) ) {
		return true;
	}

	return mh_quote_is_page_request();
}

add_action( 'template_redirect', 'mh_quote_disable_cache_for_dynamic_requests', 0 );
function mh_quote_disable_cache_for_dynamic_requests() {
	if ( ! mh_quote_is_dynamic_request() ) {
		return;
	}

	if ( function_exists( 'wc_nocache_headers' ) ) {
		wc_nocache_headers();
	} else {
		nocache_headers();
	}

	// Force the WooCommerce session to exist before the page is rendered,
	// otherwise a cached/offloaded response can make the quote appear empty.
	mh_quote_ensure_session();
}

// ── Action handlers (template_redirect) ────────────────────────────────────

add_action( 'template_redirect', 'mh_quote_handle_add' );
function mh_quote_handle_add() {
	if ( ! isset( $_POST['mh_action'] ) || $_POST['mh_action'] !== 'add_to_quote' ) {
		return;
	}
	if ( ! isset( $_POST['mh_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mh_nonce'] ) ), 'mh_add_to_quote' ) ) {
		return;
	}
	if ( ! function_exists( 'WC' ) ) {
		return;
	}
	if ( ! mh_quote_ensure_session() ) {
		return;
	}
	$product_id   = isset( $_POST['product_id'] ) ? (int) $_POST['product_id'] : 0;
	$quantity     = isset( $_POST['quantity'] ) ? max( 1, (int) $_POST['quantity'] ) : 1;
	$variation_id = isset( $_POST['variation_id'] ) ? (int) $_POST['variation_id'] : 0;
	$item_type    = isset( $_POST['item_type'] ) ? sanitize_key( wp_unslash( $_POST['item_type'] ) ) : '';
	$item_type    = mh_quote_resolve_item_type( $product_id, array( 'item_type' => $item_type ) );
	if ( $product_id < 1 || '' === $item_type ) {
		mh_quote_log( 'add_rejected_invalid_product' );
		return;
	}
	mh_quote_add_item( $product_id, $quantity, $variation_id, array(), $item_type );
	$redirect = add_query_arg( 'mh_added', $product_id, wp_get_referer() ?: get_permalink( $product_id ) );
	mh_quote_log(
		'add_redirect',
		array(
			'product_id' => $product_id,
			'item_type'  => $item_type,
			'redirect'   => $redirect,
		)
	);
	wp_safe_redirect( $redirect );
	exit;
}

add_action( 'template_redirect', 'mh_quote_handle_update_or_remove' );
function mh_quote_handle_update_or_remove() {
	if ( ! isset( $_POST['mh_action'] ) ) {
		return;
	}
	$action = sanitize_text_field( wp_unslash( $_POST['mh_action'] ) );
	if ( ! in_array( $action, array( 'mh_update_quote', 'mh_remove_from_quote' ), true ) ) {
		return;
	}
	if ( ! isset( $_POST['mh_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mh_nonce'] ) ), 'mh_quote_page' ) ) {
		return;
	}
	$items = mh_quote_get_items();
	if ( $action === 'mh_remove_from_quote' ) {
		$product_id = isset( $_POST['product_id'] ) ? (int) $_POST['product_id'] : 0;
		unset( $items[ $product_id ] );
		mh_quote_set_items( $items );
	} elseif ( $action === 'mh_update_quote' && isset( $_POST['quantities'] ) && is_array( $_POST['quantities'] ) ) {
		foreach ( $_POST['quantities'] as $pid => $qty ) {
			$pid = (int) $pid;
			$qty = max( 1, (int) $qty );
			if ( isset( $items[ $pid ] ) ) {
				$items[ $pid ]['quantity'] = $qty;
			}
		}
		mh_quote_set_items( $items );
	}
	wp_safe_redirect( mh_quote_get_page_url() );
	exit;
}

add_action( 'template_redirect', 'mh_quote_handle_submit' );
function mh_quote_handle_submit() {
	if ( ! isset( $_POST['mh_action'] ) || $_POST['mh_action'] !== 'mh_submit_quote' ) {
		return;
	}
	if ( ! isset( $_POST['mh_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mh_nonce'] ) ), 'mh_quote_page' ) ) {
		return;
	}
	$firstname = sanitize_text_field( wp_unslash( $_POST['mh_firstname'] ?? '' ) );
	$lastname  = sanitize_text_field( wp_unslash( $_POST['mh_lastname'] ?? '' ) );
	$name      = trim( $firstname . ' ' . $lastname );
	$email     = sanitize_email( wp_unslash( $_POST['mh_email'] ?? '' ) );
	$phone     = sanitize_text_field( wp_unslash( $_POST['mh_phone'] ?? '' ) );
	$message   = sanitize_textarea_field( wp_unslash( $_POST['mh_message'] ?? '' ) );
	$items     = mh_quote_get_items();
	$item_data = mh_quote_build_items_snapshot( $items );

	$body  = "Offerte aanvraag van {$name}\n\n";
	$body .= "E-mail: {$email}\n";
	if ( $phone ) {
		$body .= "Telefoon: {$phone}\n";
	}
	if ( $message ) {
		$body .= "\nBericht:\n{$message}\n";
	}
		$body .= "\n--- Gevraagde items ---\n\n";
	foreach ( $item_data as $item ) {
		$body .= '- ' . $item['name'] . ' (aantal: ' . $item['quantity'] . ")\n";
		$body .= '  ' . $item['permalink'] . "\n\n";
	}

	$html_body = mh_quote_build_email_html(
		array(
			'name'    => $name,
			'email'   => $email,
			'phone'   => $phone,
			'message' => $message,
			'items'   => $item_data,
		)
	);

	mh_quote_store_request(
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

	$to      = get_option( 'admin_email' );
	$subject = 'Nieuwe offerte aanvraag – ' . $name;
	$headers = array(
		'From: Modulairehuisvesting <' . get_option( 'admin_email' ) . '>',
		'Reply-To: ' . $email,
		'Content-Type: text/html; charset=UTF-8',
	);
	wp_mail( $to, $subject, $html_body, $headers );

	mh_quote_set_items( array() );

	wp_safe_redirect( add_query_arg( 'mh_sent', '1', mh_quote_get_page_url() ) );
	exit;
}

add_action( 'template_redirect', 'mh_quote_handle_standalone_form_submit' );
function mh_quote_handle_standalone_form_submit() {
	if ( ! isset( $_POST['mh_action'] ) || 'mh_submit_standalone_quote' !== $_POST['mh_action'] ) {
		return;
	}

	if ( ! isset( $_POST['mh_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mh_nonce'] ) ), 'mh_standalone_quote_form' ) ) {
		return;
	}

	$firstname = sanitize_text_field( wp_unslash( $_POST['mh_firstname'] ?? '' ) );
	$lastname  = sanitize_text_field( wp_unslash( $_POST['mh_lastname'] ?? '' ) );
	$name      = trim( $firstname . ' ' . $lastname );
	$email     = sanitize_email( wp_unslash( $_POST['mh_email'] ?? '' ) );
	$phone     = sanitize_text_field( wp_unslash( $_POST['mh_phone'] ?? '' ) );
	$message   = sanitize_textarea_field( wp_unslash( $_POST['mh_message'] ?? '' ) );

	mh_quote_store_request(
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

	$html_body = mh_quote_build_email_html(
		array(
			'name'    => $name,
			'email'   => $email,
			'phone'   => $phone,
			'message' => $message,
			'items'   => array(),
		)
	);

	$to      = get_option( 'admin_email' );
	$subject = 'Nieuwe offerte aanvraag – ' . $name;
	$headers = array(
		'From: Modulairehuisvesting <' . get_option( 'admin_email' ) . '>',
		'Reply-To: ' . $email,
		'Content-Type: text/html; charset=UTF-8',
	);

	wp_mail( $to, $subject, $html_body, $headers );

	$redirect = wp_get_referer() ?: home_url( '/' );
	$redirect = remove_query_arg( 'mh_quote_form_sent', $redirect );
	$redirect = add_query_arg( 'mh_quote_form_sent', '1', $redirect );
	wp_safe_redirect( $redirect );
	exit;
}

add_action( 'template_redirect', 'mh_quote_handle_room_builder_submit' );
function mh_quote_handle_room_builder_submit() {
	if ( ! isset( $_POST['mh_action'] ) || 'mh_submit_room_builder_quote' !== $_POST['mh_action'] ) {
		return;
	}

	if ( ! isset( $_POST['mh_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mh_nonce'] ) ), 'mh_room_builder_quote_form' ) ) {
		return;
	}

	$firstname = sanitize_text_field( wp_unslash( $_POST['mh_firstname'] ?? '' ) );
	$lastname  = sanitize_text_field( wp_unslash( $_POST['mh_lastname'] ?? '' ) );
	$name      = trim( $firstname . ' ' . $lastname );
	$email     = sanitize_email( wp_unslash( $_POST['mh_email'] ?? '' ) );
	$phone     = sanitize_text_field( wp_unslash( $_POST['mh_phone'] ?? '' ) );
	$message   = sanitize_textarea_field( wp_unslash( $_POST['mh_message'] ?? '' ) );
	$submitted_items = isset( $_POST['mh_room_builder_items'] ) && is_array( $_POST['mh_room_builder_items'] )
		? wp_unslash( $_POST['mh_room_builder_items'] )
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

	$item_data = mh_quote_build_room_builder_snapshot( $selected_items );

	mh_quote_store_request(
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

	$html_body = mh_quote_build_email_html(
		array(
			'name'    => $name,
			'email'   => $email,
			'phone'   => $phone,
			'message' => $message,
			'items'   => $item_data,
		)
	);

	$to      = get_option( 'admin_email' );
	$subject = 'Nieuwe inrichting aanvraag – ' . $name;
	$headers = array(
		'From: Modulairehuisvesting <' . get_option( 'admin_email' ) . '>',
		'Reply-To: ' . $email,
		'Content-Type: text/html; charset=UTF-8',
	);

	wp_mail( $to, $subject, $html_body, $headers );

	$redirect = wp_get_referer() ?: home_url( '/inrichting-samenstellen/' );
	$redirect = remove_query_arg( 'mh_room_builder_sent', $redirect );
	$redirect = add_query_arg( 'mh_room_builder_sent', '1', $redirect );
	wp_safe_redirect( $redirect );
	exit;
}

// ── Shortcode [mh_quote_page] ──────────────────────────────────────────────

add_shortcode( 'mh_quote_page', 'mh_quote_render_page' );
function mh_quote_render_page() {
	$items = function_exists( 'WC' ) ? mh_quote_get_items() : array();
	mh_quote_log(
		'render_page',
		array(
			'items_keys' => array_map( 'intval', array_keys( $items ) ),
		)
	);
	$nonce = wp_create_nonce( 'mh_quote_page' );

	// SVG iconen
	$icon_minus = '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/></svg>';
	$icon_plus  = '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>';
	$icon_trash = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>';

	ob_start();
	?>
	<style>
	/* ── Reset & base ── */
	.mh-qp {
		font-family: Inter, sans-serif;
		color: #333;
		max-width: 860px;
		margin: 0 auto;
	}
	.mh-qp *, .mh-qp *::before, .mh-qp *::after {
		box-sizing: border-box;
	}

	/* ── Bevestigingsbanner ── */
	.mh-qp__sent {
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
	.mh-qp__sent-icon {
		flex-shrink: 0;
		color: #16a34a;
		margin-top: 1px;
	}
	.mh-qp__sent-title {
		font-size: 15px;
		font-weight: 700;
		color: #15803d;
		margin: 0 0 2px;
	}
	.mh-qp__sent-sub {
		font-size: 13px;
		color: #166534;
		margin: 0;
	}

	/* ── Lege staat ── */
	.mh-qp__empty {
		text-align: center;
		padding: 64px 24px;
		color: #888;
	}
	.mh-qp__empty-icon {
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
	.mh-qp__empty h3 {
		font-size: 18px;
		font-weight: 700;
		color: #444;
		margin: 0 0 6px;
	}
	.mh-qp__empty p { margin: 0 0 20px; font-size: 14px; }
	.mh-qp__empty a {
		display: inline-flex;
		align-items: center;
		height: 44px;
		padding: 0 24px;
		background: var(--color-primary, #25476B);
		color: #fff;
		border-radius: 8px;
		font-weight: 700;
		font-size: 14px;
		text-decoration: none;
		transition: background 0.15s;
	}
	.mh-qp__empty a:hover { background: var(--color-primary-hover, #325F8D); }

	/* ── Sectie-kaarten ── */
	.mh-qp__card {
		background: #fff;
		border: 1px solid var(--color-border, #dedede);
		border-radius: 5px;
		overflow: hidden;
		margin-bottom: 24px;
		box-shadow: 0 2px 12px rgba(0,0,0,0.05);
	}
	.mh-qp__card-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		padding: 18px 24px;
		border-bottom: 1px solid var(--color-border, #dedede);
		background: #fafaf9;
	}
	.mh-qp__card-title {
		font-size: 14px;
		font-weight: 700;
		color: #222;
		text-transform: uppercase;
		letter-spacing: 0.06em;
		margin: 0;
	}
	.mh-qp__item-count {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-width: 22px;
		height: 22px;
		padding: 0 6px;
		background: var(--color-primary, #25476B);
		color: #fff;
		border-radius: 999px;
		font-size: 11px;
		font-weight: 700;
	}
	.mh-qp__card-body { padding: 0; }

	/* ── Product rijen ── */
	.mh-qp__product-row {
		display: flex;
		align-items: center;
		gap: 16px;
		padding: 16px 24px;
		border-bottom: 1px solid var(--color-border, #dedede);
		transition: background 0.1s;
	}
	.mh-qp__product-row:last-child { border-bottom: none; }
	.mh-qp__product-row:hover { background: #fafaf9; }

	.mh-qp__thumb {
		width: 64px;
		height: 64px;
		object-fit: cover;
		border-radius: 8px;
		border: 1px solid var(--color-border, #dedede);
		flex-shrink: 0;
		display: block;
	}
	.mh-qp__thumb-placeholder {
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

	.mh-qp__product-info {
		flex: 1 1 0;
		min-width: 0;
	}
	.mh-qp__product-name {
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
	.mh-qp__product-name:hover { color: var(--color-primary, #25476B); }
	.mh-qp__product-cat {
		font-size: 12px;
		color: #999;
	}

	.mh-qp__product-actions {
		display: flex;
		align-items: center;
		gap: 10px;
		margin-left: auto;
		flex-shrink: 0;
	}

	/* Stepper ── */
	.mh-qp__stepper {
		display: flex;
		align-items: center;
		border: 1px solid var(--color-border, #dedede);
		border-radius: 8px;
		overflow: hidden;
		background: #fff;
		height: 38px;
		flex-shrink: 0;
	}
	.mh-qp__qty-btn {
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
	.mh-qp__qty-btn:hover {
		background: var(--color-bg, #f5f5f5);
		color: var(--color-primary, #25476B);
	}
	.mh-qp__qty-btn svg { display: block; pointer-events: none; }
	.mh-qp__qty-input {
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
	.mh-qp__qty-input::-webkit-inner-spin-button,
	.mh-qp__qty-input::-webkit-outer-spin-button { -webkit-appearance: none; }

	/* Verwijderknop */
	.mh-qp__remove-btn {
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
	.mh-qp__remove-btn:hover {
		background: #fee2e2;
		border-color: #f87171;
	}
	.mh-qp__remove-btn svg { display: block; pointer-events: none; flex-shrink: 0; }

	/* Kaartfooter: bijwerken-knop */
	.mh-qp__card-footer {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		padding: 14px 24px;
		background: #fafaf9;
		border-top: 1px solid var(--color-border, #dedede);
		flex-wrap: wrap;
	}
	.mh-qp__footer-left { display: flex; gap: 10px; flex-wrap: wrap; }

	/* Knoppen */
	.mh-qp__btn {
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
	.mh-qp__btn--primary {
		background: var(--color-primary, #25476B);
		color: #fff;
		border-color: var(--color-primary, #25476B);
	}
	.mh-qp__btn--primary:hover { background: var(--color-primary-hover, #325F8D); border-color: var(--color-primary-hover, #325F8D); color: #fff; }
	.mh-qp__btn--outline {
		background: #fff;
		color: var(--color-primary, #25476B);
		border-color: var(--color-border, #dedede);
	}
	.mh-qp__btn--outline:hover { border-color: var(--color-primary, #25476B); }

	/* ── Formulier ── */
	.mh-qp__fields {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 20px;
		padding: 24px;
	}
	.mh-qp__field {
		display: flex;
		flex-direction: column;
		gap: 6px;
	}
	.mh-qp__field--full { grid-column: 1 / -1; }
	.mh-qp__label {
		font-size: 12px;
		font-weight: 600;
		color: #666;
		text-transform: uppercase;
		letter-spacing: 0.05em;
	}
	.mh-qp__label abbr { text-decoration: none; color: #b91c1c; margin-left: 2px; }
	.mh-qp__input,
	.mh-qp__textarea {
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
	.mh-qp__input::placeholder,
	.mh-qp__textarea::placeholder { color: #bbb; }
	.mh-qp__input:focus,
	.mh-qp__textarea:focus {
		outline: none;
		border-color: var(--color-secondary-soft, #559EA3);
		box-shadow: 0 0 0 3px var(--color-focus-ring, rgba(85, 158, 163, 0.22));
	}
	.mh-qp__textarea { resize: vertical; min-height: 100px; }

	.mh-qp__form-footer {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 16px;
		padding: 16px 24px 24px;
		flex-wrap: wrap;
	}
	.mh-qp__disclaimer {
		font-size: 12px;
		color: #aaa;
		margin: 0;
		flex: 1 1 200px;
	}
	.mh-qp__btn--submit {
		height: 48px;
		padding: 0 32px;
		font-size: 15px;
	}

	@media (max-width: 767px) {
		.mh-qp {
			max-width: 100%;
		}

		.mh-qp__sent {
			padding: 16px 18px;
			margin-bottom: 24px;
		}

		.mh-qp__empty {
			padding: 44px 18px;
		}

		.mh-qp__empty a {
			width: 100%;
			justify-content: center;
		}

		.mh-qp__card {
			margin-bottom: 18px;
		}

		.mh-qp__card-header,
		.mh-qp__card-footer {
			padding: 14px 16px;
		}

		.mh-qp__card-title {
			font-size: 13px;
		}

		.mh-qp__product-row {
			display: grid;
			grid-template-columns: 56px minmax(0, 1fr);
			gap: 12px;
			padding: 14px 16px;
			align-items: start;
		}

		.mh-qp__thumb,
		.mh-qp__thumb-placeholder {
			width: 56px;
			height: 56px;
			border-radius: 6px;
		}

		.mh-qp__product-info {
			min-width: 0;
			padding-top: 2px;
		}

		.mh-qp__product-name {
			white-space: normal;
			overflow: visible;
			text-overflow: initial;
			line-height: 1.4;
			margin-bottom: 4px;
		}

		.mh-qp__product-cat {
			display: block;
			line-height: 1.4;
		}

		.mh-qp__product-actions {
			grid-column: 1 / -1;
			margin-left: 0;
			flex-wrap: wrap;
			width: 100%;
			padding-top: 4px;
		}

		.mh-qp__stepper {
			flex: 1 1 100%;
			width: 100%;
			height: 42px;
		}

		.mh-qp__qty-btn {
			width: 40px;
		}

		.mh-qp__qty-input {
			width: 56px;
			font-size: 14px;
		}

		.mh-qp__remove-btn {
			flex: 1 1 100%;
			width: 100%;
			justify-content: center;
			height: 40px;
			font-size: 13px;
		}

		.mh-qp__footer-left {
			width: 100%;
			flex-direction: column;
		}

		.mh-qp__btn {
			width: 100%;
			justify-content: center;
		}

		.mh-qp__fields {
			grid-template-columns: 1fr;
			gap: 16px;
			padding: 16px;
		}

		.mh-qp__label {
			font-size: 11px;
		}

		.mh-qp__input,
		.mh-qp__textarea {
			font-size: 16px;
			padding: 12px 14px;
		}

		.mh-qp__textarea {
			min-height: 120px;
		}

		.mh-qp__form-footer {
			padding: 12px 16px 18px;
			flex-direction: column;
			align-items: stretch;
		}

		.mh-qp__disclaimer {
			font-size: 11px;
			line-height: 1.5;
		}

		.mh-qp__btn--submit {
			width: 100%;
			height: 46px;
			padding: 0 20px;
			font-size: 14px;
			justify-content: center;
		}
	}

	@media (max-width: 420px) {
		.mh-qp__sent {
			padding: 14px 16px;
		}

		.mh-qp__card-header,
		.mh-qp__card-footer,
		.mh-qp__product-row,
		.mh-qp__fields,
		.mh-qp__form-footer {
			padding-left: 14px;
			padding-right: 14px;
		}

		.mh-qp__product-row {
			grid-template-columns: 48px minmax(0, 1fr);
			gap: 10px;
		}

		.mh-qp__thumb,
		.mh-qp__thumb-placeholder {
			width: 48px;
			height: 48px;
		}

		.mh-qp__product-name {
			font-size: 13px;
		}

		.mh-qp__product-cat {
			font-size: 11px;
		}
	}
	</style>

	<div class="mh-qp">

	<?php if ( isset( $_GET['mh_sent'] ) ) : ?>
		<div class="mh-qp__sent">
			<div class="mh-qp__sent-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
			</div>
			<div>
				<p class="mh-qp__sent-title">Offerte aanvraag verstuurd!</p>
				<p class="mh-qp__sent-sub">Bedankt voor je aanvraag. We nemen zo snel mogelijk contact met je op.</p>
			</div>
		</div>

	<?php elseif ( empty( $items ) ) : ?>
		<div class="mh-qp__empty">
			<div class="mh-qp__empty-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
			</div>
			<h3>Je offerte is leeg</h3>
			<p>Voeg producten toe via de productpagina's.</p>
			<a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>">Bekijk ons aanbod</a>
		</div>

	<?php else : ?>

		<!-- ── Productenkaart ── -->
		<form method="post" action="">
			<?php wp_nonce_field( 'mh_quote_page', 'mh_nonce' ); ?>
			<input type="hidden" name="product_id" value="" id="mh-remove-pid">

			<div class="mh-qp__card">
				<div class="mh-qp__card-header">
					<h2 class="mh-qp__card-title">Geselecteerde items</h2>
					<span class="mh-qp__item-count"><?php echo count( $items ); ?></span>
				</div>
				<div class="mh-qp__card-body">
					<?php foreach ( $items as $product_id => $item ) :
						$item_data = mh_quote_get_item_display_data( (int) $product_id, (array) $item );
						if ( ! $item_data ) continue;
						$thumb_id   = (int) $item_data['thumb_id'];
						$meta_label = isset( $item_data['meta'] ) ? (string) $item_data['meta'] : '';
					?>
					<div class="mh-qp__product-row">
						<?php if ( $thumb_id ) : ?>
							<?php echo wp_get_attachment_image( $thumb_id, array( 64, 64 ), false, array( 'class' => 'mh-qp__thumb' ) ); ?>
						<?php else : ?>
							<div class="mh-qp__thumb-placeholder">
								<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
							</div>
						<?php endif; ?>

						<div class="mh-qp__product-info">
							<a href="<?php echo esc_url( (string) $item_data['permalink'] ); ?>" class="mh-qp__product-name">
								<?php echo esc_html( (string) $item_data['name'] ); ?>
							</a>
							<?php if ( $meta_label ) : ?>
								<span class="mh-qp__product-cat"><?php echo esc_html( $meta_label ); ?></span>
							<?php endif; ?>
						</div>

						<div class="mh-qp__product-actions">
							<div class="mh-qp__stepper" data-stepper>
								<button type="button" class="mh-qp__qty-btn mh-qp__qty-btn--minus" aria-label="Minder">
									<?php echo $icon_minus; ?>
								</button>
								<input type="number" class="mh-qp__qty-input"
									name="quantities[<?php echo esc_attr( $product_id ); ?>]"
									value="<?php echo esc_attr( $item['quantity'] ); ?>"
									min="1" aria-label="Aantal">
								<button type="button" class="mh-qp__qty-btn mh-qp__qty-btn--plus" aria-label="Meer">
									<?php echo $icon_plus; ?>
								</button>
							</div>

							<button type="submit"
								class="mh-qp__remove-btn"
								name="mh_action"
								value="mh_remove_from_quote"
								onclick="document.getElementById('mh-remove-pid').value='<?php echo esc_js( $product_id ); ?>'">
								<?php echo $icon_trash; ?>
								Verwijderen
							</button>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
				<div class="mh-qp__card-footer">
					<div class="mh-qp__footer-left">
						<button type="submit" name="mh_action" value="mh_update_quote" class="mh-qp__btn mh-qp__btn--outline">
							Aantallen bijwerken
						</button>
						<a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>" class="mh-qp__btn mh-qp__btn--outline">
							+ Meer producten toevoegen
						</a>
					</div>
				</div>
			</div>
		</form>

		<!-- ── Contactformulier ── -->
		<form method="post" action="">
			<?php wp_nonce_field( 'mh_quote_page', 'mh_nonce' ); ?>
			<input type="hidden" name="mh_action" value="mh_submit_quote">

			<div class="mh-qp__card">
				<div class="mh-qp__card-header">
					<h2 class="mh-qp__card-title">Jouw gegevens</h2>
				</div>
				<div class="mh-qp__fields">
					<div class="mh-qp__field">
						<label class="mh-qp__label" for="mh_firstname">Voornaam <abbr title="verplicht">*</abbr></label>
						<input class="mh-qp__input" type="text" id="mh_firstname" name="mh_firstname" required placeholder="Voornaam">
					</div>
					<div class="mh-qp__field">
						<label class="mh-qp__label" for="mh_lastname">Achternaam <abbr title="verplicht">*</abbr></label>
						<input class="mh-qp__input" type="text" id="mh_lastname" name="mh_lastname" required placeholder="Achternaam">
					</div>
					<div class="mh-qp__field">
						<label class="mh-qp__label" for="mh_email">E-mailadres <abbr title="verplicht">*</abbr></label>
						<input class="mh-qp__input" type="email" id="mh_email" name="mh_email" required placeholder="naam@bedrijf.nl">
					</div>
					<div class="mh-qp__field">
						<label class="mh-qp__label" for="mh_phone">Telefoonnummer</label>
						<input class="mh-qp__input" type="tel" id="mh_phone" name="mh_phone" placeholder="06 – 12 34 56 78">
					</div>
					<div class="mh-qp__field mh-qp__field--full">
						<label class="mh-qp__label" for="mh_message">Opmerkingen of vragen</label>
						<textarea class="mh-qp__textarea" id="mh_message" name="mh_message" placeholder="Bijv. leveringsadres, gewenste datum of specifieke wensen..."></textarea>
					</div>
				</div>
				<div class="mh-qp__form-footer">
					<p class="mh-qp__disclaimer">Je gegevens worden uitsluitend gebruikt voor het verwerken van je offerte aanvraag.</p>
					<button type="submit" class="mh-qp__btn mh-qp__btn--primary mh-qp__btn--submit">
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
			var input = stepper.querySelector('.mh-qp__qty-input');
			var minus = stepper.querySelector('.mh-qp__qty-btn--minus');
			var plus  = stepper.querySelector('.mh-qp__qty-btn--plus');
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

add_shortcode( 'mh_quote_form', 'mh_quote_render_standalone_form' );
function mh_quote_render_standalone_form() {
	$nonce = wp_create_nonce( 'mh_standalone_quote_form' );

	ob_start();
	?>
	<style>
	.mh-sqf {
		max-width: 860px;
		margin: 0 auto;
		font-family: Inter, sans-serif;
		color: #333;
	}
	.mh-sqf *, .mh-sqf *::before, .mh-sqf *::after {
		box-sizing: border-box;
	}
	.mh-sqf__sent {
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
	.mh-sqf__sent strong {
		display: block;
		margin-bottom: 4px;
		font-size: 15px;
		color: #15803d;
	}
	.mh-sqf__sent p {
		margin: 0;
		font-size: 13px;
		color: #166534;
	}
	.mh-sqf__card {
		background: #fff;
		border: 1px solid var(--color-border, #dedede);
		border-radius: 5px;
		overflow: hidden;
		box-shadow: 0 2px 12px rgba(0,0,0,0.05);
	}
	.mh-sqf__header {
		padding: 18px 24px;
		border-bottom: 1px solid var(--color-border, #dedede);
		background: #fafaf9;
	}
	.mh-sqf__title {
		margin: 0;
		font-size: 14px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: .06em;
		color: #222;
	}
	.mh-sqf__fields {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 20px;
		padding: 24px;
	}
	.mh-sqf__field {
		display: flex;
		flex-direction: column;
		gap: 6px;
	}
	.mh-sqf__field--full {
		grid-column: 1 / -1;
	}
	.mh-sqf__label {
		font-size: 12px;
		font-weight: 600;
		color: #666;
		text-transform: uppercase;
		letter-spacing: .05em;
	}
	.mh-sqf__label abbr {
		margin-left: 2px;
		color: #b91c1c;
		text-decoration: none;
	}
	.mh-sqf__input,
	.mh-sqf__textarea {
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
	.mh-sqf__input::placeholder,
	.mh-sqf__textarea::placeholder {
		color: #bbb;
	}
	.mh-sqf__input:focus,
	.mh-sqf__textarea:focus {
		outline: none;
		border-color: var(--color-secondary-soft, #559EA3);
		box-shadow: 0 0 0 3px var(--color-focus-ring, rgba(85, 158, 163, 0.22));
	}
	.mh-sqf__textarea {
		min-height: 140px;
		resize: vertical;
	}
	.mh-sqf__footer {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 16px;
		flex-wrap: wrap;
		padding: 0 24px 24px;
	}
	.mh-sqf__disclaimer {
		flex: 1 1 240px;
		margin: 0;
		font-size: 12px;
		line-height: 1.6;
		color: #8a8a8a;
	}
	.mh-sqf__submit {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-height: 48px;
		padding: 0 28px;
		border: 1px solid var(--color-primary, #25476B);
		border-radius: 6px;
		background: var(--color-primary, #25476B);
		color: #fff;
		font-family: Inter, sans-serif;
		font-size: 15px;
		font-weight: 700;
		cursor: pointer;
	}
	.mh-sqf__submit:hover {
		background: var(--color-primary-hover, #325F8D);
		border-color: var(--color-primary-hover, #325F8D);
	}
	@media (max-width: 767px) {
		.mh-sqf__header {
			padding: 16px 18px;
		}
		.mh-sqf__fields {
			grid-template-columns: 1fr;
			gap: 16px;
			padding: 18px;
		}
		.mh-sqf__input,
		.mh-sqf__textarea {
			font-size: 16px;
			padding: 12px 14px;
		}
		.mh-sqf__footer {
			padding: 0 18px 18px;
			flex-direction: column;
			align-items: stretch;
		}
		.mh-sqf__submit {
			width: 100%;
		}
	}
	</style>

	<div class="mh-sqf">
		<?php if ( isset( $_GET['mh_quote_form_sent'] ) ) : ?>
			<div class="mh-sqf__sent">
				<div>
					<strong>Offerte aanvraag verstuurd</strong>
					<p>Bedankt voor je aanvraag. We nemen zo snel mogelijk contact met je op.</p>
				</div>
			</div>
		<?php endif; ?>

		<form method="post" action="">
			<?php wp_nonce_field( 'mh_standalone_quote_form', 'mh_nonce' ); ?>
			<input type="hidden" name="mh_action" value="mh_submit_standalone_quote">

			<div class="mh-sqf__card">
				<div class="mh-sqf__header">
					<h2 class="mh-sqf__title">Offerte aanvraag</h2>
				</div>

				<div class="mh-sqf__fields">
					<div class="mh-sqf__field">
						<label class="mh-sqf__label" for="mh-sqf-firstname">Voornaam <abbr title="verplicht">*</abbr></label>
						<input class="mh-sqf__input" type="text" id="mh-sqf-firstname" name="mh_firstname" required placeholder="Voornaam">
					</div>
					<div class="mh-sqf__field">
						<label class="mh-sqf__label" for="mh-sqf-lastname">Achternaam <abbr title="verplicht">*</abbr></label>
						<input class="mh-sqf__input" type="text" id="mh-sqf-lastname" name="mh_lastname" required placeholder="Achternaam">
					</div>
					<div class="mh-sqf__field">
						<label class="mh-sqf__label" for="mh-sqf-email">E-mailadres <abbr title="verplicht">*</abbr></label>
						<input class="mh-sqf__input" type="email" id="mh-sqf-email" name="mh_email" required placeholder="naam@bedrijf.nl">
					</div>
					<div class="mh-sqf__field">
						<label class="mh-sqf__label" for="mh-sqf-phone">Telefoonnummer</label>
						<input class="mh-sqf__input" type="tel" id="mh-sqf-phone" name="mh_phone" placeholder="06 – 12 34 56 78">
					</div>
					<div class="mh-sqf__field mh-sqf__field--full">
						<label class="mh-sqf__label" for="mh-sqf-message">Opmerkingen of vragen</label>
						<textarea class="mh-sqf__textarea" id="mh-sqf-message" name="mh_message" placeholder="Omschrijf hier je aanvraag of wensen..."></textarea>
					</div>
				</div>

				<div class="mh-sqf__footer">
					<p class="mh-sqf__disclaimer">Je gegevens worden uitsluitend gebruikt voor het verwerken van je offerte aanvraag.</p>
					<button type="submit" class="mh-sqf__submit">Offerte aanvragen</button>
				</div>
			</div>
		</form>
	</div>
	<?php

	return ob_get_clean();
}

add_shortcode( 'mh_inrichting_samenstellen', 'mh_quote_render_room_builder' );
function mh_quote_render_room_builder( array $atts = array() ) {
	$atts = shortcode_atts(
		array(
			'title'      => 'Kies hieronder waar jij (ongeveer) naar zoekt:',
			'categories' => '',
		),
		$atts,
		'mh_inrichting_samenstellen'
	);

	$assets = mh_quote_get_room_builder_assets();

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
	.mh-rb {
		max-width: 1200px;
		margin: 0 auto;
		font-family: Inter, sans-serif;
		color: #2f2a24;
	}
	.mh-rb *, .mh-rb *::before, .mh-rb *::after {
		box-sizing: border-box;
	}
	.mh-rb__sent {
		margin-bottom: 24px;
		padding: 18px 20px;
		border: 1px solid #86efac;
		border-left: 4px solid #16a34a;
		border-radius: 8px;
		background: #f0fdf4;
	}
	.mh-rb__sent strong {
		display: block;
		margin-bottom: 4px;
		font-size: 16px;
		color: #15803d;
	}
	.mh-rb__sent p {
		margin: 0;
		font-size: 14px;
		color: #166534;
	}
	.mh-rb__headline {
		margin: 0 0 22px;
		font-family: Inter, sans-serif;
		font-size: 24px;
		font-weight: 700;
		line-height: 1.15;
		color: #2d241b;
	}
	.mh-rb__grid {
		display: grid;
		grid-template-columns: repeat(4, minmax(0, 1fr));
		gap: 20px;
		margin-bottom: 28px;
	}
	.mh-rb__card {
		display: flex;
		flex-direction: column;
		min-height: 100%;
		cursor: pointer;
		transition: transform .15s ease;
	}
	.mh-rb__card:hover {
		transform: translateY(-2px);
	}
	.mh-rb__media {
		position: relative;
		aspect-ratio: 4 / 3;
		background: linear-gradient(135deg, #f2ece3 0%, #ded1bc 100%);
		border: 1px solid #DEDEDE;
		border-radius: 5px;
		overflow: hidden;
		transition: border-color .15s ease, box-shadow .15s ease;
	}
	.mh-rb__card:hover .mh-rb__media {
		border-color: var(--color-border-strong, #8AC697);
		box-shadow: none;
	}
	.mh-rb__card.is-active .mh-rb__media {
		border-color: #2F7C7A;
		box-shadow: none;
	}
	.mh-rb__media img {
		width: 100%;
		height: 100%;
		object-fit: cover;
		display: block;
	}
	.mh-rb__media-placeholder {
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
	.mh-rb__body {
		display: flex;
		flex: 1 1 auto;
		flex-direction: column;
		gap: 14px;
		padding: 18px;
		background: transparent;
	}
	.mh-rb__product-title {
		margin: 0;
		font-family: Inter, sans-serif;
		font-size: 20px;
		font-weight: 700;
		line-height: 1.2;
		color: #2d241b;
	}
	.mh-rb__desc {
		margin: 0;
		font-size: 14px;
		line-height: 1.6;
		color: #62574c;
	}
	.mh-rb__stepper {
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
	.mh-rb__qty-btn {
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
	.mh-rb__qty-btn:hover {
		background: #f5f5f5;
		color: var(--color-primary, #25476B);
	}
	.mh-rb__qty-btn:focus,
	.mh-rb__qty-btn:active,
	.mh-rb__qty-btn:focus-visible,
	.mh-rb__qty-btn:hover:active {
		background: #f5f5f5 !important;
		color: var(--color-primary, #25476B) !important;
		outline: none !important;
		box-shadow: none !important;
	}
	.mh-rb__qty-btn svg {
		display: block;
		pointer-events: none;
	}
	.mh-rb__qty-input {
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
	.mh-rb__qty-input::-webkit-inner-spin-button,
	.mh-rb__qty-input::-webkit-outer-spin-button {
		-webkit-appearance: none;
	}
	.mh-rb__form {
		margin-top: 8px;
		padding: 28px;
		background: #fff;
		border: 1px solid #DEDEDE;
		border-radius: 10px;
	}
	.mh-rb__form-title {
		margin: 0 0 18px;
		font-size: 22px;
		color: #2d241b;
	}
	.mh-rb__fields {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: 18px 20px;
	}
	.mh-rb__field--full {
		grid-column: 1 / -1;
	}
	.mh-rb__label {
		display: block;
		margin-bottom: 8px;
		font-family: Poppins, sans-serif;
		font-size: 15px;
		font-weight: 300;
		color: #5d5144;
	}
	.mh-rb__input,
	.mh-rb__textarea {
		width: 100%;
		padding: 12px 14px;
		border: 1px solid #DEDEDE;
		border-radius: 8px;
		font: inherit;
		font-size: 15px;
		color: #2f2a24;
		background: #fff;
	}
	.mh-rb__textarea {
		min-height: 150px;
		resize: vertical;
	}
	.mh-rb__input:focus,
	.mh-rb__textarea:focus,
	.mh-rb__qty-input:focus {
		outline: none;
		border-color: var(--color-secondary-soft, #559EA3);
		box-shadow: 0 0 0 3px var(--color-focus-ring, rgba(85, 158, 163, 0.22));
	}
	.mh-rb__footer {
		display: flex;
		justify-content: space-between;
		align-items: center;
		gap: 18px;
		flex-wrap: wrap;
		margin-top: 20px;
	}
	.mh-rb__disclaimer {
		margin: 0;
		font-size: 12px;
		line-height: 1.6;
		color: #8b8175;
	}
	.mh-rb__submit {
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
	.mh-rb__submit:hover {
		background: #245F5D;
		border-color: #245F5D;
	}
	@media (max-width: 991px) {
		.mh-rb__grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}
	@media (max-width: 767px) {
		.mh-rb__headline {
			font-size: 28px;
		}
		.mh-rb__grid,
		.mh-rb__fields {
			grid-template-columns: 1fr;
		}
		.mh-rb__form {
			padding: 20px;
		}
		.mh-rb__footer {
			align-items: stretch;
		}
		.mh-rb__submit {
			width: 100%;
		}
	}
	</style>

	<div class="mh-rb">
		<?php if ( isset( $_GET['mh_room_builder_sent'] ) ) : ?>
			<div class="mh-rb__sent">
				<strong>Aanvraag verstuurd</strong>
				<p>Bedankt! We hebben je globale inrichtingseisen ontvangen en nemen snel contact met je op.</p>
			</div>
		<?php endif; ?>

		<h2 class="mh-rb__headline"><?php echo esc_html( $atts['title'] ); ?></h2>

		<form method="post" class="mh-rb__builder-form">
			<?php wp_nonce_field( 'mh_room_builder_quote_form', 'mh_nonce' ); ?>
			<input type="hidden" name="mh_action" value="mh_submit_room_builder_quote">

			<?php if ( empty( $assets ) ) : ?>
				<p>Er zijn nog geen onderdelen gevonden in de map <code>offerte</code>.</p>
			<?php else : ?>
				<div class="mh-rb__grid">
					<?php foreach ( $assets as $asset ) : ?>
						<div class="mh-rb__card" data-category-card>
							<div class="mh-rb__media">
								<img src="<?php echo esc_url( $asset['image_url'] ); ?>" alt="<?php echo esc_attr( $asset['label'] ); ?>">
							</div>
							<div class="mh-rb__body">
								<h3 class="mh-rb__product-title"><?php echo esc_html( $asset['label'] ); ?></h3>
								<input type="hidden" name="mh_room_builder_items[<?php echo esc_attr( $asset['key'] ); ?>][label]" value="<?php echo esc_attr( $asset['label'] ); ?>">
								<input type="hidden" name="mh_room_builder_items[<?php echo esc_attr( $asset['key'] ); ?>][term_id]" value="<?php echo esc_attr( $asset['term_id'] ); ?>">
								<div class="mh-rb__stepper" data-stepper>
									<button type="button" class="mh-rb__qty-btn mh-rb__qty-btn--minus" aria-label="Minder">
										<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/></svg>
									</button>
									<input type="number" class="mh-rb__qty-input" name="mh_room_builder_items[<?php echo esc_attr( $asset['key'] ); ?>][quantity]" value="0" min="0" inputmode="numeric" aria-label="<?php echo esc_attr( $asset['label'] ); ?> aantal">
									<button type="button" class="mh-rb__qty-btn mh-rb__qty-btn--plus" aria-label="Meer">
										<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
									</button>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="mh-rb__form">
				<h3 class="mh-rb__form-title">Jouw gegevens</h3>
				<div class="mh-rb__fields">
					<div class="mh-rb__field">
						<label class="mh-rb__label" for="mh-rb-firstname">Voornaam</label>
						<input class="mh-rb__input" type="text" id="mh-rb-firstname" name="mh_firstname" required placeholder="Voornaam">
					</div>
					<div class="mh-rb__field">
						<label class="mh-rb__label" for="mh-rb-lastname">Achternaam</label>
						<input class="mh-rb__input" type="text" id="mh-rb-lastname" name="mh_lastname" required placeholder="Achternaam">
					</div>
					<div class="mh-rb__field">
						<label class="mh-rb__label" for="mh-rb-email">E-mailadres</label>
						<input class="mh-rb__input" type="email" id="mh-rb-email" name="mh_email" required placeholder="naam@bedrijf.nl">
					</div>
					<div class="mh-rb__field">
						<label class="mh-rb__label" for="mh-rb-phone">Telefoonnummer</label>
						<input class="mh-rb__input" type="tel" id="mh-rb-phone" name="mh_phone" placeholder="06 12 34 56 78">
					</div>
					<div class="mh-rb__field mh-rb__field--full">
						<label class="mh-rb__label" for="mh-rb-message">Aanvullende toelichting</label>
						<textarea class="mh-rb__textarea" id="mh-rb-message" name="mh_message" placeholder="Bijvoorbeeld gewenste stijl, ruimte, levermoment of andere wensen..."></textarea>
					</div>
				</div>
				<div class="mh-rb__footer">
					<p class="mh-rb__disclaimer">Je gegevens worden uitsluitend gebruikt om je aanvraag te beoordelen en contact met je op te nemen.</p>
					<button type="submit" class="mh-rb__submit">Aanvraag samenstellen</button>
				</div>
			</div>
		</form>
	</div>

	<script>
	(function () {
		document.querySelectorAll('.mh-rb [data-category-card]').forEach(function (card) {
			var stepper = card.querySelector('[data-stepper]');
			if (!stepper) return;

			var input = stepper.querySelector('.mh-rb__qty-input');
			var minus = stepper.querySelector('.mh-rb__qty-btn--minus');
			var plus = stepper.querySelector('.mh-rb__qty-btn--plus');

			if (!input || !minus || !plus) return;

			var syncCardState = function () {
				var value = parseInt(input.value, 10);
				if (isNaN(value) || value < 0) value = 0;
				input.value = value;
				card.classList.toggle('is-active', value > 0);
			};

			card.addEventListener('click', function (event) {
				if (event.target.closest('.mh-rb__stepper')) return;

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
