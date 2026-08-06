<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [opstellen_overzicht]
 * Toont alle gepubliceerde pagina's onder /opstellen/ als blokken.
 */
add_shortcode('opstellen_overzicht', 'ob_opstellen_overzicht_shortcode');
add_shortcode('ob_opstellen_overzicht', 'ob_opstellen_overzicht_shortcode');

function ob_opstellen_overzicht_shortcode($atts): string {
    $atts = shortcode_atts([
        'base_path' => 'opstellen',
    ], $atts, 'opstellen_overzicht');

    $base_path = trim(sanitize_text_field((string) $atts['base_path']), '/');
    if ($base_path === '') {
        return '';
    }

    $items = ob_opstellen_overzicht_get_pages($base_path);
    if (empty($items)) {
        return '';
    }

    ob_start();
    ?>
    <div class="ob-opstellen-directory">
        <div class="ob-opstellen-directory__results">
            <div class="ob-opstellen-directory__grid">
                <?php foreach ($items as $item): ?>
                    <a
                        class="ob-opstellen-directory__item"
                        href="<?php echo esc_url($item['url']); ?>"
                    >
                        <span class="ob-opstellen-directory__name"><?php echo esc_html($item['title']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php ob_opstellen_overzicht_print_assets(); ?>
    <?php
    return trim(ob_get_clean());
}

function ob_opstellen_overzicht_get_pages(string $base_path): array {
    $parent = get_page_by_path($base_path, OBJECT, 'page');
    $pages  = [];

    if ($parent instanceof WP_Post && get_post_status($parent) === 'publish') {
        $pages = get_pages([
            'post_type'   => 'page',
            'post_status' => 'publish',
            'child_of'    => (int) $parent->ID,
            'sort_column' => 'post_title',
            'sort_order'  => 'ASC',
        ]);
    } else {
        $fallback_query = new WP_Query([
            'post_type'              => 'page',
            'post_status'            => 'publish',
            'posts_per_page'         => -1,
            'orderby'                => 'title',
            'order'                  => 'ASC',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]);

        if ($fallback_query->have_posts()) {
            while ($fallback_query->have_posts()) {
                $fallback_query->the_post();
                $pages[] = get_post();
            }
            wp_reset_postdata();
        }
    }

    $items = [];
    foreach ($pages as $page) {
        if (!$page instanceof WP_Post || $page->post_status !== 'publish') {
            continue;
        }

        $path = trim((string) wp_parse_url(get_permalink($page), PHP_URL_PATH), '/');
        if (strpos($path, $base_path . '/') !== 0) {
            continue;
        }

        $title   = get_the_title($page);
        $items[] = [
            'title' => $title,
            'url'   => get_permalink($page),
        ];
    }

    usort($items, function ($a, $b) {
        return strcasecmp($a['title'], $b['title']);
    });

    return $items;
}

function ob_opstellen_overzicht_print_assets(): void {
    static $printed = false;
    if ($printed) {
        return;
    }
    $printed = true;
    ?>
    <style>
    .ob-opstellen-directory,
    .ob-opstellen-directory *,
    .ob-opstellen-directory *::before,
    .ob-opstellen-directory *::after { box-sizing: border-box; }

    .ob-opstellen-directory {
        width: 100%;
        font-family: 'Poppins', sans-serif;
    }

    .ob-opstellen-directory__results {
        max-width: 1200px;
        margin-left: auto !important;
        margin-right: auto !important;
    }

    .ob-opstellen-directory__results {
        padding: 0 24px 64px;
    }

    .ob-opstellen-directory__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 14px;
    }

    .ob-opstellen-directory__item {
        display: flex;
        align-items: center;
        min-height: 86px;
        padding: 18px;
        background: #ffffff;
        border: 1px solid #DEDEDE;
        border-radius: 6px;
        color: var(--color-text, #333333) !important;
        text-decoration: none !important;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .ob-opstellen-directory__item:hover,
    .ob-opstellen-directory__item:focus {
        border-color: var(--color-primary, #7C5CFA);
        box-shadow: 0 10px 28px rgba(54, 42, 98, .08);
        transform: translateY(-1px);
        outline: none;
    }

    .ob-opstellen-directory__name {
        font-family: 'Inter', sans-serif;
        font-size: 16px;
        font-weight: 700;
        line-height: 1.3;
        color: var(--color-text, #333333);
        overflow-wrap: anywhere;
    }

    @media (max-width: 768px) {
        .ob-opstellen-directory__results {
            padding-left: 16px !important;
            padding-right: 16px !important;
        }

        .ob-opstellen-directory__grid {
            grid-template-columns: 1fr;
        }

        .ob-opstellen-directory__item {
            min-height: 78px;
            padding: 15px;
        }
    }
    </style>
    <?php
}
