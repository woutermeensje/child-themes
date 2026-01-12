<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $post;

if ( job_manager_user_can_view_job_listing( $post->ID ) ) : ?>



<body>

<?php
$first = get_post_meta(get_the_ID(), '_contact_first_name', true);
$last  = get_post_meta(get_the_ID(), '_contact_last_name', true);
$email = get_post_meta(get_the_ID(), '_contact_email', true);
$phone = get_post_meta(get_the_ID(), '_contact_phone', true);

if ($first || $last || $email || $phone) :
?>
    <div class="job-contactpersoon">
        <h3>Reageren per e-mail?</h3>
        <div class="meta-information-single">
            <?php if ($first || $last): ?>
                <p><?php echo esc_html(trim($first . ' ' . $last)); ?></p>
            <?php endif; ?>

            <?php if ($email): ?>
                <p>
                    <a class="link-color" href="mailto:<?php echo antispambot(esc_attr($email)); ?>">
                        <?php echo antispambot(esc_html($email)); ?>
                    </a>
                </p>
            <?php endif; ?>

            <?php if ($phone): ?>
                <p>
                    <a class="link-color" href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $phone)); ?>">
                        <?php echo esc_html($phone); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<style>
    .link-color {
        color: #3A89FF;
    }
</style>
    
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
                   
                
                    <?php do_action( 'single_job_listing_end' ); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php else : ?>
    <?php get_job_manager_template_part( 'access-denied', 'single-job_listing' ); ?>
<?php endif; ?>
</div>



</body>


<style>

    h1.entry-title {
        display: none; 
    }
    
h1.entry-title {
    display: none; 
}

.single_job_listing {
    max-width: 900px;
    margin: 40px auto;
    background: var(--color-bg);
    border-radius: 5px;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
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
    background-color: var(--color-bg);
    border-radius: 5px;
    padding: 5px 10px;
    margin-right: 10px;
}

li.meta-information-single {
      font-family: Balgin Bold;
    font-size: 15px;
    color: var(--color-primary);
    border: 1px solid var(--color-primary);
    background-color: var(--color-bg);
    border-radius: 5px;
    padding: 5px 10px;
    margin-right: 10px;
}

.job-title h1 {
    padding-bottom: 10px;
    border-bottom: 2px solid var(--color-primary);
    font-family: Balgin Bold;
    font-size: 20px;
    padding-top: 20px;
}

.job_description {
    font-family: Poppins, sans-serif;
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

.job-contactpersoon {
   max-width: 900px;
    margin: 40px auto;
    background: var(--color-bg);
    border-radius: 5px;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
    padding: 25px;
    border: 1px solid #3A89FF;
}

.job-contactpersoon h3 {
    margin-top: 0;
    font-family: Balgin Bold;
    font-size: 24px;
    color: #333333;
    margin-bottom: 10px;
    margin-bottom: 20px; 
}

.job-contactpersoon ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.job-contactpersoon li {
    margin-bottom: 6px;
    font-family: Poppins, sans-serif;
    font-size: 14px;
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
