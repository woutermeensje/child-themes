<?php
/**
 * Blog overzichtspagina — Fondsen.org child theme
 *
 * Layout:
 *  1. Twee hero-blokken — volledige breedte, sluiten aan op navigatie
 *  2. Lower: lijst (artikelen) + sidebar
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$img_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#FF8C2C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="m3 9 4-4 4 4 4-4 4 4"/><circle cx="8.5" cy="14.5" r="1.5"/><path d="m21 15-5-5-5 5"/></svg>';

$posts_all = [];
while ( have_posts() ) {
    the_post();
    $posts_all[] = get_post();
}
?>

<main id="content" class="site-main fn-blog-page">

    <?php if ( empty( $posts_all ) ) : ?>
        <p class="fn-blog__empty">Er zijn nog geen artikelen gepubliceerd.</p>
    <?php else : ?>

        <?php
        $top_posts = array_slice( $posts_all, 0, 2 );
        $list_rows = array_slice( $posts_all, 2 );
        ?>

        <?php if ( ! empty( $top_posts ) ) : ?>
        <div class="fn-blog__heroes">
            <?php foreach ( $top_posts as $post ) :
                $link    = get_permalink( $post );
                $title   = get_the_title( $post );
                $date    = get_the_date( 'd F Y', $post );
                $cats    = get_the_category( $post );
                $cat_lbl = $cats ? esc_html( $cats[0]->name ) : '';
                $thumb   = get_the_post_thumbnail( $post, 'full' );
                $auteur  = get_post_meta( $post->ID, '_fn_artikel_auteur', true );
            ?>
            <a href="<?php echo esc_url( $link ); ?>" class="fn-blog__hero" aria-label="<?php echo esc_attr( $title ); ?>">
                <?php if ( $thumb ) : ?>
                    <?php echo str_replace( '<img ', '<img class="fn-blog__hero-img" ', $thumb ); ?>
                <?php else : ?>
                    <div class="fn-blog__hero-placeholder"><?php echo $img_icon; ?></div>
                <?php endif; ?>
                <div class="fn-blog__hero-overlay"></div>
                <div class="fn-blog__hero-body">
                    <?php if ( $cat_lbl ) : ?>
                        <span class="fn-blog__hero-cat"><?php echo $cat_lbl; ?></span>
                    <?php endif; ?>
                    <h2 class="fn-blog__hero-title fn-blog__heading"><?php echo esc_html( $title ); ?></h2>
                    <span class="fn-blog__hero-meta">
                        <?php if ( $auteur ) : ?>
                            <span class="fn-blog__hero-auteur"><?php echo esc_html( $auteur ); ?></span>
                            <span class="fn-blog__meta-sep" aria-hidden="true">·</span>
                        <?php endif; ?>
                        <?php echo esc_html( $date ); ?>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="fn-blog__lower">

            <div class="fn-blog__main">

                <?php if ( ! empty( $list_rows ) ) : ?>
                <h2 class="fn-blog__list-title">Alle artikelen</h2>
                <div class="fn-blog__list">
                    <?php foreach ( $list_rows as $post ) :
                        $link    = get_permalink( $post );
                        $title   = get_the_title( $post );
                        $excerpt = get_the_excerpt( $post );
                        $date    = get_the_date( 'd F Y', $post );
                        $cats    = get_the_category( $post );
                        $cat_lbl = $cats ? esc_html( $cats[0]->name ) : '';
                        $thumb   = get_the_post_thumbnail( $post, 'thumbnail' );
                        $auteur  = get_post_meta( $post->ID, '_fn_artikel_auteur', true );
                    ?>
                    <a href="<?php echo esc_url( $link ); ?>" class="fn-blog__row">
                        <div class="fn-blog__row-img-wrap">
                            <?php if ( $thumb ) : ?>
                                <?php echo str_replace( '<img ', '<img class="fn-blog__row-img" ', $thumb ); ?>
                            <?php else : ?>
                                <div class="fn-blog__row-placeholder"><?php echo $img_icon; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="fn-blog__row-body">
                            <?php if ( $cat_lbl ) : ?>
                                <span class="fn-blog__row-cat"><?php echo $cat_lbl; ?></span>
                            <?php endif; ?>
                            <h3 class="fn-blog__row-title fn-blog__heading"><?php echo esc_html( $title ); ?></h3>
                            <?php if ( $excerpt ) : ?>
                                <p class="fn-blog__row-excerpt"><?php echo esc_html( wp_strip_all_tags( $excerpt ) ); ?></p>
                            <?php endif; ?>
                            <span class="fn-blog__row-meta">
                                <?php if ( $auteur ) : ?>
                                    <span class="fn-blog__row-auteur"><?php echo esc_html( $auteur ); ?></span>
                                    <span class="fn-blog__meta-sep" aria-hidden="true">·</span>
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
                <nav class="fn-blog__pagination" aria-label="Blogpaginering">
                    <div><?php previous_posts_link( $prev_arrow . ' Nieuwere artikelen' ); ?></div>
                    <div><?php next_posts_link( 'Oudere artikelen ' . $next_arrow ); ?></div>
                </nav>
                <?php endif; ?>

            </div><!-- .fn-blog__main -->

            <aside class="fn-blog__sidebar">

                <div class="fn-sidebar-newsletter">
                    <div class="fn-sidebar-newsletter__body">
                        <p class="fn-sidebar-newsletter__label">Blijf op de hoogte</p>
                        <h3 class="fn-sidebar-newsletter__title">Nieuws over fondsenwerving direct in je inbox</h3>
                        <a href="/nieuwsbrief/" class="fn-sidebar-newsletter__btn">
                            Nieuwsbrief
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                        </a>
                    </div>
                </div>

                <div class="fn-sidebar-links">
                    <a href="/vacature-plaatsen/" class="fn-sidebar-link">
                        <span class="fn-sidebar-link__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="17"/><line x1="9.5" y1="14.5" x2="14.5" y2="14.5"/></svg>
                        </span>
                        <span class="fn-sidebar-link__text">
                            <strong>Vacature plaatsen</strong>
                            <span>Bereik fondsenwervers en non-profits</span>
                        </span>
                        <svg class="fn-sidebar-link__arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                    </a>
                    <a href="/aanmelden/" class="fn-sidebar-link">
                        <span class="fn-sidebar-link__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        <span class="fn-sidebar-link__text">
                            <strong>Account aanmaken</strong>
                            <span>Sla vacatures op en stel alerts in</span>
                        </span>
                        <svg class="fn-sidebar-link__arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                    </a>
                </div>

            </aside>

        </div><!-- .fn-blog__lower -->

    <?php endif; ?>

</main>
