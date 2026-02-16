<?php
if (!defined('ABSPATH')) exit;

class OB_Taxonomies {
  const TAX_PROV = 'ob_provincie';
  const TAX_CITY = 'ob_stad';
  const TAX_TYPE = 'ob_berichttype';

  public static function register() {
    register_taxonomy(self::TAX_PROV, [ OB_Post_Type::CPT ], [
      'labels' => [
        'name' => __('Provincies', 'overlijdensberichten-plugin'),
        'singular_name' => __('Provincie', 'overlijdensberichten-plugin'),
      ],
      'public' => true,
      'hierarchical' => true,
      'show_admin_column' => true,
      'show_in_rest' => true,
      'rewrite' => [ 'slug' => 'provincie' ],
    ]);

    register_taxonomy(self::TAX_CITY, [ OB_Post_Type::CPT ], [
      'labels' => [
        'name' => __('Steden', 'overlijdensberichten-plugin'),
        'singular_name' => __('Stad', 'overlijdensberichten-plugin'),
      ],
      'public' => true,
      'hierarchical' => true,
      'show_admin_column' => true,
      'show_in_rest' => true,
      'rewrite' => [ 'slug' => 'stad' ],
    ]);

    register_taxonomy(self::TAX_TYPE, [ OB_Post_Type::CPT ], [
      'labels' => [
        'name' => __('Type bericht', 'overlijdensberichten-plugin'),
        'singular_name' => __('Type bericht', 'overlijdensberichten-plugin'),
      ],
      'public' => true,
      'hierarchical' => true,
      'show_admin_column' => true,
      'show_in_rest' => true,
      'rewrite' => [ 'slug' => 'bericht-type' ],
    ]);
  }
}
