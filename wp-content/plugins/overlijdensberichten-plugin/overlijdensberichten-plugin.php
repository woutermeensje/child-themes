<?php
/**
 * Plugin Name: Overlijdensberichten Plugin
 * Description: Overlijdensberichten CPT met filter (provincie/stad/type + zoekveld), listings en single template. Vanilla JS (AJAX).
 * Version: 1.0.0
 * Author: Overlijdens-berichten.nl
 */

if (!defined('ABSPATH')) exit;

define('OB_VERSION', '1.0.0');
define('OB_PLUGIN_FILE', __FILE__);
define('OB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OB_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once OB_PLUGIN_DIR . 'includes/class-ob-plugin.php';

register_activation_hook(__FILE__, function () {
  require_once OB_PLUGIN_DIR . 'includes/class-ob-post-type.php';
  require_once OB_PLUGIN_DIR . 'includes/class-ob-taxonomies.php';

  OB_Post_Type::register();
  OB_Taxonomies::register();

  flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function () {
  flush_rewrite_rules();
});

add_action('plugins_loaded', function () {
  OB_Plugin::instance();
});
