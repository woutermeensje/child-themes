<?php
if (!defined('ABSPATH')) exit;
get_header();
?>

<div class="single-listing">



  <div class="listing-container">



    <main class="ob-single">
      <?php while (have_posts()) : the_post(); ?>
        <article class="ob-single__card">
          <h1 class="ob-single__title"><?php the_title(); ?></h1>

          <?php if (has_post_thumbnail()): ?>
            <div class="ob-single__media"><?php the_post_thumbnail('large'); ?></div>
          <?php endif; ?>

          <div class="ob-single__content">
            <?php the_content(); ?>
          </div>
        </article>
      <?php endwhile; ?>
    </main>

    <!-- ✅ Nieuwe nette kaart: Contact & Condoleren -->
    <?php if (have_posts()) : the_post(); endif; /* zorgt dat get_the_ID werkt buiten de loop als theme dat verwacht */ ?>
    <section class="ob-info-card" aria-label="Contact en condoleren">
      <header class="ob-info-card__header">
        <h2 class="ob-info-card__title">Contact & condoleren</h2>
        <p class="ob-info-card__subtitle">Vind de gegevens van de uitvaartondernemer of betuig je medeleven.</p>
      </header>

      <div class="ob-info-card__grid">

        <!-- Uitvaartondernemer -->
        <div class="ob-panel">
          <h3 class="ob-panel__title">Uitvaartondernemer</h3>

          <div class="ob-kv">
            <div class="ob-kv__row">
              <span class="ob-kv__label">Naam</span>
              <span class="ob-kv__value"><?php echo esc_html(get_post_meta(get_the_ID(), 'uitvaartondernemer_naam', true) ?: '—'); ?></span>
            </div>

            <div class="ob-kv__row">
              <span class="ob-kv__label">Contact</span>
              <span class="ob-kv__value"><?php echo esc_html(get_post_meta(get_the_ID(), 'uitvaartondernemer_contact', true) ?: '—'); ?></span>
            </div>

            <div class="ob-kv__row">
              <span class="ob-kv__label">Telefoon</span>
              <span class="ob-kv__value"><?php echo esc_html(get_post_meta(get_the_ID(), 'uitvaartondernemer_tel', true) ?: '—'); ?></span>
            </div>

            <div class="ob-kv__row">
              <span class="ob-kv__label">E-mail</span>
              <span class="ob-kv__value"><?php echo esc_html(get_post_meta(get_the_ID(), 'uitvaartondernemer_email', true) ?: '—'); ?></span>
            </div>
          </div>

          <?php
            $contact_url = get_post_meta(get_the_ID(), 'uitvaartondernemer_url', true);
            if (!$contact_url) $contact_url = '#';
          ?>
          <div class="ob-panel__actions">
            <a class="ob-cta ob-cta--primary" href="<?php echo esc_url($contact_url); ?>">Contact opnemen</a>
          </div>
        </div>

        <!-- Condoleren -->
        <div class="ob-panel">
          <h3 class="ob-panel__title">Condoleer de nabestaanden</h3>
          <p class="ob-panel__text">
            Betuig je medeleven via een kort bericht. Je bericht wordt gedeeld met de nabestaanden.
          </p>

          <ul class="ob-bullets">
            <li>Schrijf een persoonlijk bericht</li>
            <li>Voeg je naam toe (optioneel)</li>
            <li>Respectvol en ingetogen</li>
          </ul>

          <?php
            $condoleer_url = get_post_meta(get_the_ID(), 'condoleer_url', true);
            if (!$condoleer_url) $condoleer_url = '#';
          ?>
          <div class="ob-panel__actions">
<a class="ob-cta ob-cta--secondary" href="#ob-condoleer-form">Condoleren</a>
          </div>
        </div>

      </div>
    </section>

    <section id="ob-condoleer-form" class="ob-condoleer" aria-label="Condoleer formulier">
  <h2 class="ob-condoleer__title">Condoleer de nabestaanden</h2>
  <p class="ob-condoleer__subtitle">Laat een bericht achter. Je bericht wordt respectvol behandeld en kan worden gemodereerd.</p>

  <form class="ob-condoleer__form" data-ob-condoleer-form>
    <input type="hidden" name="post_id" value="<?php echo (int) get_the_ID(); ?>">
    <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('ob_condoleer')); ?>">

    <div class="ob-formrow">
      <label>Jouw naam (optioneel)</label>
      <input type="text" name="name" maxlength="120" autocomplete="name">
    </div>

    <div class="ob-formrow">
      <label>Jouw e-mail (optioneel)</label>
      <input type="email" name="email" maxlength="190" autocomplete="email">
    </div>

    <div class="ob-formrow">
      <label>Bericht</label>
      <textarea name="message" rows="5" required maxlength="1500" placeholder="Typ hier je condoleance..."></textarea>
    </div>

    <button type="submit" class="ob-cta ob-cta--primary">Versturen</button>
    <div class="ob-formmsg" data-ob-condoleer-msg aria-live="polite"></div>
  </form>
