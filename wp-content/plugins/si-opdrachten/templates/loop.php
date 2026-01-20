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
  display: grid;
  grid-template-columns: 1fr; /* jouw cards zijn full-width, dus 1 kolom */
  gap: 18px;
  align-items: stretch;

  max-width: 95%;
  margin: 0 auto;
  padding: 0 16px; /* ademruimte op mobiel */
  box-sizing: border-box;
}

/* Cards vullen de rij */
.si-opd-grid .si-opd-card{
  height: 100%;
  margin: 0; /* jij geeft margin in card zelf; als je die laat staan, krijg je dubbele spacing */
}

/* Empty state */
.si-opd-empty{
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

@media (max-width: 768px){
  .si-opd-grid{
    gap: 14px;
    padding: 0 12px;
    width: 100%; 
  }
}

</style>