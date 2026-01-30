<?php
if (!defined('ABSPATH')) exit;

class VP_Taxonomies {
  public static function register() {
    $cpt = 'vp_vacature';

    register_taxonomy('vp_job_type', $cpt, [
      'labels' => [ 'name' => __('Job type', 'vacature-plugin') ],
      'public' => true,
      'hierarchical' => true,
      'rewrite' => [ 'slug' => 'job-type' ],
      'show_in_rest' => true,
    ]);

    register_taxonomy('vp_category', $cpt, [
      'labels' => [ 'name' => __('Categorie', 'vacature-plugin') ],
      'public' => true,
      'hierarchical' => true,
      'rewrite' => [ 'slug' => 'categorie' ],
      'show_in_rest' => true,
    ]);

    register_taxonomy('vp_org_type', $cpt, [
      'labels' => [ 'name' => __('Type organisatie', 'vacature-plugin') ],
      'public' => true,
      'hierarchical' => true,
      'rewrite' => [ 'slug' => 'organisatie-type' ],
      'show_in_rest' => true,
    ]);

    register_taxonomy('bedrijfsnaam', $cpt, [
      'labels' => [ 'name' => __('Bedrijfsnaam', 'vacature-plugin') ],
      'public' => true,
      'hierarchical' => true,
      'rewrite' => [ 'slug' => 'bedrijfsnaam' ],
      'show_in_rest' => true,
    ]);

   register_taxonomy('vp_regio', $cpt, [
      'labels' => [ 'name' => __('Regio', 'vacature-plugin') ],
      'public' => true,
      'hierarchical' => true,
      'rewrite' => [ 'slug' => 'regio' ],
      'show_in_rest' => true,
    ]);
  }
}