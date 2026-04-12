<?php if (!defined('ABSPATH')) exit; ?>

<div class="mh-units-results">
    <?php if ($query->have_posts()): ?>
        <div class="mh-units-grid">
            <?php while ($query->have_posts()): $query->the_post(); ?>
                <?php mh_units_render_template('loop-item.php'); ?>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="mh-units-gallery-empty">Geen units gevonden.</div>
    <?php endif; ?>
</div>
