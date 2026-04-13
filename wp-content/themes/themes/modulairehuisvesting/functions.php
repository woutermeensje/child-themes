<?php
if (!defined('ABSPATH')) exit;

require_once get_stylesheet_directory() . '/includes/quote.php';
require_once get_stylesheet_directory() . '/includes/information-request.php';

/**
 * Enqueue styles
 */
add_action('wp_enqueue_scripts', function () {
    $dependencies = ['parent-style'];
    if (did_action('elementor/loaded') && wp_style_is('elementor-frontend', 'registered')) {
        $dependencies[] = 'elementor-frontend';
    }

    wp_enqueue_style(
        'parent-style',
        get_template_directory_uri() . '/style.css',
        [],
        filemtime(get_template_directory() . '/style.css')
    );

    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        $dependencies,
        filemtime(get_stylesheet_directory() . '/style.css')
    );

    wp_enqueue_style('poppins-font', 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap', [], null);

    if (file_exists(get_stylesheet_directory() . '/css/header.css')) {
        wp_enqueue_style(
            'mh-header',
            get_stylesheet_directory_uri() . '/css/header.css',
            ['child-style'],
            filemtime(get_stylesheet_directory() . '/css/header.css')
        );
    }

    if (file_exists(get_stylesheet_directory() . '/css/buttons.css')) {
        wp_enqueue_style(
            'mh-buttons',
            get_stylesheet_directory_uri() . '/css/buttons.css',
            ['child-style'],
            filemtime(get_stylesheet_directory() . '/css/buttons.css')
        );
    }

    if (file_exists(get_stylesheet_directory() . '/css/forms.css')) {
        wp_enqueue_style(
            'mh-forms',
            get_stylesheet_directory_uri() . '/css/forms.css',
            ['child-style'],
            filemtime(get_stylesheet_directory() . '/css/forms.css')
        );
    }

    if (file_exists(get_stylesheet_directory() . '/css/hero-homepage.css')) {
        wp_enqueue_style(
            'mh-hero-homepage',
            get_stylesheet_directory_uri() . '/css/hero-homepage.css',
            ['child-style', 'mh-header', 'mh-buttons'],
            filemtime(get_stylesheet_directory() . '/css/hero-homepage.css')
        );
    }

    if ((is_home() || is_archive()) && file_exists(get_stylesheet_directory() . '/css/blog.css')) {
        wp_enqueue_style(
            'mh-blog',
            get_stylesheet_directory_uri() . '/css/blog.css',
            ['child-style'],
            filemtime(get_stylesheet_directory() . '/css/blog.css')
        );
    }

    if (file_exists(get_stylesheet_directory() . '/js/catalog-filters.js')) {
        wp_enqueue_script(
            'mh-catalog-filters',
            get_stylesheet_directory_uri() . '/js/catalog-filters.js',
            [],
            filemtime(get_stylesheet_directory() . '/js/catalog-filters.js'),
            true
        );
    }
});

/**
 * Nav walker met dropdown-ondersteuning
 */
if (!class_exists('MH_Nav_Walker')) :
class MH_Nav_Walker extends Walker_Nav_Menu {
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes    = empty($item->classes) ? [] : (array) $item->classes;
        $has_child  = in_array('menu-item-has-children', $classes, true);
        $is_active  = in_array('current-menu-item', $classes, true) || in_array('current-menu-ancestor', $classes, true);

        $li_class = 'mh-nav__item';
        if ($has_child)  $li_class .= ' mh-nav__item--has-children';
        if ($is_active)  $li_class .= ' is-active';

        $output .= '<li class="' . esc_attr($li_class) . '">';

        $url        = !empty($item->url) ? $item->url : '#';
        $title      = apply_filters('the_title', $item->title, $item->ID);
        $attr_title = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $target     = !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $rel        = !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';

