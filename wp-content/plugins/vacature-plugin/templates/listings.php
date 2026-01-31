<?php if (!defined('ABSPATH')) exit; ?>
<?php /** @var WP_Query $query */ ?>

<div class="vpjobs-listings">

  <div class="vpjobs-listings-header">
    <h2 class="vpjobs-results-title">
      <?php echo esc_html(vp_setting('listings_heading', 'Doorzoek alle vacatures')); ?>
    </h2>

    <a href="" class="vpjobs-alerts-link">Vacature Alerts</a>
  </div>

  <?php if ($query->have_posts()): ?>
    <?php while ($query->have_posts()): $query->the_post(); ?>
      <?php
        $post_id = get_the_ID();

        // Locatie meta (optioneel)
        $loc = get_post_meta($post_id, '_vp_location', true);

        // Featured image als "logo/afbeelding"
        if (has_post_thumbnail($post_id)) {
          $img_html = get_the_post_thumbnail($post_id, 'medium_large', [
            'loading' => 'lazy',
            'class'   => 'vpjobs-row-img',
            'alt'     => esc_attr(get_the_title($post_id)),
          ]);
        } else {
          $img_html = '<div class="vpjobs-row-img-placeholder" aria-hidden="true"></div>';
        }

        // Badge rechtsboven: bijv. Job type (eerste term)
        $badge = '';
        $job_type_terms = wp_get_post_terms($post_id, 'vp_job_type', ['fields' => 'names', 'number' => 1]);
        if (!is_wp_error($job_type_terms) && !empty($job_type_terms)) {
          $badge = $job_type_terms[0];
        }

        // Excerpt fallback
        $text = get_the_excerpt();
        if (!$text) $text = wp_strip_all_tags(get_the_content());
        $excerpt = wp_trim_words($text, 22, '…');

        // Tax badges: max 1 per taxonomy (zoals jij wil)
        $taxes = [
          'vp_category'  => true,
          'vp_org_type'  => true,
          'bedrijfsnaam' => true,
          // 'vp_regio'   => true, // als je hem toch wilt tonen
        ];

        // CTA tekst + "prijsnote" (gebruik je plugin setting, anders default)
        $cta_text = vp_setting('listings_cta_text', 'Bekijk vacature');
        $note = $loc ? $loc : '';
      ?>

      <a class="vpjobs-row" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">

        <?php if ($badge): ?>
          <span class="vpjobs-badge"><?php echo esc_html($badge); ?></span>
        <?php endif; ?>

        <div class="vpjobs-row-image">
          <?php echo $img_html; ?>
        </div>

        <div class="vpjobs-row-content">
          <h3 class="vpjobs-row-title"><?php the_title(); ?></h3>

          <p class="vpjobs-row-excerpt">
            <?php echo esc_html($excerpt); ?>
          </p>

          <?php
          // Tax badges blok
          $badges = [];
          foreach ($taxes as $tax => $enabled) {
            if (!$enabled) continue;

            $terms = wp_get_post_terms($post_id, $tax, ['fields' => 'names', 'number' => 1]);
            if (is_wp_error($terms) || empty($terms)) continue;

            $badges[] = $terms[0];
          }

          // Optioneel: locatie ook als badge in dit blok
          // (als je hem liever bij badges wil dan als note)
          // if ($loc) $badges[] = '📍 ' . $loc;
          ?>

          <?php if (!empty($badges)): ?>
            <div class="vpjobs-row-tax">
              <?php foreach ($badges as $b): ?>
                <span class="vpjobs-tax-badge"><?php echo esc_html($b); ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <div class="vpjobs-row-cta">
            <span class="vpjobs-row-btn"><?php echo esc_html($cta_text); ?></span>

            <?php if ($note): ?>
              <span class="vpjobs-row-note"><?php echo esc_html($note); ?></span>
            <?php endif; ?>
          </div>

        </div>
      </a>

    <?php endwhile; wp_reset_postdata(); ?>
  <?php else: ?>
    <div class="vpjobs-empty">Geen vacatures gevonden.</div>
  <?php endif; ?>
</div>

<style>
  /* =========================================================
   VP Jobs – Listings (Modulairehuisvesting stijl)
   ========================================================= */

/* ===== Header boven listings ===== */



.vpjobs-listings-header{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:16px;
  border:1px solid #ededed;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,.15);
  padding: 20px 24px;
  margin: 24px 0;
  background:#fff;
     text-decoration: none !important;
    border-radius: 5px;

}

.vpjobs-results-title{
  margin:0;
  font-size:18px !important;
  line-height:1.2;
  color:#333;
  font-family: Poppins !important, sans-serif; 
   text-decoration: none !important;

}

