<?php if (!defined('ABSPATH')) exit; ?>

<div class="mh-units-results">
    <?php if ($query->have_posts()): ?>
        <div class="mh-units-grid">
            <?php while ($query->have_posts()): $query->the_post(); ?>
                <?php mh_units_render_template('loop-item.php'); ?>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="mh-units-empty">Geen units gevonden.</div>
    <?php endif; ?>
</div>


<style>
    /* Overzicht/grid container */

.mh-unit-card{
  display:block;
  border:1px solid #eee;
  border-radius:12px;
  overflow:hidden;
  text-decoration:none;
}
.mh-units-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
  margin-top: 16px;
}

/* Responsive grid */
@media (max-width: 900px) {
  .mh-units-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 600px) {
  .mh-units-grid {
    grid-template-columns: 1fr;
  }
}

</style>