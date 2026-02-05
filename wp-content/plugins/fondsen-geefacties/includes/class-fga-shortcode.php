<?php
if (!defined('ABSPATH')) exit;

final class FGA_Shortcode {

  public function __construct() {
    add_shortcode('fondsen_geefacties', [$this, 'render']);
  }

  public function render($atts): string {
    wp_enqueue_style('fga-geefacties');

    $atts = shortcode_atts([
      'per_page' => 18,
    ], $atts, 'fondsen_geefacties');

    $per_page = max(1, (int) $atts['per_page']);
    $paged    = max(1, (int) get_query_var('paged', 1));

    $filters = FGA_Query::get_filters_from_request();

    $themas = FGA_Query::get_terms(FGA_Plugin::TAX_THEMA);
    $types  = FGA_Query::get_terms(FGA_Plugin::TAX_TYPE);

    $q = FGA_Query::run($filters, $per_page, $paged);

    // Data voor templates
    $data = [
      'filters'  => $filters,
      'themas'   => $themas,
      'types'    => $types,
      'posts'    => $q->have_posts() ? $q->posts : [],
      'max_pages'=> (int) $q->max_num_pages,
      'per_page' => $per_page,
      'paged'    => $paged,
    ];

    ob_start();
    include FGA_PATH . 'templates/filter.php';
    include FGA_PATH . 'templates/listing.php';
    wp_reset_postdata();
    return (string) ob_get_clean();
  }
}
