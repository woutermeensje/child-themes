<?php
/*
Template Name: landingspagina-template
Template Post Type: page
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( have_posts() ) {
	the_post();
}

$page_id    = get_the_ID();
$hero_title = trim( (string) get_post_meta( $page_id, '_mh_landing_hero_title', true ) );
$hero_text  = trim( (string) get_post_meta( $page_id, '_mh_landing_hero_text', true ) );

if ( '' === $hero_title ) {
	$hero_title = get_the_title( $page_id );
}

if ( '' === $hero_text && has_excerpt( $page_id ) ) {
	$hero_text = get_the_excerpt( $page_id );
}

if ( '' === $hero_text ) {
	$hero_text = 'Bekijk het complete aanbod van modulaire units voor lange termijn of tijdelijk gebruik.';
}

$hero_image = get_the_post_thumbnail_url( $page_id, 'full' );
$bg_style   = $hero_image ? ' style="background-image: url(\'' . esc_url( $hero_image ) . '\');"' : '';

get_header();
?>

<div class="mh-lp">
	<section class="mh-home-hero"<?php echo $bg_style; ?>>
		<div class="mh-home-hero__inner">
			<span class="mh-home-hero__eyebrow">Modulaire Huisvesting</span>
			<div class="mh-home-hero__title-wrap">
				<h1 class="mh-home-hero__title"><?php echo esc_html( $hero_title ); ?></h1>
				<p class="mh-home-hero__subtitle"><?php echo nl2br( esc_html( $hero_text ) ); ?></p>
				<div class="mh-home-hero__actions">
					<a href="<?php echo esc_url( home_url( '/informatie-aanvragen/' ) ); ?>" class="mh-lp__btn mh-lp__btn--outline">Informatie aanvragen</a>
				</div>
			</div>
		</div>
	</section>
</div>

<?php the_content(); ?>

<?php
get_footer();