</section>


  </div><!-- /.listing-container -->

  <aside class="sidebar">
    <div class="recent-listings">
      <h2>Recente Berichten</h2>
    </div>
  </aside>

</div><!-- /.single-listing -->

<?php get_footer(); ?>


<style>
.single-listing{
  width: 1200px; 
  display: flex; 
  margin: 24px auto; 
  gap: 16px; 
  justify-content:space-between;
 
}


.listing-container {
  width: 67%;  
  
}

.ob-single__card {
box-sizing:border-box;
  border:1px solid #DEDEDE;
  box-shadow:0px 10px 40px -5px rgba(0,0,0,0.15);
 padding: 24px; 
 margin-bottom: 12px; 
 background: White; 

}


    .funeral-services {
        box-sizing:border-box;
  border:1px solid #DEDEDE;
  box-shadow:0px 10px 40px -5px rgba(0,0,0,0.15);
  background: white; 
  padding: 24px; 
  margin-bottom: 12px; 
    }

    .condoleren {
       box-sizing:border-box;
  border:1px solid #DEDEDE;
  box-shadow:0px 10px 40px -5px rgba(0,0,0,0.15);
  background: white; 
  padding: 24px; 
    }

.sidebar{
  width: 33%;
  border:1px solid #DEDEDE;
  box-shadow:0px 10px 40px -5px rgba(0,0,0,0.15);
  background: white; 
  padding: 24px; 
}





</style>

<style>
  /* =========================================================
   SINGLE OVERLIJDENSBERICHT — COMPLETE STYLING
   (plak dit in je plugin CSS of in je single template)
   ========================================================= */

/* Layout */
.single-listing{
  max-width:1200px;
  margin:24px auto;
  display:flex;
  gap:16px;
  justify-content:space-between;
  align-items:flex-start;
  padding:0 12px;
  box-sizing:border-box;
}

.listing-container{
  width:67%;
  box-sizing:border-box;
}

.sidebar{
  width:33%;
  box-sizing:border-box;
  border:1px solid #DEDEDE;
  box-shadow:0px 10px 40px -5px rgba(0,0,0,0.15);
  background:#fff;
  padding:24px;
}

/* Base typography */
.ob-single{
  font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  color:#222;
}

.ob-single a{
  color:inherit;
}

/* ---------------------------------------------------------
   HERO BALK (foto + datum + CTA)
--------------------------------------------------------- */
.ob-hero{
  display:grid;
  grid-template-columns: 280px 1fr;
 
  
  background:#fff;
  margin:0 0 12px;
  padding:16px;
  box-sizing:border-box;
}

.ob-hero__media{
  border:1px solid #E3E3E3;
  background:#fff;
  overflow:hidden;
}

.ob-hero__media img{
  width:100%;
  height:100%;
  max-height:220px;
  object-fit:cover;
  display:block;
}

