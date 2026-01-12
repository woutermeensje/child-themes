<?php
/**
 * Template Name: Nieuws Template
 */

get_header(); ?>

<div class="nieuws-hero">
    <h1>Laatste nieuws</h1>
    <p>Lees hier de nieuwste berichten en updates.</p>
</div>

<div class="nieuws-grid">
    <?php
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 12,
        'paged' => get_query_var('paged') ?: 1,
    );
    $news_query = new WP_Query($args);

    if ($news_query->have_posts()) :
        while ($news_query->have_posts()) : $news_query->the_post(); ?>
            <article class="nieuws-item">
                <a href="<?php the_permalink(); ?>">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="nieuws-thumbnail">
                            <?php the_post_thumbnail('medium'); ?>
                        </div>
                    <?php endif; ?>
                    <div class="nieuws-content">
                        <h2><?php the_title(); ?></h2>
                        <p><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                        <span class="lees-meer">Lees meer →</span>
                    </div>
                </a>
            </article>
        <?php endwhile; ?>
        
        <div class="nieuws-pagination">
            <?php
            echo paginate_links(array(
                'total' => $news_query->max_num_pages,
            ));
            ?>
        </div>
    <?php else : ?>
        <p>Er zijn nog geen berichten.</p>
    <?php endif;
    wp_reset_postdata();
    ?>
</div>

<?php get_footer(); ?>


<style>
    .nieuws-hero {
    text-align: center;
    padding: 4rem 2rem;
    background-color: #f4f4f4;
}

.nieuws-hero h1 {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.nieuws-hero p {
    font-size: 1.2rem;
    color: #555;
}

.nieuws-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.nieuws-item {
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    transition: transform 0.3s;
}

.nieuws-item:hover {
    transform: translateY(-5px);
}

.nieuws-thumbnail img {
    width: 100%;
    height: auto;
    display: block;
}

.nieuws-content {
    padding: 1rem;
}

.nieuws-content h2 {
    font-size: 1.25rem;
    margin: 0 0 0.5rem;
}

.nieuws-content p {
    font-size: 1rem;
    color: #666;
}

.lees-meer {
    display: inline-block;
    margin-top: 0.5rem;
    color: #0073aa;
    font-weight: bold;
}

.nieuws-pagination {
    text-align: center;
    padding: 2rem;
}

</style>
