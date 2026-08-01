<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [duurzame_beroepen]
 * Toont alle pagina's waarbij het vinkje "Dit is een duurzaam beroep" aan
 * staat (zie inc/duurzame-beroepen-meta.php), in dezelfde vormgeving en
 * opbouw als de vacaturedirectory-shortcode (inc/shortcode-vacature-directory.php).
 */
add_shortcode('duurzame_beroepen', 'sj_duurzame_beroepen_shortcode');

function sj_duurzame_beroepen_shortcode($atts): string {
    $atts = shortcode_atts([
        'title'    => 'Alle duurzame beroepen',
        'subtitle' => 'Blader door alle beroepen binnen duurzaamheid en energietransitie.',
    ], $atts, 'duurzame_beroepen');

    $query = new WP_Query([
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'meta_query'     => [
            [
                'key'   => SJ_DUURZAAM_BEROEP_META_KEY,
                'value' => '1',
            ],
        ],
    ]);

    if (!$query->have_posts()) {
        return '';
    }

    $instance_id = wp_unique_id('sj_beroepen_');
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
    <div class="sj-beroepen-directory" data-sj-beroepen-directory>
        <div class="sj-beroepen-filter">
            <div class="filter-header">
                <h2><?php echo esc_html($atts['title']); ?></h2>
                <p><?php echo esc_html($atts['subtitle']); ?></p>
            </div>

            <div class="search-basic">
                <div class="search_keywords sj-beroepen-search">
                    <label class="sj-beroepen-filter__sr" for="<?php echo esc_attr($instance_id); ?>search">Zoeken in duurzame beroepen</label>
                    <input
                        type="text"
                        id="<?php echo esc_attr($instance_id); ?>search"
                        placeholder="Zoek een beroep.."
                        data-sj-beroepen-search
                    >
                </div>
            </div>
        </div>

        <div class="sj-beroepen-directory__results">
            <section class="sj-beroepen-directory__section" data-sj-beroepen-section>
                <div class="sj-beroepen-directory__section-header">
                    <h2 class="sj-beroepen-directory__heading">Duurzame beroepen</h2>
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
                <h2>Geen beroepen gevonden.</h2>
                <p>Pas je zoekopdracht aan om meer beroepen te zien.</p>
            </div>
        </div>
    </div>

    <?php sj_duurzame_beroepen_print_assets(); ?>
    <?php
    return trim(ob_get_clean());
}

