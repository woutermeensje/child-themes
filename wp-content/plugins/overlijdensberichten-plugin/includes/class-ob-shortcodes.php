<?php
if (!defined('ABSPATH')) exit;

class OB_Shortcodes {

  public static function register() {

    // ✅ Nieuwe shortcode met underscore
    add_shortcode('overlijdens_berichten', [ __CLASS__, 'render' ]);

    // (optioneel) oude shortcode laten werken voor backwards compatibility
    add_shortcode('overlijdensberichten', [ __CLASS__, 'render' ]);
  }

  public static function render($atts) {

    $atts = shortcode_atts([
      'per_page' => 12,
    ], $atts, 'overlijdens_berichten');

    wp_enqueue_style('ob-frontend');
    wp_enqueue_script('ob-frontend');

    $selected = [
      'provincie' => ob_clean_slugs(ob_get_req_array('provincie')),
      'stad'      => ob_clean_slugs(ob_get_req_array('stad')),
      'type'      => ob_clean_slugs(ob_get_req_array('type')),
    ];

    $keywords = ob_get_req_string('search_keywords', '');
    $per_page = (int)$atts['per_page'];

    $wrap  = '<div class="ob-wrap" data-component="obberichten">';
    $wrap .= '<form class="ob-form" data-ob-form>';
    $wrap .= '<input type="hidden" name="per_page" value="' . esc_attr($per_page) . '">';
    $wrap .= '<input type="hidden" name="paged" value="1">';
    $wrap .= '<input type="hidden" name="nonce" value="' . esc_attr(wp_create_nonce('ob_nonce')) . '">';

    $wrap .= ob_template('filter.php', [
      'selected' => $selected,
      'keywords' => $keywords,
    ]);

    $wrap .= '</form>';

    $q = OB_AJAX::build_query([
      'keywords' => $keywords,
      'selected' => $selected,
      'per_page' => $per_page,
      'paged'    => 1,
    ]);

    $wrap .= '<div id="ob-results">';
    $wrap .= ob_template('listings.php', [ 'query' => $q ]);
    $wrap .= '</div>';

    $wrap .= '</div>';

    return $wrap;
  }
}
