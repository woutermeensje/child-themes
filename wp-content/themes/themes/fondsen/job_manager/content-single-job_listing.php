<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $post;

$post_id = isset( $post->ID ) ? (int) $post->ID : 0;

if ( $post_id && job_manager_user_can_view_job_listing( $post_id ) ) :

    $con_first       = get_post_meta($post_id, '_contact_first_name', true);
    $con_last        = get_post_meta($post_id, '_contact_last_name',  true);
    $con_email       = get_post_meta($post_id, '_contact_email',      true);
    $location        = get_post_meta($post_id, '_job_location',       true);
    $company_website = get_post_meta($post_id, '_company_website',    true);

    $location_coords = ($location && function_exists('fondsen_get_job_location_coords'))
        ? fondsen_get_job_location_coords($post_id, $location)
        : null;

    $job_company_terms = get_the_terms($post_id, 'job_company');
    $company_term      = (!is_wp_error($job_company_terms) && !empty($job_company_terms)) ? $job_company_terms[0] : null;
    $company_name      = $company_term ? $company_term->name : '';
    $company_slug      = $company_term ? $company_term->slug : '';
    $company_url       = $company_slug ? home_url('/vacatures/' . $company_slug . '/') : '';

    /* ── Vraag-formulier verwerking ── */
    $vraag_success = false;
    $vraag_error   = '';
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['fn_vraag_nonce']) &&
        wp_verify_nonce($_POST['fn_vraag_nonce'], 'fn_stel_vraag_' . $post_id)
    ) {
        $v_naam  = sanitize_text_field($_POST['vraag_voornaam']   ?? '');
        $v_ach   = sanitize_text_field($_POST['vraag_achternaam'] ?? '');
        $v_email = sanitize_email($_POST['vraag_email']           ?? '');
        $v_tel   = sanitize_text_field($_POST['vraag_telefoon']   ?? '');
        $v_vraag = sanitize_textarea_field($_POST['vraag_tekst']  ?? '');
        $to      = $con_email ?: 'info@fondsen.org';

        $attachments = [];
        if (!empty($_FILES['vraag_cv']['tmp_name'])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $upload = media_handle_upload('vraag_cv', 0);
            if (!is_wp_error($upload)) {
                $path = get_attached_file($upload);
                if (file_exists($path)) $attachments[] = $path;
            }
        }

        if ($v_naam && is_email($v_email) && $v_vraag) {
            $subject = 'Vraag over vacature: ' . get_the_title($post_id);
            $body    = "Vraag via de vacaturepagina:\n\nVan: $v_naam $v_ach <$v_email>";
            if ($v_tel) $body .= "\nTelefoon: $v_tel";
            $body   .= "\n\n$v_vraag";
            wp_mail($to, $subject, $body, [
                'Content-Type: text/plain; charset=UTF-8',
                "Reply-To: $v_naam $v_ach <$v_email>",
                'Bcc: info@fondsen.org',
            ], $attachments);
            $vraag_success = true;
        } else {
            $vraag_error = 'Vul alle verplichte velden in.';
        }
    }
?>

<!-- Top banner -->
<div class="fn-single-top">
    <div class="fn-single-top__inner">
        <p>Blijf op de hoogte van de laatste vacatures!</p>
        <a href="https://www.fondsen.org/vacature-alert-instellen/" class="fn-single-top__link">Vacature Alert instellen</a>
    </div>
</div>

