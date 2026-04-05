<?php
/*
Template Name: Landingspagina-template
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		$content               = get_the_content();
		$products_shortcodes   = '';
		$landing_text_content  = $content;

		if ( preg_match_all( '/\[products[^\]]*\]/i', $content, $matches ) ) {
			$products_shortcodes  = implode( "\n", $matches[0] );
			$landing_text_content = preg_replace( '/\[products[^\]]*\]/i', '', $content );
		}

		$landing_text_content = trim( (string) $landing_text_content );
		include get_stylesheet_directory() . '/template-parts/hero-homepage.php';
		?>
		<style>
		.pms-landing-detail {
			max-width: 1200px;
			margin: 32px auto 0;
			padding: 32px;
			background: #fff;
			border: 1px solid #DEDEDE;
			border-radius: 5px;
		}

		.pms-landing-detail__title {
			margin: 0 0 16px;
			color: #2d241b;
		}

		.pms-landing-detail__content {
			color: #4b4136;
			line-height: 1.7;
		}

		.pms-landing-detail__content > :first-child {
			margin-top: 0;
		}

		.pms-landing-detail__content > :last-child {
			margin-bottom: 0;
		}

		@media (max-width: 1240px) {
			.pms-landing-detail {
				margin-left: 20px;
				margin-right: 20px;
			}
		}

		@media (max-width: 767px) {
			.pms-landing-detail {
				padding: 20px;
				margin-top: 24px;
			}
		}
		</style>
		<?php

		if ( '' !== $products_shortcodes ) {
			echo do_shortcode( $products_shortcodes );
		} else {
			the_content();
		}

		if ( '' !== wp_strip_all_tags( $landing_text_content ) ) :
			?>
			<section class="pms-landing-detail">
				<h2 class="pms-landing-detail__title"><?php the_title(); ?></h2>
				<div class="pms-landing-detail__content">
					<?php echo apply_filters( 'the_content', $landing_text_content ); ?>
				</div>
			</section>
			<?php
		endif;
	endwhile;
endif;

get_footer();
