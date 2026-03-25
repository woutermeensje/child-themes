<?php
if (!defined('ABSPATH')) exit;
global $post;

if (job_manager_user_can_view_job_listing($post->ID)) :
    $company_name    = function_exists('get_the_company_name') ? get_the_company_name() : '';
    $company_website = get_post_meta($post->ID, '_company_website', true);
    $company_slug    = $company_name ? sanitize_title($company_name) : '';
    $company_url     = $company_slug ? home_url('/organisaties/' . $company_slug . '/') : '';
?>

<div class="sj-wrap">

    <div class="sj-topbar">
        <div class="sj-topbar__text">
            <p>Blijf op de hoogte van nieuwe vacatures voor ingenieurs!</p>
        </div>
        <div>
            <a href="<?php echo esc_url(home_url('/vacature-alert/')); ?>" class="sj-topbar__link">Vacature alert instellen</a>
        </div>
    </div>

    <div class="sj-card">

        <?php if (get_option('job_manager_hide_expired_content', 1) && 'expired' === $post->post_status) : ?>
            <div class="job-manager-info"><?php _e('This listing has expired.', 'wp-job-manager'); ?></div>
        <?php else : ?>

            <div class="sj-meta">
                <span class="sj-chip">🗓️ <?php echo esc_html(date_i18n('j F Y', get_post_time('U'))); ?></span>
                <span class="sj-chip">🏷️ <?php the_job_type(); ?></span>

                <?php if (!empty($company_name) && !empty($company_url)) : ?>
                    <a href="<?php echo esc_url($company_url); ?>" class="sj-chip sj-chip--link">🏢 <?php echo esc_html($company_name); ?></a>
                <?php elseif (!empty($company_name)) : ?>
                    <span class="sj-chip">🏢 <?php echo esc_html($company_name); ?></span>
                <?php endif; ?>
            </div>

            <header class="sj-header">
                <h1 class="sj-title"><?php wpjm_the_job_title(); ?></h1>
                <?php if (!empty($company_name)) : ?>
                    <p class="sj-subtitle"><?php echo esc_html($company_name); ?></p>
                <?php endif; ?>
            </header>

            <div class="job_description sj-content">
                <?php wpjm_the_job_description(); ?>
            </div>

            <?php if (!empty($company_website)) : ?>
                <div class="sj-actions">
                    <a href="<?php echo esc_url($company_website); ?>" class="sj-btn" target="_blank" rel="noopener">
                        Solliciteren bij werkgever
                    </a>
                </div>
            <?php endif; ?>

            <?php do_action('single_job_listing_end'); ?>

        <?php endif; ?>
    </div>

    <?php
    // Contactpersoon
    $cf = get_post_meta(get_the_ID(), '_contact_first_name', true);
    $cl = get_post_meta(get_the_ID(), '_contact_last_name', true);
    $ce = get_post_meta(get_the_ID(), '_contact_email', true);
    if ($cf || $cl || $ce) :
    ?>
    <section class="sj-contact sj-card">
        <h2 class="sj-contact-title">Contactpersoon</h2>
        <div class="sj-contact-grid">
            <?php if ($cf || $cl) : ?>
                <div class="sj-contact-row">
                    <span class="sj-contact-label">Naam</span>
                    <span class="sj-contact-value"><?php echo esc_html(trim($cf . ' ' . $cl)); ?></span>
                </div>
            <?php endif; ?>
            <?php if ($ce) : ?>
                <div class="sj-contact-row">
                    <span class="sj-contact-label">E-mail</span>
                    <a class="sj-contact-value sj-contact-link" href="mailto:<?php echo esc_attr(antispambot($ce)); ?>">
                        <?php echo esc_html(antispambot($ce)); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php
    // Recente vacatures
    $recent_jobs = new WP_Query([
        'post_type'      => 'job_listing',
        'posts_per_page' => 6,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post__not_in'   => [get_the_ID()],
        'post_status'    => 'publish',
    ]);

    if ($recent_jobs->have_posts()) :
    ?>
    <section class="sj-recent sj-card">
        <div class="sj-recent-head">
            <h2 class="sj-recent-title">Recente vacatures</h2>
            <p class="sj-recent-sub">Bekijk de nieuwste vacatures voor ingenieurs.</p>
        </div>
        <ul class="sj-recent-grid">
            <?php while ($recent_jobs->have_posts()) : $recent_jobs->the_post(); ?>
                <?php
                $rc_name    = function_exists('get_the_company_name') ? get_the_company_name() : '';
                $rc_title   = function_exists('wpjm_get_the_job_title') ? wpjm_get_the_job_title() : get_the_title();
                $rc_excerpt = wp_trim_words(get_the_excerpt(), 14, '…');
                ?>
                <li class="sj-recent-item" <?php job_listing_class(); ?>>
                    <a class="sj-recent-link" href="<?php the_job_permalink(); ?>">
                        <div class="sj-recent-logo"><?php the_company_logo(); ?></div>
                        <div class="sj-recent-body">
                            <?php if ($rc_name) : ?>
                                <div class="sj-recent-company"><?php echo esc_html($rc_name); ?></div>
                            <?php endif; ?>
                            <h3 class="sj-recent-jobtitle"><?php echo esc_html($rc_title); ?></h3>
                            <?php if ($rc_excerpt) : ?>
                                <p class="sj-recent-excerpt"><?php echo esc_html($rc_excerpt); ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                </li>
            <?php endwhile; wp_reset_postdata(); ?>
        </ul>
    </section>
    <?php endif; ?>

