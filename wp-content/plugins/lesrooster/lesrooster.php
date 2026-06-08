<?php
/**
 * Plugin Name: Lesrooster
 * Description: Beheer groepstrainingen via WordPress en toon een stijlvol weekrooster met de shortcode [lesrooster].
 * Version: 1.0.0
 * Author: Wouter Meens
 * Text Domain: lesrooster
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Lesrooster_Plugin {
	private const VERSION = '1.0.0';
	private const POST_TYPE = 'lr_lesson';
	private const RESERVATION_POST_TYPE = 'lr_reservation';
	private const NONCE_ACTION = 'lr_save_lesson_meta';
	private const NONCE_NAME = 'lr_lesson_nonce';
	private const RESERVATION_NONCE_ACTION = 'lr_submit_reservation';
	private const RESERVATION_NONCE_NAME = 'lr_reservation_nonce';

	private $days = [
		'maandag' => 'Maandag',
		'dinsdag' => 'Dinsdag',
		'woensdag' => 'Woensdag',
		'donderdag' => 'Donderdag',
		'vrijdag' => 'Vrijdag',
		'zaterdag' => 'Zaterdag',
		'zondag' => 'Zondag',
	];

	public function __construct() {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_action( 'add_meta_boxes', [ $this, 'register_meta_boxes' ] );
		add_action( 'save_post_' . self::POST_TYPE, [ $this, 'save_meta_boxes' ] );
		add_action( 'admin_head', [ $this, 'render_admin_head_styles' ] );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', [ $this, 'register_admin_columns' ] );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', [ $this, 'render_admin_columns' ], 10, 2 );
		add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', [ $this, 'register_sortable_columns' ] );
		add_action( 'pre_get_posts', [ $this, 'handle_admin_sorting' ] );
		add_action( 'admin_post_nopriv_lr_submit_reservation', [ $this, 'handle_reservation_submission' ] );
		add_action( 'admin_post_lr_submit_reservation', [ $this, 'handle_reservation_submission' ] );
		add_shortcode( 'lesrooster', [ $this, 'render_shortcode' ] );
	}

	public function register_post_type(): void {
		$labels = [
			'name' => 'Lessen',
			'singular_name' => 'Les',
			'menu_name' => 'Lesrooster',
			'add_new' => 'Nieuwe les',
			'add_new_item' => 'Nieuwe les toevoegen',
			'edit_item' => 'Les bewerken',
			'new_item' => 'Nieuwe les',
			'view_item' => 'Les bekijken',
			'search_items' => 'Lessen zoeken',
			'not_found' => 'Geen lessen gevonden',
			'not_found_in_trash' => 'Geen lessen in de prullenbak',
		];

		register_post_type(
			self::POST_TYPE,
			[
				'labels' => $labels,
				'public' => false,
				'show_ui' => true,
				'show_in_menu' => true,
				'menu_position' => 25,
				'menu_icon' => 'dashicons-calendar-alt',
				'supports' => [ 'title' ],
				'show_in_rest' => false,
			]
		);

		register_post_type(
			self::RESERVATION_POST_TYPE,
			[
				'labels' => [
					'name' => 'Reserveringen',
					'singular_name' => 'Reservering',
					'menu_name' => 'Reserveringen',
					'add_new' => 'Nieuwe reservering',
					'add_new_item' => 'Nieuwe reservering',
					'edit_item' => 'Reservering bekijken',
					'view_item' => 'Reservering bekijken',
					'search_items' => 'Reserveringen zoeken',
					'not_found' => 'Geen reserveringen gevonden',
					'not_found_in_trash' => 'Geen reserveringen in de prullenbak',
				],
				'public' => false,
				'show_ui' => true,
				'show_in_menu' => 'edit.php?post_type=' . self::POST_TYPE,
				'supports' => [ 'title' ],
				'show_in_rest' => false,
			]
		);
	}

	public function register_meta_boxes(): void {
		add_meta_box(
			'lr_lesson_details',
			'Lesgegevens',
			[ $this, 'render_meta_box' ],
			self::POST_TYPE,
			'normal',
			'high'
		);
	}

	public function render_meta_box( \WP_Post $post ): void {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		$values = [
			'location' => get_post_meta( $post->ID, '_lr_location', true ),
			'trainer' => get_post_meta( $post->ID, '_lr_trainer', true ),
			'level' => get_post_meta( $post->ID, '_lr_level', true ),
			'schedule_lines' => get_post_meta( $post->ID, '_lr_schedule_lines', true ),
		];

		if ( '' === $values['schedule_lines'] ) {
			$legacy_day = get_post_meta( $post->ID, '_lr_day', true );
			$legacy_start = get_post_meta( $post->ID, '_lr_start_time', true );
			$legacy_end = get_post_meta( $post->ID, '_lr_end_time', true );

			if ( $legacy_day && $legacy_start && $legacy_end ) {
				$values['schedule_lines'] = sprintf(
					'%s %s uur - %s uur',
					$this->days[ $legacy_day ] ?? ucfirst( $legacy_day ),
					$legacy_start,
					$legacy_end
				);
			}
		}
		?>
		<style>
			.lr-admin-grid {
				display: grid;
				grid-template-columns: repeat(2, minmax(220px, 1fr));
				gap: 18px;
			}
			.lr-admin-field label {
				display: block;
				font-weight: 600;
				margin-bottom: 6px;
			}
			.lr-admin-field input,
			.lr-admin-field select,
			.lr-admin-field textarea {
				width: 100%;
			}
			.lr-admin-field--full {
				grid-column: 1 / -1;
			}
			.lr-admin-help {
				margin-top: 18px;
				padding: 14px 16px;
				background: #f6f7fb;
				border-left: 4px solid #6a11b1;
			}
			.lr-admin-example {
				margin-top: 8px;
				padding: 12px 14px;
				background: #ffffff;
				border: 1px solid #d8deea;
				border-radius: 8px;
				font-family: monospace;
				font-size: 13px;
				line-height: 1.6;
				white-space: pre-line;
			}
		</style>
		<div class="lr-admin-grid">
			<p class="lr-admin-field">
				<label for="lr_level">Label of niveau</label>
				<input type="text" id="lr_level" name="lr_level" value="<?php echo esc_attr( $values['level'] ); ?>" placeholder="Bijv. Small group of All levels">
			</p>

			<p class="lr-admin-field">
				<label for="lr_location">Locatie</label>
				<input type="text" id="lr_location" name="lr_location" value="<?php echo esc_attr( $values['location'] ); ?>" placeholder="Bijv. Lansingerland Fit Studio">
			</p>

			<p class="lr-admin-field">
				<label for="lr_trainer">Trainer</label>
				<input type="text" id="lr_trainer" name="lr_trainer" value="<?php echo esc_attr( $values['trainer'] ); ?>" placeholder="Bijv. Coach Lisa">
			</p>

			<div class="lr-admin-field lr-admin-field--full">
				<label>Momenten (dag, aanvangstijd, eindtijd)</label>
				<style>
					.lr-time-rows { margin-top:6px; }
					.lr-time-row-head, .lr-time-row { display:grid; grid-template-columns:140px 90px 90px 32px; gap:8px; align-items:center; margin-bottom:6px; }
					.lr-time-row-head span { font-size:11px; font-weight:600; color:#555; text-transform:uppercase; letter-spacing:.04em; }
					.lr-time-row select, .lr-time-row input[type="text"] { width:100%; }
					.lr-remove-row { background:#fef2f2; border:1px solid #fca5a5; border-radius:4px; color:#dc2626; cursor:pointer; font-size:14px; padding:4px 6px; }
					.lr-remove-row:hover { background:#fee2e2; }
					#lr-add-row { margin-top:8px; background:#f0fdf4; border:1px solid #86efac; border-radius:4px; color:#16a34a; cursor:pointer; font-size:13px; padding:6px 12px; }
					#lr-add-row:hover { background:#dcfce7; }
				</style>
				<div class="lr-time-rows">
					<div class="lr-time-row-head">
						<span>Dag</span><span>Aanvang</span><span>Einde</span><span></span>
					</div>
					<div id="lr-time-row-list">
						<?php
						$existing_entries = $this->parse_schedule_lines( $values['schedule_lines'] );
						foreach ( $existing_entries as $entry ) :
							preg_match( '/(\d{1,2}:\d{2})(?:\s*uur)?\s*[-–]\s*(\d{1,2}:\d{2})(?:\s*uur)?/u', $entry['time'], $tm );
							$sv = isset( $tm[1] ) ? esc_attr( $tm[1] ) : '';
							$ev = isset( $tm[2] ) ? esc_attr( $tm[2] ) : '';
						?>
						<div class="lr-time-row">
							<select name="lr_time_day[]">
								<?php foreach ( $this->days as $key => $label ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $entry['day'], $key ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
							<input type="text" name="lr_time_start[]" value="<?php echo $sv; ?>" placeholder="9:00">
							<input type="text" name="lr_time_end[]" value="<?php echo $ev; ?>" placeholder="10:00">
							<button type="button" class="lr-remove-row" onclick="this.closest('.lr-time-row').remove()">✕</button>
						</div>
						<?php endforeach; ?>
					</div>
				</div>
				<button type="button" id="lr-add-row">+ Moment toevoegen</button>
				<script>
				(function() {
					const days = <?php echo wp_json_encode( $this->days ); ?>;
					document.getElementById('lr-add-row').addEventListener('click', function() {
						const list = document.getElementById('lr-time-row-list');
						const row  = document.createElement('div');
						row.className = 'lr-time-row';
						const opts = Object.entries(days).map(([k,v]) => `<option value="${k}">${v}</option>`).join('');
						row.innerHTML = `<select name="lr_time_day[]">${opts}</select>`
							+ `<input type="text" name="lr_time_start[]" placeholder="9:00">`
							+ `<input type="text" name="lr_time_end[]" placeholder="10:00">`
							+ `<button type="button" class="lr-remove-row" onclick="this.closest('.lr-time-row').remove()">✕</button>`;
						list.appendChild(row);
					});
				})();
				</script>
			</div>
		</div>
		<div class="lr-admin-help">
			<strong>Werkwijze:</strong> gebruik de titel als activiteit, vul locatie en trainer eenmalig in, en voeg per dag een moment toe via de tabel hierboven.<br>
			<strong>Reserveren:</strong> op de website verschijnt automatisch een knop <code>Reserveer</code>. De bezoeker krijgt dan alleen een e-mailveld te zien.<br>
			<strong>Shortcode:</strong> gebruik <code>[lesrooster]</code> op een pagina of in Elementor.
		</div>
		<?php
	}

	public function save_meta_boxes( int $post_id ): void {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$schedule_lines = '';
		if ( isset( $_POST['lr_time_day'] ) && is_array( $_POST['lr_time_day'] ) ) {
			$days_post  = array_map( 'sanitize_text_field', wp_unslash( $_POST['lr_time_day'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$starts     = isset( $_POST['lr_time_start'] ) && is_array( $_POST['lr_time_start'] )
				? array_map( 'sanitize_text_field', wp_unslash( $_POST['lr_time_start'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$ends       = isset( $_POST['lr_time_end'] ) && is_array( $_POST['lr_time_end'] )
				? array_map( 'sanitize_text_field', wp_unslash( $_POST['lr_time_end'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$lines = [];
			foreach ( $days_post as $i => $day ) {
				$day = strtolower( trim( $day ) );
				if ( ! isset( $this->days[ $day ] ) ) {
					continue;
				}
				$start = trim( $starts[ $i ] ?? '' );
				$end   = trim( $ends[ $i ]   ?? '' );
				if ( '' === $start ) {
					continue;
				}
				$label     = $this->days[ $day ];
				$time_str  = '' !== $end ? "{$start} uur - {$end} uur" : "{$start} uur";
				$lines[]   = "{$label} {$time_str}";
			}
			$schedule_lines = implode( "\n", $lines );
		}

		$fields = [
			'_lr_location'       => isset( $_POST['lr_location'] ) ? sanitize_text_field( wp_unslash( $_POST['lr_location'] ) ) : '',
			'_lr_trainer'        => isset( $_POST['lr_trainer'] ) ? sanitize_text_field( wp_unslash( $_POST['lr_trainer'] ) ) : '',
			'_lr_level'          => isset( $_POST['lr_level'] ) ? sanitize_text_field( wp_unslash( $_POST['lr_level'] ) ) : '',
			'_lr_schedule_lines' => $schedule_lines,
		];

		foreach ( $fields as $meta_key => $value ) {
			if ( '' === $value ) {
				delete_post_meta( $post_id, $meta_key );
				continue;
			}

			update_post_meta( $post_id, $meta_key, $value );
		}
	}

	public function render_admin_head_styles(): void {
		$screen = get_current_screen();

		if ( ! $screen || self::POST_TYPE !== $screen->post_type ) {
			return;
		}
		?>
		<style>
			.column-lr_schedule { width: 180px; }
			.column-lr_day { width: 120px; }
			.column-lr_trainer { width: 150px; }
		</style>
		<?php
	}

	public function register_admin_columns( array $columns ): array {
		$new_columns = [];

		foreach ( $columns as $key => $label ) {
			$new_columns[ $key ] = $label;

			if ( 'title' === $key ) {
				$new_columns['lr_day'] = 'Dag';
				$new_columns['lr_schedule'] = 'Tijd';
				$new_columns['lr_trainer'] = 'Trainer';
				$new_columns['lr_location'] = 'Locatie';
			}
		}

		return $new_columns;
	}

	public function render_admin_columns( string $column, int $post_id ): void {
		switch ( $column ) {
			case 'lr_day':
				$schedule_entries = $this->parse_schedule_lines( (string) get_post_meta( $post_id, '_lr_schedule_lines', true ) );
				$labels = [];

				foreach ( $schedule_entries as $entry ) {
					$labels[] = $this->days[ $entry['day'] ] ?? ucfirst( $entry['day'] );
				}

				$labels = array_unique( $labels );
				echo esc_html( $labels ? implode( ', ', $labels ) : '-' );
				break;
			case 'lr_schedule':
				$schedule_entries = $this->parse_schedule_lines( (string) get_post_meta( $post_id, '_lr_schedule_lines', true ) );
				$times = array_map(
					static function ( array $entry ): string {
						return $entry['time'];
					},
					array_slice( $schedule_entries, 0, 2 )
				);

				echo esc_html( $times ? implode( ' | ', $times ) : '-' );
				break;
			case 'lr_trainer':
				echo esc_html( get_post_meta( $post_id, '_lr_trainer', true ) ?: '-' );
				break;
			case 'lr_location':
				echo esc_html( get_post_meta( $post_id, '_lr_location', true ) ?: '-' );
				break;
		}
	}

	public function register_sortable_columns( array $columns ): array {
		return $columns;
	}

	public function handle_admin_sorting( \WP_Query $query ): void {
		unset( $query );
	}

	public function render_shortcode( array $atts ): string {
		$atts = shortcode_atts(
			[
				'title' => 'Lesrooster groepstrainingen',
				'button_text' => 'Aanmelden',
			],
			$atts,
			'lesrooster'
		);

		wp_enqueue_style(
			'lesrooster-style',
			plugin_dir_url( __FILE__ ) . 'assets/lesrooster.css',
			[],
			file_exists( plugin_dir_path( __FILE__ ) . 'assets/lesrooster.css' )
				? filemtime( plugin_dir_path( __FILE__ ) . 'assets/lesrooster.css' )
				: self::VERSION
		);

		$lessons = get_posts(
			[
				'post_type' => self::POST_TYPE,
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'orderby' => 'title',
				'order' => 'ASC',
			]
		);

		$grouped_lessons = $this->group_lessons_by_day( $lessons );
		$status_message = $this->get_frontend_status_message();
		$weekday_keys = [ 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag' ];
		$weekend_keys = [ 'zaterdag', 'zondag' ];

		ob_start();
		?>
		<section class="lr-schedule">
			<div class="lr-schedule__intro">
				<h2 class="lr-schedule__title"><?php echo esc_html( $atts['title'] ); ?></h2>
			</div>

			<?php if ( $status_message ) : ?>
				<div class="lr-schedule__notice lr-schedule__notice--<?php echo esc_attr( $status_message['type'] ); ?>">
					<?php echo esc_html( $status_message['message'] ); ?>
				</div>
			<?php endif; ?>

			<div class="lr-schedule__toggle" role="tablist" aria-label="Selecteer lesroosterweergave">
				<button type="button" class="lr-schedule__toggle-button is-active" data-lr-toggle="weekday" aria-pressed="true">Doordeweeks</button>
				<button type="button" class="lr-schedule__toggle-button" data-lr-toggle="weekend" aria-pressed="false">Weekend</button>
			</div>

			<div class="lr-schedule__panel is-active" data-lr-panel="weekday">
				<div class="lr-schedule__grid lr-schedule__grid--weekday">
					<?php foreach ( $weekday_keys as $day_key ) : ?>
						<?php $day_label = $this->days[ $day_key ]; ?>
						<?php $day_lessons = $grouped_lessons[ $day_key ]; ?>
						<div class="lr-day-card">
							<div class="lr-day-card__header">
								<h3><?php echo esc_html( $day_label ); ?></h3>
								<span><?php echo esc_html( count( $day_lessons ) ); ?> lessen</span>
							</div>

							<?php if ( empty( $day_lessons ) ) : ?>
								<p class="lr-day-card__empty">Nog geen training ingepland.</p>
							<?php else : ?>
								<div class="lr-day-card__list">
									<?php foreach ( $day_lessons as $lesson ) : ?>
										<?php echo $this->render_lesson( $lesson, $day_label, $atts['button_text'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="lr-schedule__panel" data-lr-panel="weekend" hidden>
				<div class="lr-schedule__grid lr-schedule__grid--weekend">
					<?php foreach ( $weekend_keys as $day_key ) : ?>
						<?php $day_label = $this->days[ $day_key ]; ?>
						<?php $day_lessons = $grouped_lessons[ $day_key ]; ?>
						<div class="lr-day-card">
							<div class="lr-day-card__header">
								<h3><?php echo esc_html( $day_label ); ?></h3>
								<span><?php echo esc_html( count( $day_lessons ) ); ?> lessen</span>
							</div>

							<?php if ( empty( $day_lessons ) ) : ?>
								<p class="lr-day-card__empty">Nog geen training ingepland.</p>
							<?php else : ?>
								<div class="lr-day-card__list">
									<?php foreach ( $day_lessons as $lesson ) : ?>
										<?php echo $this->render_lesson( $lesson, $day_label, $atts['button_text'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="lr-reservation-modal" data-lr-modal hidden>
				<div class="lr-reservation-modal__backdrop" data-lr-close-modal></div>
				<div class="lr-reservation-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="lr-reservation-title">
					<button type="button" class="lr-reservation-modal__close" aria-label="Sluiten" data-lr-close-modal>&times;</button>
					<p class="lr-reservation-modal__eyebrow">Reservering</p>
					<h3 id="lr-reservation-title" class="lr-reservation-modal__title">Reserveer jouw training</h3>
					<div class="lr-reservation-modal__summary">
						<div class="lr-modal-row">
							<span class="lr-modal-row__icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/></svg></span>
							<span class="lr-modal-row__text" data-lr-summary-activity></span>
						</div>
						<div class="lr-modal-row">
							<span class="lr-modal-row__icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg></span>
							<span class="lr-modal-row__text" data-lr-summary-day></span>
						</div>
						<div class="lr-modal-row">
							<span class="lr-modal-row__icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></span>
							<span class="lr-modal-row__text"><span class="lr-modal-row__label">Van:</span> <span data-lr-summary-start></span></span>
						</div>
						<div class="lr-modal-row" data-lr-modal-row="end">
							<span class="lr-modal-row__icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg></span>
							<span class="lr-modal-row__text"><span class="lr-modal-row__label">Tot:</span> <span data-lr-summary-end></span></span>
						</div>
						<div class="lr-modal-row" data-lr-modal-row="location">
							<span class="lr-modal-row__icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg></span>
							<span class="lr-modal-row__text" data-lr-summary-location></span>
						</div>
						<div class="lr-modal-row" data-lr-modal-row="trainer">
							<span class="lr-modal-row__icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg></span>
							<span class="lr-modal-row__text" data-lr-summary-trainer></span>
						</div>
					</div>

					<form class="lr-reservation-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="lr_submit_reservation">
						<input type="hidden" name="redirect_to" value="<?php echo esc_url( $this->get_current_url() ); ?>">
						<input type="hidden" name="activity" value="" data-lr-field-activity>
						<input type="hidden" name="day" value="" data-lr-field-day>
						<input type="hidden" name="time" value="" data-lr-field-time>
						<input type="hidden" name="location" value="" data-lr-field-location>
						<input type="hidden" name="trainer" value="" data-lr-field-trainer>
						<?php wp_nonce_field( self::RESERVATION_NONCE_ACTION, self::RESERVATION_NONCE_NAME ); ?>

						<label class="lr-reservation-form__label" for="lr-reservation-email">E-mailadres</label>
						<input class="lr-reservation-form__input" type="email" id="lr-reservation-email" name="email" required placeholder="jouw@emailadres.nl">
						<p class="lr-reservation-form__hint">Na het verzenden slaan we je reservering op en sturen we een bevestiging naar dit e-mailadres.</p>

						<button type="submit" class="lr-reservation-form__submit">Bevestig reservering</button>
					</form>
				</div>
			</div>
		</section>
		<script>
		(function () {
		  const scope = document.currentScript ? document.currentScript.previousElementSibling : null;
		  if (!scope) return;

		  const modal = scope.querySelector('[data-lr-modal]');
		  if (!modal) return;
		  const toggleButtons = scope.querySelectorAll('[data-lr-toggle]');
		  const panels = scope.querySelectorAll('[data-lr-panel]');

		  const emailInput       = modal.querySelector('#lr-reservation-email');
		  const summaryActivity  = modal.querySelector('[data-lr-summary-activity]');
		  const summaryDay       = modal.querySelector('[data-lr-summary-day]');
		  const summaryStart     = modal.querySelector('[data-lr-summary-start]');
		  const summaryEnd       = modal.querySelector('[data-lr-summary-end]');
		  const summaryLocation  = modal.querySelector('[data-lr-summary-location]');
		  const summaryTrainer   = modal.querySelector('[data-lr-summary-trainer]');
		  const rowEnd           = modal.querySelector('[data-lr-modal-row="end"]');
		  const rowLocation      = modal.querySelector('[data-lr-modal-row="location"]');
		  const rowTrainer       = modal.querySelector('[data-lr-modal-row="trainer"]');
		  const activityField    = modal.querySelector('[data-lr-field-activity]');
		  const dayField         = modal.querySelector('[data-lr-field-day]');
		  const timeField        = modal.querySelector('[data-lr-field-time]');
		  const locationField    = modal.querySelector('[data-lr-field-location]');
		  const trainerField     = modal.querySelector('[data-lr-field-trainer]');

		  const openModal = (button) => {
		    const activity = button.getAttribute('data-lr-activity') || '';
		    const day      = button.getAttribute('data-lr-day')      || '';
		    const time     = button.getAttribute('data-lr-time')     || '';
		    const location = button.getAttribute('data-lr-location') || '';
		    const trainer  = button.getAttribute('data-lr-trainer')  || '';

		    const timeMatch = time.match(/(\d{1,2}:\d{2}\s*uur)\s*[-–]\s*(\d{1,2}:\d{2}\s*uur)/i);
		    const startTime = timeMatch ? timeMatch[1] : time;
		    const endTime   = timeMatch ? timeMatch[2] : '';

		    if (summaryActivity) summaryActivity.textContent = activity;
		    if (summaryDay)      summaryDay.textContent      = day;
		    if (summaryStart)    summaryStart.textContent    = startTime;
		    if (summaryEnd)      summaryEnd.textContent      = endTime;
		    if (summaryLocation) summaryLocation.textContent = location || 'Nog niet opgegeven';
		    if (summaryTrainer)  summaryTrainer.textContent  = trainer;

		    if (rowEnd)      rowEnd.hidden      = !endTime;
		    if (rowLocation) rowLocation.hidden = !location;
		    if (rowTrainer)  rowTrainer.hidden  = !trainer;

		    activityField.value = activity;
		    dayField.value      = day;
		    timeField.value     = time;
		    locationField.value = location;
		    trainerField.value  = trainer;

		    modal.hidden = false;
		    document.body.classList.add('lr-modal-open');

		    window.setTimeout(() => {
		      if (emailInput) emailInput.focus();
		    }, 30);
		  };

		  const closeModal = () => {
		    modal.hidden = true;
		    document.body.classList.remove('lr-modal-open');
		  };

		  const switchPanel = (target) => {
		    toggleButtons.forEach((button) => {
		      const isActive = button.getAttribute('data-lr-toggle') === target;
		      button.classList.toggle('is-active', isActive);
		      button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
		    });

		    panels.forEach((panel) => {
		      const isActive = panel.getAttribute('data-lr-panel') === target;
		      panel.classList.toggle('is-active', isActive);
		      panel.hidden = !isActive;
		    });
		  };

		  toggleButtons.forEach((button) => {
		    button.addEventListener('click', () => switchPanel(button.getAttribute('data-lr-toggle')));
		  });

		  scope.querySelectorAll('[data-lr-open-reservation]').forEach((button) => {
		    button.addEventListener('click', () => openModal(button));
		  });

		  modal.querySelectorAll('[data-lr-close-modal]').forEach((node) => {
		    node.addEventListener('click', closeModal);
		  });

		  document.addEventListener('keydown', (event) => {
		    if (event.key === 'Escape' && !modal.hidden) {
		      closeModal();
		    }
		  });

		  const equalizeRows = () => {
		    scope.querySelectorAll('.lr-schedule__grid').forEach((grid) => {
		      const lists = Array.from(grid.querySelectorAll('.lr-day-card__list'));
		      if (lists.length < 2) return;
		      lists.forEach((list) => {
		        Array.from(list.children).forEach((card) => { card.style.minHeight = ''; });
		      });
		      const maxLessons = Math.max(...lists.map((l) => l.children.length));
		      for (let i = 0; i < maxLessons; i++) {
		        const rowCards = lists.map((list) => list.children[i]).filter(Boolean);
		        const maxH = Math.max(...rowCards.map((c) => c.offsetHeight));
		        rowCards.forEach((c) => { c.style.minHeight = maxH + 'px'; });
		      }
		    });
		  };

		  window.addEventListener('load', equalizeRows);
		  window.addEventListener('resize', () => {
		    clearTimeout(window._lrResizeTimer);
		    window._lrResizeTimer = setTimeout(equalizeRows, 150);
		  });
		})();
		</script>
		<?php

		return (string) ob_get_clean();
	}

	public function handle_reservation_submission(): void {
		$redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : home_url( '/' );

		if ( ! isset( $_POST[ self::RESERVATION_NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::RESERVATION_NONCE_NAME ] ) ), self::RESERVATION_NONCE_ACTION ) ) {
			$this->safe_redirect( $redirect_to, [ 'lr_status' => 'invalid_nonce' ] );
		}

		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$activity = isset( $_POST['activity'] ) ? sanitize_text_field( wp_unslash( $_POST['activity'] ) ) : '';
		$day = isset( $_POST['day'] ) ? sanitize_text_field( wp_unslash( $_POST['day'] ) ) : '';
		$time = isset( $_POST['time'] ) ? sanitize_text_field( wp_unslash( $_POST['time'] ) ) : '';
		$location = isset( $_POST['location'] ) ? sanitize_text_field( wp_unslash( $_POST['location'] ) ) : '';
		$trainer = isset( $_POST['trainer'] ) ? sanitize_text_field( wp_unslash( $_POST['trainer'] ) ) : '';

		if ( ! is_email( $email ) || '' === $activity || '' === $day || '' === $time ) {
			$this->safe_redirect( $redirect_to, [ 'lr_status' => 'invalid_submission' ] );
		}

		$reservation_title = sprintf(
			'%s - %s %s - %s',
			$activity,
			$day,
			$time,
			$email
		);

		$reservation_id = wp_insert_post(
			[
				'post_type' => self::RESERVATION_POST_TYPE,
				'post_status' => 'publish',
				'post_title' => $reservation_title,
			],
			true
		);

		if ( is_wp_error( $reservation_id ) ) {
			$this->safe_redirect( $redirect_to, [ 'lr_status' => 'save_error' ] );
		}

		update_post_meta( $reservation_id, '_lr_reservation_email', $email );
		update_post_meta( $reservation_id, '_lr_reservation_activity', $activity );
		update_post_meta( $reservation_id, '_lr_reservation_day', $day );
		update_post_meta( $reservation_id, '_lr_reservation_time', $time );
		update_post_meta( $reservation_id, '_lr_reservation_location', $location );
		update_post_meta( $reservation_id, '_lr_reservation_trainer', $trainer );

		$this->send_reservation_emails(
			[
				'email' => $email,
				'activity' => $activity,
				'day' => $day,
				'time' => $time,
				'location' => $location,
				'trainer' => $trainer,
			]
		);

		$this->safe_redirect( $redirect_to, [ 'lr_status' => 'success' ] );
	}

	/**
	 * @param \WP_Post[] $lessons
	 * @return array<string, array<int, array<string, string>>>
	 */
	private function group_lessons_by_day( array $lessons ): array {
		$grouped = [];

		foreach ( array_keys( $this->days ) as $day_key ) {
			$grouped[ $day_key ] = [];
		}

		foreach ( $lessons as $lesson ) {
			$schedule_entries = $this->parse_schedule_lines( (string) get_post_meta( $lesson->ID, '_lr_schedule_lines', true ) );

			if ( [] === $schedule_entries ) {
				$legacy_day = get_post_meta( $lesson->ID, '_lr_day', true );
				$legacy_start = (string) get_post_meta( $lesson->ID, '_lr_start_time', true );
				$legacy_end = (string) get_post_meta( $lesson->ID, '_lr_end_time', true );

				if ( $legacy_day && $legacy_start && $legacy_end ) {
					$schedule_entries[] = [
						'day' => $legacy_day,
						'time' => trim( $legacy_start . ' uur - ' . $legacy_end . ' uur', ' -' ),
						'sort_time' => $legacy_start,
					];
				}
			}

			foreach ( $schedule_entries as $entry ) {
				$day = $entry['day'];

				if ( ! isset( $grouped[ $day ] ) ) {
					continue;
				}

				$grouped[ $day ][] = [
					'title' => $lesson->post_title,
					'time' => $entry['time'],
					'start_time' => $entry['sort_time'],
					'location' => (string) get_post_meta( $lesson->ID, '_lr_location', true ),
					'trainer' => (string) get_post_meta( $lesson->ID, '_lr_trainer', true ),
					'level' => (string) get_post_meta( $lesson->ID, '_lr_level', true ),
				];
			}
		}

		foreach ( $grouped as $day_key => $day_lessons ) {
			usort(
				$day_lessons,
				static function ( array $lesson_a, array $lesson_b ): int {
					return strcmp( $lesson_a['start_time'], $lesson_b['start_time'] );
				}
			);

			$grouped[ $day_key ] = $day_lessons;
		}

		return $grouped;
	}

	/**
	 * @return array<int, array{day:string,time:string,sort_time:string}>
	 */
	private function parse_schedule_lines( string $raw_schedule ): array {
		$entries = [];
		$lines = preg_split( '/\r\n|\r|\n/', trim( $raw_schedule ) );

		if ( ! is_array( $lines ) ) {
			return $entries;
		}

		$day_pattern = implode( '|', array_keys( $this->days ) );

		foreach ( $lines as $line ) {
			$line = trim( $line );

			if ( '' === $line ) {
				continue;
			}

			if ( preg_match( '/^(' . $day_pattern . ')\s+(.+)$/iu', $line, $matches ) ) {
				$day = strtolower( $matches[1] );
				$time = trim( $matches[2] );
				$sort_time = $this->extract_sort_time( $time );

				$entries[] = [
					'day' => $day,
					'time' => $time,
					'sort_time' => $sort_time,
				];
			}
		}

		return $entries;
	}

	private function extract_sort_time( string $time_label ): string {
		if ( preg_match( '/(\d{1,2}):(\d{2})/', $time_label, $matches ) ) {
			return sprintf( '%02d:%02d', (int) $matches[1], (int) $matches[2] );
		}

		return '99:99';
	}

	/**
	 * @return array{type:string,message:string}|null
	 */
	private function get_frontend_status_message() {
		$status = isset( $_GET['lr_status'] ) ? sanitize_key( wp_unslash( $_GET['lr_status'] ) ) : '';

		if ( 'success' === $status ) {
			return [
				'type' => 'success',
				'message' => 'Je reservering is ontvangen. We hebben een bevestiging naar je e-mailadres gestuurd.',
			];
		}

		if ( 'invalid_submission' === $status ) {
			return [
				'type' => 'error',
				'message' => 'De reservering kon niet worden verstuurd. Controleer je e-mailadres en probeer het opnieuw.',
			];
		}

		if ( 'invalid_nonce' === $status || 'save_error' === $status ) {
			return [
				'type' => 'error',
				'message' => 'Er ging iets mis met het verwerken van je reservering. Probeer het opnieuw.',
			];
		}

		return null;
	}

	private function send_reservation_emails( array $reservation ): void {
		$admin_email = get_option( 'admin_email' );
		$subject_admin = sprintf( 'Nieuwe reservering voor %s', $reservation['activity'] );
		$subject_user = sprintf( 'Bevestiging reservering %s', $reservation['activity'] );

		$headers = [
			'Content-Type: text/plain; charset=UTF-8',
			'From: Lansingerland Fit <' . $admin_email . '>',
		];

		$message_lines = [
			'Activiteit: ' . $reservation['activity'],
			'Moment: ' . $reservation['day'] . ' ' . $reservation['time'],
			'Locatie: ' . ( $reservation['location'] ?: 'Niet opgegeven' ),
			'Trainer: ' . ( $reservation['trainer'] ?: 'Niet opgegeven' ),
			'E-mail: ' . $reservation['email'],
		];

		wp_mail( $admin_email, $subject_admin, implode( PHP_EOL, $message_lines ), $headers );

		$user_message = array_merge(
			[
				'Bedankt voor je reservering bij Lansingerland Fit.',
				'',
				'We hebben onderstaande training voor je ontvangen:',
			],
			$message_lines
		);

		wp_mail( $reservation['email'], $subject_user, implode( PHP_EOL, $user_message ), $headers );
	}

	private function get_current_url(): string {
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
		return home_url( $request_uri );
	}

	private function render_lesson( array $lesson, string $day_label, string $button_text ): string {
		$time_parts     = $this->parse_time_parts( $lesson['time'] );
		$short_location = $lesson['location'] ? $this->get_short_location( $lesson['location'] ) : '';

		$icon_tag   = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="14" height="14" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/></svg>';
		$icon_clock = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="14" height="14" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>';
		$icon_arrow = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="14" height="14" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>';
		$icon_pin   = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="14" height="14" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>';
		$icon_user  = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="14" height="14" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>';

		$rows = '';
		if ( $lesson['level'] ) {
			$rows .= '<div class="lr-lesson__row"><span class="lr-lesson__icon">' . $icon_tag . '</span><span class="lr-lesson__row-text">' . esc_html( $lesson['level'] ) . '</span></div>';
		}
		if ( $time_parts['start'] ) {
			$rows .= '<div class="lr-lesson__row"><span class="lr-lesson__icon">' . $icon_clock . '</span><span class="lr-lesson__row-text"><span class="lr-lesson__row-label">Van:</span> ' . esc_html( $time_parts['start'] ) . '</span></div>';
		}
		if ( $time_parts['end'] ) {
			$rows .= '<div class="lr-lesson__row"><span class="lr-lesson__icon">' . $icon_arrow . '</span><span class="lr-lesson__row-text"><span class="lr-lesson__row-label">Tot:</span> ' . esc_html( $time_parts['end'] ) . '</span></div>';
		}
		if ( $short_location ) {
			$rows .= '<div class="lr-lesson__row"><span class="lr-lesson__icon">' . $icon_pin . '</span><span class="lr-lesson__row-text">' . esc_html( $short_location ) . '</span></div>';
		}
		if ( $lesson['trainer'] ) {
			$rows .= '<div class="lr-lesson__row"><span class="lr-lesson__icon">' . $icon_user . '</span><span class="lr-lesson__row-text">' . esc_html( $lesson['trainer'] ) . '</span></div>';
		}

		return sprintf(
			'<article class="lr-lesson">'
			. '<h4 class="lr-lesson__title">%s</h4>'
			. '<div class="lr-lesson__rows">%s</div>'
			. '<button type="button" class="lr-lesson__button" data-lr-open-reservation '
			. 'data-lr-activity="%s" data-lr-day="%s" data-lr-time="%s" '
			. 'data-lr-location="%s" data-lr-trainer="%s">%s</button>'
			. '</article>',
			esc_html( $lesson['title'] ),
			$rows, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_attr( $lesson['title'] ),
			esc_attr( $day_label ),
			esc_attr( $lesson['time'] ),
			esc_attr( $lesson['location'] ),
			esc_attr( $lesson['trainer'] ),
			esc_html( $button_text )
		);
	}

	private function parse_time_parts( string $time ): array {
		if ( preg_match( '/(\d{1,2}:\d{2}\s*uur)\s*[-–]\s*(\d{1,2}:\d{2}\s*uur)/iu', $time, $matches ) ) {
			return [ 'start' => trim( $matches[1] ), 'end' => trim( $matches[2] ) ];
		}
		return [ 'start' => $time, 'end' => '' ];
	}

	private function get_short_location( string $location ): string {
		$parts = explode( ' - ', $location, 2 );
		return trim( $parts[0] );
	}

	private function safe_redirect( string $url, array $args = [] ): void {
		$target = add_query_arg( $args, $url );
		wp_safe_redirect( $target );
		exit;
	}
}

new Lesrooster_Plugin();
