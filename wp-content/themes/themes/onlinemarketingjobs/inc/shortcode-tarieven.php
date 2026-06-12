<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [omj_tarieven]
 * Toont drie tarievenblokken naast elkaar.
 */
add_shortcode('omj_tarieven', 'omj_tarieven_shortcode');

function omj_tarieven_shortcode(): string {
    $check_icon = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 7 10 17l-6-6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';

    $cards = [
        [
            'modifier'    => ' sj-tarieven__card--place',
            'title'       => 'Vacature Plaatsing',
            'description' => 'Plaats een standaard vacature en bereik online marketing professionals in Nederland!',
            'price'       => '€275,-',
            'period'      => 'Per Vacature Plaatsing.',
            'button_text' => 'Vacature Plaatsen',
            'button_url'  => home_url('/vacature-plaatsen/'),
            'icon'        => 'M3 11.5 12 4l9 7.5v8a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 19.5Zm6 3h6M9 18h4.5',
            'features'    => [
                '60 dagen online.',
                '45 dagen verlengen voor €100,-',
                'Zichtbaar op de homepage.',
                'Link naar jouw eigen website.',
                'Eigen bedrijfspagina.',
                'Vermelding in job alerts.',
            ],
        ],
        [
            'modifier'    => ' sj-tarieven__card--featured',
            'title'       => 'Spotlight Vacature',
            'description' => 'Haal het maximale rendement uit je vacature en val op tussen de concurrentie!',
            'price'       => '€375,-',
            'period'      => 'Per Spotlight Vacature.',
            'button_text' => 'Vacature Plaatsen',
            'button_url'  => home_url('/vacature-plaatsen/'),
            'icon'        => 'M13 2 5.5 13h5L11 22l7.5-11h-5Z',
            'features'    => [
                '60 dagen online.',
                'Gratis 45 dagen verlengen.',
                'Zichtbaar op de homepage.',
                'Link naar jouw eigen website.',
                'Eigen bedrijfspagina.',
                'Vermelding in job alerts.',
                'Gedeeld op social media!',
                'Zichtbaar via Google for Jobs.',
                'Gedeeld in nieuwsbrief!',
                'Gratis artikel in onze blog!',
            ],
        ],
        [
            'modifier'    => ' sj-tarieven__card--membership',
            'title'       => 'Lidmaatschap',
            'description' => 'Een jaar lang onbeperkt vacatures plaatsen op Onlinemarketingjobs.nl!',
            'price'       => '€1.750,-',
            'period'      => 'Per 12 maanden.',
            'button_text' => 'Informatie aanvragen',
            'button_url'  => home_url('/contact/'),
            'icon'        => 'M12 21c0-4.2 1.1-7.2 3.2-9.2 1.7-1.7 4.2-2.9 7.3-3.8-1 3.5-2.7 6.2-5 8.1M12 21c0-3-1-5.5-2.8-7.3-1.6-1.6-3.8-2.7-6.7-3.4.7 2.7 1.9 4.9 3.5 6.5C7.7 18.7 9.6 20 12 21Z',
            'features'    => [
                'Onbeperkt vacatures plaatsen.',
                'Uitgebreide bedrijfspagina.',
                'Vermelding als Uitgelichte Werkgever!',
                'Employer brand artikelen.',
                'Werken-bij interview.',
                'Backlinks via onze kanalen.',
            ],
        ],
    ];

    ob_start();
    ?>
    <section class="sj-tarieven" aria-label="Tarieven">
        <div class="sj-tarieven__grid">
            <?php foreach ($cards as $card) : ?>
                <article class="sj-tarieven__card<?php echo esc_attr($card['modifier']); ?>">
                    <div class="sj-tarieven__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" focusable="false">
                            <path d="<?php echo esc_attr($card['icon']); ?>" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </div>

                    <h2 class="sj-tarieven__title"><?php echo esc_html($card['title']); ?></h2>
                    <p class="sj-tarieven__description"><?php echo esc_html($card['description']); ?></p>

                    <div class="sj-tarieven__price"><?php echo esc_html($card['price']); ?></div>
                    <p class="sj-tarieven__period"><?php echo esc_html($card['period']); ?></p>

                    <a class="sj-tarieven__button" href="<?php echo esc_url($card['button_url']); ?>">
                        <?php echo esc_html($card['button_text']); ?>
                    </a>

                    <ul class="sj-tarieven__list">
                        <?php foreach ($card['features'] as $feature) : ?>
                            <li>
                                <span class="sj-tarieven__check" aria-hidden="true"><?php echo $check_icon; ?></span>
                                <span><?php echo esc_html($feature); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php

    return ob_get_clean();
}
