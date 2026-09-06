<?php
if (!defined('ABSPATH')) exit;

/**
 * Vinkje "Dit is een categorie-overzichtspagina" op pagina's.
 * Pagina's die dit vinkje aan hebben staan verschijnen automatisch
 * in het overzicht via de [jobs_categories_overzicht] shortcode.
 */

if (!defined('FN_JOBS_CATEGORIE_PAGE_META_KEY')) {
    define('FN_JOBS_CATEGORIE_PAGE_META_KEY', '_fn_is_jobs_categorie_page');
}

add_action('add_meta_boxes', function () {
    add_meta_box(
        'fn_jobs_categorie_page',
        'Categorie overzicht',
        'fn_render_jobs_categorie_page_metabox',
        'page',
        'side',
        'default'
    );
});

function fn_render_jobs_categorie_page_metabox($post) {
    wp_nonce_field('fn_jobs_categorie_page_save', 'fn_jobs_categorie_page_nonce');
    $checked = (bool) get_post_meta($post->ID, FN_JOBS_CATEGORIE_PAGE_META_KEY, true);
    ?>
    <label style="display:flex;align-items:center;gap:8px;">
        <input type="checkbox" name="fn_is_jobs_categorie_page" value="1" <?php checked($checked); ?>>
        Dit is een categorie-overzichtspagina
    </label>
    <p class="description">Deze pagina verschijnt dan automatisch in het overzicht via [jobs_categories_overzicht].</p>
    <?php
}

add_action('save_post_page', function ($post_id) {
    if (
        !isset($_POST['fn_jobs_categorie_page_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['fn_jobs_categorie_page_nonce'])), 'fn_jobs_categorie_page_save')
    ) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_page', $post_id)) {
        return;
    }

    if (!empty($_POST['fn_is_jobs_categorie_page'])) {
        update_post_meta($post_id, FN_JOBS_CATEGORIE_PAGE_META_KEY, '1');
    } else {
        delete_post_meta($post_id, FN_JOBS_CATEGORIE_PAGE_META_KEY);
    }
});
