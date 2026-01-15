<?php
if ( ! defined('ABSPATH') ) exit;

/** @var WP_Query $query */
$query = $context['query'];
?>

<style>
.org-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 22px;
    width: 1050px;
    max-width: calc(100% - 24px);
    margin: 0 auto;
}

@media (max-width: 900px) {
    .org-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 600px) {
    .org-grid { grid-template-columns: 1fr; }
}

.org-card {
    border: 1px solid #E4E7EC;
    border-radius: 5px;
    background: #fff;
    overflow: hidden;
    box-shadow: 0 10px 40px -5px rgba(0,0,0,.15);
    display: flex;
    flex-direction: column;
    min-height: 100%;
}

.org-card__img {
    width: 100%;
    aspect-ratio: 16 / 9;
    background: #F2F4F7;
    overflow: hidden;
}

.org-card__img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.org-card__body {
    padding: 14px 14px 16px;
}

.org-card__title {
    margin: 0 0 10px;
    font-size: 18px;
    line-height: 1.25;
    font-family: Poppins ,sans-serif;
    font-weight: 600;
    color: #333333;
}

.org-card__title a {
    text-decoration: none;
    color: #101828;
}

.org-card__excerpt {
    margin: 0;
    font-size: 14.5px;
    line-height: 1.45;
    color: #475467;
}
</style>

<?php if ( $query->have_posts() ) : ?>
    <div class="org-grid">
        <?php while ( $query->have_posts() ) : $query->the_post(); ?>
            <article class="org-card">
                <a class="org-card__img" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <?php the_post_thumbnail('medium_large'); ?>
                    <?php else : ?>
                        <!-- optioneel: placeholder -->
                    <?php endif; ?>
                </a>

                <div class="org-card__body">
                    <h3 class="org-card__title">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_title(); ?>
                        </a>
                    </h3>

                   <p class="org-card__excerpt">
                        <?php echo esc_html( wp_trim_words( get_the_excerpt(), 15, '…' ) ); ?>
                    </p>

                </div>
            </article>
        <?php endwhile; ?>
    </div>
<?php else : ?>
    <p style="width:1050px;max-width:calc(100% - 24px);margin:0 auto;">Er zijn geen organisaties gevonden die aan je filters voldoen.</p>
<?php endif; ?>
