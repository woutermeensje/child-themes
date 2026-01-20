<?php
if (!defined('ABSPATH')) exit;

get_header();

while (have_posts()) : the_post();

  $cats  = get_the_terms(get_the_ID(), 'si_opdracht_categorie');
  $types = get_the_terms(get_the_ID(), 'si_opdracht_type');

  $logo_id = get_post_thumbnail_id(get_the_ID());
  $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'medium') : '';
?>

<div class="si-opd-single-wrap">
  <div class="si-opd-single">

    <header class="si-opd-single-header">
      <?php if ($logo_url): ?>
        <img class="si-opd-single-logo" src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
      <?php endif; ?>

      <h1 class="si-opd-single-title"><?php the_title(); ?></h1>

      <div class="si-opd-single-meta">
        <?php if (!empty($cats) && !is_wp_error($cats)): ?>
          <?php foreach ($cats as $term): ?>
            <span class="si-opd-tag"><?php echo esc_html($term->name); ?></span>
          <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($types) && !is_wp_error($types)): ?>
          <?php foreach ($types as $term): ?>
            <span class="si-opd-tag si-opd-tag--alt"><?php echo esc_html($term->name); ?></span>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </header>

    <div class="si-opd-single-content">
      <?php the_content(); ?>
    </div>

  </div>
</div>

<?php
endwhile;

get_footer();
