<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $post;

if ( job_manager_user_can_view_job_listing( $post->ID ) ) : ?>

  <div class="custom-top-section">
    <div class="top-section-text">
      <?php
      if ( function_exists( 'yoast_breadcrumb' ) ) {
        yoast_breadcrumb( '<p class="broodkruimels">','</p>' );
      }
      ?>
      <div>
        <a href="https://www.fondsen.org/vacature-alert-instellen/" class="top-section-link">Vacature Alert instellen</a>
      </div>
    </div>
  </div>

  <div class="sj-wrap">

    <div class="single_job_listing sj-card">
      <?php if ( get_option( 'job_manager_hide_expired_content', 1 ) && 'expired' === $post->post_status ) : ?>
        <div class="job-manager-info"><?php _e( 'This listing has expired.', 'wp-job-manager' ); ?></div>
      <?php else : ?>

        <?php
        $company_name    = function_exists('get_the_company_name') ? get_the_company_name() : '';
        $company_website = get_post_meta( $post->ID, '_company_website', true );
        ?>

        <div class="sj-meta">
          <span class="sj-chip">🗓️ <?php echo esc_html( date_i18n( 'j F Y', get_post_time( 'U' ) ) ); ?></span>
          <span class="sj-chip">🏷️ <?php the_job_type(); ?></span>
          <?php if ( ! empty( $company_name ) ) : ?>
            <?php
            $company_name = get_the_company_name();
            $company_slug = sanitize_title( $company_name );
            $company_url  = home_url( '/organisaties/' . $company_slug . '/' );
            ?>
        <a href="<?php echo esc_url( $company_url ); ?>" class="sj-chip sj-chip--link">
        🏢 <?php echo esc_html( $company_name ); ?>
        </a>
          <?php endif; ?>
        </div>

        <header class="sj-header">
          <h1 class="sj-title"><?php wpjm_the_job_title(); ?></h1>
          <?php if ( ! empty( $company_name ) ) : ?>
            <p class="sj-subtitle"><?php echo esc_html( $company_name ); ?></p>
          <?php endif; ?>
        </header>

        <div class="job_description sj-content">
          <?php wpjm_the_job_description(); ?>
        </div>

        <?php if ( ! empty( $company_website ) ) : ?>
          <div class="sj-actions">
            <a href="<?php echo esc_url( $company_website ); ?>" class="sj-btn" target="_blank" rel="noopener">
              Solliciteren bij werkgever
            </a>
          </div>
        <?php endif; ?>

        <?php do_action( 'single_job_listing_end' ); ?>

      <?php endif; ?>
    </div>

    <?php
    // ==============================
    // CONTACTGEGEVENS CONTACTPERSOON
    // ==============================
    $cf = get_post_meta( get_the_ID(), '_contact_first_name', true );
    $cl = get_post_meta( get_the_ID(), '_contact_last_name', true );
    $ce = get_post_meta( get_the_ID(), '_contact_email', true );

    $has_contact = ( ! empty($cf) || ! empty($cl) || ! empty($ce) );
    ?>

    <?php if ( $has_contact ) : ?>
      <section class="sj-contact sj-card">
        <h2 class="sj-contact-title">Contactpersoon - Sollicitaties</h2>

        <div class="sj-contact-grid">
          <?php if ( ! empty($cf) || ! empty($cl) ) : ?>
            <div class="sj-contact-row">
              <span class="sj-contact-label">Contactpersoon</span>
              <span class="sj-contact-value"><?php echo esc_html( trim($cf . ' ' . $cl) ); ?></span>
            </div>
          <?php endif; ?>

          <?php if ( ! empty($ce) ) : ?>
            <div class="sj-contact-row">
              <span class="sj-contact-label">E-mail</span>
              <a class="sj-contact-value sj-contact-link" href="mailto:<?php echo esc_attr( antispambot($ce) ); ?>">
                <?php echo esc_html( antispambot($ce) ); ?>
              </a>
            </div>
          <?php endif; ?>
        </div>
      </section>
    <?php endif; ?>


    <?php
    // ==============================
    // RECENTE VACATURES (6 items)
    // ==============================
    $current_id = get_the_ID();

    $recent_jobs = new WP_Query([
      'post_type'      => 'job_listing',
      'posts_per_page' => 6,
      'orderby'        => 'date',
      'order'          => 'DESC',
      'post__not_in'   => [ $current_id ],
      'post_status'    => 'publish',
    ]);
    ?>

    <?php if ( $recent_jobs->have_posts() ) : ?>
      <section class="sj-recent sj-card">
        <div class="sj-recent-head">
          <h2 class="sj-recent-title">Recente vacatures</h2>
          <p class="sj-recent-sub">Bekijk de nieuwste vacatures in ons netwerk.</p>
        </div>

        <ul class="sj-recent-grid">
          <?php while ( $recent_jobs->have_posts() ) : $recent_jobs->the_post(); ?>
            <?php
            $recent_company = function_exists('get_the_company_name') ? get_the_company_name() : '';
            $recent_title   = function_exists('wpjm_get_the_job_title') ? wpjm_get_the_job_title() : get_the_title();
            $recent_excerpt = wp_trim_words( get_the_excerpt(), 12, '…' );
            ?>
            <li class="sj-recent-item" <?php job_listing_class(); ?>>
             
            <a class="sj-recent-link" href="<?php the_job_permalink(); ?>" aria-label="<?php echo esc_attr( wpjm_get_the_job_title() ); ?>">

                <div class="sj-recent-logo">
                    <?php the_company_logo(); ?>
                </div>

                <div class="sj-recent-content">
                    <div class="sj-recent-company">
                    <?php echo esc_html( get_the_company_name() ); ?>
                    </div>

                    <h3 class="sj-recent-jobtitle">
                    <?php echo esc_html( wpjm_get_the_job_title() ); ?>
                    </h3>

                    <p class="sj-recent-excerpt">
                    <?php echo esc_html( wp_trim_words( get_the_excerpt(), 14, '…' ) ); ?>
                    </p>
                </div>

                </a>

            </li>
          <?php endwhile; ?>
        </ul>

        <?php wp_reset_postdata(); ?>
      </section>
    <?php endif; ?>

  </div>