        $output .= '<a class="mh-nav__link' . ($is_active ? ' is-active' : '') . '" href="' . esc_url($url) . '"' . $attr_title . $target . $rel . '>';
        $output .= esc_html($title);
        if ($has_child) {
            $output .= '<span class="mh-nav__chev" aria-hidden="true"></span>';
        }
        $output .= '</a>';
    }

    public function start_lvl(&$output, $depth = 0, $args = null) {
        $output .= '<ul class="mh-nav__dropdown">';
    }

    public function end_lvl(&$output, $depth = 0, $args = null) {
        $output .= '</ul>';
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= '</li>';
    }
}
endif;

/**
 * Registreer navigatiemenu's en WooCommerce support
 */
add_action('after_setup_theme', function () {
    register_nav_menus([
        'primary_nav' => 'Primaire navigatie',
        'footer_nav'  => 'Footer navigatie',
    ]);
    add_theme_support('woocommerce');
    add_theme_support('custom-logo');
    add_post_type_support('page', 'excerpt');
});

add_action('init', function () {
    register_taxonomy('mh_product_unit_type', ['product'], [
        'labels' => [
            'name'              => 'Type unit',
            'singular_name'     => 'Type unit',
            'search_items'      => 'Zoek type unit',
            'all_items'         => 'Alle type units',
            'edit_item'         => 'Type unit bewerken',
            'update_item'       => 'Type unit bijwerken',
            'add_new_item'      => 'Nieuw type unit toevoegen',
            'new_item_name'     => 'Nieuwe type unit',
            'menu_name'         => 'Type unit',
        ],
        'public'            => false,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'hierarchical'      => false,
        'rewrite'           => false,
    ]);

    register_taxonomy('mh_product_purchase_type', ['product'], [
        'labels' => [
            'name'              => 'Type aanschaf',
            'singular_name'     => 'Type aanschaf',
            'search_items'      => 'Zoek type aanschaf',
            'all_items'         => 'Alle typen aanschaf',
            'edit_item'         => 'Type aanschaf bewerken',
            'update_item'       => 'Type aanschaf bijwerken',
            'add_new_item'      => 'Nieuw type aanschaf toevoegen',
            'new_item_name'     => 'Nieuwe type aanschaf',
            'menu_name'         => 'Type aanschaf',
        ],
        'public'            => false,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'hierarchical'      => false,
        'rewrite'           => false,
    ]);
}, 20);

add_action('init', function () {
    $default_terms = [
        'mh_product_unit_type' => ['Nieuw', 'Gebruikt', 'Jong gebruikt'],
        'mh_product_purchase_type' => ['Koop', 'Huur', 'Huurkoop'],
    ];

    foreach ($default_terms as $taxonomy => $terms) {
        foreach ($terms as $term_name) {
            if (!term_exists($term_name, $taxonomy)) {
                wp_insert_term($term_name, $taxonomy);
            }
        }
    }
}, 30);

add_shortcode('mh_units_grid', function ($atts = []) {
    $atts = shortcode_atts([
        'columns' => 3,
    ], $atts, 'mh_units_grid');

    $columns = max(1, min(4, (int) $atts['columns']));
    $units_dir = trailingslashit(get_stylesheet_directory()) . 'units/';
    $units_url = trailingslashit(get_stylesheet_directory_uri()) . 'units/';
    $patterns  = ['*.png', '*.jpg', '*.jpeg', '*.webp', '*.svg'];
    $files     = [];

    foreach ($patterns as $pattern) {
        $matched = glob($units_dir . $pattern);
        if (is_array($matched)) {
            $files = array_merge($files, $matched);
        }
    }

    if (empty($files)) {
        return '<div class="mh-units-gallery-empty">Er zijn nog geen units toegevoegd.</div>';
    }

    natcasesort($files);

    ob_start();
    ?>
    <div class="mh-units-gallery mh-units-gallery--cols-<?php echo esc_attr($columns); ?>">
        <?php foreach ($files as $file_path) :
            $filename   = basename($file_path);
            $basename   = pathinfo($filename, PATHINFO_FILENAME);
            $label      = trim(str_replace(['-', '_'], ' ', $basename));
            $target_url = home_url('/' . sanitize_title($basename) . '/');
            ?>
            <a class="mh-units-gallery__card" href="<?php echo esc_url($target_url); ?>">
                <span class="mh-units-gallery__media">
                    <img
                        class="mh-units-gallery__image"
                        src="<?php echo esc_url($units_url . rawurlencode($filename)); ?>"
                        alt="<?php echo esc_attr($label); ?>"
                        loading="lazy"
                    />
                </span>
                <span class="mh-units-gallery__title"><?php echo esc_html($label); ?></span>
            </a>
        <?php endforeach; ?>
    </div>
    <?php

    return ob_get_clean();
});

