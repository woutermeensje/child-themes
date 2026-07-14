<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [sc_pricing]
 * Displays three pricing blocks side by side.
 */
add_shortcode('sc_pricing', 'sc_pricing_shortcode');

function sc_pricing_shortcode(): string {
    $check_icon = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 7 10 17l-6-6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';

    $cards = [
        [
            'modifier'    => ' sj-pricing__card--place',
            'title'       => 'Job Posting',
            'description' => 'Post a standard job listing to find your next sustainable colleague!',
            'price'       => '€275,-',
            'period'      => 'Per job posting.',
            'button_text' => 'Post a Job',
            'button_url'  => home_url('/post-a-job/'),
            'icon'        => 'M3 11.5 12 4l9 7.5v8a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 19.5Zm6 3h6M9 18h4.5',
            'features'    => [
                '60 days online.',
                'Extend 45 days for €100,-',
                'Visible on the homepage.',
                'Link to your own website.',
                'Own company page.',
                'Featured in job alerts (750+).',
            ],
        ],
        [
            'modifier'    => ' sj-pricing__card--featured',
            'title'       => 'Spotlight Job',
            'description' => 'Get maximum visibility and the best results from your job posting!',
            'price'       => '€375,-',
            'period'      => 'Per spotlight job posting.',
            'button_text' => 'Post a Job',
            'button_url'  => home_url('/post-a-job/'),
            'icon'        => 'M13 2 5.5 13h5L11 22l7.5-11h-5Z',
            'features'    => [
                '60 days online.',
                'Free 45-day extension.',
                'Visible on the homepage.',
                'Link to your own website.',
                'Own company page.',
                'Featured in job alerts (750+).',
                'Shared on social media!',
                'Visible via Google for Jobs.',
                'Featured in newsletter!',
                'Free article on our blog!',
            ],
        ],
        [
            'modifier'    => ' sj-pricing__card--membership',
            'title'       => 'Membership',
            'description' => 'A full year of all Sustainablejobs.com benefits!',
            'price'       => '€1,750,-',
            'period'      => 'Per 12 months.',
            'button_text' => 'Request information',
            'button_url'  => home_url('/contact/'),
            'icon'        => 'M12 21c0-4.2 1.1-7.2 3.2-9.2 1.7-1.7 4.2-2.9 7.3-3.8-1 3.5-2.7 6.2-5 8.1M12 21c0-3-1-5.5-2.8-7.3-1.6-1.6-3.8-2.7-6.7-3.4.7 2.7 1.9 4.9 3.5 6.5C7.7 18.7 9.6 20 12 21Z',
            'features'    => [
                'Unlimited job postings on Sustainablejobs.com.',
                'Extended company page.',
                'Listed as a Featured Employer!',
                'Employer brand articles.',
                'Working-at interview.',
                'Backlinks across Sustainablejobs.com channels.',
            ],
        ],
    ];

    ob_start();
    ?>
    <section class="sj-pricing" aria-label="Pricing">
        <div class="sj-pricing__grid">
            <?php foreach ($cards as $card) : ?>
                <article class="sj-pricing__card<?php echo esc_attr($card['modifier']); ?>">
                    <div class="sj-pricing__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" focusable="false">
                            <path d="<?php echo esc_attr($card['icon']); ?>" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </div>

                    <h2 class="sj-pricing__title"><?php echo esc_html($card['title']); ?></h2>
                    <p class="sj-pricing__description"><?php echo esc_html($card['description']); ?></p>

                    <div class="sj-pricing__price"><?php echo esc_html($card['price']); ?></div>
                    <p class="sj-pricing__period"><?php echo esc_html($card['period']); ?></p>

                    <a class="sj-pricing__button" href="<?php echo esc_url($card['button_url']); ?>">
                        <?php echo esc_html($card['button_text']); ?>
                    </a>

                    <ul class="sj-pricing__list">
                        <?php foreach ($card['features'] as $feature) : ?>
                            <li>
                                <span class="sj-pricing__check" aria-hidden="true"><?php echo $check_icon; ?></span>
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
