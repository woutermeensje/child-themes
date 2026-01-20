<?php if (!defined('ABSPATH')) exit;

$cats  = get_the_terms(get_the_ID(), 'si_opdracht_categorie');
$types = get_the_terms(get_the_ID(), 'si_opdracht_type');
?>

<article class="si-opd-card">
  <a class="si-opd-card-link" href="<?php the_permalink(); ?>">

    <div class="si-opd-card-inner">

      <div class="si-opd-main">
        <h3 class="si-opd-title"><?php the_title(); ?></h3>

        <?php if (has_excerpt()): ?>
          <p class="si-opd-excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>
        <?php endif; ?>

        <div class="si-opd-meta">
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
      </div>

      <div class="si-opd-side">
        <span class="si-opd-cta">Opdracht bekijken</span>
      </div>

    </div>

  </a>
</article>


<style>
 
/* ==============================
   Studentinhuren – Opdracht card (nieuw)
   ============================== */

.si-opd-card{
  background: #fff;
  border: 1px solid #E5E7EB;
  border-radius: 10px;
  overflow: hidden;
  transition: box-shadow .25s ease, transform .25s ease;
  margin-top: 24px;
  margin-bottom: 24px;
}

.si-opd-card:hover{
  box-shadow: 0 12px 30px rgba(0,0,0,0.12);
  transform: translateY(-2px);
}

/* Hele kaart klikbaar */
.si-opd-card-link{
  display: block;
  text-decoration: none;
  color: inherit;
}

/* Nieuwe inner layout: main links, CTA rechts */
.si-opd-card-inner{
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
  padding: 20px;
}

/* Main content */
.si-opd-main{
  flex: 1 1 auto;
  min-width: 0; /* belangrijk voor ellipsis/overflow */
}

/* Titel */
.si-opd-title{
  font-family: 'Poppins', sans-serif;
  font-size: 18px;
  font-weight: 600;
  line-height: 1.3;
  margin: 0 0 6px 0;
  color: #111827;
}

/* Excerpt */
.si-opd-excerpt{
  font-family: 'Poppins', sans-serif;
  font-size: 14px;
  line-height: 1.5;
  margin: 0 0 12px 0;
  color: #4B5563;
}

/* Tags */
.si-opd-meta{
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.si-opd-tag{
  display: inline-block;
  padding: 4px 10px;
  font-size: 12px;
  font-family: 'Poppins', sans-serif;
  border-radius: 999px;
  background: #EEF2FF;
  color: #3730A3;
  white-space: nowrap;
}

.si-opd-tag--alt{
  background: #ECFEFF;
  color: #155E75;
}

/* Rechterzijde */
.si-opd-side{
  flex: 0 0 auto;
  display: flex;
  align-items: center;
}

/* CTA tekst rechts */
.si-opd-cta{
  font-family: 'Poppins', sans-serif;
  font-size: 14px;
  font-weight: 500;
  color: #0456AB;
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

/* subtiele arrow */
.si-opd-cta::after{
  content: "→";
  transition: transform .2s ease;
}

.si-opd-card:hover .si-opd-cta::after{
  transform: translateX(3px);
}

/* ==============================
   Responsive
   ============================== */

@media (max-width: 768px){
  .si-opd-card-inner{
    flex-direction: column;
    align-items: flex-start;
  }

  .si-opd-side{
    width: 100%;
    justify-content: flex-end; /* CTA rechts onder */
    margin-top: 4px;
  }
}

@media (max-width: 640px){
  .si-opd-title{ font-size: 17px; }
}


</style>