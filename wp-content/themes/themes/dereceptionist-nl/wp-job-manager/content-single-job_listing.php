<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $post;

if ( job_manager_user_can_view_job_listing( $post->ID ) ) : ?>

<style>.custom-top-section { display: none !important; }</style>

<div class="custom-top-section">
    <p>Ontvang wekelijkse onze vacatures</p>
    <div class="custom-top-section-form">
        <form action=""></form>
        <input type="text" placeholder="E-mailadres.." class="search-input">
        <button class="button-top-section">Bevestigen</button>  
    </div>
</div>

<div>
    <div class="single_job_listing">
        <?php if ( get_option( 'job_manager_hide_expired_content', 1 ) && 'expired' === $post->post_status ) : ?>
            <div class="job-manager-info"><?php _e( 'This listing has expired.', 'wp-job-manager' ); ?></div>
        <?php else : ?>
            <div class="content-part-job-description">
                <div class="top-div">
                    <div class="meta-information-single">
                        <p><?php the_job_publish_date(); ?></p>
                        <p><?php the_job_type(); ?></p>
                        <p><?php the_company_name(); ?></p>
                        <p><?php the_job_location(); ?></p> 
                    </div>
                    <div class="job-title">
                        <h1><?php wpjm_the_job_title(); ?> | <?php the_company_name(); ?></h1>
                    </div>
                    <div class="job_description">
                        <?php wpjm_the_job_description(); ?>
                    </div>
                    <?php $company_website = get_post_meta( $post->ID, '_company_website', true ); ?>
                    <?php if ( ! empty( $company_website ) ) : ?>
                        <div class="job-apply-button">
                            <a href="<?php echo esc_url( $company_website ); ?>" class="apply-button" target="_blank">Bezoek de sollicitatiepagina</a>
                        </div>
                    <?php else : ?>
                        <p>No application link available.</p>
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

