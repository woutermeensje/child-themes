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

    if (file_exists(get_stylesheet_directory() . '/css/mh-units.css')) {
        wp_enqueue_style(
            'mh-units-theme',
            get_stylesheet_directory_uri() . '/css/mh-units.css',
            ['child-style'],
            filemtime(get_stylesheet_directory() . '/css/mh-units.css')
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
 * WP Job Manager: load templates from wp-job-manager/ folder
 */
add_filter('job_manager_locate_template', function ($template, $template_name) {
    $custom_templates = [
        'content-job_listing.php',
        'content-single-job_listing.php',
        'job-filters.php',
        'job-filter-job-types.php',
        'job-listings-start.php',
        'job-listings-end.php',
    ];
    $custom_path = get_stylesheet_directory() . '/wp-job-manager/' . $template_name;
    return ( in_array($template_name, $custom_templates, true) && file_exists($custom_path) )
        ? $custom_path
        : $template;
}, 10, 2);

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
 * Registreer navigatiemenu's
 */
add_action('after_setup_theme', function () {
    register_nav_menus([
        'primary_nav' => 'Primaire navigatie',
        'footer_nav'  => 'Footer navigatie',
    ]);
    add_theme_support('custom-logo');
    add_post_type_support('page', 'excerpt');
    add_post_type_support('job_listing', 'thumbnail');
});

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

/**
 * Gedeelde render-functie voor de units-kaartgrids.
 */
function mh_render_units_grid(string $parent_slug, int $columns): string {
    $parent_page = get_page_by_path($parent_slug);
    if (!$parent_page) return '<p>Pagina &ldquo;' . esc_html($parent_slug) . '&rdquo; niet gevonden.</p>';

    $pages = get_posts([
        'post_type'      => 'page',
        'post_parent'    => $parent_page->ID,
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
    ]);

    if (empty($pages)) return '';

    ob_start();
    ?>
    <div class="muo-grid muo-cols-<?php echo esc_attr($columns); ?>">
        <?php foreach ($pages as $page) :
            $img_url = get_the_post_thumbnail_url($page->ID, 'large');
            $link    = get_permalink($page->ID);
            $title   = get_the_title($page->ID);
            $style   = $img_url
                ? 'style="background-image: url(\'' . esc_url($img_url) . '\')"'
                : '';
        ?>
        <a href="<?php echo esc_url($link); ?>" class="muo-card<?php echo $img_url ? '' : ' muo-card--no-image'; ?>" <?php echo $style; ?>>
            <div class="muo-card__overlay"></div>
            <div class="muo-card__body">
                <h3 class="muo-card__title"><?php echo esc_html($title); ?></h3>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <style>
    .muo-grid {
        display: grid;
        gap: 16px;
        width: 100%;
    }
    .muo-cols-1 { grid-template-columns: repeat(1, 1fr); }
    .muo-cols-2 { grid-template-columns: repeat(2, 1fr); }
    .muo-cols-3 { grid-template-columns: repeat(3, 1fr); }
    .muo-cols-4 { grid-template-columns: repeat(4, 1fr); }
    .muo-cols-5 { grid-template-columns: repeat(5, 1fr); }
    .muo-cols-6 { grid-template-columns: repeat(6, 1fr); }

    .muo-card {
        position: relative;
        display: block;
        aspect-ratio: 4 / 3;
        border-radius: 12px;
        overflow: hidden;
        background-color: #25476B;
        background-size: cover;
        background-position: center;
        text-decoration: none !important;
        transition: transform .25s ease, box-shadow .25s ease;
    }

    .muo-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 40px rgba(37, 71, 107, 0.28);
    }

    .muo-card:hover .muo-card__overlay {
        opacity: 0.85;
    }

    .muo-card__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            to bottom,
            rgba(37, 71, 107, 0.02) 0%,
            rgba(37, 71, 107, 0.18) 40%,
            rgba(37, 71, 107, 0.80) 100%
        );
        opacity: 0.75;
        transition: opacity .25s ease;
    }

    .muo-card--no-image .muo-card__overlay {
        background: linear-gradient(135deg, rgba(57, 116, 155, 0.90), rgba(37, 71, 107, 0.98));
    }

    .muo-card__body {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 20px 20px 22px;
        z-index: 1;
    }

    .muo-card__title {
        margin: 0 !important;
        font-family: 'Poppins', sans-serif !important;
        font-size: 18px !important;
        font-weight: 700 !important;
        line-height: 1.25 !important;
        color: #ffffff !important;
        text-shadow: 0 1px 4px rgba(0, 0, 0, 0.4) !important;
    }

    @media (max-width: 960px) {
        .muo-cols-4,
        .muo-cols-3 { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 540px) {
        .muo-cols-4,
        .muo-cols-3,
        .muo-cols-2 { grid-template-columns: repeat(1, 1fr); }
        .muo-card { aspect-ratio: 16 / 9; }
    }
    </style>
    <?php
    return (string) ob_get_clean();
}

/**
 * ✅ SHORTCODE: [modulaire_units_kopen_overzicht]
 */
add_shortcode('modulaire_units_kopen_overzicht', function ($atts) {
    $atts = shortcode_atts(['columns' => 3], $atts, 'modulaire_units_kopen_overzicht');
    return mh_render_units_grid('kopen', max(1, min(6, intval($atts['columns']))));
});

/**
 * ✅ SHORTCODE: [modulaire_units_huren_overzicht]
 */
add_shortcode('modulaire_units_huren_overzicht', function ($atts) {
    $atts = shortcode_atts(['columns' => 3], $atts, 'modulaire_units_huren_overzicht');
    return mh_render_units_grid('huren', max(1, min(6, intval($atts['columns']))));
});