function mh_get_units_toggle_term_groups(): array {
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

function mh_get_units_active_view(array $atts = []): string {
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

function mh_get_units_shortcode_intro_html(WP_Query $query, string $active_view, string $search = '', array $types_selected = []): string {
    $page_title = get_the_title();
    $count      = (int) $query->found_posts;

    ob_start();
    ?>
    <div class="mh-units-catalog__intro">
        <div class="mh-units-catalog__intro-main">
            <?php mh_render_breadcrumbs('mh-units-catalog__breadcrumbs'); ?>
            <?php if ($page_title) : ?>
                <h1 class="mh-units-catalog__title"><?php echo esc_html($page_title); ?></h1>
            <?php endif; ?>
            <p class="mh-units-catalog__count">
                <?php
                printf(
                    esc_html(_n('%d resultaat', '%d resultaten', $count, 'modulairehuisvesting')),
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

if (!shortcode_exists('mh_units')) {
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
    $active_view    = mh_get_units_active_view($atts);
    $view_groups    = mh_get_units_toggle_term_groups();
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

    echo mh_get_units_shortcode_intro_html($query, $active_view, $search, $types_selected);
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
}

function mh_render_breadcrumbs(string $nav_class = '', string $wrapper_class = ''): void {
	$nav_class_attr = $nav_class ? ' class="' . esc_attr($nav_class) . '"' : '';

    if ($wrapper_class) {
        echo '<div class="' . esc_attr($wrapper_class) . '">';
    }

    if (function_exists('yoast_breadcrumb')) {
        yoast_breadcrumb('<nav' . $nav_class_attr . ' aria-label="Breadcrumb">', '</nav>');
    } else {
        echo '<nav' . $nav_class_attr . ' aria-label="Breadcrumb">';
        echo '<a href="' . esc_url(home_url('/')) . '">Home</a>';
        echo '<span class="mh-breadcrumb-sep" aria-hidden="true"> / </span>';
        echo '<span>' . esc_html(get_the_title()) . '</span>';
        echo '</nav>';
    }

	    if ($wrapper_class) {
	        echo '</div>';
	    }
	}

function mh_get_products_shortcode_intro_html(string $products_html): string {
    if (is_admin() || !is_singular()) {
        return '';
    }

    if (false === strpos($products_html, 'ul class="products') && false === strpos($products_html, "ul class='products")) {
        return '';
    }

    $product_count = preg_match_all('/<li\b[^>]*class="[^"]*\bproduct\b[^"]*"/i', $products_html, $matches);
    $product_count = is_int($product_count) ? $product_count : 0;
    $page_title    = get_the_title();

    ob_start();
    ?>
    <div class="mh-products-intro">
        <?php mh_render_breadcrumbs('mh-products-intro__breadcrumbs'); ?>
        <?php if ($page_title) : ?>
            <h1 class="mh-products-intro__title"><?php echo esc_html($page_title); ?></h1>
        <?php endif; ?>
        <p class="mh-products-intro__count">
            <?php
            printf(
                esc_html(_n('%d resultaat', '%d resultaten', $product_count, 'modulairehuisvesting')),
                (int) $product_count
            );
            ?>
        </p>
    </div>
    <?php

    return (string) ob_get_clean();
}

function mh_get_werkwijze_steps(): array {
    $email = 'informatie@modulairehuisvesting.nl';
    $phone = '085 239 20 40';

    return [
        [
            'icon'  => 'search',
            'title' => 'U zoekt een tijdelijke huisvesting',
            'body'  => sprintf(
                'U kunt via onze mail <a href="mailto:%1$s">%1$s</a> contact zoeken of per telefoon via <a href="tel:%2$s">%3$s</a>.',
                esc_html($email),
                esc_attr(preg_replace('/\s+/', '', $phone)),
                esc_html($phone)
            ),
        ],
        [
            'icon'  => 'quote',
            'title' => 'U vraagt een offerte aan',
            'body'  => sprintf(
                'Dit kan via <a href="mailto:%1$s">%1$s</a> of per telefoon via <a href="tel:%2$s">%3$s</a>. U kunt precies aangeven waar en naar hoeveel u op zoek bent.',
                esc_html($email),
                esc_attr(preg_replace('/\s+/', '', $phone)),
                esc_html($phone)
            ),
        ],
        [
            'icon'  => 'document',
            'title' => 'Wij sturen u een offerte',
            'body'  => 'Wij sturen u diezelfde dag nog een op maat gemaakte offerte per mail. Deze offerte bevat alle informatie, specificaties en kosten afgestemd op uw wens(en).',
        ],
        [
            'icon'  => 'checklist',
            'title' => 'Wij maken een order aan en stemmen die met u af',
            'body'  => 'Na uw akkoord, zullen wij uw order definitief maken en stemmen we alle laatste details met u af zoals bijvoorbeeld de planning.',
        ],
        [
            'icon'  => 'placement',
            'title' => 'Wij plaatsen de modulaire unit(s)',
            'body'  => 'Onze professionals zorgen ervoor dat de modulaire unit(s) veilig en efficient op uw locatie worden geplaatst.',
        ],
        [
            'icon'  => 'service',
            'title' => 'Uitstekende service tijdens huur van modulaire unit(s)',
            'body'  => sprintf(
                'Heeft u gedurende de huurperiode vragen of wilt u iets aanpassen of wijzigen? Neem dan contact op via <a href="mailto:%1$s">%1$s</a> of per telefoon <a href="tel:%2$s">%3$s</a> en wij zoeken naar een passende snelle oplossing.',
                esc_html($email),
                esc_attr(preg_replace('/\s+/', '', $phone)),
                esc_html($phone)
            ),
        ],
        [
            'icon'  => 'stop',
            'title' => 'Huur van modulaire unit(s) beeindigen',
            'body'  => sprintf(
                'U heeft de modulaire unit(s) niet meer langer nodig? Mail ons via <a href="mailto:%1$s">%1$s</a> of per telefoon naar <a href="tel:%2$s">%3$s</a> en wij zullen u laten weten wanneer de modulaire unit(s) kunnen worden opgehaald.',
                esc_html($email),
                esc_attr(preg_replace('/\s+/', '', $phone)),
                esc_html($phone)
            ),
        ],
        [
            'icon'  => 'pickup',
            'title' => 'Ophalen van de gehuurde modulaire unit(s)',
            'body'  => 'Op de afgesproken dag zullen wij de modulaire unit(s) ophalen. Onze professionals zorgen voor demontage en transport.',
        ],
    ];
}

function mh_get_werkwijze_icon(string $icon): string {
    $icons = [
        'search' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"></circle><path d="M16 16l5 5"></path></svg>',
        'quote' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 7.5h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2Z"></path><path d="m6 10 6 4 6-4"></path></svg>',
        'document' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3.5h6l4 4V20a1 1 0 0 1-1 1H8a2 2 0 0 1-2-2V5.5a2 2 0 0 1 2-2Z"></path><path d="M14 3.5V8h4"></path><path d="M9 12h6"></path><path d="M9 16h6"></path></svg>',
        'checklist' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6h10"></path><path d="M9 12h10"></path><path d="M9 18h10"></path><path d="m4 6 1.4 1.4L7.8 5"></path><path d="m4 12 1.4 1.4L7.8 11"></path><path d="m4 18 1.4 1.4L7.8 17"></path></svg>',
        'placement' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3.5 10.5 12 4l8.5 6.5"></path><path d="M5 10v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-9"></path><path d="M9 20v-5h6v5"></path></svg>',
        'service' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 13v-1a8 8 0 0 1 16 0v1"></path><path d="M4 13v4a2 2 0 0 0 2 2h2v-6H6a2 2 0 0 0-2 2Z"></path><path d="M20 13v4a2 2 0 0 1-2 2h-2v-6h2a2 2 0 0 1 2 2Z"></path><path d="M12 19v1.5"></path></svg>',
        'stop' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="8"></circle><path d="M9 9h6v6H9z"></path></svg>',
        'pickup' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7h11v8H3z"></path><path d="M14 10h3l3 3v2h-6z"></path><circle cx="7" cy="18" r="2"></circle><circle cx="17" cy="18" r="2"></circle></svg>',
    ];

    return $icons[$icon] ?? $icons['document'];
}

add_shortcode('mh_werkwijze', function (): string {
    $steps = mh_get_werkwijze_steps();

    ob_start();
    ?>
    <section class="mh-process" aria-labelledby="mh-process-title">
        <div class="mh-process__intro">
            <span class="mh-process__eyebrow">Onze Werkwijze</span>
            <h2 id="mh-process-title" class="mh-process__title">Van eerste zoektocht tot definitieve plaatsing</h2>
            <p class="mh-process__lead">Wij hebben hieronder een overzicht gemaakt van onze werkwijze van zoektocht naar definitieve plaatsing van een modulaire unit.</p>
        </div>

        <div class="mh-process__timeline">
            <?php foreach ($steps as $index => $step) : ?>
                <article class="mh-process__step">
                    <div class="mh-process__rail">
                        <span class="mh-process__icon" aria-hidden="true"><?php echo mh_get_werkwijze_icon($step['icon']); ?></span>
                        <span class="mh-process__line" aria-hidden="true"></span>
                    </div>
                    <div class="mh-process__content">
                        <div class="mh-process__meta">
                            <span class="mh-process__number"><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                            <span class="mh-process__label">Stap <?php echo esc_html((string) ($index + 1)); ?></span>
                        </div>
                        <h3 class="mh-process__card-title"><?php echo esc_html($step['title']); ?></h3>
                        <div class="mh-process__card-text"><?php echo wp_kses_post(wpautop($step['body'])); ?></div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php

    return (string) ob_get_clean();
});

function mh_get_catalog_filter_definitions(): array {
    return [
        'mh_product_unit_type' => [
            'label' => 'Type unit',
            'order' => ['nieuw', 'gebruikt', 'jong-gebruikt'],
        ],
        'mh_product_purchase_type' => [
            'label' => 'Type aanschaf',
            'order' => ['koop', 'huur', 'huurkoop'],
        ],
    ];
}

function mh_get_catalog_active_tax_filters(): array {
    $filters     = [];
    $definitions = mh_get_catalog_filter_definitions();

    foreach ($_GET as $key => $value) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (0 !== strpos((string) $key, 'mh_filter_')) {
            continue;
        }

        $taxonomy = substr((string) $key, strlen('mh_filter_'));
        if (!$taxonomy || !isset($definitions[$taxonomy])) {
            continue;
        }

        $raw_values = is_array($value) ? $value : explode(',', (string) $value);
        $terms      = array_values(
            array_filter(
                array_unique(
                    array_map(
                        'sanitize_title',
                        array_map('trim', array_map('wp_unslash', $raw_values))
                    )
                )
            )
        );

        if (!empty($terms)) {
            $filters[$taxonomy] = $terms;
        }
    }

    return $filters;
}

function mh_apply_catalog_filters_to_query_args(array $query_args): array {
    $active_tax_filters = mh_get_catalog_active_tax_filters();
    if (empty($active_tax_filters)) {
        return $query_args;
    }

    if (empty($query_args['tax_query']) || !is_array($query_args['tax_query'])) {
        $query_args['tax_query'] = [];
    }

    if (!isset($query_args['tax_query']['relation'])) {
        $query_args['tax_query']['relation'] = 'AND';
    }

    foreach ($active_tax_filters as $taxonomy => $terms) {
        $query_args['tax_query'][] = [
            'taxonomy' => $taxonomy,
            'field'    => 'slug',
            'terms'    => $terms,
            'operator' => 'IN',
        ];
    }

    return $query_args;
}

add_filter('woocommerce_shortcode_products_query', function ($query_args, $attributes, $type) {
    return mh_apply_catalog_filters_to_query_args((array) $query_args);
}, 10, 3);

function mh_get_catalog_filter_terms(string $taxonomy, array $definition): array {
    $terms = get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => false,
    ]);

    if (is_wp_error($terms) || empty($terms)) {
        return [];
    }

    $order_lookup = [];
    foreach (($definition['order'] ?? []) as $index => $slug) {
        $order_lookup[(string) $slug] = $index;
    }

    usort($terms, function ($a, $b) use ($order_lookup) {
        $a_index = $order_lookup[$a->slug] ?? 999;
        $b_index = $order_lookup[$b->slug] ?? 999;

        if ($a_index === $b_index) {
            return strcasecmp($a->name, $b->name);
        }

        return $a_index <=> $b_index;
    });

    return $terms;
}

function mh_get_products_shortcode_filters_html(): string {
    $definitions    = mh_get_catalog_filter_definitions();
    $active_filters = mh_get_catalog_active_tax_filters();

    ob_start();
    ?>
    <aside class="mh-catalog-sidebar" aria-label="Productfilters" style="width:100%;min-width:0;">
        <div class="mh-catalog-sidebar-inner" style="position:sticky;top:20px;background:#fff;border:1px solid #DEDEDE;border-radius:5px;padding:20px;box-sizing:border-box;">
            <?php foreach ($definitions as $taxonomy => $definition) :
                $terms = mh_get_catalog_filter_terms($taxonomy, $definition);
                if (empty($terms)) {
                    continue;
                }
                $selected = $active_filters[$taxonomy] ?? [];
                ?>
                <div class="mh-catalog-block" style="padding-bottom:16px;margin-bottom:16px;border-bottom:1px solid #EBEBEB;">
                    <h3 style="margin:0 0 10px;padding:0;color:#333;font-family:'Poppins',sans-serif;font-size:15px;font-weight:700;line-height:1.2;background:none;border:none;"><?php echo esc_html($definition['label']); ?></h3>
                    <div class="mh-select-wrap" style="position:relative;width:100%;">
                        <div class="mh-select" data-taxonomy="<?php echo esc_attr($taxonomy); ?>" style="position:relative;width:100%;">
                            <button type="button" class="mh-select-btn" aria-haspopup="listbox" aria-expanded="false" style="appearance:none;display:flex;align-items:center;justify-content:space-between;width:100%;min-height:42px;padding:9px 14px;border:1px solid #DEDEDE;border-radius:8px;background:#fff;color:#333;box-shadow:none;text-decoration:none;cursor:pointer;gap:10px;box-sizing:border-box;">
                                <span class="mh-select-label is-placeholder" style="min-width:0;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;text-align:left;color:#9A9A9A;font-family:'Poppins',sans-serif;font-size:14px;">Selecteer <?php echo esc_html(strtolower($definition['label'])); ?></span>
                                <svg class="mh-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>
                            <div class="mh-select-options" role="listbox" aria-multiselectable="true" style="display:none;position:absolute;top:calc(100% + 6px);left:0;right:0;z-index:50;max-height:280px;overflow-y:auto;padding:6px;border:1px solid #DEDEDE;border-radius:10px;background:#fff;box-shadow:0 8px 30px -4px rgba(0,0,0,0.12);box-sizing:border-box;">
                                <div class="mh-option-search-wrap" style="position:sticky;top:0;z-index:1;padding:4px 2px 8px;background:#fff;">
                                    <input type="search" class="mh-option-search" placeholder="Zoek <?php echo esc_attr(strtolower($definition['label'])); ?>..." style="appearance:none;width:100%;min-height:36px;padding:6px 10px;border:1px solid #DEDEDE;border-radius:5px;background:#f9f9f9;color:#333;font-family:'Poppins',sans-serif;font-size:13px;box-sizing:border-box;box-shadow:none;outline:none;">
                                </div>
                                <?php foreach ($terms as $term) : ?>
                                    <div
                                        class="mh-select-option<?php echo in_array($term->slug, $selected, true) ? ' is-selected' : ''; ?>"
                                        role="option"
                                        aria-selected="<?php echo in_array($term->slug, $selected, true) ? 'true' : 'false'; ?>"
                                        data-slug="<?php echo esc_attr($term->slug); ?>"
                                        data-label="<?php echo esc_attr($term->name); ?>"
                                        style="display:flex;align-items:center;padding:9px 10px;border-radius:6px;cursor:pointer;color:#333;font-family:'Poppins',sans-serif;font-size:14px;user-select:none;"
                                    >
                                        <span class="mh-select-option-text"><?php echo esc_html($term->name); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mh-selected-tags" style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;"></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="mh-catalog-block mh-catalog-block--actions" style="padding-bottom:0;margin-bottom:0;border-bottom:none;">
                <button type="button" class="mh-catalog-reset" style="appearance:none;width:100%;min-height:42px;padding:10px 14px;border:1px solid #DEDEDE;border-radius:8px;background:#fff;color:#333;font-family:'Poppins',sans-serif;font-size:14px;font-weight:600;cursor:pointer;">Filters wissen</button>
            </div>
        </div>
    </aside>
    <?php

    return (string) ob_get_clean();
}

add_filter('do_shortcode_tag', function ($output, $tag, $attr) {
    if ('products' !== $tag || !is_string($output) || '' === trim($output)) {
        return $output;
    }

    static $rendered_shortcodes = [];
    $post_id = get_the_ID() ?: 0;
    $key     = $post_id . ':' . md5(wp_json_encode($attr) ?: 'products');

    if (isset($rendered_shortcodes[$key])) {
        return $output;
    }

    $intro_html = mh_get_products_shortcode_intro_html($output);
    if ('' === $intro_html) {
        return $output;
    }

    $rendered_shortcodes[$key] = true;

    return $intro_html
        . '<div class="mh-catalog-layout" style="display:grid;grid-template-columns:minmax(240px, 280px) minmax(0, 1fr);gap:24px;align-items:start;">'
        . mh_get_products_shortcode_filters_html()
        . '<div class="mh-catalog-grid-wrap" style="min-width:0;width:100%;">'
        . $output
        . '</div></div>';
}, 10, 3);

/**
 * WooCommerce: verwijder standaard layout CSS op productpagina's
 */
add_action('wp_enqueue_scripts', function () {
    if (function_exists('is_product') && is_product()) {
        wp_dequeue_style('woocommerce-layout');
        wp_dequeue_style('woocommerce-smallscreen');
    }
}, 20);

/**
 * WooCommerce: verwijder reviews tab
 */
add_filter('woocommerce_product_tabs', function ($tabs) {
    unset($tabs['reviews']);
    return $tabs;
}, 98);

/**
 * WooCommerce: verwijder standaard catalogus-sortering
 */
add_action('wp', function () {
    if (is_admin()) return;
    remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
}, 20);
