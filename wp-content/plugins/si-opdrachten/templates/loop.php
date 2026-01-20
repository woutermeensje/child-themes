<?php if (!defined('ABSPATH')) exit;

/** @var WP_Query $query */
?>

<div class="si-opd-grid">
  <?php if ($query->have_posts()): ?>

    <?php while ($query->have_posts()): $query->the_post(); ?>
      <?php si_opd_render_template('loop-item.php'); ?>
    <?php endwhile; ?>

  <?php else: ?>
    <div class="si-opd-empty">
      Geen opdrachten gevonden.
    </div>
  <?php endif; ?>
</div>


<style>
  /* ==============================
   Studentinhuren – Grid / Loop
   ============================== */

.si-opd-grid{
  align-items: stretch;
  margin: 0 auto; 
  width: 1050px; 
}

/* Zorg dat cards netjes dezelfde hoogte mogen pakken */
.si-opd-grid .si-opd-card{
  height: 100%;
}

/* Empty state */
.si-opd-empty{
  grid-column: 1 / -1;
  padding: 18px 20px;
  border: 1px dashed #D1D5DB;
  border-radius: 10px;
  background: #F9FAFB;
  font-family: 'Poppins', sans-serif;
  font-size: 14px;
  color: #374151;
}

/* ==============================
   Responsive
   ============================== */

/* Tablet */
@media (max-width: 1024px){
  .si-opd-grid{
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
  }
}

/* Mobile */
@media (max-width: 640px){
  .si-opd-grid{
    grid-template-columns: 1fr;
    gap: 14px;
  }
}

</style>