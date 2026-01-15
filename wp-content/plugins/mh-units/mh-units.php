<?php
/**
 * Plugin Name: MH Units (Used Units Listings)
 * Description: Custom Unit listings met filter + grid + single template files.
 * Version: 0.1.0
 */

if (!defined('ABSPATH')) exit;

define('MH_UNITS_PATH', plugin_dir_path(__FILE__));
define('MH_UNITS_URL', plugin_dir_url(__FILE__));

require_once MH_UNITS_PATH . 'includes/cpt-unit.php';
require_once MH_UNITS_PATH . 'includes/template-loader.php';
require_once MH_UNITS_PATH . 'includes/shortcode-units.php';

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('mh-units', MH_UNITS_URL . 'assets/css/units.css', [], '0.1.0');
    wp_enqueue_script('mh-units', MH_UNITS_URL . 'assets/js/units.js', ['jquery'], '0.1.0', true);
});


add_filter('single_template', function ($single) {
    if (is_singular('mh_unit')) {
        $path = mh_units_get_template_path('single-unit.php');
        if ($path) return $path;
    }
    return $single;
});

