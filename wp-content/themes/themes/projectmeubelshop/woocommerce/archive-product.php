<?php
/**
 * Product archive template override for Projectmeubelshop.
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

if ( $is_product_cat = is_tax( 'product_cat' ) ) {
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
}

$term           = $is_product_cat ? get_queried_object() : null;
$term_image_id  = $term ? (int) get_term_meta( $term->term_id, 'pms_hero_image_id', true ) : 0;
$term_image_id  = $term_image_id ?: ( $term ? (int) get_term_meta( $term->term_id, 'thumbnail_id', true ) : 0 );
$term_image_url = $term_image_id ? wp_get_attachment_image_url( $term_image_id, 'full' ) : '';
$term_desc      = $term ? term_description( $term, 'product_cat' ) : '';
$term_excerpt   = $term ? get_term_meta( $term->term_id, 'pms_hero_excerpt', true ) : '';
$term_excerpt   = $term_excerpt ?: wp_strip_all_tags( $term_desc );
?>

<?php if ( $is_product_cat ) : ?>
	<section class="welcome-v2 pms-tax-hero">
		<header class="welcome-v2__hero welcome-v2__hero--projectmeubelshop welcome-v2__hero--product-category" aria-label="Introductie productcategorie">
			<div class="welcome-v2__hero-left">
				<div class="welcome-v2__hero-content">
					<div class="welcome-v2__hero-copy">
						<?php
						if ( function_exists( 'woocommerce_breadcrumb' ) ) {
							woocommerce_breadcrumb(
								array(
									'wrap_before' => '<nav class="blog-breadcrumbs" aria-label="Breadcrumb">',
									'wrap_after'  => '</nav>',
								)
							);
						}
						?>

						<h1 class="welcome-v2__title"><?php single_term_title(); ?></h1>

						<?php if ( $term_excerpt ) : ?>
							<p class="welcome-v2__lead"><?php echo esc_html( $term_excerpt ); ?></p>
						<?php endif; ?>

						<div class="welcome-v2__actions">
							<a class="pms-btn pms-btn--accent" href="#pms-products-grid">Producten bekijken</a>
						</div>
					</div>
				</div>
			</div>

			<div class="welcome-v2__hero-right" aria-hidden="true"<?php if ( $term_image_url ) : ?> style="background-image: url('<?php echo esc_url( $term_image_url ); ?>');"<?php endif; ?>></div>
		</header>
	</section>
<?php else : ?>
	<?php do_action( 'woocommerce_shop_loop_header' ); ?>
<?php endif; ?>

<?php do_action( 'woocommerce_before_main_content' ); ?>

<?php if ( $is_product_cat ) : ?>
	<div id="pms-products-grid" class="pms-tax-content">
<?php endif; ?>

<?php if ( woocommerce_product_loop() ) : ?>
	<div class="pms-tax-toolbar">
		<?php if ( $is_product_cat ) : ?>
			<?php woocommerce_result_count(); ?>
			<?php woocommerce_catalog_ordering(); ?>
		<?php else : ?>
			<?php do_action( 'woocommerce_before_shop_loop' ); ?>
		<?php endif; ?>
	</div>

	<?php woocommerce_product_loop_start(); ?>

	<?php if ( wc_get_loop_prop( 'total' ) ) : ?>
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<?php do_action( 'woocommerce_shop_loop' ); ?>
			<?php wc_get_template_part( 'content', 'product' ); ?>
		<?php endwhile; ?>
	<?php endif; ?>

	<?php woocommerce_product_loop_end(); ?>

	<?php do_action( 'woocommerce_after_shop_loop' ); ?>
<?php else : ?>
	<?php do_action( 'woocommerce_no_products_found' ); ?>
<?php endif; ?>

<?php if ( $is_product_cat ) : ?>
	</div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_main_content' ); ?>
<?php do_action( 'woocommerce_sidebar' ); ?>

<?php get_footer( 'shop' ); ?>
