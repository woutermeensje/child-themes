<?php
/**
 * Single blog post — Sustainablejobs COM child theme
 *
 * Layout: article (left, wide) + recent posts sidebar (right)
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

while ( have_posts() ) :
    the_post();

    if ( 'post' !== get_post_type() ) :
?>

<main id="content" <?php post_class( 'site-main' ); ?>>

    <?php if ( apply_filters( 'hello_elementor_page_title', true ) ) : ?>
        <div class="page-header">
            <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
        </div>
    <?php endif; ?>

    <div class="page-content">
        <?php the_content(); ?>
        <?php wp_link_pages(); ?>

        <?php if ( has_tag() ) : ?>
        <div class="post-tags">
            <?php the_tags( '<span class="tag-links">' . esc_html__( 'Tagged ', 'hello-elementor' ), ', ', '</span>' ); ?>
        </div>
        <?php endif; ?>
    </div>

    <?php comments_template(); ?>

</main>

<?php
        continue;
    endif;

    $cats      = get_the_category();
    $cat_lbl   = $cats ? esc_html( $cats[0]->name ) : '';
    $cat_url   = $cats ? esc_url( get_category_link( $cats[0]->term_id ) ) : '';
    $date      = get_the_date( 'F j, Y' );
    $auteur    = get_post_meta( get_the_ID(), '_sj_artikel_auteur', true );
    $thumb     = get_the_post_thumbnail( get_the_ID(), 'full' );

    // Reading time estimate
    $content    = get_the_content();
    $word_count = str_word_count( wp_strip_all_tags( $content ) );
    $read_time  = max( 1, round( $word_count / 200 ) );
?>

<main id="content" class="site-main sj-single-page">
<div class="sj-single__wrap">

    <!-- ── Article ─────────────────────────────────────── -->
    <article <?php post_class( 'sj-single__article' ); ?>>

        <?php if ( $cat_lbl ) : ?>
            <a href="<?php echo $cat_url; ?>" class="sj-single__cat"><?php echo $cat_lbl; ?></a>
        <?php endif; ?>

        <h1 class="sj-single__title"><?php the_title(); ?></h1>

        <div class="sj-single__meta">
            <?php if ( $auteur ) : ?>
                <span class="sj-single__auteur">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <?php echo esc_html( $auteur ); ?>
                </span>
            <?php endif; ?>
            <span class="sj-single__date">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <?php echo esc_html( $date ); ?>
            </span>
            <span class="sj-single__readtime">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <?php echo $read_time; ?> min read
            </span>
        </div>

        <?php if ( $thumb ) : ?>
            <div class="sj-single__hero-img">
                <?php echo $thumb; ?>
            </div>
        <?php endif; ?>

        <div class="sj-single__content">
            <?php the_content(); ?>
            <?php wp_link_pages(); ?>
        </div>

        <?php if ( has_tag() ) : ?>
            <div class="sj-single__tags">
                <?php the_tags( '', '' ); ?>
            </div>
        <?php endif; ?>

        <nav class="sj-single__nav" aria-label="Article navigation">
            <div class="sj-single__nav-prev">
                <?php previous_post_link( '%link', '&larr; %title' ); ?>
            </div>
            <div class="sj-single__nav-next">
                <?php next_post_link( '%link', '%title &rarr;' ); ?>
            </div>
        </nav>

    </article>

    <!-- ── Sidebar: recent articles ──────────────────── -->
    <aside class="sj-single__sidebar">

        <div class="sj-single__recent">
            <h3 class="sj-single__recent-title">Recent articles</h3>
            <?php
            $recent = new WP_Query( [
                'post_type'      => 'post',
                'posts_per_page' => 6,
                'post__not_in'   => [ get_the_ID() ],
                'post_status'    => 'publish',
            ] );
            if ( $recent->have_posts() ) :
                while ( $recent->have_posts() ) : $recent->the_post();
                    $r_thumb  = get_the_post_thumbnail( get_the_ID(), 'thumbnail' );
                    $r_date   = get_the_date( 'j M Y' );
                    $r_cats   = get_the_category();
                    $r_cat    = $r_cats ? esc_html( $r_cats[0]->name ) : '';
            ?>
            <a href="<?php the_permalink(); ?>" class="sj-recent__item">
                <div class="sj-recent__img-wrap">
                    <?php if ( $r_thumb ) : ?>
                        <?php echo str_replace( '<img ', '<img class="sj-recent__img" ', $r_thumb ); ?>
                    <?php else : ?>
                        <div class="sj-recent__img-placeholder">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#0A6B8D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="m3 9 4-4 4 4 4-4 4 4"/><circle cx="8.5" cy="14.5" r="1.5"/><path d="m21 15-5-5-5 5"/></svg>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="sj-recent__body">
                    <?php if ( $r_cat ) : ?>
                        <span class="sj-recent__cat"><?php echo $r_cat; ?></span>
                    <?php endif; ?>
                    <h4 class="sj-recent__title"><?php the_title(); ?></h4>
                    <span class="sj-recent__date"><?php echo esc_html( $r_date ); ?></span>
                </div>
            </a>
            <?php
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>

        <div class="sj-sidebar-newsletter" style="margin-top:24px;">
            <div class="sj-sidebar-newsletter__body">
                <p class="sj-sidebar-newsletter__label">Stay up to date</p>
                <h3 class="sj-sidebar-newsletter__title">Sustainable jobs news straight to your inbox</h3>
                <a href="/newsletter/" class="sj-sidebar-newsletter__btn">
                    Newsletter
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                </a>
            </div>
        </div>

    </aside>

</div>
</main>

<?php endwhile; ?>
