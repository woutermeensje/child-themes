<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * ✅ ENQUEUE STYLES (with Elementor check)
 */
add_action('wp_enqueue_scripts', function () {
    $dependencies = ['parent-style'];
    if (did_action('elementor/loaded') && wp_style_is('elementor-frontend', 'registered')) {
        $dependencies[] = 'elementor-frontend';
    }

    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', $dependencies, wp_get_theme()->get('Version'));
    wp_enqueue_style('poppins-font', 'https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap', [], null);
    wp_enqueue_style('inter-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', [], null);
    wp_enqueue_style('custom-fonts', get_stylesheet_directory_uri() . '/fonts/fonts.css');
    wp_enqueue_style('child-gf-styles', get_stylesheet_directory_uri() . '/css/gravity-forms.css');
});


// Load Google Fonts
function overlijdensberichten_load_fonts() {
    wp_enqueue_style(
        'overlijdensberichten-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Merriweather:ital,opsz,wght@0,18..144,300..900;1,18..144,300..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Rubik:ital,wght@0,300..900;1,300..900&display=swap',
        array(),
        null
    );
}
add_action( 'wp_enqueue_scripts', 'overlijdensberichten_load_fonts' );

// Shortcodes: overlijdensberichten + informatie-berichten

// Helper: render de grid HTML voor een WP_Query
function ob_render_overlijdens_grid( $query ) {
    ob_start();

    if ( $query->have_posts() ) : ?>
        <div class="ob-posts-grid">
            <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                <article class="ob-post-item">
                    <h2 class="ob-post-title">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_title(); ?>
                        </a>
                    </h2>

                    <div class="ob-post-excerpt">
                        <?php
                        // korte intro (zoals bij culturele-vacatures)
                        echo wp_kses_post( wp_trim_words( get_the_excerpt(), 35, '…' ) );
                        ?>
                    </div>

                    <div class="ob-post-meta">
                        <span class="ob-post-date">
                            <?php echo get_the_date( 'd-m-Y' ); ?>
                        </span>

                        <?php
                        // eventueel categorieën tonen als “/ categorie”
                        $categories = get_the_category();
                        if ( ! empty( $categories ) ) :
                            ?>
                            <span class="ob-post-separator">/</span>
                            <span class="ob-post-cats">
                                <?php
                                $cat_names = wp_list_pluck( $categories, 'name' );
                                echo esc_html( implode( ', ', $cat_names ) );
                                ?>
                            </span>
                        <?php endif; ?>

                        <span class="ob-post-meta-line"></span>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    <?php
    else :
        echo '<p>Er zijn op dit moment nog geen overlijdensberichten geplaatst.</p>';
    endif;

    wp_reset_postdata();

    return ob_get_clean();
}

/**
 * Shortcode 1:
 * [overlijdens_berichten]
 * → toont alleen berichten met categorie-slug "overlijdensberichten"
 */
add_shortcode( 'overlijdens_berichten', 'ob_overlijdens_berichten_shortcode' );

function ob_overlijdens_berichten_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'posts_per_page' => -1,
    ), $atts, 'overlijdens_berichten' );

    $query = new WP_Query( array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => intval( $atts['posts_per_page'] ),
        'orderby'        => 'date',
        'order'          => 'DESC',
        'category_name'  => 'overlijdensberichten', // ✅ gebruik de categorie-slug
    ) );

    return ob_render_overlijdens_grid( $query );
}

/**
 * Shortcode 2:
 * [overlijdens_berichten_blog]
 * → toont alleen berichten met categorie-slug "informatie"
 */
add_shortcode( 'overlijdens_berichten_blog', 'ob_overlijdens_berichten_blog_shortcode' );

function ob_overlijdens_berichten_blog_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'posts_per_page' => -1,
    ), $atts, 'overlijdens_berichten_blog' );

    $query = new WP_Query( array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => intval( $atts['posts_per_page'] ),
        'orderby'        => 'date',
        'order'          => 'DESC',
        'category_name'  => 'informatie', // ✅ gebruik de categorie-slug
    ) );

    return ob_render_overlijdens_grid( $query );
}


/**
 * Regio-taxonomie toevoegen aan berichten (post)
 */
function ob_register_regio_taxonomy() {

    $labels = array(
        'name'              => 'Regio',
        'singular_name'     => 'Regio',
        'search_items'      => 'Regio zoeken',
        'all_items'         => 'Alle regio’s',
        'parent_item'       => 'Bovenliggende regio',
        'parent_item_colon' => 'Bovenliggende regio:',
        'edit_item'         => 'Regio bewerken',
        'update_item'       => 'Regio bijwerken',
        'add_new_item'      => 'Nieuwe regio toevoegen',
        'new_item_name'     => 'Naam nieuwe regio',
        'menu_name'         => 'Regio',
    );

    $args = array(
        'hierarchical'      => true, // werkt zoals categorieën (niet zoals tags)
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true, // toont kolom in WP admin
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'regio' ),
        'show_in_rest'      => true, // ondersteunt Gutenberg + API
    );

    register_taxonomy( 'regio', array( 'post' ), $args );
}
add_action( 'init', 'ob_register_regio_taxonomy' );
