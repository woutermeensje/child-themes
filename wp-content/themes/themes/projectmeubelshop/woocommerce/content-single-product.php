<?php
/**
 * Projectmeubelshop single product content override
 */

defined( 'ABSPATH' ) || exit;

global $product;

do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form();
	return;
}
?>

<style>
/* =========================
   MAIN GRID LAYOUT
   ========================= */
.single-product .pms-single-main{
	display:grid;
	grid-template-columns:minmax(0,1.8fr) minmax(360px,640px);
	gap:60px;
	align-items:start;
	margin:40px 0;
}

/* Zorg dat kolommen echt mogen uitrekken */
.single-product .pms-single-media,
.single-product .pms-single-summary{
	min-width:0;
}

/* Mobile */
@media (max-width:980px){
	.single-product .pms-single-main{
		grid-template-columns:1fr;
		gap:20px;
	}
}

/* =========================
   FIX: THEME CENTERING / MAX WIDTH RECHTS
   (dit is wat je screenshot laat zien)
   ========================= */
.single-product .pms-single-summary{
	text-align:left !important;
	justify-items:start;
	align-content:start;
	max-width:none !important;
	margin:0 !important;
	display:grid;
	gap:24px;
}

.single-product .pms-single-summary .product_title{
	text-align:left !important;
	margin:0 !important;
	font-size:clamp(34px,4vw,54px);
	line-height:1.1;
}

/* =========================
   GALLERY: stabiele layout via eigen wrapper grid
   ========================= */
.single-product .pms-gallery-grid{
	display:grid;
	grid-template-columns:120px minmax(0,1fr);
	gap:22px;
	align-items:start;
	width:100%;
}

/* Woo figure mag niets “duwen” */
.single-product .pms-gallery-grid .woocommerce-product-gallery{
	width:100% !important;
	max-width:none !important;
	float:none !important;
	margin:0 !important;
}

/* Flexslider wrappers resetten (floats slopen layout) */
.single-product .pms-gallery-grid .flex-viewport,
.single-product .pms-gallery-grid .woocommerce-product-gallery__wrapper,
.single-product .pms-gallery-grid .woocommerce-product-gallery__image{
	float:none !important;
	max-width:none !important;
}

/* Thumbnails links */
.single-product .pms-gallery-grid .flex-control-thumbs{
	grid-column:1;
	list-style:none !important;
	margin:0 !important;
	padding:0 !important;
	width:120px !important;
	display:flex !important;
	flex-direction:column !important;
	gap:14px;
}

.single-product .pms-gallery-grid .flex-control-thumbs li{
	list-style:none !important;
	margin:0 !important;
	padding:0 !important;
	width:100% !important;
}

.single-product .pms-gallery-grid .flex-control-thumbs img{
	width:100% !important;
	height:auto !important;
	display:block !important;
	border:1px solid rgba(0,0,0,.12);
	background:#fff;
	opacity:.75;
}

.single-product .pms-gallery-grid .flex-control-thumbs img.flex-active{
	opacity:1;
	border-color:rgba(0,0,0,.25);
}

/* Main image rechts: zet viewport/wrapper in kolom 2 */
.single-product .pms-gallery-grid .flex-viewport,
.single-product .pms-gallery-grid .woocommerce-product-gallery__wrapper{
	grid-column:2;
	width:100% !important;
	margin:0 !important;
}

/* Main image frame */
.single-product .pms-gallery-grid .woocommerce-product-gallery__image{
	margin:0 !important;
	background:#f5f5f5;
	border:1px solid rgba(0,0,0,.12);
	padding:18px;
}

/* Main image schaal */
.single-product .pms-gallery-grid .woocommerce-product-gallery__image img,
.single-product .pms-gallery-grid .woocommerce-product-gallery img{
	width:100% !important;
	height:auto !important;
	max-width:100% !important;
	display:block !important;
	object-fit:contain;
}

/* Mobiel thumbs smaller */
@media (max-width:980px){
	.single-product .pms-gallery-grid{
		grid-template-columns:90px minmax(0,1fr);
		gap:14px;
	}
	.single-product .pms-gallery-grid .flex-control-thumbs{
		width:90px !important;
	}
}

/* =========================
   TEXT BLOCKS
   ========================= */
.single-product .pms-short-description,
.single-product .pms-description{
	font-size:18px;
	line-height:1.8;
	text-align:left !important;
}

/* CTA button */
.single-product .single_add_to_cart_button{
	padding:18px 28px !important;
	border-radius:999px !important;
	font-size:18px !important;
}
</style>


	<div class="pms-single-main">

		<!-- LEFT: GALLERY -->
		<div class="pms-single-media">
			<div class="pms-gallery-grid">
				<?php do_action( 'woocommerce_before_single_product_summary' ); ?>
			</div>
		</div>

		<!-- RIGHT: CONTENT -->
		<div class="summary entry-summary pms-single-summary">
			<?php
			woocommerce_template_single_title();
			woocommerce_template_single_price();

			// Korte beschrijving (boven)
			$short = $product->get_short_description();
			if ( ! empty( $short ) ) {
				echo '<div class="pms-short-description">' . wpautop( $short ) . '</div>';
			}

			woocommerce_template_single_add_to_cart();

			// Lange beschrijving (beneden)
			$desc = $product->get_description();
			if ( ! empty( $desc ) ) {
				echo '<div class="pms-description">' . apply_filters( 'the_content', $desc ) . '</div>';
			}
			?>
		</div>

	</div>

	<?php
	// Reviews/tabs/related bewust verwijderd:
	// do_action( 'woocommerce_after_single_product_summary' );
	?>

</div>

