<?php
if (!defined('ABSPATH')) exit;

final class FGA_Query {

  /**
   * Lees filters uit GET.
   * - q: search
   * - thema[]: multi
   * - type[]: multi
   */
  public static function get_filters_from_request(): array {
    $q = isset($_GET['fga_q']) ? sanitize_text_field(wp_unslash($_GET['fga_q'])) : '';

    $thema = [];
    if (isset($_GET['fga_thema'])) {
      $thema = (array) wp_unslash($_GET['fga_thema']);
      $thema = self::sanitize_slugs($thema);
    }

    $type = [];
    if (isset($_GET['fga_type'])) {
      $type = (array) wp_unslash($_GET['fga_type']);
      $type = self::sanitize_slugs($type);
    }

    return [
      'q'     => $q,
      'thema' => $thema,
      'type'  => $type,
    ];
  }

  public static function get_terms(string $taxonomy): array {
    $terms = get_terms([
      'taxonomy'   => $taxonomy,
      'hide_empty' => false,
    ]);

    if (is_wp_error($terms) || empty($terms)) return [];
    return $terms;
  }

  public static function run(array $filters, int $per_page = 18, int $paged = 1): WP_Query {
    $args = [
      'post_type'           => FGA_Plugin::CPT,
      'post_status'         => 'publish',
      'posts_per_page'      => max(1, $per_page),
      'paged'               => max(1, $paged),
      'orderby'             => 'date',
      'order'               => 'DESC',
      'ignore_sticky_posts' => true,
    ];

    if (!empty($filters['q'])) {
      $args['s'] = $filters['q'];
    }

    $tax_query = ['relation' => 'AND'];

    if (!empty($filters['thema'])) {
      $tax_query[] = [
        'taxonomy' => FGA_Plugin::TAX_THEMA,
        'field'    => 'slug',
        'terms'    => $filters['thema'],
      ];
    }

    if (!empty($filters['type'])) {
      $tax_query[] = [
        'taxonomy' => FGA_Plugin::TAX_TYPE,
        'field'    => 'slug',
        'terms'    => $filters['type'],
      ];
    }

    if (count($tax_query) > 1) {
      $args['tax_query'] = $tax_query;
    }

    return new WP_Query($args);
  }

  private static function sanitize_slugs(array $values): array {
    $values = array_map('sanitize_title', $values);
    $values = array_filter($values, fn($v) => $v !== '');
    return array_values(array_unique($values));
  }
}
