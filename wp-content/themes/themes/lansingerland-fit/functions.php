<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// =========================================================
// 1) Styles en fonts
// =========================================================
add_action('wp_enqueue_scripts', function () {
    $dependencies = ['parent-style'];
    if (did_action('elementor/loaded') && wp_style_is('elementor-frontend', 'registered')) {
        $dependencies[] = 'elementor-frontend';
    }

    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();
    $theme_version = wp_get_theme()->get('Version');
    $style_version = file_exists($theme_dir . '/style.css') ? filemtime($theme_dir . '/style.css') : $theme_version;
    $header_version = file_exists($theme_dir . '/css/header.css') ? filemtime($theme_dir . '/css/header.css') : $theme_version;

    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', $theme_uri . '/style.css', $dependencies, $style_version);
    wp_enqueue_style('poppins-font', 'https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap', [], null);
    wp_enqueue_style('inter-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', [], null);
    wp_enqueue_style('roboto-font', 'https://fonts.googleapis.com/css2?family=Roboto:wght@600&display=swap', [], null);
    wp_enqueue_style('work-sans-font', 'https://fonts.googleapis.com/css2?family=Work+Sans:wght@700;800&display=swap', [], null);
    wp_enqueue_style('lf-header', $theme_uri . '/css/header.css', ['child-style'], $header_version);
    wp_enqueue_style('lf-buttons', $theme_uri . '/css/buttons.css', ['child-style', 'lf-header'], filemtime($theme_dir . '/css/buttons.css'));
    wp_enqueue_style('lf-elementor-forms', $theme_uri . '/css/elementor-forms.css', ['child-style'], filemtime($theme_dir . '/css/elementor-forms.css'));

    if (file_exists($theme_dir . '/css/blog.css')) {
        wp_enqueue_style(
            'lf-blog',
            $theme_uri . '/css/blog.css',
            ['child-style', 'lf-header'],
            filemtime($theme_dir . '/css/blog.css')
        );
    }

    if (file_exists($theme_dir . '/css/hero-homepage.css')) {
        wp_enqueue_style(
            'lf-hero-homepage',
            $theme_uri . '/css/hero-homepage.css',
            ['child-style', 'lf-header', 'lf-buttons'],
            filemtime($theme_dir . '/css/hero-homepage.css')
        );
    }

    if (file_exists($theme_dir . '/css/lesrooster.css')) {
        wp_enqueue_style(
            'lf-lesrooster',
            $theme_uri . '/css/lesrooster.css',
            ['child-style', 'lesrooster-style'],
            filemtime($theme_dir . '/css/lesrooster.css')
        );
    }

    if (file_exists($theme_dir . '/css/landingspagina.css')) {
        wp_enqueue_style(
            'lf-landingspagina',
            $theme_uri . '/css/landingspagina.css',
            ['child-style'],
            filemtime($theme_dir . '/css/landingspagina.css')
        );
    }

    if (file_exists($theme_dir . '/css/trainingen.css')) {
        wp_enqueue_style(
            'lf-trainingen',
            $theme_uri . '/css/trainingen.css',
            ['child-style', 'lf-buttons'],
            filemtime($theme_dir . '/css/trainingen.css')
        );
    }

    if (is_singular('post') && file_exists($theme_dir . '/css/single.css')) {
        wp_enqueue_style(
            'lf-single',
            $theme_uri . '/css/single.css',
            ['child-style', 'lf-header'],
            filemtime($theme_dir . '/css/single.css')
        );
    }

    if (is_singular('post') && file_exists($theme_dir . '/css/text.css')) {
        wp_enqueue_style(
            'lf-text',
            $theme_uri . '/css/text.css',
            ['lf-single'],
            filemtime($theme_dir . '/css/text.css')
        );
    }
});


// =========================================================
// LF_Nav_Walker – dropdown-indicator voor navigatie
// =========================================================
if ( ! class_exists('LF_Nav_Walker') ) :
class LF_Nav_Walker extends Walker_Nav_Menu {
    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $classes   = empty( $item->classes ) ? [] : (array) $item->classes;
        $has_child = in_array( 'menu-item-has-children', $classes, true );
        $is_active = in_array( 'current-menu-item', $classes, true )
                  || in_array( 'current-menu-ancestor', $classes, true );
        $li_class  = 'rn-nav__item';
        if ( $has_child ) $li_class .= ' rn-nav__item--has-children';
        if ( $is_active ) $li_class .= ' is-active';
        $output .= '<li class="' . esc_attr( $li_class ) . '">';
        $url        = ! empty( $item->url ) ? $item->url : '#';
        $title      = apply_filters( 'the_title', $item->title, $item->ID );
        $attr_title = ! empty( $item->attr_title ) ? ' title="' . esc_attr( $item->attr_title ) . '"' : '';
        $target     = ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
        $rel        = ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : '';
        $output .= '<a class="rn-nav__link' . ( $is_active ? ' is-active' : '' ) . '"'
                 . ' href="' . esc_url( $url ) . '"'
                 . $attr_title . $target . $rel . '>';
        $output .= esc_html( $title );
        if ( $has_child ) $output .= '<span class="rn-nav__chev" aria-hidden="true"></span>';
        $output .= '</a>';
    }
    public function start_lvl( &$output, $depth = 0, $args = null ) {
        $output .= '<ul class="rn-nav__dropdown">';
    }
    public function end_lvl( &$output, $depth = 0, $args = null ) {
        $output .= '</ul>';
    }
    public function end_el( &$output, $item, $depth = 0, $args = null ) {
        $output .= '</li>';
    }
}
endif;

