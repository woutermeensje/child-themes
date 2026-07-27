<?php
if (!defined('ABSPATH')) exit;

function mh_units_plugin_render_breadcrumbs(string $nav_class = ''): string {
    $nav_class_attr = $nav_class ? ' class="' . esc_attr($nav_class) . '"' : '';

    ob_start();

    if (function_exists('yoast_breadcrumb')) {
        yoast_breadcrumb('<nav' . $nav_class_attr . ' aria-label="Breadcrumb">', '</nav>');
    } else {
        echo '<nav' . $nav_class_attr . ' aria-label="Breadcrumb">';
        echo '<a href="' . esc_url(home_url('/')) . '">Home</a>';
        echo '<span class="mh-breadcrumb-sep" aria-hidden="true"> / </span>';
        echo '<span>' . esc_html(get_the_title()) . '</span>';
        echo '</nav>';
    }

    return (string) ob_get_clean();
}

function mh_units_plugin_get_intro_html(WP_Query $query): string {
    $page_title = get_the_title();
    $count      = (int) $query->found_posts;

    ob_start();
    ?>
    <div class="mh-units-catalog__intro">
        <div class="mh-units-catalog__intro-main">
            <?php echo mh_units_plugin_render_breadcrumbs('mh-units-catalog__breadcrumbs mh-units-shortcode__breadcrumbs'); ?>
            <?php if ($page_title) : ?>
                <h1 class="mh-units-catalog__title"><?php echo esc_html($page_title); ?></h1>
            <?php endif; ?>
            <p class="mh-units-catalog__count">
                <?php
                printf(
                    esc_html(_n('%d resultaat', '%d resultaten', $count, 'mh-units')),
                    $count
                );
                ?>
            </p>
        </div>
    </div>
    <?php

    return (string) ob_get_clean();
}

add_shortcode('mh_units', function ($atts) {
    if (!function_exists('mh_units_render_template')) {
        return '';
    }

    $atts = shortcode_atts([
        'per_page' => 12,
        'type'     => '',
    ], $atts, 'mh_units');

    $search             = isset($_GET['mh_search']) ? sanitize_text_field(wp_unslash($_GET['mh_search'])) : '';
    $has_filter_request = isset($_GET['mh_units_filter']) || isset($_GET['mh_search']) || isset($_GET['mh_type']);

    $types_selected = [];
    if (isset($_GET['mh_type'])) {
        $raw            = wp_unslash($_GET['mh_type']);
        $types_selected = is_array($raw) ? $raw : [$raw];
    } elseif (!$has_filter_request && !empty($atts['type'])) {
        $types_selected = array_map('trim', explode(',', (string) $atts['type']));
    }

    $types_selected = array_values(array_filter(array_map('sanitize_title', $types_selected)));

    $tax_query = [];

    if (!empty($types_selected)) {
        $tax_query[] = [
            'taxonomy' => 'mh_unit_type',
            'field'    => 'slug',
            'terms'    => $types_selected,
            'operator' => 'IN',
        ];
    }

    if (count($tax_query) > 1) {
        $tax_query = array_merge([['relation' => 'AND']], $tax_query);
    }

    $query_args = [
        'post_type'      => 'mh_unit',
        'posts_per_page' => (int) $atts['per_page'],
        's'              => $search,
    ];

    if (!empty($tax_query)) {
        $query_args['tax_query'] = $tax_query;
    }

    $query = new WP_Query($query_args);

    ob_start();

    echo mh_units_plugin_get_intro_html($query);
    ?>
    <div class="mh-catalog-layout mh-units-catalog-layout mh-units-catalog">
        <?php
        mh_units_render_template('filter.php', [
            'search'         => $search,
            'types_selected' => $types_selected,
        ]);
        ?>

        <div class="mh-catalog-grid-wrap mh-units-catalog-grid-wrap">
            <?php mh_units_render_template('loop.php', ['query' => $query]); ?>
        </div>
    </div>
    <?php

    wp_reset_postdata();

    return ob_get_clean();
});
