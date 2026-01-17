<?php if (!defined('ABSPATH')) exit; ?>

<a class="mh-unit-row" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">
    <div class="mh-unit-row-image">
        <?php if (has_post_thumbnail()): ?>
            <?php the_post_thumbnail('medium_large'); ?>
        <?php endif; ?>
    </div>

    <div class="mh-unit-row-content">
        <h3 class="mh-unit-row-title"><?php the_title(); ?></h3>

        <p class="mh-unit-row-excerpt">
            <?php
            // Excerpt max ~50 woorden
            $text = get_the_excerpt();
            if (!$text) {
                $text = wp_strip_all_tags(get_the_content());
            }
            echo esc_html( wp_trim_words($text, 20, '…') );
            ?>
        </p>

        <?php
$terms_type = get_the_terms(get_the_ID(), 'mh_unit_type');
$terms_cond = get_the_terms(get_the_ID(), 'mh_unit_conditie');

if (
  (!is_wp_error($terms_type) && !empty($terms_type)) ||
  (!is_wp_error($terms_cond) && !empty($terms_cond))
): ?>
  <div class="mh-unit-row-tax">
    <?php if (!is_wp_error($terms_type) && !empty($terms_type)): ?>
      <?php foreach ($terms_type as $term): ?>
        <span class="mh-unit-tax-badge mh-unit-tax-type"><?php echo esc_html($term->name); ?></span>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!is_wp_error($terms_cond) && !empty($terms_cond)): ?>
      <?php foreach ($terms_cond as $term): ?>
        <span class="mh-unit-tax-badge mh-unit-tax-conditie"><?php echo esc_html($term->name); ?></span>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
<?php endif; ?>


        <span class="mh-unit-row-btn">Unit bekijken</span>
    </div>
</a>

<style>
/* ===== UNIT ITEM (ROW) ===== */

.mh-unit-row{
  display: flex;
  gap: 16px;
  width: 100%;
  border: 1px solid #ededed;
  overflow: hidden;
  text-decoration: none;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,.15) !important;
  margin-bottom: 24px;
  margin-top: 24px; 
  height: 325px; 
}

/* 1/3 afbeelding links */
.mh-unit-row-image{
  flex: 0 0 40%;
  position: relative;
  background: #f5f5f5;
}

.mh-unit-row-image img{
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

/* 2/3 content rechts */
.mh-unit-row-content{
  flex: 1;
  padding: 14px 16px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  font-family: 'Poppins', sans-serif;
    color: #333;
    font-size: 15px;
}

.mh-unit-row-title{
    margin: 0 0 16px; /* meer ruimte onder de titel */
  line-height: 1.2;
  font-family: 'Poppins', sans-serif;
    color: #333;
    font-size: 24px;
    font-family: Balgin Bold; 
}

.mh-unit-row-excerpt{
    margin: 0 0 20px; /* meer witruimte onder tekst */

  line-height: 1.5;
  font-family: 'Poppins', sans-serif;
    color: #333;
    font-size: 15px;
}

/* “Knop” styling (span, omdat de hele card al een link is) */
.mh-unit-row-btn{
  padding: 14px;
  border-radius: 0px;
            width: 33%; 
    margin-top: 16px;
    background-color: #0456ABFA; 
    color: #fff;
    font-family: Balgin Bold; 
    font-size: 14px;
    text-align: center; 
    text-decoration: none !important; 
}

/* Hover effect */
.mh-unit-row:hover{
  border-color: #ccc;
}

/* Mobile: onder elkaar (afbeelding boven, tekst onder) */
@media (max-width: 700px){
  .mh-unit-row{
    flex-direction: column;
  }
  .mh-unit-row-image{
    flex: 0 0 auto;
  }
}
</style>


<style>
    /* Mobile: onder elkaar (afbeelding boven, tekst onder) */
@media (max-width: 700px){

  .mh-unit-row{
    flex-direction: column;
    height: auto;              /* ✅ niet meer afkappen */
    gap: 0;                    /* ✅ geen gap tussen image/content, voelt als 1 card */
    margin-top: 16px;
    margin-bottom: 16px;
  }

  .mh-unit-row-image{
    flex: 0 0 auto;
    width: 100%;
    height: 200px;             /* ✅ vaste hoogte voor de foto op mobiel */
  }

  .mh-unit-row-image img{
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .mh-unit-row-content{
    padding: 14px 14px;        /* ✅ iets compacter */
  }

  .mh-unit-row-title{
    font-size: 20px;           /* ✅ kleiner op mobiel */
    margin-bottom: 10px;
  }

  .mh-unit-row-excerpt{
    font-size: 14px;           /* ✅ iets kleiner */
    margin-bottom: 14px;
  }

  .mh-unit-row-btn{
    width: fit-content;        /* ✅ knop blijft compact */
    padding: 10px 12px;
  }


}

/* ===== Taxonomy badges onder excerpt ===== */

.mh-unit-row-tax{
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin: 0 0 18px;
}

/* Basis badge */
.mh-unit-tax-badge{
  display: inline-flex;
  align-items: center;
  padding: 8px 14px;
  border-radius: 999px;
  font-size: 13px;
  line-height: 1;
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  text-decoration: none !important;     /* ❌ geen underline */
  border: 2px solid transparent;
  white-space: nowrap;
}

/* TYPE UNIT – lichtblauw */
.mh-unit-tax-type{
  background: #EAF2FF;
  border-color: #C7DBFF;
  color: #0456AB;
}

/* CONDITIE – donkerder blauw */
.mh-unit-tax-conditie{
  background: #DCE9FF;
  border-color: #9EBEFF;
  color: #0456AB;
}





@media (max-width: 700px){
  .mh-unit-tax-badge{ font-size: 12px; padding: 5px 9px; }
}



</style>