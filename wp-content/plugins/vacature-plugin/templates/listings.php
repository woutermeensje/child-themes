<?php if (!defined('ABSPATH')) exit; ?>
<?php /** @var WP_Query $query */ ?>

<div class="vpjobs-listings">
 <h2 class="vpjobs-results-title">
          <?php echo esc_html( vp_setting('listings_heading', 'Doorzoek alle vacatures') ); ?>
        </h2>

  <?php if ($query->have_posts()): ?>
    <?php while ($query->have_posts()): $query->the_post(); ?>
      <?php
        $post_id = get_the_ID();
        $loc = get_post_meta($post_id, '_vp_location', true);

        // Logo: featured image (thumbnail)
        $logo_html = '';
        if (has_post_thumbnail($post_id)) {
          $logo_html = get_the_post_thumbnail($post_id, 'thumbnail', [
            'class' => 'vpjobs-logo-img',
            'loading' => 'lazy',
            'alt' => esc_attr(get_the_title($post_id)),
          ]);
        } else {
          // Fallback: lege placeholder (zodat layout niet springt)
          $logo_html = '<div class="vpjobs-logo-placeholder" aria-hidden="true"></div>';
        }
      ?>

      <article class="vpjobs-card">
        <a class="vpjobs-card-link" href="<?php the_permalink(); ?>">

          <div class="vpjobs-card-row">
            <div class="vpjobs-logo">
              <?php echo $logo_html; ?>
            </div>

            <div class="vpjobs-card-content">
              <h3 class="vpjobs-title"><?php the_title(); ?></h3>

              <div class="vpjobs-meta">
                <?php
                  // 1) Locatie (meta veld) - blijft 1 pill
                  if ($loc) {
                    echo '<span class="vpjobs-pill">' . esc_html($loc) . '</span>';
                  }

                  // 2) Per taxonomy maximaal 1 term tonen
                  $taxes = [
                    'vp_job_type' => true,
                    'vp_category' => true,
                    'vp_org_type' => true,
                    'bedrijfsnaam'    => true,
                    // 'bedrijfsnaam' => true, // (optioneel) als je deze ook wilt tonen
                  ];

                  foreach ($taxes as $tax => $enabled) {
                    if (!$enabled) continue;

                    // Haal 1 term op (de "eerste" gekoppelde term)
                    $terms = wp_get_post_terms($post_id, $tax, [
                      'fields' => 'names',
                      'number' => 1,
                    ]);

                    if (is_wp_error($terms) || empty($terms)) continue;

                    // $terms is array met max 1 item
                    echo '<span class="vpjobs-pill">' . esc_html($terms[0]) . '</span>';
                  }
                ?>
              </div>

              <div class="vpjobs-excerpt">
                <?php echo esc_html( wp_trim_words( get_the_excerpt(), 10, '…' ) ); ?>              
              </div>
            </div>
          </div>

        </a>
      </article>
    <?php endwhile; wp_reset_postdata(); ?>
  <?php else: ?>
    <div class="vpjobs-empty">
      Geen vacatures gevonden.
    </div>
  <?php endif; ?>
</div>


<style>
  .vpjobs-listings{
  display:grid;
  gap:16px;
  padding: 20px 0;
}

.vpjobs-card{
  border:1px solid #DEDEDE;
  border-radius:5px;
  background:#fff;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  overflow:hidden;
}

.vpjobs-card-link{
  display:block;
  padding:16px;
  color:inherit;
  text-decoration: none !important;
  font-size: 36px !important;; 
}

.vpjobs-title{ 
 
  font-size: 18px !important; 
}

.vpjobs-meta{
  display:flex;
  flex-wrap:wrap;
  gap:8px;
  margin-bottom:8px;
}

/* =========================
   VP Jobs – Meta pills kleuren
   ========================= */

.vpjobs-meta{
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

/* =========================
   VP Jobs – Meta pills kleuren (aangepast)
   ========================= */

.vpjobs-meta{
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

/* Basis pill */
.vpjobs-pill{
  display: inline-flex;
  align-items: center;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 14px;
  font-weight: 500;
  border: 1px solid;
  line-height: 1;
}

/* 🔵 Blauw */
.vpjobs-pill:nth-child(4n+1){
  color: #2979FF;
  border-color: #2979FF;
  background-color: rgba(41, 121, 255, 0.12);
}

/* 🟣 Paars */
.vpjobs-pill:nth-child(4n+2){
  color: #7C4DFF;
  border-color: #7C4DFF;
  background-color: rgba(124, 77, 255, 0.12);
}

/* 🌸 Roze */
.vpjobs-pill:nth-child(4n+3){
  color: #FF4081;
  border-color: #FF4081;
  background-color: rgba(255, 64, 129, 0.12);
}

/* 🟠 Oranje (behouden) */
.vpjobs-pill:nth-child(4n+4){
  color: #EF6C00;
  border-color: #EF6C00;
  background-color: rgba(239, 108, 0, 0.12);
}

.vpjobs-excerpt{ margin:0; color:#333; font-weight: 300; font-size: 14px}

.vpjobs-empty{
  border:1px solid #DEDEDE;
  border-radius:5px;
  background:#fff;
  padding:16px;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
}

/* =========================
   Vacature Plugin - SINGLE
   ========================= */

.vpjobs-single{
  border:1px solid #DEDEDE;
  border-radius:5px;
  background:#fff;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  padding:20px;
}

.vpjobs-apply{
  display:inline-block;
  border:1px solid #DEDEDE;
  border-radius:5px;
  padding: 12px 16px;
  text-decoration:none;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
}


/* Logo in listing card */
.vpjobs-card-row{
  display: flex;
  gap: 16px;
  align-items: flex-start;
}

.vpjobs-logo{
  flex: 0 0 70px;
  width: 70px;
  height: 70px;
  border: 1px solid #DEDEDE;
  border-radius: 5px;
  background: #fff;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.vpjobs-logo-img{
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
}

.vpjobs-logo-placeholder{
  width: 100%;
  height: 100%;
  background: #f2f2f2;
}

.vpjobs-card-content{
  flex: 1 1 auto;
  min-width: 0;
}
</style>