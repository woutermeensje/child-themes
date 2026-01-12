<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $post;

if ( job_manager_user_can_view_job_listing( $post->ID ) ) : ?>



<body>
    
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
                            <a href="<?php echo esc_url( $company_website ); ?>" class="apply-button" target="_blank">Go to the application page</a>
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
                <li class="job-listing-simple" <?php job_listing_class(); ?>>
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
                                    echo '<span class="company-name">' . esc_html($term->name) . '</span>';
                                }
                            }
                            ?>
                            <span class="job-location"><?php the_job_location(); ?></span>
                            <span class="job-type">
                                <?php if (get_option('job_manager_enable_types')) : ?>
                                    <?php $types = wpjm_get_the_job_types(); ?>
                                    <?php if (!empty($types)) : foreach ($types as $type) : ?>
                                        <?php echo esc_html($type->name); ?>
                                    <?php endforeach; endif; ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="job-description">
                            <?php echo wp_trim_words(get_the_excerpt(), 12, '...'); ?>
                        </div>
                    </div>
                </li>
            <?php endwhile;
            wp_reset_postdata();
        else : ?>
            <li class="no-jobs-found">No recent jobs where found..!</li>
        <?php endif; ?>
    </ul>
</div>
</body>


<style>
.custom-top-section {
    background-color: var(--color-primary);
    color: var(--color-bg);
    padding: 20px;
    text-align: center;
    font-family: "Balgin Bold", sans-serif;
    font-size: 18px;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
    width: 100vw;
    margin-left: calc(-50vw + 50%);
    position: relative;
    display: flex;
}

.custom-top-section p {
    color: var(--color-bg);
    font-family: "Balgin Bold", sans-serif;
    font-size: 18px;
    margin: auto;
}

.custom-top-section-form {
    display: flex;
    margin: auto;
}

.search-input {
    padding: 10px;
    border: 1px solid var(--color-tertiary);
    border-radius: 5px;
    font-size: 15px;
    margin-right: 10px;
    font-family: Poppins;
    font-weight: 500;
}

.search-input::placeholder {
    color: #888;
    font-size: 14px;
    font-style: italic;
    font-family: Poppins;
    opacity: 0.7;
}

.button-top-section {
    background-color: var(--color-tertiary);
    color: var(--color-primary);
    border: none;
    padding: 10px 20px;
    font-family: "Balgin Bold", sans-serif;
    font-size: 15px;
    border-radius: 5px;
    cursor: pointer;
}

.button-top-section:hover {
    background-color: var(--color-bg);
    color: var(--color-primary);
    border: 1px solid var(--color-primary);
}

.single_job_listing {
    max-width: 100%;
    margin: 40px auto;
    background: var(--color-bg);
    border-radius: 5px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    padding: 25px;
}

.meta-information-single {
    display: flex;
}

.meta-information-single p {
    font-family: Balgin Bold;
    font-size: 15px;
    color: var(--color-primary);
    border: 1px solid var(--color-primary);
    background-color: var(--color-accent);
    border-radius: 5px;
    padding: 5px 10px;
    margin-right: 10px;
    cursor: pointer;
}

.job-title h1 {
    padding-bottom: 10px;
    border-bottom: 2px solid var(--color-primary);
    font-family: Balgin Bold;
    font-size: 20px;
    padding-top: 20px;
}

.job_description {
    font-family: Poppins;
    font-size: 14px;
    font-weight: 400;
    line-height: 1.6;
    color: var(--color-text);
    margin-top: 20px;
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

.job-apply-button a {
    padding: 15px;
    background: var(--color-tertiary);
    color: var(--color-primary);
    border-radius: 5px;
    font-family: Balgin Bold;
    text-decoration: none;
    border: 2px solid var(--color-primary);
    display: inline-block;
    margin-top: 20px;
}

.job-apply-button a:hover {
    background: var(--color-bg);
    color: var(--color-primary);
    border: 1px solid var(--color-primary);
}

@media only screen and (max-width: 768px) {
    
    }


</style>


<style>
.recent-jobs-list {
    padding: 0;
    margin: 0;
}

.job-listing-simple {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 16px;
    margin: 0 auto 28px auto;
    width: 90%;
    border: 1px solid var(--color-bg);
    background-color: var(--color-bg);
    border-radius: 5px;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
    transition: all 0.2s ease-in-out;
}

.job-listing-simple:hover {
    border: 1px solid var(--color-primary);
}

.job-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100px;
    height: 100px;
    margin-left: -50px;
    background-color: var(--color-bg);
}

.job-logo img {
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

.job-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.job-title {
    font-size: 20px;
    line-height: 1.2;
    color: var(--color-text);
    margin-bottom: 5px;
}

.job-title a {
    color: var(--color-text);
    text-decoration: none;
    transition: color 0.2s ease-in-out;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
}

.job-title a:hover {
    color: var(--color-primary);
    text-decoration: none;
}

.job-meta {
    margin-bottom: 5px;
    margin-top: 5px;
}

.company-name {
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

a.google_map_link {
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

.job-manager .job-type, .job-types .job-type, .job_listing .job-type {
     font-family: Poppins, sans-serif;
    font-weight: 700;
    font-size: 12px;
    color: white; 
    border: 1px solid var(--color-primary);
    background-color: var(--color-primary);
    border-radius: 5px;
    padding: 5px 10px;
    cursor: pointer;
    margin-right: 5px;
    text-decoration: none; 
}

.job-description {
    font-size: 14px;
    line-height: 1.7;
    color: var(--color-text);
    font-family: Poppins, sans-serif;
    max-width: 100%;
    font-weight: 200;
}

.job-title-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-right: 10px;
}

.job-date {
    font-family: Poppins, sans-serif;
    font-size: 12px;
    color: var(--color-primary);
    font-weight: 200;
}

@media only screen and (max-width: 768px) {
    .job-listing-simple {
        flex-direction: column;
        align-items: flex-start;
        padding: 20px;
        width: 100%
    }

    .job-logo {
        margin-left: 0;
    }

    .job-title {
        font-size: 1.25rem;
    }

    .job-meta {
        font-size: 0.95rem;
    }

    .job-date {
        display: none;
    }
}
</style>
