<?php
/**
 * Plugin Name: Vacature Plugin
 * Description: Eigen vacature plugin met filters, listings, single vacatures, custom taxonomieën en Google for Jobs schema.
 * Version: 1.0.0
 * Author: Sustainable Recruitment Marketing B.V.
 * Text Domain: vacature-plugin
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit;

define('VP_VERSION', '1.0.0');
define('VP_PLUGIN_FILE', __FILE__);
define('VP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VP_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once VP_PLUGIN_DIR . 'includes/class-vp-plugin.php';

register_activation_hook(__FILE__, function () {
  require_once VP_PLUGIN_DIR . 'includes/class-vp-post-type.php';
  require_once VP_PLUGIN_DIR . 'includes/class-vp-taxonomies.php';

  VP_Post_Type::register();
  VP_Taxonomies::register();

  flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function () {
  flush_rewrite_rules();
});

add_action('plugins_loaded', function () {
  VP_Plugin::instance();
});