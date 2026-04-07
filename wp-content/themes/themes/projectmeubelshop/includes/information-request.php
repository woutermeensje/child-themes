<?php
/**
 * Projectmeubelshop - Informatie aanvragen shortcode en opslag.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'pms_register_information_request_post_type' );
function pms_register_information_request_post_type(): void {
	register_post_type(
		'pms_information_request',
		array(
			'labels' => array(
				'name'               => 'Informatieaanvragen',
				'singular_name'      => 'Informatieaanvraag',
				'menu_name'          => 'Informatieaanvragen',
				'all_items'          => 'Alle informatieaanvragen',
				'view_item'          => 'Bekijk informatieaanvraag',
				'search_items'       => 'Zoek informatieaanvragen',
				'not_found'          => 'Geen informatieaanvragen gevonden.',
				'not_found_in_trash' => 'Geen informatieaanvragen in de prullenbak.',
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-email-alt',
			'menu_position'       => 26,
			'supports'            => array( 'title' ),
			'capability_type'     => 'post',
			'capabilities'        => array(
				'create_posts' => 'do_not_allow',
			),
			'map_meta_cap'        => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'has_archive'         => false,
		)
	);
}

add_filter( 'manage_pms_information_request_posts_columns', 'pms_information_request_columns' );
function pms_information_request_columns( array $columns ): array {
	return array(
		'cb'         => $columns['cb'] ?? '<input type="checkbox" />',
		'title'      => 'Naam',
		'pms_email'  => 'E-mail',
		'pms_phone'  => 'Telefoon',
		'pms_note'   => 'Bericht (kort)',
		'date'       => 'Datum',
	);
}

add_action( 'manage_pms_information_request_posts_custom_column', 'pms_information_request_column_content', 10, 2 );
function pms_information_request_column_content( string $column, int $post_id ): void {
	switch ( $column ) {
		case 'pms_email':
			$email = get_post_meta( $post_id, '_pms_information_email', true );
			echo $email ? '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '—';
			break;

		case 'pms_phone':
			echo esc_html( get_post_meta( $post_id, '_pms_information_phone', true ) ?: '—' );
			break;

		case 'pms_note':
			$message = wp_strip_all_tags( (string) get_post_meta( $post_id, '_pms_information_message', true ) );
			echo esc_html( wp_html_excerpt( $message, 80, '…' ) ?: '—' );
			break;
	}
}

add_action( 'add_meta_boxes', 'pms_information_request_meta_box' );
function pms_information_request_meta_box(): void {
	add_meta_box(
		'pms-information-request-details',
		'Informatieaanvraag details',
		'pms_render_information_request_meta_box',
		'pms_information_request',
		'normal',
		'high'
	);
}

function pms_render_information_request_meta_box( WP_Post $post ): void {
	$fields = array(
		'_pms_information_firstname' => 'Voornaam',
		'_pms_information_lastname'  => 'Achternaam',
		'_pms_information_email'     => 'E-mail',
		'_pms_information_phone'     => 'Telefoon',
	);

	echo '<table style="width:100%;border-collapse:collapse;">';

	foreach ( $fields as $meta_key => $label ) {
		$value = (string) get_post_meta( $post->ID, $meta_key, true );

		echo '<tr>';
		echo '<th style="text-align:left;padding:6px 10px 6px 0;width:140px;font-weight:600;">' . esc_html( $label ) . '</th>';
		echo '<td style="padding:6px 0;">';

		if ( '_pms_information_email' === $meta_key && $value ) {
			echo '<a href="mailto:' . esc_attr( $value ) . '">' . esc_html( $value ) . '</a>';
		} else {
			echo esc_html( $value ?: '—' );
		}

		echo '</td>';
		echo '</tr>';
	}

	echo '</table>';

	$message = (string) get_post_meta( $post->ID, '_pms_information_message', true );
	if ( '' !== trim( $message ) ) {
		echo '<hr style="margin:14px 0;">';
		echo '<p style="font-weight:600;margin:0 0 8px;">Bericht</p>';
		echo '<div style="background:#f9f7f2;padding:12px;border:1px solid #ddd;border-radius:4px;">' . wp_kses_post( wpautop( $message ) ) . '</div>';
	}
}

function pms_store_information_request( array $data ): int {
	$firstname = sanitize_text_field( $data['firstname'] ?? '' );
	$lastname  = sanitize_text_field( $data['lastname'] ?? '' );
	$email     = sanitize_email( $data['email'] ?? '' );
	$phone     = sanitize_text_field( $data['phone'] ?? '' );
	$message   = wp_kses_post( $data['message'] ?? '' );
	$name      = trim( $firstname . ' ' . $lastname );

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'pms_information_request',
			'post_status' => 'publish',
			'post_title'  => $name ?: 'Nieuwe informatieaanvraag',
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		return 0;
	}

	update_post_meta( $post_id, '_pms_information_firstname', $firstname );
	update_post_meta( $post_id, '_pms_information_lastname', $lastname );
	update_post_meta( $post_id, '_pms_information_email', $email );
	update_post_meta( $post_id, '_pms_information_phone', $phone );
	update_post_meta( $post_id, '_pms_information_message', $message );

	return (int) $post_id;
}

function pms_information_request_build_email_html( array $request_data, string $recipient = 'admin' ): string {
	$firstname = trim( (string) ( $request_data['firstname'] ?? '' ) );
	$lastname  = trim( (string) ( $request_data['lastname'] ?? '' ) );
	$name      = trim( (string) ( $request_data['name'] ?? trim( $firstname . ' ' . $lastname ) ) );
	$email     = (string) ( $request_data['email'] ?? '' );
	$phone     = (string) ( $request_data['phone'] ?? '' );
	$message   = (string) ( $request_data['message'] ?? '' );

	$is_customer  = 'customer' === $recipient;
	$title        = $is_customer ? 'Bevestiging informatieaanvraag' : 'Nieuwe informatieaanvraag';
	$intro        = $is_customer
		? 'Bedankt voor je aanvraag. We hebben je informatieaanvraag goed ontvangen en nemen zo snel mogelijk contact met je op.'
		: 'Er is een nieuwe informatieaanvraag binnengekomen via de website.';
	$message_html = $message
		? nl2br( esc_html( $message ) )
		: '<span style="color:#68736d;">Geen extra bericht opgegeven.</span>';

	return '
		<!doctype html>
		<html lang="nl">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>' . esc_html( $title ) . '</title>
		</head>
		<body style="margin:0;padding:0;background:#f6f3ed;font-family:Inter,Arial,sans-serif;color:#1f2a24;">
			<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f3ed;padding:32px 16px;">
				<tr>
					<td align="center">
						<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:760px;background:#ffffff;border:1px solid #e7e2d7;border-radius:12px;overflow:hidden;">
							<tr>
								<td style="padding:28px 32px;background:#4A3728;">
									<div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#e7dcc7;font-weight:700;">Projectmeubelshop</div>
									<h1 style="margin:10px 0 0;font-size:28px;line-height:1.2;color:#ffffff;">' . esc_html( $title ) . '</h1>
								</td>
							</tr>
							<tr>
								<td style="padding:28px 32px;">
									<p style="margin:0 0 24px;font-size:15px;line-height:1.7;color:#4b5563;">' . esc_html( $intro ) . '</p>

									<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 24px;border-collapse:separate;border-spacing:0;">
										<tr>
											<td colspan="2" style="padding:0 0 10px;font-size:13px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#68736d;">Contactgegevens</td>
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
										<div style="margin:0 0 10px;font-size:13px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#68736d;">Bericht</div>
										<div style="padding:16px 18px;background:#fbfaf8;border:1px solid #e7e2d7;border-radius:10px;font-size:14px;line-height:1.7;color:#1f2a24;">' . $message_html . '</div>
									</div>' .
									( $is_customer
										? '<div style="padding:18px 20px;background:#f6f3ed;border:1px solid #e7e2d7;border-radius:10px;font-size:14px;line-height:1.7;color:#4b5563;">
											We streven ernaar om je aanvraag zo snel mogelijk op te pakken. Heb je in de tussentijd aanvullende vragen? Reageer gerust op deze e-mail of neem contact met ons op via <a href="tel:0852392040" style="color:#4A3728;text-decoration:none;font-weight:600;">085 239 2040</a>.
										</div>'
										: '' ) . '
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</body>
		</html>';
}

add_shortcode( 'pms_informatie_aanvragen', 'pms_information_request_shortcode' );
function pms_information_request_shortcode( array $atts = array() ): string {
	static $instance = 0;
	$instance++;

	$atts = shortcode_atts(
		array(
			'title'           => 'Informatie aanvragen',
			'submit_label'    => 'Aanvraag versturen',
			'success_message' => 'Bedankt! We nemen zo snel mogelijk contact met je op.',
			'success_url'     => '',
		),
		$atts,
		'pms_informatie_aanvragen'
	);

	$errors = array();
	$values = array(
		'firstname' => '',
		'lastname'  => '',
		'email'     => '',
		'phone'     => '',
		'message'   => '',
	);
	$success = false;

	if (
		'POST' === $_SERVER['REQUEST_METHOD']
		&& isset( $_POST['pms_information_action'] )
		&& 'submit_information_request' === $_POST['pms_information_action']
		&& isset( $_POST['pms_information_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pms_information_nonce'] ) ), 'pms_information_request' )
	) {
		$values['firstname'] = sanitize_text_field( wp_unslash( $_POST['pms_information_firstname'] ?? '' ) );
		$values['lastname']  = sanitize_text_field( wp_unslash( $_POST['pms_information_lastname'] ?? '' ) );
		$values['email']     = sanitize_email( wp_unslash( $_POST['pms_information_email'] ?? '' ) );
		$values['phone']     = sanitize_text_field( wp_unslash( $_POST['pms_information_phone'] ?? '' ) );
		$values['message']   = wp_kses_post( wp_unslash( $_POST['pms_information_message'] ?? '' ) );

		if ( '' === $values['firstname'] ) {
			$errors[] = 'Vul je voornaam in.';
		}

		if ( '' === $values['lastname'] ) {
			$errors[] = 'Vul je achternaam in.';
		}

		if ( ! is_email( $values['email'] ) ) {
			$errors[] = 'Vul een geldig e-mailadres in.';
		}

		if ( '' === trim( wp_strip_all_tags( $values['message'] ) ) ) {
			$errors[] = 'Vul een bericht in.';
		}

		if ( empty( $errors ) ) {
			pms_store_information_request( $values );

			$name = trim( $values['firstname'] . ' ' . $values['lastname'] );
			$email_data = array(
				'firstname' => $values['firstname'],
				'lastname'  => $values['lastname'],
				'name'      => $name,
				'email'     => $values['email'],
				'phone'     => $values['phone'],
				'message'   => $values['message'],
			);

			$admin_html    = pms_information_request_build_email_html( $email_data, 'admin' );
			$customer_html = pms_information_request_build_email_html( $email_data, 'customer' );

			wp_mail(
				get_option( 'admin_email' ),
				'Nieuwe informatieaanvraag - ' . $name,
				$admin_html,
				array(
					'From: Projectmeubelshop <support@projectmeubelshop.nl>',
					'Content-Type: text/html; charset=UTF-8',
					'Reply-To: ' . $values['email'],
				)
			);

			wp_mail(
				$values['email'],
				'Bevestiging informatieaanvraag - Projectmeubelshop',
				$customer_html,
				array(
					'From: Projectmeubelshop <support@projectmeubelshop.nl>',
					'Content-Type: text/html; charset=UTF-8',
					'Reply-To: Projectmeubelshop <support@projectmeubelshop.nl>',
				)
			);

			if ( '' !== trim( (string) $atts['success_url'] ) ) {
				wp_safe_redirect( esc_url_raw( $atts['success_url'] ) );
				exit;
			}

			$success = true;
			$values  = array_fill_keys( array_keys( $values ), '' );
		}
	}

	wp_enqueue_style( 'poppins-font', 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap', array(), null );
	wp_enqueue_style( 'quill-snow', 'https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css', array(), '1.3.7' );
	wp_enqueue_script( 'quill', 'https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js', array(), '1.3.7', true );

	$editor_id = 'pms_information_message_' . $instance;
	$hidden_id = $editor_id . '_hidden';
	$form_id   = 'pms_information_form_' . $instance;

	ob_start();
	?>
	<style>
	.pms-info-form {
		max-width: 860px;
		margin: 0 auto;
		font-family: Inter, sans-serif;
		color: #2f2a24;
	}
	.pms-info-form *, .pms-info-form *::before, .pms-info-form *::after {
		box-sizing: border-box;
	}
	.pms-info-form__notice {
		display: flex;
		gap: 12px;
		padding: 16px 18px;
		border-radius: 10px;
		margin-bottom: 20px;
		font-size: 14px;
		line-height: 1.6;
	}
	.pms-info-form__notice--success {
		background: #edfdf2;
		border: 1px solid #9bd3ad;
		color: #17643a;
	}
	.pms-info-form__notice--error {
		background: #fff6f5;
		border: 1px solid #f0b8b2;
		color: #9f2f24;
	}
	.pms-info-form__notice ul {
		margin: 8px 0 0 18px;
	}
	.pms-info-form__header {
		padding: 0;
	}
	.pms-info-form__title {
		margin: 0;
		font-size: 26px;
		line-height: 1.2;
		color: #2d241b;
	}
	.pms-info-form__body {
		padding: 0;
	}
	.pms-info-form__grid {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: 18px 20px;
	}
	.pms-info-form__field--full {
		grid-column: 1 / -1;
	}
	.pms-info-form__label {
		display: block;
		margin: 0 0 8px;
		font-family: 'Poppins', sans-serif;
		font-size: 15px;
		font-weight: 300;
		letter-spacing: 0;
		text-transform: none;
		color: #5d5144;
	}
	.pms-info-form__req {
		color: #b14b3d;
	}
	.pms-info-form__input,
	.pms-info-form__textarea {
		width: 100%;
		border: 1px solid #DEDEDE;
		border-radius: 8px;
		padding: 12px 14px;
		background: #fff;
		font: inherit;
		font-size: 15px;
		color: #2f2a24;
		transition: border-color .15s ease, box-shadow .15s ease;
	}
	.pms-info-form__input:focus {
		outline: none;
		border-color: #c5b17d;
		box-shadow: 0 0 0 3px rgba(197, 177, 125, 0.18);
	}
	.pms-info-form__quill-wrap {
		border: 1px solid #DEDEDE;
		border-radius: 8px;
		overflow: hidden;
		background: #fff;
	}
	.pms-info-form__quill-editor {
		min-height: 220px;
	}
	.pms-info-form__quill-wrap .ql-toolbar.ql-snow {
		border: 0;
		border-bottom: 1px solid #e7ddcc;
		background: #fbfaf7;
	}
	.pms-info-form__quill-wrap .ql-container.ql-snow {
		border: 0;
		font-family: Inter, sans-serif;
		font-size: 15px;
		color: #2f2a24;
	}
	.pms-info-form__quill-wrap .ql-editor {
		background: #fff;
		min-height: 220px;
	}
	.pms-info-form__quill-hidden { display: none; }
	.pms-info-form__footer {
		padding: 0;
		display: flex;
		justify-content: space-between;
		align-items: center;
		gap: 16px;
		flex-wrap: wrap;
	}
	.pms-info-form__privacy {
		margin: 0;
		font-size: 12px;
		color: #8b8175;
		line-height: 1.5;
	}
	.pms-info-form__submit {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-height: 48px;
		padding: 0 28px;
		border: 0;
		border-radius: 8px;
		background: #4a3728;
		color: #fff;
		font: inherit;
		font-size: 15px;
		font-weight: 700;
		cursor: pointer;
		transition: background-color .15s ease;
	}
	.pms-info-form__submit:hover {
		background: #3d2d20;
	}
	@media (max-width: 767px) {
		.pms-info-form__grid {
			grid-template-columns: 1fr;
		}
		.pms-info-form__footer {
			align-items: stretch;
		}
		.pms-info-form__submit {
			width: 100%;
		}
	}
	</style>

	<div class="pms-info-form">
		<?php if ( $success ) : ?>
			<div class="pms-info-form__notice pms-info-form__notice--success">
				<div><?php echo esc_html( $atts['success_message'] ); ?></div>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $errors ) ) : ?>
			<div class="pms-info-form__notice pms-info-form__notice--error">
				<div>
					<strong>Er zijn een paar fouten:</strong>
					<ul>
						<?php foreach ( $errors as $error ) : ?>
							<li><?php echo esc_html( $error ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		<?php endif; ?>

		<div class="pms-info-form__header">
			<h2 class="pms-info-form__title"><?php echo esc_html( $atts['title'] ); ?></h2>
		</div>

		<form method="post" novalidate id="<?php echo esc_attr( $form_id ); ?>">
			<div class="pms-info-form__body">
				<?php wp_nonce_field( 'pms_information_request', 'pms_information_nonce' ); ?>
				<input type="hidden" name="pms_information_action" value="submit_information_request">

				<div class="pms-info-form__grid">
					<div class="pms-info-form__field">
						<label class="pms-info-form__label" for="pms_information_firstname">Voornaam <span class="pms-info-form__req">*</span></label>
						<input class="pms-info-form__input" type="text" id="pms_information_firstname" name="pms_information_firstname" value="<?php echo esc_attr( $values['firstname'] ); ?>" placeholder="Jan" required>
					</div>

					<div class="pms-info-form__field">
						<label class="pms-info-form__label" for="pms_information_lastname">Achternaam <span class="pms-info-form__req">*</span></label>
						<input class="pms-info-form__input" type="text" id="pms_information_lastname" name="pms_information_lastname" value="<?php echo esc_attr( $values['lastname'] ); ?>" placeholder="de Vries" required>
					</div>

					<div class="pms-info-form__field">
						<label class="pms-info-form__label" for="pms_information_email">E-mailadres <span class="pms-info-form__req">*</span></label>
						<input class="pms-info-form__input" type="email" id="pms_information_email" name="pms_information_email" value="<?php echo esc_attr( $values['email'] ); ?>" placeholder="naam@bedrijf.nl" required>
					</div>

					<div class="pms-info-form__field">
						<label class="pms-info-form__label" for="pms_information_phone">Telefoonnummer</label>
						<input class="pms-info-form__input" type="tel" id="pms_information_phone" name="pms_information_phone" value="<?php echo esc_attr( $values['phone'] ); ?>" placeholder="06 12 34 56 78">
					</div>

					<div class="pms-info-form__field pms-info-form__field--full">
						<label class="pms-info-form__label" for="<?php echo esc_attr( $editor_id ); ?>">Bericht <span class="pms-info-form__req">*</span></label>
						<div class="pms-info-form__quill-wrap">
							<div id="<?php echo esc_attr( $editor_id ); ?>" class="pms-info-form__quill-editor"></div>
						</div>
						<textarea id="<?php echo esc_attr( $hidden_id ); ?>" name="pms_information_message" class="pms-info-form__quill-hidden"><?php echo esc_textarea( $values['message'] ); ?></textarea>
					</div>
				</div>
			</div>

			<div class="pms-info-form__footer">
				<p class="pms-info-form__privacy">Je gegevens worden uitsluitend gebruikt om je aanvraag te beantwoorden.</p>
				<button type="submit" class="pms-info-form__submit"><?php echo esc_html( $atts['submit_label'] ); ?></button>
			</div>
		</form>
	</div>
	<script>
	(function () {
		function initPmsQuill() {
			if (typeof Quill === 'undefined') {
				setTimeout(initPmsQuill, 80);
				return;
			}

			var editor = document.getElementById(<?php echo wp_json_encode( $editor_id ); ?>);
			var hidden = document.getElementById(<?php echo wp_json_encode( $hidden_id ); ?>);
			var form = document.getElementById(<?php echo wp_json_encode( $form_id ); ?>);

			if (!editor || !hidden || !form || editor.dataset.quillReady === '1') {
				return;
			}

			editor.dataset.quillReady = '1';

			var quill = new Quill(editor, {
				theme: 'snow',
				modules: {
					toolbar: [
						['bold', 'italic', 'underline'],
						[{ list: 'ordered' }, { list: 'bullet' }],
						['link'],
						['clean']
					]
				},
				placeholder: 'Vertel kort waar je informatie over wilt ontvangen...'
			});

			if (hidden.value) {
				quill.clipboard.dangerouslyPasteHTML(hidden.value);
			}

			quill.on('text-change', function () {
				hidden.value = quill.root.innerHTML;
			});

			form.addEventListener('submit', function () {
				hidden.value = quill.root.innerHTML;
			});
		}

		initPmsQuill();
	})();
	</script>
	<?php

	return ob_get_clean();
}
