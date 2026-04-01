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
	overflow: hidden;
}

.pms-col-right {
	background: #fff;
	border: 1px solid #DEDEDE;
	padding: 24px;
	border-radius: 5px;
}

.single-product .pms-col-right .product_title.entry-title {
	display: block !important;
	margin: 0 0 12px !important;
	color: #111 !important;
	font-family: Inter, sans-serif;
	font-size: 32px;
	font-weight: 700;
	line-height: 1.2;
}

/* Preview blok */
.pms-gallery-preview {
	position: relative;
	overflow: hidden;
	border-radius: 5px;
	background: #fff;
}

.pms-gallery-preview img {
	width: 100%;
	height: 500px;
	object-fit: contain;
	object-position: center;
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

/* Paginatitel boven de blokken */
h1.pms-page-title {
	max-width: 1200px;
	margin: 8px auto 24px;
	font-size: 28px;
	font-weight: 700;
	color: #111;
}

/* Beschrijvingen blok */
.pms-col-description {
	max-width: 1200px;
	margin: 40px auto 0;
	background: #fff;
	border: 1px solid #DEDEDE;
	padding: 24px;
	border-radius: 5px;
}

/* Gerelateerde producten */
.pms-related {
	background: #FBFAF8 !important;
	border: none !important;
	box-shadow: none !important;
	padding: 0 !important;
	margin-top: 40px;
}

.pms-related-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
	gap: 20px;
	margin-top: 20px;
}

.pms-related-item {
	display: flex !important;
	flex-direction: column !important;
	gap: 0 !important;
	text-decoration: none !important;
	color: #222 !important;
	border: 1px solid #DEDEDE;
	border-radius: 5px;
	overflow: hidden;
}

.pms-related-item:hover {
	border-color: #d2c19a;
}

.pms-related-item .pms-related-thumb {
	width: 100%;
	height: 180px;
	overflow: hidden;
	flex-shrink: 0;
}

.pms-related-item .pms-related-thumb img {
	width: 100% !important;
	height: 100% !important;
	object-fit: cover !important;
	display: block !important;
}

.pms-related-name {
	font-weight: 600;
	font-size: 15px;
	color: #222;
	padding: 10px 12px 4px;
	display: block;
}

.pms-related-cats {
	font-size: 13px;
	color: #888;
	padding: 0 12px 12px;
	display: block;
}

.pms-related-cats a {
	color: #888 !important;
	text-decoration: none !important;
}

/* Categorieën chips */
.pms-product-cats {
	display: flex;
	flex-wrap: wrap;
	gap: 6px;
	margin: 4px 0 16px;
	padding: 0;
	list-style: none;
}

.pms-product-cats a {
	display: inline-flex;
	align-items: center;
	padding: 5px 14px;
	background: #fff;
	border: 1px solid #C5B17D;
	border-radius: 999px;
	font-family: Inter, sans-serif;
	font-size: 13px;
	font-weight: 700;
	color: #333;
	text-decoration: none;
	transition: border-color 0.15s;
}

/* Contactgegevens */
.pms-contact-info {
	display: flex;
	flex-direction: column;
	gap: 8px;
	margin: 0 0 28px;
}

.pms-contact-item {
	display: flex;
	align-items: center;
	gap: 8px;
	font-size: 15px;
	color: #333 !important;
	text-decoration: none;
}

.pms-contact-item svg,
.pms-contact-item [data-lucide] {
	color: #C5B17D;
	stroke: #C5B17D;
	flex-shrink: 0;
}

.pms-contact-item:hover {
	color: #C5B17D;
}

/* Intro tekst */
.pms-intro-text {
	font-size: 15px;
	line-height: 1.7;
	color: #555;
	margin-bottom: 24px;
}

/* Offerte CTA blok */
.pms-quote-cta {
	margin-top: 16px;
	margin-bottom: 28px;
}

.pms-quote-cta__divider {
	border: none;
	border-top: 1px solid #EBEBEB;
	margin: 20px 0 16px;
}