// =========================================================
// Nav menu locaties registreren
// =========================================================
add_action('after_setup_theme', function() {
    register_nav_menus([
        'primary_nav' => 'Primaire navigatie',
        'footer_nav'  => 'Footer navigatie',
    ]);

    add_post_type_support('page', 'excerpt');
});

// =========================================================
// Shortcode: [lf_header]
// =========================================================
add_shortcode('lf_header', function() {
    ob_start();
    include get_stylesheet_directory() . '/template-parts/header.php';
    return ob_get_clean();
});

// =========================================================
// Trainingen: pagina-markering en overzicht-shortcode
// =========================================================
add_action('add_meta_boxes_page', function() {
    add_meta_box(
        'lf_training_settings',
        'LansingerlandFit training',
        function($post) {
            wp_nonce_field('lf_save_training_setting', 'lf_training_nonce');
            $is_training = get_post_meta($post->ID, '_lf_is_training', true);
            ?>
            <label for="lf_is_training">
                <input type="checkbox" name="lf_is_training" id="lf_is_training" value="1" <?php checked($is_training, '1'); ?>>
                Toon deze pagina in het trainingenoverzicht
            </label>
            <?php
        },
        'page',
        'side',
        'default'
    );
});

add_action('save_post_page', function($post_id) {
    if (!isset($_POST['lf_training_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['lf_training_nonce'])), 'lf_save_training_setting')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_page', $post_id)) {
        return;
    }

    if (isset($_POST['lf_is_training'])) {
        update_post_meta($post_id, '_lf_is_training', '1');
    } else {
        delete_post_meta($post_id, '_lf_is_training');
    }
});

add_shortcode('trainingen_overzicht', function() {
    $training_query = new WP_Query([
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_key'       => '_lf_is_training',
        'meta_value'     => '1',
        'orderby'        => ['menu_order' => 'ASC', 'title' => 'ASC'],
        'order'          => 'ASC',
    ]);

    if (!$training_query->have_posts()) {
        return '<p class="lf-trainingen__empty">Er zijn nog geen trainingen toegevoegd.</p>';
    }

    ob_start();
    ?>
    <div class="lf-trainingen-overzicht">
        <?php while ($training_query->have_posts()) : $training_query->the_post(); ?>
            <a class="lf-training-card" href="<?php the_permalink(); ?>">
                <span class="lf-training-card__image">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('large', ['loading' => 'lazy']); ?>
                    <?php else : ?>
                        <span class="lf-training-card__placeholder" aria-hidden="true">LansingerlandFit</span>
                    <?php endif; ?>
                </span>
                <span class="lf-training-card__body">
                    <span class="lf-training-card__title"><?php the_title(); ?></span>
                    <?php if (has_excerpt()) : ?><span class="lf-training-card__excerpt"><?php echo esc_html(get_the_excerpt()); ?></span><?php endif; ?>
                    <span class="lf-training-card__link">Bekijk training <span aria-hidden="true">&rarr;</span></span>
                </span>
            </a>
        <?php endwhile; ?>
    </div>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
});


// =========================================================
// 2) Breadcrumb separator (Yoast)
// =========================================================
add_filter('wpseo_breadcrumb_separator', function($separator) {
    return ' / ';
});

function lf_render_breadcrumbs(string $nav_class = '', string $wrapper_class = ''): void {
    $nav_class_attr = $nav_class ? ' class="' . esc_attr($nav_class) . '"' : '';

    if ($wrapper_class) {
        echo '<div class="' . esc_attr($wrapper_class) . '">';
    }

    if (function_exists('yoast_breadcrumb')) {
        yoast_breadcrumb('<nav' . $nav_class_attr . ' aria-label="Breadcrumb">', '</nav>');
    } else {
        echo '<nav' . $nav_class_attr . ' aria-label="Breadcrumb">';
        echo '<a href="' . esc_url(home_url('/')) . '">Home</a>';
        echo '<span class="lf-breadcrumb-sep" aria-hidden="true"> / </span>';
        echo '<span>' . esc_html(get_the_title()) . '</span>';
        echo '</nav>';
    }

    if ($wrapper_class) {
        echo '</div>';
    }
}
