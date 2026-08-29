<?php
/**
 * Blog overzichtspagina — Studentinhuren NL child theme
 *
 * Layout:
 *  1. Twee hero-blokken — volledige breedte, sluiten aan op navigatie
 *  2. Lower: lijst (artikelen) + nieuwsbrief sidebar
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! is_home() && ! is_category() && ! is_tag() && ! is_date() && ! is_author() ) {
    require get_template_directory() . '/template-parts/archive.php';
    return;
}

$img_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#2f5f80" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="m3 9 4-4 4 4 4-4 4 4"/><circle cx="8.5" cy="14.5" r="1.5"/><path d="m21 15-5-5-5 5"/></svg>';

$zoekterm     = isset( $_GET['si_s'] ) ? sanitize_text_field( wp_unslash( $_GET['si_s'] ) ) : '';
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
    $top_posts = array_slice( $posts_all, 0, 2 );
    $list_rows = array_slice( $posts_all, 2 );
}
?>

<main id="content" class="site-main si-blog-page">

    <?php if ( empty( $posts_all ) && $zoekterm === '' ) : ?>
        <p class="si-blog__empty">Er zijn nog geen artikelen gepubliceerd.</p>
    <?php else : ?>

        <?php

        // --------------------------------------------------------------------
        // 1. TWEE HERO-BLOKKEN — volledige breedte, sluiten aan op header
        // --------------------------------------------------------------------
        if ( ! empty( $top_posts ) ) :
        ?>
        <div class="si-blog__heroes">
            <?php foreach ( $top_posts as $post ) :
                $link    = get_permalink( $post );
                $title   = get_the_title( $post );
                $date    = get_the_date( 'd F Y', $post );
                $cats    = get_the_category( $post );
                $cat     = $cats ? $cats[0] : null;
                $cat_lbl = $cat ? esc_html( $cat->name ) : '';
                $cat_style = ( $cat && function_exists( 'si_get_blog_category_style' ) ) ? si_get_blog_category_style( $cat ) : '';
                $thumb   = get_the_post_thumbnail(
                    $post,
                    'full',
                    [
                        'class' => 'si-blog__hero-img',
                        'sizes' => '(max-width: 640px) 100vw, 50vw',
                    ]
                );
                $auteur  = get_post_meta( $post->ID, '_si_artikel_auteur', true );
            ?>
            <a href="<?php echo esc_url( $link ); ?>" class="si-blog__hero" aria-label="<?php echo esc_attr( $title ); ?>"<?php echo $cat_style ? ' style="' . esc_attr( $cat_style ) . '"' : ''; ?>>
                <?php if ( $thumb ) : ?>
                    <?php echo $thumb; ?>
                <?php else : ?>
                    <div class="si-blog__hero-placeholder"><?php echo $img_icon; ?></div>
                <?php endif; ?>
                <div class="si-blog__hero-overlay"></div>
                <div class="si-blog__hero-body">
                    <div class="si-blog__hero-topline">
                        <?php if ( $cat_lbl ) : ?>
                            <span class="si-blog__hero-cat"><?php echo $cat_lbl; ?></span>
                        <?php endif; ?>
                        <span class="si-blog__hero-meta">
                            <?php if ( $auteur ) : ?>
                                <span class="si-blog__hero-auteur"><?php echo esc_html( $auteur ); ?></span>
                                <span class="si-blog__meta-sep" aria-hidden="true">·</span>
                            <?php endif; ?>
                            <?php echo esc_html( $date ); ?>
                        </span>
                    </div>
                    <h2 class="si-blog__hero-title"><span class="si-blog__title-marker"><?php echo esc_html( $title ); ?></span></h2>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php
        // --------------------------------------------------------------------
        // 2. LOWER — lijst + sidebar wrapper
        // --------------------------------------------------------------------
        ?>
        <div class="si-blog__lower">

            <div class="si-blog__main">

                <form class="si-blog__search" role="search" method="get" action="<?php echo esc_url( $blog_url ); ?>">
                    <div class="si-blog__search-wrap">
                        <svg class="si-blog__search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input
                            class="si-blog__search-input"
                            type="search"
                            name="si_s"
                            placeholder="Zoek in artikelen…"
                            value="<?php echo esc_attr( $zoekterm ); ?>"
                            autocomplete="off"
                            aria-label="Zoek in artikelen"
                        >
                        <?php if ( $zoekterm ) : ?>
                        <a href="<?php echo esc_url( $blog_url ); ?>" class="si-blog__search-clear" aria-label="Zoekopdracht wissen" title="Wis zoekopdracht">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </a>
                        <?php endif; ?>
                        <button type="submit" class="si-blog__search-btn">Zoeken</button>
                    </div>
                </form>

                <?php if ( $zoekterm !== '' && empty( $list_rows ) ) : ?>
                    <p class="si-blog__empty">Geen artikelen gevonden voor <strong><?php echo esc_html( $zoekterm ); ?></strong>.</p>
                <?php elseif ( ! empty( $list_rows ) ) : ?>
                <?php
                    $n = count( $list_rows );
                    $list_title = $zoekterm !== ''
                        ? $n . ' ' . ( $n === 1 ? 'resultaat' : 'resultaten' ) . " voor '" . esc_html( $zoekterm ) . "'"
                        : 'Alle artikelen';
                ?>
                <h2 class="si-blog__list-title"><?php echo $list_title; ?></h2>
                <div class="si-blog__list">
                    <?php foreach ( $list_rows as $post ) :
                        $link    = get_permalink( $post );
                        $title   = get_the_title( $post );
                        $excerpt = get_the_excerpt( $post );
                        $date    = get_the_date( 'd F Y', $post );
                        $cats    = get_the_category( $post );
                        $cat     = $cats ? $cats[0] : null;
                        $cat_lbl = $cat ? esc_html( $cat->name ) : '';
                        $cat_style = ( $cat && function_exists( 'si_get_blog_category_style' ) ) ? si_get_blog_category_style( $cat ) : '';
                        $thumb   = get_the_post_thumbnail(
                            $post,
                            'medium_large',
                            [
                                'class' => 'si-blog__row-img',
                                'sizes' => '(max-width: 480px) calc(100vw - 32px), (max-width: 960px) calc(100vw - 48px), 270px',
                            ]
                        );
                        $auteur  = get_post_meta( $post->ID, '_si_artikel_auteur', true );
                    ?>
                    <a href="<?php echo esc_url( $link ); ?>" class="si-blog__row"<?php echo $cat_style ? ' style="' . esc_attr( $cat_style ) . '"' : ''; ?>>
                        <div class="si-blog__row-img-wrap">
                            <?php if ( $thumb ) : ?>
                                <?php echo $thumb; ?>
                            <?php else : ?>
                                <div class="si-blog__row-placeholder"><?php echo $img_icon; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="si-blog__row-body">
                            <?php if ( $cat_lbl ) : ?>
                                <span class="si-blog__row-cat"><?php echo $cat_lbl; ?></span>
                            <?php endif; ?>
                            <h3 class="si-blog__row-title si-blog__heading"><?php echo esc_html( $title ); ?></h3>
                            <?php if ( $excerpt ) : ?>
                                <p class="si-blog__row-excerpt"><?php echo esc_html( wp_strip_all_tags( $excerpt ) ); ?></p>
                            <?php endif; ?>
                            <span class="si-blog__row-meta">
                                <?php if ( $auteur ) : ?>
                                    <span class="si-blog__row-auteur"><?php echo esc_html( $auteur ); ?></span>
                                    <span class="si-blog__meta-sep" aria-hidden="true">·</span>
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
                <nav class="si-blog__pagination" aria-label="Blogpaginering">
                    <div><?php previous_posts_link( $prev_arrow . ' Nieuwere artikelen' ); ?></div>
                    <div><?php next_posts_link( 'Oudere artikelen ' . $next_arrow ); ?></div>
                </nav>
                <?php endif; ?>

            </div><!-- .si-blog__main -->

            <aside class="si-blog__sidebar">

                <div class="si-sidebar-newsletter">
                    <div class="si-sidebar-newsletter__body">
                        <p class="si-sidebar-newsletter__label">Blijf op de hoogte</p>
                        <h3 class="si-sidebar-newsletter__title">Tips over studenten inhuren direct in je inbox</h3>
                        <a href="<?php echo esc_url( home_url( '/updates/' ) ); ?>" class="si-sidebar-newsletter__btn">
                            Updates
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                        </a>
                    </div>
                </div>

                <div class="si-sidebar-links">
                    <a href="<?php echo esc_url( home_url( '/opdracht-plaatsen/' ) ); ?>" class="si-sidebar-link">
                        <span class="si-sidebar-link__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="17"/><line x1="9.5" y1="14.5" x2="14.5" y2="14.5"/></svg>
                        </span>
                        <span class="si-sidebar-link__text">
                            <strong>Opdracht plaatsen</strong>
                            <span>Bereik studenten, starters en young professionals</span>
                        </span>
                        <svg class="si-sidebar-link__arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                    </a>
                    <a href="https://platform.student-inhuren.nl/aanmelden" class="si-sidebar-link" target="_blank" rel="noopener noreferrer">
                        <span class="si-sidebar-link__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        <span class="si-sidebar-link__text">
                            <strong>Account aanmaken</strong>
                            <span>Sla opdrachten op en stel alerts in</span>
                        </span>
                        <svg class="si-sidebar-link__arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                    </a>
                </div>

            </aside>

        </div><!-- .si-blog__lower -->

    <?php endif; ?>

</main>
