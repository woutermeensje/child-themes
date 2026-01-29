<?php
if ( ! defined('ABSPATH') ) exit;

?>

<?php if (!defined('ABSPATH')) exit; ?>

<div class="fod-org-grid-wrap">
  <div class="fod-org-grid" id="fod-org-grid"
       data-page="1"
       data-max-pages="<?php echo (int) ($data['max_pages'] ?? 1); ?>"
       data-per-page="<?php echo (int) ($data['per_page'] ?? 30); ?>">
    <?php
    if (!empty($data['posts'])) :
      global $post;
      foreach ($data['posts'] as $post) :
        setup_postdata($post);
        include __DIR__ . '/organisaties-grid-item.php';
      endforeach;
      wp_reset_postdata();
    else :
      echo '<p>Geen organisaties gevonden.</p>';
    endif;
    ?>
  </div>

  <?php if ( (int) ($data['max_pages'] ?? 1) > 1 ) : ?>
    <div class="fod-org-loadmore-wrap">
      <button type="button" class="fod-org-loadmore" id="fod-org-loadmore">
        Laad meer organisaties
      </button>
    </div>
  <?php endif; ?>
</div>


<style>
    /* =========================================
   Fondsen Organisaties Directory – Listings
   Doel: strak grid + cards + consistente thumbnails
   ========================================= */

/* Container (optioneel) */
.fod-org-results,
#fod-org-results {
  width: 100%;
}

/* GRID */
#fod-org-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 22px;
  margin-top: 18px;
}

.fod-org-grid-wrap {
    width: 1050px; 
    margin: 0 auto; 
}

/* Responsive */
@media (max-width: 1024px) {
  #fod-org-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
  }
}
@media (max-width: 640px) {
  #fod-org-grid {
    grid-template-columns: 1fr;
    gap: 14px;
  }
}

/* CARD */
.org-card {
  background: #fff;
  border: 1px solid #DEDEDE;
  border-radius: 5px;
  overflow: hidden;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
  min-height: 100%;
  display: flex;
  flex-direction: column;
}

.org-card:hover {
  transform: translateY(-2px);
  border-color: #DCDCDC;
  box-shadow: 0 14px 40px rgba(0,0,0,0.10);
}

/* Image wrapper */
.org-card__img {
  position: relative;
  width: 100%;
  aspect-ratio: 16 / 9;       /* houdt alles netjes gelijk */
  background: #F4F4F4;
  overflow: hidden;
}

/* WordPress thumbnail */
.org-card__img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform .22s ease;
}

.org-card:hover .org-card__img img {
  transform: scale(1.03);
}

/* Body */
.org-card__body {
  padding: 16px 16px 18px 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  flex: 1;
}

/* Title */
.org-card__title {
  margin: 0;
  font-size: 20px;
  line-height: 1.2;
  letter-spacing: -0.2px;
}

.org-card__title a {
  color: #222;
  text-decoration: none;
}

.org-card__title a:hover {
  text-decoration: underline;
}

/* Excerpt */
.org-card__excerpt {
  margin: 0;
  color: #555;
  font-size: 14px;
  line-height: 1.55;
}

/* (Optioneel) tags/metadata als je ze later toevoegt */
.org-card__meta {
  margin-top: auto;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.org-card__pill {
  display: inline-flex;
  align-items: center;
  padding: 6px 10px;
  border-radius: 999px;
  background: #F3F6FF;
  color: #1A4ED8;
  font-size: 12px;
  line-height: 1;
  border: 1px solid rgba(26, 78, 216, 0.12);
}

/* LOAD MORE WRAP */
.fod-org-loadmore-wrap,
#fod-org-loadmore-wrap {
  display: flex;
  justify-content: center;
  margin: 26px 0 10px 0;
}

/* LOAD MORE BUTTON */
#fod-org-loadmore,
.fod-org-loadmore {
  appearance: none;
  border: 1px solid #DDD;
  background: #fff;
  color: #222;
  font-size: 14px;
  font-weight: 600;
  padding: 12px 18px;
  border-radius: 12px;
  cursor: pointer;
  transition: background .15s ease, border-color .15s ease, transform .15s ease;
}

#fod-org-loadmore:hover,
.fod-org-loadmore:hover {
  background: #F7F7F7;
  border-color: #CFCFCF;
  transform: translateY(-1px);
}

#fod-org-loadmore:disabled,
.fod-org-loadmore:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* Loading state (als je .is-loading toevoegt in JS) */
#fod-org-loadmore.is-loading,
.fod-org-loadmore.is-loading {
  position: relative;
  padding-right: 44px;
}

#fod-org-loadmore.is-loading::after,
.fod-org-loadmore.is-loading::after {
  content: "";
  position: absolute;
  right: 16px;
  top: 50%;
  width: 16px;
  height: 16px;
  margin-top: -8px;
  border-radius: 50%;
  border: 2px solid rgba(0,0,0,0.25);
  border-top-color: rgba(0,0,0,0.75);
  animation: fodSpin .7s linear infinite;
}

@keyframes fodSpin {
  to { transform: rotate(360deg); }
}

</style>