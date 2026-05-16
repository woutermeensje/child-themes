<?php
if (!defined('ABSPATH')) {
    exit;
}

$sj_favorites_email_notice = null;
$sj_favorites_email_value  = '';

if (
    ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' &&
    isset($_POST['sj_favorites_email_nonce']) &&
    wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sj_favorites_email_nonce'])), 'sj_favorites_email')
) {
    $sj_favorites_email_value = sanitize_email(wp_unslash($_POST['sj_favorites_email'] ?? ''));
    $ids_raw                  = sanitize_text_field(wp_unslash($_POST['sj_favorite_job_ids'] ?? ''));
    $ids                      = array_filter(array_map('absint', explode(',', $ids_raw)));
    $ids                      = array_values(array_unique(array_slice($ids, 0, 100)));

    if (!is_email($sj_favorites_email_value)) {
        $sj_favorites_email_notice = [
            'type'    => 'error',
            'message' => 'Vul een geldig e-mailadres in.',
        ];
    } elseif (empty($ids)) {
        $sj_favorites_email_notice = [
            'type'    => 'error',
            'message' => 'Je hebt nog geen vacatures opgeslagen om te e-mailen.',
        ];
    } else {
        $jobs = get_posts([
            'post_type'      => 'job_listing',
            'post_status'    => 'publish',
            'post__in'       => $ids,
            'orderby'        => 'post__in',
            'posts_per_page' => count($ids),
        ]);

        if (empty($jobs)) {
            $sj_favorites_email_notice = [
                'type'    => 'error',
                'message' => 'De opgeslagen vacatures zijn niet meer beschikbaar.',
            ];
        } else {
            ob_start();
            ?>
            <div style="font-family: Arial, sans-serif; color:#333333; line-height:1.6;">
                <h1 style="color:#254F6E; font-size:24px; margin:0 0 12px;">Jouw opgeslagen vacatures</h1>
                <p style="margin:0 0 20px;">Je hebt deze vacatures opgeslagen op Sustainablejobs.nl.</p>

                <?php foreach ($jobs as $job) :
                    $job_data = function_exists('sj_get_job_favorite_data') ? sj_get_job_favorite_data($job->ID) : [];
                    if (empty($job_data)) {
                        continue;
                    }
                    ?>
                    <div style="padding:18px 0; border-top:1px solid #DEDEDE;">
                        <h2 style="font-size:18px; margin:0 0 6px;">
                            <a href="<?php echo esc_url($job_data['url']); ?>" style="color:#168AAD; text-decoration:none;"><?php echo esc_html($job_data['title']); ?></a>
                        </h2>
                        <?php if ($job_data['company'] || $job_data['location']) : ?>
                            <p style="margin:0 0 8px; color:#254F6E; font-weight:700;">
                                <?php echo esc_html(implode(' | ', array_filter([$job_data['company'], $job_data['location']]))); ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($job_data['excerpt']) : ?>
                            <p style="margin:0 0 10px;"><?php echo esc_html($job_data['excerpt']); ?></p>
                        <?php endif; ?>
                        <p style="margin:0;"><a href="<?php echo esc_url($job_data['url']); ?>" style="color:#168AAD;">Bekijk vacature</a></p>
                    </div>
                <?php endforeach; ?>

                <p style="margin:24px 0 0; font-size:13px; color:#777777;">Deze mail is verstuurd via Sustainablejobs.nl.</p>
            </div>
            <?php
            $message = ob_get_clean();
            $sent    = wp_mail(
                $sj_favorites_email_value,
                'Jouw opgeslagen vacatures op Sustainablejobs.nl',
                $message,
                ['Content-Type: text/html; charset=UTF-8']
            );

            $sj_favorites_email_notice = $sent
                ? [
                    'type'    => 'success',
                    'message' => 'Je opgeslagen vacatures zijn verstuurd.',
                ]
                : [
                    'type'    => 'error',
                    'message' => 'Het versturen is niet gelukt. Probeer het later opnieuw.',
                ];
        }
    }
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
            <div class="sj-favorites-page__layout">
                <div class="sj-favorites-page__main">
                    <div class="sj-favorites-page__list" data-sj-favorites-list></div>

                    <div class="sj-favorites-page__empty" data-sj-favorites-empty hidden>
                        <h2>Je hebt nog geen vacatures opgeslagen.</h2>
                        <p>Klik op het hartje bij een vacature om die hier terug te vinden.</p>
                        <a href="<?php echo esc_url(home_url('/vacatures/')); ?>" class="sj-favorites-page__empty-link">Bekijk vacatures</a>
                    </div>
                </div>

                <aside class="sj-favorites-page__sidebar">
                    <div class="sj-favorites-email" data-sj-favorites-email-card hidden>
                        <h2 class="sj-favorites-email__title">Mail mijn vacatures</h2>
                        <p class="sj-favorites-email__intro">
                            Ontvang een overzicht van je opgeslagen vacatures in je inbox.
                        </p>

                        <?php if ($sj_favorites_email_notice) : ?>
                            <p class="sj-favorites-email__notice sj-favorites-email__notice--<?php echo esc_attr($sj_favorites_email_notice['type']); ?>">
                                <?php echo esc_html($sj_favorites_email_notice['message']); ?>
                            </p>
                        <?php endif; ?>

                        <form method="post" class="sj-favorites-email__form" data-sj-favorites-email-form>
                            <?php wp_nonce_field('sj_favorites_email', 'sj_favorites_email_nonce'); ?>
                            <input type="hidden" name="sj_favorite_job_ids" value="" data-sj-favorites-email-ids>

                            <label class="sj-favorites-email__label" for="sj_favorites_email">E-mailadres</label>
                            <input
                                type="email"
                                name="sj_favorites_email"
                                id="sj_favorites_email"
                                class="sj-favorites-email__input"
                                placeholder="jouw@emailadres.nl"
                                value="<?php echo esc_attr($sj_favorites_email_value); ?>"
                                required
                            >

                            <button type="submit" class="sj-favorites-email__submit" data-sj-favorites-email-submit disabled>
                                Verstuur overzicht
                            </button>

                            <p class="sj-favorites-email__hint" data-sj-favorites-email-hint>
                                Je opgeslagen vacatures worden automatisch toegevoegd.
                            </p>
                        </form>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();
