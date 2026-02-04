<?php if (!defined('ABSPATH')) exit; ?>

<?php
  $post_id = get_the_ID();

  $goal   = Fondsen_Geefacties_Plugin::get_amount_int($post_id, Fondsen_Geefacties_Plugin::META_GOAL);
  $raised = Fondsen_Geefacties_Plugin::get_amount_int($post_id, Fondsen_Geefacties_Plugin::META_RAISED);

  $pct = 0;
  if ($goal > 0) {
    $pct = (int) round(($raised / $goal) * 100);
  }
  // Geef.nl toont soms >100% als er meer opgehaald is
  $pct_label = $pct . '%';

  $img_url = '';
  if (has_post_thumbnail($post_id)) {
    $img_url = get_the_post_thumbnail_url($post_id, 'large');
  }

  // fallback (lichte placeholder)
  if (!$img_url) {
    $img_url = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="700"><rect width="100%" height="100%" fill="#eef2f7"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#94a3b8" font-family="Arial" font-size="42">Geefactie</text></svg>');
  }

  $author_name = get_the_author();
  $org_line = $author_name ? $author_name : '';

  $amount_line = '';
  if ($goal > 0) {
    $amount_line = Fondsen_Geefacties_Plugin::euro($raised) . ' opgehaald van ' . Fondsen_Geefacties_Plugin::euro($goal);
  } elseif ($raised > 0) {
    $amount_line = Fondsen_Geefacties_Plugin::euro($raised) . ' opgehaald';
  }
?>

<article class="ga-card">
  <a class="ga-card__link" href="<?php echo esc_url(get_permalink()); ?>">

    <div class="ga-card__media" style="background-image:url('<?php echo esc_url($img_url); ?>');">
      <div class="ga-card__progress">
        <div class="ga-progress__bar">
          <span class="ga-progress__fill" style="width:<?php echo esc_attr(max(0, min(100, $pct))); ?>%"></span>
        </div>
        <div class="ga-progress__badge" aria-label="<?php echo esc_attr($pct_label); ?>">
          <?php echo esc_html($pct_label); ?>
        </div>
      </div>
    </div>

    <div class="ga-card__body">
      <?php if ($amount_line): ?>
        <div class="ga-card__amount">
          <?php echo esc_html($amount_line); ?>
        </div>
      <?php endif; ?>

      <h3 class="ga-card__title">
        <?php the_title(); ?>
      </h3>

      <div class="ga-card__meta">
        <span class="ga-card__avatar" aria-hidden="true"></span>
        <span class="ga-card__byline"><?php echo esc_html($org_line); ?></span>
      </div>
    </div>

  </a>
</article>