<div class="recent-jobs-container">
    <ul class="recent-jobs-list">
        <?php
        $recent_jobs = new WP_Query([
            'post_type'      => 'job_listing',
            'posts_per_page' => 5,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        if ($recent_jobs->have_posts()) :
            while ($recent_jobs->have_posts()) : $recent_jobs->the_post(); ?>
                <li class="recent-job-item">
                    <a href="<?php the_job_permalink(); ?>" class="job-listing-link">
                        <div class="logo-and-title">
                            <div class="rounded-image">
                                <div class="logo-wrapper"><?php the_company_logo(); ?></div>
                            </div>
                            <div class="recent-job-content">
                                <div>
                                    <h3 class="recent-job-title"><?php the_title(); ?> | <?php the_company_name(); ?></h3>
                                    <?php the_job_location(); ?>
                                </div>
                                <p class="recent-job-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?></p>
                            </div>
                        </div>
                    </a>
                </li>
            <?php endwhile;
            wp_reset_postdata();
        else : ?>
            <li class="no-jobs-found">No recent jobs found.</li>
        <?php endif; ?>
    </ul>
</div>

<style>

    main#content {
    width: 100%;
}

a.google_map_link {
    text-decoration: none;
    font-family: Balgin Bold !important;
    color: var(--color-primary) !important;
    font-size: 15px;
}

.custom-top-section {
    background-color: var(--color-primary);
    color: var(--color-bg);
    padding: 20px;
    text-align: center;
    font-family: "Balgin Bold", sans-serif;
    font-size: 18px;
    border-radius: 0;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    width: 100vw;
    margin-left: calc(-50vw + 50%);
    position: relative;
    left: 0;
    display: flex;
}

.custom-top-section p {
    color: var(--color-bg);
    text-align: center;
    font-family: "Balgin Bold", sans-serif;
    font-size: 18px;
    margin: auto 0 auto auto;
}

.button-top-section {
    background-color: var(--color-tertiary);
    color: var(--color-primary);
    border: none;
    padding: 10px 20px;
    margin: 0 auto;
    display: block;
    font-family: "Balgin Bold", sans-serif;
    font-size: 15px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.button-top-section:hover {
    background-color: var(--color-tertiary);
    color: var(--color-primary);
}

.custom-top-section-form {
    display: flex;
    margin: auto;
}

.search-input {
    padding: 10px;
    border: 1px solid var(--color-tertiary) !important;
    border-radius: 5px;
    font-size: 15px;
    margin-right: 10px;
    font-family: Poppins;
    font-weight: 500;
}

input[type=text]:focus {
    outline: none;
}

.search-input:focus,
.search-input:active,
.search-input:hover {
    border-color: var(--color-tertiary) !important;
}

.search-input::placeholder {
    color: var(--color-text-muted);
    font-size: 14px;
    font-style: italic;
    font-family: Poppins;
    opacity: 0.7;
}

h1.entry-title {
    display: none;
}

.single_job_listing {
    max-width: 80%;
    margin: 40px auto;
    background: var(--color-bg);
    border-radius: 5px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    padding: 25px;
}

.cover-image-top {
    position: relative;
    overflow: hidden;
    height: 300px;
    border-bottom: 2px solid var(--color-primary);
}

.cover-image {
    width: auto;
    height: 100%;
    object-fit: contain;
}

.meta-information-single {
    display: flex;
    position: left;
}

.meta-information-single p {
    font-family: Balgin Bold;
    font-size: 15px;
    color: #2C2C31;
    border: 1px solid #FCAF2B;
    background-color: #FEEAD5;
    font-weight: 300;
    margin-right: 10px;
    border-radius: 5px;
    padding: 5px 10px;
    cursor: pointer;
}


.job-title h1 {
    padding-bottom: 10px;
    border-bottom: 2px solid var(--color-primary);
    font-family: Balgin Bold;
    font-size: 20px !important;
    padding-top: 20px;
}

.content-part-job-description {
    padding: 20px;
    position: relative;
}

.job-manager-info {
    background-color: #ffdddd;
    color: #cc0000;
    border: 1px solid #cc0000;
    padding: 10px 15px;
    border-radius: 4px;
    text-align: center;
    margin-bottom: 20px;
    font-weight: 600;
}

.job_description {
    font-family: Poppins;
    font-size: 14px;
    font-weight: 400;
    line-height: 1.6;
    color: var(--color-text);
    margin-top: 20px !important;
}

.single_job_listing .job-application .application_button,
input.application_button.button {
    display: inline-block;
    background-color: var(--color-primary);
    color: var(--color-bg) !important;
    padding: 10px 20px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 600;
    margin-top: 20px;
    border: 1px solid var(--color-primary);
    font-family: Balgin Bold;
    text-align: center;
    width: 100%;
}

input.application_button.button:hover {
    background-color: var(--color-bg);
    color: var(--color-primary) !important;
    border: 1px solid var(--color-primary);
}

.single_job_listing .single_job_listing_start,
.single_job_listing .single_job_listing_end {
    margin-top: 20px;
    padding: 15px;
    background: var(--color-bg-light);
    border: 1px solid var(--color-border-light);
    border-radius: 4px;
}

@media (max-width: 768px) {
    .single_job_listing {
        width: 100%;
        max-width: 600px;
        min-width: 320px;
        margin: 0 auto;
        padding: 10px;
    }

    .meta-information-single p {
        font-size: 13px;
        background-color: var(--color-bg);
    }

    .meta-information-single p:hover {
        background-color: var(--color-accent);
    }

    .custom-top-section p {
        font-size: 14px;
    }

    .button-top-section {
        font-size: 14px;
        background-color: var(--color-accent);
    }
}

.recent-jobs-container {
    max-width: 80%;
    margin: 40px auto;
}

.recent-jobs-list {
    list-style: none;
    padding: 0;
}

.recent-job-item {
    background: var(--color-bg);
    border-radius: 5px;
    border: 1px solid var(--color-primary);
    margin: 15px 0;
    display: flex;
    padding: 20px;
    justify-content: left;
    align-items: left;
    transition: transform 0.3s ease;
}

.recent-job-item:hover {
    transform: scale(1.05);
}

.recent-job-content {
    margin: auto;
}

.recent-job-content h3 {
    font-family: Balgin Bold;
    font-size: 18px;
    color: var(--color-text);
    margin: 5px 0;
}

.recent-job-content p {
    font-family: Poppins;
    font-size: 13px;
    font-weight: 200;
    color: var(--color-text);
    margin: 5px 0;
}

.recent-job-company h4 {
    color: var(--color-text);
    font-family: Balgin Bold;
    font-size: 15px;
}

a.job-listing-link {
    text-decoration: none;
}

.rounded-image .logo-wrapper img {
    max-height: 50%;
    max-width: 50%;
    border-radius: 5px;
    object-fit: cover;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin: auto;
    padding: 2px;
    background: var(--color-bg);
}

.logo-and-title {
    display: flex;
}

.custom-bottom-section {
    background-color: var(--color-primary);
    color: var(--color-bg);
    padding: 20px;
    text-align: center;
    font-family: "Balgin Bold", sans-serif;
    font-size: 18px;
    border-radius: 0;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    width: 100vw;
    margin-left: calc(-50vw + 50%);
    position: sticky;
    bottom: 0;
    z-index: 1000;
    display: flex;
    left: 0;
}

.custom-bottom-section p {
    color: var(--color-bg);
    text-align: center;
    font-family: "Balgin Bold", sans-serif;
    font-size: 18px;
    margin: auto;
}



.job-apply-button a {
    padding: 15px;
    
    border-radius: 5px;
    margin-top: 20px;
    font-family: Balgin Bold;
    text-decoration: none;
    color: #2C2C31;
    border: 1px solid #FCAF2B;
    background-color: #FEEAD5;
}

.job-apply-button a:hover {
    background: var(--color-bg);
    color: var(--color-primary);
    border: 1px solid var(--color-primary);
}

</style>