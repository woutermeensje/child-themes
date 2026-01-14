<head>
<style>
@font-face {
    font-family: 'Balgin-Bold';
    src: url('/wp-content/themes/sustainablejobs-nl/fonts/Balgin-Bold.woff2') format('woff2'),
         url('/wp-content/themes/sustainablejobs-nl/fonts/Balgin-Bold.woff') format('woff');
    font-weight: 700;
    font-style: normal;
    font-display: swap;
}

@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

body {
    font-family: 'Poppins', sans-serif;
}
</style>


</head>

<?php
// Shortcode: [childpages]
function list_child_pages( $atts ) {
    $atts = shortcode_atts( array(), $atts, 'childpages' );

    // Probeer altijd de pagina /organisaties/ als parent te pakken
    $organisaties_page = get_page_by_path( 'organisaties' );

    if ( $organisaties_page instanceof WP_Post ) {
        $parent_id = $organisaties_page->ID;
    } else {
        global $post;
        if ( ! $post ) return '';
        $parent_id = $post->post_parent ? $post->post_parent : $post->ID;
    }

    // --- Filters uit URL ---
    $search_query = isset( $_GET['org_search'] ) ? sanitize_text_field( wp_unslash( $_GET['org_search'] ) ) : '';

    // Multiselect arrays uit URL (org_cat[]=... etc.)
    $selected_cats   = isset( $_GET['org_cat'] )   ? (array) $_GET['org_cat']   : array();
    $selected_certs  = isset( $_GET['org_cert'] )  ? (array) $_GET['org_cert']  : array();
    $selected_sector = isset( $_GET['org_sector'] )? (array) $_GET['org_sector']: array();

    // Sanitize arrays
    $selected_cats   = array_values( array_filter( array_map( 'sanitize_text_field', array_map( 'wp_unslash', $selected_cats ) ) ) );
    $selected_certs  = array_values( array_filter( array_map( 'sanitize_text_field', array_map( 'wp_unslash', $selected_certs ) ) ) );
    $selected_sector = array_values( array_filter( array_map( 'sanitize_text_field', array_map( 'wp_unslash', $selected_sector ) ) ) );

    // --- Taxonomy slugs: pas aan naar jouw site ---
    $taxonomies = array(
        'cats'   => 'category',
        'certs'  => 'certificering',
        'sector' => 'job_sector',
    );

    // Terms ophalen
    $categories = get_terms( array(
        'taxonomy'   => $taxonomies['cats'],
        'hide_empty' => true,
    ) );

    $certs = get_terms( array(
        'taxonomy'   => $taxonomies['certs'],
        'hide_empty' => true,
    ) );

    $sectors = get_terms( array(
        'taxonomy'   => $taxonomies['sector'],
        'hide_empty' => true,
    ) );

    // --- Basis query ---
    $args = array(
        'post_type'      => 'page',
        'post_parent'    => $parent_id,
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    );

    if ( ! empty( $search_query ) ) {
        $args['s'] = $search_query;
    }

    // --- tax_query bouwen (meerdere multiselects) ---
    $tax_query = array( 'relation' => 'AND' );

    if ( ! empty( $selected_cats ) ) {
        $tax_query[] = array(
            'taxonomy' => $taxonomies['cats'],
            'field'    => 'slug',
            'terms'    => $selected_cats,
            'operator' => 'IN',
        );
    }

    if ( ! empty( $selected_certs ) ) {
        $tax_query[] = array(
            'taxonomy' => $taxonomies['certs'],
            'field'    => 'slug',
            'terms'    => $selected_certs,
            'operator' => 'IN',
        );
    }

    if ( ! empty( $selected_sector ) ) {
        $tax_query[] = array(
            'taxonomy' => $taxonomies['sector'],
            'field'    => 'slug',
            'terms'    => $selected_sector,
            'operator' => 'IN',
        );
    }

    // Alleen toevoegen als er echt filters zijn
    if ( count( $tax_query ) > 1 ) {
        $args['tax_query'] = $tax_query;
    }

    $query = new WP_Query( $args );

    ob_start();
    ?>

    <form method="get" class="childpages-filter" id="childpagesFilter">
        <div class="text-filter">
            <h2 class="filter-title">Doorzoek alle organisaties</h2>
            <p class="filter-description">Of schrijf je in voor de <a href="" class="newsletter-link">nieuwsbrief van fondsen.org.</a></p>   
        </div>
        <div class="childpages-filter-row">

          <style>

            .text-filter {
                font-family: Balgin Bold !important;  
            }
            
            .filter-title {
                font-size: 24px;
                margin-bottom: 8px;
                color: #333333;
                font-family: Poppins !important; 
            }

            p.filter-description {
                color: #333333;
                text-decoration: none;
                font-weight: 400;
                font-size: 15px; 
                font-family: Poppins !important;
            }

            a.newsletter-link {
                color: #0A6B8D;
                text-decoration: none;
                font-weight: 400;
                font-size: 15px; 
                font-family: Poppins !important;
            }

             a.newsletter-link:hover {
                color: #E0D0E1;
                text-decoration: none;
                font-weight: 400;
                font-size: 15px; 
                font-family: Poppins !important;
            }


            .multiselect-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 20px;

            }

           #org_search {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512' fill='%23999999'%3E%3Cpath d='M505 442.7L405.3 343c28.4-34.9 45.7-79.4 45.7-128C451 96.5 354.5 0 233.5 0S16 96.5 16 215.5 112.5 431 233.5 431c48.6 0 93.1-17.3 128-45.7L461.3 505c9.4 9.4 24.6 9.4 33.9 0l9.8-9.8c9.4-9.4 9.4-24.6 0-33.9zM233.5 383c-92.6 0-167.5-74.9-167.5-167.5S140.9 48 233.5 48 401 122.9 401 215.5 326.1 383 233.5 383z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 12px center;
            background-size: 16px;
            padding-left: 42px;
            }

           </style>

            <!-- 1) Tekstzoekveld -->
            <div class="childpages-filter-item">
                <label for="org_search">Zoek organisatie</label>
                <input
                    type="text"
                    id="org_search"
                    name="org_search"
                    value="<?php echo esc_attr( $search_query ); ?>"
                    placeholder="Naam, onderwerp, trefwoord..."
                    autocomplete="off"
                >
            </div>


            </div>

            <div class="multiselect-row">

         

            <!-- 2) Multiselect categorie -->
            <div class="childpages-filter-item">
                <label for="org_cat">Categorieën</label>
                <select name="org_cat[]" id="org_cat" multiple>
                    <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
                        <?php foreach ( $categories as $cat ) : ?>
                            <option
                                value="<?php echo esc_attr( $cat->slug ); ?>"
                                <?php echo in_array( $cat->slug, $selected_cats, true ) ? 'selected' : ''; ?>
                            >
                                <?php echo esc_html( $cat->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- 3) Multiselect certificering -->
            <div class="childpages-filter-item">
                <label for="org_cert">Certificering</label>
                <select name="org_cert[]" id="org_cert" multiple>
                    <?php if ( ! empty( $certs ) && ! is_wp_error( $certs ) ) : ?>
                        <?php foreach ( $certs as $term ) : ?>
                            <option
                                value="<?php echo esc_attr( $term->slug ); ?>"
                                <?php echo in_array( $term->slug, $selected_certs, true ) ? 'selected' : ''; ?>
                            >
                                <?php echo esc_html( $term->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- 4) Multiselect sector -->
            <div class="childpages-filter-item">
                <label for="org_sector">Sector</label>
                <select name="org_sector[]" id="org_sector" multiple>
                    <?php if ( ! empty( $sectors ) && ! is_wp_error( $sectors ) ) : ?>
                        <?php foreach ( $sectors as $term ) : ?>
                            <option
                                value="<?php echo esc_attr( $term->slug ); ?>"
                                <?php echo in_array( $term->slug, $selected_sector, true ) ? 'selected' : ''; ?>
                            >
                                <?php echo esc_html( $term->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
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

    <script>
    (function () {
        const form = document.getElementById('childpagesFilter');
        if (!form) return;

        const searchInput = form.querySelector('#org_search');
        const selects = [
            form.querySelector('#org_cat'),
            form.querySelector('#org_cert'),
            form.querySelector('#org_sector')
        ].filter(Boolean);

        // Debounce voor typen
        let t = null;
        function debounceSubmit() {
            window.clearTimeout(t);
            t = window.setTimeout(() => {
                form.submit();
            }, 350);
        }

        // Bij typen -> auto submit
        if (searchInput) {
            searchInput.addEventListener('input', debounceSubmit);
        }

        // Bij selectie -> direct submit
        selects.forEach(sel => {
            sel.addEventListener('change', () => form.submit());
        });
    })();
    </script>

    <?php
    wp_reset_postdata();
    return ob_get_clean();
}

