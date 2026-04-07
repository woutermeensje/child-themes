<?php
/**
 * Custom meta box: "Geplaatst door"
 * Hiermee kan per artikel een weergavenaam worden ingesteld
 * (bijv. "Redactie", "Onderzoeksteam", "Duurzaamheidsdesk").
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registreer de meta box op het post edit-scherm.
 */
add_action( 'add_meta_boxes', function () {
    add_meta_box(
        'sj_artikel_auteur',
        'Geplaatst door',
        'sj_render_auteur_meta_box',
        'post',
        'side',
        'high'
    );
} );

/**
 * Render het veld in de meta box.
 */
function sj_render_auteur_meta_box( $post ) {
    $waarde = get_post_meta( $post->ID, '_sj_artikel_auteur', true );
    wp_nonce_field( 'sj_auteur_opslaan', 'sj_auteur_nonce' );
    ?>
    <p style="margin-top:0;">
        <label for="sj_artikel_auteur" style="display:block;font-weight:600;margin-bottom:6px;">
            Weergavenaam auteur
        </label>
        <input
            type="text"
            id="sj_artikel_auteur"
            name="sj_artikel_auteur"
            value="<?php echo esc_attr( $waarde ); ?>"
            placeholder="bijv. Redactie, Onderzoeksteam…"
            style="width:100%;"
        >
        <span style="display:block;margin-top:6px;font-size:12px;color:#666;">
            Laat leeg om geen auteur te tonen.
        </span>
    </p>
    <?php
}

/**
 * Sla de waarde op bij het opslaan van het bericht.
 */
add_action( 'save_post', function ( $post_id ) {
    if (
        ! isset( $_POST['sj_auteur_nonce'] ) ||
        ! wp_verify_nonce( $_POST['sj_auteur_nonce'], 'sj_auteur_opslaan' )
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
        $waarde = sanitize_text_field( $_POST['sj_artikel_auteur'] );
        update_post_meta( $post_id, '_sj_artikel_auteur', $waarde );
    }
} );
