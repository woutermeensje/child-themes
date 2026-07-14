<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [companyspagina_filter]
 * Shows a filter form and list of pages linked to job_company and related taxonomies.
 */

// Enqueue scripts and styles.
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], null, true);

    wp_enqueue_script(
        'companyspagina-filters',
        get_stylesheet_directory_uri() . '/inc/bedrijfspagina-filters.js',
        ['jquery', 'select2-js'],
        null,
        true
    );

    wp_localize_script('companyspagina-filters', 'company_filter_ajax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
    ]);
});


// Shortcode function.
function companyspagina_filter_shortcode() {
    ob_start(); ?>

    <form class="company_filter-form" id="companyspagina-filter-form">
        <div class="filter-text-h1">
            <h1>Browse all sustainable organizations in our network</h1>
            <p>Or sign up for the <a href="https://sustainablejobs.com/newsletter/" target="_blank">job newsletter</a>!</p>
        </div>

        <div class="company_filter-search">
            <div class="company_filter-keywords">
                <input type="text" name="search_keywords" id="company_filter_keywords" placeholder="Company name..." />
            </div>
        </div>

        <div class="company_filter-box">
            <?php
            $taxonomies = [
                'job_company'    => 'Organization',
                'job_sector'     => '🌱 Sector',
                'organisatie_type' => 'Organization type',
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



    <div id="company-resultaten"></div>

    <?php
    return ob_get_clean();
}




add_shortcode('companyspagina_filter', 'companyspagina_filter_shortcode');
add_shortcode('company_page_filter', 'companyspagina_filter_shortcode');

// AJAX handler.
add_action('wp_ajax_filter_companyspaginas', 'filter_companyspaginas_ajax');
add_action('wp_ajax_nopriv_filter_companyspaginas', 'filter_companyspaginas_ajax');

function filter_companyspaginas_ajax() {
    $search = sanitize_text_field($_POST['search_keywords'] ?? '');
    $tax_filters = ['job_company', 'job_sector', 'organisatie_type', 'job_tag'];
    $tax_query = [];

    // Always filter on pages with a linked job_company.
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

    // Add other filters if present.
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
        echo "<div class='company-grid'>";
        while ($query->have_posts()) : $query->the_post();
            $title = get_the_title();
            $permalink = get_permalink();

            $sectors = wp_get_post_terms(get_the_ID(), 'job_sector', ['fields' => 'names']);
            $organisatie_types = wp_get_post_terms(get_the_ID(), 'organisatie_type', ['fields' => 'names']);
            $tags = wp_get_post_terms(get_the_ID(), 'job_tag', ['fields' => 'names']);

            echo "<a href='{$permalink}' class='company-item'>";
                echo "<h3 class='company-title'>{$title}</h3>";

                echo "<div class='company-taxonomies'>";
                if (!empty($sectors)) {
                    echo "<span class='company-sector'>" . implode(', ', $sectors) . "</span><br />";
                }
                if (!empty($organisatie_types)) {
                    echo "<span class='company-organisatie-type'>" . implode(', ', $organisatie_types) . "</span><br />";
                }
                if (!empty($tags)) {
                    echo "<span class='company-tags'>" . implode(', ', $tags) . "</span>";
                }
                echo "</div>";
            echo "</a>";
        endwhile;
        echo "</div>";
    } else {
        echo "<p>No organizations found.</p>";
    }



    wp_reset_postdata();

    echo ob_get_clean();
    wp_die();
}


