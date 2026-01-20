<?php
if (!defined('ABSPATH')) exit;

add_action('init', function () {

    register_post_type('si_opdracht', [
        'labels' => [
            'name'          => 'Freelance opdrachten',
            'singular_name' => 'Freelance opdracht',
        ],
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => ['slug' => 'opdrachten'],
        'menu_icon'    => 'dashicons-clipboard',
        // We gebruiken thumbnail als "logo" (uitgelichte afbeelding) – maar we tonen hem klein in de listings.
        'supports'     => ['title', 'editor', 'excerpt', 'thumbnail'],
        'show_in_rest' => true,
    ]);

    // Categorie (marketing, design, software, fotografie, etc.)
    register_taxonomy('si_opdracht_categorie', ['si_opdracht'], [
        'labels' => [
            'name'          => 'Categorieën',
            'singular_name' => 'Categorie',
        ],
        'public'            => true,
        'rewrite'           => ['slug' => 'opdracht-categorie'],
        'hierarchical'      => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
    ]);

    // Type opdracht (freelance, zzp, project, tijdelijk, flexibel, payroll, loondienst etc.)
    register_taxonomy('si_opdracht_type', ['si_opdracht'], [
        'labels' => [
            'name'          => 'Type opdracht',
            'singular_name' => 'Type',
        ],
        'public'            => true,
        'rewrite'           => ['slug' => 'opdracht-type'],
        'hierarchical'      => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
    ]);

});
