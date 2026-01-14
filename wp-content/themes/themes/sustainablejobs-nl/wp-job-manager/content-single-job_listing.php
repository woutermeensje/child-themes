<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $post;

$post_id = isset( $post->ID ) ? (int) $post->ID : 0;
?>

<!-- TOP SECTION (zoals Fondsen.org, maar kleuren via CSS vars) -->
<div class="custom-top-section">
    <div class="top-section-text">

        <?php
        if ( function_exists( 'yoast_breadcrumb' ) ) {
            yoast_breadcrumb( '<p class="broodkruimels">','</p>' );
        }
        ?>

        <div>
            <a href="https://sustainablejobs.nl/job-alerts/" class="top-section-link">Job alert instellen</a>
        </div>

    </div>
</div>

<div class="single-job-wrapper">

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
                            <p><?php the_job_publish_date(); ?></p>
                            <p><?php the_job_type(); ?></p>
                            <p><?php the_company_name(); ?></p>
                        </div>

                        <!-- Mobile meta (zoals Fondsen.org) -->
                        <div class="meta-information-mobile">
                            <p>🗓️ <?php echo esc_html( date_i18n( 'j F Y', get_post_time( 'U', true, $post_id ) ) ); ?></p>
                            <p>🏷️ <?php the_job_type(); ?></p>
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

</div>




<style>
/* ===== TOP SECTION (Fondsen structuur, Sustainable kleuren) ===== */
.custom-top-section {
    background-color: var(--color-primary);
    color: var(--color-bg);
    padding: 20px;
    text-align: center;
    font-family: "Balgin Bold", sans-serif;
    font-size: 18px;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);

    width: 100vw;
    margin-left: calc(50% - 50vw);
    margin-right: calc(50% - 50vw);

    position: relative;
    display: flex;
}


.top-section-text{
    text-align: left;
    width: 900px;
    margin: 0 auto;
}

.top-section-link{
    color: var(--color-bg) !important;
    border: 1px solid var(--color-tertiary);
    background: var(--color-tertiary);
    font-family: Balgin-Bold;
    font-size: 16px;
    border-radius: 5px;
    cursor: pointer;
    position: relative;
    display: inline-block;
    text-decoration: none !important;
    margin-top: 24px;
    padding: 10px 20px;
}

a.top-section-link {
    font-family: Balgin Bold;
    font-size: 16px; 
    color: var(--color-primary) !important;
}

.top-section-link:hover{
    color: var(--color-bg);
    opacity: 0.95;
}

/* Yoast broodkruimels */
.broodkruimels{
    color: white !important; 
    margin: 0;
    font-family: Poppins, sans-serif;
    font-weight: 400;
    font-size: 15px;
}

.broodkruimels a{
    color: var(--color-bg);
    text-decoration: none;
    font-family: Poppins, sans-serif;
    font-weight: 400;
    font-size: 15px;
}

.broodkruimels a:hover{
    text-decoration: none;
    opacity: 0.85;
}

.broodkruimels span,
.broodkruimels .breadcrumb_last{
    color: var(--color-bg);
    font-family: Poppins, sans-serif;
    font-weight: 400;
    font-size: 15px;
}

/* ===== SINGLE JOB CARD ===== */
.single_job_listing{
    max-width: 100%;
    width: 900px;
    margin: 20px auto;
    background: var(--color-bg);
    border-radius: 5px;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
    padding: 25px;
    border: 1px solid var(--color-border);
}

.meta-information-single{
    display: flex;
}

.meta-information-single p{
    font-family: Poppins !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    color: var(--color-bg);
    border: 1px solid var(--color-primary);
    background-color: var(--color-primary);
    border-radius: 5px;
    padding: 5px 10px;
    margin-right: 10px;
    cursor: pointer;
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
    border-bottom: 2px solid var(--color-primary);
    font-family: Balgin Bold;
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

/* ===== MOBILE (Fondsen fixes) ===== */
@media (max-width: 768px){

    /* 100vw overflow fix */
    .custom-top-section{
        width: 100% !important;
        margin-left: 0 !important;
    }

    .top-section-text{
        width: 100% !important;
        margin: 0 !important;
        padding: 0 16px !important;
        box-sizing: border-box;
    }

    .broodkruimels,
    .broodkruimels a,
    .broodkruimels span,
    .broodkruimels .breadcrumb_last{
        font-size: 13px;
        line-height: 1.35;
        word-break: break-word;
    }

    .top-section-link{
        display: block;
        width: 100%;
        box-sizing: border-box;
        text-align: center;
        margin-top: 16px;
        padding: 12px 16px;
        font-size: 16px;
    }

    /* alle vaste 900px breedtes resetten */
    .single_job_listing,
    .recent-jobs-list{
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box;
    }

    .single_job_listing{
        padding: 16px !important;
        margin: 16px auto !important;
    }

    .recent-jobs-list{
        margin: 16px auto !important;
        padding: 0 16px !important;
    }

    /* meta switch */
    .meta-information-single{ display: none; }
    .meta-information-mobile{ display: block; }

    /* negatieve marge logo weg */
    .job-logo{ margin-left: 0 !important; }

    /* recent jobs card stacking */
    .job-listing-simple{
        flex-direction: column;
        align-items: flex-start;
        padding: 20px;
        width: 100%;
    }

    .job-date{ display: none; }
}
</style>
