<?php if (!defined('ABSPATH')) exit; ?>
<?php /** @var WP_Query $query */ ?>

<div class="vpjobs-listings">
  <?php if ($query->have_posts()): ?>
    <?php while ($query->have_posts()): $query->the_post(); ?>
      <article class="vpjobs-card">
        <a class="vpjobs-card-link" href="<?php the_permalink(); ?>">
          <h3 class="vpjobs-title"><?php the_title(); ?></h3>

          <div class="vpjobs-meta">
            <?php $loc = get_post_meta(get_the_ID(), '_vp_location', true); ?>
            <?php if ($loc): ?><span class="vpjobs-pill"><?php echo esc_html($loc); ?></span><?php endif; ?>

            <?php
              $types = wp_get_post_terms(get_the_ID(), 'vp_job_type', [ 'fields' => 'names' ]);
              foreach ($types as $t) echo '<span class="vpjobs-pill">' . esc_html($t) . '</span>';
            ?>
          </div>

          <div class="vpjobs-excerpt">
            <?php echo wp_kses_post(wpautop(get_the_excerpt())); ?>
          </div>
        </a>
      </article>
    <?php endwhile; wp_reset_postdata(); ?>
  <?php else: ?>
    <div class="vpjobs-empty">
      Geen vacatures gevonden.
    </div>
  <?php endif; ?>
</div>