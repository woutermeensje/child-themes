<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $post;

if ( job_manager_user_can_view_job_listing( $post->ID ) ) : ?>

<div class="sj-wrap">

    <div class="sj-card">
      <?php if ( get_option( 'job_manager_hide_expired_content', 1 ) && 'expired' === $post->post_status ) : ?>
        <div class="job-manager-info"><?php _e( 'This listing has expired.', 'wp-job-manager' ); ?></div>
      <?php else : ?>

        <?php
        $company_name    = function_exists('get_the_company_name') ? get_the_company_name() : '';
        $company_website = get_post_meta( $post->ID, '_company_website', true );
        ?>

        <div class="sj-meta">
          <span class="sj-chip">🗓️ <?php echo esc_html( date_i18n( 'j F Y', get_post_time( 'U' ) ) ); ?></span>
          <span class="sj-chip">🏷️ <?php the_job_type(); ?></span>

          <?php if ( ! empty( $company_name ) ) : ?>
            <span class="sj-chip">🏢 <?php echo esc_html( $company_name ); ?></span>
          <?php endif; ?>
        </div>

        <header class="sj-header">
          <h1 class="sj-title"><?php wpjm_the_job_title(); ?></h1>
          <?php if ( ! empty( $company_name ) ) : ?>
            <p class="sj-subtitle"><?php echo esc_html( $company_name ); ?></p>
          <?php endif; ?>
        </header>

        <div class="job_description sj-content">
          <?php wpjm_the_job_description(); ?>
        </div>

        <?php if ( ! empty( $company_website ) ) : ?>
          <div class="sj-actions">
            <a href="<?php echo esc_url( $company_website ); ?>" class="sj-btn" target="_blank" rel="noopener">
              Solliciteer op deze vacature
            </a>
          </div>
        <?php endif; ?>

        <?php do_action( 'single_job_listing_end' ); ?>

      <?php endif; ?>
    </div>

    <?php
    $cf = get_post_meta( get_the_ID(), '_contact_first_name', true );
    $cl = get_post_meta( get_the_ID(), '_contact_last_name', true );
    $ce = get_post_meta( get_the_ID(), '_contact_email', true );
    $has_contact = ( ! empty($cf) || ! empty($cl) || ! empty($ce) );
    ?>

    <?php if ( $has_contact ) : ?>
      <section class="sj-contact sj-card">
        <h2 class="sj-contact-title">Contactpersoon Vacature</h2>
        <div class="sj-contact-grid">
          <?php if ( ! empty($cf) || ! empty($cl) ) : ?>
            <div class="sj-contact-row">
              <span class="sj-contact-label">Contactpersoon</span>
              <span class="sj-contact-value"><?php echo esc_html( trim($cf . ' ' . $cl) ); ?></span>
            </div>
          <?php endif; ?>
          <?php if ( ! empty($ce) ) : ?>
            <div class="sj-contact-row">
              <span class="sj-contact-label">E-mail</span>
              <a class="sj-contact-value sj-contact-link" href="mailto:<?php echo esc_attr( antispambot($ce) ); ?>">
                <?php echo esc_html( antispambot($ce) ); ?>
              </a>
            </div>
          <?php endif; ?>
        </div>
      </section>
    <?php endif; ?>

    <?php
    $current_id = get_the_ID();
    $recent_jobs = new WP_Query([
      'post_type'      => 'job_listing',
      'posts_per_page' => 6,
      'orderby'        => 'date',
      'order'          => 'DESC',
      'post__not_in'   => [ $current_id ],
      'post_status'    => 'publish',
    ]);
    ?>

    <?php if ( $recent_jobs->have_posts() ) : ?>
      <section class="sj-recent sj-card">
        <div class="sj-recent-head">
          <h2 class="sj-recent-title">Andere openstaande functies</h2>
          <p class="sj-recent-sub">Bekijk al onze vacatures bij Modulairehuisvesting.</p>
        </div>
        <ul class="sj-recent-grid">
          <?php while ( $recent_jobs->have_posts() ) : $recent_jobs->the_post(); ?>
            <?php
              $rc_title   = function_exists('wpjm_get_the_job_title') ? wpjm_get_the_job_title() : get_the_title();
              $rc_excerpt = wp_trim_words( get_the_excerpt(), 14, '…' );
            ?>
            <li class="sj-recent-item" <?php job_listing_class(); ?>>
              <a class="sj-recent-link" href="<?php the_job_permalink(); ?>" aria-label="<?php echo esc_attr( $rc_title ); ?>">
                <div class="sj-recent-logo">
                  <?php the_company_logo(); ?>
                </div>
                <div class="sj-recent-body">
                  <h3 class="sj-recent-jobtitle"><?php echo esc_html( $rc_title ); ?></h3>
                  <?php if ( ! empty($rc_excerpt) ) : ?>
                    <p class="sj-recent-excerpt"><?php echo esc_html( $rc_excerpt ); ?></p>
                  <?php endif; ?>
                </div>
              </a>
            </li>
          <?php endwhile; ?>
        </ul>
        <?php wp_reset_postdata(); ?>
      </section>
    <?php endif; ?>

  </div>

