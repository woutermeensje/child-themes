<?php
if (!defined('ABSPATH')) exit;

get_header();
?>

<div class="vpjobs-page">

  <!-- Bovenste promo header -->
  <div class="single-header">
    <div class="single-header-box">
      <h2 class="single-header-title">Blijf op de hoogte van de laatste vacatures</h2>

      <a href="/job-alerts/" class="single-header-cta">
        Alerts instellen
      </a>
    </div>
  </div>

  <!-- Single vacature -->
  <article class="vpjobs-single">
    <header class="vpjobs-single-header">

      <h1 class="vpjobs-single-title"><?php the_title(); ?></h1>

    
<?php
$post_id   = get_the_ID();
$post_type = get_post_type($post_id);

$tax_objects = get_object_taxonomies($post_type, 'objects');

$exclude = [
  'post_format',
  'nav_menu',
  'link_category',
];

$has_any = false;

ob_start();

foreach ($tax_objects as $tax) {
  if (in_array($tax->name, $exclude, true)) continue;

  $terms = get_the_terms($post_id, $tax->name);
  if (empty($terms) || is_wp_error($terms)) continue;

  foreach ($terms as $term) {
    if (!isset($term->name) || $term->name === '') continue;

    $has_any = true;
    ?>
    <a class="vac-tax-chip" href="<?php echo esc_url(get_term_link($term)); ?>">
      <?php echo esc_html($term->name); ?>
    </a>
    <?php
  }
}

$tax_content = ob_get_clean();

if ($has_any) : ?>
  <div class="vac-taxonomies">
    <?php echo $tax_content; ?>
  </div>
<?php endif; ?>

    </header>

    <div class="vpjobs-single-content">
      <?php the_content(); ?>
    </div>

    <?php
      $apply_url   = get_post_meta(get_the_ID(), '_vp_apply_url', true);
      $apply_email = get_post_meta(get_the_ID(), '_vp_apply_email', true);
    ?>

    <footer class="vpjobs-single-footer">
      <?php if ($apply_url): ?>
        <a class="vpjobs-apply" href="<?php echo esc_url($apply_url); ?>" target="_blank" rel="noopener">
          Solliciteer
        </a>
      <?php elseif ($apply_email): ?>
        <a class="vpjobs-apply" href="mailto:<?php echo antispambot(esc_attr($apply_email)); ?>">
          Solliciteer
        </a>
      <?php endif; ?>
    </footer>

  </article>

</div>

<style>
/* =========================================
   Page wrapper
   ========================================= */
.vpjobs-page{
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  box-sizing: border-box;
  font-family: Inter, sans-serif;
  color: #333;
}

/* =========================================
   Single header (promo blok bovenaan)
   ========================================= */
.single-header{
  max-width: 900px;
  width: 100%;
  margin: 24px auto;
  border: 1px solid #DEDEDE;
  border-radius: 5px;
  background: #fff;
  box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
  box-sizing: border-box;
}

.single-header-box{
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 24px;
  box-sizing: border-box;
}

.single-header-title{
  margin: 0;
  font-family: Inter, sans-serif;
  font-size: 20px !important;
  line-height: 1.2;
  color: #333;
  font-weight: 700;
}

.single-header-cta{
  display: inline-flex;       /* ✅ voorkomt “uitrekken” */
  align-items: center;
  justify-content: center;
  white-space: nowrap;

  border-radius: 5px;
  padding: 10px 14px;

  background: #EAF1FF;
  border: 2px solid #BFD3FF;
  color: #0B4DB8;

  font-family: Inter, sans-serif;
  font-weight: 700;
  font-size: 14px;
  text-decoration: none;
  box-sizing: border-box;
}

.single-header-cta:hover{
  background: #E3ECFF;
}

/* =========================================
   Vacature card
   ========================================= */
.vpjobs-single{
  max-width: 900px;
  width: 100%;
  border: 1px solid #E0E0E0;
  border-radius: 5px;
  padding: 24px;
  margin: 24px auto;
  background: #fff;
  box-sizing: border-box;
}

.vpjobs-single-header{
  margin-bottom: 18px;
}

.vpjobs-single-title{
  margin: 0 0 12px 0;
  font-size: 28px;
  line-height: 1.15;
  font-weight: 800;
  color: #111;
}



/* =========================================
   Taxonomie chips onder titel
   ========================================= */
/* =========================================
   Taxonomie chips – alles naast elkaar
   ========================================= */

.vac-taxonomies{
  display: flex;
  flex-wrap: wrap;      /* 🔑 breekt netjes naar nieuwe regel */
  gap: 8px;
  margin: 12px 0 18px 0;
}

.vac-tax-chip{
  display: inline-flex;
  align-items: center;
  justify-content: center;
  white-space: nowrap;

  padding: 7px 12px;
  border-radius: 5px;

  background: #EAF1FF;
  border: 2px solid #BFD3FF;
  color: #0B4DB8;

  font-family: Inter, sans-serif;
  font-weight: 600;
  font-size: 13px;
  line-height: 1;
  text-decoration: none;
}

.vac-tax-chip:hover{
  background: #E3ECFF;
}
.vac-tax-group{
  display: grid;
  gap: 8px;
}

.vac-tax-label{
  font-size: 12px;
  font-weight: 700;
  color: #333;
  opacity: 0.9;
}

.vac-tax-chips{
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.vac-tax-chip{
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: auto;                /* ✅ niet uitrekken */
  white-space: nowrap;

  padding: 7px 10px;
  border-radius: 5px;

  background: #EAF1FF;
  border: 2px solid #BFD3FF;
  color: #0B4DB8;

  font-weight: 700;
  font-size: 13px;
  line-height: 1;
  text-decoration: none;
  box-sizing: border-box;
}

.vac-tax-chip:hover{
  background: #E3ECFF;
}

.vpjobs-single-content{
  font-size: 16px;
  line-height: 1.65;
  color: #333;
}

/* =========================================
   Footer / solliciteer knop
   ========================================= */
.vpjobs-single-footer{
  margin-top: 22px;
  display: flex;
  justify-content: flex-start;
}

.vpjobs-apply{
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 12px 16px;
  border-radius: 5px;
  border: 1px solid #DEDEDE;
  background: #111;
  color: #fff;
  text-decoration: none;
  font-weight: 700;
  font-size: 14px;
}

.vpjobs-apply:hover{
  opacity: 0.9;
}

/* =========================================
   Tablet & mobiel
   ========================================= */
@media (max-width: 768px){

  .vpjobs-page{
    padding: 16px;
  }

  .single-header{
    margin: 16px auto;
  }

  .single-header-box{
    flex-direction: column;
    align-items: stretch;
    padding: 18px;
    gap: 14px;
  }

  .single-header-title{
    font-size: 18px;
    line-height: 1.25;
  }

  .single-header-cta{
    width: 100%; /* op mobiel wél full width */
    padding: 12px 14px;
    font-size: 16px;
  }

  .vpjobs-single{
    padding: 18px;
    margin: 16px auto;
  }

  .vpjobs-single-title{
    font-size: 24px;
  }
}
</style>

<?php get_footer(); ?>