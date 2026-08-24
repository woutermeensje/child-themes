<?php
/**
 * Single blogpost voor LansingerlandFit.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

while ( have_posts() ) :
    the_post();

    if ( 'post' !== get_post_type() ) :
        ?>
        <main id="content" <?php post_class( 'site-main lf-single-page' ); ?>>
            <div class="lf-single__content"><?php the_content(); ?></div>
        </main>
        <?php
        continue;
    endif;

    $categories = get_the_category();
    $category = $categories ? $categories[0] : null;
    $date = get_the_date( 'd F Y' );
    $content_text = wp_strip_all_tags( get_the_content() );
    $read_time = max( 1, (int) round( str_word_count( $content_text ) / 200 ) );
    ?>

    <main id="content" class="site-main lf-single-page">
        <div class="lf-single__wrap">
            <article <?php post_class( 'lf-single__article' ); ?>>
                <?php if ( $category ) : ?>
                    <a class="lf-single__category" href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>">
                        <?php echo esc_html( $category->name ); ?>
                    </a>
                <?php endif; ?>

                <h1 class="lf-single__title"><?php the_title(); ?></h1>

                <div class="lf-single__meta">
                    <span><?php echo esc_html( $date ); ?></span>
                    <span aria-hidden="true">&middot;</span>
                    <span><?php echo esc_html( $read_time ); ?> min leestijd</span>
                </div>

                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="lf-single__hero">
                        <?php the_post_thumbnail( 'full' ); ?>
                    </div>
                <?php endif; ?>

                <div class="lf-single__content">
                    <?php the_content(); ?>
                    <?php wp_link_pages(); ?>
                </div>

                <?php if ( has_tag() ) : ?>
                    <div class="lf-single__tags"><?php the_tags( '', '' ); ?></div>
                <?php endif; ?>

                <nav class="lf-single__nav" aria-label="Artikelnavigatie">
                    <div><?php previous_post_link( '%link', '&larr; %title' ); ?></div>
                    <div><?php next_post_link( '%link', '%title &rarr;' ); ?></div>
                </nav>
            </article>

            <aside class="lf-single__sidebar">
                <section class="lf-single__recent">
                    <h2 class="lf-single__recent-title">Recente artikelen</h2>
                    <?php
                    $recent_query = new WP_Query(
                        [
                            'post_type'      => 'post',
                            'post_status'    => 'publish',
                            'posts_per_page' => 6,
                            'post__not_in'   => [ get_the_ID() ],
                            'no_found_rows'  => true,
                        ]
                    );
                    if ( $recent_query->have_posts() ) :
                        while ( $recent_query->have_posts() ) :
                            $recent_query->the_post();
                            ?>
                            <a class="lf-recent__item" href="<?php the_permalink(); ?>">
                                <span class="lf-recent__image">
                                    <?php if ( has_post_thumbnail() ) : ?>
                                        <?php the_post_thumbnail( 'thumbnail', [ 'loading' => 'lazy' ] ); ?>
                                    <?php else : ?>
                                        <span aria-hidden="true">LF</span>
                                    <?php endif; ?>
                                </span>
                                <span class="lf-recent__body">
                                    <strong><?php the_title(); ?></strong>
                                    <small><?php echo esc_html( get_the_date( 'd M Y' ) ); ?></small>
                                </span>
                            </a>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    else :
                        ?>
                        <p class="lf-single__recent-empty">Geen andere artikelen gevonden.</p>
                    <?php endif; ?>
                </section>
            </aside>
        </div>
    </main>
<?php endwhile; ?>
