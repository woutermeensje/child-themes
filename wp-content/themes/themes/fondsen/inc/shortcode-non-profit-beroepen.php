<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [non_profit_beroepen]
 * Toont alle pagina's waarbij het vinkje "Dit is een non-profit beroep" aan
 * staat (zie inc/non-profit-beroepen-meta.php), in een doorzoekbaar kaart-grid.
 *
 * Attributen:
 * - title:    kop boven het zoekblok
 * - subtitle: subtekst onder de kop
 *
 * Overgenomen van het sustainablejobs-nl child theme ([duurzame_beroepen]).
 */
add_shortcode('non_profit_beroepen', 'fn_non_profit_beroepen_shortcode');

function fn_non_profit_beroepen_shortcode($atts): string {
    $atts = shortcode_atts([
        'title'    => 'Alle non-profit beroepen',
        'subtitle' => 'Blader door alle beroepen binnen de non-profitsector.',
    ], $atts, 'non_profit_beroepen');

    $query = new WP_Query([
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'meta_query'     => [
            [
                'key'   => FN_NON_PROFIT_BEROEP_META_KEY,
                'value' => '1',
            ],
        ],
    ]);

    if (!$query->have_posts()) {
        return '';
    }

    $instance_id = wp_unique_id('fn_beroepen_');
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
    <div class="fn-beroepen-directory" data-fn-beroepen-directory>
        <div class="fn-beroepen-filter">
            <div class="filter-header">
                <h2><?php echo esc_html($atts['title']); ?></h2>
                <p><?php echo esc_html($atts['subtitle']); ?></p>
            </div>

            <div class="search-basic">
                <div class="search_keywords fn-beroepen-search">
                    <label class="fn-beroepen-filter__sr" for="<?php echo esc_attr($instance_id); ?>search">Zoeken in non-profit beroepen</label>
                    <input
                        type="text"
                        id="<?php echo esc_attr($instance_id); ?>search"
                        placeholder="Zoek een beroep.."
                        data-fn-beroepen-search
                    >
                </div>
            </div>
        </div>

        <div class="fn-beroepen-directory__results">
            <section class="fn-beroepen-directory__section" data-fn-beroepen-section>
                <div class="fn-beroepen-directory__section-header">
                    <h2 class="fn-beroepen-directory__heading">Non-profit beroepen</h2>
                    <span class="fn-beroepen-directory__section-count" data-fn-beroepen-section-count>
                        <?php echo esc_html(number_format_i18n(count($items))); ?>
                    </span>
                </div>

                <div class="fn-beroepen-directory__grid">
                    <?php foreach ($items as $item): ?>
                        <a
                            class="fn-beroepen-directory__item"
                            href="<?php echo esc_url($item['url']); ?>"
                            data-fn-beroepen-item
                            data-search="<?php echo esc_attr($item['search']); ?>"
                        >
                            <span class="fn-beroepen-directory__badge" aria-hidden="true"><?php echo esc_html($item['initial']); ?></span>
                            <span class="fn-beroepen-directory__content">
                                <span class="fn-beroepen-directory__name"><?php echo esc_html($item['title']); ?></span>
                                <?php if ($item['excerpt']): ?>
                                    <span class="fn-beroepen-directory__meta"><?php echo esc_html($item['excerpt']); ?></span>
                                <?php endif; ?>
                            </span>
                            <span class="fn-beroepen-directory__arrow" aria-hidden="true"></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <div class="fn-beroepen-directory__empty" data-fn-beroepen-empty hidden>
                <h2>Geen beroepen gevonden.</h2>
                <p>Pas je zoekopdracht aan om meer beroepen te zien.</p>
            </div>
        </div>
    </div>

    <?php fn_non_profit_beroepen_print_assets(); ?>
    <?php
    return trim(ob_get_clean());
}

