<?php
if (!defined('ABSPATH')) {
    exit;
}

$page_id        = get_the_ID();
$hero_image_url = get_the_post_thumbnail_url($page_id, 'full');
$contact_phone  = get_post_meta($page_id, 'hero_phone', true) ?: '085 239 2040';
$contact_email  = get_post_meta($page_id, 'hero_email', true) ?: 'support@student-inhuren.nl';
?>

<section class="welcome-v2">
    <header class="welcome-v2__hero welcome-v2__hero--freelancer-inhuren" aria-label="Introductie Studentinhuren.nl">

        <div class="welcome-v2__hero-left">
            <div class="welcome-v2__hero-content">

                <div class="welcome-v2__hero-copy">

                    <?php si_render_breadcrumbs('blog-breadcrumbs'); ?>

                    <h1 class="welcome-v2__title"><?php the_title(); ?></h1>

                    <?php if (has_excerpt($page_id)) : ?>
                        <p class="welcome-v2__lead"><?php echo esc_html(get_the_excerpt($page_id)); ?></p>
                    <?php else : ?>
                        <p class="welcome-v2__lead">Ben je op zoek naar personeel of zoek je opdrachten en projecten? Het team van Studentinhuren.nl helpt jou verder!</p>
                    <?php endif; ?>

                    <div class="welcome-v2__actions">
                        <a href="https://platform.student-inhuren.nl/aanmelden" class="si-btn si-btn--accent">Ik zoek werk</a>
                        <a href="<?php echo esc_url(home_url('/informatie-aanvragen/')); ?>" class="si-btn si-btn--secondary">Ik zoek personeel</a>
                    </div>

                    <a class="welcome-v2__direct-link" href="<?php echo esc_url(home_url('/opdracht-plaatsen/')); ?>">Of plaats direct een gratis opdracht</a>

                </div>

                <div class="welcome-v2__hero-actions-panel">
                    <div class="welcome-v2__hero-contact-list" aria-label="Contactmogelijkheden">
                        <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $contact_phone)); ?>" class="welcome-v2__hero-contact-item">
                            <i class="ph ph-phone-call" aria-hidden="true"></i>
                            <span><?php echo esc_html($contact_phone); ?></span>
                        </a>
                        <a href="mailto:<?php echo esc_attr($contact_email); ?>" class="welcome-v2__hero-contact-item">
                            <i class="ph ph-envelope-simple" aria-hidden="true"></i>
                            <span><?php echo esc_html($contact_email); ?></span>
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <div class="welcome-v2__hero-right" aria-hidden="true"
            <?php if ($hero_image_url) : ?>
                style="background-image: url('<?php echo esc_url($hero_image_url); ?>');"
            <?php endif; ?>>
        </div>

    </header>
</section>
