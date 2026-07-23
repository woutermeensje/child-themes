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
    $application_status = isset( $_GET['mh_job_application'] )
      ? sanitize_key( wp_unslash( $_GET['mh_job_application'] ) )
      : '';
    ?>

    <section class="sj-application sj-card" id="solliciteren">
      <h2 class="sj-application-title">Reageren op deze vacature/opdracht.</h2>

      <?php if ( 'sent' === $application_status ) : ?>
        <div class="sj-form-notice sj-form-notice--success">
          Bedankt voor je reactie. We nemen zo snel mogelijk contact met je op.
        </div>
      <?php elseif ( 'error' === $application_status ) : ?>
        <div class="sj-form-notice sj-form-notice--error">
          Het formulier kon niet worden verzonden. Controleer de velden en probeer het opnieuw.
        </div>
      <?php endif; ?>

      <form class="sj-application-form" method="post" action="<?php echo esc_url( get_permalink( $post ) ); ?>" enctype="multipart/form-data">
        <?php wp_nonce_field( 'mh_job_application_' . $post->ID, 'mh_job_application_nonce' ); ?>
        <input type="hidden" name="mh_job_application_action" value="submit">
        <input type="hidden" name="mh_job_application_job_id" value="<?php echo esc_attr( (string) $post->ID ); ?>">

        <div class="sj-application-grid">
          <label class="sj-application-field">
            <span>Voornaam</span>
            <input type="text" name="mh_job_application_first_name" autocomplete="given-name" required>
          </label>

          <label class="sj-application-field">
            <span>Achternaam</span>
            <input type="text" name="mh_job_application_last_name" autocomplete="family-name" required>
          </label>

          <label class="sj-application-field">
            <span>E-mailadres</span>
            <input type="email" name="mh_job_application_email" autocomplete="email" required>
          </label>

          <label class="sj-application-field">
            <span>Telefoonnummer</span>
            <input type="tel" name="mh_job_application_phone" autocomplete="tel" required>
          </label>
        </div>

        <label class="sj-application-field sj-application-field--full">
          <span>Bericht</span>
          <textarea name="mh_job_application_message" rows="6" required></textarea>
        </label>

        <label class="sj-application-field sj-application-field--full">
          <span>CV bijlage</span>
          <input type="file" name="mh_job_application_cv" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
        </label>

        <button class="sj-application-submit" type="submit">Solliciteren</button>
      </form>
    </section>

  </div>

<?php else : ?>
  <?php get_job_manager_template_part( 'access-denied', 'single-job_listing' ); ?>
<?php endif; ?>

<style>
:root {
  --sj-ink: #111827;
  --sj-muted: #6B7280;
  --sj-border: #E5E7EB;
  --sj-card: #FFFFFF;
  --sj-blue: var(--color-primary);
  --sj-radius: 12px;
}

.sj-wrap{ max-width:900px; width:100%; margin:56px auto; padding:0 16px; display:grid; gap:16px; }
.job_description,.sj-content{ overflow-wrap:anywhere; word-break:break-word; }

