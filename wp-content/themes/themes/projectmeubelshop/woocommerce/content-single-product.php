<?php
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form();
	return;
}

global $product;

// Alle afbeeldingen verzamelen (hoofdafbeelding + galerij)
$image_ids = array_filter( array_merge(
	[ $product->get_image_id() ],
	$product->get_gallery_image_ids()
) );
?>

<style>
.pms-product-wrapper {
	max-width: 1200px;
	margin: 40px auto;
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 40px;
}

.pms-col-left {
	border: 1px solid #DEDEDE;
	border-radius: 5px;
	box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
	overflow: hidden;
}

.pms-col-right {
	border: 1px solid #DEDEDE;
	padding: 24px;
	border-radius: 5px;
	box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
}

/* Preview blok */
.pms-gallery-preview {
	position: relative;
	overflow: hidden;
	border-radius: 5px;
}

.pms-gallery-preview img {
	width: 100%;
	height: 500px;
	object-fit: cover;
	display: none;
}

.pms-gallery-preview img.is-active {
	display: block;
}

.pms-gallery-preview-hint {
	position: absolute;
	bottom: 16px;
	right: 16px;
	background: rgba(0,0,0,0.55);
	color: #fff;
	font-size: 13px;
	padding: 6px 12px;
	border-radius: 4px;
	pointer-events: none;
}

/* Preview pijlen */
.pms-preview-arrow {
	position: absolute;
	top: 50%;
	transform: translateY(-50%);
	background: rgba(0,0,0,0.45);
	border: none;
	color: #fff;
	font-size: 22px;
	width: 44px;
	height: 44px;
	border-radius: 50%;
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	z-index: 5;
	transition: background 0.2s;
}

.pms-preview-arrow:hover {
	background: rgba(0,0,0,0.7);
}

.pms-preview-arrow--prev { left: 12px; }
.pms-preview-arrow--next { right: 12px; }

/* Teller */
.pms-preview-counter {
	position: absolute;
	bottom: 12px;
	left: 50%;
	transform: translateX(-50%);
	display: flex;
	gap: 6px;
}

.pms-preview-counter span {
	width: 8px;
	height: 8px;
	border-radius: 50%;
	background: rgba(255,255,255,0.5);
	display: block;
}

.pms-preview-counter span.is-active {
	background: #fff;
}

/* Lightbox overlay */
.pms-lightbox {
	display: none;
	position: fixed;
	inset: 0;
	background: rgba(0,0,0,0.9);
	z-index: 99999;
	align-items: center;
	justify-content: center;
}

.pms-lightbox.is-open {
	display: flex;
}

.pms-lightbox-inner {
	position: relative;
	width: 100%;
	height: 100%;
	display: flex;
	align-items: center;
}

/* Horizontaal scroll strip */
.pms-lightbox-strip {
	display: flex;
	align-items: center;
	gap: 24px;
	overflow-x: auto;
	scroll-snap-type: x mandatory;
	scrollbar-width: none;
	padding: 40px 80px;
	width: 100%;
	height: 100%;
	box-sizing: border-box;
}

.pms-lightbox-strip::-webkit-scrollbar {
	display: none;
}

.pms-lightbox-strip img {
	flex: 0 0 auto;
	max-height: calc(100vh - 120px);
	max-width: 85vw;
	object-fit: contain;
	scroll-snap-align: center;
	border-radius: 4px;
}

/* Pijlen */
.pms-lightbox-arrow {
	position: absolute;
	top: 50%;
	transform: translateY(-50%);
	background: rgba(255,255,255,0.15);
	border: none;
	color: #fff;
	font-size: 28px;
	width: 52px;
	height: 52px;
	border-radius: 50%;
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	z-index: 10;
	transition: background 0.2s;
}

.pms-lightbox-arrow:hover {
	background: rgba(255,255,255,0.3);
}

.pms-lightbox-arrow--prev { left: 16px; }
.pms-lightbox-arrow--next { right: 16px; }

