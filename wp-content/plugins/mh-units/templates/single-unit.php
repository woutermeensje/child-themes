<?php
if (!defined('ABSPATH')) exit;

get_header();

if (have_posts()) : while (have_posts()) : the_post();

$price_note   = get_post_meta(get_the_ID(), '_mh_unit_price_note', true);
$terms_type   = get_the_terms(get_the_ID(), 'mh_unit_type');
$terms_cond   = get_the_terms(get_the_ID(), 'mh_unit_conditie');
$terms_aanbod = get_the_terms(get_the_ID(), 'mh_unit_aanbod');
$aanbod_label = (!is_wp_error($terms_aanbod) && !empty($terms_aanbod)) ? $terms_aanbod[0]->name : '';
$unit_id      = get_the_ID();
$in_quote     = function_exists('mh_quote_has_product') && mh_quote_has_product($unit_id);
$quote_url    = function_exists('mh_quote_get_page_url') ? mh_quote_get_page_url() : home_url('/mijn-offerte/');
$just_added   = isset($_GET['mh_added']) && (int) $_GET['mh_added'] === $unit_id;
?>

<main class="mhu-single">

    <!-- Breadcrumb -->
    <nav class="mhu-breadcrumb" aria-label="Breadcrumb">
        <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
        <span class="mhu-breadcrumb__sep" aria-hidden="true">›</span>
        <a href="<?php echo esc_url(home_url('/units/')); ?>">Units</a>
        <span class="mhu-breadcrumb__sep" aria-hidden="true">›</span>
        <span aria-current="page"><?php the_title(); ?></span>
    </nav>

    <!-- Title + badges -->
    <header class="mhu-header">
        <div class="mhu-header__top">
            <h1 class="mhu-header__title"><?php the_title(); ?></h1>
            <?php if ($aanbod_label): ?>
                <span class="mhu-aanbod-badge"><?php echo esc_html($aanbod_label); ?></span>
            <?php endif; ?>
        </div>

        <?php
        $has_type = (!is_wp_error($terms_type) && !empty($terms_type));
        $has_cond = (!is_wp_error($terms_cond) && !empty($terms_cond));
        if ($has_type || $has_cond): ?>
        <div class="mhu-header__badges">
            <?php if ($has_type): foreach ($terms_type as $term): ?>
                <span class="mhu-tax-badge"><?php echo esc_html($term->name); ?></span>
            <?php endforeach; endif; ?>
            <?php if ($has_cond): foreach ($terms_cond as $term): ?>
                <span class="mhu-tax-badge mhu-tax-badge--cond"><?php echo esc_html($term->name); ?></span>
            <?php endforeach; endif; ?>
        </div>
        <?php endif; ?>
    </header>

    <!-- Image + Sidebar -->
    <div class="mhu-top">

        <div class="mhu-image">
            <?php if (has_post_thumbnail()): ?>
                <?php the_post_thumbnail('large', ['loading' => 'eager']); ?>
            <?php else: ?>
                <div class="mhu-image__placeholder" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" fill="none" stroke="#c8d5e3" stroke-width="1.5" width="72" height="72"><rect x="2" y="10" width="60" height="44" rx="5"/><circle cx="20" cy="28" r="6"/><path d="M2 44 l18-16 12 12 10-12 22 16"/></svg>
                </div>
            <?php endif; ?>
        </div>

        <aside class="mhu-sidebar">
            <div class="mhu-sidebar__box">

                <?php if ($price_note || $aanbod_label || $has_type || $has_cond): ?>
                <ul class="mhu-meta">
                    <?php if ($aanbod_label): ?>
                    <li class="mhu-meta__row">
                        <span class="mhu-meta__label">Aanbod</span>
                        <span class="mhu-meta__value"><?php echo esc_html($aanbod_label); ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if ($has_type): ?>
                    <li class="mhu-meta__row">
                        <span class="mhu-meta__label">Type</span>
                        <span class="mhu-meta__value"><?php echo esc_html(implode(', ', wp_list_pluck($terms_type, 'name'))); ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if ($has_cond): ?>
                    <li class="mhu-meta__row">
                        <span class="mhu-meta__label">Conditie</span>
                        <span class="mhu-meta__value"><?php echo esc_html(implode(', ', wp_list_pluck($terms_cond, 'name'))); ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if ($price_note): ?>
                    <li class="mhu-meta__row mhu-meta__row--price">
                        <span class="mhu-meta__label">Prijs</span>
                        <span class="mhu-meta__value mhu-meta__price"><?php echo esc_html($price_note); ?></span>
                    </li>
                    <?php endif; ?>
                </ul>
                <hr class="mhu-sidebar__divider">
                <?php endif; ?>

                <h3 class="mhu-sidebar__heading">Interesse in deze unit?</h3>
                <p class="mhu-sidebar__text">Neem contact op voor een bezichtiging of vrijblijvende offerte.</p>

                <?php if ($in_quote || $just_added): ?>
                <a class="mhu-btn mhu-btn--quote" href="<?php echo esc_url($quote_url); ?>">
                    Bekijk mijn offerte
                </a>
                <?php else: ?>
                <form method="post" action="" class="mhu-quote-form">
                    <?php wp_nonce_field('mh_add_to_quote', 'mh_nonce'); ?>
                    <input type="hidden" name="mh_action" value="add_to_quote">
                    <input type="hidden" name="product_id" value="<?php echo esc_attr($unit_id); ?>">
                    <input type="hidden" name="item_type" value="mh_unit">
                    <button type="submit" class="mhu-btn mhu-btn--quote">Toevoegen aan offerte</button>
                </form>
                <?php endif; ?>

                <a class="mhu-btn mhu-btn--primary"
                   href="<?php echo esc_url(home_url('/offerte-aanvragen/?unit=' . get_the_ID())); ?>">
                    Informatie aanvragen
                </a>
                <a class="mhu-btn mhu-btn--outline" href="tel:0852392040">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16" aria-hidden="true"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.7 10.5 19.79 19.79 0 01.67 4.08 2 2 0 012.66 2h3a2 2 0 012 1.72c.13.96.36 1.9.7 2.81a2 2 0 01-.45 2.11L6.91 9.91a16 16 0 006.18 6.18l1.27-1.27a2 2 0 012.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0122 16.92z"/></svg>
                    085 239 2040
                </a>

                <a class="mhu-back-link" href="<?php echo esc_url(home_url('/units/')); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
                    Terug naar alle units
                </a>

            </div>
        </aside>
    </div>

    <!-- Content / Omschrijving -->
    <?php if (get_the_content()): ?>
    <section class="mhu-content">
        <h2 class="mhu-content__title">Omschrijving</h2>
        <div class="mhu-content__body">
            <?php the_content(); ?>
        </div>
    </section>
    <?php endif; ?>

