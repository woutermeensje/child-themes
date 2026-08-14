<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$page_id        = get_the_ID();
$hero_image_url = get_the_post_thumbnail_url( $page_id, 'full' );
$eyebrow        = get_post_meta( $page_id, 'hero_eyebrow', true ) ?: 'LansingerlandFit';
$eyebrow        = str_replace( 'Lansingerland Fit', 'LansingerlandFit', $eyebrow );

$button_font_style = 'font-family: "Work Sans", "Inter", sans-serif !important; font-weight: 700 !important;';
$bg_style = $hero_image_url ? ' style="background-image: url(\'' . esc_url( $hero_image_url ) . '\');"' : '';
?>

<section class="lf-hero-home" aria-label="Introductie LansingerlandFit">
    <div class="lf-hero-home__bg"<?php echo $bg_style; ?> aria-hidden="true"></div>

    <div class="lf-hero-home__inner">
        <span class="lf-hero-home__eyebrow"><?php echo esc_html( $eyebrow ); ?></span>

        <div class="lf-hero-home__title-wrap">
            <h1 class="lf-hero-home__title"><?php the_title(); ?></h1>

            <?php if ( has_excerpt( $page_id ) ) : ?>
                <p class="lf-hero-home__lead"><?php echo esc_html( get_the_excerpt( $page_id ) ); ?></p>
            <?php else : ?>
                <p class="lf-hero-home__lead">Buiten sporten in Lansingerland - groepslessen, XCO walking, outdoor bootcamp, personal training en bedrijfsbootcamp.</p>
            <?php endif; ?>
        </div>

        <div class="lf-hero-home__actions">
            <a href="<?php echo esc_url( home_url( '/gratis-proefles/' ) ); ?>" class="lf-hero-home__btn lf-hero-home__btn--primary" style="<?php echo esc_attr( $button_font_style ); ?>">Gratis proefles</a>
            <a href="<?php echo esc_url( home_url( '/lesrooster/' ) ); ?>" class="lf-hero-home__btn lf-hero-home__btn--outline" style="<?php echo esc_attr( $button_font_style ); ?>">Lesrooster</a>
        </div>
    </div>

</section>
