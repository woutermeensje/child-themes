<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [sj_recente_vacatures]
 * Compacte lijst met de laatste vacatures, bedoeld voor het smalle (~40%)
 * zijblok op de duurzame-beroepengids landingspagina's. Is qua filters een
 * variant van de standaard [jobs] shortcode van WP Job Manager, maar dan
 * met een smalle kaart-opbouw i.p.v. de grote vacaturekaart.
 *
 * Attributen (zelfde taxonomie-filters als [jobs]):
 * - limit: aantal vacatures (standaard 5)
 * - job_sector / job_company / job_tag / job_listing_type / organisatie_type: comma-separated slugs
 * - title: kop boven de lijst (standaard "Recente vacatures")
 * - button_text / button_url: link onderaan naar het volledige overzicht
 * - show_button: 1 of 0 (standaard 1)
 */
add_shortcode('sj_recente_vacatures', 'sj_recente_vacatures_shortcode');

function sj_recente_vacatures_shortcode($atts): string {
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
    ], $atts, 'sj_recente_vacatures');

    $taxonomy_filters = [
        'job_sector'       => 'job_sector',
        'job_company'      => 'job_company',
        'job_tag'          => 'job_tag',
        'job_listing_type' => 'job_listing_type',
        'organisatie_type' => 'organisatie_type',
    ];

    $tax_query = [];
    foreach ($taxonomy_filters as $attr => $taxonomy) {
        if (empty($atts[$attr])) {
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
    <div class="sj-rv">
        <?php if ($atts['title']): ?>
            <h3 class="sj-rv__title"><?php echo esc_html($atts['title']); ?></h3>
        <?php endif; ?>

        <div class="sj-rv__list">
            <?php while ($jobs->have_posts()): $jobs->the_post();
                $post_id      = get_the_ID();
                $company_name = function_exists('get_the_company_name') ? get_the_company_name($post_id) : '';
                $location     = function_exists('get_the_job_location') ? get_the_job_location($post_id) : '';
                $logo_html    = function_exists('sj_get_company_logo_html') ? sj_get_company_logo_html($post_id, 'thumbnail') : '';
                $initial      = $company_name ? mb_substr($company_name, 0, 1) : mb_substr(get_the_title(), 0, 1);
                $posted_ago   = human_time_diff(get_post_time('U', true, $post_id), current_time('timestamp', true));

                $meta_parts = array_filter([$company_name, $location]);
            ?>
                <a class="sj-rv__item" href="<?php the_permalink(); ?>">
                    <span class="sj-rv__badge" aria-hidden="true">
                        <?php if ($logo_html): ?>
                            <?php echo $logo_html; ?>
                        <?php else: ?>
                            <?php echo esc_html(strtoupper($initial)); ?>
                        <?php endif; ?>
                    </span>
                    <span class="sj-rv__content">
                        <span class="sj-rv__job-title"><?php the_title(); ?></span>
                        <?php if ($meta_parts): ?>
                            <span class="sj-rv__meta"><?php echo esc_html(implode(' · ', $meta_parts)); ?></span>
                        <?php endif; ?>
                        <span class="sj-rv__posted"><?php echo esc_html($posted_ago); ?> geleden</span>
                    </span>
                    <span class="sj-rv__arrow" aria-hidden="true"></span>
                </a>
            <?php endwhile; ?>
        </div>

        <?php if (sj_vacature_directory_bool($atts['show_button']) && $atts['button_url']): ?>
            <a class="sj-rv__button" href="<?php echo esc_url($atts['button_url']); ?>"><?php echo esc_html($atts['button_text']); ?></a>
        <?php endif; ?>
    </div>

    <?php wp_reset_postdata(); ?>
    <?php sj_recente_vacatures_print_assets(); ?>
    <?php
    return trim(ob_get_clean());
}

function sj_recente_vacatures_print_assets(): void {
    static $printed = false;
    if ($printed) {
        return;
    }
    $printed = true;
    ?>
    <style>
    .sj-rv,
    .sj-rv *,
    .sj-rv *::before,
    .sj-rv *::after { box-sizing: border-box; }

    .sj-rv {
        width: 100%;
        font-family: 'Poppins', sans-serif;
    }

    .sj-rv__title {
        margin: 0 0 16px !important;
        font-family: 'Inter', sans-serif !important;
        font-size: 20px !important;
        font-weight: 700 !important;
        line-height: 1.2 !important;
        color: #254F6E !important;
    }

    .sj-rv__list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .sj-rv__item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px;
        background: #ffffff;
        border: 1px solid #DEDEDE;
        border-radius: 6px;
        color: #333333 !important;
        text-decoration: none !important;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .sj-rv__item:hover,
    .sj-rv__item:focus {
        border-color: #168AAD;
        box-shadow: 0 10px 24px rgba(37, 79, 110, .1);
        transform: translateY(-1px);
        outline: none;
    }

    .sj-rv__badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        border-radius: 6px;
        overflow: hidden;
        background: #EEF6F4;
        color: #168AAD;
        font-family: 'Inter', sans-serif;
        font-size: 16px;
        font-weight: 700;
    }

    .sj-rv__badge img {
        width: 100% !important;
        height: 100% !important;
        object-fit: contain !important;
        margin: 0 !important;
    }

    .sj-rv__content {
        display: flex;
        flex-direction: column;
        gap: 3px;
        min-width: 0;
        flex: 1 1 auto;
    }

    .sj-rv__job-title {
        font-family: 'Inter', sans-serif;
        font-size: 15px;
        font-weight: 700;
        line-height: 1.3;
        color: #27323A;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .sj-rv__meta {
        font-family: 'Poppins', sans-serif;
        font-size: 12.5px;
        font-weight: 400;
        line-height: 1.35;
        color: #666666;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .sj-rv__posted {
        font-family: 'Poppins', sans-serif;
        font-size: 12px;
        font-weight: 400;
        color: #9AA3A8;
    }

    .sj-rv__arrow {
        width: 8px;
        height: 8px;
        flex: 0 0 8px;
        border-right: 2px solid #168AAD;
        border-bottom: 2px solid #168AAD;
        transform: rotate(-45deg);
        transition: transform .18s ease;
    }

    .sj-rv__item:hover .sj-rv__arrow,
    .sj-rv__item:focus .sj-rv__arrow {
        transform: translateX(3px) rotate(-45deg);
    }

    .sj-rv__button {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        margin-top: 16px !important;
        padding: 12px 20px !important;
        border: 1.5px solid #168AAD !important;
        border-radius: 5px !important;
        background: transparent !important;
        color: #168AAD !important;
        font-family: 'Work Sans', sans-serif !important;
        font-size: 15px !important;
        font-weight: 700 !important;
        text-decoration: none !important;
        transition: background-color .15s ease, color .15s ease !important;
    }

    .sj-rv__button:hover,
    .sj-rv__button:focus {
        background: #168AAD !important;
        color: #ffffff !important;
    }

    @media (max-width: 600px) {
        .sj-rv__item { padding: 12px; }
        .sj-rv__badge { width: 36px; height: 36px; flex: 0 0 36px; font-size: 14px; }
    }
    </style>
    <?php
}
