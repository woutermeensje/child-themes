<?php if (!defined('ABSPATH')) exit; ?>

<article class="org-card">
  <div class="org-card__img">
    <?php if (has_post_thumbnail()): ?>
      <?php the_post_thumbnail('medium_large'); ?>
    <?php endif; ?>
  </div>

  <div class="org-card__body">
    <h3 class="org-card__title">
      <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
    </h3>

    <p class="org-card__excerpt">
      <?php echo esc_html( wp_trim_words( get_the_excerpt() ?: wp_strip_all_tags(get_the_content()), 22 ) ); ?>
    </p>
  </div>
</article>
