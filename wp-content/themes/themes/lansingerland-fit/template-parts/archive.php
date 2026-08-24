<?php
/**
 * Blog overzichtspagina voor LansingerlandFit.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$placeholder_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="m3 9 4-4 4 4 4-4 4 4"/><circle cx="8.5" cy="14.5" r="1.5"/><path d="m21 15-5-5-5 5"/></svg>';
$search_term = isset( $_GET['lf_s'] ) ? sanitize_text_field( wp_unslash( $_GET['lf_s'] ) ) : '';
$blog_page_id = get_option( 'page_for_posts' );
$blog_url = $blog_page_id ? get_permalink( $blog_page_id ) : home_url( '/' );
$posts = [];
while ( have_posts() ) {
    the_post();
    $posts[] = get_post();
}
$featured_posts = $search_term ? [] : array_slice( $posts, 0, 2 );
$list_posts = $search_term ? $posts : array_slice( $posts, 2 );
?>

<main id="content" class="site-main lf-blog-page">
    <?php if ( empty( $posts ) && ! $search_term ) : ?>
        <p class="lf-blog__empty">Er zijn nog geen artikelen gepubliceerd.</p>
    <?php else : ?>
        <?php if ( $featured_posts ) : ?>
            <div class="lf-blog__heroes">
                <?php foreach ( $featured_posts as $post ) :
                    $link = get_permalink( $post );
                    $title = get_the_title( $post );
                    $date = get_the_date( 'd F Y', $post );
                    $categories = get_the_category( $post );
                    $category = $categories ? $categories[0]->name : '';
                    $thumbnail = get_the_post_thumbnail( $post, 'full', [ 'class' => 'lf-blog__hero-img' ] );
                ?>
                    <a class="lf-blog__hero" href="<?php echo esc_url( $link ); ?>" aria-label="<?php echo esc_attr( $title ); ?>">
                        <?php echo $thumbnail ?: '<div class="lf-blog__placeholder">' . $placeholder_icon . '</div>'; ?>
                        <span class="lf-blog__hero-overlay"></span>
                        <span class="lf-blog__hero-body">
                            <span class="lf-blog__hero-topline">
                                <?php if ( $category ) : ?><span class="lf-blog__hero-category"><?php echo esc_html( $category ); ?></span><?php endif; ?>
                                <span class="lf-blog__hero-meta"><?php echo esc_html( $date ); ?></span>
                            </span>
                            <span class="lf-blog__hero-title"><?php echo esc_html( $title ); ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="lf-blog__lower">
            <div class="lf-blog__main">
                <form class="lf-blog__search" role="search" method="get" action="<?php echo esc_url( $blog_url ); ?>">
                    <div class="lf-blog__search-wrap">
                        <svg class="lf-blog__search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input class="lf-blog__search-input" type="search" name="lf_s" placeholder="Zoek in artikelen..." value="<?php echo esc_attr( $search_term ); ?>" aria-label="Zoek in artikelen">
                        <?php if ( $search_term ) : ?><a class="lf-blog__search-clear" href="<?php echo esc_url( $blog_url ); ?>" aria-label="Zoekopdracht wissen">&times;</a><?php endif; ?>
                        <button class="lf-blog__search-button" type="submit">Zoeken</button>
                    </div>
                </form>

                <?php if ( $search_term && ! $list_posts ) : ?>
                    <p class="lf-blog__empty">Geen artikelen gevonden voor <strong><?php echo esc_html( $search_term ); ?></strong>.</p>
                <?php elseif ( $list_posts ) : ?>
                    <h2 class="lf-blog__list-title"><?php echo $search_term ? esc_html( count( $list_posts ) . ' resultaten voor "' . $search_term . '"' ) : 'Alle artikelen'; ?></h2>
                    <div class="lf-blog__list">
                        <?php foreach ( $list_posts as $post ) :
                            $link = get_permalink( $post );
                            $title = get_the_title( $post );
                            $excerpt = get_the_excerpt( $post );
                            $date = get_the_date( 'd F Y', $post );
                            $categories = get_the_category( $post );
                            $category = $categories ? $categories[0]->name : '';
                            $thumbnail = get_the_post_thumbnail( $post, 'medium_large', [ 'class' => 'lf-blog__row-img' ] );
                        ?>
                            <a class="lf-blog__row" href="<?php echo esc_url( $link ); ?>">
                                <span class="lf-blog__row-image"><?php echo $thumbnail ?: '<span class="lf-blog__placeholder">' . $placeholder_icon . '</span>'; ?></span>
                                <span class="lf-blog__row-body">
                                    <?php if ( $category ) : ?><span class="lf-blog__row-category"><?php echo esc_html( $category ); ?></span><?php endif; ?>
                                    <span class="lf-blog__row-title"><?php echo esc_html( $title ); ?></span>
                                    <?php if ( $excerpt ) : ?><span class="lf-blog__row-excerpt"><?php echo esc_html( wp_strip_all_tags( $excerpt ) ); ?></span><?php endif; ?>
                                    <span class="lf-blog__row-meta"><?php echo esc_html( $date ); ?></span>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php global $wp_query; if ( $wp_query->max_num_pages > 1 ) : ?>
                    <nav class="lf-blog__pagination" aria-label="Blogpaginering">
                        <?php previous_posts_link( '&larr; Nieuwere artikelen' ); ?>
                        <?php next_posts_link( 'Oudere artikelen &rarr;' ); ?>
                    </nav>
                <?php endif; ?>
            </div>

            <aside class="lf-blog__sidebar">
                <section class="lf-blog__newsletter">
                    <p>Blijf op de hoogte</p>
                    <h2>Praktische inspiratie voor een fitter Lansingerland</h2>
                    <a href="<?php echo esc_url( home_url( '/nieuwsbrief/' ) ); ?>">Nieuwsbrief <span aria-hidden="true">&rarr;</span></a>
                </section>
                <div class="lf-blog__links">
                    <a href="<?php echo esc_url( home_url( '/activiteiten/' ) ); ?>"><strong>Bekijk activiteiten</strong><span>Vind sport en beweging bij jou in de buurt</span><b aria-hidden="true">&rarr;</b></a>
                    <a href="<?php echo esc_url( home_url( '/over-lansingerlandfit/' ) ); ?>"><strong>Over LansingerlandFit</strong><span>Samen werken aan een gezonde gemeente</span><b aria-hidden="true">&rarr;</b></a>
                </div>
            </aside>
        </div>
    <?php endif; ?>
</main>
