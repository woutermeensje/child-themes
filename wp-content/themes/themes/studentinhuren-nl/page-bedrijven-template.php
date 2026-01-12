<?php
/**
 * Template Name: bedrijven-template
 * Description: Template voor de bedrijven pagina
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

    <!-- Dynamische paginatitel als bedrijfsnaam -->
    <h1><?php the_title(); ?></h1>

    <!-- Bedrijfsgegevens -->
    <div class="company-section" id="company-info">
        <h2>About <?php the_title(); ?></h2>
        <?php the_content(); ?>
    </div>

    <!-- Openstaande vacatures via shortcode -->
    <div class="company-section" id="company-jobs">
        <h2>Open Positions</h2>
        <?php
        $company_slug = sanitize_title(get_the_title());
        echo do_shortcode('[company_jobs_simple slug="' . $company_slug . '"]');
        ?>
    </div>

    <!-- Laatste nieuwsberichten voor dit bedrijf -->
    <div class="company-section" id="company-news">
        <h2>Latest News</h2>
        <ul>
        <?php
        // Je kunt nieuws koppelen via categorie-slug op basis van paginatitel (slug match)
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

    <!-- Job alert formulier -->
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
            // Hier zou je e-mailadres kunnen opslaan in een opt-in systeem of mailchimp
            echo "<p>✅ You're now subscribed to job alerts for " . get_the_title() . ".</p>";
        }
        ?>
    </div>

</div>

<?php get_footer(); ?>