<?php
/**
 * Inhuren.com – Job listing card
 */
if ( ! defined( 'ABSPATH' ) ) exit;
global $post;

$types = wpjm_get_the_job_types();
?>

<li <?php job_listing_class( 'ih-job' ); ?>
    data-longitude="<?php echo esc_attr( $post->geolocation_long ); ?>"
    data-latitude="<?php echo esc_attr( $post->geolocation_lat ); ?>"
>
    <a class="ih-job__card" href="<?php the_job_permalink(); ?>">

        <!-- Logo -->
        <div class="ih-job__logo">
            <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail( [72, 72] ); ?>
            <?php else : ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#c8cdd4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
            <?php endif; ?>
        </div>

        <!-- Links: bedrijf, titel -->
        <div class="ih-job__left">
            <span class="ih-job__company"><?php the_company_name(); ?></span>
            <h2 class="ih-job__title"><?php wpjm_the_job_title(); ?></h2>
        </div>

        <!-- Rechts: locatie + datum bovenaan, chips onderaan -->
        <div class="ih-job__right">
            <div class="ih-job__meta">
                <?php if ( get_the_job_location() ) : ?>
                    <span class="ih-job__meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        <?php the_job_location( false ); ?>
                    </span>
                <?php endif; ?>
                <span class="ih-job__meta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <?php the_job_publish_date(); ?>
                </span>
            </div>

            <?php
            $sectors = get_the_terms( $post->ID, 'job_sector' );
            $tags    = get_the_terms( $post->ID, 'job_tag' );
            $chips   = array_merge(
                is_array( $sectors ) ? $sectors : [],
                is_array( $tags )    ? $tags    : []
            );
            ?>
                <div class="ih-job__chips">
                    <?php if ( $types ) : foreach ( $types as $type ) : ?>
                        <span class="ih-job__chip ih-job__chip--type"><?php echo esc_html( $type->name ); ?></span>
                    <?php endforeach; endif; ?>
                    <?php foreach ( array_slice( $chips, 0, 4 ) as $chip ) : ?>
                        <span class="ih-job__chip"><?php echo esc_html( $chip->name ); ?></span>
                    <?php endforeach; ?>
                </div>
        </div>

    </a>
</li>

<style>
/* ==============================================
   Inhuren.com – Job listings (LaraJobs stijl)
   ============================================== */

ul.job_listings {
    list-style: none !important;
    max-width: 1080px !important;
    margin: 24px auto !important;
    padding: 0 !important;
    border: none !important;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

div.job_listings ul.job_listings {
    padding-left: 0 !important;
    border: none !important;
}

ul.job_listings li.ih-job {
    padding: 0 !important;
    margin: 0 !important;
    border: none !important;
    list-style: none !important;
}

/* ---- Card ---- */
.ih-job__card {
    display: flex !important;
    align-items: center;
    gap: 20px;
    padding: 0 !important;
    padding-right: 24px !important;
    background: #ffffff !important;
    border: 1px solid #dedede !important;
    border-radius: 5px !important;
    text-decoration: none !important;
    color: inherit !important;
    box-shadow: none !important;
    outline: none !important;
    overflow: hidden;
    transition: box-shadow .15s ease, border-color .15s ease, transform .15s ease;
}

.ih-job__card:hover {
    border-color: #c0c8d4 !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
    transform: translateY(-2px);
}

.ih-job__card:focus-visible {
    outline: 2px solid var(--color-primary, #2f5f80) !important;
    outline-offset: 2px;
}

/* ---- Logo ---- */
.ih-job__logo {
    flex: 0 0 108px;
    width: 108px;
    align-self: stretch;
    border-radius: 0;
    border: none;
    border-right: 1px solid #dedede;
    background: #fafafa;
    box-shadow: none;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.ih-job__logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    padding: 0;
    border-radius: 0 !important;
    box-shadow: none !important;
    transition: transform .2s ease;
}

.ih-job__card:hover .ih-job__logo img {
    transform: scale(1.04);
}

/* ---- Links ---- */
.ih-job__left {
    flex: 1 1 auto;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.ih-job__company {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: .03em;
    text-transform: uppercase;
    color: #9ca3af;
    line-height: 1.3;
}

.ih-job__title {
    margin: 0 !important;
    font-family: 'Roboto', sans-serif !important;
    font-size: 21px !important;
    font-weight: 700 !important;
    color: #111827 !important;
    line-height: 1.3 !important;
}

.ih-job__card:hover .ih-job__title {
    color: var(--color-primary, #2f5f80) !important;
}

/* ---- Rechts ---- */
.ih-job__right {
    flex: 0 0 auto;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 10px;
    min-width: 220px;
}

.ih-job__meta {
    display: flex;
    align-items: center;
    gap: 16px;
}

.ih-job__meta-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: #9ca3af;
    white-space: nowrap;
}

.ih-job__meta-item svg {
    flex-shrink: 0;
}

/* ---- Chips ---- */
.ih-job__chips {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    justify-content: flex-end;
}

.ih-job__chip {
    display: inline-flex;
    align-items: center;
    padding: 4px 14px;
    font-family: 'Roboto', sans-serif;
    font-size: 12px;
    font-weight: 700;
    color: #374151;
    background: #ffffff;
    border: 1px solid #dedede;
    border-radius: 999px;
    white-space: nowrap;
    line-height: 1.5;
    transition: border-color .15s ease, background .15s ease;
}

.ih-job__chip--type {
    color: var(--color-primary, #2f5f80);
    background: rgba(4, 88, 171, 0.08);
    border-color: rgba(4, 88, 171, 0.25);
}

.ih-job__card:hover .ih-job__chip {
    border-color: #c0c8d4;
}

/* ---- Mobiel ---- */
@media (max-width: 680px) {
    .ih-job__card {
        flex-wrap: wrap;
        align-items: flex-start;
        padding: 16px;
        gap: 14px;
    }

    .ih-job__logo {
        width: 78px;
        height: 78px;
        flex: 0 0 78px;
        border-radius: 8px;
    }

    .ih-job__right {
        min-width: 0;
        width: 100%;
        align-items: flex-start;
    }

    .ih-job__chips {
        justify-content: flex-start;
    }
}
</style>