/* Sluiten */
.pms-lightbox-close {
	position: absolute;
	top: 16px;
	right: 20px;
	background: none;
	border: none;
	color: #fff;
	font-size: 36px;
	cursor: pointer;
	line-height: 1;
	z-index: 10;
}
</style>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>

	<div class="pms-product-wrapper">

		<!-- LINKS: Afbeeldingen -->
		<div class="pms-col-left">
			<?php if ( ! empty( $image_ids ) ) : ?>

				<?php $first_id = reset( $image_ids ); ?>

				<!-- Preview -->
				<div class="pms-gallery-preview" id="pms-open-gallery">
					<?php $i = 0; foreach ( $image_ids as $id ) : ?>
						<?php echo wp_get_attachment_image( $id, 'large', false, [ 'class' => $i === 0 ? 'is-active' : '' ] ); ?>
					<?php $i++; endforeach; ?>

					<?php if ( count( $image_ids ) > 1 ) : ?>
						<button class="pms-preview-arrow pms-preview-arrow--prev" id="pms-preview-prev" aria-label="Vorige">&#8592;</button>
						<button class="pms-preview-arrow pms-preview-arrow--next" id="pms-preview-next" aria-label="Volgende">&#8594;</button>
						<div class="pms-preview-counter" id="pms-preview-counter">
							<?php for ( $j = 0; $j < count( $image_ids ); $j++ ) : ?>
								<span class="<?php echo $j === 0 ? 'is-active' : ''; ?>"></span>
							<?php endfor; ?>
						</div>
					<?php endif; ?>
				</div>

				<!-- Lightbox -->
				<div class="pms-lightbox" id="pms-lightbox" role="dialog" aria-modal="true">
					<div class="pms-lightbox-inner">
						<button class="pms-lightbox-close" id="pms-lightbox-close" aria-label="Sluiten">&times;</button>
						<button class="pms-lightbox-arrow pms-lightbox-arrow--prev" id="pms-arrow-prev" aria-label="Vorige">&#8592;</button>

						<div class="pms-lightbox-strip" id="pms-lightbox-strip">
							<?php foreach ( $image_ids as $id ) : ?>
								<?php echo wp_get_attachment_image( $id, 'full' ); ?>
							<?php endforeach; ?>
						</div>

						<button class="pms-lightbox-arrow pms-lightbox-arrow--next" id="pms-arrow-next" aria-label="Volgende">&#8594;</button>
					</div>
				</div>

			<?php endif; ?>
		</div>

		<!-- RECHTS: Productinfo -->
		<div class="pms-col-right">
			<?php
			$product_id = $product->get_id();
			$quote_url  = function_exists( 'pms_quote_get_page_url' ) ? pms_quote_get_page_url() : home_url( '/offerte-samenstellen/' );
			$in_quote   = function_exists( 'pms_quote_has_product' ) ? pms_quote_has_product( $product_id ) : false;

			// 1. Titel
			the_title( '<h1 class="product_title entry-title">', '</h1>' );

			// 2. Offerte-knop
			if ( $in_quote ) : ?>
				<a class="button pms-quote-view-button" href="<?php echo esc_url( $quote_url ); ?>">
					<?php esc_html_e( 'Offerte bekijken', 'projectmeubelshop-child' ); ?>
				</a>
			<?php else : ?>
				<form method="post" class="pms-quote-form">
					<?php wp_nonce_field( 'pms_add_to_quote', 'pms_quote_nonce' ); ?>
					<input type="hidden" name="pms_product_id" value="<?php echo esc_attr( $product_id ); ?>">
					<input type="hidden" name="quantity" value="1">
					<button type="submit" name="pms_add_to_quote" value="1" class="button pms-quote-add-button" style="border-radius: 999px; color: #fff; background-color: #C5B17D; border: none; font-weight: 700;">
						<?php esc_html_e( 'Voeg toe aan offerte', 'projectmeubelshop-child' ); ?>
					</button>
				</form>
			<?php endif;

			// 3. Korte beschrijving
			$short = $product->get_short_description();
			if ( ! empty( $short ) ) {
				echo wpautop( $short );
			}

			// 4. Uitgebreide beschrijving
			$desc = $product->get_description();
			if ( ! empty( $desc ) ) {
				echo apply_filters( 'the_content', $desc );
			}
			?>
		</div>

	</div>

</div>

<script>
(function () {
	// --- Preview slider ---
	const previewImgs = document.querySelectorAll('#pms-open-gallery img');
	const dots        = document.querySelectorAll('#pms-preview-counter span');
	const prevBtn     = document.getElementById('pms-preview-prev');
	const nextBtn     = document.getElementById('pms-preview-next');
	let current = 0;

	function showSlide(index) {
		current = (index + previewImgs.length) % previewImgs.length;
		previewImgs.forEach((img, i) => img.classList.toggle('is-active', i === current));
		dots.forEach((dot, i) => dot.classList.toggle('is-active', i === current));
	}

	if (prevBtn) prevBtn.addEventListener('click', (e) => { e.stopPropagation(); showSlide(current - 1); });
	if (nextBtn) nextBtn.addEventListener('click', (e) => { e.stopPropagation(); showSlide(current + 1); });

	// --- Lightbox (opent bij klik op afbeelding) ---
	const lightbox = document.getElementById('pms-lightbox');
	const closeBtn = document.getElementById('pms-lightbox-close');
	const strip    = document.getElementById('pms-lightbox-strip');
	const lbPrev   = document.getElementById('pms-arrow-prev');
	const lbNext   = document.getElementById('pms-arrow-next');

	if (!lightbox) return;

	const lbImgs = strip.querySelectorAll('img');
	let lbCurrent = 0;

	function lbScrollTo(index) {
		lbCurrent = Math.max(0, Math.min(lbImgs.length - 1, index));
		lbImgs[lbCurrent].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
	}

	previewImgs.forEach((img) => {
		img.style.cursor = 'zoom-in';
		img.addEventListener('click', () => {
			lbScrollTo(current);
			lightbox.classList.add('is-open');
			document.body.style.overflow = 'hidden';
		});
	});

	closeBtn.addEventListener('click', closeLb);
	lightbox.addEventListener('click', (e) => { if (e.target === lightbox) closeLb(); });
	if (lbPrev) lbPrev.addEventListener('click', () => lbScrollTo(lbCurrent - 1));
	if (lbNext) lbNext.addEventListener('click', () => lbScrollTo(lbCurrent + 1));

	document.addEventListener('keydown', (e) => {
		if (!lightbox.classList.contains('is-open')) return;
		if (e.key === 'Escape')      closeLb();
		if (e.key === 'ArrowLeft')   lbScrollTo(lbCurrent - 1);
		if (e.key === 'ArrowRight')  lbScrollTo(lbCurrent + 1);
	});

	function closeLb() {
		lightbox.classList.remove('is-open');
		document.body.style.overflow = '';
	}
})();
</script>

<?php do_action( 'woocommerce_after_single_product' ); ?>
