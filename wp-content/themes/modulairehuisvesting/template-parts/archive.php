<?php
/**
 * Blog overzichtspagina — Modulaire Huisvesting child theme
 *
 * Layout:
 *  1. Grote hero links + twee gestapelde hero's rechts
 *  2. Lower: artikellijst + compacte sidebar
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$img_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#25476B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="m3 9 4-4 4 4 4-4 4 4"/><circle cx="8.5" cy="14.5" r="1.5"/><path d="m21 15-5-5-5 5"/></svg>';

$posts_all = [];
while ( have_posts() ) {
    the_post();
    $posts_all[] = get_post();
}
?>

<main id="content" class="site-main mh-blog-page">

    <?php if ( empty( $posts_all ) ) : ?>
        <p class="mh-blog__empty">Er zijn nog geen artikelen gepubliceerd.</p>
    <?php else : ?>

        <?php
        $top_posts = array_slice( $posts_all, 0, 3 );
        $list_rows = array_slice( $posts_all, 3 );
        $hero_main = isset( $top_posts[0] ) ? $top_posts[0] : null;
        $hero_side = array_slice( $top_posts, 1, 2 );
        ?>

        <?php if ( ! empty( $top_posts ) ) : ?>
        <div class="mh-blog__heroes">

            <?php if ( $hero_main ) :
                $link    = get_permalink( $hero_main );
                $title   = get_the_title( $hero_main );
                $date    = get_the_date( 'd F Y', $hero_main );
                $cats    = get_the_category( $hero_main );
                $cat_lbl = $cats ? esc_html( $cats[0]->name ) : '';
                $thumb   = get_the_post_thumbnail( $hero_main, 'full' );
                $author  = get_the_author_meta( 'display_name', $hero_main->post_author );
            ?>
            <a href="<?php echo esc_url( $link ); ?>" class="mh-blog__hero mh-blog__hero--main" aria-label="<?php echo esc_attr( $title ); ?>">
                <?php if ( $thumb ) : ?>
                    <?php echo str_replace( '<img ', '<img class="mh-blog__hero-img" ', $thumb ); ?>
                <?php else : ?>
                    <div class="mh-blog__hero-placeholder"><?php echo $img_icon; ?></div>
                <?php endif; ?>
                <div class="mh-blog__hero-overlay"></div>
                <div class="mh-blog__hero-body">
                    <?php if ( $cat_lbl ) : ?>
                        <span class="mh-blog__hero-cat"><?php echo $cat_lbl; ?></span>
                    <?php endif; ?>
                    <h2 class="mh-blog__hero-title mh-blog__heading"><?php echo esc_html( $title ); ?></h2>
                    <span class="mh-blog__hero-meta">
                        <?php if ( $author ) : ?>
                            <span class="mh-blog__hero-author"><?php echo esc_html( $author ); ?></span>
                            <span class="mh-blog__meta-sep" aria-hidden="true">·</span>
                        <?php endif; ?>
                        <?php echo esc_html( $date ); ?>
                    </span>
                </div>
            </a>
            <?php endif; ?>

            <?php if ( ! empty( $hero_side ) ) : ?>
            <div class="mh-blog__heroes-side">
                <?php foreach ( $hero_side as $post ) :
                    $link    = get_permalink( $post );
                    $title   = get_the_title( $post );
                    $date    = get_the_date( 'd F Y', $post );
                    $cats    = get_the_category( $post );
                    $cat_lbl = $cats ? esc_html( $cats[0]->name ) : '';
                    $thumb   = get_the_post_thumbnail( $post, 'full' );
                    $author  = get_the_author_meta( 'display_name', $post->post_author );
                ?>
                <a href="<?php echo esc_url( $link ); ?>" class="mh-blog__hero mh-blog__hero--side" aria-label="<?php echo esc_attr( $title ); ?>">
                    <?php if ( $thumb ) : ?>
                        <?php echo str_replace( '<img ', '<img class="mh-blog__hero-img" ', $thumb ); ?>
                    <?php else : ?>
                        <div class="mh-blog__hero-placeholder"><?php echo $img_icon; ?></div>
                    <?php endif; ?>
                    <div class="mh-blog__hero-overlay"></div>
                    <div class="mh-blog__hero-body">
                        <?php if ( $cat_lbl ) : ?>
                            <span class="mh-blog__hero-cat"><?php echo $cat_lbl; ?></span>
                        <?php endif; ?>
                        <h2 class="mh-blog__hero-title mh-blog__heading"><?php echo esc_html( $title ); ?></h2>
                        <span class="mh-blog__hero-meta">
                            <?php if ( $author ) : ?>
                                <span class="mh-blog__hero-author"><?php echo esc_html( $author ); ?></span>
                                <span class="mh-blog__meta-sep" aria-hidden="true">·</span>
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

        <div class="mh-blog__lower">

            <div class="mh-blog__main">

                <?php if ( ! empty( $list_rows ) ) : ?>
                <h2 class="mh-blog__list-title">Alle artikelen</h2>
                <div class="mh-blog__list">
                    <?php foreach ( $list_rows as $post ) :
                        $link    = get_permalink( $post );
                        $title   = get_the_title( $post );
                        $excerpt = get_the_excerpt( $post );
                        $date    = get_the_date( 'd F Y', $post );
                        $cats    = get_the_category( $post );
                        $cat_lbl = $cats ? esc_html( $cats[0]->name ) : '';
                        $thumb   = get_the_post_thumbnail( $post, 'thumbnail' );
                        $author  = get_the_author_meta( 'display_name', $post->post_author );
                    ?>
                    <a href="<?php echo esc_url( $link ); ?>" class="mh-blog__row">
                        <div class="mh-blog__row-img-wrap">
                            <?php if ( $thumb ) : ?>
                                <?php echo str_replace( '<img ', '<img class="mh-blog__row-img" ', $thumb ); ?>
                            <?php else : ?>
                                <div class="mh-blog__row-placeholder"><?php echo $img_icon; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="mh-blog__row-body">
                            <?php if ( $cat_lbl ) : ?>
                                <span class="mh-blog__row-cat"><?php echo $cat_lbl; ?></span>
                            <?php endif; ?>
                            <h3 class="mh-blog__row-title mh-blog__heading"><?php echo esc_html( $title ); ?></h3>
                            <?php if ( $excerpt ) : ?>
                                <p class="mh-blog__row-excerpt"><?php echo esc_html( wp_strip_all_tags( $excerpt ) ); ?></p>
                            <?php endif; ?>
                            <span class="mh-blog__row-meta">
                                <?php if ( $author ) : ?>
                                    <span class="mh-blog__row-author"><?php echo esc_html( $author ); ?></span>
                                    <span class="mh-blog__meta-sep" aria-hidden="true">·</span>
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
                <nav class="mh-blog__pagination" aria-label="Blogpaginering">
                    <div><?php previous_posts_link( $prev_arrow . ' Nieuwere artikelen' ); ?></div>
                    <div><?php next_posts_link( 'Oudere artikelen ' . $next_arrow ); ?></div>
                </nav>
                <?php endif; ?>

            </div>

            <aside class="mh-blog__sidebar">

                <div class="mh-sidebar-newsletter">
                    <div class="mh-sidebar-newsletter__body">
                        <p class="mh-sidebar-newsletter__label">Verkoop uw units</p>
                        <h3 class="mh-sidebar-newsletter__title">Modulaire units verkopen aan ModulaireHuisvesting.nl</h3>
                        <a href="/verkopen/" class="mh-sidebar-newsletter__btn">
                            Bekijk mogelijkheden
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                        </a>
                    </div>
                </div>

                <div class="mh-sidebar-links">
                    <a href="/modulaire-units/" class="mh-sidebar-link">
                        <span class="mh-sidebar-link__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4.35-4.35"></path></svg>
                        </span>
                        <span class="mh-sidebar-link__text">
                            <strong>Ik zoek units</strong>
                            <span>Vind snel beschikbare modulaire units voor koop of huur</span>
                        </span>
                        <svg class="mh-sidebar-link__arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                    </a>
                    <a href="/verkopen/" class="mh-sidebar-link">
                        <span class="mh-sidebar-link__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v18"></path><path d="M17 8c0-1.933-2.239-3.5-5-3.5S7 6.067 7 8s2.239 3.5 5 3.5 5 1.567 5 3.5-2.239 3.5-5 3.5-5-1.567-5-3.5"></path></svg>
                        </span>
                        <span class="mh-sidebar-link__text">
                            <strong>Units verkopen</strong>
                            <span>Laat uw gebruikte modulaire units vrijblijvend door ons beoordelen</span>
                        </span>
                        <svg class="mh-sidebar-link__arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                    </a>
                </div>

            </aside>

        </div>

    <?php endif; ?>

</main>