function sj_duurzame_beroepen_print_assets(): void {
    static $printed = false;
    if ($printed) {
        return;
    }
    $printed = true;
    ?>
    <style>
    .sj-beroepen-directory,
    .sj-beroepen-directory *,
    .sj-beroepen-directory *::before,
    .sj-beroepen-directory *::after { box-sizing: border-box; }

    .sj-beroepen-directory {
        width: 100%;
        font-family: 'Poppins', sans-serif;
    }

    .sj-beroepen-directory [hidden] { display: none !important; }

    .sj-beroepen-filter {
        width: 100vw;
        position: relative;
        left: 50%;
        right: 50%;
        margin: 0 -50vw 40px;
        padding: 56px 0;
        background: var(--color-bg-filter, #EEF6F4);
        border-top: 1px solid var(--color-border, #C0D8D4);
        border-bottom: 1px solid var(--color-border, #C0D8D4);
        box-sizing: border-box;
    }

    .sj-beroepen-filter .filter-header,
    .sj-beroepen-filter .search-basic,
    .sj-beroepen-directory__results {
        max-width: 1200px;
        margin-left: auto !important;
        margin-right: auto !important;
    }

    .sj-beroepen-filter .filter-header {
        padding: 0 24px 18px !important;
    }

    .sj-beroepen-filter .filter-header h2 {
        margin: 0;
        font-family: 'Inter', sans-serif;
        font-size: 24px;
        line-height: 1.1;
        font-weight: 700;
        color: var(--color-midnight-blue, #254F6E);
    }

    .sj-beroepen-filter .filter-header p {
        margin: 10px 0 0;
        font-family: 'Poppins', sans-serif;
        font-size: 15px;
        color: var(--color-text-muted, #777777);
    }

    .sj-beroepen-filter .search-basic {
        display: flex;
        gap: 16px;
        padding: 0 24px;
    }

    .sj-beroepen-search {
        width: 100%;
        display: flex;
        align-items: center;
        position: relative;
    }

    .sj-beroepen-filter input[type="text"] {
        width: 100%;
        padding: 13px 14px 13px 40px;
        font-size: 15px;
        border: 1px solid #DDE8C5;
        border-radius: 8px;
        background-color: #ffffff;
        color: var(--color-text, #333333);
        box-shadow: none;
        transition: border-color .2s ease, box-shadow .2s ease;
        font-family: 'Poppins', sans-serif;
        font-weight: 400;
    }

    .sj-beroepen-filter input[type="text"]:focus {
        outline: none;
        border-color: var(--color-primary, #168AAD);
        box-shadow: 0 0 0 3px rgba(22, 138, 173, 0.15);
    }

    .sj-beroepen-filter input[type="text"]::placeholder {
        color: #7c7c7c;
        font-size: 15px !important;
        font-style: italic;
    }

    .sj-beroepen-search::before {
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
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23168AAD' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E");
    }

    .sj-beroepen-filter__sr {
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

    .sj-beroepen-directory__results {
        padding: 0 24px 64px;
    }

    .sj-beroepen-directory__section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0 0 18px;
    }

    .sj-beroepen-directory__heading {
        margin: 0 !important;
        font-family: 'Inter', sans-serif !important;
        font-size: 24px !important;
        font-weight: 700 !important;
        line-height: 1.2 !important;
        color: #333333 !important;
    }

    .sj-beroepen-directory__section-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 30px;
        height: 30px;
        padding: 0 9px;
        border-radius: 999px;
        background: var(--color-secondary-soft, #EEF7E9);
        color: var(--color-primary-dk, #254F6E);
        font-family: 'Inter', sans-serif;
        font-size: 13px;
        font-weight: 700;
        line-height: 1;
    }

    .sj-beroepen-directory__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 14px;
    }

    .sj-beroepen-directory__item {
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 86px;
        padding: 18px;
        background: #ffffff;
        border: 1px solid #DEDEDE;
        border-radius: 6px;
        color: #333333 !important;
        text-decoration: none !important;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .sj-beroepen-directory__item:hover,
    .sj-beroepen-directory__item:focus {
        border-color: var(--color-primary, #168AAD);
        box-shadow: 0 10px 28px rgba(37, 79, 110, .08);
        transform: translateY(-1px);
        outline: none;
    }

    .sj-beroepen-directory__badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 6px;
        flex: 0 0 42px;
        background: var(--color-bg-filter, #EEF6F4);
        color: var(--color-primary, #168AAD);
        font-family: 'Inter', sans-serif;
        font-size: 18px;
        font-weight: 700;
    }

    .sj-beroepen-directory__content {
        display: flex;
        flex-direction: column;
        gap: 5px;
        min-width: 0;
        flex: 1 1 auto;
    }

    .sj-beroepen-directory__name {
        font-family: 'Inter', sans-serif;
        font-size: 16px;
        font-weight: 700;
        line-height: 1.3;
        color: #333333;
    }

    .sj-beroepen-directory__meta {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 1;
        overflow: hidden;
        font-family: 'Poppins', sans-serif;
        font-size: 13px;
        font-weight: 400;
        line-height: 1.35;
        color: var(--color-text-muted, #777777);
    }

    .sj-beroepen-directory__arrow {
        width: 10px;
        height: 10px;
        flex: 0 0 10px;
        border-right: 2px solid var(--color-primary, #168AAD);
        border-bottom: 2px solid var(--color-primary, #168AAD);
        transform: rotate(-45deg);
        transition: transform .18s ease;
    }

    .sj-beroepen-directory__item:hover .sj-beroepen-directory__arrow,
    .sj-beroepen-directory__item:focus .sj-beroepen-directory__arrow {
        transform: translateX(3px) rotate(-45deg);
    }

    .sj-beroepen-directory__empty {
        background: #ffffff;
        border: 1px solid #DEDEDE;
        border-radius: 6px;
        padding: 24px;
    }

    .sj-beroepen-directory__empty h2 {
        margin: 0 0 8px;
        font-family: 'Inter', sans-serif;
        font-size: 22px;
        font-weight: 700;
        color: #333333;
    }

    .sj-beroepen-directory__empty p {
        margin: 0;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        line-height: 1.7;
        color: var(--color-text-muted, #777777);
    }

    @media (max-width: 768px) {
        .sj-beroepen-filter {
            padding: 36px 0;
        }

        .sj-beroepen-filter .filter-header,
        .sj-beroepen-filter .search-basic,
        .sj-beroepen-directory__results {
            padding-left: 16px !important;
            padding-right: 16px !important;
        }

        .sj-beroepen-filter .filter-header h2 {
            font-size: 22px;
        }

        .sj-beroepen-directory__grid {
            grid-template-columns: 1fr;
        }

        .sj-beroepen-directory__item {
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
            const searchInput = directory.querySelector('[data-sj-beroepen-search]');
            const items = Array.from(directory.querySelectorAll('[data-sj-beroepen-item]'));
            const section = directory.querySelector('[data-sj-beroepen-section]');
            const count = directory.querySelector('[data-sj-beroepen-section-count]');
            const empty = directory.querySelector('[data-sj-beroepen-empty]');

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
                document.querySelectorAll('[data-sj-beroepen-directory]').forEach(initBeroepenDirectory);
            });
        } else {
            document.querySelectorAll('[data-sj-beroepen-directory]').forEach(initBeroepenDirectory);
        }
    }());
    </script>
    <?php
}
