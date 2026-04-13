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
				<label for="lr_schedule_lines">Datum/Tijd van en tot</label>
				<textarea id="lr_schedule_lines" name="lr_schedule_lines" rows="6" placeholder="Maandag 9:00 uur - 10:00 uur&#10;Donderdag 9:00 uur - 10:00 uur"><?php echo esc_textarea( $values['schedule_lines'] ); ?></textarea>
				<div class="lr-admin-example">Voorbeeld:
Maandag 9:00 uur - 10:00 uur
Donderdag 9:00 uur - 10:00 uur</div>
			</div>
		</div>
		<div class="lr-admin-help">
			<strong>Werkwijze:</strong> gebruik de titel van dit bericht als activiteit, vul locatie en trainer eenmalig in, en zet alle dag/tijd-combinaties onder elkaar in het veld <code>Datum/Tijd van en tot</code>.<br>
			<strong>Reserveren:</strong> op de website verschijnt automatisch een standaard knop <code>Reserveer</code>. De bezoeker krijgt dan alleen een e-mailveld te zien.<br>
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

		$fields = [
			'_lr_location' => isset( $_POST['lr_location'] ) ? sanitize_text_field( wp_unslash( $_POST['lr_location'] ) ) : '',
			'_lr_trainer' => isset( $_POST['lr_trainer'] ) ? sanitize_text_field( wp_unslash( $_POST['lr_trainer'] ) ) : '',
			'_lr_level' => isset( $_POST['lr_level'] ) ? sanitize_text_field( wp_unslash( $_POST['lr_level'] ) ) : '',
			'_lr_schedule_lines' => isset( $_POST['lr_schedule_lines'] ) ? sanitize_textarea_field( wp_unslash( $_POST['lr_schedule_lines'] ) ) : '',
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
				'intro' => 'Bekijk hieronder per dag welke groepstrainingen er gepland staan.',
				'button_text' => 'Reserveer',
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
				<p class="lr-schedule__eyebrow">Groepstrainingen</p>
				<h2 class="lr-schedule__title"><?php echo esc_html( $atts['title'] ); ?></h2>
				<p class="lr-schedule__text"><?php echo esc_html( $atts['intro'] ); ?></p>
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
										<article class="lr-lesson">
											<p class="lr-lesson__time"><?php echo esc_html( $lesson['time'] ); ?></p>
											<h4 class="lr-lesson__title"><?php echo esc_html( $lesson['title'] ); ?></h4>

											<?php if ( $lesson['level'] ) : ?>
												<p class="lr-lesson__badge"><?php echo esc_html( $lesson['level'] ); ?></p>
											<?php endif; ?>

											<div class="lr-lesson__meta">
												<?php if ( $lesson['trainer'] ) : ?>
													<span>Trainer: <?php echo esc_html( $lesson['trainer'] ); ?></span>
												<?php endif; ?>
												<?php if ( $lesson['location'] ) : ?>
													<span>Locatie: <?php echo esc_html( $lesson['location'] ); ?></span>
												<?php endif; ?>
											</div>

											<button
												type="button"
												class="lr-lesson__button"
												data-lr-open-reservation
												data-lr-activity="<?php echo esc_attr( $lesson['title'] ); ?>"
												data-lr-day="<?php echo esc_attr( $day_label ); ?>"
												data-lr-time="<?php echo esc_attr( $lesson['time'] ); ?>"
												data-lr-location="<?php echo esc_attr( $lesson['location'] ); ?>"
												data-lr-trainer="<?php echo esc_attr( $lesson['trainer'] ); ?>"
											>
												<?php echo esc_html( $atts['button_text'] ); ?>
											</button>
										</article>
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
										<article class="lr-lesson">
											<p class="lr-lesson__time"><?php echo esc_html( $lesson['time'] ); ?></p>
											<h4 class="lr-lesson__title"><?php echo esc_html( $lesson['title'] ); ?></h4>

											<?php if ( $lesson['level'] ) : ?>
												<p class="lr-lesson__badge"><?php echo esc_html( $lesson['level'] ); ?></p>
											<?php endif; ?>

											<div class="lr-lesson__meta">
												<?php if ( $lesson['trainer'] ) : ?>
													<span>Trainer: <?php echo esc_html( $lesson['trainer'] ); ?></span>
												<?php endif; ?>
												<?php if ( $lesson['location'] ) : ?>
													<span>Locatie: <?php echo esc_html( $lesson['location'] ); ?></span>
												<?php endif; ?>
											</div>

											<button
												type="button"
												class="lr-lesson__button"
												data-lr-open-reservation
												data-lr-activity="<?php echo esc_attr( $lesson['title'] ); ?>"
												data-lr-day="<?php echo esc_attr( $day_label ); ?>"
												data-lr-time="<?php echo esc_attr( $lesson['time'] ); ?>"
												data-lr-location="<?php echo esc_attr( $lesson['location'] ); ?>"
												data-lr-trainer="<?php echo esc_attr( $lesson['trainer'] ); ?>"
											>
												<?php echo esc_html( $atts['button_text'] ); ?>
											</button>
										</article>
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
						<p><strong>Activiteit:</strong> <span data-lr-summary-activity></span></p>
						<p><strong>Moment:</strong> <span data-lr-summary-slot></span></p>
						<p><strong>Locatie:</strong> <span data-lr-summary-location></span></p>
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

		  const emailInput = modal.querySelector('#lr-reservation-email');
		  const summaryActivity = modal.querySelector('[data-lr-summary-activity]');
		  const summarySlot = modal.querySelector('[data-lr-summary-slot]');
		  const summaryLocation = modal.querySelector('[data-lr-summary-location]');
		  const activityField = modal.querySelector('[data-lr-field-activity]');
		  const dayField = modal.querySelector('[data-lr-field-day]');
		  const timeField = modal.querySelector('[data-lr-field-time]');
		  const locationField = modal.querySelector('[data-lr-field-location]');
		  const trainerField = modal.querySelector('[data-lr-field-trainer]');

		  const openModal = (button) => {
		    const activity = button.getAttribute('data-lr-activity') || '';
		    const day = button.getAttribute('data-lr-day') || '';
		    const time = button.getAttribute('data-lr-time') || '';
		    const location = button.getAttribute('data-lr-location') || '';
		    const trainer = button.getAttribute('data-lr-trainer') || '';

		    summaryActivity.textContent = activity;
		    summarySlot.textContent = [day, time].filter(Boolean).join(' ');
		    summaryLocation.textContent = location || 'Nog niet opgegeven';

		    activityField.value = activity;
		    dayField.value = day;
		    timeField.value = time;
		    locationField.value = location;
		    trainerField.value = trainer;

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

		$message_lines = [
			'Activiteit: ' . $reservation['activity'],
			'Moment: ' . $reservation['day'] . ' ' . $reservation['time'],
			'Locatie: ' . ( $reservation['location'] ?: 'Niet opgegeven' ),
			'Trainer: ' . ( $reservation['trainer'] ?: 'Niet opgegeven' ),
			'E-mail: ' . $reservation['email'],
		];

		wp_mail( $admin_email, $subject_admin, implode( PHP_EOL, $message_lines ) );

		$user_message = array_merge(
			[
				'Bedankt voor je reservering bij Lansingerland Fit.',
				'',
				'We hebben onderstaande training voor je ontvangen:',
			],
			$message_lines
		);

		wp_mail( $reservation['email'], $subject_user, implode( PHP_EOL, $user_message ) );
	}

	private function get_current_url(): string {
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
		return home_url( $request_uri );
	}

	private function safe_redirect( string $url, array $args = [] ): void {
		$target = add_query_arg( $args, $url );
		wp_safe_redirect( $target );
		exit;
	}
}

new Lesrooster_Plugin();
