<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $post;

$post_id = isset( $post->ID ) ? (int) $post->ID : 0;
?>

<!-- TOP SECTION (zoals Fondsen.org, maar kleuren via CSS vars) -->
<div class="update-header">
  <div class="opdrachten-update">
    <p>
      Blijf op de hoogte van de nieuwste vacatures!
    </p>
    <a href="/job-alerts/" class="update-link">
      Vacature Alert
    </a>
  </div>
</div>

<style>

  .update-header {
    max-width: 100%;  
    width: 900px; 
   margin: auto; 
   
  }

  .opdrachten-update {
    padding: 24px; 
     margin: 24px auto; 
    border: 1px solid #DEDEDE; 
    border-radius: 5px; 
    box-shadow: 0px 10px 40px -5px rgba(0,0,0,0.15);
    display: flex; 
    justify-content: space-between; /* 🔥 dit is de key */
    align-items: center;            /* verticaal netjes uitlijnen */
    width: 100%;

  }

  .opdrachten-update p {
    color: #333; 
    margin: 0; /* 🔥 dit fixeert 90% van dit soort issues */
    font-weight: 700; 
    font-size: 18px; 

  }

  .update-link {
    color: #0A6B8D !important;
    background: #E0D0E1; 
    font-family: Poppins; 
    font-weight: 700; 
    padding: 8px; 
    border: 1px solid #0A6B8D !important;
    border-radius: 5px; 
    text-decoration: none !important; 
    }

    .update-link:hover {
      background: #0A6B8D !important;
      color: #B9D1B3  !important;
    }

 @media (max-width: 768px) {

  .update-header {
    width: 100%;
    margin: 16px auto;
    padding: 0 12px;
  }

  .opdrachten-update {
    flex-direction: column;   /* 🔥 onder elkaar */
    align-items: stretch;     /* knop volle breedte */
    gap: 16px;                /* ruimte tussen tekst en knop */
    padding: 20px;
    text-align: left;
  }

  .opdrachten-update p {
    font-size: 16px;
    line-height: 1.4;
  }

  .update-link {
    display: block;
    width: 100%;
    text-align: center;
    padding: 12px 16px;
    font-size: 16px;
  }
}
   
</style>



    <?php if ( $post_id && job_manager_user_can_view_job_listing( $post_id ) ) : ?>

        <div class="single_job_listing">
            <?php if ( get_option( 'job_manager_hide_expired_content', 1 ) && 'expired' === $post->post_status ) : ?>

                <div class="job-manager-info">
                    <?php _e( 'This listing has expired.', 'wp-job-manager' ); ?>
                </div>

            <?php else : ?>

                <div class="content-part-job-description">
                    <div class="top-div">

                        <!-- Desktop meta (pills) -->
                        <div class="meta-information-single">
                            <p>🗓️ <?php the_job_publish_date(); ?></p>
                            <p>🏷️ <?php wpjm_the_job_types(); ?></p>
                            <p>🏢 <?php the_company_name(); ?></p>
                        </div>


                        <div class="job-title">
                            <h1><?php wpjm_the_job_title(); ?> | <?php the_company_name(); ?></h1>
                        </div>

                        <div class="job_description">
                            <?php wpjm_the_job_description(); ?>
                        </div>

                        <?php $company_website = get_post_meta( $post_id, '_company_website', true ); ?>
                        <?php if ( ! empty( $company_website ) ) : ?>
                            <div class="job-apply-button">
                                <a href="<?php echo esc_url( $company_website ); ?>" class="apply-button" target="_blank" rel="noopener">
                                    Solliciteren op deze vacature!
                                </a>
                            </div>
                        <?php else : ?>
                            <p>Geen link naar werkgever</p>
                        <?php endif; ?>

                        <?php do_action( 'single_job_listing_end' ); ?>

                    </div>
                </div>

            <?php endif; ?>
        </div>

    <?php else : ?>

        <?php get_job_manager_template_part( 'access-denied', 'single-job_listing' ); ?>

    <?php endif; ?>







<style>
/* ===== SINGLE JOB CARD ===== */
.single_job_listing{
    max-width: 100%;  
    width: 900px; 
    margin: 24px auto; 
    padding: 24px; /* ademruimte op mobiel */
    background: var(--color-bg);
    border-radius: 5px;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
    border: 1px solid var(--color-border);
}

.meta-information-single{
    display: flex;
}

.meta-information-single p{
    font-family: Poppins !important;
    font-size: 14px !important;
    font-weight: 700 !important;
    color: #333;
    border: 1px solid #DEDEDE;
    background-color: white; 
    border-radius: 999px;
    padding: 8px;
    margin-right: 10px;
    cursor: pointer;
    box-shadow: 0px 10px 40px -5px rgba(0, 0, 0, 0.15);
}

