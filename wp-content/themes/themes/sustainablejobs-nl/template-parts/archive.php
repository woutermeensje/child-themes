<?php
/**
 * Blog overzichtspagina — Sustainablejobs NL child theme
 *
 * Layout:
 *  1. Twee hero-blokken — volledige breedte, sluiten aan op navigatie
 *  2. Lower: lijst (artikelen) + nieuwsbrief sidebar
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$img_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#168AAD" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="m3 9 4-4 4 4 4-4 4 4"/><circle cx="8.5" cy="14.5" r="1.5"/><path d="m21 15-5-5-5 5"/></svg>';

$zoekterm     = isset( $_GET['sj_s'] ) ? sanitize_text_field( wp_unslash( $_GET['sj_s'] ) ) : '';
$blog_page_id = get_option( 'page_for_posts' );
$blog_url     = $blog_page_id ? get_permalink( $blog_page_id ) : home_url( '/' );

$posts_all = [];
while ( have_posts() ) {
    the_post();
    $posts_all[] = get_post();
}

// Bij actieve zoekopdracht: geen hero-blokken, alle resultaten als lijstrijen.
if ( $zoekterm !== '' ) {
    $top_posts = [];
    $list_rows = $posts_all;
} else {
    $top_posts = array_slice( $posts_all, 0, 3 );
    $list_rows = array_slice( $posts_all, 3 );
}
?>

<main id="content" class="site-main sj-blog-page">

    <?php if ( empty( $posts_all ) && $zoekterm === '' ) : ?>
        <p class="sj-blog__empty">Er zijn nog geen artikelen gepubliceerd.</p>
    <?php else : ?>

        <?php

        // --------------------------------------------------------------------
        // 1. TWEE HERO-BLOKKEN — volledige breedte, sluiten aan op header
        // --------------------------------------------------------------------
        if ( ! empty( $top_posts ) ) :
        ?>
        <?php
        $hero_main = isset( $top_posts[0] ) ? $top_posts[0] : null;
        $hero_side = array_slice( $top_posts, 1, 2 );
        ?>
        <div class="sj-blog__heroes">

            <?php if ( $hero_main ) :
                $link    = get_permalink( $hero_main );
                $title   = get_the_title( $hero_main );
                $date    = get_the_date( 'd F Y', $hero_main );
                $cats    = get_the_category( $hero_main );
                $cat_lbl = $cats ? esc_html( $cats[0]->name ) : '';
                $thumb   = get_the_post_thumbnail( $hero_main, 'full' );
                $auteur  = get_post_meta( $hero_main->ID, '_sj_artikel_auteur', true );
            ?>
            <a href="<?php echo esc_url( $link ); ?>" class="sj-blog__hero sj-blog__hero--main" aria-label="<?php echo esc_attr( $title ); ?>">
                <?php if ( $thumb ) : ?>
                    <?php echo str_replace( '<img ', '<img class="sj-blog__hero-img" ', $thumb ); ?>
                <?php else : ?>
                    <div class="sj-blog__hero-placeholder"><?php echo $img_icon; ?></div>
                <?php endif; ?>
                <div class="sj-blog__hero-overlay"></div>
                <div class="sj-blog__hero-body">
                    <?php if ( $cat_lbl ) : ?>
                        <span class="sj-blog__hero-cat"><?php echo $cat_lbl; ?></span>
                    <?php endif; ?>
                    <h2 class="sj-blog__hero-title sj-blog__heading"><?php echo esc_html( $title ); ?></h2>
                    <span class="sj-blog__hero-meta">
                        <?php if ( $auteur ) : ?>
                            <span class="sj-blog__hero-auteur"><?php echo esc_html( $auteur ); ?></span>
                            <span class="sj-blog__meta-sep" aria-hidden="true">·</span>
                        <?php endif; ?>
                        <?php echo esc_html( $date ); ?>
                    </span>
                </div>
            </a>
            <?php endif; ?>

            <?php if ( ! empty( $hero_side ) ) : ?>
            <div class="sj-blog__heroes-side">
                <?php foreach ( $hero_side as $post ) :
                    $link    = get_permalink( $post );
                    $title   = get_the_title( $post );
                    $date    = get_the_date( 'd F Y', $post );
                    $cats    = get_the_category( $post );
                    $cat_lbl = $cats ? esc_html( $cats[0]->name ) : '';
                    $thumb   = get_the_post_thumbnail( $post, 'full' );
                    $auteur  = get_post_meta( $post->ID, '_sj_artikel_auteur', true );
                ?>
                <a href="<?php echo esc_url( $link ); ?>" class="sj-blog__hero sj-blog__hero--side" aria-label="<?php echo esc_attr( $title ); ?>">
                    <?php if ( $thumb ) : ?>
                        <?php echo str_replace( '<img ', '<img class="sj-blog__hero-img" ', $thumb ); ?>
                    <?php else : ?>
                        <div class="sj-blog__hero-placeholder"><?php echo $img_icon; ?></div>
                    <?php endif; ?>
                    <div class="sj-blog__hero-overlay"></div>
                    <div class="sj-blog__hero-body">
                        <?php if ( $cat_lbl ) : ?>
                            <span class="sj-blog__hero-cat"><?php echo $cat_lbl; ?></span>
                        <?php endif; ?>
                        <h2 class="sj-blog__hero-title sj-blog__heading"><?php echo esc_html( $title ); ?></h2>
                        <span class="sj-blog__hero-meta">
                            <?php if ( $auteur ) : ?>
                                <span class="sj-blog__hero-auteur"><?php echo esc_html( $auteur ); ?></span>
                                <span class="sj-blog__meta-sep" aria-hidden="true">·</span>
                            <?php endif; ?>
                            <?php echo esc_html( $date ); ?>
                        </span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
        <?php endif; ?>

        <?php
        // --------------------------------------------------------------------
        // 2. LOWER — lijst + sidebar wrapper
        // --------------------------------------------------------------------
        ?>
        <div class="sj-blog__lower">

            <div class="sj-blog__main">

                <form class="sj-blog__search" role="search" method="get" action="<?php echo esc_url( $blog_url ); ?>">
                    <div class="sj-blog__search-wrap">
                        <svg class="sj-blog__search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input
                            class="sj-blog__search-input"
                            type="search"
                            name="sj_s"
                            placeholder="Zoek in artikelen…"
                            value="<?php echo esc_attr( $zoekterm ); ?>"
                            autocomplete="off"
                            aria-label="Zoek in artikelen"
                        >
                        <?php if ( $zoekterm ) : ?>
                        <a href="<?php echo esc_url( $blog_url ); ?>" class="sj-blog__search-clear" aria-label="Zoekopdracht wissen" title="Wis zoekopdracht">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </a>
                        <?php endif; ?>
                        <button type="submit" class="sj-blog__search-btn">Zoeken</button>
                    </div>
                </form>

                <?php if ( $zoekterm !== '' && empty( $list_rows ) ) : ?>
                    <p class="sj-blog__empty">Geen artikelen gevonden voor <strong><?php echo esc_html( $zoekterm ); ?></strong>.</p>
                <?php elseif ( ! empty( $list_rows ) ) : ?>
                <?php
                    $n = count( $list_rows );
                    $list_title = $zoekterm !== ''
                        ? $n . ' ' . ( $n === 1 ? 'resultaat' : 'resultaten' ) . " voor '" . esc_html( $zoekterm ) . "'"
                        : 'Alle artikelen';
                ?>
                <h2 class="sj-blog__list-title"><?php echo $list_title; ?></h2>
                <div class="sj-blog__list">
                    <?php foreach ( $list_rows as $post ) :
                        $link    = get_permalink( $post );
                        $title   = get_the_title( $post );
                        $excerpt = get_the_excerpt( $post );
                        $date    = get_the_date( 'd F Y', $post );
                        $cats    = get_the_category( $post );
                        $cat_lbl = $cats ? esc_html( $cats[0]->name ) : '';
                        $thumb   = get_the_post_thumbnail( $post, 'thumbnail' );
                        $auteur  = get_post_meta( $post->ID, '_sj_artikel_auteur', true );
                    ?>
                    <a href="<?php echo esc_url( $link ); ?>" class="sj-blog__row">
                        <div class="sj-blog__row-img-wrap">
                            <?php if ( $thumb ) : ?>
                                <?php echo str_replace( '<img ', '<img class="sj-blog__row-img" ', $thumb ); ?>
                            <?php else : ?>
                                <div class="sj-blog__row-placeholder"><?php echo $img_icon; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="sj-blog__row-body">
                            <?php if ( $cat_lbl ) : ?>
                                <span class="sj-blog__row-cat"><?php echo $cat_lbl; ?></span>
                            <?php endif; ?>
                            <h3 class="sj-blog__row-title sj-blog__heading"><?php echo esc_html( $title ); ?></h3>
                            <?php if ( $excerpt ) : ?>
                                <p class="sj-blog__row-excerpt"><?php echo esc_html( wp_strip_all_tags( $excerpt ) ); ?></p>
                            <?php endif; ?>
                            <span class="sj-blog__row-meta">
                                <?php if ( $auteur ) : ?>
                                    <span class="sj-blog__row-auteur"><?php echo esc_html( $auteur ); ?></span>
                                    <span class="sj-blog__meta-sep" aria-hidden="true">·</span>
                                <?php endif; ?>
                                <?php echo esc_html( $date ); ?>
                            </span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php
                global $wp_query;
                if ( $wp_query->max_num_pages > 1 ) :
                    $prev_arrow = is_rtl() ? '&rarr;' : '&larr;';
                    $next_arrow = is_rtl() ? '&larr;' : '&rarr;';
                ?>
                <nav class="sj-blog__pagination" aria-label="Blogpaginering">
                    <div><?php previous_posts_link( $prev_arrow . ' Nieuwere artikelen' ); ?></div>
                    <div><?php next_posts_link( 'Oudere artikelen ' . $next_arrow ); ?></div>
                </nav>
                <?php endif; ?>

            </div><!-- .sj-blog__main -->

            <aside class="sj-blog__sidebar">

                <div class="sj-sidebar-newsletter">
                    <div class="sj-sidebar-newsletter__body">
                        <p class="sj-sidebar-newsletter__label">Blijf op de hoogte</p>
                        <h3 class="sj-sidebar-newsletter__title">Nieuws over duurzame banen direct in je inbox</h3>
                        <a href="/nieuwsbrief/" class="sj-sidebar-newsletter__btn">
                            Nieuwsbrief
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                        </a>
                    </div>
                </div>

                <div class="sj-sidebar-links">
                    <a href="/vacature-plaatsen/" class="sj-sidebar-link">
                        <span class="sj-sidebar-link__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="17"/><line x1="9.5" y1="14.5" x2="14.5" y2="14.5"/></svg>
                        </span>
                        <span class="sj-sidebar-link__text">
                            <strong>Vacature plaatsen</strong>
                            <span>Bereik duizenden duurzame professionals</span>
                        </span>
                        <svg class="sj-sidebar-link__arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                    </a>
                    <a href="https://platform.sustainablejobs.nl/aanmelden" class="sj-sidebar-link" target="_blank" rel="noopener noreferrer">
                        <span class="sj-sidebar-link__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        <span class="sj-sidebar-link__text">
                            <strong>Account aanmaken</strong>
                            <span>Sla vacatures op en stel alerts in</span>
                        </span>
                        <svg class="sj-sidebar-link__arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                    </a>
                </div>

            </aside>

        </div><!-- .sj-blog__lower -->

    <?php endif; ?>

</main>
