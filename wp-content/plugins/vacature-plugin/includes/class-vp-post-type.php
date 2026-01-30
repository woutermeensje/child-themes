<?php
if (!defined('ABSPATH')) exit;

class VP_Post_Type {
  public static function register() {
    register_post_type('vp_vacature', [
      'labels' => [
        'name'          => __('Vacatures', 'vacature-plugin'),
        'singular_name' => __('Vacature', 'vacature-plugin'),
        'add_new_item'  => __('Nieuwe vacature', 'vacature-plugin'),
        'edit_item'     => __('Vacature bewerken', 'vacature-plugin'),
      ],
      'public' => true,
      'has_archive' => true,
      'rewrite' => [ 'slug' => 'vacatures' ],
      'menu_icon' => 'dashicons-id',
      'supports' => [ 'title', 'editor', 'excerpt', 'thumbnail', 'author' ],
      'show_in_rest' => true,
    ]);
  }
}