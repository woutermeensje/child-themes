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
  box-shadow: 0 10px 40px -5px rgba(0,0,0,.15);
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
  margin: 0 0 8px;
  line-height: 1.2;
  font-family: 'Poppins', sans-serif;
    color: #333;
    font-size: 24px;
}

.mh-unit-row-excerpt{
  margin: 0 0 12px;
  line-height: 1.5;
  font-family: 'Poppins', sans-serif;
    color: #333;
    font-size: 15px;
}

/* “Knop” styling (span, omdat de hele card al een link is) */
.mh-unit-row-btn{
  display: inline-block;
  padding: 10px 14px;
  border-radius: 8px;
  border: 1px solid #ddd;
  width: fit-content;
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


</style>