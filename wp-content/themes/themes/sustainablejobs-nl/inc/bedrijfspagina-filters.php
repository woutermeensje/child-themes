<?php
if (!defined('ABSPATH')) exit;

/**
 * ✅ Shortcode: [bedrijfspagina_filter]
 * Toont filterformulier en lijst met pagina's die gekoppeld zijn aan job_company e.d.
 */

// Enqueue scripts en styles
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], null, true);

    wp_enqueue_script(
        'bedrijfspagina-filters',
        get_stylesheet_directory_uri() . '/inc/bedrijfspagina-filters.js',
        ['jquery', 'select2-js'],
        null,
        true
    );

    wp_localize_script('bedrijfspagina-filters', 'bedrijf_filter_ajax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
    ]);
});


// Shortcode functie
function bedrijfspagina_filter_shortcode() {
    ob_start(); ?>

    <form class="company_filter-form" id="bedrijfspagina-filter-form">
        <div class="filter-text-h1">
            <h1>Doorzoek alle Duurzame Organisaties in ons netwerk</h1>
            <p>Of schrijf je in voor de <a href="https://sustainablejobs.nl/nieuwsbrief/" target="_blank">vacature nieuwsbrief</a>!</p>
        </div>

        <div class="company_filter-search">
            <div class="company_filter-keywords">
                <input type="text" name="search_keywords" id="company_filter_keywords" placeholder="Bedrijfsnaam..." />
            </div>
        </div>

        <div class="company_filter-box">
            <?php
            $taxonomies = [
                'job_company'    => '🏢 Organisatie',
                'job_sector'     => '🌱 Sector',
                'certificering'  => '🏅 Certificering',
                'job_tag'        => '📌 Tags',
            ];
            foreach ($taxonomies as $taxonomy => $label) {
                $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
                if (!empty($terms)) {
                    echo "<div class='company_filter-field company_filter-{$taxonomy}'>";
                    echo "<select name='filter_{$taxonomy}[]' id='company_filter_{$taxonomy}' class='company_filter-select company_filter-{$taxonomy}' multiple='multiple' data-placeholder='{$label}'>";
                    foreach ($terms as $term) {
                        echo "<option value='" . esc_attr($term->slug) . "'>" . esc_html($term->name) . "</option>";
                    }
                    echo "</select></div>";
                }
            }
            ?>
        </div>
    </form>



    <div id="bedrijf-resultaten"></div>

    <?php
    return ob_get_clean();
}




add_shortcode('bedrijfspagina_filter', 'bedrijfspagina_filter_shortcode');

// AJAX-handler
add_action('wp_ajax_filter_bedrijfspaginas', 'filter_bedrijfspaginas_ajax');
add_action('wp_ajax_nopriv_filter_bedrijfspaginas', 'filter_bedrijfspaginas_ajax');

function filter_bedrijfspaginas_ajax() {
    $search = sanitize_text_field($_POST['search_keywords'] ?? '');
    $tax_filters = ['job_company', 'job_sector', 'certificering', 'job_tag'];
    $tax_query = [];

    // Altijd filteren op pagina’s met een gekoppelde job_company
    $job_company_terms = get_terms([
        'taxonomy'   => 'job_company',
        'hide_empty' => false,
        'fields'     => 'slugs',
    ]);

    if (!empty($job_company_terms)) {
        $tax_query[] = [
            'taxonomy' => 'job_company',
            'field'    => 'slug',
            'terms'    => $job_company_terms,
            'operator' => 'IN',
        ];
    }

    // Voeg overige filters toe (indien aanwezig)
    foreach ($tax_filters as $tax) {
        if (!empty($_POST["filter_{$tax}"])) {
            $tax_query[] = [
                'taxonomy' => $tax,
                'field'    => 'slug',
                'terms'    => (array) $_POST["filter_{$tax}"],
            ];
        }
    }

    $args = [
        'post_type'      => 'page',
        'posts_per_page' => -1,
        's'              => $search,
    ];

    if (!empty($tax_query)) {
        $args['tax_query'] = [
            'relation' => 'AND',
            ...$tax_query
        ];
    }

    $query = new WP_Query($args);

    ob_start();
    if ($query->have_posts()) {
        echo "<div class='bedrijf-grid'>";
        while ($query->have_posts()) : $query->the_post();
            $title = get_the_title();
            $permalink = get_permalink();

            $sectors = wp_get_post_terms(get_the_ID(), 'job_sector', ['fields' => 'names']);
            $certificeringen = wp_get_post_terms(get_the_ID(), 'certificering', ['fields' => 'names']);
            $tags = wp_get_post_terms(get_the_ID(), 'job_tag', ['fields' => 'names']);

            echo "<a href='{$permalink}' class='bedrijf-item'>";
                echo "<h3 class='bedrijf-title'>{$title}</h3>";

                echo "<div class='bedrijf-taxonomies'>";
                if (!empty($sectors)) {
                    echo "<span class='bedrijf-sector'>" . implode(', ', $sectors) . "</span><br />";
                }
                if (!empty($certificeringen)) {
                    echo "<span class='bedrijf-certificering'>" . implode(', ', $certificeringen) . "</span><br />";
                }
                if (!empty($tags)) {
                    echo "<span class='bedrijf-tags'>" . implode(', ', $tags) . "</span>";
                }
                echo "</div>";
            echo "</a>";
        endwhile;
        echo "</div>";
    } else {
        echo "<p>Geen bedrijven gevonden.</p>";
    }



    wp_reset_postdata();

    echo ob_get_clean();
    wp_die();
}



