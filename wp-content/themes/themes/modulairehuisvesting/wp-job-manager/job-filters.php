<?php
if ( ! defined( 'ABSPATH' ) ) exit;

wp_enqueue_script( 'wp-job-manager-ajax-filters' );
do_action( 'job_manager_job_filters_before', $atts );

$selected = [
  'job_types' => [],
];

$job_type_sources = [
  $_GET['filter_job_type'] ?? null,
  $_GET['filter_job_types'] ?? null,
  $_GET['job_types'] ?? null,
  $_GET['search_job_type'] ?? null,
  $_POST['filter_job_type'] ?? null,
  $_POST['filter_job_types'] ?? null,
  $_POST['job_types'] ?? null,
];

foreach ( $job_type_sources as $source ) {
  if ( empty( $source ) ) {
    continue;
  }

  $source = is_array( $source ) ? $source : explode( ',', (string) $source );
  $selected['job_types'] = array_values( array_filter( array_map( 'sanitize_title', wp_unslash( $source ) ) ) );
  break;
}

$keywords = isset( $keywords ) ? $keywords : ( $_GET['search_keywords'] ?? '' );
$location = isset( $location ) ? $location : ( $_GET['search_location'] ?? '' );

if ( ! function_exists( 'mh_get_open_job_filter_counts' ) ) {
  function mh_get_open_job_filter_counts( $taxonomy ) {
    static $cache = [];
    $taxonomy = sanitize_key( $taxonomy );
    if ( isset( $cache[$taxonomy] ) ) return $cache[$taxonomy];
    if ( ! taxonomy_exists( $taxonomy ) ) { $cache[$taxonomy] = []; return []; }

    global $wpdb;
    $sql = $wpdb->prepare(
      "SELECT tt.term_id, COUNT(DISTINCT p.ID) AS open_jobs
       FROM {$wpdb->term_relationships} tr
       INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
       INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
       LEFT JOIN {$wpdb->postmeta} filled ON filled.post_id = p.ID AND filled.meta_key = '_filled' AND filled.meta_value = '1'
       LEFT JOIN {$wpdb->postmeta} expires ON expires.post_id = p.ID AND expires.meta_key = '_job_expires'
       WHERE tt.taxonomy = %s
         AND p.post_type = 'job_listing'
         AND p.post_status = 'publish'
         AND filled.meta_id IS NULL
         AND (expires.meta_id IS NULL OR expires.meta_value = '' OR expires.meta_value >= %s)
       GROUP BY tt.term_id",
      $taxonomy,
      current_time( 'Y-m-d' )
    );
    $counts = [];
    foreach ( (array) $wpdb->get_results( $sql ) as $row ) {
      $counts[ (int) $row->term_id ] = (int) $row->open_jobs;
    }
    $cache[$taxonomy] = $counts;
    return $counts;
  }
}

$job_listing_types = get_job_listing_types();
$job_type_counts   = mh_get_open_job_filter_counts( 'job_listing_type' );
?>

<form class="job_filters">
  <?php do_action( 'job_manager_job_filters_start', $atts ); ?>

  <div class="filter-header">
    <h2>Werken bij Modulairehuisvesting</h2>
    <p>Bekijk onze openstaande vacatures en word onderdeel van ons team.</p>
  </div>

  <div class="search-basic mh-job-filter-row">
    <?php do_action( 'job_manager_job_filters_search_jobs_start', $atts ); ?>

    <div class="search_keywords">
      <input type="text" name="search_keywords" id="search_keywords"
             placeholder="Functienaam of afdeling.."
             value="<?php echo esc_attr( $keywords ); ?>" />
    </div>

    <div class="search_location">
      <input type="text" name="search_location" id="search_location"
             placeholder="Stad of locatie"
             value="<?php echo esc_attr( $location ); ?>" />
    </div>

    <!-- Dienstverband (MULTI) -->
    <div class="job_type">
      <select name="filter_job_type[]" id="filter_job_types"
              class="js-custom-select"
              data-placeholder="Dienstverband"
              data-wpjm-filter="job_type"
              multiple>
        <?php foreach ( $job_listing_types as $type ) :
          $count = $job_type_counts[ (int) $type->term_id ] ?? 0;
        ?>
          <option value="<?php echo esc_attr( $type->slug ); ?>"
            data-label="<?php echo esc_attr( $type->name ); ?>"
            data-count="<?php echo esc_attr( $count ); ?>"
            <?php selected( in_array( $type->slug, $selected['job_types'], true ) ); ?>>
            <?php echo esc_html( $type->name . ' (' . $count . ')' ); ?>
          </option>
        <?php endforeach; ?>
      </select>

      <div class="job_types mh-job-type-values" aria-hidden="true">
        <?php
        $sync_job_types = ! empty( $selected['job_types'] )
          ? $selected['job_types']
          : wp_list_pluck( $job_listing_types, 'slug' );
        ?>
        <?php foreach ( $sync_job_types as $job_type_slug ) : ?>
          <input type="checkbox" name="filter_job_type[]" value="<?php echo esc_attr( $job_type_slug ); ?>" checked>
        <?php endforeach; ?>
      </div>
    </div>

    <?php do_action( 'job_manager_job_filters_search_jobs_end', $atts ); ?>
  </div>

  <!-- Active filters (chips below the search row) -->
  <div class="active-filters" id="active-filters" aria-live="polite"></div>

</form>

<?php do_action( 'job_manager_job_filters_after', $atts ); ?>
