<?php
// Shortcode: [childpages]
function list_child_pages( $atts ) {
    // Eventuele toekomstige shortcode-attributen
    $atts = shortcode_atts( array(), $atts, 'childpages' );

    // Probeer altijd de pagina /organisaties/ als parent te pakken
    $organisaties_page = get_page_by_path( 'organisaties' );

    if ( $organisaties_page instanceof WP_Post ) {
        $parent_id = $organisaties_page->ID;
    } else {
        // Fallback: gebruik huidige logica als /organisaties/ niet bestaat
        global $post;

        if ( ! $post ) {
            return '';
        }

        $parent_id = $post->post_parent ? $post->post_parent : $post->ID;
    }

    // Lees filters uit de URL (?org_cat=...&org_search=...)
    $selected_cat = isset( $_GET['org_cat'] ) ? sanitize_text_field( wp_unslash( $_GET['org_cat'] ) ) : '';
    $search_query = isset( $_GET['org_search'] ) ? sanitize_text_field( wp_unslash( $_GET['org_search'] ) ) : '';

    // Haal categorieën op (taxonomy: category)
    // Vervang 'category' door je eigen taxonomy indien nodig (bijv. 'organisatie_categorie')
    $categories = get_terms( array(
        'taxonomy'   => 'category',
        'hide_empty' => true,
    ) );

    // Basis query voor child pages van /organisaties/
    $args = array(
        'post_type'      => 'page',
        'post_parent'    => $parent_id,
        'posts_per_page' => -1,
        'orderby'        => 'title',   // alfabetisch
        'order'          => 'ASC',
    );

    // Zoekfilter
    if ( ! empty( $search_query ) ) {
        $args['s'] = $search_query;
    }

    // Categorie-filter
    if ( ! empty( $selected_cat ) ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'category', // vervang dit als je een andere taxonomy gebruikt
                'field'    => 'slug',
                'terms'    => $selected_cat,
            )
        );
    }

    $query = new WP_Query( $args );

    ob_start();
    ?>

    <form method="get" class="childpages-filter">
        <div class="childpages-filter-row">
            <!-- Zoekveld -->
            <div class="childpages-filter-item">
                <label for="org_search">Zoek organisatie</label>
                <input
                    type="text"
                    id="org_search"
                    name="org_search"
                    value="<?php echo esc_attr( $search_query ); ?>"
                    placeholder="Naam van de organisatie..."
                >
            </div>

            <!-- Categorie dropdown -->
            <div class="childpages-filter-item">
                <label for="org_cat">Categorie</label>
                <select name="org_cat" id="org_cat">
                    <option value="">Alle categorieën</option>
                    <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
                        <?php foreach ( $categories as $cat ) : ?>
                            <option
                                value="<?php echo esc_attr( $cat->slug ); ?>"
                                <?php selected( $selected_cat, $cat->slug ); ?>
                            >
                                <?php echo esc_html( $cat->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Submit knop -->
            <div class="childpages-filter-item">
                <button type="submit">Zoeken</button>
            </div>
        </div>
    </form>

   <?php if ( $query->have_posts() ) : ?>
    <div class="childpages-grid">
        <?php while ( $query->have_posts() ) : $query->the_post(); ?>

            <article class="childpage-card">
                <h3 class="childpage-title">
                    <a href="<?php the_permalink(); ?>">
                        <?php the_title(); ?>
                    </a>
                </h3>

                <div class="childpage-excerpt">
                    <?php the_excerpt(); ?>
                </div>
            </article>

        <?php endwhile; ?>
    </div>
<?php else : ?>
    <p>Er zijn geen organisaties gevonden die aan je filters voldoen.</p>
<?php endif; ?>

<?php
wp_reset_postdata();
return ob_get_clean();
}

add_shortcode( 'childpages', 'list_child_pages' );

// Einde van inc/shortcodes-fondsen.php
?>
