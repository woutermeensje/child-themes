<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [fn_hero]
 *
 * Renders the fondsen-job-hero block standalone, voor gebruik buiten de vacaturepagina.
 *
 * Attributen:
 *   eyebrow  — kleine tekst boven de titel       (default: "Fondsen.org")
 *   titel    — h1 tekst                          (default: "Vacaturesite en platform voor Non Profits!")
 *   subtitel — alinea onder de titel             (default: leeg)
 *   afbeelding — URL van de achtergrondafbeelding (default: zelfde afbeelding als vacaturepagina)
 */
add_shortcode('fn_hero', 'fn_hero_shortcode');

function fn_hero_shortcode(array $atts): string {
    $atts = shortcode_atts([
        'eyebrow'     => 'Fondsen.org',
        'titel'       => 'Vacaturesite en platform voor <span class="fondsen-job-hero__count">Non Profits</span>!',
        'subtitel'    => '',
        'afbeelding'  => '',
    ], $atts, 'fn_hero');

    if (empty($atts['afbeelding'])) {
        $attachment = get_page_by_path('fondsen-org-non-profit-vacaturesite-en-platform', OBJECT, 'attachment');
        $atts['afbeelding'] = $attachment
            ? wp_get_attachment_url($attachment->ID)
            : get_the_post_thumbnail_url(get_the_ID(), 'full');
    }

    $bg_style = $atts['afbeelding'] ? ' style="background-image: url(\'' . esc_url($atts['afbeelding']) . '\');"' : '';

    ob_start();
    ?>
    <div class="fondsen-job-hero"<?php echo $bg_style; ?>>
        <div class="fondsen-job-hero__inner">
            <?php if ($atts['eyebrow']) : ?>
                <span class="fondsen-job-hero__eyebrow"><?php echo esc_html($atts['eyebrow']); ?></span>
            <?php endif; ?>
            <div class="fondsen-job-hero__title-wrap">
                <h1 class="fondsen-job-hero__title"><?php echo wp_kses_post($atts['titel']); ?></h1>
                <?php if ($atts['subtitel']) : ?>
                    <p class="fondsen-job-hero__subtitle"><?php echo wp_kses_post($atts['subtitel']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