<div class="fn-single-layout">

    <!-- Linker kolom -->
    <div class="fn-single-layout__main">

        <div class="fn-single-card">
            <?php if ( get_option('job_manager_hide_expired_content', 1) && 'expired' === $post->post_status ) : ?>
                <div class="fn-expired-notice"><?php _e('This listing has expired.', 'wp-job-manager'); ?></div>
            <?php else : ?>

                <div class="fn-single-meta">
                    <span class="fn-chip">🗓️ <?php echo esc_html(date_i18n('j F Y', get_post_time('U'))); ?></span>
                    <span class="fn-chip">🏷️ <?php wpjm_the_job_types(); ?></span>
                    <?php if ($company_name && $company_url) : ?>
                        <a href="<?php echo esc_url($company_url); ?>" class="fn-chip fn-chip--link">🏢 <?php echo esc_html($company_name); ?></a>
                    <?php elseif ($company_name) : ?>
                        <span class="fn-chip">🏢 <?php echo esc_html($company_name); ?></span>
                    <?php endif; ?>
                </div>

                <h1 class="fn-single-title fondsen-job-title"><?php wpjm_the_job_title(); ?></h1>
                <?php if ($company_name) : ?>
                    <p class="fn-single-subtitle"><?php echo esc_html($company_name); ?></p>
                <?php endif; ?>

                <div class="job_description fn-single-content">
                    <?php wpjm_the_job_description(); ?>
                </div>

                <?php if (!empty($company_website)) : ?>
                    <div class="fn-single-actions">
                        <a href="<?php echo esc_url($company_website); ?>" class="fn-single-btn" target="_blank" rel="noopener">
                            Solliciteren bij werkgever
                        </a>
                    </div>
                <?php endif; ?>

                <?php do_action('single_job_listing_end'); ?>

            <?php endif; ?>
        </div>

        <!-- Stel een vraag blok -->
        <div class="fn-vraag-blok">
            <h3 class="fn-vraag-blok__title">Stel een vraag aan de contactpersoon van deze vacature.</h3>

            <?php if ($vraag_success) : ?>
                <p class="fn-vraag-success">Je vraag is verstuurd! We nemen zo snel mogelijk contact met je op.</p>
            <?php else : ?>
                <?php if ($vraag_error) : ?>
                    <p class="fn-vraag-error"><?php echo esc_html($vraag_error); ?></p>
                <?php endif; ?>
                <form method="post" class="fn-vraag-blok__form" enctype="multipart/form-data" novalidate>
                    <?php wp_nonce_field('fn_stel_vraag_' . $post_id, 'fn_vraag_nonce'); ?>

                    <div class="fn-vraag-blok__row">
                        <div class="fn-vraag-blok__field">
                            <label class="fn-vraag-blok__label" for="vraag_voornaam">Voornaam <span class="fn-vraag-req">*</span></label>
                            <input type="text" name="vraag_voornaam" id="vraag_voornaam" class="fn-vraag-blok__input" placeholder="Voornaam" required value="<?php echo esc_attr($_POST['vraag_voornaam'] ?? ''); ?>">
                        </div>
                        <div class="fn-vraag-blok__field">
                            <label class="fn-vraag-blok__label" for="vraag_achternaam">Achternaam</label>
                            <input type="text" name="vraag_achternaam" id="vraag_achternaam" class="fn-vraag-blok__input" placeholder="Achternaam" value="<?php echo esc_attr($_POST['vraag_achternaam'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="fn-vraag-blok__row">
                        <div class="fn-vraag-blok__field">
                            <label class="fn-vraag-blok__label" for="vraag_email">E-mailadres <span class="fn-vraag-req">*</span></label>
                            <input type="email" name="vraag_email" id="vraag_email" class="fn-vraag-blok__input" placeholder="jouw@emailadres.nl" required value="<?php echo esc_attr($_POST['vraag_email'] ?? ''); ?>">
                        </div>
                        <div class="fn-vraag-blok__field">
                            <label class="fn-vraag-blok__label" for="vraag_telefoon">Telefoonnummer</label>
                            <input type="tel" name="vraag_telefoon" id="vraag_telefoon" class="fn-vraag-blok__input" placeholder="+31 6 12345678" value="<?php echo esc_attr($_POST['vraag_telefoon'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="fn-vraag-blok__field">
                        <label class="fn-vraag-blok__label">Je vraag of motivatie <span class="fn-vraag-req">*</span></label>
                        <div class="fn-vraag-blok__quill-wrap">
                            <div id="fn_vraag_quill" style="min-height:160px;"></div>
                        </div>
                        <textarea name="vraag_tekst" id="fn_vraag_hidden" class="fn-vraag-blok__hidden" aria-hidden="true"><?php echo esc_textarea($_POST['vraag_tekst'] ?? ''); ?></textarea>
                    </div>

                    <div class="fn-vraag-blok__field">
                        <label class="fn-vraag-blok__label" for="vraag_cv">CV uploaden <span style="font-weight:400;color:#6b7280;font-size:12px;">(optioneel)</span></label>
                        <label class="fn-vraag-blok__upload" for="vraag_cv">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M213.66,82.34l-56-56A8,8,0,0,0,152,24H56A16,16,0,0,0,40,40V216a16,16,0,0,0,16,16H200a16,16,0,0,0,16-16V88A8,8,0,0,0,213.66,82.34ZM160,51.31,188.69,80H160ZM200,216H56V40h88V88a8,8,0,0,0,8,8h48V216Zm-72-96a8,8,0,0,1,8,8v16h16a8,8,0,0,1,0,16H136v16a8,8,0,0,1-16,0V160H104a8,8,0,0,1,0-16h16V128A8,8,0,0,1,128,120Z"/></svg>
                            <span class="fn-vraag-blok__upload-label">Kies je CV</span>
                            <span class="fn-vraag-blok__upload-name" id="fn_vraag_cv_name">Geen bestand gekozen</span>
                            <input type="file" name="vraag_cv" id="vraag_cv" accept=".pdf,.doc,.docx" class="fn-vraag-blok__upload-input" onchange="document.getElementById('fn_vraag_cv_name').textContent = this.files[0]?.name || 'Geen bestand gekozen'">
                        </label>
                        <span class="fn-vraag-blok__hint">PDF, Word. Max. 5 MB.</span>
                    </div>

                    <button type="submit" class="fn-vraag-blok__submit">Versturen</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Recente vacatures -->
        <?php
        $recent_jobs = new WP_Query([
            'post_type'      => 'job_listing',
            'posts_per_page' => 6,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post__not_in'   => [$post_id],
            'post_status'    => 'publish',
        ]);
        ?>
        <?php if ($recent_jobs->have_posts()) : ?>
            <section class="fn-recent">
                <div class="fn-recent-head">
                    <h2 class="fn-recent-title">Recente vacatures</h2>
                    <p class="fn-recent-sub">Bekijk de nieuwste vacatures in ons netwerk.</p>
                </div>
                <ul class="fn-recent-grid">
                    <?php while ($recent_jobs->have_posts()) : $recent_jobs->the_post(); ?>
                        <?php
                        $rc_terms   = get_the_terms(get_the_ID(), 'job_company');
                        $rc_name    = (!is_wp_error($rc_terms) && !empty($rc_terms)) ? $rc_terms[0]->name : '';
                        $rc_title   = function_exists('wpjm_get_the_job_title') ? wpjm_get_the_job_title() : get_the_title();
                        $rc_excerpt = wp_trim_words(get_the_excerpt(), 14, '…');
                        ?>
                        <li class="fn-recent-item" <?php job_listing_class(); ?>>
                            <a class="fn-recent-link" href="<?php the_job_permalink(); ?>" aria-label="<?php echo esc_attr($rc_title); ?>">
                                <div class="fn-recent-logo"><?php the_company_logo(); ?></div>
                                <div class="fn-recent-body">
                                    <?php if ($rc_name) : ?>
                                        <div class="fn-recent-company"><?php echo esc_html($rc_name); ?></div>
                                    <?php endif; ?>
                                    <h3 class="fn-recent-jobtitle"><?php echo esc_html($rc_title); ?></h3>
                                    <?php if ($rc_excerpt) : ?>
                                        <p class="fn-recent-excerpt"><?php echo esc_html($rc_excerpt); ?></p>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </li>
                    <?php endwhile; wp_reset_postdata(); ?>
                </ul>
            </section>
        <?php endif; ?>

    </div>

    <!-- Rechter kolom: sidebar -->
    <aside class="fn-single-layout__sidebar">

        <!-- Vacature details -->
        <div class="fn-sidebar-block">
            <p class="fn-sidebar__block-title">Vacature details</p>
            <div class="fn-sidebar__details">

                <?php if ($company_name) : ?>
                    <div class="fn-sidebar__detail-row">
                        <span class="fn-sidebar__detail-label">Organisatie</span>
                        <span class="fn-sidebar__detail-value"><?php echo esc_html($company_name); ?></span>
                    </div>
                <?php endif; ?>

                <?php $types = wpjm_get_the_job_types(); if (!empty($types)) : ?>
                    <div class="fn-sidebar__detail-row">
                        <span class="fn-sidebar__detail-label">Type baan</span>
                        <span class="fn-sidebar__detail-value">
                            <?php foreach ($types as $type) : ?>
                                <span class="fn-sidebar__chip"><?php echo esc_html($type->name); ?></span>
                            <?php endforeach; ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ($location) : ?>
                    <div class="fn-sidebar__detail-row">
                        <span class="fn-sidebar__detail-label">Standplaats</span>
                        <span class="fn-sidebar__detail-value"><?php echo esc_html($location); ?></span>
                    </div>
                <?php endif; ?>

                <?php $sectors = get_the_terms($post_id, 'job_sector'); if (!is_wp_error($sectors) && !empty($sectors)) : ?>
                    <div class="fn-sidebar__detail-row">
                        <span class="fn-sidebar__detail-label">Sector</span>
                        <span class="fn-sidebar__detail-value">
                            <?php foreach ($sectors as $s) : ?>
                                <span class="fn-sidebar__chip"><?php echo esc_html($s->name); ?></span>
                            <?php endforeach; ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php $org_types = get_the_terms($post_id, 'organization_type'); if (!is_wp_error($org_types) && !empty($org_types)) : ?>
                    <div class="fn-sidebar__detail-row">
                        <span class="fn-sidebar__detail-label">Type organisatie</span>
                        <span class="fn-sidebar__detail-value">
                            <?php foreach ($org_types as $o) : ?>
                                <span class="fn-sidebar__chip"><?php echo esc_html($o->name); ?></span>
                            <?php endforeach; ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php $certs = get_the_terms($post_id, 'certificering'); if (!is_wp_error($certs) && !empty($certs)) : ?>
                    <div class="fn-sidebar__detail-row">
                        <span class="fn-sidebar__detail-label">Certificering</span>
                        <span class="fn-sidebar__detail-value">
                            <?php foreach ($certs as $c) : ?>
                                <span class="fn-sidebar__chip"><?php echo esc_html($c->name); ?></span>
                            <?php endforeach; ?>
                        </span>
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <?php if ($location && $location_coords) : ?>
        <!-- Locatiekaart -->
        <div class="fn-sidebar-block fn-map-block">
            <p class="fn-sidebar__block-title">Locatie</p>
            <div id="fn-job-map"
                 class="fn-job-map"
                 data-lat="<?php echo esc_attr($location_coords['lat']); ?>"
                 data-lng="<?php echo esc_attr($location_coords['lng']); ?>"
                 data-label="<?php echo esc_attr($location); ?>">
            </div>
        </div>
        <?php endif; ?>

        <?php if ($con_first || $con_last || $con_email) : ?>
        <!-- Contactpersoon -->
        <div class="fn-sidebar-block">
            <p class="fn-sidebar__block-title">Contactpersoon</p>
            <div class="fn-sidebar__contact">
                <?php if ($con_first || $con_last) : ?>
                    <p class="fn-sidebar__contact-name"><?php echo esc_html(trim("$con_first $con_last")); ?></p>
                <?php endif; ?>
                <?php if ($con_email) : ?>
                    <a href="mailto:<?php echo esc_attr(antispambot($con_email)); ?>" class="fn-sidebar__contact-email">
                        <?php echo esc_html(antispambot($con_email)); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Gerelateerde vacatures -->
        <?php
        $sidebar_jobs = new WP_Query([
            'post_type'      => 'job_listing',
            'posts_per_page' => 5,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post__not_in'   => [$post_id],
            'post_status'    => 'publish',
        ]);
        ?>
        <?php if ($sidebar_jobs->have_posts()) : ?>
        <div class="fn-sidebar-block">
            <p class="fn-sidebar__block-title">Gerelateerde Vacatures</p>
            <ul class="fn-sidebar-jobs">
                <?php while ($sidebar_jobs->have_posts()) : $sidebar_jobs->the_post(); ?>
                    <?php
                    $sj_terms = get_the_terms(get_the_ID(), 'job_company');
                    $sj_org   = (!is_wp_error($sj_terms) && !empty($sj_terms)) ? $sj_terms[0]->name : '';
                    $sj_title = function_exists('wpjm_get_the_job_title') ? wpjm_get_the_job_title() : get_the_title();
                    ?>
                    <li class="fn-sidebar-job">
                        <a href="<?php the_job_permalink(); ?>" class="fn-sidebar-job__link">
                            <?php if ($sj_org) : ?>
                                <span class="fn-sidebar-job__org"><?php echo esc_html($sj_org); ?></span>
                            <?php endif; ?>
                            <span class="fn-sidebar-job__title"><?php echo esc_html($sj_title); ?></span>
                        </a>
                    </li>
                <?php endwhile; wp_reset_postdata(); ?>
            </ul>
        </div>
        <?php endif; ?>

    </aside>
