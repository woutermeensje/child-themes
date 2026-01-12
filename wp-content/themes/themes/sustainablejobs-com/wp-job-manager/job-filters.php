<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

wp_enqueue_script( 'wp-job-manager-ajax-filters' );

do_action( 'job_manager_job_filters_before', $atts );
?>

<form class="job_filters">

    <?php do_action( 'job_manager_job_filters_start', $atts ); ?>

    <div class="filter-header">
        <h2>Explore All Open Sustainability Jobs!</h2>
        <p>
            Or sign up for the <a href="https://sustainablejobs.com/sustainability-jobs-newsletter/" target="_blank" class="unstyled-newsletter-link">sustainability jobs newsletter</a>!
        </p>
    </div>

    <div class="search-basic">
        <div class="search_keywords">
            <input type="text" name="search_keywords" id="search_keywords" placeholder="Job title, sector or topic.." value="<?php echo esc_attr( $keywords ); ?>" />
        </div>
        <div class="search_location">
            <input type="text" name="search_location" id="search_location" placeholder="City or location" value="<?php echo esc_attr( $location ); ?>" />
        </div>

        <?php if ( get_option( 'job_manager_enable_categories' ) ) : ?>
            <div class="search_categories">
                <?php job_manager_dropdown_categories(); ?>
            </div>
        <?php endif; ?>
    </div>

    <?php do_action( 'job_manager_job_filters_end', $atts ); ?>
</form>

<?php do_action( 'job_manager_job_filters_after', $atts ); ?>


<style>
.job_filters {
    width: 90%;
    padding: 20px 0;
    margin: 20px auto;
    background-color: var(--color-bg);
    border: 1px solid var(--color-primary);
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
}

.filter-header {
    padding: 0 20px 10px 20px;
}

.filter-header p {
    font-family: Poppins;
    font-size: 15px;
    color: var(--color-text);
    margin: 10px 0;
}

.filter-header h2 {
    font-family: Balgin Bold;
    font-size: 25px;
    color: var(--color-text);
    margin-bottom: 15px;
    display: inline;
    background: linear-gradient(transparent 60%, var(--color-tertiary) 60%);
    font-weight: bold;
    border-radius: 2px;
}

body .filter-header a.unstyled-newsletter-link {
    color: var(--color-primary);
    text-decoration: none;
    font-weight: 400;
    font-family: "Poppins", sans-serif;
}

body .filter-header a.unstyled-newsletter-link:hover {
    color: var(--color-tertiary) !important;
    text-decoration: none;
}

.search-basic {
    display: flex;
    justify-content: left;
    gap: 20px;
    padding: 0 20px;
}

.search_location,
.search_keywords {
    flex-basis: 50%;
    max-width: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
}

@media (max-width: 768px) {
    .search-basic {
        flex-direction: column;
    }

    .search-basic > div {
        max-width: 100%;
        flex-basis: 100%;
    }
}

.search-basic input[type="text"] {
    width: 100%;
    padding: 12px 14px 12px 38px;
    font-size: 16px;
    border: 1px solid var(--color-border-light);
    border-radius: 0;
    background-color: var(--color-bg);
    color: var(--color-text);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.search-basic input[type="text"]::placeholder {
    color: var(--color-text-muted);
}

.search-basic input[type="text"]:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 2px 8px rgba(10, 107, 141, 0.25);
}

.search_keywords::before,
.search_location::before {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
    color: var(--color-primary);
    pointer-events: none;
}

.search_keywords::before {
    content: '🔍';
}

.search_location::before {
    content: '📍';
}
</style>
