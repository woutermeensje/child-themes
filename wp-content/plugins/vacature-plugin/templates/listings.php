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

        // Cover = featured image (uitgelichte afbeelding)
        $cover_url = get_the_post_thumbnail_url($post_id, 'large');

        // Logo thumbnail = extra meta (attachment ID)
        $logo_id = (int) get_post_meta($post_id, '_vp_logo_id', true);
        $logo_html = '';
        if ($logo_id) {
          $logo_html = wp_get_attachment_image($logo_id, 'thumbnail', false, [
            'class'   => 'vpjobs-logo-img',
            'loading' => 'lazy',
            'alt'     => esc_attr(get_the_title($post_id) . ' logo'),
          ]);
        } else {
          $logo_html = '<div class="vpjobs-logo-fallback" aria-hidden="true"></div>';
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

        // Tax badges: max 1 per taxonomy
        $taxes = [
          'vp_category'  => true,
          'vp_org_type'  => true,
          'bedrijfsnaam' => true,
        ];

        $cta_text = vp_setting('listings_cta_text', 'Vacature bekijken');
        $note = $loc ? $loc : '';
      ?>

      <a class="vpjobs-row" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">

        <!-- DESKTOP/TABLET -->
        <div class="vpjobs-card__desktop">

          <div class="vpjobs-card__media">
            <?php if ($badge): ?>
              <span class="vpjobs-badge"><?php echo esc_html($badge); ?></span>
            <?php endif; ?>

            <div class="vpjobs-bg-wrap">
              <div
                class="vpjobs-bg"
                style="<?php echo $cover_url ? 'background-image:url(' . esc_url($cover_url) . ');' : ''; ?>"
                aria-hidden="true"
              ></div>
              <div class="vpjobs-bg-overlay" aria-hidden="true"></div>

              <div class="vpjobs-logo-absolute">
                <div class="vpjobs-logo-wrapper">
                  <?php echo $logo_html; ?>
                </div>
              </div>
            </div>
          </div>

          <div class="vpjobs-card__content">
            <div class="vpjobs-content-inner">

              <h3 class="vpjobs-row-title"><?php the_title(); ?></h3>

              <p class="vpjobs-row-excerpt">
                <?php echo esc_html($excerpt); ?>
              </p>

              <?php
                $badges = [];
                foreach ($taxes as $tax => $enabled) {
                  if (!$enabled) continue;
                  $terms = wp_get_post_terms($post_id, $tax, ['fields' => 'names', 'number' => 1]);
                  if (is_wp_error($terms) || empty($terms)) continue;
                  $badges[] = $terms[0];
                }
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
                  <span class="vpjobs-row-note">📍 <?php echo esc_html($note); ?></span>
                <?php endif; ?>
              </div>

            </div>
          </div>

        </div>

        <!-- MOBILE -->
        <div class="vpjobs-card__mobile">
          <div class="vpjobs-m-top">
            <div class="vpjobs-m-logo">
              <?php echo $logo_html; ?>
            </div>

            <div class="vpjobs-m-toptext">
              <div class="vpjobs-m-company">
                <?php
                  // Als je ‘bedrijfsnaam’ tax gebruikt:
                  $org = wp_get_post_terms($post_id, 'bedrijfsnaam', ['fields' => 'names', 'number' => 1]);
                  echo (!is_wp_error($org) && !empty($org)) ? esc_html($org[0]) : '';
                ?>
              </div>
              <div class="vpjobs-m-title"><?php the_title(); ?></div>
            </div>
          </div>

          <div class="vpjobs-m-excerpt">
            <?php echo esc_html( wp_trim_words($text, 10, '…') ); ?>
          </div>

          <ul class="vpjobs-m-meta">
            <?php if ($loc): ?>
              <li class="vpjobs-m-meta-item"><span class="vpjobs-m-ic">📍</span><span class="vpjobs-m-tx"><?php echo esc_html($loc); ?></span></li>
            <?php endif; ?>

            <?php if ($badge): ?>
              <li class="vpjobs-m-meta-item"><span class="vpjobs-m-ic">⏰</span><span class="vpjobs-m-tx"><?php echo esc_html($badge); ?></span></li>
            <?php endif; ?>

            <li class="vpjobs-m-meta-item"><span class="vpjobs-m-ic">📅</span><span class="vpjobs-m-tx"><?php echo esc_html( human_time_diff(get_the_time('U'), current_time('timestamp')) ); ?> geleden</span></li>
          </ul>

          <div class="vpjobs-m-cta"><?php echo esc_html($cta_text); ?></div>
        </div>

      </a>

    <?php endwhile; wp_reset_postdata(); ?>
  <?php else: ?>
    <div class="vpjobs-empty">Geen vacatures gevonden.</div>
  <?php endif; ?>
</div>


<style>

  /* =========================================================
   VP Jobs – Listings (Fondsen.org stijl)
   ========================================================= */

.vpjobs-listings-header{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:16px;
  border:1px solid #E0E0E0;
  box-shadow:0 10px 40px -5px rgba(0,0,0,.15);
  padding:20px 24px;
  margin:24px 0;
  background:#fff;
  border-radius:6px;
}

.vpjobs-results-title{
  margin:0;
  font-size:18px !important;
  line-height:1.2;
  color:#333;
  font-family:Poppins, system-ui, sans-serif;
}

.vpjobs-alerts-link{
  display:inline-flex;
  align-items:center;
  padding:10px 16px;
  border:1px solid #0884CC;
  background:#EAF2FF;
  color:#0884CC !important;
  font-family:Poppins, system-ui, sans-serif;
  font-size:15px;
  font-weight:700;
  line-height:1;
  text-decoration:none;
  border-radius:6px;
  transition:all .2s ease;
}
.vpjobs-alerts-link:hover{ filter:brightness(.98); }

/* Card wrapper (klikbaar) */
.vpjobs-row{
  display:block;
  text-decoration:none !important;
  color:inherit;
  margin:16px 0;
}

/* Desktop wrapper */
.vpjobs-card__desktop{
  width:100%;
  display:flex;
  flex-wrap:nowrap;
  background:#fff;
  border-radius:6px;
  box-shadow:0 10px 40px -5px rgba(0,0,0,.15);
  border:1px solid #E0E0E0;
  overflow:hidden;
}

.vpjobs-card__media{
  position:relative;
  flex:0 0 40%;
  max-width:40%;
  min-height:318px;
}

.vpjobs-bg-wrap{
  position:relative;
  width:100%;
  height:100%;
  overflow:hidden;
}

.vpjobs-bg{
  width:100%;
  height:318px;
  background-size:cover;
  background-repeat:no-repeat;
  background-position:center center;
  background-color:#f2f2f2;
}

.vpjobs-bg-overlay{
  position:absolute;
  inset:0;
  background:#0884CC;
  opacity:.30;
}

.vpjobs-badge{
  position:absolute;
  top:14px;
  right:14px;
  z-index:5;
  background:#FFF4DD;
  color:#7A5200;
  border:2px solid #F4B740;
  padding:8px 14px;
  border-radius:6px;
  font-family:Poppins, system-ui, sans-serif;
  font-weight:800;
  font-size:14px;
  line-height:1;
}

.vpjobs-logo-absolute{
  position:absolute;
  left:24px;
  bottom:24px;
  z-index:9;
  width:80px;
  height:80px;
  border-radius:999px;
  background:#fff;
  display:grid;
  place-items:center;
  border:1px solid #eee;
}

.vpjobs-logo-wrapper{
  width:80px;
  height:80px;
  border-radius:999px;
  display:grid;
  place-items:center;
  overflow:hidden;
}

.vpjobs-logo-img{
  width:42px;
  height:42px;
  object-fit:contain;
  border-radius:999px;
}

.vpjobs-logo-fallback{
  width:42px;
  height:42px;
  border-radius:999px;
  background:#f2f2f2;
}

/* Content */
.vpjobs-card__content{
  flex:0 0 60%;
  max-width:60%;
  display:flex;
  align-items:center;
  min-width:0;
}

.vpjobs-content-inner{
  padding:0 40px;
  min-width:0;
  width:100%;
}

.vpjobs-row-title{
  margin:0 0 8px 0;
  font-family:"Balgin-Bold", Poppins, system-ui, sans-serif;
  font-size:20px !important;
  color:#333;
  overflow-wrap:anywhere;
  word-break:break-word;
}

.vpjobs-row-excerpt{
  margin:18px 0 16px;
  font-family:Poppins, system-ui, sans-serif;
  font-size:14px;
  line-height:1.6;
  color:#333;
  font-weight:400;
}

/* Tax badges */
.vpjobs-row-tax{
  display:flex;
  flex-wrap:wrap;
  gap:10px;
  margin:0 0 18px;
}
.vpjobs-tax-badge{
  display:inline-flex;
  align-items:center;
  padding: 8px;
  border-radius:999px;
  font-size:13px;
  font-family:Poppins, system-ui, sans-serif;
  font-weight:700;
  white-space:nowrap;
  background: white; 
  border:1px solid #DEDEDE;
  color: #333; 
}

/* CTA */
.vpjobs-row-cta{
  display:flex;
  align-items:center;
  gap:16px;
  flex-wrap:wrap;
  margin-top:18px;
}

.vpjobs-row-btn{
  display:inline-block;
  background:#0884CC;
  color:#fff;
  padding:0 30px;
  height:48px;
  line-height:48px;
  border-radius:0;
  font-family:"Balgin-Bold", Poppins, system-ui, sans-serif;
  font-size:15px;
  text-align:center;
  box-shadow:0 2px 6px rgba(0,0,0,.08);
  max-width:50%;
}

.vpjobs-row-note{
  font-family:Poppins, system-ui, sans-serif;
  font-size:14px;
  color:#333;
}

/* Empty */
.vpjobs-empty{
  border:1px solid #E0E0E0;
  border-radius:6px;
  background:#fff;
  padding:16px;
  box-shadow:0 10px 40px -5px rgba(0,0,0,.15);
  font-family:Poppins, system-ui, sans-serif;
}

/* ========== Mobile layout ========== */
.vpjobs-card__mobile{ display:none; }

@media (max-width:960px){
  .vpjobs-card__desktop{ display:none; }
  .vpjobs-card__mobile{
    display:block;
    background:#fff;
    border-radius:6px;
    box-shadow:0 10px 40px -5px rgba(0,0,0,.15);
    border:1px solid #E0E0E0;
    padding:18px;
  }

  .vpjobs-m-top{
    display:flex;
    gap:14px;
    align-items:center;
    min-width:0;
  }
  .vpjobs-m-logo{
    width:56px; height:56px;
    border-radius:999px;
    background:#fff;
    border:1px solid #eee;
    display:grid;
    place-items:center;
    overflow:hidden;
    flex:0 0 auto;
  }
  .vpjobs-m-logo img{ width:42px; height:42px; object-fit:contain; border-radius:999px; }

  .vpjobs-m-toptext{ min-width:0; }
  .vpjobs-m-company{
    font-family:Poppins, system-ui, sans-serif;
    font-weight:800;
    font-size:14px;
    color:#111827;
    line-height:1.2;
  }
  .vpjobs-m-title{
    margin-top:4px;
    font-family:Poppins, system-ui, sans-serif;
    font-weight:800;
    font-size:18px;
    line-height:1.25;
    color:#111827;
    overflow-wrap:anywhere;
    word-break:break-word;
  }

  .vpjobs-m-excerpt{
    margin-top:12px;
    font-family:Poppins, system-ui, sans-serif;
    font-size:14px;
    line-height:1.6;
    color:#333;
  }

  .vpjobs-m-meta{
    list-style:none;
    padding:0;
    margin:14px 0 0 0;
    display:grid;
    gap:8px;
  }
  .vpjobs-m-meta-item{
    display:flex;
    gap:10px;
    align-items:center;
    min-width:0;
  }
  .vpjobs-m-ic{ width:18px; text-align:center; flex:0 0 auto; }
  .vpjobs-m-tx{
    font-family:Poppins, system-ui, sans-serif;
    font-size:14px;
    color:#111827;
    min-width:0;
    overflow-wrap:anywhere;
    word-break:break-word;
  }

  .vpjobs-m-cta{
    margin-top:14px;
    display:inline-block;
    background:#0884CC;
    color:#fff;
    padding:0 24px;
    height:44px;
    line-height:44px;
    font-family:"Balgin-Bold", Poppins, system-ui, sans-serif;
    font-size:15px;
    border-radius:0;
    text-align:center;
  }
}

@media (max-width:640px){
  .vpjobs-listings-header{
    flex-direction:column;
    align-items:stretch;
    gap:14px;
  }
  .vpjobs-alerts-link{ width:100%; justify-content:center; }
}

</style>