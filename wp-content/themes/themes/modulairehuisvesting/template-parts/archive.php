<?php
/**
 * Blog overzichtspagina — Modulaire Huisvesting child theme
 *
 * Layout:
 *  1. Hero (eerste post) — volledige breedte, sluit aan op navigatie
 *  2. Duo (2e en 3e post) — volledige breedte, twee kolommen
 *  3. Lower: lijst (900px) + nieuwsbrief sidebar (300px)
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$img_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#25476B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="m3 9 4-4 4 4 4-4 4 4"/><circle cx="8.5" cy="14.5" r="1.5"/><path d="m21 15-5-5-5 5"/></svg>';

$posts_all = [];
while ( have_posts() ) {
    the_post();
    $posts_all[] = get_post();
}
?>

<main id="content" class="site-main mh-blog-page">

    <?php if ( empty( $posts_all ) ) : ?>
        <p class="mh-blog__empty">Er zijn nog geen blogartikelen gepubliceerd.</p>
    <?php else : ?>

        <?php
        $top_posts = array_slice( $posts_all, 0, 2 );
        $list_rows = array_slice( $posts_all, 2 );

        // --------------------------------------------------------------------
        // 1. TWEE HERO-BLOKKEN — volledige breedte, sluiten aan op header
        // --------------------------------------------------------------------
        if ( ! empty( $top_posts ) ) :
        ?>
        <div class="mh-blog__heroes">
            <?php foreach ( $top_posts as $post ) :
                $link    = get_permalink( $post );
                $title   = get_the_title( $post );
                $date    = get_the_date( 'd F Y', $post );
                $cats    = get_the_category( $post );
                $cat_lbl = $cats ? esc_html( $cats[0]->name ) : '';
                $thumb   = get_the_post_thumbnail( $post, 'full' );
            ?>
            <a href="<?php echo esc_url( $link ); ?>" class="mh-blog__hero" aria-label="<?php echo esc_attr( $title ); ?>">
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
                    <span class="mh-blog__hero-meta"><?php echo esc_html( $date ); ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php
        // --------------------------------------------------------------------
        // 2. LOWER — lijst (900px) + nieuwsbrief sidebar (300px)
        // --------------------------------------------------------------------
        ?>
        <div class="mh-blog__lower">

            <div class="mh-blog__main">

                <?php if ( ! empty( $list_rows ) ) : ?>
                <h2 class="mh-blog__list-title">Meer artikelen</h2>
                <div class="mh-blog__list">
                    <?php foreach ( $list_rows as $post ) :
                        $link    = get_permalink( $post );
                        $title   = get_the_title( $post );
                        $excerpt = get_the_excerpt( $post );
                        $date    = get_the_date( 'd F Y', $post );
                        $cats    = get_the_category( $post );
                        $cat_lbl = $cats ? esc_html( $cats[0]->name ) : '';
                        $thumb   = get_the_post_thumbnail( $post, 'thumbnail' );
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
                            <span class="mh-blog__row-meta"><?php echo esc_html( $date ); ?></span>
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

            </div><!-- .mh-blog__main -->

            <aside class="mh-blog__sidebar">
                <div class="mh-newsletter">
                    <div class="mh-newsletter__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 7 10-7"/></svg>
                    </div>
                    <h3 class="mh-newsletter__title">Blijf op de hoogte</h3>
                    <p class="mh-newsletter__desc">Ontvang het laatste nieuws over modulaire huisvesting direct in je inbox.</p>
                    <?php
                    // Vervang dit blok door een Gravity Forms of andere shortcode:
                    // echo do_shortcode('[gravityforms id="X"]');
                    ?>
                    <form class="mh-newsletter__form" action="#" method="post" novalidate>
                        <div class="mh-newsletter__field">
                            <label for="nl-name" class="mh-newsletter__label">Naam</label>
                            <input type="text" id="nl-name" name="nl_name" class="mh-newsletter__input" placeholder="Jan de Vries" autocomplete="name">
                        </div>
                        <div class="mh-newsletter__field">
                            <label for="nl-email" class="mh-newsletter__label">E-mailadres <span aria-hidden="true">*</span></label>
                            <input type="email" id="nl-email" name="nl_email" class="mh-newsletter__input" placeholder="jouw@email.nl" required autocomplete="email">
                        </div>
                        <button type="submit" class="mh-newsletter__btn">
                            Aanmelden
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                        </button>
                    </form>
                    <p class="mh-newsletter__privacy">
                        We respecteren je privacy. <a href="/privacy">Privacybeleid</a>
                    </p>
                </div>
            </aside>

        </div><!-- .mh-blog__lower -->

    <?php endif; ?>

</main>
