<?php
if (!defined('ABSPATH')) exit;

function mh_units_plugin_get_toggle_term_groups(): array {
    $groups = [
        'new'  => [],
        'used' => [],
    ];

    $terms = get_terms([
        'taxonomy'   => 'mh_unit_conditie',
        'hide_empty' => false,
    ]);

    if (!is_wp_error($terms) && !empty($terms)) {
        foreach ($terms as $term) {
            $haystack = strtolower($term->slug . ' ' . $term->name);

            if (false !== strpos($haystack, 'nieuw')) {
                $groups['new'][] = $term->slug;
            }

            if (
                false !== strpos($haystack, 'gebruikt')
                || false !== strpos($haystack, 'used')
                || false !== strpos($haystack, 'occasion')
            ) {
                $groups['used'][] = $term->slug;
            }
        }
    }

    if (empty($groups['new'])) {
        $groups['new'] = ['nieuw'];
    }

    if (empty($groups['used'])) {
        $groups['used'] = ['gebruikt', 'jong-gebruikt'];
    }

    $groups['new']  = array_values(array_unique(array_filter(array_map('sanitize_title', $groups['new']))));
    $groups['used'] = array_values(array_unique(array_filter(array_map('sanitize_title', $groups['used']))));

    return $groups;
}

function mh_units_plugin_get_active_view(array $atts = []): string {
    $allowed = ['new', 'used'];
    $view    = isset($_GET['mh_units_state']) ? sanitize_key(wp_unslash($_GET['mh_units_state'])) : '';

    if (!$view && isset($atts['view'])) {
        $view = sanitize_key((string) $atts['view']);
    }

    if (!in_array($view, $allowed, true)) {
        $view = 'new';
    }

    return $view;
}

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

function mh_units_plugin_get_intro_html(WP_Query $query, string $active_view, string $search = '', array $types_selected = []): string {
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

        <form class="mh-units-catalog__toggle-form" method="get">
            <?php if ('' !== $search) : ?>
                <input type="hidden" name="mh_search" value="<?php echo esc_attr($search); ?>">
            <?php endif; ?>

            <?php foreach ($types_selected as $selected_type) : ?>
                <input type="hidden" name="mh_type[]" value="<?php echo esc_attr($selected_type); ?>">
            <?php endforeach; ?>

            <div class="mh-units-catalog__toggle" role="radiogroup" aria-label="Selecteer type aanbod">
                <label class="mh-units-catalog__toggle-option<?php echo 'new' === $active_view ? ' is-active' : ''; ?>">
                    <input
                        class="mh-units-catalog__toggle-input"
                        type="radio"
                        name="mh_units_state"
                        value="new"
                        <?php checked('new', $active_view); ?>
                    >
                    <span>Nieuwe units</span>
                </label>

                <label class="mh-units-catalog__toggle-option<?php echo 'used' === $active_view ? ' is-active' : ''; ?>">
                    <input
                        class="mh-units-catalog__toggle-input"
                        type="radio"
                        name="mh_units_state"
                        value="used"
                        <?php checked('used', $active_view); ?>
                    >
                    <span>Gebruikte units</span>
                </label>
            </div>
        </form>
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
        'view'     => 'new',
    ], $atts, 'mh_units');

    $search = isset($_GET['mh_search']) ? sanitize_text_field(wp_unslash($_GET['mh_search'])) : '';

    $types_selected = [];
    if (isset($_GET['mh_type'])) {
        $raw            = wp_unslash($_GET['mh_type']);
        $types_selected = is_array($raw) ? $raw : [$raw];
    } elseif (!empty($atts['type'])) {
        $types_selected = array_map('trim', explode(',', (string) $atts['type']));
    }

    $types_selected = array_values(array_filter(array_map('sanitize_title', $types_selected)));
    $active_view    = mh_units_plugin_get_active_view($atts);
    $view_groups    = mh_units_plugin_get_toggle_term_groups();
    $condities      = $view_groups[$active_view] ?? [];

    $tax_query = [];

    if (!empty($types_selected)) {
        $tax_query[] = [
            'taxonomy' => 'mh_unit_type',
            'field'    => 'slug',
            'terms'    => $types_selected,
            'operator' => 'IN',
        ];
    }

    if (!empty($condities)) {
        $tax_query[] = [
            'taxonomy' => 'mh_unit_conditie',
            'field'    => 'slug',
            'terms'    => $condities,
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

    echo mh_units_plugin_get_intro_html($query, $active_view, $search, $types_selected);
    ?>
    <div class="mh-catalog-layout mh-units-catalog-layout mh-units-catalog">
        <?php
        mh_units_render_template('filter.php', [
            'search'         => $search,
            'types_selected' => $types_selected,
            'active_view'    => $active_view,
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
