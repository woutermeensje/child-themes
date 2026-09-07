<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [fn_recente_vacatures]  (alias: [sj_recente_vacatures])
 * Compacte lijst met de laatste vacatures, bedoeld voor een smal zijblok.
 * Qua filters een variant van de standaard [jobs] shortcode van WP Job Manager,
 * maar met een smalle kaart-opbouw i.p.v. de grote vacaturekaart.
 *
 * Attributen (zelfde taxonomie-filters als [jobs]):
 * - limit: aantal vacatures (standaard 5)
 * - job_sector / job_company / job_tag / job_listing_type / organisatie_type: comma-separated slugs
 * - title: kop boven de lijst (standaard "Recente vacatures")
 * - button_text / button_url: link onderaan naar het volledige overzicht
 * - show_button: 1 of 0 (standaard 1)
 *
 * Overgenomen van het sustainablejobs-nl child theme ([sj_recente_vacatures]).
 */
add_shortcode('fn_recente_vacatures', 'fn_recente_vacatures_shortcode');
add_shortcode('sj_recente_vacatures', 'fn_recente_vacatures_shortcode');

function fn_recente_vacatures_bool($value): bool {
    return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'ja', 'on'], true);
}

function fn_recente_vacatures_shortcode($atts): string {
    if (!function_exists('get_job_listings')) {
        return '';
    }

    $atts = shortcode_atts([
        'limit'             => 5,
        'job_sector'        => '',
        'job_company'       => '',
        'job_tag'           => '',
        'job_listing_type'  => '',
        'organisatie_type'  => '',
        'title'             => 'Recente vacatures',
        'button_text'       => 'Bekijk alle vacatures',
        'button_url'        => home_url('/vacatures/'),
        'show_button'       => '1',
    ], $atts, 'fn_recente_vacatures');

    $taxonomy_filters = [
        'job_sector'       => 'job_sector',
        'job_company'      => 'job_company',
        'job_tag'          => 'job_tag',
        'job_listing_type' => 'job_listing_type',
        'organisatie_type' => 'organisatie_type',
    ];

    $tax_query = [];
    foreach ($taxonomy_filters as $attr => $taxonomy) {
        if (empty($atts[$attr]) || !taxonomy_exists($taxonomy)) {
            continue;
        }
        $tax_query[] = [
            'taxonomy' => $taxonomy,
            'field'    => 'slug',
            'terms'    => array_map('sanitize_title', explode(',', (string) $atts[$attr])),
            'operator' => 'IN',
        ];
    }

    $query_args = [
        'posts_per_page' => max(1, (int) $atts['limit']),
        'orderby'        => 'date',
        'order'          => 'DESC',
        'featured'       => null,
    ];
    if ($tax_query) {
        $query_args['tax_query'] = $tax_query;
    }

    $jobs = get_job_listings($query_args);

    if (!($jobs instanceof WP_Query) || !$jobs->have_posts()) {
        return '';
    }

    ob_start();
    ?>
    <div class="fn-rv">
        <?php if ($atts['title']): ?>
            <h3 class="fn-rv__title"><?php echo esc_html($atts['title']); ?></h3>
        <?php endif; ?>

        <div class="fn-rv__list">
            <?php while ($jobs->have_posts()): $jobs->the_post();
                $post_id      = get_the_ID();
                $company_name = function_exists('get_the_company_name') ? get_the_company_name($post_id) : '';
                $location     = function_exists('get_the_job_location') ? get_the_job_location($post_id) : '';
                $logo_html    = function_exists('fondsen_get_job_listing_company_logo_html')
                    ? fondsen_get_job_listing_company_logo_html($post_id, 'thumbnail')
                    : '';
                $initial      = $company_name ? mb_substr($company_name, 0, 1) : mb_substr(get_the_title(), 0, 1);
                $posted_ago   = human_time_diff(get_post_time('U', true, $post_id), current_time('timestamp', true));

                $meta_parts = array_filter([$company_name, $location]);
            ?>
                <a class="fn-rv__item" href="<?php the_permalink(); ?>">
                    <span class="fn-rv__badge" aria-hidden="true">
                        <?php if ($logo_html): ?>
                            <?php echo $logo_html; ?>
                        <?php else: ?>
                            <?php echo esc_html(strtoupper($initial)); ?>
                        <?php endif; ?>
                    </span>
                    <span class="fn-rv__content">
                        <span class="fn-rv__job-title"><?php the_title(); ?></span>
                        <?php if ($meta_parts): ?>
                            <span class="fn-rv__meta"><?php echo esc_html(implode(' · ', $meta_parts)); ?></span>
                        <?php endif; ?>
                        <span class="fn-rv__posted"><?php echo esc_html($posted_ago); ?> geleden</span>
                    </span>
                    <span class="fn-rv__arrow" aria-hidden="true"></span>
                </a>
            <?php endwhile; ?>
        </div>

        <?php if (fn_recente_vacatures_bool($atts['show_button']) && $atts['button_url']): ?>
            <a class="fn-rv__button" href="<?php echo esc_url($atts['button_url']); ?>"><?php echo esc_html($atts['button_text']); ?></a>
        <?php endif; ?>
    </div>

    <?php wp_reset_postdata(); ?>
    <?php fn_recente_vacatures_print_assets(); ?>
    <?php
    return trim(ob_get_clean());
}