.pms-quote-cta__label {
	font-family: Inter, sans-serif;
	font-size: 13px;
	font-weight: 600;
	color: var(--pms-muted);
	text-transform: uppercase;
	letter-spacing: 0.05em;
	margin: 0 0 10px;
}

/* Offerte formulier wrapper */
.pms-quote-fallback {
	display: flex !important;
	align-items: center !important;
	gap: 10px !important;
	width: 100% !important;
	box-sizing: border-box !important;
}

/* Stepper: − [n] + */
.pms-qty-stepper {
	display: flex !important;
	align-items: center !important;
	border: 1px solid var(--color-border) !important;
	border-radius: 8px !important;
	overflow: hidden !important;
	background: #fff !important;
	flex: 0 0 auto !important;
	height: 46px !important;
}

.pms-qty-btn {
	display: flex !important;
	align-items: center !important;
	justify-content: center !important;
	width: 36px !important;
	height: 100% !important;
	background: none !important;
	border: none !important;
	padding: 0 !important;
	color: #555 !important;
	cursor: pointer !important;
	transition: background 0.15s, color 0.15s !important;
	flex-shrink: 0 !important;
}

.pms-qty-btn:hover {
	background: var(--color-bg, #f5f5f5) !important;
	color: var(--color-primary) !important;
}

.pms-qty-btn svg {
	display: block !important;
	pointer-events: none !important;
}

.pms-qty-input {
	-webkit-appearance: none !important;
	appearance: none !important;
	width: 44px !important;
	height: 100% !important;
	border: none !important;
	border-left: 1px solid var(--color-border) !important;
	border-right: 1px solid var(--color-border) !important;
	padding: 0 !important;
	font-family: Inter, sans-serif !important;
	font-size: 15px !important;
	font-weight: 600 !important;
	color: #333 !important;
	background: #fff !important;
	text-align: center !important;
	box-shadow: none !important;
	outline: none !important;
}

.pms-qty-input::-webkit-inner-spin-button,
.pms-qty-input::-webkit-outer-spin-button {
	-webkit-appearance: none !important;
}

.pms-quote-fallback-btn {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	flex: 1 1 0 !important;
	min-width: 0 !important;
	height: 46px !important;
	padding: 0 16px !important;
	background: var(--color-primary) !important;
	color: #fff !important;
	border: none !important;
	border-radius: 8px !important;
	font-family: Inter, sans-serif !important;
	font-size: 14px !important;
	font-weight: 700 !important;
	text-decoration: none !important;
	cursor: pointer !important;
	white-space: nowrap !important;
	transition: background-color 0.15s !important;
	box-sizing: border-box !important;
}

.pms-quote-fallback-btn:hover {
	background: #3d2d20 !important;
	color: #fff !important;
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
	top: 20px;
	right: 20px;
	display: flex;
	align-items: center;
	justify-content: center;
	width: 52px;
	height: 52px;
	background: rgba(255,255,255,0.16);
	border: 1px solid rgba(255,255,255,0.22);
	border-radius: 50%;
	color: #fff;
	font-size: 30px;
	font-weight: 400;
	cursor: pointer;
	line-height: 1;
	z-index: 10;
	transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
}

.pms-lightbox-close:hover {
	background: rgba(255,255,255,0.28);
	border-color: rgba(255,255,255,0.4);
	transform: scale(1.04);
}

@media (max-width: 980px) {
	.pms-product-wrapper {
		grid-template-columns: 1fr;
		gap: 20px;
		margin: 20px auto 0;
	}

	.pms-col-right,
	.pms-col-description {
		padding: 20px;
	}

	.single-product .pms-col-right .product_title.entry-title {
		font-size: 28px;
		margin-bottom: 14px !important;
	}

	.pms-gallery-preview {
		padding: 12px;
	}

	.pms-gallery-preview img {
		height: 380px;
		max-width: 100%;
	}

	.pms-quote-fallback {
		flex-wrap: wrap !important;
	}

	.pms-qty-stepper,
	.pms-quote-fallback-btn {
		width: 100% !important;
		flex: 1 1 100% !important;
	}

	.pms-related-grid {
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: 14px;
	}

	.pms-lightbox-strip {
		padding: 80px 24px 24px;
		gap: 16px;
	}

	.pms-lightbox-strip img {
		max-width: calc(100vw - 48px);
		max-height: calc(100vh - 140px);
	}

	.pms-lightbox-arrow {
		width: 44px;
		height: 44px;
		font-size: 24px;
	}
}

@media (max-width: 640px) {
	.pms-product-wrapper {
		gap: 16px;
		margin-top: 16px;
	}

	.pms-col-right,
	.pms-col-description {
		padding: 16px;
	}

	.single-product .pms-col-right .product_title.entry-title {
		font-size: 24px;
		line-height: 1.25;
	}

	.pms-col-left,
	.pms-col-right,
	.pms-col-description {
		border-radius: 5px;
	}

	.pms-gallery-preview {
		padding: 10px;
	}

	.pms-gallery-preview img {
		height: 280px;
	}

	.pms-preview-arrow {
		width: 38px;
		height: 38px;
		font-size: 18px;
	}

	.pms-preview-arrow--prev {
		left: 8px;
	}

	.pms-preview-arrow--next {
		right: 8px;
	}

	.pms-product-cats {
		margin-bottom: 14px;
		gap: 8px;
	}

	.pms-product-cats a {
		padding: 6px 10px;
		font-size: 12px;
	}

	.pms-contact-info {
		gap: 10px;
		margin-bottom: 22px;
	}

	.pms-contact-item,
	.pms-intro-text {
		font-size: 14px;
	}

	.pms-contact-item {
		line-height: 1.5;
	}

	.pms-quote-cta {
		margin-top: 12px;
		margin-bottom: 20px;
	}

	.pms-quote-cta__divider {
		margin: 16px 0 14px;
	}

	.pms-quote-cta__label {
		font-size: 12px;
	}

	.pms-qty-stepper {
		height: 44px !important;
	}

	.pms-qty-btn {
		width: 40px !important;
	}

	.pms-qty-input {
		width: 56px !important;
		font-size: 14px !important;
	}

	.pms-quote-fallback-btn {
		min-height: 46px !important;
		padding: 0 14px !important;
		font-size: 13px !important;
	}

	.pms-col-description h2 {
		font-size: 22px;
		line-height: 1.25;
		margin: 0 0 14px;
	}

	.pms-related-grid {
		grid-template-columns: 1fr;
	}

	.pms-related-item .pms-related-thumb {
		height: 220px;
	}

	.pms-lightbox-strip {
		padding: 88px 16px 16px;
	}

	.pms-lightbox-arrow {
		top: auto;
		bottom: 16px;
		transform: none;
	}

	.pms-lightbox-arrow--prev {
		left: 16px;
	}

	.pms-lightbox-arrow--next {
		right: 16px;
	}

	.pms-lightbox-close {
		top: 16px;
		right: 16px;
		width: 44px;
		height: 44px;
		font-size: 26px;
	}
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

			// Titel
			the_title( '<h1 class="product_title entry-title">', '</h1>' );

			// Categorieën als chips
			$cat_terms = get_the_terms( $product_id, 'product_cat' );
			if ( ! empty( $cat_terms ) && ! is_wp_error( $cat_terms ) ) {
				echo '<div class="pms-product-cats">';
				foreach ( $cat_terms as $term ) {
					echo '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>';
				}
				echo '</div>';
			}

			$in_quote   = function_exists( 'pms_quote_has_product' ) && pms_quote_has_product( $product->get_id() );
			$quote_url  = function_exists( 'pms_quote_get_page_url' ) ? pms_quote_get_page_url() : home_url( '/offerte-samenstellen/' );
			$just_added = isset( $_GET['pms_added'] ) && (int) $_GET['pms_added'] === $product->get_id();
			?>

			<!-- Offerte CTA -->
			<div class="pms-quote-cta">
				<hr class="pms-quote-cta__divider">
				<p class="pms-quote-cta__label">Interesse in dit product?</p>

				<?php if ( $in_quote || $just_added ) : ?>
					<a href="<?php echo esc_url( $quote_url ); ?>" class="pms-quote-fallback-btn">
						Offerte bekijken
					</a>
				<?php else : ?>
					<form method="post" action="" class="pms-quote-fallback" id="pms-quote-form">
						<?php wp_nonce_field( 'pms_add_to_quote', 'pms_nonce' ); ?>
						<input type="hidden" name="pms_action" value="add_to_quote">
						<input type="hidden" name="product_id" value="<?php echo esc_attr( $product->get_id() ); ?>">
						<div class="pms-qty-stepper">
							<button type="button" class="pms-qty-btn pms-qty-btn--minus" aria-label="Minder">
								<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/></svg>
							</button>
							<input type="number" class="pms-qty-input" name="quantity" value="1" min="1" aria-label="Aantal">
							<button type="button" class="pms-qty-btn pms-qty-btn--plus" aria-label="Meer">
								<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
							</button>
						</div>
						<button type="submit" class="pms-quote-fallback-btn">
							Toevoegen aan offerte
						</button>
					</form>
				<?php endif; ?>
			</div>

			<!-- Contactgegevens -->
			<div class="pms-contact-info">
				<a href="mailto:support@projectmeubelshop.nl" class="pms-contact-item">
					<i data-lucide="mail" width="16" height="16"></i>
					support@projectmeubelshop.nl
				</a>
				<a href="tel:0852392040" class="pms-contact-item">
					<i data-lucide="phone" width="16" height="16"></i>
					085 239 20 40
				</a>
			</div>

			<!-- Korte beschrijving -->
			<?php
			$short = $product->get_short_description();
			if ( ! empty( $short ) ) {
				echo '<div class="pms-intro-text">' . wpautop( $short ) . '</div>';
			}
			?>
		</div>

	</div>

	<!-- ONDER: Beschrijvingen -->
	<?php
	$desc = $product->get_description();
	if ( ! empty( $desc ) ) : ?>
		<div class="pms-col-description">
			<h2>Product beschrijving</h2>
			<?php echo apply_filters( 'the_content', $desc ); ?>
		</div>
	<?php endif; ?>

	<!-- ONDER: Gerelateerde producten -->
	<?php
	$related_ids = wc_get_related_products( $product->get_id(), 6 );
	if ( ! empty( $related_ids ) ) : ?>
		<div class="pms-col-description pms-related">
			<h2>Gerelateerde producten</h2>
			<div class="pms-related-grid">
				<?php foreach ( $related_ids as $related_id ) :
					$related = wc_get_product( $related_id );
					if ( ! $related ) continue;
				?>
					<a href="<?php echo esc_url( get_permalink( $related_id ) ); ?>" class="pms-related-item">
						<div class="pms-related-thumb"><?php echo wp_get_attachment_image( $related->get_image_id(), 'medium' ); ?></div>
						<span class="pms-related-name"><?php echo esc_html( $related->get_name() ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

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

if (typeof lucide !== 'undefined') lucide.createIcons();

// --- Qty steppers ---
document.querySelectorAll('.pms-qty-stepper').forEach(function (stepper) {
	var input  = stepper.querySelector('.pms-qty-input');
	var minus  = stepper.querySelector('.pms-qty-btn--minus');
	var plus   = stepper.querySelector('.pms-qty-btn--plus');
	if (!input) return;
	minus.addEventListener('click', function () {
		var v = parseInt(input.value, 10) || 1;
		if (v > 1) input.value = v - 1;
	});
	plus.addEventListener('click', function () {
		var v = parseInt(input.value, 10) || 1;
		input.value = v + 1;
	});
});
</script>

<?php do_action( 'woocommerce_after_single_product' ); ?>