add_shortcode( 'childpages', 'list_child_pages' );
?>

<style>
    .childpages-filter {
    width: 1050px;
    max-width: calc(100% - 24px);
    padding: 24px;
    background: white;
    border-radius: 5px;
    border: 1px solid #E1E1E1;
    margin: 0 auto;
    box-shadow: 0 10px 40px -5px rgba(0,0,0,.15);
}

.childpages-filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem 1.5rem;
}

.childpages-filter-item {
    display: flex;
    flex-direction: column;
    min-width: 220px;
    flex: 1 1 220px;
}

.childpages-filter-item label {
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
    color: #333333;
}

.childpages-filter-item input,
.childpages-filter-item select {
    padding: 0.55rem 0.75rem;
    border-radius: 8px;
    border: 1px solid #d0d5dd;
    font-size: 0.95rem;
    outline: none;
    background-color: #ffffff;
    min-height: 44px;
}

.childpages-filter-item select[multiple] {
    min-height: 44px;
}

.childpages-filter-item input:focus,
.childpages-filter-item select:focus {
    border-color: #0884CC;
    box-shadow: 0 0 0 1px rgba(8,132,204,0.12);
}

@media (max-width: 600px) {
    .childpages-filter-row {
        flex-direction: column;
    }
}

.childpages-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.75rem;
}

@media (max-width: 900px) {
    .childpages-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 600px) {
    .childpages-grid {
        grid-template-columns: 1fr;
    }
}

.childpage-card {
    background: #ffffff;
    border-radius: 5px;
    border: 1px solid #e5e7eb;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    height: 100%;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
}

.childpage-excerpt {
    margin: 0;
    font-size: 0.95rem;
    line-height: 1.4;
    color: #4b5563;
}

</style>