<?php else : ?>
  <?php get_job_manager_template_part( 'access-denied', 'single-job_listing' ); ?>
<?php endif; ?>

<style>
:root{
  --sj-ink: var(--color-text);
  --sj-muted: var(--color-text-soft);
  --sj-border: var(--color-border);
  --sj-card: var(--color-surface);
  --sj-accent: var(--color-primary);
  --sj-radius: 5px;
  --sj-shadow: 0 10px 40px -5px var(--color-shadow);
}

html, body{ width:100%; max-width:100%; overflow-x:hidden; }

.sj-wrap{ max-width:900px; width:100%; margin:20px auto; padding:0 16px; display:grid; gap:16px; }
.job_description,.sj-content{ overflow-wrap:anywhere; word-break:break-word !important; }

.sj-card{ padding:24px; background:var(--color-surface); border:1px solid var(--color-border); box-shadow:var(--sj-shadow); border-radius:var(--sj-radius); }

.single_job_listing{ padding:22px; }
.job-manager-info{ padding:14px 16px; border:1px solid var(--sj-border); border-radius:var(--sj-radius); background:var(--color-surface); font-family:Poppins,system-ui,sans-serif; color:var(--sj-ink); }

.sj-meta{ display:flex; flex-wrap:wrap; gap:10px; margin-bottom:14px; }
.sj-chip{ display:inline-flex; align-items:center; gap:8px; padding:8px 10px; border-radius:999px; border:1px solid var(--color-border); background:var(--color-surface-alt); color:var(--color-text); font-family:Poppins,system-ui,sans-serif; font-weight:700; font-size:14px; box-shadow:var(--sj-shadow); }

.sj-header{ padding-bottom:14px; border-bottom:1px solid var(--sj-border); margin-bottom:16px; margin-top:24px; }
.sj-title{ margin:0; font-family:'Balgin-Bold','Balgin Bold',serif; font-size:26px; line-height:1.15; font-weight:800; color:var(--color-text); max-width:100%; white-space:normal !important; overflow-wrap:anywhere; word-break:break-word; hyphens:auto; }
.sj-subtitle{ margin:8px 0 0 0; font-family:Poppins,system-ui,sans-serif; font-size:14px; font-weight:600; color:var(--color-secondary); }

.sj-content{ font-family:Poppins,system-ui,sans-serif; color:var(--color-text); font-size:15px; line-height:1.75; }
.sj-content p{ margin:0 0 14px 0; }

