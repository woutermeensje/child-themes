<?php
/**
 * Job listing in the loop.
 *
 * This template can be overridden by copying it to
 * yourtheme/job_manager/content-job_listing.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @since       1.0.0
 * @version     1.34.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

global $post;

// Cover / achtergrondafbeelding bepalen
$cover_image      = get_post_meta( $post->ID, '_cover_image', true );
$background_image = $cover_image ? $cover_image : get_secondary_imageurl( $post->ID );
?>

<li <?php job_listing_class(); ?>
    data-longitude="<?php echo esc_attr( $post->geolocation_long ); ?>"
    data-latitude="<?php echo esc_attr( $post->geolocation_lat ); ?>"
>
    <div class="job-card" data-href="<?php the_job_permalink(); ?>">

        <!-- Media / afbeelding-kolom -->
        <div class="job-card__media">
            <div class="background-wrapper">
                <div class="company-logo-absolute hide_on_single">
                    <div class="company-logo-wrapper">
                        <?php the_post_thumbnail(); ?>
                    </div>
                </div>

                <div
                    class="background-inner"
                    style="
                        background-image: url('<?php echo esc_url( $background_image ); ?>');
                        background-size: cover;
                        min-height: 100%;
                        display: block;
                        width: 100%;
                        background-repeat: no-repeat;
                        background-position: center center;
                        background-attachment: scroll;
                    "
                ></div>

                <div
                    class="block-bg-overlay style-color-139258-bg"
                    style="opacity: 0.5; height: 100%;"
                ></div>
            </div>
        </div>

        <!-- Content / tekst-kolom -->
        <div class="job-card__content">
            <div class="job_listing_content">

                <a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" class="title-link">
                    <h2><?php wpjm_the_job_title(); ?></h2>
                </a>

                <div class="job_text">
                    <p><?php echo wp_trim_words( get_the_excerpt(), 15, '...' ); ?></p>
                </div>

                <ul class="job-card-meta">
                    <!-- Organisatie -->
                    <li class="job-card-meta__item job-card-meta__item--org">
                        <span class="job-card-meta__icon">🤝</span>
                        <div class="job-card-meta__textgroup">
                            <p class="job-card-meta__title"><?php the_company_name(); ?></p>
                            <?php the_company_tagline(); ?>
                        </div>
                    </li>

                    <!-- Locatie -->
                    <li class="job-card-meta__item job-card-meta__item--location">
                        <span class="job-card-meta__icon">📍</span>
                        <p class="job-card-meta__text">
                            <?php the_job_location( false ); ?>
                        </p>
                    </li>

                    <!-- Dienstverband -->
                    <?php if ( display_tax_terms( 'job_listing_type', $post->ID ) ) : ?>
                        <li class="job-card-meta__item job-card-meta__item--type">
                            <span class="job-card-meta__icon">⏰</span>
                            <p class="job-card-meta__text">
                                <?php echo display_tax_terms( 'job_listing_type', $post->ID ); ?>
                            </p>
                        </li>
                    <?php endif; ?>

                    <!-- Publicatiedatum -->
                    <li class="job-card-meta__item job-card-meta__item--date">
                        <span class="job-card-meta__icon">📅</span>
                        <p class="job-card-meta__text">
                            <?php the_job_publish_date(); ?>
                        </p>
                    </li>
                </ul>

                <div class="jobs_buttons">
                    <a href="<?php the_job_permalink(); ?>">
                        Vacature bekijken
                    </a>

                </div>


            </div>
        </div>

    </div>
</li>


<style>
/* ========== Job card layout ========== */

ul.job_listings {
    max-width: 1140px;
    margin: 30px auto !important;
}


div.job_listings ul.job_listings {
    padding-left: 0;
    border: none;
}

ul.job_listings li.job_listing {
    padding: 30px;
}

/* Card container */
.job-card {
    display: flex;
    flex-wrap: nowrap;
    background: #ffffff;
}

/* Linkerkolom met cover + logo */
.job-card__media {
    position: relative;
    flex: 0 0 40%;
    max-width: 40%;
}

/* Rechterkolom met content */
.job-card__content {
    flex: 0 0 60%;
    max-width: 60%;
    display: flex;
    align-items: center;
}

/* Logo-wrapper */
ul.job_listings li.job_listing .company-logo-wrapper,
.single_job_listing .company-logo-wrapper,
.company-logo-wrapper {
    height: 100px;
    width: 100px;
    text-align: left;
}

ul.job_listings li.job_listing .company-logo-wrapper img,
.single_job_listing .company-logo-wrapper img,
.company-logo-wrapper img {
    border-radius: 50%;
    width: 80px;
    height: 80px;
    border: 1px solid #eee;
    padding: 5px;
    max-width: 100%;
    object-fit: contain;
}