<?php else : ?>
  <?php get_job_manager_template_part( 'access-denied', 'single-job_listing' ); ?>
<?php endif; ?>

<style>
    /* =========================================================
   Fondsen.org – Single job modern styling (Studentinhuren-ish)
   Gebruik: plak in je child theme style.css (liefst), of in template.
   ========================================================= */

/* ---------- Design tokens (veilig: overridable) ---------- */
:root{
  --sj-ink: #111827;
  --sj-muted: #6B7280;
  --sj-border: #E5E7EB;
  --sj-bg: #FBFAF8;
  --sj-card: #FFFFFF;
  --sj-blue: #0884CC;   /* Fondsen blauw */
  --sj-orange: #FF8C2C; /* Fondsen oranje */
  --sj-radius: 12px;
  --sj-shadow: 0 10px 40px -5px rgba(0,0,0,0.10);
}

/* ---------- Layout wrapper ---------- */
.sj-wrap{
  width: 900px;
  max-width: calc(100% - 24px);
  margin: 20px auto 40px;
  display: grid;
  gap: 16px;
}

/* ---------- Generic card ---------- */
.sj-card{
  background: var(--sj-card);
  border: 1px solid var(--sj-border);
  border-radius: var(--sj-radius);
  box-shadow: var(--sj-shadow);
}

/* ---------- Top section (breadcrumb + CTA) ---------- */
.custom-top-section{
  background-color: var(--sj-blue);
  color: #fff;
  padding: 20px;
  width: 100vw;
  margin-left: calc(-50vw + 50%);
  position: relative;
  display: flex;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
}

.top-section-text{
  width: 900px;
  max-width: calc(100% - 24px);
  margin: 0 auto;
  text-align: left;
}

.broodkruimels,
.broodkruimels a,
.broodkruimels span,
.broodkruimels .breadcrumb_last{
  color: #fff;
  margin: 0;
  font-family: Poppins, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  font-weight: 400;
  font-size: 14px;
  text-decoration: none;
  line-height: 1.4;
}

