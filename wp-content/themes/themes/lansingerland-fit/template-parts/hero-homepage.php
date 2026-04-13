<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$page_id        = get_the_ID();
$hero_image_url = get_the_post_thumbnail_url( $page_id, 'full' );
$contact_phone  = get_post_meta( $page_id, 'hero_phone', true ) ?: '06 34049481';
$contact_email  = get_post_meta( $page_id, 'hero_email', true ) ?: 'cindystarre@hotmail.com';
?>

<section class="welcome-v2">
    <header class="welcome-v2__hero welcome-v2__hero--lansingerland-fit" aria-label="Introductie Lansingerland Fit">
        <div class="welcome-v2__hero-left">
            <div class="welcome-v2__hero-content">
                <div class="welcome-v2__hero-copy">
                    <?php lf_render_breadcrumbs( 'blog-breadcrumbs' ); ?>

                    <h1 class="welcome-v2__title welcome-v2__title--homepage"><?php the_title(); ?></h1>

                    <?php if ( has_excerpt( $page_id ) ) : ?>
                        <p class="welcome-v2__lead"><?php echo esc_html( get_the_excerpt( $page_id ) ); ?></p>
                    <?php else : ?>
                        <p class="welcome-v2__lead">Werk aan een fitter, sterker en energieker leven met persoonlijke begeleiding, slimme trainingen en een aanpak die past bij jouw doel.</p>
                    <?php endif; ?>

                    <div class="welcome-v2__actions">
                        <a href="<?php echo esc_url( home_url( '/lesrooster/' ) ); ?>" class="rn-btn rn-btn--accent">Lesrooster</a>
                        <a href="<?php echo esc_url( home_url( '/informatie-aanvragen/' ) ); ?>" class="rn-btn rn-btn--outline">Informatie aanvragen</a>
                    </div>
                </div>

                <div class="welcome-v2__hero-actions-panel">
                    <div class="welcome-v2__hero-contact-list" aria-label="Contactmogelijkheden">
                        <a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $contact_phone ) ); ?>" class="welcome-v2__hero-contact-item">
                            <span class="welcome-v2__hero-contact-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" focusable="false"><path d="M6.6 10.8a15.5 15.5 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.24c1.08.36 2.24.54 3.43.54a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.97 21 3 13.03 3 3a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.19.18 2.35.54 3.43a1 1 0 0 1-.24 1Z" fill="currentColor"/></svg>
                            </span>
                            <span><?php echo esc_html( $contact_phone ); ?></span>
                        </a>
                        <a href="mailto:<?php echo esc_attr( $contact_email ); ?>" class="welcome-v2__hero-contact-item">
                            <span class="welcome-v2__hero-contact-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" focusable="false"><path d="M4 5h16a2 2 0 0 1 2 2v.28l-10 5.88L2 7.28V7a2 2 0 0 1 2-2Zm18 4.03V17a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9.03l9.49 5.58a1 1 0 0 0 1.02 0Z" fill="currentColor"/></svg>
                            </span>
                            <span><?php echo esc_html( $contact_email ); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="welcome-v2__hero-right" aria-hidden="true"<?php if ( $hero_image_url ) : ?> style="background-image: url('<?php echo esc_url( $hero_image_url ); ?>');"<?php endif; ?>></div>
    </header>
</section>
