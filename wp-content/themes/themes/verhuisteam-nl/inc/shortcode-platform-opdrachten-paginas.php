<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [platform_opdrachten_pagina's]
 * Alias: [platform_opdrachten_paginas]
 * Preset: [platform_opdrachten_bekijk_ook]
 * Categorieen: [platform_opdrachten_vertalers], [platform_opdrachten_online_marketing],
 * [platform_opdrachten_office], [platform_opdrachten_logistiek], [platform_opdrachten_creative],
 * [platform_opdrachten_werkstudent], [platform_opdrachten_freelance]
 *
 * Toont alle pagina's waarbij het vinkje "Platform opdrachten" aan staat.
 */

add_shortcode("platform_opdrachten_pagina's", 'si_platform_opdrachten_paginas_shortcode');
add_shortcode('platform_opdrachten_paginas', 'si_platform_opdrachten_paginas_shortcode');
add_shortcode('platform_opdrachten_bekijk_ook', 'si_platform_opdrachten_paginas_shortcode');
add_shortcode('platform_opdrachten_vertalers', 'si_platform_opdrachten_paginas_shortcode');
add_shortcode('platform_opdrachten_online_marketing', 'si_platform_opdrachten_paginas_shortcode');
add_shortcode('platform_opdrachten_office', 'si_platform_opdrachten_paginas_shortcode');
add_shortcode('platform_opdrachten_logistiek', 'si_platform_opdrachten_paginas_shortcode');
add_shortcode('platform_opdrachten_creative', 'si_platform_opdrachten_paginas_shortcode');
add_shortcode('platform_opdrachten_werkstudent', 'si_platform_opdrachten_paginas_shortcode');
add_shortcode('platform_opdrachten_freelance', 'si_platform_opdrachten_paginas_shortcode');

function si_platform_opdrachten_paginas_shortcode($atts = [], $content = null, string $shortcode_tag = ''): string {
    $category_shortcode = si_platform_opdrachten_paginas_category_from_shortcode($shortcode_tag);

    $defaults = [
        'title'              => "Werkzaamheden waarvoor je opdrachten kunt plaatsen",
        'subtitle'           => "Bekijk voor welke werkzaamheden je via Verhuisteam.nl snel studenten, starters en young professionals kunt inhuren.",
        'section_title'      => 'Werkzaamheden',
        'search'             => 'true',
        'search_placeholder' => 'Zoek werkzaamheden..',
        'button_label'       => 'Bekijk mogelijkheden',
        'limit'              => 0,
        'category'           => $category_shortcode,
    ];

    if ($shortcode_tag === 'platform_opdrachten_bekijk_ook') {
        $defaults['title']         = 'Bekijk ook:';
        $defaults['subtitle']      = '';
        $defaults['section_title'] = '';
        $defaults['search']        = 'false';
        $defaults['limit']         = 12;
    }

    if ($category_shortcode) {
        $category_label = si_platform_opdrachten_paginas_category_label($category_shortcode);

        $defaults['title']         = $category_label;
        $defaults['subtitle']      = '';
        $defaults['section_title'] = '';
        $defaults['search']        = 'false';
    }

    $atts = shortcode_atts($defaults, $atts, $shortcode_tag ?: 'platform_opdrachten_paginas');

    if (!defined('SI_PLATFORM_OPDRACHT_PAGE_META_KEY')) {
        return '';
    }

    $limit = max(0, (int) $atts['limit']);
    $category = sanitize_key((string) $atts['category']);

    $query_args = [
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => $limit > 0 ? $limit : -1,
        'orderby'        => [
            'menu_order' => 'ASC',
            'title'      => 'ASC',
        ],
        'meta_query'     => [
            [
                'key'   => SI_PLATFORM_OPDRACHT_PAGE_META_KEY,
                'value' => '1',
            ],
        ],
    ];

    if ($category && function_exists('si_platform_opdracht_categories') && isset(si_platform_opdracht_categories()[$category])) {
        $query_args['meta_query'][] = [
            'key'   => si_platform_opdracht_category_meta_key($category),
            'value' => '1',
        ];
    }

    $query = new WP_Query(apply_filters('si_platform_opdrachten_paginas_query_args', $query_args, $atts));

    if (!$query->have_posts()) {
        return '';
    }

    $items = [];

    while ($query->have_posts()) {
        $query->the_post();

        $title   = get_the_title();
        $excerpt = wp_trim_words(wp_strip_all_tags(get_the_excerpt()), 24, '...');
        $initial = function_exists('mb_substr') ? mb_substr($title, 0, 1, 'UTF-8') : substr($title, 0, 1);

        $items[] = [
            'title'   => $title,
            'url'     => get_permalink(),
            'excerpt' => $excerpt,
            'initial' => function_exists('mb_strtoupper') ? mb_strtoupper($initial, 'UTF-8') : strtoupper($initial),
            'search'  => si_platform_opdrachten_paginas_search_text($title . ' ' . $excerpt),
        ];
    }

    wp_reset_postdata();

    $instance_id = wp_unique_id('si_platform_pages_');
    $show_search = si_platform_opdrachten_paginas_bool($atts['search']);

    ob_start();
    ?>
    <div class="si-platform-pages" data-si-platform-pages>
        <div class="si-platform-pages__filter">
            <div class="si-platform-pages__filter-inner">
                <div class="si-platform-pages__intro">
                    <h2><?php echo esc_html($atts['title']); ?></h2>
                    <?php if ($atts['subtitle']): ?>
                        <p><?php echo esc_html($atts['subtitle']); ?></p>
                    <?php endif; ?>
                </div>

                <?php if ($show_search): ?>
                    <div class="si-platform-pages__search">
                        <label class="si-platform-pages__sr" for="<?php echo esc_attr($instance_id); ?>search">Zoeken in werkzaamheden</label>
                        <input
                            type="text"
                            id="<?php echo esc_attr($instance_id); ?>search"
                            placeholder="<?php echo esc_attr($atts['search_placeholder']); ?>"
                            data-si-platform-pages-search
                        >
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="si-platform-pages__results">
            <section class="si-platform-pages__section" data-si-platform-pages-section>
                <?php if ($atts['section_title']): ?>
                    <div class="si-platform-pages__section-header">
                        <h2 class="si-platform-pages__heading"><?php echo esc_html($atts['section_title']); ?></h2>
                        <span class="si-platform-pages__count" data-si-platform-pages-count><?php echo esc_html(number_format_i18n(count($items))); ?></span>
                    </div>
                <?php else: ?>
                    <span class="si-platform-pages__count si-platform-pages__count--hidden" data-si-platform-pages-count><?php echo esc_html(number_format_i18n(count($items))); ?></span>
                <?php endif; ?>

                <div class="si-platform-pages__grid">
                    <?php foreach ($items as $item): ?>
                        <a
                            class="si-platform-pages__card"
                            href="<?php echo esc_url($item['url']); ?>"
                            data-si-platform-pages-item
                            data-search="<?php echo esc_attr($item['search']); ?>"
                        >
                            <span class="si-platform-pages__badge" aria-hidden="true"><?php echo esc_html($item['initial']); ?></span>
                            <span class="si-platform-pages__content">
                                <span class="si-platform-pages__name"><?php echo esc_html($item['title']); ?></span>
                                <?php if ($item['excerpt']): ?>
                                    <span class="si-platform-pages__excerpt"><?php echo esc_html($item['excerpt']); ?></span>
                                <?php endif; ?>
                                <span class="si-platform-pages__button">
                                    <?php echo esc_html($atts['button_label']); ?>
                                    <span class="si-platform-pages__arrow" aria-hidden="true"></span>
                                </span>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <div class="si-platform-pages__empty" data-si-platform-pages-empty hidden>
                <h2>Geen werkzaamheden gevonden.</h2>
                <p>Pas je zoekopdracht aan om meer werkzaamheden te zien.</p>
            </div>
        </div>
    </div>

    <?php si_platform_opdrachten_paginas_print_assets(); ?>
    <?php
    return trim(ob_get_clean());
}

function si_platform_opdrachten_paginas_bool($value): bool {
    return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'ja', 'on'], true);
}

