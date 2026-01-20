<?php
if (!defined('ABSPATH')) exit;

get_header();

while (have_posts()) : the_post();

  $cats  = get_the_terms(get_the_ID(), 'si_opdracht_categorie');
  $types = get_the_terms(get_the_ID(), 'si_opdracht_type');

  $logo_id = get_post_thumbnail_id(get_the_ID());
  $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'medium') : '';
?>

<div class="si-opd-single-wrap">
  <div class="si-opd-single">

    <header class="si-opd-single-header">
      <?php if ($logo_url): ?>
        <img class="si-opd-single-logo" src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
      <?php endif; ?>

      <h1 class="si-opd-single-title"><?php the_title(); ?></h1>

      <div class="si-opd-single-meta">
        <?php if (!empty($cats) && !is_wp_error($cats)): ?>
          <?php foreach ($cats as $term): ?>
            <span class="si-opd-tag"><?php echo esc_html($term->name); ?></span>
          <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($types) && !is_wp_error($types)): ?>
          <?php foreach ($types as $term): ?>
            <span class="si-opd-tag si-opd-tag--alt"><?php echo esc_html($term->name); ?></span>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </header>

    <div class="si-opd-single-content">
      <?php the_content(); ?>
    </div>

  </div>
</div>

<?php
endwhile;

get_footer();

?>

<style>
  /* ==============================
   Studentinhuren – Single opdracht
   ============================== */

.si-opd-single-wrap{
  max-width: 1050px;
  margin: 24px auto;
  padding: 0 16px; /* ademruimte op mobiel */
}

.si-opd-single{
  background: #fff;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  box-shadow: 0 10px 40px -10px rgba(0,0,0,0.10);
  overflow: hidden;
}

/* Header */
.si-opd-single-header{
  padding: 28px 28px 18px 28px;
  border-bottom: 1px solid #E5E7EB;
}

.si-opd-single-logo{
  width: 72px;
  height: 72px;
  object-fit: contain;
  border: 1px solid #E5E7EB;
  border-radius: 10px;
  background: #fff;
  padding: 8px;
  margin: 0 0 14px 0;
}

/* Titel */
.si-opd-single-title{
  font-family: 'Poppins', sans-serif;
  font-size: 28px;
  line-height: 1.2;
  font-weight: 700;
  margin: 0 0 12px 0;
  color: #111827;
}

/* Tags */
.si-opd-single-meta{
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 6px;
}

/* Hergebruik tags (zelfde als listing) */
.si-opd-tag{
  display: inline-block;
  padding: 4px 10px;
  font-size: 12px;
  font-family: 'Poppins', sans-serif;
  border-radius: 999px;
  background: #EEF2FF;
  color: #3730A3;
  white-space: nowrap;
}

.si-opd-tag--alt{
  background: #ECFEFF;
  color: #155E75;
}

/* Content */
.si-opd-single-content{
  padding: 24px 28px 30px 28px;
  font-family: 'Poppins', sans-serif;
  color: #111827;
  font-size: 15px;
  line-height: 1.75;
}

/* Typografie binnen content */
.si-opd-single-content p{
  margin: 0 0 14px 0;
  color: #374151;
}

.si-opd-single-content h2,
.si-opd-single-content h3,
.si-opd-single-content h4{
  font-family: 'Poppins', sans-serif;
  color: #111827;
  margin: 22px 0 10px 0;
  line-height: 1.3;
}

.si-opd-single-content h2{ font-size: 20px; font-weight: 700; }
.si-opd-single-content h3{ font-size: 17px; font-weight: 700; }
.si-opd-single-content h4{ font-size: 15px; font-weight: 700; }

.si-opd-single-content ul,
.si-opd-single-content ol{
  margin: 0 0 14px 22px;
  color: #374151;
}

.si-opd-single-content li{
  margin: 6px 0;
}

/* Links */
.si-opd-single-content a{
  color: #0456AB;
  text-decoration: none;
}

.si-opd-single-content a:hover{
  text-decoration: underline;
}

/* Quotes (optioneel) */
.si-opd-single-content blockquote{
  margin: 18px 0;
  padding: 14px 16px;
  border-left: 3px solid #0456AB;
  background: #F9FAFB;
  color: #374151;
  border-radius: 8px;
}

/* Afbeeldingen in content */
.si-opd-single-content img{
  max-width: 100%;
  height: auto;
  border-radius: 10px;
  margin: 12px 0 16px 0;
}

/* ==============================
   Responsive
   ============================== */

@media (max-width: 768px){
  .si-opd-single-header{
    padding: 22px 18px 14px 18px;
  }

  .si-opd-single-content{
    padding: 18px 18px 22px 18px;
  }

  .si-opd-single-title{
    font-size: 22px;
  }

  .si-opd-single-logo{
    width: 64px;
    height: 64px;
  }
}

</style>
   