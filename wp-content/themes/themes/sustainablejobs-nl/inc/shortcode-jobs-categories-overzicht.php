<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [jobs_categories_overzicht]
 * Toont alle pagina's waarbij het vinkje "Dit is een vacaturecategorie pagina"
 * aan staat (zie inc/vacature-categorieen-meta.php).
 */
add_shortcode('jobs_categories_overzicht', 'sj_jobs_categories_overzicht_shortcode');

function sj_jobs_categories_overzicht_shortcode($atts): string {
    $atts = shortcode_atts([
        'title'    => 'Alle vacaturecategorieën',
        'subtitle' => 'Blader door alle vacaturecategorieën binnen Sustainablejobs.nl.',
    ], $atts, 'jobs_categories_overzicht');

    $query = new WP_Query([
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'meta_query'     => [
            [
                'key'   => SJ_VACATURE_CATEGORIE_PAGE_META_KEY,
                'value' => '1',
            ],
        ],
    ]);

    if (!$query->have_posts()) {
        return '';
    }

    $instance_id = wp_unique_id('sj_jobs_categories_');
    $items       = [];

    while ($query->have_posts()) {
        $query->the_post();

        $excerpt = get_the_excerpt();
        $initial = function_exists('mb_substr') ? mb_substr(get_the_title(), 0, 1) : substr(get_the_title(), 0, 1);

        $items[] = [
            'title'   => get_the_title(),
            'url'     => get_permalink(),
            'excerpt' => $excerpt,
            'initial' => strtoupper($initial),
            'search'  => strtolower(wp_strip_all_tags(get_the_title() . ' ' . $excerpt)),
        ];
    }
    wp_reset_postdata();

    ob_start();
    ?>
    <div class="sj-beroepen-directory sj-jobs-categories-overzicht" data-sj-beroepen-directory>
        <div class="sj-beroepen-filter">
            <div class="filter-header">
                <h2><?php echo esc_html($atts['title']); ?></h2>
                <p><?php echo esc_html($atts['subtitle']); ?></p>
            </div>

            <div class="search-basic">
                <div class="search_keywords sj-beroepen-search">
                    <label class="sj-beroepen-filter__sr" for="<?php echo esc_attr($instance_id); ?>search">Zoeken in vacaturecategorieën</label>
                    <input
                        type="text"
                        id="<?php echo esc_attr($instance_id); ?>search"
                        placeholder="Zoek een categorie.."
                        data-sj-beroepen-search
                    >
                </div>
            </div>
        </div>

        <div class="sj-beroepen-directory__results">
            <section class="sj-beroepen-directory__section" data-sj-beroepen-section>
                <div class="sj-beroepen-directory__section-header">
                    <h2 class="sj-beroepen-directory__heading">Vacaturecategorieën</h2>
                    <span class="sj-beroepen-directory__section-count" data-sj-beroepen-section-count>
                        <?php echo esc_html(number_format_i18n(count($items))); ?>
                    </span>
                </div>

                <div class="sj-beroepen-directory__grid">
                    <?php foreach ($items as $item): ?>
                        <a
                            class="sj-beroepen-directory__item"
                            href="<?php echo esc_url($item['url']); ?>"
                            data-sj-beroepen-item
                            data-search="<?php echo esc_attr($item['search']); ?>"
                        >
                            <span class="sj-beroepen-directory__badge" aria-hidden="true"><?php echo esc_html($item['initial']); ?></span>
                            <span class="sj-beroepen-directory__content">
                                <span class="sj-beroepen-directory__name"><?php echo esc_html($item['title']); ?></span>
                                <?php if ($item['excerpt']): ?>
                                    <span class="sj-beroepen-directory__meta"><?php echo esc_html($item['excerpt']); ?></span>
                                <?php endif; ?>
                            </span>
                            <span class="sj-beroepen-directory__arrow" aria-hidden="true"></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <div class="sj-beroepen-directory__empty" data-sj-beroepen-empty hidden>
                <h2>Geen categorieën gevonden.</h2>
                <p>Pas je zoekopdracht aan om meer categorieën te zien.</p>
            </div>
        </div>
    </div>

    <?php
    if (function_exists('sj_duurzame_beroepen_print_assets')) {
        sj_duurzame_beroepen_print_assets();
    }
    ?>
    <?php
    return trim(ob_get_clean());
}
