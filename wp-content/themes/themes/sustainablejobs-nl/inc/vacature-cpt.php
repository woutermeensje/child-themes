<?php
if (!defined('ABSPATH')) exit;

/* ============================================================
   Custom Post Type: sj_vacature_aanvraag
   Slaat vacature-formulier inzendingen op in de database.
============================================================ */

/* ── CPT registratie ─────────────────────────────────────── */
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
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_icon'           => 'dashicons-portfolio',
        'menu_position'       => 25,
        'supports'            => ['title'],
        'capability_type'     => 'post',
        'capabilities'        => ['create_posts' => 'do_not_allow'],
        'map_meta_cap'        => true,
        'show_in_rest'        => false,
    ]);
});

/* ── Aangepaste admin kolommen ───────────────────────────── */
add_filter('manage_sj_vacature_posts_columns', function ($columns) {
    return [
        'cb'          => $columns['cb'],
        'title'       => 'Vacaturetitel',
        'sj_pakket'   => 'Pakket',
        'sj_bedrijf'  => 'Bedrijf',
        'sj_email'    => 'E-mail',
        'sj_type'     => 'Type baan',
        'sj_locatie'  => 'Locatie',
        'sj_status'   => 'Status',
        'date'        => 'Datum',
    ];
});

add_action('manage_sj_vacature_posts_custom_column', function ($column, $post_id) {
    switch ($column) {
        case 'sj_pakket':
            $pakket = get_post_meta($post_id, '_sj_pakket', true);
            // Haal eerste deel vóór de ':' op als verkorte weergave
            $label = $pakket ? explode(':', $pakket)[0] : '—';
            echo '<strong>' . esc_html($label) . '</strong>';
            break;
        case 'sj_bedrijf':
            $naam    = get_post_meta($post_id, '_sj_voornaam', true);
            $ach     = get_post_meta($post_id, '_sj_achternaam', true);
            $bedrijf = get_post_meta($post_id, '_sj_bedrijfsnaam', true);
            echo esc_html($bedrijf);
            echo '<br><span style="color:#777;font-size:12px;">' . esc_html(trim("$naam $ach")) . '</span>';
            break;
        case 'sj_email':
            $email = get_post_meta($post_id, '_sj_email', true);
            echo '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>';
            break;
        case 'sj_type':
            $types = get_post_meta($post_id, '_sj_type_baan', true);
            echo esc_html(is_array($types) ? implode(', ', $types) : ($types ?: '—'));
            break;
        case 'sj_locatie':
            echo esc_html(get_post_meta($post_id, '_sj_locatie', true) ?: '—');
            break;
        case 'sj_status':
            $status = get_post_status($post_id);
            $labels = [
                'pending' => ['Nieuw',        '#C5D77F', '#168AAD'],
                'publish' => ['Gepubliceerd', '#d1fae5', '#065f46'],
                'draft'   => ['Concept',      '#e5e7eb', '#374151'],
                'trash'   => ['Verwijderd',   '#fee2e2', '#991b1b'],
            ];
            [$text, $bg, $color] = $labels[$status] ?? ['—', '#f3f4f6', '#374151'];
            printf(
                '<span style="display:inline-block;padding:3px 10px;border-radius:999px;background:%s;color:%s;font-size:12px;font-weight:700;">%s</span>',
                esc_attr($bg), esc_attr($color), esc_html($text)
            );
            break;
    }
}, 10, 2);

/* Sorteerbare kolommen */
add_filter('manage_edit-sj_vacature_sortable_columns', function ($columns) {
    $columns['sj_bedrijf'] = 'sj_bedrijf';
    $columns['sj_pakket']  = 'sj_pakket';
    return $columns;
});

/* ── Meta box: alle inzendingsdetails ────────────────────── */
add_action('add_meta_boxes', function () {
    add_meta_box(
        'sj_vacature_details',
        'Inzendingsdetails',
        'sj_vacature_details_meta_box',
        'sj_vacature',
        'normal',
        'high'
    );
    add_meta_box(
        'sj_vacature_omschrijving',
        'Vacature omschrijving',
        'sj_vacature_omschrijving_meta_box',
        'sj_vacature',
        'normal',
        'default'
    );
    add_meta_box(
        'sj_vacature_logo',
        'Bedrijfslogo',
        'sj_vacature_logo_meta_box',
        'sj_vacature',
        'side',
        'default'
    );
});

