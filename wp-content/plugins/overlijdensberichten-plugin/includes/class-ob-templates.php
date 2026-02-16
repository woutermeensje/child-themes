<?php
if (!defined('ABSPATH')) exit;

class OB_Templates {
  public static function register() {
    add_filter('template_include', [ __CLASS__, 'template_include' ]);
  }

  public static function template_include($template) {
    if (is_singular(OB_Post_Type::CPT)) {
      $custom = OB_PLUGIN_DIR . 'templates/single-overlijdensbericht.php';
      if (file_exists($custom)) return $custom;
    }
    if (is_post_type_archive(OB_Post_Type::CPT)) {
      $custom = OB_PLUGIN_DIR . 'templates/archive-overlijdensbericht.php';
      if (file_exists($custom)) return $custom;
    }
    return $template;
  }
}