.broodkruimels a:hover{ opacity: .9; }

.top-section-link{
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-top: 14px;
  padding: 10px 14px;
  border-radius: 10px;
  background: rgba(255, 140, 44, 0.15);
  color: #fff !important;
  border: 1px solid rgba(255, 140, 44, 0.55);
  text-decoration: none !important;
  font-family: Poppins, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  font-weight: 700;
  font-size: 14px;
  transition: transform .15s ease, filter .15s ease, box-shadow .15s ease;
}

.top-section-link:hover{
  transform: translateY(-1px);
  filter: brightness(.98);
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
}

/* ---------- Single job card ---------- */
.single_job_listing{
  padding: 22px;
}

.job-manager-info{
  padding: 14px 16px;
  border: 1px solid var(--sj-border);
  border-radius: 10px;
  background: #fff;
  font-family: Poppins, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  color: var(--sj-ink);
}

/* Meta chips */
.sj-meta{
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 14px;
}

.sj-chip{
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 10px;
  border-radius: 999px;
  border: 1px solid #DEDEDE;
  background: white; 
  color: #333; 
  font-family: Poppins; 
  font-weight: 700;
  font-size: 14px;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
}

a.sj-chip.sj-chip--link {
    color: #333; 
    text-decoration: none; 
}

/* Header */
.sj-header{
  padding-bottom: 14px;
  border-bottom: 1px solid var(--sj-border);
  margin-bottom: 16px;
  margin-top: 24px; 
}

.sj-title{
  margin: 0;
  font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  font-size: 26px;
  line-height: 1.15;
  font-weight: 800;
  color: var(--sj-ink);
}

.sj-subtitle{
  margin: 8px 0 0 0;
  font-family: Poppins, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  font-size: 14px;
  font-weight: 600;
  color: var(--sj-blue);
}

/* Content / job description */
.sj-content{
  font-family: Poppins, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  color: var(--sj-ink);
  font-size: 15px;
  line-height: 1.75;
}

.sj-content p{ margin: 0 0 14px 0; }
.sj-content h2, .sj-content h3{
  font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  color: var(--sj-ink);
  margin: 20px 0 10px;
  line-height: 1.25;
}
.sj-content ul, .sj-content ol{
  margin: 0 0 14px 18px;
}