function fn_non_profit_beroepen_print_assets(): void {
    static $printed = false;
    if ($printed) {
        return;
    }
    $printed = true;
    ?>
    <style>
    .fn-beroepen-directory,
    .fn-beroepen-directory *,
    .fn-beroepen-directory *::before,
    .fn-beroepen-directory *::after { box-sizing: border-box; }

    .fn-beroepen-directory {
        width: 100%;
        font-family: 'Poppins', sans-serif;
    }

    .fn-beroepen-directory [hidden] { display: none !important; }

    .fn-beroepen-filter {
        width: 100vw;
        position: relative;
        left: 50%;
        right: 50%;
        margin: 0 -50vw 40px;
        padding: 56px 0;
        background: var(--color-bg-filter, #FFE0C0);
        border-top: 1px solid var(--color-border, #E0E0E0);
        border-bottom: 1px solid var(--color-border, #E0E0E0);
        box-sizing: border-box;
    }

    .fn-beroepen-filter .filter-header,
    .fn-beroepen-filter .search-basic,
    .fn-beroepen-directory__results {
        max-width: 1200px;
        margin-left: auto !important;
        margin-right: auto !important;
    }

    .fn-beroepen-filter .filter-header {
        padding: 0 24px 18px !important;
    }

    .fn-beroepen-filter .filter-header h2 {
        margin: 0;
        font-family: 'Work Sans', sans-serif;
        font-size: 24px;
        line-height: 1.1;
        font-weight: 700;
        color: #333333;
    }

    .fn-beroepen-filter .filter-header p {
        margin: 10px 0 0;
        font-family: 'Poppins', sans-serif;
        font-size: 15px;
        color: var(--color-text-muted, #6B7280);
    }

    .fn-beroepen-filter .search-basic {
        display: flex;
        gap: 16px;
        padding: 0 24px;
    }

    .fn-beroepen-search {
        width: 100%;
        display: flex;
        align-items: center;
        position: relative;
    }

    .fn-beroepen-filter input[type="text"] {
        width: 100%;
        padding: 13px 14px 13px 40px;
        font-size: 15px;
        border: 1px solid var(--color-border, #E0E0E0);
        border-radius: 8px;
        background-color: #ffffff;
        color: var(--color-text, #333333);
        box-shadow: none;
        transition: border-color .2s ease, box-shadow .2s ease;
        font-family: 'Poppins', sans-serif;
        font-weight: 400;
    }

    .fn-beroepen-filter input[type="text"]:focus {
        outline: none;
        border-color: var(--color-primary, #FF8C2C);
        box-shadow: 0 0 0 3px rgba(255, 140, 44, 0.15);
    }

    .fn-beroepen-filter input[type="text"]::placeholder {
        color: #7c7c7c;
        font-size: 15px !important;
        font-style: italic;
    }

    .fn-beroepen-search::before {
        content: '';
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        background-repeat: no-repeat;
        background-size: contain;
        pointer-events: none;
        z-index: 1;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23FF8C2C' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E");
    }

    .fn-beroepen-filter__sr {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    .fn-beroepen-directory__results {
        padding: 0 24px 64px;
    }

    .fn-beroepen-directory__section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0 0 18px;
    }

    .fn-beroepen-directory__heading {
        margin: 0 !important;
        font-family: 'Inter', sans-serif !important;
        font-size: 24px !important;
        font-weight: 700 !important;
        line-height: 1.2 !important;
        color: #333333 !important;
    }

    .fn-beroepen-directory__section-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 30px;
        height: 30px;
        padding: 0 9px;
        border-radius: 999px;
        background: var(--color-accent, #E7F4FB);
        color: var(--color-tertiary, #055D92);
        font-family: 'Inter', sans-serif;
        font-size: 13px;
        font-weight: 700;
        line-height: 1;
    }

    .fn-beroepen-directory__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 14px;
    }

    .fn-beroepen-directory__item {
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 86px;
        padding: 18px;
        background: #ffffff;
        border: 1px solid var(--color-border, #E0E0E0);
        border-radius: 6px;
        color: #333333 !important;
        text-decoration: none !important;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .fn-beroepen-directory__item:hover,
    .fn-beroepen-directory__item:focus {
        border-color: var(--color-primary, #FF8C2C);
        box-shadow: 0 10px 28px rgba(5, 93, 146, .08);
        transform: translateY(-1px);
        outline: none;
    }

    .fn-beroepen-directory__badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 6px;
        flex: 0 0 42px;
        background: var(--color-bg-filter, #FFE0C0);
        color: var(--color-primary-dk, #E47012);
        font-family: 'Inter', sans-serif;
        font-size: 18px;
        font-weight: 700;
    }

    .fn-beroepen-directory__content {
        display: flex;
        flex-direction: column;
        gap: 5px;
        min-width: 0;
        flex: 1 1 auto;
    }

    .fn-beroepen-directory__name {
        font-family: 'Inter', sans-serif;
        font-size: 16px;
        font-weight: 700;
        line-height: 1.3;
        color: #333333;
    }

    .fn-beroepen-directory__meta {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 1;
        overflow: hidden;
        font-family: 'Poppins', sans-serif;
        font-size: 13px;
        font-weight: 400;
        line-height: 1.35;
        color: var(--color-text-muted, #6B7280);
    }

    .fn-beroepen-directory__arrow {
        width: 10px;
        height: 10px;
        flex: 0 0 10px;
        border-right: 2px solid var(--color-primary, #FF8C2C);
        border-bottom: 2px solid var(--color-primary, #FF8C2C);
        transform: rotate(-45deg);
        transition: transform .18s ease;
    }

    .fn-beroepen-directory__item:hover .fn-beroepen-directory__arrow,
    .fn-beroepen-directory__item:focus .fn-beroepen-directory__arrow {
        transform: translateX(3px) rotate(-45deg);
    }

    .fn-beroepen-directory__empty {
        background: #ffffff;
        border: 1px solid var(--color-border, #E0E0E0);
        border-radius: 6px;
        padding: 24px;
    }

    .fn-beroepen-directory__empty h2 {
        margin: 0 0 8px;
        font-family: 'Inter', sans-serif;
        font-size: 22px;
        font-weight: 700;
        color: #333333;
    }

    .fn-beroepen-directory__empty p {
        margin: 0;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        line-height: 1.7;
        color: var(--color-text-muted, #6B7280);
    }

    @media (max-width: 768px) {
        .fn-beroepen-filter {
            padding: 36px 0;
        }

        .fn-beroepen-filter .filter-header,
        .fn-beroepen-filter .search-basic,
        .fn-beroepen-directory__results {
            padding-left: 16px !important;
            padding-right: 16px !important;
        }

        .fn-beroepen-filter .filter-header h2 {
            font-size: 22px;
        }

        .fn-beroepen-directory__grid {
            grid-template-columns: 1fr;
        }

        .fn-beroepen-directory__item {
            min-height: 78px;
            padding: 15px;
        }
    }
    </style>
    <script>
    (function () {
        function normalize(value) {
            return String(value || '').trim().toLowerCase();
        }

        function initBeroepenDirectory(directory) {
            const searchInput = directory.querySelector('[data-fn-beroepen-search]');
            const items = Array.from(directory.querySelectorAll('[data-fn-beroepen-item]'));
            const section = directory.querySelector('[data-fn-beroepen-section]');
            const count = directory.querySelector('[data-fn-beroepen-section-count]');
            const empty = directory.querySelector('[data-fn-beroepen-empty]');

            if (!searchInput || items.length === 0) return;

            function applyFilter() {
                const term = normalize(searchInput.value);
                let visibleCount = 0;

                items.forEach((item) => {
                    const isVisible = term === '' || normalize(item.dataset.search).includes(term);
                    item.hidden = !isVisible;
                    if (isVisible) visibleCount += 1;
                });

                if (count) count.textContent = String(visibleCount);
                if (section) section.hidden = visibleCount === 0;
                if (empty) empty.hidden = visibleCount > 0;
            }

            searchInput.addEventListener('input', applyFilter);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-fn-beroepen-directory]').forEach(initBeroepenDirectory);
            });
        } else {
            document.querySelectorAll('[data-fn-beroepen-directory]').forEach(initBeroepenDirectory);
        }
    }());
    </script>
    <?php
}
