<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $post;

if ( job_manager_user_can_view_job_listing( $post->ID ) ) : ?>

<div class="sj-wrap">
    
<div class="custom-top-section">
    <div class="top-section-text">
        <p>
            Blijf op de hoogte van de laatste vacatures!
        </p>
     <div>
        <a href="https://www.fondsen.org/vacature-alert-instellen/" class="top-section-link">Vacature Alert instellen</a>
      </div>
    </div>
</div>

  

  

    <div class="sj-card">
      <?php if ( get_option( 'job_manager_hide_expired_content', 1 ) && 'expired' === $post->post_status ) : ?>
        <div class="job-manager-info"><?php _e( 'This listing has expired.', 'wp-job-manager' ); ?></div>
      <?php else : ?>

        <?php
        $company_name    = function_exists('get_the_company_name') ? get_the_company_name() : '';
        $company_website = get_post_meta( $post->ID, '_company_website', true );

        $company_slug = $company_name ? sanitize_title( $company_name ) : '';
        $company_url  = $company_slug ? home_url( '/organisaties/' . $company_slug . '/' ) : '';
        ?>

        <div class="sj-meta">
          <span class="sj-chip">🗓️ <?php echo esc_html( date_i18n( 'j F Y', get_post_time( 'U' ) ) ); ?></span>
          <span class="sj-chip">🏷️ <?php the_job_type(); ?></span>

          <?php if ( ! empty( $company_name ) && ! empty( $company_url ) ) : ?>
            <a href="<?php echo esc_url( $company_url ); ?>" class="sj-chip sj-chip--link">🏢 <?php echo esc_html( $company_name ); ?></a>
          <?php elseif ( ! empty( $company_name ) ) : ?>
            <span class="sj-chip">🏢 <?php echo esc_html( $company_name ); ?></span>
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
        <h2 class="sj-contact-title">Contactpersoon Vacature</h2>

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
              $rc_name    = function_exists('get_the_company_name') ? get_the_company_name() : '';
              $rc_title   = function_exists('wpjm_get_the_job_title') ? wpjm_get_the_job_title() : get_the_title();
              $rc_excerpt = wp_trim_words( get_the_excerpt(), 14, '…' );
            ?>
            <li class="sj-recent-item" <?php job_listing_class(); ?>>
              <a class="sj-recent-link" href="<?php the_job_permalink(); ?>" aria-label="<?php echo esc_attr( $rc_title ); ?>">

                <div class="sj-recent-logo">
                  <?php the_company_logo(); ?>
                </div>

                <div class="sj-recent-body">
                  <?php if ( ! empty($rc_name) ) : ?>
                    <div class="sj-recent-company"><?php echo esc_html( $rc_name ); ?></div>
                  <?php endif; ?>

                  <h3 class="sj-recent-jobtitle"><?php echo esc_html( $rc_title ); ?></h3>

                  <?php if ( ! empty($rc_excerpt) ) : ?>
                    <p class="sj-recent-excerpt"><?php echo esc_html( $rc_excerpt ); ?></p>
                  <?php endif; ?>
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
   ========================================================= */

:root{
  --sj-ink: #111827;
  --sj-muted: #6B7280;
  --sj-border: #E5E7EB;
  --sj-card: #FFFFFF;
  --sj-blue: #0884CC;
  --sj-orange: #FF8C2C;
  --sj-radius: 12px;
  --sj-shadow: 0 10px 40px -5px rgba(0,0,0,0.10);
}



html, body{
  width: 100%;
  max-width: 100%;
  overflow-x: hidden;
}


.sj-wrap{
  max-width: 900px;
  width: 100%;
  margin: 20px auto;
  padding: 0 16px;
  display: grid;
  gap: 16px;
}

.job_description,
.sj-content{
  overflow-wrap: anywhere;
  word-break: break-word !important;
}



.sj-card{
  padding: 24px; 
  border: 1px solid #DEDEDE;
    box-shadow: 0px 10px 40px -5px rgba(0,0,0,0.15);
    border-radius: 5px; 



}

.custom-top-section{
  width: 100%;
  max-width: 900px;
  margin: 24px auto;
  color: #333;
  border: 1px solid #DEDEDE;
  box-shadow: 0px 10px 40px -5px rgba(0,0,0,0.15);
  border-radius: 5px;
  background: #fff;
}


.top-section-text{
  padding: 20px 24px;
  display: flex;
  align-items: center;
  gap: 16px;
}

/* Link-blok (knop) */
.top-section-text > div{
  flex: 0 0 auto;
}

/* Tekst rechts neemt de rest van de ruimte */
.top-section-text p{
  margin: 0;
  flex: 1 1 auto;
  font-family: Poppins, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  font-size: 18px;
  color: #333;
  font-weight: 600; 
}

/* Knop */
.top-section-link{
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 14px;
  border-radius: 8px;
  background: rgba(8, 132, 204, 0.15);
  color: #0884CC !important;
  border: 1px solid #0884CC;
  text-decoration: none !important;
  font-family: Poppins, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  font-weight: 600;
  font-size: 14px;
  transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
}

.top-section-link:hover{
  transform: translateY(-1px);
  filter: brightness(.98);
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
}

/* Mobiel: knop boven, tekst eronder */
@media (max-width: 640px){
  .top-section-text{
    flex-direction: column;
    align-items: stretch;
  }

  .top-section-link{
    width: 100%;
  }
}


/* Single job */
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

/* Chips */
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
  background: #fff;
  color: #333;
  font-family: Poppins, system-ui, sans-serif;
  font-weight: 700;
  font-size: 14px;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
}

