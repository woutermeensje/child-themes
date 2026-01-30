<?php
if (!defined('ABSPATH')) exit;

class VP_Templates {

  public static function register() {
    add_filter('template_include', [ __CLASS__, 'template_include' ], 99);
  }

  public static function template_include($template) {

    // Single: vp_vacature
    if (is_singular('vp_vacature')) {
      $plugin_tpl = VP_PLUGIN_DIR . 'templates/single-vacature.php';
      if (file_exists($plugin_tpl)) return $plugin_tpl;
    }

    // Archive: vp_vacature (handig voor /vacatures/)
    if (is_post_type_archive('vp_vacature')) {
      // als je later een eigen archive template wil: maak templates/archive-vacature.php aan
      $plugin_tpl = VP_PLUGIN_DIR . 'templates/archive-vacature.php';
      if (file_exists($plugin_tpl)) return $plugin_tpl;
    }

    return $template;
  }
}