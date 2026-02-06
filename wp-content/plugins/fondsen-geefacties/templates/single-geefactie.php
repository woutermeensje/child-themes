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

$img = get_the_post_thumbnail_url($post_id, 'large');
if (!$img) {
  $img = 'data:image/svg+xml;utf8,' . rawurlencode(
    '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="600">
      <rect width="100%" height="100%" fill="#f2f2f2"/>
      <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
        fill="#999" font-family="Arial" font-size="28">
        Geen afbeelding
      </text>
    </svg>'
  );
}
?>

<style>
:root{
  --fga-text:#333333;
  --fga-muted:#6b7280;
  --fga-border:#e6e6e6;
  --fga-blue:#0884CC;
  --fga-orange:#FF8C2C;
  --fga-bg:#ffffff;
  --fga-soft:#fafafa;
}

/* Layout */
.fga-single-wrap{
  max-width:1100px;
  margin:24px auto 60px;
  padding:0 16px;
}

.fga-single-card{
  background:var(--fga-bg);
  border:1px solid var(--fga-border);
  border-radius:5px;
  overflow:hidden;
  box-shadow:0 10px 30px rgba(0,0,0,.06);
}

.fga-single-hero{
  height:380px;
  background:#f2f2f2;
  background-size:cover;
  background-position:center;
  position:relative;
}

.fga-single-hero:after{
  content:"";
  position:absolute;
  inset:0;
  background:linear-gradient(to bottom, rgba(0,0,0,.10), rgba(0,0,0,0));
}

/* Body */
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

/* Meta row */
.fga-single-meta{
  display:flex;
  flex-wrap:wrap;
  gap:10px;
  margin:0 0 18px;
  color:var(--fga-muted);
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

.fga-chip strong{
  font-weight:700;
}

.fga-chip--accent{
  border-color:rgba(8,132,204,.25);
  background:rgba(8,132,204,.06);
}

/* Content */
.fga-single-content{
  font-size:16px;
  line-height:1.75;
  color:var(--fga-text);
}

.fga-single-content p{ margin:0 0 14px; }
.fga-single-content h2,
.fga-single-content h3{
  margin:22px 0 10px;
  color:var(--fga-text);
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
  font-weight:700;
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
  border-radius:5px;
  border:1px solid var(--fga-border);
  background:#fff;
  color:var(--fga-text);
  text-decoration:none;
  font-weight:800;
  font-size:14px;
}

.fga-btn:hover{
  background:#f6f6f6;
}

.fga-btn--primary{
  background:var(--fga-blue);
  border-color:var(--fga-blue);
  color:#fff;
}
.fga-btn--primary:hover{
  filter:brightness(.95);
}

@media (max-width: 700px){
  .fga-single-hero{ height:230px; }
  .fga-single-body{ padding:18px; }
  .fga-single-title{ font-size:28px; }
}
</style>

<div class="fga-single-wrap">
  <article class="fga-single-card">

    <div class="fga-single-hero" style="background-image:url('<?php echo esc_url($img); ?>')"></div>

    <div class="fga-single-body">
      <h1 class="fga-single-title"><?php the_title(); ?></h1>

      <div class="fga-single-meta">
        <span class="fga-chip"><?php echo esc_html(get_the_date()); ?></span>

        <?php if ((int)$kapitaal > 0): ?>
          <span class="fga-chip fga-chip--accent">
            <strong>Benodigd</strong>
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
    </div>
  </article>
</div>

<div class="contact-section">

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
          <?php endif; ?>

          <?php if ($email): ?>
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
      

<?php get_footer(); ?>


<style>
  .contact-section {
    max-width:1100px;
    margin:24px auto 60px;
    padding:0 16px;
    border: 1px solid var(--fga-border);
    border-radius: 5px;
    background: var(--fga-soft);
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, .15);
  }

/* Contact box */
.fga-contact-box{
  
}

</style>