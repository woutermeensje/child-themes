<?php if (!defined('ABSPATH')) exit; ?>

<?php
$img = get_the_post_thumbnail_url(get_the_ID(), 'large');
if (!$img) {
  $img = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="600"><rect width="100%" height="100%" fill="#f2f2f2"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#999" font-family="Arial" font-size="28">Geen afbeelding</text></svg>');
}
?>

<article class="fga-card">
  <a class="fga-card-link" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">
    <div class="fga-card-media" style="background-image:url('<?php echo esc_url($img); ?>')"></div>

    <div class="fga-card-body">
      <div class="fga-card-kicker">
        <?php
          $type_terms = get_the_terms(get_the_ID(), FGA_Plugin::TAX_TYPE);
          if (!is_wp_error($type_terms) && !empty($type_terms)) {
            echo esc_html($type_terms[0]->name);
          } else {
            echo 'Geefactie';
          }
        ?>
      </div>

      <h3 class="fga-card-title"><?php the_title(); ?></h3>

      <?php if (has_excerpt()): ?>
        <p class="fga-card-excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>
      <?php endif; ?>

      <div class="fga-card-meta">
        <?php
$first = get_post_meta(get_the_ID(), FGA_Plugin::META_FIRSTNAME, true);
$last  = get_post_meta(get_the_ID(), FGA_Plugin::META_LASTNAME, true);
$name  = trim($first . ' ' . $last);
?>
<span><?php echo esc_html($name ?: ''); ?></span>

        <span>•</span>
        <span><?php echo esc_html(get_the_date()); ?></span>
      </div>
    </div>
  </a>
</article>


<style>
    /* ===== Card (templates/card.php) ===== */

.fga-card {
  background: #fff;
  border: 1px solid #e6e6e6;
  border-radius: 5px;
  overflow: hidden;
  box-shadow: 0 8px 24px rgba(0,0,0,.06);
  height: 100%;
}

.fga-card-link {
  display: block;
  height: 100%;
  color: inherit;
  text-decoration: none;
}

.fga-card-media {
  height: 170px;
  background-size: cover;
  background-position: center;
}

.fga-card-body {
  padding: 14px 14px 16px;
}

.fga-card-kicker {
  font-size: 13px;
  opacity: .75;
  margin-bottom: 6px;
}

.fga-card-title {
  margin: 0 0 8px;
  font-size: 18px;
  line-height: 1.25;
}

.fga-card-excerpt {
  margin: 0 0 10px;
  opacity: .82;
  font-size: 14px;
  line-height: 1.45;
}

.fga-card-meta {
  display: flex;
  gap: 8px;
  font-size: 13px;
  opacity: .7;
}

</style>