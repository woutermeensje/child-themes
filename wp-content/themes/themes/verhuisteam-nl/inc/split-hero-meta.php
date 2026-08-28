<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function () {
    $fields = ['split_hero_title', 'split_hero_accent_terms', 'split_hero_description'];

    foreach ($fields as $key) {
        register_post_meta('page', $key, [
            'show_in_rest'  => true,
            'single'        => true,
            'type'          => 'string',
            'default'       => '',
            'auth_callback' => static function () {
                return current_user_can('edit_posts');
            },
        ]);
    }
});

add_action('add_meta_boxes', function () {
    add_meta_box(
        'verhuisteam_split_hero_meta',
        'Split Hero instellingen',
        'verhuisteam_split_hero_meta_box',
        'page',
        'normal',
        'high'
    );
});

function verhuisteam_split_hero_meta_box(WP_Post $post): void {
    if (get_page_template_slug($post->ID) !== 'page-split-hero.php') {
        echo '<p style="color:#999;margin:0;">Alleen beschikbaar bij het paginasjabloon <strong>Verhuisteam.nl Split Hero</strong>.</p>';
        return;
    }

    wp_nonce_field('verhuisteam_split_hero_save', 'verhuisteam_split_hero_nonce', false);

    $fields = [
        'split_hero_title'        => [
            'label'       => 'Hero titel',
            'type'        => 'textarea',
            'placeholder' => 'Flexibele inhuur voor jouw vacature, opdracht of project.',
            'hint'        => '',
        ],
        'split_hero_accent_terms' => [
            'label'       => 'Accentwoorden',
            'type'        => 'text',
            'placeholder' => 'Flexibele inhuur',
            'hint'        => 'Kommagescheiden. Deze woorden krijgen automatisch de accentkleur in de titel.',
        ],
        'split_hero_description'  => [
            'label'       => 'Beschrijving',
            'type'        => 'textarea',
            'placeholder' => 'Bekijk 78 openstaande vacatures, projecten én (freelance) opdrachten voor studenten, starters en young professionals. Of maak gelijk een account aan.',
            'hint'        => '',
        ],
    ];

    echo '<table style="width:100%;border-collapse:collapse;">';
    foreach ($fields as $key => $field) {
        $value = get_post_meta($post->ID, $key, true);
        echo '<tr><td style="padding:10px 0;vertical-align:top;width:200px;">';
        echo '<label for="' . esc_attr($key) . '"><strong>' . esc_html($field['label']) . '</strong>';
        if ($field['hint']) {
            echo '<br><span style="color:#777;font-size:12px;font-weight:400;">' . esc_html($field['hint']) . '</span>';
        }
        echo '</label></td><td style="padding:10px 0 10px 16px;">';
        if ($field['type'] === 'textarea') {
            echo '<textarea id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" rows="3" style="width:100%;font-family:monospace;" placeholder="' . esc_attr($field['placeholder']) . '">' . esc_textarea($value) . '</textarea>';
        } else {
            echo '<input type="' . esc_attr($field['type']) . '" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" style="width:100%;" placeholder="' . esc_attr($field['placeholder']) . '">';
        }
        echo '</td></tr>';
    }
    echo '</table>';
}

add_action('save_post', function (int $post_id): void {
    if (wp_is_post_revision($post_id)) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (get_post_type($post_id) !== 'page') {
        return;
    }

    if (!current_user_can('edit_page', $post_id)) {
        return;
    }

    if (!isset($_POST['verhuisteam_split_hero_nonce'])) {
        return;
    }

    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['verhuisteam_split_hero_nonce'])), 'verhuisteam_split_hero_save')) {
        return;
    }

    foreach (['split_hero_title', 'split_hero_accent_terms', 'split_hero_description'] as $key) {
        if (array_key_exists($key, $_POST)) {
            update_post_meta($post_id, $key, sanitize_textarea_field(wp_unslash($_POST[$key])));
        }
    }
});

function verhuisteam_split_hero_highlight(string $text, string $terms_raw): string {
    $escaped = esc_html($text);

    if (!$terms_raw) {
        return $escaped;
    }

    $terms = array_filter(array_map('trim', explode(',', $terms_raw)));
    if (empty($terms)) {
        return $escaped;
    }

    foreach ($terms as $term) {
        $escaped = preg_replace(
            '/(' . preg_quote(esc_html($term), '/') . ')/iu',
            '<span class="si-split-hero__title-accent">$1</span>',
            $escaped
        );
    }

    return $escaped;
}
