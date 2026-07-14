<?php
/**
 * Template Name: companies-template
 * Description: Template for the company page
 */

get_header(); ?>


<style>
.company-page-wrapper {
    max-width: 1000px;
    margin: 40px auto;
    padding: 30px;
    font-family: 'Poppins', sans-serif;
    background-color: #fff;
    box-shadow: 0 10px 30px -5px rgba(0,0,0,0.1);
    border: 1px solid #e0e0e0;
}

.company-page-wrapper h1 {
    font-size: 32px;
    font-weight: 600;
    margin-bottom: 10px;
    color: #0a6b8d;
}

.company-section {
    margin-top: 40px;
}

.company-section h2 {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 15px;
    border-bottom: 2px solid #0a6b8d;
    padding-bottom: 5px;
    color: #333;
}

.company-section p,
.company-section li {
    font-size: 16px;
    color: #444;
    line-height: 1.6;
}

.job-alert-form input[type="email"] {
    width: 100%;
    padding: 12px;
    margin-top: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.job-alert-form input[type="submit"] {
    background-color: #0a6b8d;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
</style>

<div class="company-page-wrapper">

    <!-- Dynamic page title as company name. -->
    <h1><?php the_title(); ?></h1>

    <!-- Company details. -->
    <div class="company-section" id="company-info">
        <h2>About <?php the_title(); ?></h2>
        <?php the_content(); ?>
    </div>

    <!-- Open jobs via shortcode. -->
    <div class="company-section" id="company-jobs">
        <h2>Open Positions</h2>
        <?php
        $company_slug = sanitize_title(get_the_title());
        echo do_shortcode('[company_jobs_simple slug="' . $company_slug . '"]');
        ?>
    </div>

    <!-- Latest news posts for this company. -->
    <div class="company-section" id="company-news">
        <h2>Latest News</h2>
        <ul>
        <?php
        // News can be linked via category slug based on the page title.
        $news_query = new WP_Query([
            'post_type' => 'post',
            'posts_per_page' => 3,
            'category_name' => sanitize_title(get_the_title())
        ]);

        if ($news_query->have_posts()) :
            while ($news_query->have_posts()) : $news_query->the_post(); ?>
                <li>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a><br>
                    <small><?php the_time('F j, Y'); ?></small>
                </li>
            <?php endwhile;
        else : ?>
            <p>No news articles found for this company.</p>
        <?php endif;
        wp_reset_postdata();
        ?>
        </ul>
    </div>

    <!-- Job alert form. -->
    <div class="company-section" id="job-alert">
        <h2>Stay updated</h2>
        <p>Receive an email when new jobs are published for <?php the_title(); ?>.</p>
        <form method="post" class="job-alert-form">
            <input type="email" name="job_alert_email" placeholder="Your email address" required>
            <input type="submit" name="submit_alert" value="Set up job alert">
        </form>

        <?php
        if (isset($_POST['submit_alert']) && is_email($_POST['job_alert_email'])) {
            $email = sanitize_email($_POST['job_alert_email']);
            // This could be stored in an opt-in system or Mailchimp.
            echo "<p>✅ You're now subscribed to job alerts for " . get_the_title() . ".</p>";
        }
        ?>
    </div>

</div>

<?php get_footer(); ?>
