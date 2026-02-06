<?php if (!defined('ABSPATH')) exit; ?>
<?php get_header(); ?>

<?php
$post_id = get_the_ID();

$first = get_post_meta($post_id, FGA_Plugin::META_FIRSTNAME, true);
$last  = get_post_meta($post_id, FGA_Plugin::META_LASTNAME, true);
$email = get_post_meta($post_id, FGA_Plugin::META_EMAIL, true);
$name  = trim($first . ' ' . $last);

$kapitaal  = get_post_meta($post_id, FGA_Plugin::META_BENODIGD_KAPITAAL, true);
$stichting = get_post_meta($post_id, FGA_Plugin::META_STICHTING_NAAM, true);
?>

<style>
/* Scoped variables (admin-safe) */
.fga-single-wrap{
  --fga-text:#333333;
  --fga-muted:#6b7280;
  --fga-border:#e6e6e6;
  --fga-blue:#0884CC;
  --fga-soft:#fafafa;
  --fga-bg:#ffffff;
}

.fga-single-wrap{
  max-width:1100px;
  margin:24px auto 60px;
  padding:0 16px;
}

.fga-single-card{
  background:var(--fga-bg);
  border:1px solid var(--fga-border);
  border-radius:14px;
  overflow:hidden;
  box-shadow:0 10px 30px rgba(0,0,0,.06);
}

.fga-single-body{
  padding:26px;
}

.fga-single-title{
  margin:0 0 10px;
  font-size:36px;
  line-height:1.1;
  color:var(--fga-text);
  letter-spacing:-0.02em;
}

.fga-single-meta{
  display:flex;
  flex-wrap:wrap;
  gap:10px;
  margin:0 0 18px;
  font-size:14px;
}

.fga-chip{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:6px 10px;
  border:1px solid var(--fga-border);
  background:#fff;
  border-radius:999px;
  color:var(--fga-text);
  font-size:13px;
}

.fga-chip--accent{
  border-color:rgba(8,132,204,.25);
  background:rgba(8,132,204,.06);
}

.fga-chip strong{ font-weight:800; }

.fga-single-content{
  font-size:16px;
  line-height:1.75;
  color:var(--fga-text);
}

.fga-single-content p{ margin:0 0 14px; }

.fga-contact-box{
  margin-top:26px;
  padding:18px;
  border:1px solid var(--fga-border);
  border-radius:14px;
  background:var(--fga-soft);
}

.fga-contact-title{
  margin:0 0 10px;
  font-size:16px;
  font-weight:800;
  color:var(--fga-text);
}

.fga-contact-row{
  margin:0 0 8px;
  font-size:14px;
  color:var(--fga-text);
}

.fga-contact-row a{
  color:var(--fga-blue);
  text-decoration:none;
  font-weight:800;
}
.fga-contact-row a:hover{ text-decoration:underline; }

.fga-contact-actions{
  margin-top:12px;
  display:flex;
  gap:10px;
  flex-wrap:wrap;
}

.fga-btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:10px;
  padding:10px 14px;
  border-radius:12px;
  border:1px solid var(--fga-border);
  background:#fff;
  color:var(--fga-text);
  text-decoration:none;
  font-weight:800;
  font-size:14px;
}

.fga-btn--primary{
  background:var(--fga-blue);
  border-color:var(--fga-blue);
  color:#fff;
}
.fga-btn--primary:hover{ filter:brightness(.95); }

@media (max-width: 700px){
  .fga-single-body{ padding:18px; }
  .fga-single-title{ font-size:28px; }
}
</style>

<div class="fga-single-wrap">
  <article class="fga-single-card">
    <div class="fga-single-body">
      <h1 class="fga-single-title"><?php the_title(); ?></h1>

      <div class="fga-single-meta">
        <span class="fga-chip"><?php echo esc_html(get_the_date()); ?></span>

        <?php if ((int)$kapitaal > 0): ?>
          <span class="fga-chip fga-chip--accent">
            <strong>Op te halen bedrag</strong>
            €<?php echo esc_html(number_format_i18n((int) $kapitaal)); ?>
          </span>
        <?php endif; ?>

        <?php if (!empty($stichting)): ?>
          <span class="fga-chip">
            <strong>Stichting</strong>
            <?php echo esc_html($stichting); ?>
          </span>
        <?php endif; ?>
      </div>

      <div class="fga-single-content">
        <?php the_content(); ?>
      </div>

      <?php if ($name || $email): ?>
        <div class="fga-contact-box">
          <h3 class="fga-contact-title">Contactgegevens plaatser</h3>

          <?php if ($name): ?>
            <p class="fga-contact-row"><strong>Naam:</strong> <?php echo esc_html($name); ?></p>
          <?php endif; ?>

          <?php if ($email): ?>
            <p class="fga-contact-row">
              <strong>E-mail:</strong>
              <a href="mailto:<?php echo esc_attr($email); ?>">
                <?php echo esc_html($email); ?>
              </a>
            </p>

            <div class="fga-contact-actions">
              <a class="fga-btn fga-btn--primary" href="mailto:<?php echo esc_attr($email); ?>">
                Neem contact op
              </a>
              <a class="fga-btn" href="<?php echo esc_url(get_post_type_archive_link(FGA_Plugin::CPT)); ?>">
                Terug naar overzicht
              </a>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

    </div>
  </article>
</div>

<?php get_footer(); ?>
