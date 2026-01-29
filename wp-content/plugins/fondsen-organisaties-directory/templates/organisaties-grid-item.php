<?php if (!defined('ABSPATH')) exit; ?>

<?php
$post_id = get_the_ID();
$title   = get_the_title($post_id);
$link    = get_permalink($post_id);

$excerpt = get_the_excerpt($post_id);
if (!$excerpt) $excerpt = wp_strip_all_tags(get_post_field('post_content', $post_id));
$excerpt = wp_trim_words($excerpt, 22);
?>

<article class="org-card">
  <div class="org-card__img">
    <?php if (has_post_thumbnail($post_id)): ?>
      <?php echo get_the_post_thumbnail($post_id, 'medium_large'); ?>
    <?php endif; ?>
  </div>

  <div class="org-card__body">
    <h3 class="org-card__title">
      <a href="<?php echo esc_url($link); ?>">
        <?php echo esc_html($title); ?>
      </a>
    </h3>

    <p class="org-card__excerpt">
      <?php echo esc_html($excerpt); ?>
    </p>
  </div>
</article>