function si_platform_opdrachten_paginas_category_from_shortcode(string $shortcode_tag): string {
    $map = [
        'platform_opdrachten_vertalers'        => 'vertalers',
        'platform_opdrachten_online_marketing' => 'online-marketing',
        'platform_opdrachten_office'           => 'office',
        'platform_opdrachten_logistiek'        => 'logistiek',
        'platform_opdrachten_creative'         => 'creative',
        'platform_opdrachten_werkstudent'      => 'werkstudent',
        'platform_opdrachten_freelance'        => 'freelance',
    ];

    return $map[$shortcode_tag] ?? '';
}

function si_platform_opdrachten_paginas_category_label(string $category): string {
    if (function_exists('si_platform_opdracht_categories')) {
        $categories = si_platform_opdracht_categories();

        if (isset($categories[$category])) {
            return $categories[$category];
        }
    }

    return ucwords(str_replace('-', ' ', $category));
}

function si_platform_opdrachten_paginas_search_text(string $text): string {
    $text = wp_strip_all_tags($text);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim((string) $text);

    return function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
}

function si_platform_opdrachten_paginas_print_assets(): void {
    static $printed = false;

    if ($printed) {
        return;
    }

    $printed = true;
    ?>
    <style>
    .si-platform-pages,
    .si-platform-pages *,
    .si-platform-pages *::before,
    .si-platform-pages *::after { box-sizing: border-box; }

    .si-platform-pages {
        --si-platform-pages-primary: #356B8C;
        --si-platform-pages-primary-rgb: 53, 107, 140;
        width: 100%;
        font-family: 'Poppins', sans-serif;
    }

    .si-platform-pages [hidden] { display: none !important; }

    .si-platform-pages__filter {
        width: 100vw;
        position: relative;
        left: 50%;
        right: 50%;
        margin: 0 -50vw 40px;
        padding: 48px 0;
        background: #f4f8fb;
        border-top: 1px solid rgba(var(--si-platform-pages-primary-rgb), .24);
        border-bottom: 1px solid rgba(var(--si-platform-pages-primary-rgb), .24);
    }

    .si-platform-pages__filter-inner,
    .si-platform-pages__results {
        max-width: 1200px;
        margin-left: auto !important;
        margin-right: auto !important;
        padding-left: 24px;
        padding-right: 24px;
    }

    .si-platform-pages__intro {
        max-width: 760px;
        margin-bottom: 20px;
    }

    .si-platform-pages__intro h2,
    .si-platform-pages__heading,
    .si-platform-pages__empty h2 {
        font-family: 'Work Sans', sans-serif !important;
        font-weight: 700 !important;
        line-height: 1.2 !important;
        color: var(--si-platform-pages-primary) !important;
    }

    .si-platform-pages__intro h2 {
        margin: 0 !important;
        font-size: 26px !important;
    }

    .si-platform-pages__intro p {
        margin: 10px 0 0;
        max-width: 680px;
        font-family: 'Poppins', sans-serif;
        font-size: 15px;
        line-height: 1.65;
        color: var(--color-text-muted, #777777);
    }

    .si-platform-pages__search {
        width: 100%;
        position: relative;
    }

    .si-platform-pages__search::before {
        content: '';
        position: absolute;
        left: 14px;
        top: 50%;
        width: 18px;
        height: 18px;
        transform: translateY(-50%);
        pointer-events: none;
        background-repeat: no-repeat;
        background-size: contain;
        background-color: var(--si-platform-pages-primary);
        -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E") center / contain no-repeat;
        mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E") center / contain no-repeat;
    }

    .si-platform-pages__search input[type="text"] {
        width: 100%;
        min-height: 48px;
        padding: 13px 14px 13px 44px;
        border: 1px solid var(--color-border, #DEDEDE);
        border-radius: 8px;
        background: #ffffff;
        color: var(--color-text, #333333);
        box-shadow: none;
        font-family: 'Poppins', sans-serif;
        font-size: 15px;
        line-height: 1.35;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .si-platform-pages__search input[type="text"]:focus {
        outline: none;
        border-color: var(--si-platform-pages-primary);
        box-shadow: 0 0 0 3px rgba(var(--si-platform-pages-primary-rgb), .16);
    }

    .si-platform-pages__search input[type="text"]::placeholder {
        color: #7c7c7c;
        font-size: 15px !important;
        font-style: italic;
    }

    .si-platform-pages__sr {
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

    .si-platform-pages__results {
        padding-bottom: 64px;
    }

    .si-platform-pages__section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0 0 18px;
    }

    .si-platform-pages__heading {
        margin: 0 !important;
        font-size: 24px !important;
    }

    .si-platform-pages__count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 30px;
        height: 30px;
        padding: 0 9px;
        border-radius: 999px;
        background: rgba(128, 212, 36, .16);
        color: var(--si-platform-pages-primary);
        font-family: 'Work Sans', sans-serif;
        font-size: 13px;
        font-weight: 700;
        line-height: 1;
    }

    .si-platform-pages__count--hidden {
        display: none !important;
    }

    .si-platform-pages__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 14px;
    }

    .si-platform-pages__card {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        min-height: 150px;
        padding: 18px;
        background: #ffffff;
        border: 1px solid var(--color-border, #DEDEDE);
        border-radius: 8px;
        color: var(--color-text, #333333) !important;
        text-decoration: none !important;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .si-platform-pages__card:hover,
    .si-platform-pages__card:focus {
        border-color: var(--si-platform-pages-primary);
        box-shadow: 0 12px 30px rgba(var(--si-platform-pages-primary-rgb), .14);
        transform: translateY(-1px);
        outline: none;
    }

    .si-platform-pages__badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 8px;
        flex: 0 0 44px;
        background: rgba(var(--si-platform-pages-primary-rgb), .14);
        color: var(--si-platform-pages-primary);
        font-family: 'Work Sans', sans-serif;
        font-size: 18px;
        font-weight: 700;
    }

    .si-platform-pages__content {
        display: flex;
        min-width: 0;
        flex: 1 1 auto;
        flex-direction: column;
        gap: 8px;
    }

    .si-platform-pages__name {
        font-family: 'Work Sans', sans-serif;
        font-size: 17px;
        font-weight: 700;
        line-height: 1.3;
        color: #333333;
    }

    .si-platform-pages__excerpt {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
        font-family: 'Poppins', sans-serif;
        font-size: 13px;
        font-weight: 400;
        line-height: 1.45;
        color: var(--color-text-muted, #777777);
    }

    .si-platform-pages__button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: fit-content;
        margin-top: auto;
        color: var(--si-platform-pages-primary);
        font-family: 'Work Sans', sans-serif;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.2;
    }

    .si-platform-pages__arrow {
        width: 9px;
        height: 9px;
        flex: 0 0 9px;
        border-right: 2px solid currentColor;
        border-bottom: 2px solid currentColor;
        transform: rotate(-45deg);
        transition: transform .18s ease;
    }

    .si-platform-pages__card:hover .si-platform-pages__arrow,
    .si-platform-pages__card:focus .si-platform-pages__arrow {
        transform: translateX(3px) rotate(-45deg);
    }

    .si-platform-pages__empty {
        background: #ffffff;
        border: 1px solid var(--color-border, #DEDEDE);
        border-radius: 8px;
        padding: 24px;
    }

    .si-platform-pages__empty h2 {
        margin: 0 0 8px !important;
        font-size: 22px !important;
    }

    .si-platform-pages__empty p {
        margin: 0;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        line-height: 1.7;
        color: var(--color-text-muted, #777777);
    }

    @media (max-width: 768px) {
        .si-platform-pages__filter {
            padding: 36px 0;
        }

        .si-platform-pages__filter-inner,
        .si-platform-pages__results {
            padding-left: 16px;
            padding-right: 16px;
        }

        .si-platform-pages__intro h2 {
            font-size: 22px !important;
        }

        .si-platform-pages__grid {
            grid-template-columns: 1fr;
        }

        .si-platform-pages__card {
            min-height: 132px;
            padding: 15px;
        }
    }
    </style>
    <script>
    (function () {
        function normalize(value) {
            return String(value || '').trim().toLowerCase();
        }

        function initPlatformPages(directory) {
            const searchInput = directory.querySelector('[data-si-platform-pages-search]');
            const items = Array.from(directory.querySelectorAll('[data-si-platform-pages-item]'));
            const section = directory.querySelector('[data-si-platform-pages-section]');
            const count = directory.querySelector('[data-si-platform-pages-count]');
            const empty = directory.querySelector('[data-si-platform-pages-empty]');

            if (!items.length) return;

            function applyFilter() {
                const term = searchInput ? normalize(searchInput.value) : '';
                let visibleCount = 0;

                items.forEach((item) => {
                    const isVisible = term === '' || normalize(item.dataset.search).includes(term);
                    item.hidden = !isVisible;

                    if (isVisible) {
                        visibleCount += 1;
                    }
                });

                if (count) count.textContent = String(visibleCount);
                if (section) section.hidden = visibleCount === 0;
                if (empty) empty.hidden = visibleCount > 0;
            }

            if (searchInput) {
                searchInput.addEventListener('input', applyFilter);
            }

            applyFilter();
        }

        function initAll() {
            document.querySelectorAll('[data-si-platform-pages]').forEach(initPlatformPages);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAll);
        } else {
            initAll();
        }
    }());
    </script>
    <?php
}
