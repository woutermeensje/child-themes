<li class="job-listing-simple" <?php job_listing_class(); ?>>

    <a class="job-card-link"
       href="<?php the_job_permalink(); ?>"
       aria-label="<?php echo esc_attr( wpjm_get_the_job_title() ); ?>">
    </a>

    <div class="job-logo">
        <?php the_company_logo(); ?>
    </div>

    <div class="job-details">
        <div class="job-title-line">
            <h2 class="job-title">
                <a href="<?php the_job_permalink(); ?>"><?php wpjm_the_job_title(); ?></a>
            </h2>
            <span class="job-date"><?php echo get_the_date('d-m-Y'); ?></span>
        </div>

        <div class="job-meta">
            <?php
            $terms = wp_get_post_terms(get_the_ID(), 'job_company');
            if (!empty($terms) && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    echo '<a style="text-decoration: none;" class="company-name" href="' . esc_url(home_url('/vacatures/' . sanitize_title($term->name))) . '">' . esc_html($term->name) . '</a>';
                }
            }
            ?>
            <span class="job-location"><?php the_job_location(); ?></span>
            <span class="job-type">
                <?php if (get_option('job_manager_enable_types')) wpjm_the_job_types(); ?>
            </span>
        </div>

        <div class="job-description">
            <?php echo wp_trim_words(get_the_excerpt(), 12, '...'); ?>
        </div>
    </div>
</li>

<style>

    /* ===============================
   Job listing card (klikbaar blok)
   =============================== */

.job-listing-simple{
  position: relative;               /* nodig voor overlay-link */
  display: flex;
  align-items: center;
  gap: 20px;

  padding: 16px;
  margin: 0 auto 28px auto;
  width: 90%;

  border: 1px solid var(--color-bg);
  background-color: var(--color-bg);
  border-radius: 5px;

  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  transition: border-color .2s ease-in-out, transform .2s ease-in-out, box-shadow .2s ease-in-out;
  cursor: pointer;
}

.job-listing-simple:hover{
  border-color: var(--color-primary);
  transform: translateY(-1px);
}

/* Keyboard focus: als overlay-link focus krijgt, highlight hele card */
.job-listing-simple:has(.job-card-link:focus){
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(8,132,204,0.18), 0 10px 40px -5px rgba(0,0,0,0.15);
}

/* De klik-overlay (maakt hele blok klikbaar) */
.job-listing-simple .job-card-link{
  position: absolute;
  inset: 0;
  z-index: 1;
  border-radius: 5px;
  text-decoration: none;
}

/* Focus zichtbaar (toetsenbord) */
.job-listing-simple .job-card-link:focus{
  outline: 2px solid var(--color-primary);
  outline-offset: 3px;
}

/* Zorg dat echte content boven overlay zit (links blijven klikbaar) */
.job-listing-simple .job-logo,
.job-listing-simple .job-details{
  position: relative;
  z-index: 2;
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
  min-width: 0; /* voorkomt overflow bij lange titels */
}

/* Titel + datum */
.job-title-line{
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
  margin-right: 10px;
}

.job-title{
  font-size: 20px;
  line-height: 1.2;
  color: var(--color-text);
  margin: 0;
  min-width: 0;
}

.job-title a{
  color: var(--color-text);
  text-decoration: none;
  transition: color .2s ease-in-out;

  font-family: 'Inter', sans-serif;
  font-weight: 700;

  display: inline-block;
  max-width: 100%;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.job-title a:hover{
  color: var(--color-primary);
  text-decoration: none;
}

.job-date{
  font-family: Poppins, sans-serif;
  font-size: 12px;
  color: var(--color-primary);
  font-weight: 200;
  white-space: nowrap;
}

/* Meta info */
.job-meta{
  margin: 5px 0;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: center;
}

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
}

.company-name:hover{
  text-decoration: none;
  filter: brightness(0.98);
}

a.google_map_link{
  font-family: Poppins, sans-serif;
  font-weight: 700;
  font-size: 12px;

  color: var(--color-primary);
  border: 1px solid var(--color-primary);
  background-color: var(--color-accent);

  border-radius: 5px;
  padding: 5px 10px;
  text-decoration: none;
}

a.google_map_link:hover{
  text-decoration: none;
  filter: brightness(0.98);
}

.job-type{
  font-family: Poppins, sans-serif;
  font-weight: 700;
  font-size: 12px;

  color: var(--color-accent);
  border: 1px solid var(--color-primary);
  background-color: var(--color-primary);

  border-radius: 5px;
  padding: 5px 10px;
}

/* Locatie (komt uit the_job_location; vaak geen <a>, dus stijl als pill) */
.job-location{
  font-family: Poppins, sans-serif;
  font-weight: 700;
  font-size: 12px;

  color: var(--color-primary);
  border: 1px solid var(--color-primary);
  background-color: #fff;

  border-radius: 5px;
  padding: 5px 10px;
}

/* Beschrijving */
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
}

</style>