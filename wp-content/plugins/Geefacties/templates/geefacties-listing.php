<?php if (!defined('ABSPATH')) exit; ?>

<div class="ga-listing-wrap">
  <div
    id="fod-ga-grid"
    class="ga-grid"
    data-per-page="<?php echo esc_attr((int)($data['per_page'] ?? 18)); ?>"
  >

    <?php if (!empty($data['posts'])): ?>
      <?php foreach ($data['posts'] as $p): ?>
        <?php
          $post = $p;
          setup_postdata($post);
          include $this->plugin_path('templates/geefacties-card.php');
        ?>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="ga-empty">
        Geen geefacties gevonden.
      </div>
    <?php endif; ?>

  </div>

  <?php if (!empty($data['posts']) && (int)($data['max_pages'] ?? 1) > 1): ?>
    <div class="ga-loadmore-wrap">
      <button id="fod-ga-loadmore" class="ga-loadmore" type="button" data-page="2">
        Meer geefacties laden
      </button>
    </div>
  <?php endif; ?>

</div>

<?php wp_reset_postdata(); ?>