function sj_vacature_details_meta_box($post) {
    $fields = [
        'Pakket'      => get_post_meta($post->ID, '_sj_pakket', true),
        'Voornaam'    => get_post_meta($post->ID, '_sj_voornaam', true),
        'Achternaam'  => get_post_meta($post->ID, '_sj_achternaam', true),
        'Bedrijfsnaam'=> get_post_meta($post->ID, '_sj_bedrijfsnaam', true),
        'E-mail'      => get_post_meta($post->ID, '_sj_email', true),
        'Locatie'     => get_post_meta($post->ID, '_sj_locatie', true),
        'Type baan'   => implode(', ', (array) get_post_meta($post->ID, '_sj_type_baan', true)),
        'Hoe gevonden'=> get_post_meta($post->ID, '_sj_referral', true),
    ];
    ?>
    <style>
    .sj-meta-table { width: 100%; border-collapse: collapse; font-family: -apple-system, sans-serif; }
    .sj-meta-table th { width: 160px; padding: 10px 12px; background: #f9fafb; border: 1px solid #e5e7eb; font-weight: 600; font-size: 13px; color: #374151; text-align: left; vertical-align: top; }
    .sj-meta-table td { padding: 10px 12px; border: 1px solid #e5e7eb; font-size: 13px; color: #111827; vertical-align: top; }
    .sj-meta-table td a { color: #168AAD; }
    .sj-meta-badge { display: inline-block; padding: 3px 10px; border-radius: 999px; background: #C5D77F; color: #168AAD; font-size: 12px; font-weight: 700; }
    </style>
    <table class="sj-meta-table">
        <?php foreach ($fields as $label => $value):
            $display = esc_html($value ?: '—');
            if ($label === 'E-mail' && $value) {
                $display = '<a href="mailto:' . esc_attr($value) . '">' . esc_html($value) . '</a>';
            }
            if ($label === 'Pakket' && $value) {
                $display = '<span class="sj-meta-badge">' . esc_html(explode(':', $value)[0]) . '</span> <span style="color:#6b7280;font-size:12px;">' . esc_html($value) . '</span>';
            }
        ?>
        <tr>
            <th><?php echo esc_html($label); ?></th>
            <td><?php echo $display; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php
}

function sj_vacature_omschrijving_meta_box($post) {
    $omschrijving = get_post_meta($post->ID, '_sj_omschrijving', true);
    $extra_info   = get_post_meta($post->ID, '_sj_extra_info', true);
    ?>
    <style>
    .sj-rich-content { font-family: -apple-system, sans-serif; font-size: 14px; line-height: 1.7; color: #111827; padding: 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 5px; margin-bottom: 16px; }
    .sj-rich-content h4 { margin: 0 0 8px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; }
    </style>
    <?php if ($omschrijving): ?>
    <div class="sj-rich-content">
        <h4>Vacature omschrijving</h4>
        <?php echo wp_kses_post($omschrijving); ?>
    </div>
    <?php endif; ?>
    <?php if ($extra_info): ?>
    <div class="sj-rich-content">
        <h4>Aanvullende informatie</h4>
        <?php echo wp_kses_post($extra_info); ?>
    </div>
    <?php endif; ?>
    <?php if (!$omschrijving && !$extra_info): ?>
        <p style="color:#9ca3af;font-size:13px;">Geen omschrijving beschikbaar.</p>
    <?php endif; ?>
    <?php
}

function sj_vacature_logo_meta_box($post) {
    $logo_id = get_post_meta($post->ID, '_sj_logo_id', true);
    if ($logo_id) {
        $logo_url = wp_get_attachment_image_url($logo_id, 'medium');
        if ($logo_url) {
            echo '<img src="' . esc_url($logo_url) . '" style="max-width:100%;border-radius:5px;border:1px solid #e5e7eb;">';
            echo '<p style="margin:8px 0 0;"><a href="' . esc_url(get_edit_post_link($logo_id)) . '" target="_blank" style="font-size:12px;color:#168AAD;">Bekijk in mediabibliotheek</a></p>';
        }
    } else {
        echo '<p style="color:#9ca3af;font-size:13px;margin:0;">Geen logo meegestuurd.</p>';
    }
}

/* ── Verberg "Publiceren" sidebar — alleen-lezen posts ───── */
add_action('admin_head', function () {
    global $post_type;
    if ($post_type === 'sj_vacature') {
        echo '<style>
        #submitdiv .misc-pub-section:not(.misc-pub-post-status) { display:none; }
        #submitdiv #publish { display:none; }
        #submitdiv #save-post { width:100%; text-align:center; }
        </style>';
    }
});

/* ── Admin notice: nieuwe aanvragen ─────────────────────── */
add_action('admin_notices', function () {
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'sj_vacature') return;

    $count = wp_count_posts('sj_vacature');
    $pending = $count->pending ?? 0;
    if ($pending > 0) {
        printf(
            '<div class="notice notice-info"><p><strong>%d nieuwe vacature-aanvra%s</strong> wacht op beoordeling.</p></div>',
            $pending,
            $pending === 1 ? 'ag' : 'gen'
        );
    }
});
