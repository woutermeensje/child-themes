<?php
/**
 * Custom meta box: "Geplaatst door" voor blogartikelen.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'rn_get_article_author' ) ) {
    function rn_get_article_author( $post_id = null ): string {
        $post_id = $post_id ?: get_the_ID();

        if ( ! $post_id ) {
            return '';
        }

        $author = get_post_meta( $post_id, '_rn_artikel_auteur', true );

        if ( '' === $author ) {
            $author = get_post_meta( $post_id, '_sj_artikel_auteur', true );
        }

        return (string) $author;
    }
}

add_action( 'add_meta_boxes', function () {
    add_meta_box(
        'rn_artikel_auteur',
        'Geplaatst door',
        'rn_render_auteur_meta_box',
        'post',
        'side',
        'high'
    );
} );

function rn_render_auteur_meta_box( $post ): void {
    $waarde = rn_get_article_author( $post->ID );
    wp_nonce_field( 'rn_auteur_opslaan', 'rn_auteur_nonce' );
    ?>
    <p style="margin-top:0;">
        <label for="rn_artikel_auteur" style="display:block;font-weight:600;margin-bottom:6px;">
            Weergavenaam auteur
        </label>
        <input
            type="text"
            id="rn_artikel_auteur"
            name="rn_artikel_auteur"
            value="<?php echo esc_attr( $waarde ); ?>"
            placeholder="bijv. Redactie, Recruitmentdesk..."
            style="width:100%;"
        >
        <span style="display:block;margin-top:6px;font-size:12px;color:#666;">
            Laat leeg om geen auteur te tonen.
        </span>
    </p>
    <?php
}

add_action( 'save_post', function ( $post_id ) {
    if (
        ! isset( $_POST['rn_auteur_nonce'] ) ||
        ! wp_verify_nonce( $_POST['rn_auteur_nonce'], 'rn_auteur_opslaan' )
    ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['rn_artikel_auteur'] ) ) {
        update_post_meta( $post_id, '_rn_artikel_auteur', sanitize_text_field( $_POST['rn_artikel_auteur'] ) );
    }
} );
