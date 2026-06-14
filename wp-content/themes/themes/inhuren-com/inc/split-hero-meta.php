<?php
if (!defined('ABSPATH')) exit;

add_action('init', function () {
    $fields = ['split_hero_title', 'split_hero_accent_terms', 'split_hero_description', 'split_hero_social_proof'];
    foreach ($fields as $key) {
        register_post_meta('page', $key, [
            'show_in_rest'  => true,
            'single'        => true,
            'type'          => 'string',
            'default'       => '',
            'auth_callback' => fn() => current_user_can('edit_posts'),
        ]);
    }
});

add_action('add_meta_boxes', function () {
    add_meta_box(
        'inhuren_split_hero_meta',
        'Split Hero instellingen',
        'inhuren_split_hero_meta_box',
        'page',
        'normal',
        'high'
    );
});

function inhuren_split_hero_meta_box(WP_Post $post): void {
    if (get_page_template_slug($post->ID) !== 'page-split-hero.php') {
        echo '<p style="color:#999;margin:0;">Alleen beschikbaar bij het paginasjabloon <strong>Inhuren.com Split Hero</strong>.</p>';
        return;
    }

    wp_nonce_field('inhuren_split_hero_save', 'inhuren_split_hero_nonce', false);

    $fields = [
        'split_hero_title'        => [
            'label'       => 'Hero titel',
            'type'        => 'textarea',
            'placeholder' => 'De beste freelancers vinden via Inhuren.com.',
            'hint'        => '',
        ],
        'split_hero_accent_terms' => [
            'label'       => 'Accentwoorden',
            'type'        => 'text',
            'placeholder' => 'freelancers, Inhuren.com',
            'hint'        => 'Kommagescheiden. Deze woorden krijgen automatisch de accentkleur in de titel.',
        ],
        'split_hero_description'  => [
            'label'       => 'Beschrijving',
            'type'        => 'textarea',
            'placeholder' => 'Inhuren.com is dé marktplaats voor freelancers en opdrachtgevers.',
            'hint'        => '',
        ],
        'split_hero_social_proof' => [
            'label'       => 'Social proof tekst',
            'type'        => 'text',
            'placeholder' => 'Bekijk alle 500+ opdrachten in ons netwerk.',
            'hint'        => 'Verschijnt als kleine regel onder de knoppen.',
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
    if (wp_is_post_revision($post_id)) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (get_post_type($post_id) !== 'page') return;
    if (!current_user_can('edit_page', $post_id)) return;
    if (!isset($_POST['inhuren_split_hero_nonce'])) return;
    if (!wp_verify_nonce($_POST['inhuren_split_hero_nonce'], 'inhuren_split_hero_save')) return;

    foreach (['split_hero_title', 'split_hero_accent_terms', 'split_hero_description', 'split_hero_social_proof'] as $key) {
        if (array_key_exists($key, $_POST)) {
            update_post_meta($post_id, $key, sanitize_textarea_field(wp_unslash($_POST[$key])));
        }
    }
});

function inhuren_split_hero_highlight(string $text, string $terms_raw): string {
    $escaped = esc_html($text);

    if (!$terms_raw) return $escaped;

    $terms = array_filter(array_map('trim', explode(',', $terms_raw)));
    if (empty($terms)) return $escaped;

    foreach ($terms as $term) {
        $escaped = preg_replace(
            '/(' . preg_quote(esc_html($term), '/') . ')/iu',
            '<span class="hero-title__accent">$1</span>',
            $escaped
        );
    }

    return $escaped;
}
