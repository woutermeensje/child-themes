<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="content" class="site-main sj-favorites-page">
    <section class="sj-favorites-page__hero">
        <div class="sj-favorites-page__inner">
            <h1 class="sj-favorites-page__title">Mijn vacatures</h1>
            <p class="sj-favorites-page__intro">Hier staan de vacatures die je met het hartje hebt opgeslagen.</p>
        </div>
    </section>

    <section class="sj-favorites-page__content" aria-live="polite">
        <div class="sj-favorites-page__inner">
            <div class="sj-favorites-page__list" data-sj-favorites-list></div>

            <div class="sj-favorites-page__empty" data-sj-favorites-empty hidden>
                <h2>Je hebt nog geen vacatures opgeslagen.</h2>
                <p>Klik op het hartje bij een vacature om die hier terug te vinden.</p>
                <a href="<?php echo esc_url(home_url('/vacatures/')); ?>" class="sj-favorites-page__empty-link">Bekijk vacatures</a>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();