/* Desktop standaard: mobiel meta verborgen */
.meta-information-mobile{
    display: none;
    font-size: 16px;
    font-family: Poppins;
    font-weight: 400;
}

.job-title h1{
    padding-bottom: 10px;
    border-bottom: 1px solid #DEDEDE;;
    font-family: Inter, sans-serif; 
    font-weight: 700; 
    font-size: 20px;
    padding-top: 20px;
}

.job_description{
    font-family: Poppins;
    font-size: 14px;
    font-weight: 400;
    line-height: 1.6;
    color: var(--color-text);
    margin-top: 20px;
}

.job-manager-info{
    background-color: #ffdddd;
    color: #cc0000;
    border: 1px solid #cc0000;
    padding: 10px 15px;
    border-radius: 4px;
    text-align: center;
    margin-bottom: 20px;
    font-weight: 600;
}

/* Apply button (Fondsen look: “solid primary”) */
.job-apply-button a{
    padding: 12px;
    color: var(--color-bg);
    background-color: var(--color-primary);
    border-radius: 5px;
    font-family: Balgin-Bold;
    text-decoration: none;
    display: inline-block;
    margin-top: 20px;
    border: 1px solid var(--color-primary);
}

.job-apply-button a:hover{
    background: var(--color-bg);
    color: var(--color-primary);
    border: 1px solid var(--color-primary);
}

/* ===== RECENT JOBS LIST ===== */
.recent-jobs-list{
    
   
}

.job-listing-simple{
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 16px;
    margin: 20px auto;
    border: 1px solid var(--color-bg);
    background-color: var(--color-bg);
    border-radius: 5px;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
    transition: all 0.2s ease-in-out;
}

.job-listing-simple:hover{
    border: 1px solid var(--color-primary);
}

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
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
    border: 1px solid var(--color-border);
    transition: all 0.2s ease-in-out;
    background-color: var(--color-bg);
}

.job-details{
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.job-title{
    font-size: 20px;
    line-height: 1.2;
    color: var(--color-text);
    margin-bottom: 5px;
}

.job-title a{
    color: var(--color-text);
    text-decoration: none;
    transition: color 0.2s ease-in-out;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
}

.job-title a:hover{
    color: var(--color-primary);
    text-decoration: none;
}

.job-meta{
    margin-bottom: 5px;
    margin-top: 5px;
}

.company-name{
    font-family: Poppins, sans-serif;
    font-weight: 700;
    font-size: 12px;
    color: var(--color-primary);
    border: 1px solid var(--color-primary);
    background-color: var(--color-accent);
    border-radius: 5px;
    padding: 5px 10px;
    cursor: pointer;
    margin-right: 5px;
    text-decoration: none;
}

a.google_map_link{
    font-family: Poppins, sans-serif;
    font-weight: 700;
    font-size: 12px;
    color: var(--color-primary);
    border: 1px solid var(--color-primary);
    background-color: var(--color-tertiary);
    border-radius: 5px;
    padding: 5px 10px;
    cursor: pointer;
    margin-right: 5px;
    text-decoration: none;
}

.job-manager .job-type, .job-types .job-type, .job_listing .job-type{
    font-family: Poppins, sans-serif;
    font-weight: 700;
    font-size: 12px;
    color: var(--color-bg);
    border: 1px solid var(--color-primary);
    background-color: var(--color-primary);
    border-radius: 5px;
    padding: 5px 10px;
    cursor: pointer;
    margin-right: 5px;
    text-decoration: none;
}

.job-description{
    font-size: 14px;
    line-height: 1.7;
    color: var(--color-text);
    font-family: Poppins, sans-serif;
    max-width: 100%;
    font-weight: 200;
}

.job-title-line{
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-right: 10px;
}

.job-date{
    font-family: Poppins, sans-serif;
    font-size: 12px;
    color: var(--color-primary);
    font-weight: 200;
}

/* Verberg standaard WP page title */
h1.entry-title{ display: none; }

@media (max-width: 768px) {

  .meta-information-single{
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
  }

  .meta-information-single p{
    width: auto;
    height: auto;

    padding: 10px 14px;
    border-radius: 999px;

    font-size: 14px;
    line-height: 1.2;

    display: inline-flex;
    align-items: center;
    gap: 8px;

    white-space: nowrap; /* 🔥 essentieel */
    flex: 0 0 auto;      /* 🔥 NIET laten stretchen */
  }
}




</style>
