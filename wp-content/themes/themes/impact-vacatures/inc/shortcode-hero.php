<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [fn_hero]
 *
 * Renders the Impact Vacatures hero block standalone, voor gebruik buiten de vacaturepagina.
 *
 * Attributen:
 *   eyebrow  — kleine tekst boven de titel       (default: "Impact Vacatures")
 *   titel    — h1 tekst                          (default: "Impactvacatures.nl: Vacaturesite voor impact vacature en non profits.")
 *   subtitel — alinea onder de titel             (default: leeg)
 *   afbeelding — URL van de achtergrondafbeelding (default: zelfde afbeelding als vacaturepagina)
 */
add_shortcode('fn_hero', 'fn_hero_shortcode');

function fn_hero_shortcode(array $atts): string {
    $atts = shortcode_atts([
        'eyebrow'     => 'Impact Vacatures',
        'titel'       => 'Impactvacatures.nl: Vacaturesite voor impact vacature en non profits.',
        'subtitel'    => '',
        'afbeelding'  => '',
    ], $atts, 'fn_hero');

    if (empty($atts['afbeelding'])) {
        $atts['afbeelding'] = function_exists('impact_vacatures_get_default_hero_image_url')
            ? impact_vacatures_get_default_hero_image_url(get_the_ID())
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
