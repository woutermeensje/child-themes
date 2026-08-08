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

function si_platform_opdracht_categories(): array {
    return [
        'vertalers'        => 'Vertalers',
        'online-marketing' => 'Online marketing',
        'office'           => 'Office',
        'logistiek'        => 'Logistiek',
        'creative'         => 'Creative',
        'werkstudent'      => 'Werkstudent',
        'freelance'        => 'Freelance',
    ];
}

function si_platform_opdracht_category_meta_key(string $category): string {
    return '_si_platform_opdracht_category_' . str_replace('-', '_', sanitize_key($category));
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

    foreach (si_platform_opdracht_categories() as $category => $label) {
        register_post_meta('page', si_platform_opdracht_category_meta_key($category), [
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
    }
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

    <hr>
    <p style="margin:0 0 8px;"><strong>Opdrachtcategorieën</strong></p>
    <p class="description" style="margin-top:0;">Vink een of meer categorieën aan voor de categorie-shortcodes. Een categorie maakt deze pagina automatisch onderdeel van de platform-opdrachten.</p>

    <?php foreach (si_platform_opdracht_categories() as $category => $label) : ?>
        <?php $category_checked = (bool) get_post_meta($post->ID, si_platform_opdracht_category_meta_key($category), true); ?>
        <label style="display:flex;align-items:center;gap:8px;margin:6px 0;">
            <input
                type="checkbox"
                name="si_platform_opdracht_categories[]"
                value="<?php echo esc_attr($category); ?>"
                <?php checked($category_checked); ?>
            >
            <?php echo esc_html($label); ?>
        </label>
    <?php endforeach; ?>
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

    $selected_categories = [];
    if (!empty($_POST['si_platform_opdracht_categories']) && is_array($_POST['si_platform_opdracht_categories'])) {
        $selected_categories = array_map('sanitize_key', wp_unslash($_POST['si_platform_opdracht_categories']));
    }

    if (!empty($selected_categories)) {
        update_post_meta($post_id, SI_PLATFORM_OPDRACHT_PAGE_META_KEY, '1');
    }

    foreach (si_platform_opdracht_categories() as $category => $label) {
        if (in_array($category, $selected_categories, true)) {
            update_post_meta($post_id, si_platform_opdracht_category_meta_key($category), '1');
        } else {
            delete_post_meta($post_id, si_platform_opdracht_category_meta_key($category));
        }
    }
});
