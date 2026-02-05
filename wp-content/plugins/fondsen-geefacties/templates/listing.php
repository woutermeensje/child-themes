<?php if (!defined('ABSPATH')) exit; ?>

<div class="fga-listing">
  <?php if (!empty($data['posts'])): ?>
    <div class="fga-grid">
      <?php foreach ($data['posts'] as $p): ?>
        <?php
          global $post;
          $post = $p;                // ✅ belangrijk: global post zetten
          setup_postdata($post);
          include FGA_PATH . 'templates/card.php';
        ?>
      <?php endforeach; ?>
    </div>

    <?php if (($data['max_pages'] ?? 1) > 1): ?>
      <div class="fga-pagination">
        <?php
          echo paginate_links([
            'total'   => (int) $data['max_pages'],
            'current' => (int) ($data['paged'] ?? 1),
          ]);
        ?>
      </div>
    <?php endif; ?>

  <?php else: ?>
    <div class="fga-empty">Geen geefacties gevonden.</div>
  <?php endif; ?>
</div>

<?php wp_reset_postdata(); ?>

<style>
    /* ===== Listing (templates/listing.php) ===== */

.fga-listing {
  max-width: 1100px;
  margin: 18px auto 40px;
  padding: 0 16px;
}

.fga-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 18px;
}

.fga-empty {
  background: #fff;
  border: 1px solid #e6e6e6;
  border-radius: 5px;
  padding: 18px;
  opacity: .85;
}

/* Pagination */
.fga-pagination {
  margin-top: 20px;
}

.fga-pagination .page-numbers {
  display: inline-block;
  padding: 8px 12px;
  margin: 0 6px 6px 0;
  border: 1px solid #e6e6e6;
  border-radius: 5px;
  text-decoration: none;
  color: #1f2937;
  background: #fff;
}

.fga-pagination .page-numbers.current {
  background: #1f2937;
  color: #fff;
  border-color: #1f2937;
}

/* Responsive */
@media (max-width: 980px){
  .fga-grid {
    grid-template-columns: repeat(2, minmax(0,1fr));
  }
}

@media (max-width: 560px){
  .fga-grid {
    grid-template-columns: 1fr;
  }
}

</style>