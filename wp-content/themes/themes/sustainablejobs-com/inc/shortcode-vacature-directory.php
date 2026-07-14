<?php
if (!defined('ABSPATH')) exit;

add_shortcode('sj_job_directory', 'sj_job_directory_shortcode');
add_shortcode('sj_job_categoryen', 'sj_job_directory_shortcode');

function sj_job_directory_bool($value): bool {
    return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'ja'], true);
}

function sj_job_directory_shortcode($atts = []): string {
    $atts = shortcode_atts([
        'taxonomies' => 'job_sector,job_company,organisatie_type',
        'hide_empty' => '0',
        'orderby'    => 'count',
        'order'      => 'DESC',
    ], $atts, 'sj_job_directory');

    $taxonomy_labels = [
        'job_sector'        => 'Sectors',
        'job_company'       => 'Organizations',
        'organisatie_type'  => 'Organization types',
        'job_listing_type'  => 'Employment types',
    ];

    $requested_taxonomies = array_filter(array_map('sanitize_key', explode(',', (string) $atts['taxonomies'])));
    $hide_empty           = sj_job_directory_bool($atts['hide_empty']);
    $orderby              = in_array($atts['orderby'], ['name', 'slug', 'count'], true) ? $atts['orderby'] : 'count';
    $order                = strtoupper((string) $atts['order']) === 'ASC' ? 'ASC' : 'DESC';
    $sections             = [];

    foreach ($requested_taxonomies as $taxonomy) {
        if (!taxonomy_exists($taxonomy)) {
            continue;
        }

        $terms = get_terms([
            'taxonomy'   => $taxonomy,
            'hide_empty' => $hide_empty,
            'orderby'    => $orderby,
            'order'      => $order,
        ]);

        if (is_wp_error($terms) || empty($terms)) {
            continue;
        }

        $taxonomy_object = get_taxonomy($taxonomy);
        $sections[] = [
            'taxonomy' => $taxonomy,
            'label'    => $taxonomy_labels[$taxonomy] ?? $taxonomy_object->labels->name,
            'terms'    => $terms,
        ];
    }

    if (empty($sections)) {
        return '';
    }

    $instance_id = wp_unique_id('sj_directory_');

    ob_start();
    ?>
    <div class="sj-job-directory" data-sj-directory>
        <form class="sj-directory-filter" data-sj-directory-filter>
            <div class="filter-header">
                <h2>Search all job categories</h2>
                <p>Filter by sectors, organizations and other categories on Sustainablejobs.com.</p>
            </div>

            <div class="search-basic">
                <div class="search_keywords sj-directory-search">
                    <label class="sj-directory-filter__sr" for="<?php echo esc_attr($instance_id); ?>search">Search categories</label>
                    <input
                        type="text"
                        name="sj_directory_search"
                        id="<?php echo esc_attr($instance_id); ?>search"
                        placeholder="Search sector, organization or topic..."
                        data-sj-directory-search
                    >
                </div>
            </div>

            <div class="filter-box">
                <?php foreach ($sections as $section) : ?>
                    <div class="sj-directory-category sj-directory-category--<?php echo esc_attr($section['taxonomy']); ?>">
                        <label class="sj-directory-filter__sr" for="<?php echo esc_attr($instance_id . $section['taxonomy']); ?>">
                            <?php echo esc_html($section['label']); ?>
                        </label>
                        <select
                            name="sj_directory_filter[<?php echo esc_attr($section['taxonomy']); ?>][]"
                            id="<?php echo esc_attr($instance_id . $section['taxonomy']); ?>"
                            class="sj-directory-custom-select"
                            data-placeholder="<?php echo esc_attr($section['label']); ?>"
                            data-sj-directory-term-filter="<?php echo esc_attr($section['taxonomy']); ?>"
                            multiple
                        >
                            <?php foreach ($section['terms'] as $term) : ?>
                                <option value="<?php echo esc_attr($term->slug); ?>">
                                    <?php echo esc_html($term->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="active-filters" data-sj-directory-active-filters aria-live="polite"></div>
        </form>

        <div class="sj-job-directory__results" data-sj-directory-results>
            <?php foreach ($sections as $section) : ?>
                <section
                    class="sj-job-directory__section sj-job-directory__section--<?php echo esc_attr($section['taxonomy']); ?>"
                    data-sj-directory-section
                    data-taxonomy="<?php echo esc_attr($section['taxonomy']); ?>"
                >
                    <div class="sj-job-directory__section-header">
                        <h2 class="sj-job-directory__heading"><?php echo esc_html($section['label']); ?></h2>
                        <span class="sj-job-directory__section-count" data-sj-directory-section-count>
                            <?php echo esc_html(number_format_i18n(count($section['terms']))); ?>
                        </span>
                    </div>

                    <div class="sj-job-directory__grid">
                        <?php foreach ($section['terms'] as $term) :
                            $term_url       = home_url('/jobs/' . $term->slug . '/');
                            $search_content = strtolower(wp_strip_all_tags($term->name . ' ' . $term->slug . ' ' . $section['label']));
                            $initial        = function_exists('mb_substr') ? mb_substr($term->name, 0, 1) : substr($term->name, 0, 1);
                            $initial        = strtoupper($initial);
                            ?>
                            <a
                                class="sj-job-directory__item"
                                href="<?php echo esc_url($term_url); ?>"
                                data-sj-directory-item
                                data-taxonomy="<?php echo esc_attr($section['taxonomy']); ?>"
                                data-term-slug="<?php echo esc_attr($term->slug); ?>"
                                data-search="<?php echo esc_attr($search_content); ?>"
                            >
                                <span class="sj-job-directory__badge" aria-hidden="true"><?php echo esc_html($initial); ?></span>
                                <span class="sj-job-directory__content">
                                    <span class="sj-job-directory__name"><?php echo esc_html($term->name); ?></span>
                                    <span class="sj-job-directory__meta">
                                        <?php echo esc_html($section['label']); ?>
                                        <span class="sj-job-directory__count">
                                            <?php echo esc_html(number_format_i18n((int) $term->count)); ?>
                                        </span>
                                    </span>
                                </span>
                                <span class="sj-job-directory__arrow" aria-hidden="true"></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>

            <div class="sj-job-directory__empty" data-sj-directory-empty hidden>
                <h2>No categories found.</h2>
                <p>Adjust your search or filter to see more results.</p>
            </div>
        </div>
    </div>
    <?php
    return trim(ob_get_clean());
}
