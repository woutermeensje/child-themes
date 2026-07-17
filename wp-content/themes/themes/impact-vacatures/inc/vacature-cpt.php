<?php
if (!defined('ABSPATH')) exit;

add_action('init', function () {
    register_post_type('sj_vacature', [
        'labels' => [
            'name'               => 'Vacature Aanvragen',
            'singular_name'      => 'Vacature Aanvraag',
            'add_new'            => 'Nieuwe Aanvraag',
            'add_new_item'       => 'Nieuwe Vacature Aanvraag',
            'edit_item'          => 'Aanvraag bekijken',
            'view_item'          => 'Bekijk Aanvraag',
            'all_items'          => 'Alle Aanvragen',
            'search_items'       => 'Zoek Aanvragen',
            'not_found'          => 'Geen aanvragen gevonden.',
            'not_found_in_trash' => 'Geen aanvragen in de prullenbak.',
            'menu_name'          => 'Vacature Aanvragen',
        ],
        'public'          => false,
        'show_ui'         => true,
        'show_in_menu'    => true,
        'menu_icon'       => 'dashicons-portfolio',
        'menu_position'   => 25,
        'supports'        => ['title'],
        'capability_type' => 'post',
        'show_in_rest'    => false,
    ]);
});
