<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [sj_uitgelichte_werkgevers]
 * Toont een 3-koloms grid van uitgelichte werkgevers.
 * Logo, naam (uit alt-text) en beschrijving (uit afbeelding-beschrijving).
 */
add_shortcode('sj_uitgelichte_werkgevers', 'sj_uitgelichte_werkgevers_shortcode');

function sj_uitgelichte_werkgevers_shortcode(): string {
    global $wpdb;

    $ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts}
             WHERE post_type = 'attachment'
               AND post_status = 'inherit'
               AND post_title LIKE %s
             ORDER BY post_title ASC",
            '%uitgelichte-werkgever%'
        )
    );

    if (empty($ids)) {
        return '';
    }

    ob_start();
    ?>
    <div class="sj-uw">
        <div class="sj-uw__grid">
            <?php foreach ($ids as $id):
                $id          = (int) $id;
                $img_url     = wp_get_attachment_image_url($id, 'medium');
                $naam        = get_post_meta($id, '_wp_attachment_image_alt', true);
                $beschrijving = get_post_field('post_content', $id);
                if (!$img_url) continue;
            ?>
            <div class="sj-uw__card">
                <div class="sj-uw__logo-wrap">
                    <img src="<?php echo esc_url($img_url); ?>"
                         alt="<?php echo esc_attr($naam ?: get_the_title($id)); ?>"
                         class="sj-uw__logo"
                         loading="lazy">
                </div>
                <?php if ($naam): ?>
                <h3 class="sj-uw__naam"><?php echo esc_html($naam); ?></h3>
                <?php endif; ?>
                <?php if ($beschrijving): ?>
                <p class="sj-uw__desc"><?php echo esc_html(wp_strip_all_tags($beschrijving)); ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <style>
    .sj-uw,
    .sj-uw *,
    .sj-uw *::before,
    .sj-uw *::after { box-sizing: border-box; }

    .sj-uw {
        font-family: 'Poppins', sans-serif;
        width: 100%;
    }

    .sj-uw__grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }

    .sj-uw__card {
        background: #ffffff;
        border: 1px solid #DEDEDE;
        border-radius: 6px;
        padding: 28px 24px;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        transition: border-color .2s ease, transform .2s ease, box-shadow .2s ease;
    }

    .sj-uw__card:hover {
        border-color: var(--color-primary, #2C8FAF);
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(44, 143, 175, .1);
    }

    .sj-uw__logo-wrap {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: #ffffff;
        border: 1px solid #DEDEDE;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-bottom: 20px;
        padding: 10px;
        overflow: hidden;
    }

    .sj-uw__logo {
        width: 100%;
        height: 100%;
        object-fit: contain;
        filter: grayscale(30%) opacity(.85);
        transition: filter .25s ease;
    }

    .sj-uw__card:hover .sj-uw__logo {
        filter: grayscale(0%) opacity(1);
    }

    .sj-uw__naam {
        font-family: 'Inter', sans-serif !important;
        font-size: 16px !important;
        font-weight: 700 !important;
        color: var(--color-primary, #2C8FAF) !important;
        margin: 0 0 10px !important;
        line-height: 1.3 !important;
    }

    .sj-uw__desc {
        font-family: 'Poppins', sans-serif !important;
        font-size: 14px !important;
        font-weight: 300 !important;
        color: #555 !important;
        margin: 0 !important;
        line-height: 1.65 !important;
    }

    @media (max-width: 768px) {
        .sj-uw__grid { grid-template-columns: repeat(2, 1fr); gap: 14px; }
        .sj-uw__card { padding: 22px 18px; }
    }

    @media (max-width: 480px) {
        .sj-uw__grid { grid-template-columns: 1fr; gap: 12px; }
        .sj-uw__card { padding: 20px 16px; }
        .sj-uw__logo { max-height: 44px; }
    }
    </style>
    <?php
    return ob_get_clean();
}
