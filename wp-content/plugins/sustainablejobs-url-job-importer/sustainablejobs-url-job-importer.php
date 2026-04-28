<?php
/**
 * Plugin Name: Sustainablejobs URL Job Importer
 * Description: Importeert een vacature via een URL en maakt direct een WP Job Manager conceptvacature aan.
 * Version: 0.1.0
 * Author: Sustainablejobs.nl
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SJ_URL_Job_Importer {
	const MENU_SLUG             = 'sj-url-job-importer';
	const NONCE_ACTION          = 'sj_url_job_importer_import';
	const NONCE_NAME            = 'sj_url_job_importer_nonce';
	const SOURCE_URL_META       = '_sj_import_source_url';
	const IMPORTED_AT_META      = '_sj_imported_at';
	const EXTRACTED_COMPANY_META = '_sj_extracted_company_name';
	const EXTRACTION_METHOD_META = '_sj_extraction_method';
	const NOTICE_TRANSIENT      = 'sj_url_job_importer_notice_';

	public static function init() {
		add_action( 'admin_menu', [ __CLASS__, 'register_admin_page' ] );
		add_action( 'admin_post_sj_url_job_importer_import', [ __CLASS__, 'handle_import' ] );
	}

	public static function register_admin_page() {
		add_submenu_page(
			'edit.php?post_type=job_listing',
			'Vacature via URL importeren',
			'Import via URL',
			apply_filters( 'sj_url_job_importer_capability', 'manage_options' ),
			self::MENU_SLUG,
			[ __CLASS__, 'render_admin_page' ]
		);
	}

	public static function render_admin_page() {
		if ( ! current_user_can( apply_filters( 'sj_url_job_importer_capability', 'manage_options' ) ) ) {
			wp_die( esc_html__( 'Je hebt geen toestemming om deze pagina te bekijken.', 'sj-url-job-importer' ) );
		}

		$notice = self::pull_notice();
		$taxonomy = self::company_taxonomy();
		$terms = taxonomy_exists( $taxonomy ) ? self::get_company_terms( $taxonomy ) : [];
		?>
		<div class="wrap sj-url-job-importer">
			<h1>Vacature importeren via URL</h1>

			<?php if ( $notice ) : ?>
				<div class="notice notice-<?php echo esc_attr( $notice['type'] ); ?> is-dismissible">
					<p><?php echo wp_kses_post( $notice['message'] ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( ! post_type_exists( 'job_listing' ) ) : ?>
				<div class="notice notice-error">
					<p>WP Job Manager lijkt niet actief te zijn. De post type <code>job_listing</code> bestaat niet.</p>
				</div>
			<?php elseif ( ! taxonomy_exists( $taxonomy ) ) : ?>
				<div class="notice notice-error">
					<p>De bedrijfs-taxonomie <code><?php echo esc_html( $taxonomy ); ?></code> bestaat niet. Controleer of het Sustainablejobs theme actief is.</p>
				</div>
			<?php else : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="sj_url_job_importer_import">
					<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME ); ?>

					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row">
									<label for="sj_job_url">Vacature URL</label>
								</th>
								<td>
									<input type="url" id="sj_job_url" name="sj_job_url" class="regular-text code" placeholder="https://..." required>
									<p class="description">De bronpagina waar de vacature op staat.</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="sj_company_term_id">Organisatie</label>
								</th>
								<td>
									<select id="sj_company_term_id" name="sj_company_term_id" required>
										<option value="">Selecteer organisatie</option>
										<?php foreach ( $terms as $term ) : ?>
											<option value="<?php echo esc_attr( $term->term_id ); ?>"><?php echo esc_html( $term->name ); ?></option>
										<?php endforeach; ?>
									</select>
									<p class="description">Deze organisatie wordt gekoppeld aan <code>job_company</code> en gebruikt als bedrijfsnaam in WP Job Manager.</p>
								</td>
							</tr>
						</tbody>
					</table>

					<?php submit_button( 'Conceptvacature aanmaken' ); ?>
				</form>

				<style>
					.sj-url-job-importer select {
						min-width: 320px;
					}
					.sj-url-job-importer .regular-text {
						width: min(620px, 100%);
					}
				</style>
			<?php endif; ?>
		</div>
		<?php
	}

	public static function handle_import() {
		if ( ! current_user_can( apply_filters( 'sj_url_job_importer_capability', 'manage_options' ) ) ) {
			wp_die( esc_html__( 'Je hebt geen toestemming om vacatures te importeren.', 'sj-url-job-importer' ) );
		}

		check_admin_referer( self::NONCE_ACTION, self::NONCE_NAME );

		$taxonomy = self::company_taxonomy();
		$url = isset( $_POST['sj_job_url'] ) ? esc_url_raw( trim( wp_unslash( $_POST['sj_job_url'] ) ) ) : '';
		$company_term_id = isset( $_POST['sj_company_term_id'] ) ? absint( $_POST['sj_company_term_id'] ) : 0;

		if ( ! post_type_exists( 'job_listing' ) ) {
			self::redirect_with_notice( 'error', 'WP Job Manager lijkt niet actief te zijn.' );
		}

		if ( ! taxonomy_exists( $taxonomy ) ) {
			self::redirect_with_notice( 'error', 'De bedrijfs-taxonomie bestaat niet.' );
		}

		if ( ! self::is_valid_import_url( $url ) ) {
			self::redirect_with_notice( 'error', 'Vul een geldige http(s)-URL in.' );
		}

		$company_term = get_term( $company_term_id, $taxonomy );
		if ( ! $company_term || is_wp_error( $company_term ) ) {
			self::redirect_with_notice( 'error', 'Selecteer een geldige organisatie.' );
		}

		$existing = self::find_existing_import( $url );
		if ( $existing ) {
			$edit_link = get_edit_post_link( $existing, '' );
			self::redirect_with_notice(
				'warning',
				sprintf(
					'Deze URL is al geimporteerd als vacature <a href="%s">#%d</a>.',
					esc_url( $edit_link ),
					(int) $existing
				)
			);
		}

		$fetch = self::fetch_url( $url );
		if ( is_wp_error( $fetch ) ) {
			self::redirect_with_notice( 'error', $fetch->get_error_message() );
		}

		$extracted = self::extract_job_data( $fetch['body'], $url );
		$job_id = self::create_job_listing( $url, $company_term, $taxonomy, $extracted );

		if ( is_wp_error( $job_id ) ) {
			self::redirect_with_notice( 'error', $job_id->get_error_message() );
		}

		$summary = self::create_success_summary( $job_id, $extracted );
		self::redirect_with_notice( 'success', $summary );
	}

	private static function company_taxonomy() {
		return apply_filters( 'sj_url_job_importer_company_taxonomy', 'job_company' );
	}

	private static function admin_page_url() {
		return admin_url( 'edit.php?post_type=job_listing&page=' . self::MENU_SLUG );
	}

	private static function notice_key() {
		return self::NOTICE_TRANSIENT . get_current_user_id();
	}

	private static function pull_notice() {
		$notice = get_transient( self::notice_key() );
		delete_transient( self::notice_key() );

		if ( ! is_array( $notice ) || empty( $notice['message'] ) || empty( $notice['type'] ) ) {
			return null;
		}

		return $notice;
	}

	private static function redirect_with_notice( $type, $message ) {
		set_transient(
			self::notice_key(),
			[
				'type'    => sanitize_key( $type ),
				'message' => (string) $message,
			],
			MINUTE_IN_SECONDS
		);

		wp_safe_redirect( self::admin_page_url() );
		exit;
	}

	private static function get_company_terms( $taxonomy ) {
		$terms = get_terms(
			[
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			]
		);

		return is_wp_error( $terms ) ? [] : $terms;
	}

	private static function is_valid_import_url( $url ) {
		if ( ! self::is_absolute_http_url( $url ) ) {
			return false;
		}

		$parts = wp_parse_url( $url );
		$host = strtolower( trim( $parts['host'], '.' ) );

		if ( isset( $parts['user'] ) || isset( $parts['pass'] ) || false !== strpbrk( $host, ':#?[]' ) ) {
			return false;
		}

		if ( in_array( $host, [ 'localhost', 'localhost.localdomain' ], true ) || '.local' === substr( $host, -6 ) ) {
			return false;
		}

		if ( filter_var( $host, FILTER_VALIDATE_IP ) && ! self::is_public_ip( $host ) ) {
			return false;
		}

		return true;
	}

	private static function is_absolute_http_url( $url ) {
		if ( ! is_string( $url ) || '' === trim( $url ) || is_numeric( $url ) ) {
			return false;
		}

		$parts = wp_parse_url( $url );
		if ( ! is_array( $parts ) || empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
			return false;
		}

		return in_array( strtolower( (string) $parts['scheme'] ), [ 'http', 'https' ], true );
	}

	private static function is_public_ip( $ip ) {
		return (bool) filter_var(
			$ip,
			FILTER_VALIDATE_IP,
			FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
		);
	}

	private static function find_existing_import( $url ) {
		$existing = get_posts(
			[
				'post_type'      => 'job_listing',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => self::SOURCE_URL_META,
				'meta_value'     => $url,
			]
		);

		return ! empty( $existing[0] ) ? (int) $existing[0] : 0;
	}

	private static function fetch_url( $url ) {
		$response = wp_remote_get(
			$url,
			[
				'timeout'             => 20,
				'redirection'         => 5,
				'limit_response_size' => 5242880,
				'user-agent'          => 'Sustainablejobs.nl Job Importer; ' . home_url( '/' ),
				'headers'             => [
					'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'sj_import_fetch_failed', 'De URL kon niet worden opgehaald: ' . $response->get_error_message() );
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 400 ) {
			return new WP_Error( 'sj_import_bad_status', 'De bronpagina gaf HTTP-status ' . $code . ' terug.' );
		}

		$body = wp_remote_retrieve_body( $response );
		if ( '' === trim( (string) $body ) ) {
			return new WP_Error( 'sj_import_empty_body', 'De bronpagina gaf geen HTML terug.' );
		}

		return [
			'body' => self::normalize_html_encoding( $body ),
			'url'  => $url,
		];
	}

	private static function normalize_html_encoding( $html ) {
		if ( function_exists( 'mb_detect_encoding' ) && function_exists( 'mb_convert_encoding' ) ) {
			$encoding = mb_detect_encoding( $html, [ 'UTF-8', 'Windows-1252', 'ISO-8859-1' ], true );
			if ( $encoding && 'UTF-8' !== $encoding ) {
				$html = mb_convert_encoding( $html, 'UTF-8', $encoding );
			}
		}

		return $html;
	}

	private static function extract_job_data( $html, $source_url ) {
		$document = self::load_document( $html );
		$xpath = new DOMXPath( $document );

		$data = [
			'title'             => '',
			'description'       => '',
			'company_name'      => '',
			'company_website'   => '',
			'application'       => '',
			'location'          => '',
			'employment_types'  => [],
			'salary'            => '',
			'salary_currency'   => '',
			'salary_unit'       => '',
			'hours'             => '',
			'logo'              => '',
			'remote'            => false,
			'extraction_method' => 'html',
		];

		$json_ld_data = self::extract_json_ld_job_data( $xpath, $source_url );
		if ( ! empty( $json_ld_data ) ) {
			$data = array_merge( $data, array_filter( $json_ld_data, [ __CLASS__, 'not_empty_value' ] ) );
			$data['extraction_method'] = 'json-ld';
		}

		$html_data = self::extract_html_job_data( $document, $xpath, $source_url );
		foreach ( $html_data as $key => $value ) {
			if ( self::not_empty_value( $value ) && ! self::not_empty_value( $data[ $key ] ?? null ) ) {
				$data[ $key ] = $value;
			}
		}

		$data['application'] = $data['application'] ? self::absolute_url( $data['application'], $source_url ) : $source_url;
		$data['company_website'] = $data['company_website'] ? self::absolute_url( $data['company_website'], $source_url ) : $data['application'];
		$data['logo'] = $data['logo'] ? self::absolute_url( $data['logo'], $source_url ) : '';

		if ( empty( $data['employment_types'] ) ) {
			$data['employment_types'] = self::detect_employment_types_from_text( wp_strip_all_tags( $data['description'] ) );
		}

		if ( empty( $data['salary'] ) ) {
			$data['salary'] = self::detect_salary_from_text( wp_strip_all_tags( $data['description'] ) );
		}

		if ( empty( $data['hours'] ) ) {
			$data['hours'] = self::detect_hours_from_text( wp_strip_all_tags( $data['description'] ) );
		}

		$data['title'] = self::clean_text( $data['title'] ?: 'Geimporteerde vacature' );
		$data['description'] = self::clean_description_html( $data['description'] );

		if ( ! $data['description'] ) {
			$data['description'] = sprintf(
				'<p>Deze vacature is geimporteerd vanaf <a href="%s" target="_blank" rel="noopener">de bronpagina</a>.</p>',
				esc_url( $source_url )
			);
		}

		return $data;
	}

	private static function not_empty_value( $value ) {
		if ( is_array( $value ) ) {
			return ! empty( array_filter( $value ) );
		}

		return null !== $value && '' !== trim( (string) $value );
	}

	private static function load_document( $html ) {
		$document = new DOMDocument();
		$previous = libxml_use_internal_errors( true );
		$document->loadHTML( '<?xml encoding="utf-8" ?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_NONET );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );

		return $document;
	}

	private static function extract_json_ld_job_data( DOMXPath $xpath, $source_url ) {
		$scripts = $xpath->query( '//script[contains(translate(@type, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "ld+json")]' );
		if ( ! $scripts || 0 === $scripts->length ) {
			return [];
		}

		foreach ( $scripts as $script ) {
			$json = trim( $script->textContent );
			if ( '' === $json ) {
				continue;
			}

			$decoded = json_decode( $json, true );
			if ( null === $decoded && preg_match( '/^\s*<!--(.*)-->\s*$/s', $json, $matches ) ) {
				$decoded = json_decode( trim( $matches[1] ), true );
			}

			if ( null === $decoded ) {
				continue;
			}

			$jobs = [];
			self::collect_job_posting_nodes( $decoded, $jobs );

			if ( empty( $jobs ) ) {
				continue;
			}

			return self::map_json_ld_job( $jobs[0], $source_url );
		}

		return [];
	}

	private static function collect_job_posting_nodes( $node, &$jobs ) {
		if ( ! is_array( $node ) ) {
			return;
		}

		if ( self::json_ld_is_job_posting( $node ) ) {
			$jobs[] = $node;
			return;
		}

		foreach ( $node as $value ) {
			if ( is_array( $value ) ) {
				self::collect_job_posting_nodes( $value, $jobs );
			}
		}
	}

	private static function json_ld_is_job_posting( $node ) {
		if ( empty( $node['@type'] ) ) {
			return false;
		}

		$types = (array) $node['@type'];
		foreach ( $types as $type ) {
			if ( is_string( $type ) && false !== stripos( $type, 'JobPosting' ) ) {
				return true;
			}
		}

		return false;
	}

	private static function map_json_ld_job( $job, $source_url ) {
		$organization = self::first_array_value( $job['hiringOrganization'] ?? [] );
		$salary = self::map_salary( $job['baseSalary'] ?? [] );

		$application = self::first_string(
			[
				$job['applicationUrl'] ?? '',
				$job['applicationURL'] ?? '',
				$job['applyUrl'] ?? '',
				$job['url'] ?? '',
			]
		);

		$company_website = self::first_string(
			[
				$organization['url'] ?? '',
				$organization['sameAs'] ?? '',
			]
		);

		return [
			'title'            => self::first_string( [ $job['title'] ?? '', $job['name'] ?? '' ] ),
			'description'      => is_string( $job['description'] ?? null ) ? $job['description'] : '',
			'company_name'     => self::first_string( [ $organization['name'] ?? '' ] ),
			'company_website'  => $company_website,
			'application'      => $application ? self::absolute_url( $application, $source_url ) : $source_url,
			'location'         => self::map_location( $job ),
			'employment_types' => self::normalize_employment_types( $job['employmentType'] ?? [] ),
			'salary'           => $salary['salary'],
			'salary_currency'  => $salary['currency'],
			'salary_unit'      => $salary['unit'],
			'hours'            => self::first_string( [ $job['workHours'] ?? '', $job['workHoursPerWeek'] ?? '' ] ),
			'logo'             => self::first_string( [ $organization['logo'] ?? '' ] ),
			'remote'           => self::is_remote_job( $job ),
		];
	}

	private static function map_location( $job ) {
		if ( ! empty( $job['jobLocation'] ) ) {
			$locations = self::array_list( $job['jobLocation'] );
			$parts = [];

			foreach ( $locations as $location ) {
				if ( is_string( $location ) ) {
					$parts[] = $location;
					continue;
				}

				if ( ! is_array( $location ) ) {
					continue;
				}

				$address = $location['address'] ?? $location;
				if ( is_string( $address ) ) {
					$parts[] = $address;
					continue;
				}

				if ( is_array( $address ) ) {
					$parts[] = self::join_non_empty(
						[
							$address['addressLocality'] ?? '',
							$address['addressRegion'] ?? '',
							$address['addressCountry'] ?? '',
						],
						', '
					);
				}
			}

			$location = self::join_non_empty( $parts, ' / ' );
			if ( $location ) {
				return $location;
			}
		}

		if ( ! empty( $job['applicantLocationRequirements'] ) ) {
			$requirements = self::array_list( $job['applicantLocationRequirements'] );
			$parts = [];
			foreach ( $requirements as $requirement ) {
				if ( is_array( $requirement ) && ! empty( $requirement['name'] ) ) {
					$parts[] = $requirement['name'];
				} elseif ( is_string( $requirement ) ) {
					$parts[] = $requirement;
				}
			}

			return self::join_non_empty( $parts, ' / ' );
		}

		return '';
	}

	private static function map_salary( $salary_data ) {
		$result = [
			'salary'   => '',
			'currency' => '',
			'unit'     => '',
		];

		if ( empty( $salary_data ) ) {
			return $result;
		}

		$salary_data = self::first_array_value( $salary_data );
		$result['currency'] = self::first_string( [ $salary_data['currency'] ?? '', $salary_data['salaryCurrency'] ?? '' ] );
		$value = $salary_data['value'] ?? $salary_data;

		if ( is_numeric( $value ) ) {
			$result['salary'] = (string) $value;
			return $result;
		}

		if ( ! is_array( $value ) ) {
			return $result;
		}

		$min = $value['minValue'] ?? '';
		$max = $value['maxValue'] ?? '';
		$single = $value['value'] ?? '';
		$result['unit'] = self::first_string( [ $value['unitText'] ?? '', $salary_data['unitText'] ?? '' ] );

		if ( '' !== (string) $min && '' !== (string) $max ) {
			$result['salary'] = trim( $result['currency'] . ' ' . $min . ' - ' . $max );
		} elseif ( '' !== (string) $single ) {
			$result['salary'] = trim( $result['currency'] . ' ' . $single );
		}

		return $result;
	}

	private static function is_remote_job( $job ) {
		$type = $job['jobLocationType'] ?? '';
		if ( is_array( $type ) ) {
			$type = implode( ' ', array_filter( $type, 'is_scalar' ) );
		}

		return is_string( $type ) && false !== stripos( $type, 'TELECOMMUTE' );
	}

	private static function extract_html_job_data( DOMDocument $document, DOMXPath $xpath, $source_url ) {
		$title = self::extract_title( $xpath );
		$description = self::extract_description( $document, $xpath );
		$text = wp_strip_all_tags( $description );

		return [
			'title'            => $title,
			'description'      => $description,
			'company_name'     => self::extract_meta_content( $xpath, [ 'company', 'organization', 'hiringOrganization' ] ),
			'company_website'  => self::extract_meta_content( $xpath, [ 'company:url', 'organization:url' ] ),
			'application'      => self::extract_application_url( $xpath, $source_url ),
			'location'         => self::extract_location( $xpath ),
			'employment_types' => self::detect_employment_types_from_text( $text ),
			'salary'           => self::detect_salary_from_text( $text ),
			'salary_currency'  => '',
			'salary_unit'      => '',
			'hours'            => self::detect_hours_from_text( $text ),
			'logo'             => self::extract_meta_content( $xpath, [ 'og:image', 'twitter:image' ] ),
			'remote'           => false !== stripos( $text, 'remote' ) || false !== stripos( $text, 'thuiswerk' ),
		];
	}

	private static function extract_title( DOMXPath $xpath ) {
		$title = self::node_text( self::first_node( $xpath, '//h1' ) );
		if ( $title ) {
			return $title;
		}

		$meta_title = self::extract_meta_content( $xpath, [ 'og:title', 'twitter:title' ] );
		if ( $meta_title ) {
			return $meta_title;
		}

		return self::node_text( self::first_node( $xpath, '//title' ) );
	}

	private static function extract_description( DOMDocument $document, DOMXPath $xpath ) {
		$class_id = 'translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz")';
		$queries = [
			'//*[contains(' . $class_id . ', "job-description") or contains(' . $class_id . ', "vacature-description") or contains(' . $class_id . ', "vacancy-description") or contains(' . $class_id . ', "functieomschrijving")]',
			'//*[contains(' . $class_id . ', "job-content") or contains(' . $class_id . ', "vacancy-content") or contains(' . $class_id . ', "vacature-content") or contains(' . $class_id . ', "job-details")]',
			'//*[contains(' . $class_id . ', "wysiwyg") or contains(' . $class_id . ', "description") or contains(' . $class_id . ', "omschrijving")]',
			'//article',
			'//main',
		];

		foreach ( $queries as $query ) {
			$best = self::best_content_node( $xpath->query( $query ) );
			if ( $best ) {
				return self::node_html( $document, $best );
			}
		}

		$body = self::first_node( $xpath, '//body' );
		return $body ? self::node_html( $document, $body ) : '';
	}

	private static function best_content_node( $nodes ) {
		if ( ! $nodes || 0 === $nodes->length ) {
			return null;
		}

		$best = null;
		$best_score = 0;

		foreach ( $nodes as $node ) {
			$text = self::clean_text( $node->textContent );
			$length = strlen( $text );

			if ( $length < 250 ) {
				continue;
			}

			$score = min( $length, 60000 );
			if ( $score > $best_score ) {
				$best = $node;
				$best_score = $score;
			}
		}

		return $best;
	}

	private static function extract_meta_content( DOMXPath $xpath, array $names ) {
		foreach ( $names as $name ) {
			$quoted = self::xpath_literal( strtolower( $name ) );
			$query = '//meta[translate(@property, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = ' . $quoted . ' or translate(@name, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = ' . $quoted . ' or translate(@itemprop, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = ' . $quoted . ']/@content';
			$value = self::node_text( self::first_node( $xpath, $query ) );
			if ( $value ) {
				return $value;
			}
		}

		return '';
	}

	private static function extract_application_url( DOMXPath $xpath, $source_url ) {
		$queries = [
			'//a[contains(translate(normalize-space(.), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "solliciteer")]/@href',
			'//a[contains(translate(normalize-space(.), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "apply")]/@href',
			'//a[contains(translate(@class, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "apply")]/@href',
			'//a[contains(translate(@href, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "apply")]/@href',
		];

		foreach ( $queries as $query ) {
			$url = self::node_text( self::first_node( $xpath, $query ) );
			if ( $url ) {
				return self::absolute_url( $url, $source_url );
			}
		}

		return $source_url;
	}

	private static function extract_location( DOMXPath $xpath ) {
		$queries = [
			'//*[@itemprop="jobLocation"]',
			'//*[contains(translate(@class, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "location")]',
			'//*[contains(translate(@class, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "locatie")]',
			'//*[contains(translate(@data-automation, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "location")]',
		];

		foreach ( $queries as $query ) {
			$nodes = $xpath->query( $query );
			if ( ! $nodes ) {
				continue;
			}

			foreach ( $nodes as $node ) {
				$text = self::clean_text( $node->textContent );
				if ( $text && strlen( $text ) <= 140 ) {
					return $text;
				}
			}
		}

		return '';
	}

	private static function clean_description_html( $html ) {
		if ( ! $html ) {
			return '';
		}

		$document = self::load_document( '<div id="sj-import-root">' . $html . '</div>' );
		$xpath = new DOMXPath( $document );
		foreach ( [ '//script', '//style', '//noscript', '//iframe', '//form', '//nav', '//header', '//footer', '//svg' ] as $query ) {
			$nodes = $xpath->query( $query );
			if ( ! $nodes ) {
				continue;
			}

			for ( $i = $nodes->length - 1; $i >= 0; $i-- ) {
				$node = $nodes->item( $i );
				if ( $node && $node->parentNode ) {
					$node->parentNode->removeChild( $node );
				}
			}
		}

		$root = self::first_node( $xpath, '//*[@id="sj-import-root"]' );
		$cleaned = $root ? self::children_html( $document, $root ) : $html;
		$cleaned = preg_replace( '/\s(?:class|id|style|onclick|onload|data-[a-z0-9_-]+)="[^"]*"/i', '', $cleaned );
		$cleaned = preg_replace( "/\s(?:class|id|style|onclick|onload|data-[a-z0-9_-]+)='[^']*'/i", '', $cleaned );

		return wp_kses_post( trim( $cleaned ) );
	}

	private static function create_job_listing( $source_url, WP_Term $company_term, $taxonomy, array $data ) {
		$postarr = [
			'post_title'   => wp_strip_all_tags( $data['title'] ),
			'post_content' => wp_kses_post( $data['description'] ),
			'post_status'  => 'draft',
			'post_type'    => 'job_listing',
			'post_author'  => get_current_user_id(),
		];

		$job_id = wp_insert_post( $postarr, true );
		if ( is_wp_error( $job_id ) ) {
			return $job_id;
		}

		update_post_meta( $job_id, '_job_location', sanitize_text_field( $data['location'] ) );
		update_post_meta( $job_id, '_application', esc_url_raw( $data['application'] ) );
		update_post_meta( $job_id, '_company_name', sanitize_text_field( $company_term->name ) );
		update_post_meta( $job_id, '_company_website', esc_url_raw( $data['application'] ) );
		update_post_meta( $job_id, '_company_logo', esc_url_raw( $data['logo'] ) );
		update_post_meta( $job_id, '_job_salary', sanitize_text_field( $data['salary'] ) );
		update_post_meta( $job_id, '_job_salary_range', sanitize_text_field( $data['salary'] ) );
		update_post_meta( $job_id, '_job_hours_per_week', sanitize_text_field( $data['hours'] ) );
		update_post_meta( $job_id, '_remote_position', ! empty( $data['remote'] ) ? 1 : 0 );
		update_post_meta( $job_id, '_filled', 0 );
		update_post_meta( $job_id, '_featured', 0 );
		update_post_meta( $job_id, self::SOURCE_URL_META, esc_url_raw( $source_url ) );
		update_post_meta( $job_id, self::IMPORTED_AT_META, current_time( 'mysql' ) );
		update_post_meta( $job_id, self::EXTRACTED_COMPANY_META, sanitize_text_field( $data['company_name'] ) );
		update_post_meta( $job_id, self::EXTRACTION_METHOD_META, sanitize_key( $data['extraction_method'] ) );

		if ( ! empty( $data['salary_currency'] ) ) {
			update_post_meta( $job_id, '_job_salary_currency', sanitize_text_field( $data['salary_currency'] ) );
		}

		if ( ! empty( $data['salary_unit'] ) ) {
			update_post_meta( $job_id, '_job_salary_unit', sanitize_text_field( self::map_salary_unit( $data['salary_unit'] ) ) );
		}

		wp_set_object_terms( $job_id, [ (int) $company_term->term_id ], $taxonomy, false );
		self::assign_job_types( $job_id, $data['employment_types'] );

		do_action( 'sj_url_job_importer_created_job', $job_id, $source_url, $company_term, $data );

		return $job_id;
	}

	private static function assign_job_types( $job_id, array $employment_types ) {
		if ( ! taxonomy_exists( 'job_listing_type' ) || empty( $employment_types ) ) {
			return;
		}

		$term_ids = [];
		foreach ( $employment_types as $employment_type ) {
			$term = self::find_matching_job_type_term( $employment_type );
			if ( $term ) {
				$term_ids[] = (int) $term->term_id;
			}
		}

		if ( $term_ids ) {
			wp_set_object_terms( $job_id, array_values( array_unique( $term_ids ) ), 'job_listing_type', false );
		}
	}

	private static function find_matching_job_type_term( $employment_type ) {
		$slug_candidates = self::employment_type_slug_candidates( $employment_type );
		foreach ( $slug_candidates as $slug ) {
			$term = get_term_by( 'slug', $slug, 'job_listing_type' );
			if ( $term && ! is_wp_error( $term ) ) {
				return $term;
			}
		}

		foreach ( $slug_candidates as $name ) {
			$term = get_term_by( 'name', $name, 'job_listing_type' );
			if ( $term && ! is_wp_error( $term ) ) {
				return $term;
			}
		}

		return null;
	}

	private static function employment_type_slug_candidates( $employment_type ) {
		$normalized = strtolower( trim( str_replace( '_', '-', (string) $employment_type ) ) );
		$map = [
			'full-time'  => [ 'full-time', 'fulltime', 'full time', 'voltijd' ],
			'part-time'  => [ 'part-time', 'parttime', 'part time', 'deeltijd' ],
			'contractor' => [ 'freelance', 'contractor', 'zzp', 'contract' ],
			'temporary'  => [ 'temporary', 'tijdelijk' ],
			'internship' => [ 'internship', 'stage', 'stagiair' ],
		];

		foreach ( $map as $canonical => $candidates ) {
			if ( in_array( $normalized, $candidates, true ) ) {
				return array_unique( array_merge( [ sanitize_title( $canonical ) ], array_map( 'sanitize_title', $candidates ) ) );
			}
		}

		return [ sanitize_title( $normalized ) ];
	}

	private static function map_salary_unit( $unit ) {
		$unit = strtolower( trim( (string) $unit ) );
		$map = [
			'hour'  => 'HOUR',
			'day'   => 'DAY',
			'week'  => 'WEEK',
			'month' => 'MONTH',
			'year'  => 'YEAR',
			'uur'   => 'HOUR',
			'dag'   => 'DAY',
			'week'  => 'WEEK',
			'maand' => 'MONTH',
			'jaar'  => 'YEAR',
		];

		return $map[ $unit ] ?? $unit;
	}

	private static function create_success_summary( $job_id, array $data ) {
		$edit_link = get_edit_post_link( $job_id, '' );
		$parts = [
			sprintf(
				'Conceptvacature <a href="%s">#%d</a> is aangemaakt.',
				esc_url( $edit_link ),
				(int) $job_id
			),
		];

		$found = [];
		foreach ( [ 'title' => 'titel', 'description' => 'tekst', 'location' => 'locatie', 'application' => 'sollicitatielink', 'salary' => 'salaris', 'hours' => 'uren' ] as $key => $label ) {
			if ( self::not_empty_value( $data[ $key ] ?? null ) ) {
				$found[] = $label;
			}
		}

		if ( $found ) {
			$parts[] = 'Opgehaald: ' . esc_html( implode( ', ', $found ) ) . '.';
		}

		$parts[] = 'Methode: ' . esc_html( $data['extraction_method'] );

		return implode( ' ', $parts );
	}

	private static function first_node( DOMXPath $xpath, $query ) {
		$nodes = $xpath->query( $query );
		return ( $nodes && $nodes->length ) ? $nodes->item( 0 ) : null;
	}

	private static function node_text( $node ) {
		return $node ? self::clean_text( $node->textContent ) : '';
	}

	private static function clean_text( $text ) {
		$text = html_entity_decode( (string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$text = preg_replace( '/\s+/u', ' ', $text );
		return trim( $text );
	}

	private static function node_html( DOMDocument $document, DOMNode $node ) {
		return $document->saveHTML( $node );
	}

	private static function children_html( DOMDocument $document, DOMNode $node ) {
		$html = '';
		foreach ( $node->childNodes as $child ) {
			$html .= $document->saveHTML( $child );
		}

		return $html;
	}

	private static function first_string( array $values ) {
		foreach ( $values as $value ) {
			if ( is_array( $value ) ) {
				$value = self::first_string( $value );
			}

			if ( is_scalar( $value ) && '' !== trim( (string) $value ) ) {
				return trim( (string) $value );
			}
		}

		return '';
	}

	private static function first_array_value( $value ) {
		if ( ! is_array( $value ) ) {
			return [];
		}

		if ( isset( $value[0] ) && is_array( $value[0] ) ) {
			return $value[0];
		}

		return $value;
	}

	private static function array_list( $value ) {
		if ( ! is_array( $value ) ) {
			return [ $value ];
		}

		if ( array_keys( $value ) === range( 0, count( $value ) - 1 ) ) {
			return $value;
		}

		return [ $value ];
	}

	private static function join_non_empty( array $parts, $separator ) {
		$parts = array_map( [ __CLASS__, 'clean_text' ], $parts );
		$parts = array_filter( $parts );
		return implode( $separator, $parts );
	}

	private static function normalize_employment_types( $types ) {
		$types = self::array_list( $types );
		$normalized = [];

		foreach ( $types as $type ) {
			if ( is_string( $type ) ) {
				$normalized[] = strtolower( trim( $type ) );
			}
		}

		return array_values( array_unique( array_filter( $normalized ) ) );
	}

	private static function detect_employment_types_from_text( $text ) {
		$text = strtolower( remove_accents( (string) $text ) );
		$types = [];
		$checks = [
			'full-time'  => [ 'fulltime', 'full-time', 'full time', 'voltijd' ],
			'part-time'  => [ 'parttime', 'part-time', 'part time', 'deeltijd' ],
			'contractor' => [ 'freelance', 'zzp', 'contractor' ],
			'temporary'  => [ 'tijdelijk', 'temporary' ],
			'internship' => [ 'stage', 'internship', 'stagiair' ],
		];

		foreach ( $checks as $type => $needles ) {
			foreach ( $needles as $needle ) {
				if ( false !== strpos( $text, $needle ) ) {
					$types[] = $type;
					break;
				}
			}
		}

		return array_values( array_unique( $types ) );
	}

	private static function detect_salary_from_text( $text ) {
		$text = self::clean_text( $text );
		if ( preg_match( '/(?:EUR|€)\s?[\d\.\,]+(?:\s?(?:-|–|tot)\s?(?:EUR|€)?\s?[\d\.\,]+)?(?:\s?(?:per|\/)\s?(?:maand|jaar|uur|week))?/iu', $text, $matches ) ) {
			return $matches[0];
		}

		if ( preg_match( '/[\d\.\,]+\s?(?:-|–|tot)\s?[\d\.\,]+\s?(?:euro|eur)(?:\s?(?:per|\/)\s?(?:maand|jaar|uur|week))?/iu', $text, $matches ) ) {
			return $matches[0];
		}

		return '';
	}

	private static function detect_hours_from_text( $text ) {
		$text = self::clean_text( $text );
		if ( preg_match( '/\b\d{1,2}\s?(?:-|–|tot)\s?\d{1,2}\s?(?:uur|uren|hours)\b/iu', $text, $matches ) ) {
			return $matches[0];
		}

		if ( preg_match( '/\b\d{1,2}\s?(?:uur|uren|hours)\s?(?:per\s?week|\/week)?\b/iu', $text, $matches ) ) {
			return $matches[0];
		}

		return '';
	}

	private static function absolute_url( $url, $base_url ) {
		$url = trim( (string) $url );
		if ( '' === $url || 0 === strpos( $url, 'mailto:' ) ) {
			return $url;
		}

		if ( self::is_absolute_http_url( $url ) ) {
			return $url;
		}

		$base_parts = wp_parse_url( $base_url );
		if ( empty( $base_parts['scheme'] ) || empty( $base_parts['host'] ) ) {
			return $url;
		}

		if ( 0 === strpos( $url, '//' ) ) {
			return $base_parts['scheme'] . ':' . $url;
		}

		if ( 0 === strpos( $url, '/' ) ) {
			return $base_parts['scheme'] . '://' . $base_parts['host'] . $url;
		}

		$path = isset( $base_parts['path'] ) ? preg_replace( '#/[^/]*$#', '/', $base_parts['path'] ) : '/';
		return $base_parts['scheme'] . '://' . $base_parts['host'] . $path . $url;
	}

	private static function xpath_literal( $value ) {
		if ( false === strpos( $value, "'" ) ) {
			return "'" . $value . "'";
		}

		if ( false === strpos( $value, '"' ) ) {
			return '"' . $value . '"';
		}

		$parts = explode( "'", $value );
		return "concat('" . implode( "', \"'\", '", $parts ) . "')";
	}
}

SJ_URL_Job_Importer::init();
