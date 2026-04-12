<?php
if (!defined('ABSPATH')) exit;

/**
 * Pak standaard plugin templates.
 * Theme overrides zijn optioneel en staan default uit zodat de plugin
 * zijn eigen styling en markup beheert.
 */
function mh_units_get_template_path($file) {
    $plugin_path = MH_UNITS_PATH . 'templates/' . $file;
    if (file_exists($plugin_path)) return $plugin_path;

    $allow_theme_overrides = (bool) apply_filters('mh_units_allow_theme_overrides', false, $file);
    if ($allow_theme_overrides) {
        $theme_path = trailingslashit(get_stylesheet_directory()) . 'mh-units/' . $file;
        if (file_exists($theme_path)) return $theme_path;
    }

    return '';
}

function mh_units_render_template($file, $vars = []) {
    $path = mh_units_get_template_path($file);
    if (!$path) return;

    extract($vars);
    include $path;
}