</div>

<?php else : ?>
    <?php get_job_manager_template_part('access-denied', 'single-job_listing'); ?>
<?php endif; ?>

<style>
/* ==============================================
   Ingenieurvacaturebank – Single job
   ============================================== */
:root {
  --sj-ink:    #111827;
  --sj-muted:  #6B7280;
  --sj-border: #E5E7EB;
  --sj-blue:   #0A6B8D;
  --sj-blue-dk:#085978;
}

html, body { width: 100%; max-width: 100%; overflow-x: hidden; }

h1.entry-title { display: none; }

.sj-wrap {
    max-width: 900px;
    width: 100%;
    margin: 20px auto;
    padding: 0 16px;
    display: grid;
    gap: 16px;
}

.sj-card {
    padding: 24px;
    background: #ffffff;
    border: 1px solid #DEDEDE;
    box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
    border-radius: 5px;
}

/* Topbalk */
.sj-topbar {
    width: 100%;
    background: #fff;
    border: 1px solid #DEDEDE;
    box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
    border-radius: 5px;
    padding: 20px 24px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.sj-topbar__text {
    flex: 1 1 auto;
}

.sj-topbar__text p {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

.sj-topbar__link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 14px;
    border-radius: 8px;
    background: rgba(10, 107, 141, 0.12);
    color: #0A6B8D !important;
    border: 1px solid #0A6B8D;
    text-decoration: none !important;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 14px;
    transition: transform .15s ease, box-shadow .15s ease;
    flex-shrink: 0;
}

.sj-topbar__link:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(10,107,141,0.18);
}

/* Chips */
.sj-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 14px;
}

.sj-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    border-radius: 999px;
    border: 1px solid #DEDEDE;
    background: #fff;
    color: #333;
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.sj-chip--link { text-decoration: none !important; }
.sj-chip--link:hover { border-color: rgba(10,107,141,.35); }

/* Header */
.sj-header {
    padding-bottom: 14px;
    border-bottom: 1px solid var(--sj-border);
    margin-bottom: 16px;
    margin-top: 24px;
}

.sj-title {
    margin: 0;
    font-family: 'Inter', sans-serif;
    font-size: 26px;
    font-weight: 800;
    color: var(--sj-ink);
    overflow-wrap: anywhere;
    word-break: break-word;
}

.sj-subtitle {
    margin: 8px 0 0;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: var(--sj-blue);
}

/* Content */
.sj-content {
    font-family: 'Poppins', sans-serif;
    color: var(--sj-ink);
    font-size: 15px;
    line-height: 1.75;
    overflow-wrap: anywhere;
    word-break: break-word;
}

.sj-content p { margin: 0 0 14px; }

/* Solliciteer knop */
.sj-actions {
    margin-top: 18px;
    display: flex;
}

.sj-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 20px;
    border-radius: 8px;
    background: rgba(10, 107, 141, 0.12);
    color: var(--sj-blue) !important;
    text-decoration: none !important;
    border: 1px solid var(--sj-blue);
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 14px;
    transition: transform .15s ease, box-shadow .15s ease;
}

.sj-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(10,107,141,0.18);
}

/* Contact */
.sj-contact-title {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 20px;
    color: #333;
    margin: 0 0 16px;
}

.sj-contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 28px 56px;
}

.sj-contact-row { display: grid; gap: 8px; }

.sj-contact-label {
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    font-weight: 600;
    color: #7A7F87;
}

.sj-contact-value {
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    color: #333;
    word-break: break-word;
}

.sj-contact-link {
    color: var(--sj-blue) !important;
    text-decoration: none !important;
}

.sj-contact-link:hover { text-decoration: underline !important; }

/* Recente vacatures */
.sj-recent-head { margin-bottom: 18px; }

.sj-recent-title {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 20px;
    color: var(--sj-ink);
}

.sj-recent-sub {
    margin: 6px 0 0;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    color: var(--sj-muted);
}

.sj-recent-grid {
    list-style: none;
    padding: 0; margin: 0;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
}

.sj-recent-item {
    background: #fff;
    border: 1px solid var(--sj-border);
    border-radius: 5px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    transition: transform .18s ease, border-color .18s ease;
}

.sj-recent-item:hover {
    transform: translateY(-3px);
    border-color: rgba(10,107,141,.35);
}

.sj-recent-link {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 18px;
    text-decoration: none !important;
    color: inherit;
}

.sj-recent-logo img {
    display: block;
    width: 120px;
    max-height: 80px;
    object-fit: contain;
    margin: 0 !important;
}

.sj-recent-company {
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    font-weight: 700;
    color: var(--sj-blue);
}

h3.sj-recent-jobtitle {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    font-weight: 700;
    color: var(--sj-ink);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.sj-recent-excerpt {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    color: var(--sj-muted);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.sj-recent-item:hover .sj-recent-jobtitle { color: var(--sj-blue); }

@media (max-width: 768px) {
    .sj-topbar {
        flex-direction: column;
        align-items: stretch;
    }

    .sj-topbar__link { width: 100%; justify-content: center; }

    .sj-contact-grid { grid-template-columns: 1fr; gap: 18px; }

    .sj-recent-grid { grid-template-columns: 1fr; }

    .sj-wrap, .sj-card { width: 100% !important; max-width: 100% !important; box-sizing: border-box !important; }
}
</style>
