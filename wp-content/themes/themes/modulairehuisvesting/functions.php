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
