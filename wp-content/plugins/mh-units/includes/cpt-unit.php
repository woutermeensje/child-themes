<?php
if (!defined('ABSPATH')) exit;

add_action('init', function () {

    register_post_type('mh_unit', [
        'labels' => [
            'name' => 'Units',
            'singular_name' => 'Unit',
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'units'],
        'menu_icon' => 'dashicons-admin-multisite',
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
        'show_in_rest' => true,
    ]);

    register_taxonomy('mh_unit_type', ['mh_unit'], [
        'labels' => [
            'name' => 'Unit types',
            'singular_name' => 'Unit type',
        ],
        'public' => true,
        'rewrite' => ['slug' => 'unit-type'],
        'hierarchical' => true,
        'show_in_rest' => true,
    ]);

    register_taxonomy('mh_unit_conditie', ['mh_unit'], [
    'labels' => [
        'name' => 'Conditie',
        'singular_name' => 'Conditie',
    ],
    'public' => true,
    'rewrite' => ['slug' => 'conditie'],
    'hierarchical' => true,     // conditie is meestal "tags", geen boomstructuur
    'show_in_rest' => true,
    'show_admin_column' => true, // handig in overzicht
]);

// Aanbod: Koop / Huur / Koop & Huur
register_taxonomy('mh_unit_aanbod', ['mh_unit'], [
    'labels' => [
        'name'          => 'Aanbod',
        'singular_name' => 'Aanbod',
    ],
    'public'            => true,
    'rewrite'           => ['slug' => 'aanbod'],
    'hierarchical'      => true,  // tag-achtig
    'show_in_rest'      => true,
    'show_admin_column' => true,
]);


});