</div>

<?php else : ?>
    <?php get_job_manager_template_part('access-denied', 'single-job_listing'); ?>
<?php endif; ?>

<style>
:root {
    --fn-ink:    #111827;
    --fn-muted:  #6B7280;
    --fn-border: #E5E7EB;
    --fn-radius: 5px;
    --fn-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
}

/* ── Top banner ── */
.fn-single-top {
    max-width: 1100px;
    width: 100%;
    margin: 24px auto 0;
    padding: 0 24px;
    box-sizing: border-box;
}

.fn-single-top__inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    background: #fff;
    border: 1px solid var(--fn-border);
    border-radius: var(--fn-radius);
    box-shadow: none;
    gap: 16px;
}

.fn-single-top__inner p {
    margin: 0;
    font-family: Poppins, system-ui, sans-serif;
    font-size: 18px;
    font-weight: 600;
    color: #333;
    flex: 1 1 auto;
}

.fn-single-top__link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 14px;
    border-radius: 8px;
    background: rgba(8,132,204,0.15);
    color: var(--color-secondary) !important;
    border: 1px solid var(--color-secondary);
    text-decoration: none !important;
    font-family: Poppins, system-ui, sans-serif;
    font-weight: 600;
    font-size: 14px;
    white-space: nowrap;
    transition: transform .15s ease, box-shadow .15s ease;
}

