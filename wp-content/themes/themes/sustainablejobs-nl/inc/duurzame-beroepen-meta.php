<?php
if (!defined('ABSPATH')) exit;

/**
 * Vinkje "Dit is een duurzaam beroep" op pagina's.
 * Pagina's die dit vinkje aan hebben staan verschijnen automatisch
 * in het overzicht op /duurzame-beroepen/ via de [duurzame_beroepen] shortcode.
 */

define('SJ_DUURZAAM_BEROEP_META_KEY', '_sj_is_duurzaam_beroep');

add_action('add_meta_boxes', function () {
    add_meta_box(
        'sj_duurzaam_beroep',
        'Duurzame beroepen',
        'sj_render_duurzaam_beroep_metabox',
        'page',
        'side',
        'default'
    );
});

function sj_render_duurzaam_beroep_metabox($post) {
    wp_nonce_field('sj_duurzaam_beroep_save', 'sj_duurzaam_beroep_nonce');
    $checked = (bool) get_post_meta($post->ID, SJ_DUURZAAM_BEROEP_META_KEY, true);
    ?>
    <label style="display:flex;align-items:center;gap:8px;">
        <input type="checkbox" name="sj_is_duurzaam_beroep" value="1" <?php checked($checked); ?>>
        Dit is een duurzaam beroep
    </label>
    <p class="description">Deze pagina verschijnt dan automatisch in het overzicht op /duurzame-beroepen/.</p>
    <?php
}

add_action('save_post_page', function ($post_id) {
    if (!isset($_POST['sj_duurzaam_beroep_nonce']) || !wp_verify_nonce($_POST['sj_duurzaam_beroep_nonce'], 'sj_duurzaam_beroep_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_page', $post_id)) {
        return;
    }

    if (!empty($_POST['sj_is_duurzaam_beroep'])) {
        update_post_meta($post_id, SJ_DUURZAAM_BEROEP_META_KEY, '1');
    } else {
        delete_post_meta($post_id, SJ_DUURZAAM_BEROEP_META_KEY);
    }
});
