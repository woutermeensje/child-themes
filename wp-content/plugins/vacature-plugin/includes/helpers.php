<?php
if (!defined('ABSPATH')) exit;

function vp_get_req_array($key) {
  $filter_key = 'filter_' . $key;

  if (!empty($_GET[$key])) return (array) $_GET[$key];
  if (!empty($_GET[$filter_key])) return (array) $_GET[$filter_key];

  if (!empty($_POST[$filter_key])) return (array) $_POST[$filter_key];
  if (!empty($_POST[$key])) return (array) $_POST[$key];

  return [];
}

function vp_get_req_string($key, $default = '') {
  if (isset($_GET[$key]) && $_GET[$key] !== '') return sanitize_text_field(wp_unslash($_GET[$key]));
  if (isset($_POST[$key]) && $_POST[$key] !== '') return sanitize_text_field(wp_unslash($_POST[$key]));
  return $default;
}

function vp_template($file, $vars = []) {
  $path = VP_PLUGIN_DIR . 'templates/' . ltrim($file, '/');
  if (!file_exists($path)) return '';
  ob_start();
  extract($vars, EXTR_SKIP);
  include $path;
  return ob_get_clean();
}

function vp_clean_slugs($arr) {
  $arr = is_array($arr) ? $arr : [$arr];
  $arr = array_map('sanitize_title', $arr);
  return array_values(array_filter(array_unique($arr)));
}

/**
 * Default settings (centrale defaults voor de plugin)
 * -> Hier voeg je dus jouw 'filters_heading' toe.
 */
function vp_default_settings() {
  return [
    'filters_heading'               => 'Filters',
    'reset_button_text'             => 'Wis alles',

    // 👇 nieuw
    'filters_newsletter_text'       => 'Of schrijf je in voor de',
    'filters_newsletter_link_text'  => 'vacature nieuwsbrief',
    'filters_newsletter_url'        => '',
    'filters_newsletter_site'       => 'Recruiternext.nl',
  ];
}




/**
 * Settings helper
 * Volgorde:
 * 1) opgeslagen optie in wp_options (vp_settings)
 * 2) default uit vp_default_settings()
 * 3) fallback $default (parameter)
 */
function vp_setting($key, $default = '') {
  $opt = get_option('vp_settings', []);
  $defaults = vp_default_settings();

  // 1) opgeslagen waarde
  if (is_array($opt) && array_key_exists($key, $opt) && $opt[$key] !== '') {
    return $opt[$key];
  }

  // 2) plugin default
  if (array_key_exists($key, $defaults) && $defaults[$key] !== '') {
    return $defaults[$key];
  }

  // 3) fallback uit aanroep
  return $default;
}


