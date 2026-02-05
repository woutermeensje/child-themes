<?php if (!defined('ABSPATH')) exit; ?>
<?php get_header(); ?>

<style>
/* Basic single layout (pas gerust aan) */
.fga-single-wrap{max-width:1100px;margin:24px auto 60px;padding:0 16px;}
.fga-single-card{background:#fff;border:1px solid #e6e6e6;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.06);overflow:hidden;}
.fga-single-hero{height:360px;background:#f2f2f2;background-size:cover;background-position:center;}
.fga-single-body{padding:22px;}
.fga-single-title{margin:0 0 10px;font-size:34px;line-height:1.1;color:#1f2937;}
.fga-single-meta{display:flex;gap:10px;flex-wrap:wrap;font-size:14px;opacity:.75;margin-bottom:16px;}
.fga-single-tax{display:flex;gap:8px;flex-wrap:wrap;margin:10px 0 18px;}
.fga-pill{display:inline-block;padding:6px 10px;border:1px solid #e6e6e6;border-radius:999px;font-size:13px;opacity:.9;background:#fff;}
.fga-single-content{font-size:16px;line-height:1.65;color:#1f2937;}
.fga-back{display:inline-block;margin:14px 0 0;color:#0884CC;text-decoration:none;}
.fga-back:hover{text-decoration:underline;}

/* Contact block */
.fga-contact{
  margin-top: 22px;
  padding: 16px;
  border: 1px solid #e6e6e6;
  border-radius: 12px;
  background: #fff;
}
.fga-contact-title{margin:0 0 8px;font-size:16px;font-weight:700;color:#1f2937;}
.fga-contact-row{display:flex;gap:10px;flex-wrap:wrap;align-items:center;font-size:14px;opacity:.9;}
.fga-contact-name{font-weight:700;opacity:1;}
.fga-contact-mail a{color:#0884CC;text-decoration:none;}
.fga-contact-mail a:hover{text-decoration:underline;}
.fga-contact-btn{
  display:inline-block;
  margin-top: 10px;
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid #d9d9d9;
  text-decoration: none;
  background: #fff;
  color: #1f2937;
  font-weight: 700;
}
.fga-contact-btn:hover{background:#f6f6f6;}

@media (max-width: 700px){
  .fga-single-hero{height:220px;}
  .fga-single-title{font-size:28px;}
}
</style>

<div class="fga-single-wrap">
  <?php if (have_posts()): while (have_posts()): the_post(); ?>

    <?php
      $post_id = get_the_ID();
      $img = get_the_post_thumbnail_url($post_id, 'full');

      // Custom contact fields
      $first = class_exists('FGA_Plugin') ? get_post_meta($post_id, FGA_Plugin::META_FIRSTNAME, true) : '';
      $last  = class_exists('FGA_Plugin') ? get_post_meta($post_id, FGA_Plugin::META_LASTNAME, true) : '';
      $email = class_exists('FGA_Plugin') ? get_post_meta($post_id, FGA_Plugin::META_EMAIL, true) : '';

      $name  = trim($first . ' ' . $last);
      $has_contact = ($name !== '' || $email !== '');
    ?>

    <article class="fga-single-card">
      <?php if ($img): ?>
        <div class="fga-single-hero" style="background-image:url('<?php echo esc_url($img); ?>')"></div>
      <?php endif; ?>

      <div class="fga-single-body">
        <h2 class="fga-single-title"><?php the_title(); ?></h2>

        <div class="fga-single-meta">
          <?php if ($name): ?>
            <span><?php echo esc_html($name); ?></span>
            <span>•</span>
          <?php endif; ?>
          <span><?php echo esc_html(get_the_date()); ?></span>
        </div>

        <div class="fga-single-tax">
          <?php
            $types = get_the_terms($post_id, FGA_Plugin::TAX_TYPE);
            if (!is_wp_error($types) && !empty($types)) {
              foreach ($types as $t) echo '<span class="fga-pill">' . esc_html($t->name) . '</span>';
            }

            $themas = get_the_terms($post_id, FGA_Plugin::TAX_THEMA);
            if (!is_wp_error($themas) && !empty($themas)) {
              foreach ($themas as $t) echo '<span class="fga-pill">' . esc_html($t->name) . '</span>';
            }
          ?>
        </div>

        <div class="fga-single-content">
          <?php the_content(); ?>
        </div>

       

        <a class="fga-back" href="<?php echo esc_url(get_post_type_archive_link(FGA_Plugin::CPT)); ?>">
          ← Terug naar alle geefacties
        </a>
      </div>
    </article>

  <?php endwhile; endif; ?>
</div>

<div class="contact-section">


 <?php if ($has_contact): ?>
          <div class="fga-contact">
            <div class="fga-contact-title">Contactgegevens</div>

            <div class="fga-contact-row">
              <?php if ($name): ?>
                <span class="fga-contact-name"><?php echo esc_html($name); ?></span>
              <?php endif; ?>

              <?php if ($email): ?>
                <span class="fga-contact-mail">
                  <a href="<?php echo esc_url('mailto:' . sanitize_email($email)); ?>">
                    <?php echo esc_html($email); ?>
                  </a>
                </span>
              <?php endif; ?>
            </div>

            <?php if ($email): ?>
              <a class="fga-contact-btn" href="<?php echo esc_url('mailto:' . sanitize_email($email)); ?>">
                E-mail sturen
              </a>
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
    }
</style>