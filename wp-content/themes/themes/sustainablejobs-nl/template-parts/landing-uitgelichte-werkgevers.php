<?php
if (!defined('ABSPATH')) {
    exit;
}

$page_id         = get_the_ID();
$hero_image_url  = get_the_post_thumbnail_url($page_id, 'full');
$intro           = has_excerpt($page_id) ? get_the_excerpt($page_id) : 'Ontdek vacatures van de meest duurzame en maatschappelijk betrokken werkgevers van Nederland. Organisaties die vooroplopen in de transitie naar een betere wereld.';
$primary_label   = get_post_meta($page_id, 'landing_primary_button_text', true)   ?: 'Profiel aanmaken';
$primary_url     = get_post_meta($page_id, 'landing_primary_button_url', true)    ?: 'https://platform.sustainablejobs.nl/aanmelden';
$secondary_label = get_post_meta($page_id, 'landing_secondary_button_text', true) ?: 'Job alerts instellen';
$secondary_url   = get_post_meta($page_id, 'landing_secondary_button_url', true)  ?: home_url('/job-alerts/');
$contact_phone   = get_post_meta($page_id, 'landing_phone', true) ?: '085 239 2040';
$contact_email   = get_post_meta($page_id, 'landing_email', true) ?: 'support@sustainablejobs.nl';
?>

<main id="content" <?php post_class('fnd-page'); ?>>
    <section class="fnd-hero">

        <div class="fnd-hero__left">

            <div class="fnd-hero__left-main">

                <?php if (function_exists('yoast_breadcrumb')) : ?>
                    <div class="fnd-hero__breadcrumbs">
                        <?php yoast_breadcrumb('<nav class="fnd-hero__breadcrumb-nav" aria-label="Breadcrumb">', '</nav>'); ?>
                    </div>
                <?php endif; ?>

                <h1 class="fnd-hero__title"><?php the_title(); ?></h1>

                <p class="fnd-hero__intro"><?php echo esc_html($intro); ?></p>

                <div class="fnd-hero__actions">
                    <?php if ($primary_label && $primary_url) : ?>
                        <a class="fnd-hero__btn fnd-hero__btn--primary" href="<?php echo esc_url($primary_url); ?>">
                            <?php echo esc_html($primary_label); ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($secondary_label && $secondary_url) : ?>
                        <a class="fnd-hero__btn fnd-hero__btn--secondary" href="<?php echo esc_url($secondary_url); ?>">
                            <?php echo esc_html($secondary_label); ?>
                        </a>
                    <?php endif; ?>
                </div>

            </div>

            <div class="fnd-hero__contact">
                <?php if ($contact_phone) : ?>
                    <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $contact_phone)); ?>" class="fnd-hero__contact-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.63 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.82a16 16 0 0 0 6.29 6.29l.98-.98a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <?php echo esc_html($contact_phone); ?>
                    </a>
                <?php endif; ?>
                <a href="mailto:<?php echo esc_attr($contact_email); ?>" class="fnd-hero__contact-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    <?php echo esc_html($contact_email); ?>
                </a>
            </div>

        </div>

        <div class="fnd-hero__right<?php echo $hero_image_url ? '' : ' fnd-hero__right--placeholder'; ?>"
            <?php if ($hero_image_url) : ?>
                style="background-image: url('<?php echo esc_url($hero_image_url); ?>');"
            <?php endif; ?>>
        </div>

    </section>

    <?php the_content(); ?>
</main>
