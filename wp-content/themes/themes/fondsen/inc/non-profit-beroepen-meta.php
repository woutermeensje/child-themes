<?php
if (!defined('ABSPATH')) exit;

/**
 * Vinkje "Dit is een non-profit beroep" op pagina's.
 * Pagina's die dit vinkje aan hebben staan verschijnen automatisch
 * in het overzicht via de [non_profit_beroepen] shortcode.
 */

if (!defined('FN_NON_PROFIT_BEROEP_META_KEY')) {
    define('FN_NON_PROFIT_BEROEP_META_KEY', '_fn_is_non_profit_beroep');
}

add_action('add_meta_boxes', function () {
    add_meta_box(
        'fn_non_profit_beroep',
        'Non-profit beroepen',
        'fn_render_non_profit_beroep_metabox',
        'page',
        'side',
        'default'
    );
});

function fn_render_non_profit_beroep_metabox($post) {
    wp_nonce_field('fn_non_profit_beroep_save', 'fn_non_profit_beroep_nonce');
    $checked = (bool) get_post_meta($post->ID, FN_NON_PROFIT_BEROEP_META_KEY, true);
    ?>
    <label style="display:flex;align-items:center;gap:8px;">
        <input type="checkbox" name="fn_is_non_profit_beroep" value="1" <?php checked($checked); ?>>
        Dit is een non-profit beroep
    </label>
    <p class="description">Deze pagina verschijnt dan automatisch in het overzicht via [non_profit_beroepen].</p>
    <?php
}

add_action('save_post_page', function ($post_id) {
    if (
        !isset($_POST['fn_non_profit_beroep_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['fn_non_profit_beroep_nonce'])), 'fn_non_profit_beroep_save')
    ) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_page', $post_id)) {
        return;
    }

    if (!empty($_POST['fn_is_non_profit_beroep'])) {
        update_post_meta($post_id, FN_NON_PROFIT_BEROEP_META_KEY, '1');
    } else {
        delete_post_meta($post_id, FN_NON_PROFIT_BEROEP_META_KEY);
    }
});
