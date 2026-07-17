<?php
if (!defined('ABSPATH')) {
    exit;
}

$page_id = get_the_ID();
$page_slug = get_post_field('post_name', $page_id);
$template_slug = get_page_template_slug($page_id);
$landing_variant = $page_slug;

if ($template_slug === 'page-werkgever.php') {
    $landing_variant = 'werkgever';
} elseif ($template_slug === 'page-werkzoekende.php') {
    $landing_variant = 'werkzoekende';
}

$default_content = [
    'werkzoekende' => [
        'eyebrow' => 'Voor professionals in de non-profitsector',
        'intro' => 'Ontdek vacatures, betekenisvolle organisaties en nieuwe kansen om met jouw talent impact te maken binnen non-profits.',
        'primary_label' => 'Ik zoek werk',
        'primary_url' => home_url('/vacatures/'),
        'secondary_label' => 'Bekijk organisaties',
        'secondary_url' => home_url('/organisaties/'),
        'text_link_label' => 'Of schrijf je in voor vacature-updates',
        'text_link_url' => home_url('/nieuwsbrief/'),
    ],
    'werkgever' => [
        'eyebrow' => 'Voor organisaties met een missie',
        'intro' => 'Bereik professionals die bewust kiezen voor maatschappelijke impact en presenteer jouw organisatie overtuigend aan nieuw talent.',
        'primary_label' => 'Vacature plaatsen',
        'primary_url' => home_url('/vacature-plaatsen/'),
        'secondary_label' => 'Bekijk mogelijkheden',
        'secondary_url' => home_url('/adverteren/'),
        'text_link_label' => '',
        'text_link_url' => '',
    ],
];

$defaults = $default_content[$landing_variant] ?? [
    'eyebrow' => '',
    'intro' => '',
    'primary_label' => '',
    'primary_url' => '',
    'secondary_label' => '',
    'secondary_url' => '',
    'text_link_label' => '',
    'text_link_url' => '',
];

$eyebrow = get_post_meta($page_id, 'landing_eyebrow', true);
$intro = has_excerpt($page_id) ? get_the_excerpt($page_id) : '';
$primary_label = get_post_meta($page_id, 'landing_primary_button_text', true);
$primary_url = get_post_meta($page_id, 'landing_primary_button_url', true);
$secondary_label = get_post_meta($page_id, 'landing_secondary_button_text', true);
$secondary_url = get_post_meta($page_id, 'landing_secondary_button_url', true);
$text_link_label = get_post_meta($page_id, 'landing_text_link_text', true);
$text_link_url = get_post_meta($page_id, 'landing_text_link_url', true);
$hero_image_url = get_the_post_thumbnail_url($page_id, 'full');
$landing_classes = 'fondsen-landing fondsen-landing--' . sanitize_html_class($landing_variant ?: 'default');

$eyebrow = $eyebrow ?: $defaults['eyebrow'];
$intro = $intro ?: $defaults['intro'];
$primary_label = $primary_label ?: $defaults['primary_label'];
$primary_url = $primary_url ?: $defaults['primary_url'];
$secondary_label = $secondary_label ?: $defaults['secondary_label'];
$secondary_url = $secondary_url ?: $defaults['secondary_url'];
$text_link_label = $text_link_label ?: $defaults['text_link_label'];
$text_link_url = $text_link_url ?: $defaults['text_link_url'];
?>

<main id="content" <?php post_class($landing_classes); ?>>
    <section class="fondsen-landing__hero">
        <div class="fondsen-landing__hero-inner">
            <div class="fondsen-landing__panel">
                <div class="fondsen-landing__panel-content">
                    <?php if ($eyebrow) : ?>
                        <p class="fondsen-landing__eyebrow"><?php echo esc_html($eyebrow); ?></p>
                    <?php endif; ?>

                    <h1 class="fondsen-landing__title"><?php the_title(); ?></h1>

                    <?php if ($intro) : ?>
                        <div class="fondsen-landing__intro">
                            <?php echo wp_kses_post(wpautop($intro)); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($primary_label || $secondary_label) : ?>
                        <div class="fondsen-landing__actions">
                            <?php if ($primary_label && $primary_url) : ?>
                                <a class="fondsen-landing__button fondsen-landing__button--primary" href="<?php echo esc_url($primary_url); ?>">
                                    <?php echo esc_html($primary_label); ?>
                                </a>
                            <?php endif; ?>

                            <?php if ($secondary_label && $secondary_url) : ?>
                                <a class="fondsen-landing__button fondsen-landing__button--secondary" href="<?php echo esc_url($secondary_url); ?>">
                                    <?php echo esc_html($secondary_label); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($text_link_label && $text_link_url) : ?>
                        <a class="fondsen-landing__text-link" href="<?php echo esc_url($text_link_url); ?>">
                            <?php echo esc_html($text_link_label); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="fondsen-landing__media">
                <?php if ($hero_image_url) : ?>
                    <img
                        class="fondsen-landing__image"
                        src="<?php echo esc_url($hero_image_url); ?>"
                        alt="<?php echo esc_attr(get_the_title($page_id)); ?>"
                    >
                <?php else : ?>
                    <div class="fondsen-landing__image-placeholder" aria-hidden="true"></div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php if ($landing_variant !== 'werkgever') : ?>
        <section class="fondsen-landing__content-wrap">
            <div class="fondsen-landing__content">
                <?php
                while (have_posts()) :
                    the_post();
                    the_content();
                endwhile;
                ?>
            </div>
        </section>
    <?php endif; ?>
</main>
