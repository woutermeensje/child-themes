<?php
if (!defined('ABSPATH')) exit;

class VP_AJAX {

  public static function register() {
    add_action('wp_ajax_vp_filter_jobs', [ __CLASS__, 'handle' ]);
    add_action('wp_ajax_nopriv_vp_filter_jobs', [ __CLASS__, 'handle' ]);
  }

  public static function handle() {
    check_ajax_referer('vp_jobs_nonce', 'nonce');

    $per_page = (int) vp_get_req_string('per_page', 12);
    $paged    = (int) vp_get_req_string('paged', 1);

    $selected = [
      'job_type'  => vp_clean_slugs(vp_get_req_array('job_type')),
      'categorie' => vp_clean_slugs(vp_get_req_array('categorie')),
      'org_type'  => vp_clean_slugs(vp_get_req_array('org_type')),
      'regio'     => vp_clean_slugs(vp_get_req_array('regio')),
      'bedrijfsnaam' => vp_clean_slugs(vp_get_req_array('bedrijfsnaam')),
    ];

    $keywords = vp_get_req_string('search_keywords', '');
    $location = vp_get_req_string('search_location', '');

    $q = self::build_query([
      'keywords' => $keywords,
      'location' => $location,
      'selected' => $selected,
      'per_page' => $per_page,
      'paged'    => $paged,
    ]);

    wp_send_json_success([
      'html' => vp_template('listings.php', [ 'query' => $q ]),
    ]);
  }

  public static function build_query($args) {
    $per_page = max(1, (int)($args['per_page'] ?? 12));
    $paged    = max(1, (int)($args['paged'] ?? 1));
    $keywords = (string)($args['keywords'] ?? '');
    $location = (string)($args['location'] ?? '');
    $selected = (array)($args['selected'] ?? []);

    $tax_query = [ 'relation' => 'AND' ];

    // mapping: request key => taxonomy
    $map = [
      'job_type'  => 'vp_job_type',
      'categorie' => 'vp_category',
      'org_type'  => 'vp_org_type',
      'regio'     => 'vp_regio',
      'bedrijfsnaam' => 'bedrijfsnaam',
    ];

    foreach ($map as $key => $tax) {
      $vals = isset($selected[$key]) ? vp_clean_slugs($selected[$key]) : [];
      if (!empty($vals)) {
        $tax_query[] = [
          'taxonomy' => $tax,
          'field'    => 'slug',
          'terms'    => $vals,
          'operator' => 'IN',
        ];
      }
    }

    $meta_query = [ 'relation' => 'AND' ];

    // simpele locatie-filter (tekstveld in meta)
    if ($location !== '') {
      $meta_query[] = [
        'key'     => '_vp_location',
        'value'   => $location,
        'compare' => 'LIKE',
      ];
    }

    $qargs = [
      'post_type'      => 'vp_vacature',
      'post_status'    => 'publish',
      'posts_per_page' => $per_page,
      'paged'          => $paged,
      's'              => $keywords,
      'tax_query'      => count($tax_query) > 1 ? $tax_query : [],
      'meta_query'     => count($meta_query) > 1 ? $meta_query : [],
    ];

    return new WP_Query($qargs);
  }
}