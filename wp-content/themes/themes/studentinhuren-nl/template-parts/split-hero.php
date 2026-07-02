<?php
if (!defined('ABSPATH')) {
    exit;
}

$page_id      = get_the_ID();
$image_url    = get_the_post_thumbnail_url($page_id, 'full');
$title_raw    = get_post_meta($page_id, 'split_hero_title', true) ?: 'Flexibele inhuur voor jouw vacature, opdracht of project.';
$accent_terms = get_post_meta($page_id, 'split_hero_accent_terms', true) ?: 'Flexibele inhuur';
$description  = get_post_meta($page_id, 'split_hero_description', true) ?: 'Bekijk 78 openstaande vacatures, projecten én (freelance) opdrachten voor studenten, starters en young professionals. Of maak gelijk een account aan.';

$title_html = function_exists('studentinhuren_split_hero_highlight')
    ? studentinhuren_split_hero_highlight($title_raw, $accent_terms)
    : esc_html($title_raw);

$description_link_style = 'color: #8FC0FF !important; font-weight: 600 !important; text-decoration: none !important;';
$personnel_button_style = "font-family: 'Work Sans', sans-serif !important; font-weight: 700 !important;";

$description_html = esc_html($description);
$description_html = str_replace(
    '78 openstaande vacatures',
    '<a class="si-split-hero__description-link" style="' . esc_attr($description_link_style) . '" href="https://platform.student-inhuren.nl/" target="_blank" rel="noopener noreferrer">78 openstaande vacatures</a>',
    $description_html
);
$description_html = str_replace(
    'account',
    '<a class="si-split-hero__description-link" style="' . esc_attr($description_link_style) . '" href="https://platform.student-inhuren.nl/aanmelden" target="_blank" rel="noopener noreferrer">account</a>',
    $description_html
);
?>

<section class="si-split-hero"
    aria-labelledby="split-hero-title"
    <?php if ($image_url) : ?>
        style="background-image: url('<?php echo esc_url($image_url); ?>');"
    <?php endif; ?>>

    <div class="si-split-hero__inner">
        <div class="si-split-hero__title-wrap">
            <h1 class="si-split-hero__title" id="split-hero-title"><?php echo wp_kses($title_html, ['span' => ['class' => []]]); ?></h1>

            <?php if ($description) : ?>
                <p class="si-split-hero__description">
                    <?php echo wp_kses($description_html, ['a' => ['class' => [], 'style' => [], 'href' => [], 'target' => [], 'rel' => []]]); ?>
                </p>
            <?php endif; ?>

            <div class="si-split-hero__actions">
                <a class="si-split-hero__personnel-btn" href="<?php echo esc_url(home_url('/informatie-aanvragen/')); ?>" target="_blank" rel="noopener noreferrer" style="<?php echo esc_attr($personnel_button_style); ?>">Ik zoek personeel</a>
            </div>
        </div>
    </div>
</section>
