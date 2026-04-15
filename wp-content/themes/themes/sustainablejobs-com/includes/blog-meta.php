<?php
/**
 * Custom meta box: "Posted by"
 * Allows setting a display name per article
 * (e.g. "Editorial team", "Research team", "Sustainability desk").
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the meta box on the post edit screen.
 */
add_action( 'add_meta_boxes', function () {
    add_meta_box(
        'sj_artikel_auteur',
        'Posted by',
        'sc_render_author_meta_box',
        'post',
        'side',
        'high'
    );
} );

/**
 * Render the field in the meta box.
 */
function sc_render_author_meta_box( $post ) {
    $value = get_post_meta( $post->ID, '_sj_artikel_auteur', true );
    wp_nonce_field( 'sc_author_save', 'sc_author_nonce' );
    ?>
    <p style="margin-top:0;">
        <label for="sj_artikel_auteur" style="display:block;font-weight:600;margin-bottom:6px;">
            Author display name
        </label>
        <input
            type="text"
            id="sj_artikel_auteur"
            name="sj_artikel_auteur"
            value="<?php echo esc_attr( $value ); ?>"
            placeholder="e.g. Editorial team, Research team…"
            style="width:100%;"
        >
        <span style="display:block;margin-top:6px;font-size:12px;color:#666;">
            Leave empty to hide the author.
        </span>
    </p>
    <?php
}

/**
 * Save the value when the post is saved.
 */
add_action( 'save_post', function ( $post_id ) {
    if (
        ! isset( $_POST['sc_author_nonce'] ) ||
        ! wp_verify_nonce( $_POST['sc_author_nonce'], 'sc_author_save' )
    ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['sj_artikel_auteur'] ) ) {
        $value = sanitize_text_field( $_POST['sj_artikel_auteur'] );
        update_post_meta( $post_id, '_sj_artikel_auteur', $value );
    }
} );
