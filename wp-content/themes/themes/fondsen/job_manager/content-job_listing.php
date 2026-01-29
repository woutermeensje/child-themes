<?php
/**
 * Job listing in the loop.
 *
 * Template override: yourtheme/job_manager/content-job_listing.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $post;

// Cover / achtergrondafbeelding bepalen (desktop)
$cover_image      = get_post_meta( $post->ID, '_cover_image', true );
$background_image = $cover_image ? $cover_image : ( function_exists('get_secondary_imageurl') ? get_secondary_imageurl( $post->ID ) : '' );

?>
<li <?php job_listing_class(); ?>
    data-longitude="<?php echo esc_attr( $post->geolocation_long ); ?>"
    data-latitude="<?php echo esc_attr( $post->geolocation_lat ); ?>"
>
    <div class="job-card" data-href="<?php the_job_permalink(); ?>">

        <!-- =========================================
             DESKTOP / TABLET LAYOUT (jouw huidige)
             ========================================= -->
        <div class="job-card__desktop">

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
                            <p class="job-card-meta__text"><?php the_job_location( false ); ?></p>
                        </li>

                        <!-- Dienstverband -->
                        <?php if ( function_exists('display_tax_terms') && display_tax_terms( 'job_listing_type', $post->ID ) ) : ?>
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
                            <p class="job-card-meta__text"><?php the_job_publish_date(); ?></p>
                        </li>
                    </ul>

                    <div class="jobs_buttons">
                        <a href="<?php the_job_permalink(); ?>">Vacature bekijken</a>
                    </div>

                </div>
            </div>

        </div><!-- /.job-card__desktop -->


        <!-- =========================================
             MOBILE LAYOUT (nieuwe opbouw)
             ========================================= -->
        <div class="job-card__mobile">
            <a class="job-mobile__link" href="<?php the_job_permalink(); ?>">

                <div class="job-mobile__top">
                    <div class="job-mobile__logo">
                        <?php the_post_thumbnail(); ?>
                    </div>

                    <div class="job-mobile__toptext">
                        <div class="job-mobile__company"><?php the_company_name(); ?></div>
                        <h2 class="job-mobile__title"><?php wpjm_the_job_title(); ?></h2>
                    </div>
                </div>

                <div class="job-mobile__excerpt">
                    <?php echo esc_html( wp_trim_words( get_the_excerpt(), 8, '…' ) ); ?>
                </div>

                <ul class="job-mobile__meta">
                    <li class="job-mobile__meta-item">
                        <span class="job-mobile__icon">📍</span>
                        <span class="job-mobile__text"><?php the_job_location( false ); ?></span>
                    </li>

                    <?php if ( function_exists('display_tax_terms') && display_tax_terms( 'job_listing_type', $post->ID ) ) : ?>
                        <li class="job-mobile__meta-item">
                            <span class="job-mobile__icon">⏰</span>
                            <span class="job-mobile__text"><?php echo esc_html( display_tax_terms( 'job_listing_type', $post->ID ) ); ?></span>
                        </li>
                    <?php endif; ?>

                    <li class="job-mobile__meta-item">
                        <span class="job-mobile__icon">📅</span>
                        <span class="job-mobile__text"><?php the_job_publish_date(); ?></span>
                    </li>
                </ul>

                <div class="job-mobile__cta">Vacature bekijken</div>
            </a>
        </div><!-- /.job-card__mobile -->

    </div>
</li>


<style>
/* ========== Basis / container ========== */

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
    background: #FBFAF8;
    border-radius: 6px;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
    padding: 24px;
    border: 1px solid #E0E0E0;
}

a.title-link {
    background-color: #FBFAF8 !important;
}

/* IMPORTANT: voorkomt “breken” door te brede children */
.job-card,
.job-card *{
    max-width: 100%;
}

.job-card__desktop,
.job-card__mobile{
    width: 100%;
}

/* ========== Desktop layout ========== */

.job-card__desktop{
    display: flex;
    flex-wrap: nowrap;
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

    /* Belangrijk voor lange titels / woorden */
    min-width: 0;
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
    object-fit: contain;
    background: #fff;
}

.company-logo-absolute {
    position: absolute;
    border-radius: 50%;
    width: 80px;
    height: 80px;
    background: white;
    z-index: 9;
    left: 30px;
    bottom: 30px;
}

/* Contentblok in de kaart */
.job_listing .job_listing_content {
    padding: 0 40px;
    min-width: 0; /* essentieel bij flex */
}

