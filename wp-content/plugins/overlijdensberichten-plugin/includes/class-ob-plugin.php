<?php
if (!defined('ABSPATH')) exit;

require_once OB_PLUGIN_DIR . 'includes/helpers.php';
require_once OB_PLUGIN_DIR . 'includes/class-ob-post-type.php';
require_once OB_PLUGIN_DIR . 'includes/class-ob-taxonomies.php';
require_once OB_PLUGIN_DIR . 'includes/class-ob-shortcodes.php';
require_once OB_PLUGIN_DIR . 'includes/class-ob-ajax.php';
require_once OB_PLUGIN_DIR . 'includes/class-ob-templates.php';

final class OB_Plugin {
  private static $instance = null;

  public static function instance() {
    if (self::$instance === null) self::$instance = new self();
    return self::$instance;
  }

  private function __construct() {
    add_action('init', [ 'OB_Post_Type', 'register' ]);
    add_action('init', [ 'OB_Taxonomies', 'register' ]);
    add_action('init', [ 'OB_Shortcodes', 'register' ]);
    add_action('init', [ 'OB_AJAX', 'register' ]);
    add_action('init', [ 'OB_Templates', 'register' ]);

    add_action('wp_enqueue_scripts', [ $this, 'enqueue_assets' ]);
  }

  public function enqueue_assets() {
    wp_register_style('ob-frontend', OB_PLUGIN_URL . 'assets/css/frontend.css', [], OB_VERSION);
    wp_register_script('ob-frontend', OB_PLUGIN_URL . 'assets/js/frontend.js', [], OB_VERSION, true);

    wp_localize_script('ob-frontend', 'OB', [
      'ajaxurl' => admin_url('admin-ajax.php'),
      'nonce'   => wp_create_nonce('ob_nonce'),
    ]);
  }
}