.ob-hero__placeholder{
  width:100%;
  height:220px;
  background:linear-gradient(180deg, #f6f6f6, #ededed);
}

.ob-hero__meta{
  display:flex;
  flex-direction:column;
  justify-content:center;
  gap:8px;
}

.ob-hero__date{
  font-size:13px;
  color:#555;
}

.ob-hero__life{
  font-size:14px;
  color:#222;
  line-height:1.5;
}

.ob-hero__life strong{
  font-weight:700;
}

.ob-hero__cta{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  padding:12px 14px;
  border:1px solid #BFBFBF;
  background:#fff;
  color:#111;
  text-decoration:none;
  font-weight:700;
  font-size:14px;
  transition:background .15s ease, border-color .15s ease;
}

.ob-hero__cta:hover{
  background:#F5F5F5;
  border-color:#9F9F9F;
}

/* ---------------------------------------------------------
   MAIN CARD (bericht)
--------------------------------------------------------- */
.ob-single__card{
  box-sizing:border-box;
  border:1px solid #DEDEDE;
  box-shadow:0px 10px 40px -5px rgba(0,0,0,0.15);
  padding:24px;
  margin-bottom:12px;
  background:#fff;
}

.ob-single__title{
  margin:0 0 12px;
  font-family: Georgia, "Times New Roman", Times, serif; /* krantgevoel */
  font-size:34px;
  line-height:1.12;
  font-weight:800;
  color:#111;
}

.ob-single__media{
  margin:10px 0 16px;
}

.ob-single__media img{
  width:100%;
  height:auto;
  display:block;
  border:1px solid #E3E3E3;
}

.ob-single__content{
  font-family: Georgia, "Times New Roman", Times, serif;
  font-size:18px;
  line-height:1.75;
  color:#222;
}

.ob-single__content p{
  margin:0 0 14px;
}

.ob-single__content h2,
.ob-single__content h3{
  font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  color:#111;
  margin:24px 0 10px;
  line-height:1.25;
}

.ob-single__content ul,
.ob-single__content ol{
  margin:0 0 14px 18px;
}

/* ---------------------------------------------------------
   INFO CARD (Contact & condoleren)
--------------------------------------------------------- */
.ob-info-card{
  box-sizing:border-box;
  border:1px solid #DEDEDE;
  box-shadow:0px 10px 40px -5px rgba(0,0,0,0.15);
  background:#fff;
  padding:24px;
  margin-bottom:12px;
}

.ob-info-card__header{
  margin-bottom:16px;
  padding-bottom:14px;
  border-bottom:1px solid #EFEFEF;
}

.ob-info-card__title{
  margin:0;
  font-size:18px;
  font-weight:800;
  color:#222;
}

.ob-info-card__subtitle{
  margin:6px 0 0;
  font-size:14px;
  color:#555;
  line-height:1.5;
}

.ob-info-card__grid{
  display:grid;
  grid-template-columns: 1fr 1fr;
  gap:16px;
}

/* panel blocks */
.ob-panel{
  border:1px solid #E6E6E6;
  background:#FAFAFA;
  padding:16px;
}

.ob-panel__title{
  margin:0 0 10px;
  font-size:16px;
  font-weight:800;
  color:#222;
}

.ob-panel__text{
  margin:0 0 10px;
  font-size:14px;
  color:#333;
  line-height:1.6;
}

/* Key-value rows */
.ob-kv{
  display:flex;
  flex-direction:column;
  gap:10px;
  margin:12px 0 14px;
}

.ob-kv__row{
  display:flex;
  justify-content:space-between;
  gap:12px;
  padding-bottom:10px;
  border-bottom:1px dashed #DCDCDC;
}

.ob-kv__label{
  font-size:13px;
  color:#666;
  min-width:90px;
}

.ob-kv__value{
  font-size:13px;
  color:#111;
  text-align:right;
  word-break:break-word;
}

/* bullets */
.ob-bullets{
  margin:10px 0 14px 18px;
  padding:0;
  color:#333;
  font-size:14px;
  line-height:1.6;
}

.ob-panel__actions{
  margin-top:8px;
  display:flex;
  gap:10px;
  flex-wrap:wrap;
}

/* CTA buttons (rustig) */
.ob-cta{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  padding:10px 14px;
  font-size:14px;
  font-weight:800;
  text-decoration:none;
  border:1px solid #BFBFBF;
  background:#fff;
  color:#111;
  transition: background .15s ease, border-color .15s ease;
}

.ob-cta:hover{
  background:#F5F5F5;
  border-color:#9F9F9F;
}

.ob-cta--primary{
  border-color:#333;
}

.ob-cta--secondary{
  border-color:#BFBFBF;
}

/* ---------------------------------------------------------
   CONDOLEER FORM
--------------------------------------------------------- */
.ob-condoleer{
  box-sizing:border-box;
  border:1px solid #DEDEDE;
  box-shadow:0px 10px 40px -5px rgba(0,0,0,0.15);
  background:#fff;
  padding:24px;
  margin-bottom:12px;
}

.ob-condoleer__title{
  margin:0 0 6px;
  font-size:18px;
  font-weight:800;
  color:#222;
}

.ob-condoleer__subtitle{
  margin:0 0 14px;
  font-size:14px;
  color:#555;
  line-height:1.5;
}

.ob-condoleer__form{
  display:block;
}

.ob-formrow{
  display:flex;
  flex-direction:column;
  margin:0 0 12px;
}

.ob-formrow label{
  font-weight:700;
  font-size:13px;
  margin:0 0 6px;
  color:#222;
}

.ob-formrow input,
.ob-formrow textarea{
  padding:12px 14px;
  border:2px solid #D7DEE7;
  border-radius:5px;
  font-size:14px;
  background:#fff;
  transition: box-shadow .15s ease, border-color .15s ease;
}

.ob-formrow input:focus,
.ob-formrow textarea:focus{
  outline:none;
  border-color:#AFC0D5;
  box-shadow:0 0 0 4px rgba(25,118,210,0.08);
}

.ob-formrow textarea{
  resize:vertical;
  min-height:120px;
}

.ob-formmsg{
  margin-top:10px;
  font-weight:700;
  color:#333;
}

/* ---------------------------------------------------------
   SIDEBAR
--------------------------------------------------------- */
.sidebar h2{
  margin:0 0 10px;
  font-size:18px;
  font-weight:800;
  color:#222;
}

.recent-listings{
  border-top:1px solid #EFEFEF;
  padding-top:12px;
}

/* ---------------------------------------------------------
   RESPONSIVE
--------------------------------------------------------- */
@media (max-width: 980px){
  .single-listing{
    flex-direction:column;
  }
  .listing-container,
  .sidebar{
    width:100%;
  }
  .ob-info-card__grid{
    grid-template-columns:1fr;
  }
  .ob-hero{
    grid-template-columns:1fr;
  }
  .ob-single__title{
    font-size:28px;
  }
}

/* ---------------------------------------------------------
   OPTIONAL: nicer separation between sections
--------------------------------------------------------- */
.ob-single__card,
.ob-info-card,
.ob-condoleer,
.sidebar,
.ob-hero{
  border-radius:5px;
}
 
</style>