<?php
if (!defined('ABSPATH')) exit;


// Prijs / prijsindicatie meta box
add_action('add_meta_boxes', function () {
    add_meta_box(
        'mh_unit_price_note',
        'Prijsindicatie',
        'mh_unit_price_note_callback',
        'mh_unit',
        'side',
        'default'
    );
});

function mh_unit_price_note_callback($post){
    $value = get_post_meta($post->ID, '_mh_unit_price_note', true);
    wp_nonce_field('mh_unit_price_note_save', 'mh_unit_price_note_nonce');
    ?>
    <input
        type="text"
        name="mh_unit_price_note"
        value="<?php echo esc_attr($value); ?>"
        style="width:100%;"
        placeholder="Bijv. Prijs op aanvraag"
    />
    <p style="font-size:12px;color:#666;margin-top:6px;">
        Wordt getoond bij de knop in het overzicht
    </p>
    <?php
}

// Opslaan
add_action('save_post', function ($post_id) {
    if (!isset($_POST['mh_unit_price_note_nonce'])) return;
    if (!wp_verify_nonce($_POST['mh_unit_price_note_nonce'], 'mh_unit_price_note_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (isset($_POST['mh_unit_price_note'])) {
        update_post_meta(
            $post_id,
            '_mh_unit_price_note',
            sanitize_text_field($_POST['mh_unit_price_note'])
        );
    }
});