</main>

<!-- Footer CTA -->
<section class="mhu-footer-cta">
    <div class="mhu-footer-cta__inner">
        <p class="mhu-footer-cta__text">Kom in contact met één van onze adviseurs.</p>
        <a href="<?php echo esc_url(home_url('/advies/')); ?>" class="mhu-footer-cta__btn">Contact opnemen</a>
    </div>
</section>

<style>
/* ============================================================
   MH Units – Single Unit Page
   ============================================================ */

:root {
  --mhu-primary:    var(--color-primary, #25476B);
  --mhu-primary-dk: var(--color-primary-hover, #325F8D);
  --mhu-secondary:  var(--color-secondary, #39749B);
  --mhu-ocean:      var(--color-ocean, #4188AA);
  --mhu-accent:     var(--color-accent, #8AC697);
  --mhu-highlight:  var(--color-highlight, #BFE396);
  --mhu-lime:       var(--color-highlight-soft, #CCDA91);
  --mhu-surface:    var(--color-surface, #FFFFFF);
  --mhu-surface-alt: var(--color-surface-alt, #F1F8EE);
  --mhu-text:       var(--color-text, #25476B);
  --mhu-muted:      var(--color-text-soft, #39749B);
  --mhu-border:     var(--color-border, #BFE396);
  --mhu-bg:         var(--color-bg, #F7FBF7);
  --mhu-radius:     6px;
  --mhu-shadow:     0 14px 34px rgba(37, 71, 107, 0.12);
}

.mhu-single {
  max-width: 1200px;
  margin: 40px auto 64px;
  padding: 0 24px;
  font-family: 'Poppins', sans-serif;
  color: var(--mhu-text);
}

/* ── Breadcrumb ── */
.mhu-breadcrumb {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 4px;
  margin-bottom: 24px;
  font-size: 13px;
  color: var(--mhu-muted);
}

.mhu-breadcrumb a {
  color: var(--mhu-muted);
  text-decoration: none;
}

.mhu-breadcrumb a:hover {
  color: var(--mhu-primary);
  text-decoration: underline;
}

.mhu-breadcrumb__sep {
  color: #d1d5db;
  font-size: 16px;
  line-height: 1;
}

.mhu-breadcrumb span[aria-current] {
  color: var(--mhu-text);
  font-weight: 500;
}

/* ── Page header ── */
.mhu-header {
  margin-bottom: 28px;
}

.mhu-header__top {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  flex-wrap: wrap;
  margin-bottom: 14px;
}

.mhu-header__title {
  flex: 1 1 auto;
  margin: 0;
  font-size: 28px;
  font-family: 'Balgin Bold', 'Balgin-Bold', serif;
  font-weight: 700;
  line-height: 1.2;
  color: var(--mhu-text);
}

/* Aanbod badge (header) */
.mhu-aanbod-badge {
  flex-shrink: 0;
  display: inline-flex;
  align-items: center;
  padding: 6px 14px;
  border-radius: var(--mhu-radius);
  background: var(--mhu-lime);
  color: var(--mhu-primary);
  border: 2px solid var(--mhu-highlight);
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  font-size: 13px;
  line-height: 1;
  white-space: nowrap;
  margin-top: 4px;
}

/* Taxonomy badges */
.mhu-header__badges {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.mhu-tax-badge {
  display: inline-flex;
  align-items: center;
  padding: 5px 12px;
  border-radius: var(--mhu-radius);
  background: rgba(65, 136, 170, 0.10);
  border: 2px solid rgba(65, 136, 170, 0.24);
  color: var(--mhu-secondary);
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  font-size: 13px;
  white-space: nowrap;
}

.mhu-tax-badge--cond {
  background: rgba(109, 180, 156, 0.12);
  border-color: rgba(109, 180, 156, 0.26);
  color: #2f6b69;
}

/* ── Top layout: image + sidebar ── */
.mhu-top {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 32px;
  margin-bottom: 48px;
  align-items: start;
}

/* ── Image ── */
.mhu-image {
  border-radius: var(--mhu-radius);
  overflow: hidden;
  box-shadow: var(--mhu-shadow);
  background: var(--mhu-bg);
  aspect-ratio: 4 / 3;
  position: relative;
}

.mhu-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.mhu-image__placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f1f5f9;
  min-height: 320px;
}

/* ── Sidebar ── */
.mhu-sidebar {
  position: sticky;
  top: 100px;
}

.mhu-sidebar__box {
  background: var(--mhu-surface);
  border: 1px solid var(--mhu-border);
  border-radius: var(--mhu-radius);
  box-shadow: var(--mhu-shadow);
  padding: 28px;
  display: flex;
  flex-direction: column;
  gap: 0;
}

/* Meta list */
.mhu-meta {
  list-style: none;
  margin: 0 0 4px;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0;
}

.mhu-meta__row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
  padding: 11px 0;
  border-bottom: 1px solid var(--mhu-border);
  font-size: 14px;
}

.mhu-meta__row:first-child {
  padding-top: 0;
}

.mhu-meta__label {
  color: var(--mhu-muted);
  font-weight: 400;
  white-space: nowrap;
}

.mhu-meta__value {
  color: var(--mhu-text);
  font-weight: 600;
  text-align: right;
}

.mhu-meta__row--price .mhu-meta__label {
  color: var(--mhu-text);
  font-weight: 600;
}

.mhu-meta__price {
  color: var(--mhu-secondary);
  font-weight: 700;
  font-size: 15px;
}

.mhu-sidebar__divider {
  border: none;
  border-top: 1px solid var(--mhu-border);
  margin: 20px 0;
}

.mhu-sidebar__heading {
  margin: 0 0 10px;
  font-size: 18px;
  font-family: 'Balgin Bold', 'Balgin-Bold', serif;
  font-weight: 700;
  color: var(--mhu-text);
  line-height: 1.2;
}

.mhu-sidebar__text {
  margin: 0 0 20px;
  font-size: 14px;
  line-height: 1.6;
  color: var(--mhu-muted);
}

.mhu-quote-form {
  margin: 0;
}

/* ── Buttons ── */
.mhu-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 13px 16px;
  border-radius: var(--mhu-radius);
  font-family: 'Poppins', sans-serif;
  font-size: 15px;
  font-weight: 600;
  text-decoration: none !important;
  text-align: center;
  white-space: nowrap;
  cursor: pointer;
  transition: background .15s ease, color .15s ease, border-color .15s ease;
  margin-bottom: 10px;
  border: 2px solid transparent;
  box-shadow: 0 10px 24px rgba(37, 71, 107, 0.12);
}

.mhu-btn--primary {
  background: linear-gradient(135deg, var(--mhu-ocean) 0%, var(--mhu-secondary) 100%);
  color: #fff !important;
  border-color: var(--mhu-ocean);
}

.mhu-btn--primary:hover {
  background: linear-gradient(135deg, var(--mhu-secondary) 0%, var(--mhu-primary-dk) 100%);
  border-color: var(--mhu-secondary);
}

.mhu-btn--quote {
  background: linear-gradient(135deg, var(--mhu-lime) 0%, var(--mhu-highlight) 45%, var(--mhu-accent) 100%);
  color: var(--mhu-primary) !important;
  border-color: var(--mhu-highlight);
}

.mhu-btn--quote:hover {
  background: linear-gradient(135deg, var(--mhu-highlight) 0%, var(--mhu-accent) 50%, var(--color-tertiary, #6DB49C) 100%);
  border-color: var(--mhu-accent);
  color: var(--mhu-primary) !important;
}

.mhu-btn--outline {
  background: var(--mhu-surface-alt);
  color: var(--mhu-primary) !important;
  border-color: var(--mhu-border);
  box-shadow: none;
}

.mhu-btn--outline:hover {
  background: var(--mhu-primary);
  border-color: var(--mhu-primary);
  color: #fff !important;
}

/* Back link */
.mhu-back-link {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  margin-top: 8px;
  font-size: 13px;
  color: var(--mhu-muted);
  text-decoration: none;
  transition: color .15s ease;
  align-self: center;
}

.mhu-back-link:hover {
  color: var(--mhu-primary);
}

/* ── Content / Description ── */
.mhu-content {
  max-width: 800px;
}

.mhu-content__title {
  margin: 0 0 20px;
  font-size: 22px;
  font-family: 'Balgin Bold', 'Balgin-Bold', serif;
  font-weight: 700;
  color: var(--mhu-text);
  padding-bottom: 14px;
  border-bottom: 2px solid var(--mhu-border);
}

.mhu-content__body {
  background: var(--mhu-surface);
  border: 1px solid var(--mhu-border);
  border-radius: var(--mhu-radius);
  box-shadow: var(--mhu-shadow);
  padding: 32px;
  font-size: 16px;
  line-height: 1.75;
  color: var(--mhu-text);
}

.mhu-content__body h2,
.mhu-content__body h3 {
  margin-top: 28px;
  margin-bottom: 12px;
  font-family: 'Balgin Bold', 'Balgin-Bold', serif;
}

.mhu-content__body h2:first-child,
.mhu-content__body h3:first-child {
  margin-top: 0;
}

.mhu-content__body p {
  margin: 0 0 16px;
}

.mhu-content__body p:last-child {
  margin-bottom: 0;
}

.mhu-content__body ul,
.mhu-content__body ol {
  padding-left: 20px;
  margin: 0 0 16px;
}

.mhu-content__body li {
  margin-bottom: 6px;
}

/* ── Footer CTA ── */
.mhu-footer-cta {
  background: linear-gradient(135deg, var(--mhu-primary) 0%, var(--mhu-primary-dk) 100%);
  width: 100%;
  margin-top: 64px;
}

.mhu-footer-cta__inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 32px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
}

.mhu-footer-cta__text {
  margin: 0;
  font-family: 'Balgin Bold', 'Balgin-Bold', serif;
  font-size: 18px;
  font-weight: 700;
  color: #fff;
}

.mhu-footer-cta__btn {
  flex-shrink: 0;
  display: inline-flex;
  align-items: center;
  padding: 12px 24px;
  border-radius: var(--mhu-radius);
  background: var(--mhu-highlight);
  color: var(--mhu-primary) !important;
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  font-size: 15px;
  text-decoration: none !important;
  white-space: nowrap;
  transition: background .15s ease;
  border: 2px solid var(--mhu-highlight);
}

.mhu-footer-cta__btn:hover {
  background: var(--mhu-lime);
  border-color: var(--mhu-lime);
}

/* ── Responsive ── */
@media (max-width: 960px) {
  .mhu-top {
    grid-template-columns: 1fr;
  }

  .mhu-sidebar {
    position: relative;
    top: auto;
  }

  .mhu-image {
    aspect-ratio: 16 / 9;
  }
}

@media (max-width: 640px) {
  .mhu-single {
    margin-top: 24px;
    padding: 0 16px;
  }

  .mhu-header__title {
    font-size: 22px;
  }

  .mhu-content__body {
    padding: 20px;
  }

  .mhu-footer-cta__inner {
    flex-direction: column;
    align-items: flex-start;
    padding: 24px 16px;
  }

  .mhu-footer-cta__btn {
    width: 100%;
    justify-content: center;
  }
}
</style>

<?php endwhile; endif;

get_footer();
