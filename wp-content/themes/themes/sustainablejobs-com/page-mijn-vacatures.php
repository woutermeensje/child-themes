<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="content" class="site-main sj-favorites-page">
    <section class="sj-favorites-page__hero">
        <div class="sj-favorites-page__inner">
            <h1 class="sj-favorites-page__title">My saved jobs</h1>
            <p class="sj-favorites-page__intro">These are the jobs you saved with the heart icon.</p>
        </div>
    </section>

    <section class="sj-favorites-page__content" aria-live="polite">
        <div class="sj-favorites-page__inner">
            <div class="sj-favorites-page__list" data-sj-favorites-list></div>

            <div class="sj-favorites-page__empty" data-sj-favorites-empty hidden>
                <h2>You have not saved any jobs yet.</h2>
                <p>Click the heart icon on a job to save it here.</p>
                <a href="<?php echo esc_url(home_url('/jobs/')); ?>" class="sj-favorites-page__empty-link">View jobs</a>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();
