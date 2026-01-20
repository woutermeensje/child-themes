<?php if (!defined('ABSPATH')) exit;

$cats  = get_the_terms(get_the_ID(), 'si_opdracht_categorie');
$types = get_the_terms(get_the_ID(), 'si_opdracht_type');
?>

<article class="si-opd-card">
  <a class="si-opd-card-link" href="<?php the_permalink(); ?>">

    <div class="si-opd-card-inner">

      <div class="si-opd-main">
        <h3 class="si-opd-title"><?php the_title(); ?></h3>

       <?php
      $excerpt = '';

      if (has_excerpt()) {
        $excerpt = get_the_excerpt();
      } else {
        $content = get_the_content();
        $content = strip_shortcodes($content);
        $content = wp_strip_all_tags($content);
        $excerpt = wp_trim_words($content, 16, '…');
      }

      if ($excerpt):
      ?>
        <p class="si-opd-excerpt"><?php echo esc_html($excerpt); ?></p>
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
  border: 1px solid #DEDEDE; 
  border-radius: 5px;
  overflow: hidden;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  margin-top: 24px;
  margin-bottom: 24px;
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

/* Tags */
.si-opd-meta{
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

/* Categorie (primair) */
.si-opd-tag{
  display: inline-block;
  padding: 8px 10px;
  font-size: 14px;
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  border-radius: 5px;
  background: rgba(58, 137, 255, 0.12); /* #3a89ff met zachtere tint */
  color: #3a89ff;
  white-space: nowrap;
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

/* Opdracht preview (excerpt uit content) */
.si-opd-excerpt{
  font-family: 'Poppins', sans-serif;
  font-size: 14px;
  font-weight: 300; 
  color: #333333;
  margin: 0 0 10px 0;
  max-width: 80%; 

  /* Max 2 regels tonen */
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}


/* ==============================
   Responsive
   ============================== */

/* ==============================
   Responsive – Mobile optimalisatie
   ============================== */

/* Tablet en kleiner */
@media (max-width: 768px){
.si-opd-card-inner{
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
    padding: 16px;
  }

  .si-opd-title{
    font-size: 16px;
    margin-bottom: 6px;
  }

  .si-opd-excerpt{
    font-size: 13px;
    -webkit-line-clamp: 3; /* iets meer context op mobiel */
  }

  /* Tags compacter */
  .si-opd-meta{
    gap: 6px;
  }

  .si-opd-tag{
    padding: 6px 10px;
    font-size: 12px;
    border-radius: 6px;
  }

  /* CTA als "row" onder content, voelt geïntegreerd */
  .si-opd-side{
    display: none; 
  }

  .si-opd-cta{
    width: 100%;
    padding: 10px 12px;
    border-radius: 6px;
    background: rgba(58, 137, 255, 0.10);
    color: #3a89ff;
    font-weight: 600;
    justify-content: center; /* voelt als actie, zonder button */
  }

  .si-opd-cta::after{
    margin-left: 6px;
  }

  .si-opd-side {
    margin-top: 0;
  }
}

/* Kleinere telefoons */
@media (max-width: 480px){
  .si-opd-card{
    margin-top: 14px;
    margin-bottom: 14px;
  }

  .si-opd-card-inner{
    padding: 14px;
  }

  .si-opd-title{
    font-size: 15px;
  }

  .si-opd-tag{
    font-size: 11px;
    padding: 6px 9px;
  }

    .si-opd-side{
    display: none; 
  }

}


</style>