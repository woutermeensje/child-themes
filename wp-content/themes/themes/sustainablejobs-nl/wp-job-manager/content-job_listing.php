<li class="job-listing-simple" <?php job_listing_class(); ?>>

  <div class="job-logo">
    <a class="job-main-link" href="<?php the_job_permalink(); ?>" aria-label="<?php echo esc_attr( wpjm_get_the_job_title() ); ?>">
      <?php the_company_logo(); ?>
    </a>
  </div>

  <div class="job-details">

    <!-- ✅ Click-zone voor vacature: titel + excerpt + "wit gedeelte" -->
    <a class="job-main-link job-main-area"
       href="<?php the_job_permalink(); ?>"
       aria-label="<?php echo esc_attr( wpjm_get_the_job_title() ); ?>">

      <div class="job-title-line">
        <h2 class="job-title"><?php wpjm_the_job_title(); ?></h2>
        <span class="job-date"><?php echo get_the_date('d-m-Y'); ?></span>
      </div>

      <div class="job-description">
        <?php echo wp_trim_words(get_the_excerpt(), 12, '...'); ?>
      </div>

    </a>

    <!-- ✅ Meta blijft buiten click-zone -->
    <div class="job-meta">
      <?php
      $terms = wp_get_post_terms(get_the_ID(), 'job_company');
      if (!empty($terms) && !is_wp_error($terms)) {
        foreach ($terms as $term) {
          echo '<a class="company-name" href="' . esc_url(home_url('/vacatures/' . sanitize_title($term->name))) . '">' . esc_html($term->name) . '</a>';
        }
      }
      ?>
      <span class="job-location"><?php the_job_location(); ?></span>
      <span class="job-type">
        <?php if (get_option('job_manager_enable_types')) wpjm_the_job_types(); ?>
      </span>
    </div>

  </div>

</li>

<style>
  

  /* ===============================
   Job listing card (NIET meer alles klikbaar)
   =============================== */

.job-listing-simple{
  position: relative;
  display: flex;
  align-items: center;
  gap: 20px;

  padding: 16px;
  margin: 0 auto 28px auto;
  width: 90%;

  border: 1px solid #DEDEDE;
  background-color: var(--color-bg);
  border-radius: 5px;

  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  transition: border-color .2s ease-in-out, transform .2s ease-in-out, box-shadow .2s ease-in-out;
}

/* hover op de kaart mag wel, maar zonder “alles is klikbaar” */
.job-listing-simple:hover{
  border-color: var(--color-primary);
  transform: translateY(-1px);
}

/* ===============================
   Links / Click zones
   =============================== */

/* algemene link reset binnen card */
.job-listing-simple a{
  text-decoration: none;
}

/* ✅ Logo link */
.job-logo a{
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

/* ✅ Klikbare zone naar vacature (titel + excerpt + wit vlak) */
.job-main-area{
  display: block;
  color: inherit;
  border-radius: 5px;
  padding: 6px 0;              /* maakt “wit deel” in details ook klikbaar */
}

/* hover effect alleen op de klikzone */
.job-main-area:hover .job-title{
  color: var(--color-primary);
}

/* focus zichtbaar (toetsenbord) */
.job-main-area:focus,
.job-logo a:focus,
.company-name:focus{
  outline: 2px solid var(--color-primary);
  outline-offset: 3px;
  border-radius: 6px;
}

/* ===============================
   Logo
   =============================== */

.job-logo{
  display: flex;
  align-items: center;
  justify-content: center;

  width: 100px;
  height: 100px;
  margin-left: -50px;

  background-color: var(--color-bg);
}

.job-logo img{
  width: 100px;
  height: 100px;
  object-fit: contain;

  border-radius: 5px;
  padding: 6px;

  background-color: var(--color-bg);
  border: 1px solid var(--color-border);

  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  transition: transform .2s ease-in-out, box-shadow .2s ease-in-out, border-color .2s ease-in-out;
}

.job-listing-simple:hover .job-logo img{
  transform: translateY(-1px);
}

/* ===============================
   Details
   =============================== */

.job-details{
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-width: 0;
  margin-top: 12px; 
}

/* Titel + datum */
.job-title-line{
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;

  margin-right: 10px;
  font-family: 'Inter', sans-serif;
}

.job-title{
  font-size: 20px;
  line-height: 1.2;
  color: var(--color-text);
  margin: 0;
  min-width: 0;

  font-family: 'Inter', sans-serif !important;
  font-weight: 700;

  display: inline-block;
  max-width: 100%;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.job-date{
  font-family: Poppins, sans-serif;
  font-size: 12px;
  color: var(--color-primary);
  font-weight: 200;
  white-space: nowrap;
}

/* ===============================
   Meta info (NIET klikbaar behalve bedrijfsnaam)
   =============================== */

.job-meta{
  margin: 5px 0;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: center;
}

/* ✅ Bedrijfsnaam = link naar bedrijfspagina */
.company-name{
  font-family: Poppins, sans-serif;
  font-weight: 700;
  font-size: 12px;

  color: var(--color-primary);
  border: 1px solid var(--color-primary);
  background-color: var(--color-tertiary);

  border-radius: 5px;
  padding: 5px 10px;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
}

.company-name:hover{
  text-decoration: none;
  filter: brightness(0.98);
}

/* ✅ Job type pill (span, niet klikbaar) */
.job-type{
  font-family: Poppins, sans-serif;
  font-weight: 700;
  font-size: 12px;

  color: var(--color-accent);
  border: 1px solid var(--color-primary);
  background-color: var(--color-primary);

  border-radius: 5px;
  padding: 5px 10px;

  display: inline-flex;
  align-items: center;
}

/* ===============================
   Locatie - FIX dubbele border
   =============================== */

/* Als WPJM locatie als link rendert: style de link als pill */
a.google_map_link{
  font-family: Poppins, sans-serif;
  font-weight: 700;
  font-size: 12px;

  color: var(--color-primary);
  border: 1px solid var(--color-primary);
  background-color: #fff;

  border-radius: 5px;
  padding: 5px 10px;

  display: inline-flex;
  align-items: center;
}

a.google_map_link:hover{
  text-decoration: none;
  filter: brightness(0.98);
}

/* Maak job-location neutraal, zodat je niet 2x pill/border krijgt */
.job-location{
  font-family: Poppins, sans-serif;
  font-weight: 700;
  font-size: 12px;
  color: var(--color-primary);

  border: 0;
  background: transparent;
  padding: 0;
  display: inline-flex;
  align-items: center;
}

/* Als job-location plain text is (geen google_map_link),
   kun je hieronder de pill aanzetten door deze override te gebruiken:

.job-location{
  border: 1px solid var(--color-primary);
  background-color: #fff;
  border-radius: 5px;
  padding: 5px 10px;
}

*/

/* ===============================
   Beschrijving
   =============================== */

.job-description{
  font-size: 14px;
  line-height: 1.7;
  color: var(--color-text);

  font-family: Poppins, sans-serif;
  max-width: 100%;
  font-weight: 200;
}

/* ===============================
   Responsive
   =============================== */

@media only screen and (max-width: 768px){
  .job-listing-simple{
    flex-direction: column;
    align-items: flex-start;
    padding: 20px;
  }

  .job-logo{
    margin-left: 0;
  }

  .job-title{
    font-size: 1.25rem;
  }

  .job-date{
    display: none;
  }

  /* meta pills blijven netjes wrappen op mobiel */
  .job-meta{
    gap: 10px;
  }
}

</style>