/* TITEL: maak 'm altijd wrapbaar */
.job_listing .job_listing_content h2{
    margin: 0 0 5px 0;
    font-family: "Balgin-Bold", Sans-serif;
    font-size: 20px;

    overflow-wrap: anywhere;
    word-break: break-word;
}

/* excerpt */
.job_listing .job_listing_content .job_text {
    margin: 20px 0;
}
.job_listing .job_listing_content .job_text p {
    margin-top: 0;
    margin-bottom: 10px;
    color: #333;
}

/* Buttons */
.jobs_buttons{ margin-top:24px; }

.jobs_buttons a{
    display:inline-block !important;
    background: #0884CC !important;
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
    padding: 0 !important;
    color: #333333;
    text-decoration: none;
    font-family: Balgin-Bold !important;
}

/* Header-afbeelding + overlay */
.job_listing .background-wrapper .title { display: none; }

.background-wrapper {
    width: 100%;
    position: relative;
    max-height: 318px;
    overflow: hidden;
}

.block-bg-overlay {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0px;
    background-color: var(--e-global-color-primary);
    opacity: 0.3 !important;
}

.block-bg-overlay,
.background-inner {
    height: 318px;
}

/* Meta desktop */
.job-card-meta {
    list-style: none;
    margin: 0;
    padding: 0;
    color: #333;
}
.job-card-meta__item {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 6px;
    flex-direction: row !important;
}
.job-card-meta__icon { font-size: 18px; line-height: 1; }
.job-card-meta__text,
.job-card-meta__title { margin: 0; }
.job-card-meta__title { font-weight: 600; }
.job-card-meta__textgroup {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

/* ========== Mobile layout (standaard uit) ========== */

.job-card__mobile{
    display: none;
}

.job-card {
    margin-top: 8px; 
    margin-bottom: 8px; 
}

.job-mobile__link{
    display: block;
    text-decoration: none !important;
    color: inherit;
}

.job-mobile__top{
    display: flex;
    gap: 14px;
    align-items: center;
    min-width: 0;
}

.job-mobile__logo{
    flex: 0 0 auto;
    width: 56px;
    height: 56px;
    border-radius: 999px;
    background: #fff;
    border: 1px solid #eee;
    display: grid;
    place-items: center;
    overflow: hidden;
}

.job-mobile__logo img{
    width: 42px;
    height: 42px;
    object-fit: contain;
    border-radius: 999px;
}

.job-mobile__toptext{
    min-width: 0;
}

.job-mobile__company{
    font-family: Poppins, system-ui, sans-serif;
    font-weight: 700;
    font-size: 14px;
    color: #111827;
    line-height: 1.2;
}

.job-mobile__title{
    margin: 4px 0 0 0;
    font-family: Poppins, system-ui, sans-serif;
    font-weight: 700;
    font-size: 18px;
    line-height: 1.25;
    color: #111827;

    /* dit voorkomt jouw “te brede titel” issue */
    overflow-wrap: anywhere;
    word-break: break-word;
}

.job-mobile__excerpt{
    margin-top: 12px;
    font-family: Poppins, system-ui, sans-serif;
    font-size: 14px;
    line-height: 1.6;
    color: #333;
}

.job-mobile__meta{
    list-style: none;
    padding: 0;
    margin: 14px 0 0 0;
    display: grid;
    gap: 8px;
}

.job-mobile__meta-item{
    display: flex;
    gap: 10px;
    align-items: center;
    min-width: 0;
}

.job-mobile__icon{
    width: 18px;
    text-align: center;
    flex: 0 0 auto;
}

.job-mobile__text{
    font-family: Poppins, system-ui, sans-serif;
    font-size: 14px;
    color: #111827;
    min-width: 0;

    overflow-wrap: anywhere;
    word-break: break-word;
}

.job-mobile__cta{
    display: none; 
}

/* ========== Responsive switch ========== */
/* Zet desktop uit en mobile aan op mobiel */
@media (max-width: 960px){

    /* card op mobiel breder laten ogen */
    ul.job_listings li.job_listing{
        padding: 12px;
    }

    .job-card{
        padding: 18px;
    }

    .job-card__desktop{
        display: none;
    }

    .job-card__mobile{
        display: block;
    }

    /* oude globale selectors die soms padding/width slopen */
    .job_listing,
    .job_listing_content{
        padding: 0 !important;
    }
}

/* Kleine algemene dingen */
input[type='text']::placeholder { font-size: 13px; }
</style>
