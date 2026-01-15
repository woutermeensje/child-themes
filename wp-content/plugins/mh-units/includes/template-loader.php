<?php
if (!defined('ABSPATH')) exit;

/**
 * Zoek template eerst in theme override:
 * /wp-content/themes/childtheme/mh-units/<file>
 * anders pak plugin template:
 * /wp-content/plugins/mh-units/templates/<file>
 */
function mh_units_get_template_path($file) {
    $theme_path = trailingslashit(get_stylesheet_directory()) . 'mh-units/' . $file;
    if (file_exists($theme_path)) return $theme_path;

    $plugin_path = MH_UNITS_PATH . 'templates/' . $file;
    if (file_exists($plugin_path)) return $plugin_path;

    return '';
}

function mh_units_render_template($file, $vars = []) {
    $path = mh_units_get_template_path($file);
    if (!$path) return;

    extract($vars);
    include $path;
}