.company-logo-absolute {
    position: absolute;
    border-radius: 50%;
    width: 80px;
    height: 80px;
    background: white;
    max-width: 100%;
    object-fit: contain;
    z-index: 9;
    left: 30px;
    bottom: 30px;
}

/* Contentblok in de kaart */
.job_listing .job_listing_content {
    padding: 0 40px;
}

.job_listing .job_listing_content h2 {
    margin: 0 0 5px 0;
    font-family: "Balgin-Bold", Sans-serif;
    font-size: 20px;
}

.job_listing .job_listing_content .job_text {
    margin: 20px 0;
}

.job_listing .job_listing_content .job_text p {
    margin-top: 0;
    margin-bottom: 10px;
}

/* Buttons onderaan de listing */
/* container */
.jobs_buttons{
    margin-top:24px;
}

/* button-style link */
.jobs_buttons a{
    display:inline-block !important;
    background: #0884CC !important;        /* Sustainablejobs / Fondsen blauw */
    color: white !important;
    solid: 1px solid #0884CC !important;
    padding:0 30px !important;
    height:48px !important;
    line-height:48px !important;
    border-radius: 0px !important;
    text-decoration:none !important;
    font-family: Balgin-Bold !important;
    font-size:15px;
    box-shadow:0 2px 6px rgba(0,0,0,.08);
    transition:all .2s ease;
    max-width: 50%; 
    text-align: center;
}

/* Titel-link */
a.title-link {
    background-color: white !important;
    padding: 0 !important;
    color: #333333;
    text-decoration: none;
    font-family: Balgin-Bold !important; 
}

/* Header-afbeelding + overlay */
.job_listing .background-wrapper .title {
    display: none;
}

.background-wrapper {
    width: 100%;
    position: relative;
    max-height: 318px;
    overflow: hidden;
}

.block-bg-overlay {
    border-radius: inherit;
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0px;
    background-color: var(--e-global-color-primary);
    opacity: 0.3 !important;
    transition: background 0.3s, border-radius 0.3s, opacity 0.3s;
}

.block-bg-overlay,
.background-inner {
    height: 318px;
    min-height: 0;
}

/* Meta-informatie onder de vacaturetitel */
.job-card-meta {
    list-style: none;
    margin: 0;
    padding: 0;
}

.job-card-meta__item {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 6px;
    flex-direction: row !important;
}

.job-card-meta__icon {
    font-size: 18px;
    line-height: 1;
}

.job-card-meta__text,
.job-card-meta__title {
    margin: 0;
}

.job-card-meta__title {
    font-weight: 600;
}

.job-card-meta__textgroup {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

/* Overig */
.only_mobile {
    display: none;
}

/* Responsive */
@media only screen and (max-width: 960px) {
    .only_mobile {
        display: block !important;
        padding: 15px 15px 0 15px;
    }

    .showing_jobs {
        display: none !important;
    }

    body.single-job_listing .single_job_listing {
        padding: 15px;
        margin: 0 0 0 0 !important;
    }

    .elementor-element-b245f86 {
        position: fixed !important;
        width: 100%;
        z-index: 99999;
        background: white;
    }

    .jobs_buttons {
        padding: 0;
    }

    a.title-link {
        margin-top: 30px;
    }

    .sidebar_jobs .company-logo-wrapper {
        text-align: center !important;
        width: 100% !important;
    }


    .page-header .company-logo-absolute {
        left: 0;
        right: 0;
        margin: 0 auto;
        top: 110px;
    }

    .job_info_wrapper .jobs_buttons {
        padding: 0 15px;
    }

    .apply-now-pink,
    .more-info-white,
    .apply-now,
    .more-info {
        display: block;
        float: left;
        width: 100%;
    }

    .apply-now {
        margin-bottom: 5px;
    }

    .more-info {
        margin-bottom: 15px;
    }

    h2.extra_padding {
        padding-left: 15px;
        padding-top: 15px;
        display: none;
    }

    .elementor-element-c6e8b82 {
        text-align: center;
        padding-top: 60px;
    }

    .hidden-sm {
        display: none !important;
    }

    .job_listing,
    .job_listing_content {
        padding: 2% !important;
    }

    .limit-width {
        padding: 0 !important;
    }

    a.title-link {
        background-color: white !important;
        padding: 0 !important;
        color: #5170ff;
    }

    .job_listing h2 {
        font-size: 20px;
    }

    /* Job card stacken op mobiel */
    .job-card {
        flex-direction: column;
    }

    .job-card__media,
    .job-card__content {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

/* Kleine algemene dingen */
input[type='text']::placeholder {
    font-size: 13px;
}

.menu-container {
    height: 80px;
}

.logo-image {
    height: 30px !important;
}

.job-card {
    border-radius: 6px;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
    padding: 24px;
    border: 1px solid #E0E0E0; 
}
    
</style>