.fn-single-top__link:hover {
    transform: translateY(-1px);
    box-shadow: none;
}

/* ── Twee-kolom layout ── */
.fn-single-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 24px;
    max-width: 1100px;
    width: 100%;
    margin: 24px auto;
    padding: 0 24px;
    box-sizing: border-box;
}

.fn-single-layout__main {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* ── Hoofd vacature kaart ── */
.fn-single-card {
    background: #fff;
    border: 1px solid var(--fn-border);
    border-radius: var(--fn-radius);
    box-shadow: none;
    padding: 28px;
}

.fn-expired-notice {
    padding: 14px 16px;
    border: 1px solid var(--fn-border);
    border-radius: var(--fn-radius);
    font-family: Poppins, system-ui, sans-serif;
    color: var(--fn-ink);
}

/* ── Meta chips ── */
.fn-single-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 16px;
}

.fn-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 12px;
    border-radius: 999px;
    border: 1px solid var(--fn-border);
    background: #fff;
    color: #333;
    font-family: Poppins, system-ui, sans-serif;
    font-weight: 700;
    font-size: 13px;
    box-shadow: none;
}

.fn-chip--link {
    text-decoration: none !important;
    color: var(--color-secondary) !important;
}

.fn-chip--link:hover { border-color: var(--color-secondary); }

