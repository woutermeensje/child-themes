<?php if (!defined('ABSPATH')) exit; ?>

<div class="mh-units-results">
    <?php if ($query->have_posts()): ?>
        <div class="mh-units-list">
            <?php while ($query->have_posts()): $query->the_post(); ?>
                <?php mh_units_render_template('loop-item.php'); ?>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="mh-units-empty">Geen units gevonden.</div>
    <?php endif; ?>
</div>

<style>
/* ===== UNITS OVERZICHT: 1 onder elkaar (full width) ===== */

.mh-units-list{
  display: flex;
  flex-direction: column;
  width: 1050px; 
  margin: 0 auto;
 
}
</style>