function fn_recente_vacatures_print_assets(): void {
    static $printed = false;
    if ($printed) {
        return;
    }
    $printed = true;
    ?>
    <style>
    .fn-rv,
    .fn-rv *,
    .fn-rv *::before,
    .fn-rv *::after { box-sizing: border-box; }

    .fn-rv {
        width: 100%;
        font-family: 'Poppins', sans-serif;
    }

    .fn-rv__title {
        margin: 0 0 16px !important;
        font-family: 'Work Sans', sans-serif !important;
        font-size: 20px !important;
        font-weight: 700 !important;
        line-height: 1.2 !important;
        color: #333333 !important;
    }

    .fn-rv__list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .fn-rv__item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px;
        background: #ffffff;
        border: 1px solid var(--color-border, #E0E0E0);
        border-radius: 6px;
        color: #333333 !important;
        text-decoration: none !important;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .fn-rv__item:hover,
    .fn-rv__item:focus {
        border-color: var(--color-primary, #FF8C2C);
        box-shadow: 0 10px 24px rgba(5, 93, 146, .1);
        transform: translateY(-1px);
        outline: none;
    }

    .fn-rv__badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        border-radius: 6px;
        overflow: hidden;
        background: var(--color-bg-filter, #FFE0C0);
        color: var(--color-primary-dk, #E47012);
        font-family: 'Inter', sans-serif;
        font-size: 16px;
        font-weight: 700;
    }

    .fn-rv__badge img {
        width: 100% !important;
        height: 100% !important;
        object-fit: contain !important;
        margin: 0 !important;
    }

    .fn-rv__content {
        display: flex;
        flex-direction: column;
        gap: 3px;
        min-width: 0;
        flex: 1 1 auto;
    }

    .fn-rv__job-title {
        font-family: 'Inter', sans-serif;
        font-size: 15px;
        font-weight: 700;
        line-height: 1.3;
        color: #27323A;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .fn-rv__meta {
        font-family: 'Poppins', sans-serif;
        font-size: 12.5px;
        font-weight: 400;
        line-height: 1.35;
        color: var(--color-text-muted, #6B7280);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .fn-rv__posted {
        font-family: 'Poppins', sans-serif;
        font-size: 12px;
        font-weight: 400;
        color: #9AA3A8;
    }

    .fn-rv__arrow {
        width: 8px;
        height: 8px;
        flex: 0 0 8px;
        border-right: 2px solid var(--color-primary, #FF8C2C);
        border-bottom: 2px solid var(--color-primary, #FF8C2C);
        transform: rotate(-45deg);
        transition: transform .18s ease;
    }

    .fn-rv__item:hover .fn-rv__arrow,
    .fn-rv__item:focus .fn-rv__arrow {
        transform: translateX(3px) rotate(-45deg);
    }

    .fn-rv__button {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        margin-top: 16px !important;
        padding: 12px 20px !important;
        border: 1.5px solid var(--color-primary, #FF8C2C) !important;
        border-radius: 5px !important;
        background: transparent !important;
        color: var(--color-primary-dk, #E47012) !important;
        font-family: 'Work Sans', sans-serif !important;
        font-size: 15px !important;
        font-weight: 700 !important;
        text-decoration: none !important;
        transition: background-color .15s ease, color .15s ease !important;
    }

    .fn-rv__button:hover,
    .fn-rv__button:focus {
        background: var(--color-primary, #FF8C2C) !important;
        color: #ffffff !important;
    }

    @media (max-width: 600px) {
        .fn-rv__item { padding: 12px; }
        .fn-rv__badge { width: 36px; height: 36px; flex: 0 0 36px; font-size: 14px; }
    }
    </style>
    <?php
}
