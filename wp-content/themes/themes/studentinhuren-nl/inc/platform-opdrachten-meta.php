<?php
if (!defined('ABSPATH')) exit;

/**
 * Vinkje "Platform opdrachtenpagina" op pagina's.
 * Pagina's die dit vinkje aan hebben staan verschijnen automatisch
 * in het overzicht via de [platform_opdrachten_pagina's] shortcode.
 */

if (!defined('SI_PLATFORM_OPDRACHT_PAGE_META_KEY')) {
    define('SI_PLATFORM_OPDRACHT_PAGE_META_KEY', '_si_is_platform_opdracht_page');
}

add_action('init', function () {
    register_post_meta('page', SI_PLATFORM_OPDRACHT_PAGE_META_KEY, [
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'boolean',
        'default'           => false,
        'sanitize_callback' => static function ($value): bool {
            return (bool) $value;
        },
        'auth_callback'     => static function (): bool {
            return current_user_can('edit_posts');
        },
    ]);
});

add_action('add_meta_boxes', function () {
    add_meta_box(
        'si_platform_opdracht_page',
        'Platform opdrachten',
        'si_render_platform_opdracht_page_metabox',
        'page',
        'side',
        'default'
    );
});

function si_render_platform_opdracht_page_metabox(WP_Post $post): void {
    wp_nonce_field('si_platform_opdracht_page_save', 'si_platform_opdracht_page_nonce');

    $checked = (bool) get_post_meta($post->ID, SI_PLATFORM_OPDRACHT_PAGE_META_KEY, true);
    ?>
    <label style="display:flex;align-items:center;gap:8px;">
        <input type="checkbox" name="si_is_platform_opdracht_page" value="1" <?php checked($checked); ?>>
        Deze pagina opnemen als platform-opdracht
    </label>
    <p class="description">Deze pagina verschijnt automatisch in het overzicht via [platform_opdrachten_pagina's].</p>
    <?php
}

add_action('save_post_page', function (int $post_id): void {
    if (
        !isset($_POST['si_platform_opdracht_page_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['si_platform_opdracht_page_nonce'])), 'si_platform_opdracht_page_save')
    ) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_page', $post_id)) {
        return;
    }

    if (!empty($_POST['si_is_platform_opdracht_page'])) {
        update_post_meta($post_id, SI_PLATFORM_OPDRACHT_PAGE_META_KEY, '1');
    } else {
        delete_post_meta($post_id, SI_PLATFORM_OPDRACHT_PAGE_META_KEY);
    }
});
