<?php
if (!defined('ABSPATH')) exit;

class VP_Shortcodes {
  public static function register() {
    add_shortcode('vacature_plugin', [ __CLASS__, 'render' ]);
  }

  public static function render($atts) {
    $atts = shortcode_atts([
      'per_page'   => 12,
      'job_type'   => '',
      'categorie'  => '',
      'org_type'   => '',
      'bedrijf'    => '',
    ], $atts, 'vacature_plugin');

    // enqueue assets
    wp_enqueue_style('vp-frontend');
    wp_enqueue_script('vp-frontend');

    // selected: GET > POST > shortcode
    $selected = [
      'job_type'  => [],
      'categorie' => [],
      'org_type'  => [],
      'bedrijf'   => [],
    ];

    foreach ($selected as $k => &$v) {
      $req = vp_get_req_array($k);
      if (!empty($req)) {
        $v = vp_clean_slugs($req);
      } elseif (!empty($atts[$k])) {
        $v = vp_clean_slugs(array_map('trim', explode(',', (string)$atts[$k])));
      }
    }
    unset($v);

    $keywords  = vp_get_req_string('search_keywords', '');
    $location  = vp_get_req_string('search_location', '');

    $wrap = '<div class="vpjobs-wrap" data-component="vpjobs">';
    $wrap .= vp_template('filter.php', [
      'atts'     => $atts,
      'selected' => $selected,
      'keywords' => $keywords,
      'location' => $location,
    ]);

    // initial render (server) – JS pakt daarna AJAX over
    $wrap .= '<div id="vpjobs-results">';
    $wrap .= vp_template('listings.php', [
      'query' => VP_AJAX::build_query([
        'keywords' => $keywords,
        'location' => $location,
        'selected' => $selected,
        'per_page' => (int)$atts['per_page'],
        'paged'    => 1,
      ]),
    ]);
    $wrap .= '</div>';

    $wrap .= '</div>';

    return $wrap;
  }
}