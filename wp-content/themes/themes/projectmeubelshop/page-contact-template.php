<?php
/*
Template Name: Contact Template
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		?>
		<main id="primary" class="site-main pms-contact-page">
			<section class="pms-contact-hero">
				<div class="pms-contact-hero__inner">
					<div class="pms-contact-hero__eyebrow">Contact</div>
					<h1 class="pms-contact-hero__title"><?php the_title(); ?></h1>
					<div class="pms-contact-hero__intro">
						<?php
						if ( has_excerpt() ) {
							echo wp_kses_post( wpautop( get_the_excerpt() ) );
						} else {
							echo '<p>Neem contact met ons op voor vragen, productadvies of een afspraak in onze showroom.</p>';
						}
						?>
					</div>
				</div>
			</section>

			<section class="pms-contact-layout" aria-label="Contactgegevens">
				<div class="pms-contact-grid">
					<a class="pms-contact-card" href="tel:0852392040">
						<span class="pms-contact-card__icon" aria-hidden="true">
							<i data-lucide="phone"></i>
						</span>
						<span class="pms-contact-card__label">Telefoon</span>
						<span class="pms-contact-card__value">085 239 2040</span>
						<span class="pms-contact-card__meta">Bel ons voor direct contact en advies.</span>
					</a>

					<a class="pms-contact-card" href="mailto:support@projectmeubelshop.nl">
						<span class="pms-contact-card__icon" aria-hidden="true">
							<i data-lucide="mail"></i>
						</span>
						<span class="pms-contact-card__label">E-mail</span>
						<span class="pms-contact-card__value">support@projectmeubelshop.nl</span>
						<span class="pms-contact-card__meta">Stuur ons gerust je vraag of aanvraag.</span>
					</a>

					<div class="pms-contact-card pms-contact-card--static">
						<span class="pms-contact-card__icon" aria-hidden="true">
							<i data-lucide="map-pin"></i>
						</span>
						<span class="pms-contact-card__label">Adres / Loods / Showroom</span>
						<span class="pms-contact-card__value">Veenland 31</span>
						<span class="pms-contact-card__meta">2291 NS, Wateringen</span>
					</div>
				</div>

				<div class="pms-contact-panel">
					<div class="pms-contact-panel__header">
						<h2 class="pms-contact-panel__title">Langskomen of iets bespreken?</h2>
						<p class="pms-contact-panel__text">
							We helpen je graag met productvragen, projectinrichting en een bezoek aan onze showroom of loods.
						</p>
					</div>

					<div class="pms-contact-details">
						<div class="pms-contact-details__row">
							<span class="pms-contact-details__icon" aria-hidden="true">
								<i data-lucide="phone-call"></i>
							</span>
							<div>
								<div class="pms-contact-details__label">Telefonisch bereikbaar</div>
								<a href="tel:0852392040" class="pms-contact-details__link">085 239 2040</a>
							</div>
						</div>

						<div class="pms-contact-details__row">
							<span class="pms-contact-details__icon" aria-hidden="true">
								<i data-lucide="send"></i>
							</span>
							<div>
								<div class="pms-contact-details__label">E-mail ons</div>
								<a href="mailto:support@projectmeubelshop.nl" class="pms-contact-details__link">support@projectmeubelshop.nl</a>
							</div>
						</div>

						<div class="pms-contact-details__row">
							<span class="pms-contact-details__icon" aria-hidden="true">
								<i data-lucide="warehouse"></i>
							</span>
							<div>
								<div class="pms-contact-details__label">Bezoekadres</div>
								<div class="pms-contact-details__text">Veenland 31, 2291 NS, Wateringen</div>
							</div>
						</div>
					</div>

					<a
						class="pms-contact-panel__button"
						href="https://www.google.com/maps/search/?api=1&query=Veenland+31,+2291+NS,+Wateringen"
						target="_blank"
						rel="noopener noreferrer"
					>
						Route plannen
					</a>
				</div>
			</section>

			<?php if ( trim( get_the_content() ) !== '' ) : ?>
				<section class="pms-contact-content">
					<div class="pms-contact-content__inner">
						<?php the_content(); ?>
					</div>
				</section>
			<?php endif; ?>

			<section class="pms-contact-calendly" aria-label="Afspraak plannen">
				<div class="pms-contact-calendly__intro">
					<div class="pms-contact-hero__eyebrow">Afspraak plannen</div>
					<h2 class="pms-contact-calendly__title">Plan direct een moment in</h2>
					<p class="pms-contact-calendly__text">
						Kies hieronder eenvoudig een geschikt moment om kort met ons te bellen of een afspraak in te plannen.
					</p>
				</div>

				<div class="pms-contact-calendly__embed">
					<div class="calendly-inline-widget" data-url="https://calendly.com/projectmeubelshop/15minuten" style="min-width:320px;height:630px;"></div>
					<script type="text/javascript" src="https://assets.calendly.com/assets/external/widget.js" async></script>
				</div>
			</section>
		</main>

		<script>
		document.addEventListener('DOMContentLoaded', function () {
		  if (typeof lucide !== 'undefined') {
		    lucide.createIcons();
		  }
		});
		</script>
		<?php
	endwhile;
endif;

get_footer();