.sj-actions{ margin-top:18px; display:flex; }
.sj-btn{ display:inline-flex; align-items:center; justify-content:center; padding:12px 20px; border-radius:var(--sj-radius); background:var(--color-secondary); color:#ffffff !important; text-decoration:none !important; border:1px solid var(--color-secondary); font-family:'Balgin-Bold','Balgin Bold',serif; font-weight:700; font-size:15px; box-shadow:var(--sj-shadow); transition:background .15s ease,transform .15s ease; }
.sj-btn:hover{ background:var(--color-primary); border-color:var(--color-primary); transform:translateY(-1px); }

h1.entry-title{ display:none; }

.sj-contact{ padding:24px; }
h2.sj-contact-title{ font-family:'Balgin-Bold','Balgin Bold',serif; font-weight:700; font-size:20px; color:var(--color-text); }
.sj-contact-grid{ display:grid; grid-template-columns:1fr 1fr; gap:28px 56px; }
.sj-contact-row{ display:grid; gap:8px; }
.sj-contact-label{ font-family:Poppins,system-ui,sans-serif; font-size:16px; font-weight:600; color:var(--color-text-soft); }
.sj-contact-value{ font-family:Poppins,system-ui,sans-serif; font-size:14px; font-weight:400; color:var(--color-text); line-height:1.25; word-break:break-word; }
.sj-contact-link{ color:var(--color-primary) !important; text-decoration:none !important; }
.sj-contact-link:hover{ text-decoration:underline !important; }

.sj-recent{ padding:24px; }
.sj-recent-head{ margin-bottom:18px; }
.sj-recent-title{ margin:0; font-family:'Balgin-Bold','Balgin Bold',serif; font-weight:700; font-size:20px; color:var(--color-text); }
.sj-recent-sub{ margin:6px 0 0 0; font-family:Poppins,system-ui,sans-serif; font-size:14px; color:var(--color-text-soft); }
.sj-recent-grid{ list-style:none; padding:0; margin:0; display:grid; grid-template-columns:repeat(3,1fr); gap:18px; }
.sj-recent-item{ background:var(--color-surface); border:1px solid var(--color-border); border-radius:var(--sj-radius); overflow:hidden; box-shadow:var(--sj-shadow); transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease; }
.sj-recent-item:hover{ transform:translateY(-3px); border-color:var(--color-border-strong); box-shadow:0 18px 44px var(--color-shadow); }
.sj-recent-link{ display:flex; flex-direction:column; gap:14px; padding:18px; text-decoration:none !important; color:inherit; align-items:flex-start; }
.sj-recent-logo{ display:block; padding:0; border:0; background:transparent; box-shadow:none; }
.sj-recent-logo img,.sj-recent-logo .company_logo{ display:block; margin:0 !important; }
.sj-recent-logo img{ width:120px; height:auto; max-height:80px; object-fit:contain; }
.sj-recent-body{ display:flex; flex-direction:column; gap:6px; min-width:0; }
h3.sj-recent-jobtitle{ margin:0; font-family:'Balgin-Bold','Balgin Bold',serif; font-size:16px; font-weight:700; line-height:1.25; color:var(--color-text); display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.sj-recent-excerpt{ margin:0; font-family:Poppins,system-ui,sans-serif; font-size:14px; line-height:1.5; color:var(--color-text-soft); display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.sj-recent-item:hover .sj-recent-jobtitle{ color:var(--color-secondary); }

@media (max-width:768px){
  *, *::before, *::after{ box-sizing:border-box; }
  .sj-wrap,.single_job_listing,.sj-contact,.sj-recent,.sj-card{ width:100% !important; max-width:100% !important; box-sizing:border-box !important; }
  .sj-wrap{ padding:0 16px; }
  .sj-meta,.sj-recent-grid,.sj-recent-link,.sj-contact-grid{ min-width:0 !important; }
  .sj-contact-grid{ grid-template-columns:1fr; gap:18px; }
  .sj-recent-grid{ grid-template-columns:1fr; }
  img{ max-width:100%; height:auto; }
}
</style>
