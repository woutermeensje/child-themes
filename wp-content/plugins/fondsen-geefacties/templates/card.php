<?php if (!defined('ABSPATH')) exit; ?>

<?php
$img = get_the_post_thumbnail_url(get_the_ID(), 'large');
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

$type_terms = get_the_terms(get_the_ID(), FGA_Plugin::TAX_TYPE);
$kicker = (!is_wp_error($type_terms) && !empty($type_terms)) ? $type_terms[0]->name : 'Geefactie';

$first = get_post_meta(get_the_ID(), FGA_Plugin::META_FIRSTNAME, true);
$last  = get_post_meta(get_the_ID(), FGA_Plugin::META_LASTNAME, true);
$name  = trim($first . ' ' . $last);

$kapitaal  = get_post_meta(get_the_ID(), FGA_Plugin::META_BENODIGD_KAPITAAL, true);
$stichting = get_post_meta(get_the_ID(), FGA_Plugin::META_STICHTING_NAAM, true);
?>

<article class="fga-card">
  <a class="fga-card-link" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">

    <div class="fga-card-media" style="background-image:url('<?php echo esc_url($img); ?>')">
      <span class="fga-card-badge"><?php echo esc_html($kicker); ?></span>
    </div>

    <div class="fga-card-body">
      <h3 class="fga-card-title"><?php the_title(); ?></h3>

      <?php if (has_excerpt()): ?>
        <p class="fga-card-excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>
      <?php endif; ?>

      <div class="fga-card-meta">
        <span class="fga-chip"><?php echo esc_html(get_the_date()); ?></span>

        <?php if (!empty($name)): ?>
          <span class="fga-chip"><?php echo esc_html($name); ?></span>
        <?php endif; ?>

        <?php if (!empty($kapitaal) && (int)$kapitaal > 0): ?>
          <span class="fga-chip fga-chip--accent">
            €<?php echo esc_html(number_format_i18n((int) $kapitaal)); ?>
          </span>
        <?php endif; ?>

        <?php if (!empty($stichting)): ?>
          <span class="fga-chip"><?php echo esc_html($stichting); ?></span>
        <?php endif; ?>
      </div>

      <div class="fga-card-cta">
        <span class="fga-card-cta-text">Bekijk geefactie</span>
        <span class="fga-card-cta-arrow">→</span>
      </div>
    </div>
  </a>
</article>

<style>
:root{
  --fga-text:#333333;
  --fga-muted:#6b7280;
  --fga-border:#e6e6e6;
  --fga-blue:#0884CC;
  --fga-orange:#FF8C2C;
  --fga-bg:#ffffff;
}

/* ===== Card (templates/card.php) ===== */
.fga-card{
  background:var(--fga-bg);
  border:1px solid var(--fga-border);
  border-radius:5px;
  overflow:hidden;
  box-shadow:0 10px 30px rgba(0,0,0,.06);
  height:100%;
  transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}

.fga-card-link{
  display:block;
  height:100%;
  color:inherit;
  text-decoration:none;
}

.fga-card:hover{
  transform:translateY(-2px);
  border-color:rgba(8,132,204,.35);
  box-shadow:0 14px 40px rgba(0,0,0,.10);
}

/* Media */
.fga-card-media{
  height:180px;
  background-size:cover;
  background-position:center;
  position:relative;
}

.fga-card-media:after{
  content:"";
  position:absolute;
  inset:0;
  background:linear-gradient(to bottom, rgba(0,0,0,.12), rgba(0,0,0,0));
}

/* Badge */
.fga-card-badge{
  position:absolute;
  left:12px;
  top:12px;
  z-index:2;
  display:inline-flex;
  align-items:center;
  padding:7px 10px;
  border-radius:999px;
  background:#fff;
  border:1px solid rgba(255,255,255,.6);
  color:var(--fga-text);
  font-size:13px;
  font-weight:800;
  box-shadow:0 6px 18px rgba(0,0,0,.10);
}

/* Body */
.fga-card-body{
  padding:16px 16px 14px;
}

.fga-card-title{
  margin:0 0 8px;
  font-size:18px;
  line-height:1.25;
  color:var(--fga-text);
  letter-spacing:-0.01em;
}

.fga-card-excerpt{
  margin:0 0 12px;
  color:var(--fga-muted);
  font-size:14px;
  line-height:1.5;
}

/* Meta chips */
.fga-card-meta{
  display:flex;
  flex-wrap:wrap;
  gap:8px;
  margin-top:6px;
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

/* CTA */
.fga-card-cta{
  margin-top:14px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding-top:12px;
  border-top:1px solid var(--fga-border);
  color:var(--fga-blue);
  font-weight:800;
  font-size:14px;
}

.fga-card-cta-arrow{
  transition:transform .18s ease;
}

.fga-card:hover .fga-card-cta-arrow{
  transform:translateX(3px);
}

@media (max-width: 700px){
  .fga-card-media{ height:160px; }
  .fga-card-body{ padding:14px; }
}
</style>
