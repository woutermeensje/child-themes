<?php if (!defined('ABSPATH')) exit; ?>

<article class="mh-unit-single">
    <header class="mh-unit-single-header">
        <h1 class="mh-unit-single-title"><?php the_title(); ?></h1>
    </header>

    <div class="mh-unit-single-top">
        <div class="mh-unit-single-image">
            <?php if (has_post_thumbnail()) the_post_thumbnail('large'); ?>
        </div>

        <div class="mh-unit-single-sidebar">
            <a class="mh-btn mh-btn-primary" href="/contact/?unit=<?php echo esc_attr(get_the_ID()); ?>">
                Offerte aanvragen
            </a>
        </div>
    </div>

    <div class="mh-unit-single-content">
        <?php the_content(); ?>
    </div>
</article>
