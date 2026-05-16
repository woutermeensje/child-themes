<?php
if (!defined('ABSPATH')) exit;

add_shortcode('sj_vacature_directory', 'sj_vacature_directory_shortcode');
add_shortcode('sj_vacature_categorieen', 'sj_vacature_directory_shortcode');

function sj_vacature_directory_bool($value): bool {
    return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'ja'], true);
}

function sj_vacature_directory_shortcode($atts = []): string {
    $atts = shortcode_atts([
        'taxonomies' => 'job_sector,job_company,organisatie_type',
        'hide_empty' => '0',
        'orderby'    => 'count',
        'order'      => 'DESC',
    ], $atts, 'sj_vacature_directory');

    $taxonomy_labels = [
        'job_sector'        => 'Sectoren',
        'job_company'       => 'Organisaties',
        'organisatie_type'  => 'Type organisaties',
        'job_listing_type'  => 'Dienstverbanden',
    ];

    $requested_taxonomies = array_filter(array_map('sanitize_key', explode(',', (string) $atts['taxonomies'])));
    $hide_empty           = sj_vacature_directory_bool($atts['hide_empty']);
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
    <div class="sj-vacature-directory" data-sj-directory>
        <form class="sj-directory-filter" data-sj-directory-filter>
            <div class="filter-header">
                <h2>Zoek in alle vacaturecategorieën</h2>
                <p>Filter op sectoren, organisaties en andere categorieën binnen Sustainablejobs.nl.</p>
            </div>

            <div class="search-basic">
                <div class="search_keywords sj-directory-search">
                    <label class="sj-directory-filter__sr" for="<?php echo esc_attr($instance_id); ?>search">Zoeken in categorieën</label>
                    <input
                        type="text"
                        name="sj_directory_search"
                        id="<?php echo esc_attr($instance_id); ?>search"
                        placeholder="Zoek sector, organisatie of onderwerp.."
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

        <div class="sj-vacature-directory__results" data-sj-directory-results>
            <?php foreach ($sections as $section) : ?>
                <section
                    class="sj-vacature-directory__section sj-vacature-directory__section--<?php echo esc_attr($section['taxonomy']); ?>"
                    data-sj-directory-section
                    data-taxonomy="<?php echo esc_attr($section['taxonomy']); ?>"
                >
                    <div class="sj-vacature-directory__section-header">
                        <h2 class="sj-vacature-directory__heading"><?php echo esc_html($section['label']); ?></h2>
                        <span class="sj-vacature-directory__section-count" data-sj-directory-section-count>
                            <?php echo esc_html(number_format_i18n(count($section['terms']))); ?>
                        </span>
                    </div>

                    <div class="sj-vacature-directory__grid">
                        <?php foreach ($section['terms'] as $term) :
                            $term_url       = home_url('/vacatures/' . $term->slug . '/');
                            $search_content = strtolower(wp_strip_all_tags($term->name . ' ' . $term->slug . ' ' . $section['label']));
                            $initial        = function_exists('mb_substr') ? mb_substr($term->name, 0, 1) : substr($term->name, 0, 1);
                            $initial        = strtoupper($initial);
                            ?>
                            <a
                                class="sj-vacature-directory__item"
                                href="<?php echo esc_url($term_url); ?>"
                                data-sj-directory-item
                                data-taxonomy="<?php echo esc_attr($section['taxonomy']); ?>"
                                data-term-slug="<?php echo esc_attr($term->slug); ?>"
                                data-search="<?php echo esc_attr($search_content); ?>"
                            >
                                <span class="sj-vacature-directory__badge" aria-hidden="true"><?php echo esc_html($initial); ?></span>
                                <span class="sj-vacature-directory__content">
                                    <span class="sj-vacature-directory__name"><?php echo esc_html($term->name); ?></span>
                                    <span class="sj-vacature-directory__meta">
                                        <?php echo esc_html($section['label']); ?>
                                        <span class="sj-vacature-directory__count">
                                            <?php echo esc_html(number_format_i18n((int) $term->count)); ?>
                                        </span>
                                    </span>
                                </span>
                                <span class="sj-vacature-directory__arrow" aria-hidden="true"></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>

            <div class="sj-vacature-directory__empty" data-sj-directory-empty hidden>
                <h2>Geen categorieën gevonden.</h2>
                <p>Pas je zoekopdracht of filter aan om meer resultaten te zien.</p>
            </div>
        </div>
    </div>
    <?php
    return trim(ob_get_clean());
}
