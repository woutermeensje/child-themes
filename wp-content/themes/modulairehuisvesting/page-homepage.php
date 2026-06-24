<?php
/*
Template Name: Homepage
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( have_posts() ) {
	the_post();
}

$hero_image = get_the_post_thumbnail_url( get_the_ID(), 'full' );
$bg_style   = $hero_image ? ' style="background-image: url(\'' . esc_url( $hero_image ) . '\');"' : '';

get_header();
?>

<div class="mh-lp">
	<section class="mh-home-hero"<?php echo $bg_style; ?>>
		<div class="mh-home-hero__inner">
			<span class="mh-home-hero__eyebrow">Modulaire Huisvesting</span>
			<div class="mh-home-hero__title-wrap">
				<h1 class="mh-home-hero__title"><span class="mh-home-hero__accent">Modulaire Units</span> en Oplossingen voor <span class="mh-home-hero__accent">Tijdelijke Huisvesting</span> (zowel koop als huur).</h1>
				<p class="mh-home-hero__subtitle">Neem contact op met ons team en laat je adviseren in de keuze voor de beste unit.</p>
				<div class="mh-home-hero__actions">
					<a href="<?php echo esc_url( home_url( '/modulaire-units/' ) ); ?>" class="mh-lp__btn mh-lp__btn--outline">Bekijk aanbod</a>
				</div>
			</div>
		</div>
	</section>
</div>

<?php the_content(); ?>

<?php
get_footer();