.sj-card{ padding:24px; background:#ffffff; border:1px solid #DEDEDE; border-radius:5px; }

.single_job_listing{ padding:22px; }
.job-manager-info{ padding:14px 16px; border:1px solid var(--sj-border); border-radius:10px; background:#fff; font-family:Poppins,system-ui,sans-serif; color:var(--sj-ink); }

.sj-meta{ display:flex; flex-wrap:wrap; gap:10px; margin-bottom:14px; }
.sj-chip{ display:inline-flex; align-items:center; gap:8px; padding:8px 10px; border-radius:999px; border:1px solid #DEDEDE; background:#fff; color:#333; font-family:Poppins,system-ui,sans-serif; font-weight:700; font-size:14px; }
.sj-chip--link{ text-decoration:none; }
.sj-chip--link:hover{ border-color:rgba(37,71,107,.35); }

.sj-header{ padding-bottom:14px; border-bottom:1px solid var(--sj-border); margin-bottom:16px; margin-top:24px; }
.sj-title{ margin:0; font-family:Inter,system-ui,sans-serif; font-size:26px; line-height:1.15; font-weight:800; color:var(--sj-ink); max-width:100%; white-space:normal; overflow-wrap:anywhere; word-break:break-word; hyphens:auto; }
.sj-subtitle{ margin:8px 0 0 0; font-family:Poppins,system-ui,sans-serif; font-size:14px; font-weight:600; color:var(--sj-blue); }

.sj-content{ font-family:Poppins,system-ui,sans-serif; color:var(--sj-ink); font-size:15px; line-height:1.75; }
.sj-content p{ margin:0 0 14px 0; }

.sj-actions{ margin-top:18px; display:flex; }
.sj-btn{ display:inline-flex; align-items:center; justify-content:center; padding:10px 14px; border-radius:10px; background:rgba(37,71,107,0.12); color:var(--color-primary); text-decoration:none; border:1px solid var(--color-primary); font-family:Poppins,system-ui,sans-serif; font-weight:700; font-size:14px; transition:transform .15s ease,filter .15s ease; }
.sj-btn:hover{ transform:translateY(-1px); }

.single-job_listing h1.entry-title{ display:none; }

.sj-contact{ padding:24px; }
h2.sj-contact-title{ font-family:Poppins; font-weight:600; font-size:20px; color:#333; }
.sj-contact-grid{ display:grid; grid-template-columns:1fr 1fr; gap:28px 56px; }
.sj-contact-row{ display:grid; gap:8px; }
.sj-contact-label{ font-family:Poppins,system-ui,sans-serif; font-size:16px; font-weight:600; color:#7A7F87; }
.sj-contact-value{ font-family:Poppins,system-ui,sans-serif; font-size:14px; font-weight:400; color:#333; line-height:1.25; word-break:break-word; }
.sj-contact-link{ color:var(--color-primary); text-decoration:none; }
.sj-contact-link:hover{ text-decoration:underline; }

.sj-application{ padding:24px; }
.sj-application-title{ margin:0 0 20px; font-family:Poppins,system-ui,sans-serif; font-weight:600; font-size:20px; line-height:1.25; color:#333; }
.sj-application-form{ display:grid; gap:16px; }
.sj-application-grid{ display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.sj-application-field{ display:grid; gap:6px; min-width:0; font-family:Poppins,system-ui,sans-serif; color:#333; }
.sj-application-field span{ font-size:15px; font-weight:600; line-height:1.4; }
.sj-application-field input,
.sj-application-field textarea{ width:100%; min-width:0; border:1px solid #DEDEDE; border-radius:5px; background:#fff; color:#333; font-family:Poppins,system-ui,sans-serif; font-size:15px; line-height:1.5; padding:10px 12px; box-sizing:border-box; }
.sj-application-field input:focus,
.sj-application-field textarea:focus{ outline:2px solid var(--color-focus-ring, rgba(85,158,163,.22)); outline-offset:2px; border-color:var(--color-secondary, #4188AA); }
.sj-application-field textarea{ resize:vertical; }
.sj-application-field input[type="file"]{ padding:9px 12px; }
.sj-application-submit{ display:inline-flex; align-items:center; justify-content:center; justify-self:start; min-height:44px; padding:10px 28px; border:1px solid var(--color-primary, #25476B); border-radius:5px; background:var(--color-primary, #25476B); color:#fff; font-family:"Work Sans",-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; font-size:15px; font-weight:600; cursor:pointer; transition:background-color .15s ease,border-color .15s ease; }
.sj-application-submit:hover,
.sj-application-submit:focus{ background:var(--color-secondary, #4188AA); border-color:var(--color-secondary, #4188AA); outline:2px solid var(--color-focus-ring, rgba(85,158,163,.22)); outline-offset:2px; }
.sj-form-notice{ padding:12px 14px; margin:0 0 18px; border:1px solid #DEDEDE; border-radius:5px; font-family:Poppins,system-ui,sans-serif; font-size:15px; line-height:1.5; }
.sj-form-notice--success{ border-color:#75B77D; background:#F1F8EE; color:#234C2B; }
.sj-form-notice--error{ border-color:#D77A7A; background:#FFF4F4; color:#7B1D1D; }

@media (max-width:768px){
  *, *::before, *::after{ box-sizing:border-box; }
  .sj-wrap,.single_job_listing,.sj-contact,.sj-application,.sj-card{ width:100%; max-width:100%; box-sizing:border-box; }
  .sj-wrap{ padding:0 16px; }
  .sj-meta,.sj-contact-grid,.sj-application-grid{ min-width:0; }
  .sj-contact-grid,.sj-application-grid{ grid-template-columns:1fr; gap:18px; }
  .sj-application-submit{ width:100%; justify-self:stretch; }
  img{ max-width:100%; height:auto; }
}
</style>