.vpjobs-alerts-link{
  display: inline-flex;
  align-items: center;
  padding: 10px 16px;
  border-radius: 10px;
  border: 2px solid #BFD7FF;      /* lichtblauw kader */
  background-color: #EAF2FF;      /* lichtblauwe achtergrond */
  color: #0456ABFA !important;                 /* donkerblauwe tekst */
  font-family: Poppins, sans-serif;
  font-size: 16px;
  font-weight: 600;
  line-height: 1;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.15s ease-in-out;
  border-radius: 5px;
}

/* ===== Listing card ===== */
.vpjobs-row{
  position: relative;
  display:flex;
  width:100%;
  border:1px solid #ededed;
  overflow:hidden;
  text-decoration:none;
  background:#fff;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,.15);
  margin: 24px 0;
  height: 325px; /* desktop vaste hoogte zoals MH */
  border-radius: 5px;
}

/* Hover */
.vpjobs-row:hover{
  border-color:#ccc;
}

/* ===== Badge rechtsboven ===== */
.vpjobs-badge{
  position:absolute;
  top:14px;
  right:14px;
  z-index:5;
  background:#FFF4DD;
  color:#7A5200;
  border:2px solid #F4B740;
  padding: 8px 14px;
  border-radius:5px;
  font-family:Poppins, sans-serif;
  font-weight:700;
  font-size:15px;
  line-height:1;
}

/* ===== Afbeelding links (40%) ===== */
.vpjobs-row-image{
  flex: 0 0 40%;
  position: relative;
  background:#f5f5f5;
  overflow:hidden;
}

/* Featured image vult volledig */
.vpjobs-row-image img,
.vpjobs-row-img{
  width:100%;
  height:100%;
  object-fit:cover;
  display:block;
}

/* Placeholder */
.vpjobs-row-img-placeholder{
  width:100%;
  height:100%;
  background:#f2f2f2;
}

/* ===== Content rechts ===== */
.vpjobs-row-content{
  flex:1;
  padding: 16px 18px;
  display:flex;
  flex-direction:column;
  justify-content:center;
  font-family:Poppins, sans-serif;
  color:#333;
  font-size:15px;
}

.vpjobs-row-title{
  margin: 0 0 16px;
  line-height:1.2;
  font-size:20px !important;
  color:#333;
  font-family: Poppins, sans-serif;
     text-decoration: none !important;
     font-weight: 600;

}

h3.vpjobs-row-title {
  text-decoration: none !important; 
}

.vpjobs-row-excerpt{
  margin: 0 0 20px;
  line-height:1.5;
  font-size:14px;
  font-weight:300;
}

/* ===== Tax badges ===== */
.vpjobs-row-tax{
  display:flex;
  flex-wrap:wrap;
  gap:10px;
  margin: 0 0 18px;
}

.vpjobs-tax-badge{
  display:inline-flex;
  align-items:center;
  padding: 6px 12px;
  border-radius:5px;
  font-size:13px;
  font-family:Poppins, sans-serif;
  font-weight:700;
  white-space:nowrap;
  background:#EAF2FF;
  border:2px solid #C7DBFF;
  color:#0456AB;
}

/* ===== CTA ===== */
.vpjobs-row-cta{
  display:flex;
  align-items:center;
  gap:18px;
  margin-top:16px;
  flex-wrap:wrap;
}

.vpjobs-row-btn{
  padding:14px;
  border-radius:5px;
  min-width:180px;
  background-color:#0456ABFA;
  color:#fff;
  font-family: Balgin Bold, Poppins, sans-serif;
  font-size:14px;
  text-align:center;
}

.vpjobs-row-note{
  padding:14px;
  color:#333;
  font-family:Poppins, sans-serif;
  font-size:15px;
  font-weight:400;
}

/* ===== Empty state ===== */
.vpjobs-empty{
  border:1px solid #DEDEDE;
  border-radius:5px;
  background:#fff;
  padding:16px;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,.15);
  font-family:Poppins, sans-serif;
}

/* ===== Mobile ===== */
@media (max-width:700px){

  .vpjobs-row{
    flex-direction:column;
    height:auto;
    margin:16px 0;
  }

  .vpjobs-row-image{
    width:100%;
    height:200px;
    flex:0 0 auto;
  }

  .vpjobs-row-content{
    padding: 14px;
  }

  .vpjobs-row-title{
    font-size:20px;
    margin-bottom:10px;
  }

  .vpjobs-row-excerpt{
    font-size:14px;
    margin-bottom:14px;
  }

  .vpjobs-row-btn{
    min-width:auto;
    padding:10px 12px;
  }

  .vpjobs-badge{
    top:10px;
    right:10px;
    padding:7px 12px;
    font-size:12px;
  }
}
</style>