<?php
if (!defined('ABSPATH')) exit;
if (!isset($query)) return;

function ob_render_term_chips($post_id) {
  $taxes = [
    OB_Taxonomies::TAX_PROV => 'Provincie',
    OB_Taxonomies::TAX_CITY => 'Stad',
    OB_Taxonomies::TAX_TYPE => 'Type',
  ];

  $out = '';

  foreach ($taxes as $tax => $label) {
    $terms = get_the_terms($post_id, $tax);
    if (is_wp_error($terms) || empty($terms)) continue;

    foreach ($terms as $t) {
      $out .= '<span class="ob-chip" data-tax="' . esc_attr($tax) . '">' . esc_html($t->name) . '</span>';
    }
  }

  if ($out === '') return '';

  return '<div class="ob-chips" aria-label="Labels">' . $out . '</div>';
}
?>

<div class="ob-listings ob-listings--memorial">
<?php if ($query->have_posts()) : ?>
  <?php while ($query->have_posts()) : $query->the_post(); ?>

    <article class="ob-memorial">
      <div class="ob-memorial__inner">

        <div class="ob-memorial__header">
          <a class="ob-memorial__title" href="<?php the_permalink(); ?>">
            <?php the_title(); ?>
          </a>

          <!-- ✅ Chips onder titel -->
          <?php echo ob_render_term_chips(get_the_ID()); ?>
        </div>

        <div class="ob-memorial__content">
          <?php if (has_post_thumbnail()): ?>
            <div class="ob-memorial__media">
              <?php the_post_thumbnail('thumbnail', ['loading' => 'lazy']); ?>
            </div>
          <?php endif; ?>

          <div class="ob-memorial__text">
            <div class="ob-memorial__excerpt">
              <?php echo esc_html( wp_trim_words( wp_strip_all_tags(get_the_excerpt()), 12, '…' ) ); ?>
            </div>

            <div class="ob-memorial__footer">
              <a class="ob-memorial__btn" href="<?php the_permalink(); ?>">Bekijken</a>
            </div>
          </div>
        </div>

      </div>
    </article>

  <?php endwhile; ?>

  <?php
    $max = (int) $query->max_num_pages;
    $paged = max(1, (int) $query->get('paged'));
    if ($max > 1):
  ?>
    <div class="ob-pagination" data-ob-pagination>
      <button type="button" class="ob-pagebtn" data-ob-page="<?php echo (int) max(1, $paged-1); ?>" <?php disabled($paged<=1); ?>>Vorige</button>
      <span class="ob-pagelabel">Pagina <?php echo (int)$paged; ?> van <?php echo (int)$max; ?></span>
      <button type="button" class="ob-pagebtn" data-ob-page="<?php echo (int) min($max, $paged+1); ?>" <?php disabled($paged>=$max); ?>>Volgende</button>
    </div>
  <?php endif; ?>

<?php else : ?>
  <div class="ob-empty">Geen overlijdensberichten gevonden.</div>
<?php endif; ?>
</div>

<style>
  /* Container */
  .ob-listings--memorial{
    max-width:1080px;
    margin:24px auto;
    padding:0 10px;
  }

  /* Each listing looks like a newspaper notice */
  .ob-memorial{ margin:0 0 14px; }

  .ob-memorial__inner{
    background:#fff;
    border:1px solid #DEDEDE;
    border-radius:5;
    padding:24px; 
    
  }

  .ob-memorial + .ob-memorial .ob-memorial__inner{ border-top:0; }

  /* Title */
  .ob-memorial__title{
    display:block;
    font-family: Inter; 
    font-size:24px;
    line-height:1.15;
    font-weight:700;
    color:#333 !important;
    text-decoration:none;
    margin:0 0 8px;
  }

  /* ✅ Chips */
  .ob-chips{
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    margin:0 0 12px;
  }
  .ob-chip{
    display:inline-block;
    font-family: Poppins; 
    font-size:12px;
    font-weight:700;
    color:#333;
    padding:8px;
    border:1px solid #DEDEDE;
    background:#FAFAFA;
    border-radius:999px;
    line-height:1;
  }

  /* Content layout */
  .ob-memorial__content{
  display:grid;
  
  gap:14px;
}

.ob-memorial__media{
  grid-column:1;
}

.ob-memorial__text{
  grid-column:1;
}


  .ob-memorial__media img{
    width:90px;
    height:90px;
    object-fit:cover;
    border:1px solid #D9D9D9;
    border-radius:0;
    display:block;
  }

  /* If no thumbnail, take full width (fallback; :has not supported everywhere) */
  .ob-memorial__text{ width:100%; }

  .ob-memorial__excerpt{
    font-family: Georgia, "Times New Roman", Times, serif;
    font-size:16px;
    line-height:1.55;
    color:#222;
    margin:0 0 12px;
  }

  .ob-memorial__btn{
    display:inline-block;
    font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    font-size:14px;
    font-weight:600;
    color:#111;
    text-decoration:none;
    padding:10px 14px;
    border:1px solid #BFBFBF;
    background:#fff;
  }
  .ob-memorial__btn:hover{ background:#F7F7F7; }

  .ob-pagination{
    display:flex;
    gap:12px;
    justify-content:center;
    align-items:center;
    margin:18px 0 0;
  }
  .ob-pagebtn{
    padding:10px 12px;
    border:1px solid #BFBFBF;
    background:#fff;
    cursor:pointer;
  }
  .ob-pagebtn:disabled{ opacity:.4; cursor:not-allowed; }
  .ob-pagelabel{
    font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    font-size:14px;
    color:#444;
  }

  @media (max-width: 700px){
    .ob-memorial__title{ font-size:20px; }
    .ob-memorial__content{ grid-template-columns: 70px 1fr; }
    .ob-memorial__media img{ width:70px; height:70px; }
  }
</style>