/* ── Titel & subtitle ── */
.fn-single-title.fondsen-job-title,
.fn-single-card .fondsen-job-title {
    margin: 0 0 6px;
    font-family: Inter, system-ui, sans-serif;
    font-size: 26px;
    line-height: 1.15;
    font-weight: 800;
    color: var(--fn-ink);
    max-width: 100%;
    white-space: normal !important;
    overflow-wrap: anywhere;
    word-break: break-word;
    hyphens: auto;
}

.fn-single-subtitle {
    margin: 0 0 20px;
    font-family: Poppins, system-ui, sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: var(--color-secondary);
}

/* ── Beschrijving ── */
.fn-single-content,
.job_description {
    font-family: Poppins, system-ui, sans-serif;
    font-size: 15px;
    line-height: 1.75;
    color: var(--fn-ink);
    overflow-wrap: anywhere;
    word-break: break-word;
}

.fn-single-content p { margin: 0 0 14px; }

/* ── Solliciteer knop ── */
.fn-single-actions { margin-top: 20px; }

.fn-single-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 20px;
    border-radius: 8px;
    background: rgba(8,132,204,0.15);
    color: var(--color-secondary) !important;
    border: 1px solid var(--color-secondary);
    text-decoration: none !important;
    font-family: Poppins, system-ui, sans-serif;
    font-weight: 700;
    font-size: 14px;
    transition: transform .15s ease;
}

