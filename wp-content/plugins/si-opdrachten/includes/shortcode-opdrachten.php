<?php
if (!defined('ABSPATH')) exit;

add_shortcode('si_opdrachten', function ($atts) {

    $atts = shortcode_atts([
        'per_page' => 12,
        // optioneel prefilteren via shortcode: categorie="marketing,design" type="freelance,zzp"
        'categorie' => '',
        'type'      => '',
    ], $atts, 'si_opdrachten');

    // Search (GET heeft prioriteit)
    $search = isset($_GET['si_search']) ? sanitize_text_field($_GET['si_search']) : '';

    // CATEGORIE (si_categorie[])
    $cats_selected = [];
    if (isset($_GET['si_categorie'])) {
        $raw = $_GET['si_categorie'];
        $cats_selected = is_array($raw) ? $raw : [$raw];
    } elseif (!empty($atts['categorie'])) {
        $cats_selected = array_map('trim', explode(',', $atts['categorie']));
    }
    $cats_selected = array_values(array_filter(array_map('sanitize_title', $cats_selected)));

    // TYPE (si_type[])
    $types_selected = [];
    if (isset($_GET['si_type'])) {
        $raw = $_GET['si_type'];
        $types_selected = is_array($raw) ? $raw : [$raw];
    } elseif (!empty($atts['type'])) {
        $types_selected = array_map('trim', explode(',', $atts['type']));
    }
    $types_selected = array_values(array_filter(array_map('sanitize_title', $types_selected)));

    // Tax query
    $tax_query = [];

    if (!empty($cats_selected)) {
        $tax_query[] = [
            'taxonomy' => 'si_opdracht_categorie',
            'field'    => 'slug',
            'terms'    => $cats_selected,
            'operator' => 'IN',
        ];
    }

    if (!empty($types_selected)) {
        $tax_query[] = [
            'taxonomy' => 'si_opdracht_type',
            'field'    => 'slug',
            'terms'    => $types_selected,
            'operator' => 'IN',
        ];
    }

    if (count($tax_query) > 1) {
        $tax_query = array_merge([['relation' => 'AND']], $tax_query);
    }

    $query_args = [
        'post_type'      => 'si_opdracht',
        'posts_per_page' => (int) $atts['per_page'],
        's'              => $search,
    ];

    if (!empty($tax_query)) {
        $query_args['tax_query'] = $tax_query;
    }

    $query = new WP_Query($query_args);

    ob_start();

    si_opd_render_template('filter.php', [
        'search'         => $search,
        'cats_selected'  => $cats_selected,
        'types_selected' => $types_selected,
    ]);

    si_opd_render_template('loop.php', [
        'query' => $query,
    ]);

    wp_reset_postdata();

    return ob_get_clean();
});


// Shortcode: [si_latest_opdrachten limit="8" title="De laatste opdrachten in ons netwerk"]
add_shortcode('si_latest_opdrachten', function($atts){

    $atts = shortcode_atts([
        'limit' => 8,
        'title' => 'De laatste opdrachten in ons netwerk',
    ], $atts, 'si_latest_opdrachten');

    $limit = max(1, (int) $atts['limit']);
    $title = sanitize_text_field($atts['title']);

    $q = new WP_Query([
        'post_type'      => 'si_opdracht',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
        'ignore_sticky_posts' => true,
    ]);

    ob_start();
    ?>
    <section class="si-latest-strip">
        <div class="si-latest-strip__head">
            <h2 class="si-latest-strip__title"><?php echo esc_html($title); ?></h2>
        </div>

        <?php if ($q->have_posts()): ?>
            <div class="si-latest-strip__scroller" role="list">
                <?php while ($q->have_posts()): $q->the_post(); ?>
                    <?php
                    // 1 categorie (eerste term)
                    $cat_name = '';
                    $cats = get_the_terms(get_the_ID(), 'si_opdracht_categorie');
                    if (!empty($cats) && !is_wp_error($cats)) {
                        $cat_name = $cats[0]->name;
                    }

                    // excerpt: max 5 woorden
                    $ex = has_excerpt() ? get_the_excerpt() : wp_strip_all_tags(strip_shortcodes(get_the_content()));
                    $ex = wp_trim_words($ex, 5, '…');

                    // datum NL (als site NL is)
                    $date = get_the_date('j F Y');
                    ?>
                    <a class="si-latest-card" href="<?php the_permalink(); ?>" role="listitem">
                        <div class="si-latest-card__top">
                            <div class="si-latest-card__date"><?php echo esc_html($date); ?></div>

                            <?php if ($cat_name): ?>
                                <div class="si-latest-card__cat"><?php echo esc_html($cat_name); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="si-latest-card__title"><?php echo esc_html(get_the_title()); ?></div>

                        <?php if (!empty($ex)): ?>
                            <div class="si-latest-card__excerpt"><?php echo esc_html($ex); ?></div>
                        <?php endif; ?>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="si-latest-strip__empty">Er zijn nog geen opdrachten geplaatst.</div>
        <?php endif; ?>
    </section>

    <style>
        .si-latest-strip{
            max-width: 1050px;
            margin: 0 auto;
            padding: 18px 0;
        }

        .si-latest-strip__head{
            display:flex;
            align-items:center;
            justify-content: space-between;
            gap: 12px;
            margin: 0 0 12px 0;
        }

        .si-latest-strip__title{
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            color: #111827;
        }

        /* Horizontale strook */
        .si-latest-strip__scroller{
            display: flex;
            gap: 14px;
            overflow-x: auto;
            padding: 6px 2px 12px 2px;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
        }

        /* Kaartjes max 300px */
        .si-latest-card{
            flex: 0 0 300px;
            max-width: 300px;
            scroll-snap-align: start;

            display: block;
            text-decoration: none !important;
            color: inherit;

            background: #fff;
            border: 1px solid #DEDEDE;
            border-radius: 8px;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.15);
            padding: 14px 14px 12px 14px;
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .si-latest-card:hover{
            transform: translateY(-1px);
            box-shadow: 0 14px 34px -12px rgba(0,0,0,0.20);
        }

        .si-latest-card__top{
            display:flex;
            align-items:center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 10px;
        }

        .si-latest-card__date{
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            color: #6B7280;
            white-space: nowrap;
        }

        /* 1 categorie label - in jouw paarse stijl */
        .si-latest-card__cat{
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            font-weight: 700;

            color: #7C5CFA;
            border: 1px solid #7C5CFA;
            background: rgba(124, 92, 250, 0.12);
            border-radius: 6px;
            padding: 6px 10px;
            white-space: nowrap;

            background-clip: padding-box;
        }

        .si-latest-card__title{
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            line-height: 1.35;
            margin: 0 0 6px 0;

            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 40px;
        }

        .si-latest-card__excerpt{
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 400;
            color: #374151;
        }

        .si-latest-strip__empty{
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #374151;
            border: 1px dashed #D1D5DB;
            background: #F9FAFB;
            border-radius: 10px;
            padding: 14px 16px;
        }

        @media (max-width: 480px){
            .si-latest-card{
                flex-basis: 260px;
                max-width: 260px;
            }
        }
    </style>
    <?php

    wp_reset_postdata();

    return ob_get_clean();
});
