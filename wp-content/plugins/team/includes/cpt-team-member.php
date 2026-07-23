<?php
if (!defined('ABSPATH')) exit;

function mh_team_register_post_type(): void {
    register_post_type('mh_team_member', [
        'labels' => [
            'name'                  => 'Team en partners',
            'singular_name'         => 'Teamlid of partner',
            'menu_name'             => 'Team',
            'name_admin_bar'        => 'Teamlid of partner',
            'add_new'               => 'Nieuw item',
            'add_new_item'          => 'Nieuw teamlid of partner toevoegen',
            'edit_item'             => 'Teamlid of partner bewerken',
            'new_item'              => 'Nieuw teamlid of partner',
            'view_item'             => 'Teamlid of partner bekijken',
            'search_items'          => 'Teamleden en partners zoeken',
            'not_found'             => 'Geen teamleden of partners gevonden',
            'not_found_in_trash'    => 'Geen teamleden of partners gevonden in de prullenbak',
            'all_items'             => 'Alle teamleden en partners',
            'archives'              => 'Team en partners',
            'featured_image'        => 'Profielfoto / logo',
            'set_featured_image'    => 'Profielfoto of logo instellen',
            'remove_featured_image' => 'Profielfoto of logo verwijderen',
            'use_featured_image'    => 'Gebruik als profielfoto of logo',
        ],
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_admin_bar'   => true,
        'show_in_rest'        => true,
        'exclude_from_search' => true,
        'publicly_queryable'  => false,
        'has_archive'         => false,
        'menu_icon'           => 'dashicons-groups',
        'menu_position'       => 22,
        'supports'            => ['title', 'editor', 'thumbnail', 'page-attributes'],
    ]);
}
add_action('init', 'mh_team_register_post_type');

add_filter('enter_title_here', function ($placeholder, $post) {
    if ($post && 'mh_team_member' === $post->post_type) {
        return 'Volledige naam of bedrijfsnaam';
    }

    return $placeholder;
}, 10, 2);

add_filter('wp_insert_post_data', function ($data, $postarr) {
    if (($data['post_type'] ?? '') !== 'mh_team_member') {
        return $data;
    }

    if (trim((string) ($data['post_title'] ?? '')) !== '') {
        return $data;
    }

    $first_name = isset($_POST['mh_team_first_name'])
        ? sanitize_text_field(wp_unslash($_POST['mh_team_first_name']))
        : '';
    $last_name = isset($_POST['mh_team_last_name'])
        ? sanitize_text_field(wp_unslash($_POST['mh_team_last_name']))
        : '';
    $full_name = trim($first_name . ' ' . $last_name);

    if ($full_name !== '') {
        $data['post_title'] = $full_name;
    }

    return $data;
}, 10, 2);

add_filter('manage_mh_team_member_posts_columns', function ($columns) {
    $new_columns = [];

    foreach ($columns as $key => $label) {
        if ('title' === $key) {
            $new_columns['mh_team_photo'] = 'Foto';
        }

        $new_columns[$key] = $label;
    }

    $new_columns['mh_team_role']  = 'Functie';
    $new_columns['mh_team_type']  = 'Soort';
    $new_columns['mh_team_email'] = 'E-mailadres';
    $new_columns['mh_team_phone'] = 'Telefoon';

    return $new_columns;
});

add_action('manage_mh_team_member_posts_custom_column', function ($column, $post_id) {
    if ('mh_team_photo' === $column) {
        if (has_post_thumbnail($post_id)) {
            echo get_the_post_thumbnail($post_id, [48, 48], [
                'style' => 'width:48px;height:48px;object-fit:cover;border-radius:50%;',
            ]);
        } else {
            echo '<span aria-hidden="true" style="display:inline-flex;width:48px;height:48px;align-items:center;justify-content:center;border-radius:50%;background:#f1f8ee;color:#25476b;">-</span>';
        }
    }

    if ('mh_team_role' === $column) {
        echo esc_html(get_post_meta($post_id, '_mh_team_role', true));
    }

    if ('mh_team_type' === $column) {
        $type  = get_post_meta($post_id, '_mh_team_entry_type', true);
        $type  = $type !== '' ? $type : 'team_member';
        $types = function_exists('mh_team_entry_types') ? mh_team_entry_types() : [
            'team_member' => 'Teamlid',
            'partner'     => 'Partner',
        ];

        echo esc_html($types[$type] ?? 'Teamlid');
    }

    if ('mh_team_email' === $column) {
        $email = get_post_meta($post_id, '_mh_team_email', true);
        echo $email ? '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>' : '';
    }

    if ('mh_team_phone' === $column) {
        echo esc_html(get_post_meta($post_id, '_mh_team_phone', true));
    }
}, 10, 2);