.fn-single-btn:hover { transform: translateY(-1px); }

/* ── Sidebar ── */
.fn-single-layout__sidebar {
    display: flex;
    flex-direction: column;
    gap: 16px;
    position: sticky;
    top: 24px;
    align-self: start;
}

.fn-sidebar-block {
    background: #fff;
    border: 1px solid var(--fn-border);
    border-radius: var(--fn-radius);
    box-shadow: none;
    padding: 20px;
}

.fn-sidebar__block-title {
    font-family: Inter, sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: var(--fn-ink);
    margin: 0 0 14px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--fn-border);
}

.fn-sidebar__details {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.fn-sidebar__detail-row {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.fn-sidebar__detail-label {
    font-family: Poppins, sans-serif;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--fn-muted);
}

.fn-sidebar__detail-value {
    font-family: Poppins, sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: var(--fn-ink);
}

.fn-sidebar__chip {
    display: inline-block;
    padding: 3px 10px;
    background: rgba(8,132,204,0.08);
    border: 1px solid rgba(8,132,204,0.25);
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    font-family: Poppins, sans-serif;
    color: var(--color-secondary);
    margin: 2px 4px 2px 0;
}

.fn-sidebar__contact-name {
    font-family: Poppins, sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: var(--fn-ink);
    margin: 0 0 4px;
}

.fn-sidebar__contact-email {
    font-family: Poppins, sans-serif;
    font-size: 13px;
    color: var(--color-secondary);
    text-decoration: none;
}

.fn-sidebar__contact-email:hover { text-decoration: underline; }

/* ── Gerelateerde vacatures (sidebar) ── */
.fn-sidebar-jobs {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.fn-sidebar-job__link {
    display: flex;
    flex-direction: column;
    gap: 2px;
    text-decoration: none !important;
    padding: 10px 12px;
    border: 1px solid var(--fn-border);
    border-radius: var(--fn-radius);
    transition: border-color .15s ease, background .15s ease;
}

.fn-sidebar-job__link:hover {
    border-color: var(--color-secondary);
    background: rgba(8,132,204,0.04);
}

.fn-sidebar-job__org {
    font-family: Poppins, sans-serif;
    font-size: 11px;
    font-weight: 700;
    color: var(--color-secondary);
    text-transform: uppercase;
    letter-spacing: .04em;
}

.fn-sidebar-job__title {
    font-family: Poppins, sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: var(--fn-ink);
    line-height: 1.35;
}

/* ── Stel een vraag blok ── */
.fn-vraag-blok {
    background: #fff;
    border: 1px solid var(--fn-border);
    border-radius: var(--fn-radius);
    box-shadow: none;
    padding: 28px;
}

.fn-vraag-blok__title {
    font-family: Inter, sans-serif;
    font-size: 20px;
    font-weight: 700;
    color: var(--fn-ink);
    margin: 0 0 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--fn-border);
    line-height: 1.3;
}

.fn-vraag-blok__form {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.fn-vraag-blok__row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.fn-vraag-blok__field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.fn-vraag-blok__label {
    font-family: Poppins, sans-serif;
    font-size: 15px;
    font-weight: 400;
    color: #333;
}

.fn-vraag-req { color: var(--color-secondary); margin-left: 2px; }

.fn-vraag-blok__input {
    width: 100% !important;
    padding: 8px 12px !important;
    font-family: Poppins, sans-serif !important;
    font-size: 15px !important;
    font-weight: 400 !important;
    color: var(--fn-ink) !important;
    background: #fff !important;
    border: 1px solid var(--fn-border) !important;
    border-radius: var(--fn-radius) !important;
    outline: none !important;
    box-shadow: none !important;
    box-sizing: border-box;
    height: auto !important;
    line-height: 1.5 !important;
    transition: border-color .2s ease, box-shadow .2s ease;
}

.fn-vraag-blok__input:focus {
    border-color: var(--color-secondary) !important;
    box-shadow: 0 0 0 3px rgba(8,132,204,.12) !important;
}

.fn-vraag-blok__input::placeholder {
    color: var(--fn-muted) !important;
    font-weight: 300 !important;
}

.fn-vraag-blok__hidden { display: none !important; }

.fn-vraag-blok__quill-wrap {
    border: 1px solid var(--fn-border);
    border-radius: var(--fn-radius);
    overflow: hidden;
    transition: border-color .2s ease, box-shadow .2s ease;
}

.fn-vraag-blok__quill-wrap:focus-within {
    border-color: var(--color-secondary);
    box-shadow: 0 0 0 3px rgba(8,132,204,.12);
}

.fn-vraag-blok__quill-wrap .ql-toolbar {
    border: none !important;
    border-bottom: 1px solid var(--fn-border) !important;
    background: #f9fafb;
    padding: 8px 12px !important;
}

.fn-vraag-blok__quill-wrap .ql-container {
    border: none !important;
    font-family: Poppins, sans-serif !important;
    font-size: 15px !important;
}

.fn-vraag-blok__quill-wrap .ql-editor {
    padding: 14px !important;
    color: var(--fn-ink) !important;
    line-height: 1.7 !important;
}

.fn-vraag-blok__quill-wrap .ql-editor.ql-blank::before {
    color: var(--fn-muted) !important;
    font-style: normal !important;
    font-weight: 300 !important;
}

.fn-vraag-blok__upload {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    min-height: 110px;
    padding: 24px 20px;
    background: #f0f7fb;
    border: 2px dashed var(--color-secondary);
    border-radius: var(--fn-radius);
    cursor: pointer;
    text-align: center;
    position: relative;
    transition: border-color .2s ease, background .2s ease;
    box-sizing: border-box;
}

.fn-vraag-blok__upload:hover {
    background: #e4f0f5;
}

.fn-vraag-blok__upload svg { color: var(--color-secondary); flex-shrink: 0; }

.fn-vraag-blok__upload-label {
    font-family: Poppins, sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: var(--color-secondary);
}

.fn-vraag-blok__upload-name {
    font-family: Poppins, sans-serif;
    font-size: 12px;
    color: var(--fn-muted);
}

.fn-vraag-blok__upload-input {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 2;
}

.fn-vraag-blok__hint {
    font-family: Poppins, sans-serif;
    font-size: 13px;
    color: var(--fn-muted);
}

.fn-vraag-blok__submit {
    align-self: flex-start;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: Balgin-Bold, serif;
    font-size: 15px;
    background: var(--color-secondary);
    border: 2px solid var(--color-secondary);
    color: #fff !important;
    padding: 10px 28px;
    border-radius: var(--fn-radius);
    cursor: pointer;
    transition: background .15s ease, border-color .15s ease;
}

.fn-vraag-blok__submit:hover {
    background: var(--color-tertiary);
    border-color: var(--color-tertiary);
}

.fn-vraag-success {
    font-family: Poppins, sans-serif;
    font-size: 14px;
    color: #065f46;
    background: #ecfdf5;
    border: 1px solid #6ee7b7;
    border-radius: var(--fn-radius);
    padding: 10px 14px;
    margin: 0;
}

.fn-vraag-error {
    font-family: Poppins, sans-serif;
    font-size: 13px;
    color: #991b1b;
    background: #fef2f2;
    border: 1px solid #fca5a5;
    border-radius: var(--fn-radius);
    padding: 8px 12px;
    margin: 0 0 8px;
}

/* ── Recente vacatures ── */
.fn-recent {
    padding: 0;
}

.fn-recent-head { margin-bottom: 18px; }

.fn-recent-title {
    margin: 0;
    font-family: Poppins, sans-serif;
    font-weight: 700;
    font-size: 20px;
    color: var(--fn-ink);
}

.fn-recent-sub {
    margin: 6px 0 0;
    font-family: Poppins, sans-serif;
    font-size: 14px;
    color: var(--fn-muted);
}

.fn-recent-grid {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
}

.fn-recent-item {
    background: #fff;
    border: 1px solid var(--fn-border);
    border-radius: var(--fn-radius);
    overflow: hidden;
    box-shadow: none;
    transition: transform .18s ease, border-color .18s ease;
}

.fn-recent-item:hover {
    transform: translateY(-3px);
    border-color: var(--color-secondary);
}

.fn-recent-link {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 18px;
    text-decoration: none !important;
    color: inherit;
}

.fn-recent-logo { display: block; }

.fn-recent-logo img,
.fn-recent-logo .company_logo {
    display: block;
    margin: 0 !important;
    width: 120px;
    height: auto;
    max-height: 80px;
    object-fit: contain;
}

.fn-recent-body {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
}

.fn-recent-company {
    font-family: Poppins, sans-serif;
    font-size: 13px;
    font-weight: 700;
    color: var(--color-secondary);
}

h3.fn-recent-jobtitle {
    margin: 0;
    font-family: Poppins, sans-serif;
    font-size: 15px;
    font-weight: 700;
    line-height: 1.25;
    color: var(--fn-ink);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.fn-recent-item:hover h3.fn-recent-jobtitle { color: var(--color-secondary); }

.fn-recent-excerpt {
    margin: 0;
    font-family: Poppins, sans-serif;
    font-size: 14px;
    line-height: 1.5;
    color: var(--fn-muted);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* ── Locatiekaart ── */
.fn-map-block { padding: 20px; }

.fn-job-map {
    width: 100%;
    height: 220px;
    border-radius: var(--fn-radius);
    overflow: hidden;
}

/* ── entry-title verbergen ── */
.single-job_listing h1.entry-title,
body.single-job_listing .entry-title {
    display: none !important;
}

/* ── Responsive ── */
@media (max-width: 900px) {
    .fn-single-layout {
        grid-template-columns: 1fr;
    }

    .fn-single-layout__sidebar {
        position: static;
    }

    .fn-recent-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .fn-single-top__inner {
        flex-direction: column;
        align-items: stretch;
        text-align: left;
    }

    .fn-single-top__link { width: 100%; text-align: center; }

    .fn-single-layout {
        padding: 0 12px;
        margin: 16px auto;
    }

    .fn-single-top { padding: 0 12px; }

    .fn-vraag-blok__row { grid-template-columns: 1fr; }

    .fn-vraag-blok__submit { width: 100%; align-self: stretch; }

    .fn-recent-grid { grid-template-columns: 1fr; }
}
</style>

<script>
(function () {
    function initJobMap() {
        if (typeof maplibregl === 'undefined') { setTimeout(initJobMap, 100); return; }
        var el = document.getElementById('fn-job-map');
        if (!el) return;

        var lat = parseFloat(el.dataset.lat);
        var lng = parseFloat(el.dataset.lng);
        var label = el.dataset.label || '';

        var map = new maplibregl.Map({
            container: 'fn-job-map',
            style: 'https://tiles.openfreemap.org/styles/liberty',
            center: [lng, lat],
            zoom: 11,
            attributionControl: true,
            scrollZoom: false,
        });

        new maplibregl.Marker({ color: '#0884CC' })
            .setLngLat([lng, lat])
            .setPopup(new maplibregl.Popup({ offset: 25 }).setText(label))
            .addTo(map);
    }
    initJobMap();
})();

(function () {
    function initVraagQuill() {
        if (typeof Quill === 'undefined') { setTimeout(initVraagQuill, 80); return; }
        var hidden = document.getElementById('fn_vraag_hidden');
        if (!hidden) return;
        var quill = new Quill('#fn_vraag_quill', {
            theme: 'snow',
            placeholder: 'Stel je vraag of schrijf een korte motivatie...',
            modules: { toolbar: [['bold','italic','underline'],[{'list':'ordered'},{'list':'bullet'}],['link'],['clean']] }
        });
        if (hidden.value) quill.root.innerHTML = hidden.value;
        quill.on('text-change', function () { hidden.value = quill.root.innerHTML; });
        var form = hidden.closest('form');
        if (form) form.addEventListener('submit', function () { hidden.value = quill.root.innerHTML; });
    }
    initVraagQuill();
})();
</script>
