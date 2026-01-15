<?php if (!defined('ABSPATH')) exit; ?>

<a class="mh-unit-card" href="<?php the_permalink(); ?>">
    <div class="mh-unit-card-image">
        <?php if (has_post_thumbnail()): ?>
            <?php the_post_thumbnail('medium_large'); ?>
        <?php endif; ?>
    </div>

    <div class="mh-unit-card-body">
        <h3 class="mh-unit-card-title"><?php the_title(); ?></h3>

        <?php if (has_excerpt()): ?>
            <p class="mh-unit-card-excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>
        <?php endif; ?>

        <span class="mh-unit-card-cta">Bekijk unit →</span>
    </div>
</a>
