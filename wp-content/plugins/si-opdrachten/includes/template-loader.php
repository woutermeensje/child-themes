<?php
if (!defined('ABSPATH')) exit;

/**
 * Zoek template eerst in theme override:
 * /wp-content/themes/childtheme/si-opdrachten/<file>
 * anders pak plugin template:
 * /wp-content/plugins/si-opdrachten/templates/<file>
 */
function si_opd_get_template_path($file) {
    $theme_path = trailingslashit(get_stylesheet_directory()) . 'si-opdrachten/' . $file;
    if (file_exists($theme_path)) return $theme_path;

    $plugin_path = SI_OPD_PATH . 'templates/' . $file;
    if (file_exists($plugin_path)) return $plugin_path;

    return '';
}

function si_opd_render_template($file, $vars = []) {
    $path = si_opd_get_template_path($file);
    if (!$path) return;

    extract($vars);
    include $path;
}
