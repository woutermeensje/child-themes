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