/* CTA */
.sj-actions{
  margin-top: 18px;
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.sj-btn{
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px;
  border-radius: 5px;
  background: rgba(8, 132, 204, 0.15);

  color: var(--sj-blue) !important;
  text-decoration: none !important;
  border: 1px solid var(--sj-blue);
  font-family: Poppins, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  font-weight: 700;
  font-size: 14px;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  transition: transform .15s ease, filter .15s ease, box-shadow .15s ease;
}

.sj-btn:hover{
  transform: translateY(-1px);
  filter: brightness(.98);
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
}

/* Hide default entry title if theme outputs it */
h1.entry-title{ display:none; }



/* ==============================
   CONTACT CARD (Studentinhuren look)
   ============================== */

.sj-contact{
  padding: 22px 26px;            /* lekker ruim */
}

.sj-contact-title{
  margin: 0 0 18px 0;
  font-family: Poppins;
  font-weight: 700;
  font-size: 20px;
  color: var(--sj-ink);
}

/* 2 kolommen zoals in Studentinhuren */
.sj-contact-grid{
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 28px 56px;
  align-items: start;
}

/* rij: label boven value */
.sj-contact-row{
  display: grid;
  gap: 8px;
  min-width: 0;
}

.sj-contact-label{
  font-family: Poppins;
  font-size: 16px;
  font-weight: 400;
  color: #7A7F87;                /* lichtgrijs label */
}

.sj-contact-value{
  font-family: Poppins;
  font-size: 16px;
  font-weight: 700;
  color: #333;
  line-height: 1.25;
  word-break: break-word;
}

/* e-mail link in “Studentinhuren paars” */
.sj-contact-link{
  color: var(--sj-blue) !important;
  text-decoration: none !important;
  font-weight: 700;
}

.sj-contact-link:hover{
  text-decoration: underline !important;
  opacity: 0.95;
}

/* Mobile: onder elkaar */
@media (max-width: 768px){
  .sj-contact{
    padding: 18px 18px;
  }

  .sj-contact-grid{
    grid-template-columns: 1fr;
    gap: 18px;
  }

  .sj-contact-title{
    font-size: 20px;
    margin-bottom: 14px;
  }

  .sj-contact-label{
    font-size: 15px;
  }

  .sj-contact-value{
    font-size: 17px;
  }
}


/* ---------- Recente vacatures ---------- */
/* ==============================
   RECENTE VACATURES – MODERN CARD GRID
   ============================== */

.sj-recent{
  padding: 24px;
}

.sj-recent-head{
  margin-bottom: 18px;
}

.sj-recent-title{
  margin: 0;
  font-family: Inter, system-ui, sans-serif;
  font-weight: 800;
  font-size: 20px;
  color: var(--sj-ink);
}

.sj-recent-sub{
  margin-top: 6px;
  font-family: Poppins, system-ui, sans-serif;
  font-size: 14px;
  color: var(--sj-muted);
}

/* GRID */
.sj-recent-grid{
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 18px;
}

/* CARD */
.sj-recent-item{
  background: #fff;
  border: 1px solid var(--sj-border);
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 12px 32px rgba(16,24,40,.08);
  transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}

.sj-recent-item:hover{
  transform: translateY(-3px);
  border-color: rgba(8,132,204,.35);
  box-shadow: 0 18px 44px rgba(16,24,40,.14);
}

/* LINK = VERTICAL LAYOUT */
.sj-recent-link{
  display: flex;
  flex-direction: column;
  gap: 14px;
  padding: 18px;
  text-decoration: none !important;
  color: inherit;
}

/* LOGO BOVEN */

/* ==============================
   LOGO – ZONDER WRAPPER STYLING
   ============================== */

.sj-recent-logo{
  display: block;
}

/* WP Job Manager logo */
.sj-recent-logo img{
  display: block;
  width: 100px;
  height: auto;
  max-height: 100px;
  object-fit: contain;
  border: 1px solid #DEDEDE; 
  border-radius: 5px; 
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);

}


/* CONTENT ONDER LOGO */
.sj-recent-body{
  display: flex;
  flex-direction: column;
  gap: 6px;
}

/* BEDRIJFSNAAM */
.sj-recent-company{
  font-family: Poppins, system-ui, sans-serif;
  font-size: 13px;
  font-weight: 600;
  color: var(--sj-blue);
}

/* TITEL */
.sj-recent-jobtitle{
  margin: 0;
  font-family: Inter, system-ui, sans-serif;
  font-size: 18px;
  font-weight: 800;
  line-height: 1.25;
  color: var(--sj-ink);

  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* EXCERPT */
.sj-recent-excerpt{
  margin: 0;
  font-family: Poppins, system-ui, sans-serif;
  font-size: 14px;
  line-height: 1.5;
  color: var(--sj-muted);

  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.sj-recent-item:hover .sj-recent-jobtitle{
  color: var(--sj-blue);
}

/* ==============================
   RESPONSIVE
   ============================== */

@media (max-width: 900px){
  .sj-recent-grid{
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 520px){
  .sj-recent-grid{
    grid-template-columns: 1fr;
  }

  .sj-recent-logo{
    min-height: 120px;
  }

  .sj-recent-logo img{
    width: 110px;
    height: 110px;
  }
}


@media (max-width: 600px){
  /* voorkom 100vw overflow op mobiel */
  .custom-top-section{
    width: 100% !important;
    margin-left: 0 !important;
  }

  .sj-wrap{
    max-width: calc(100% - 20px);
    margin: 16px auto 30px;
  }

  .single_job_listing,
  .sj-contact,
  .sj-recent{
    padding: 16px;
  }

  .sj-title{
    font-size: 22px;
  }



  .sj-contact-row{
    grid-template-columns: 1fr;
  }

  .top-section-link{
    width: 100%;
    margin-top: 12px;
  }
}

</style>