.sj-chip--link{
  text-decoration: none !important;
}
.sj-chip--link:hover{
  border-color: rgba(8,132,204,.35);
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
  font-family: Poppins, system-ui, sans-serif;
  font-size: 14px;
  font-weight: 600;
  color: var(--sj-blue);
}

/* Content */
.sj-content{
  font-family: Poppins, system-ui, sans-serif;
  color: var(--sj-ink);
  font-size: 15px;
  line-height: 1.75;
}

.sj-content p{ margin: 0 0 14px 0; }

/* Button */
.sj-actions{
  margin-top: 18px;
  display: flex;
}

.sj-btn{
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 14px;
  border-radius: 10px;
  background: rgba(8, 132, 204, 0.15);
  color: var(--sj-blue) !important;
  text-decoration: none !important;
  border: 1px solid var(--sj-blue);
  font-family: Poppins, system-ui, sans-serif;
  font-weight: 700;
  font-size: 14px;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  transition: transform .15s ease, filter .15s ease, box-shadow .15s ease;
}
.sj-btn:hover{
  transform: translateY(-1px);
}

/* Hide entry title */
h1.entry-title{ display:none; }

/* Contact (Studentinhuren style) */
.sj-contact{
  padding: 24px;
}

h2.sj-contact-title{
  
  font-family: Poppins; 
 font-weight: 600; 
  font-size: 20px;
  color: #333; 
}

.sj-contact-grid{
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 28px 56px;
}

.sj-contact-row{
  display: grid;
  gap: 8px;
}

.sj-contact-label{
  font-family: Poppins, system-ui, sans-serif;
  font-size: 16px;
  font-weight: 600;
  color: #7A7F87;
}

.sj-contact-value{
  font-family: Poppins, system-ui, sans-serif;
  font-size: 14px;
  font-weight: 400;
  color: #333;
  line-height: 1.25;
  word-break: break-word;
}

.sj-contact-link{
  color: var(--sj-blue) !important;
  text-decoration: none !important;
}
.sj-contact-link:hover{
  text-decoration: underline !important;
}

/* Recente vacatures */
.sj-recent{
  padding: 24px;
}

.sj-recent-head{
  margin-bottom: 18px;
}

.sj-recent-title{
  margin: 0;
  font-family: Poppins; 
  font-weight: 700;
  font-size: 20px;
  color: var(--sj-ink);
}

.sj-recent-sub{
  margin: 6px 0 0 0;
  font-family: Poppins, system-ui, sans-serif;
  font-size: 14px;
  color: var(--sj-muted);
}

.sj-recent-grid{
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 18px;
}

.sj-recent-item{
  background: #fff;
  border: 1px solid var(--sj-border);
  border-radius: 5px;
  overflow: hidden;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}

.sj-recent-item:hover{
  transform: translateY(-3px);
  border-color: rgba(8,132,204,.35);
  box-shadow: 0 18px 44px rgba(16,24,40,.14);
}

.sj-recent-link{
  display: flex;
  flex-direction: column;
  gap: 14px;
  padding: 18px;
  text-decoration: none !important;
  color: inherit;
  align-items: flex-start;
}

/* Logo: geen extra “kaart/box” eromheen */
.sj-recent-logo{
  display: block;
  padding: 0;
  border: 0;
  background: transparent;
  box-shadow: none;
}

/* Force left (WPJM kan margin:auto zetten) */
.sj-recent-logo img,
.sj-recent-logo .company_logo{
  display: block;
  margin: 0 !important;

}

/* Logo groter */
.sj-recent-logo img{
  width: 120px;
  height: auto;
  max-height: 80px;
  object-fit: contain;

}

/* Text */
.sj-recent-body{
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-width: 0;
}

.sj-recent-company{
  font-family: Poppins, system-ui, sans-serif;
  font-size: 13px;
  font-weight: 700;
  color: var(--sj-blue);
}

h3.sj-recent-jobtitle{
  margin: 0;
  font-family: Poppins; 
  font-size: 16px;
  font-weight: 700;
  line-height: 1.25;
  color: var(--sj-ink);

  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

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



@media (max-width: 768px){

  /* kill 100vw tricks (deze is 99% van de gevallen de boosdoener) */
  .top-section-text{
    flex-direction: column;
    align-items: stretch;
  }

  .top-section-link{
    width: 100%;
  }

  /* zorg dat alles binnen het scherm blijft */
  
  .sj-wrap,
  .single_job_listing,
  .sj-contact,
  .sj-recent,
  .sj-card{
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
  }

  /* sj-wrap is jouw container: die moet niet breder worden door padding */
  .sj-wrap{
    
  }

  /* voorkom dat grids/flex kinderen breder worden dan hun container */
  .sj-meta,
  .sj-recent-grid,
  .sj-recent-link,
  .sj-contact-grid{
    min-width: 0 !important;
  }

  /* images kunnen soms overflow veroorzaken */
  img{
    max-width: 100%;
    height: auto;
  }
}


@media (max-width: 768px){

  /* Zorg dat ALLES netjes binnen de viewport valt */
  *, *::before, *::after{
    box-sizing: border-box;
  }

  .sj-wrap{
    padding: 0 16px;
  }

  .custom-top-section{
    margin: 16px auto;
  }

  .top-section-text{
    padding: 16px;
  }

  /* Contact grid onder elkaar */
  .sj-contact-grid{
    grid-template-columns: 1fr;
    gap: 18px;
  }

  /* Recente vacatures: 1 of 2 kolommen */
  .sj-recent-grid{
    grid-template-columns: 1fr;
  }
}

.sj-header{
  min-width: 0;                 =
}

.sj-title{
  max-width: 100%;
  white-space: normal !important;
  overflow-wrap: anywhere;       
  word-break: break-word;       
  hyphens: auto;                 
}



</style>
