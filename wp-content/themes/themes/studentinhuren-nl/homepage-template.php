<?php
/**
 * Template Name: Homepage-template
 * Description: Template voor de homepage
 */

get_header(); ?>

<?php
$hero_bg = get_the_post_thumbnail_url(get_the_ID(), 'full');
if (!$hero_bg) {
    // fallback afbeelding als er geen uitgelichte afbeelding is
    $hero_bg = 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=1600&auto=format&fit=crop&q=80';
}
?>



<section class="home-hero" style="background-image: url('<?php echo esc_url($hero_bg); ?>');">

  <div class="hero-overlay"></div>

  <div class="hero-inner">

    <!-- Links: content block -->
    <div class="hero-left">
      <div class="hero-left-inner">

        <h1>Studentinhuren.nl</h1>

        <h3>Freelancers, studenten en young professionals op één plek.</h3>

        <p>
          Plaats je opdracht in minuten en ontvang reacties van gemotiveerde kandidaten.
          Van marketing tot IT en van design tot data.
        </p>

        <div class="hero-actions">
          <a class="btn-primary" href="#">Opdracht plaatsen</a>
        
        </div>

    

      </div>
    </div>

    <!-- Rechts: leeg (ruimte voor later) -->
    <div class="hero-right"></div>

  </div>
</section>

<section class="home-disciplines">

  <!-- CARD 1: FREELANCE ONLINE MARKETING -->
  <div class="disciplines-card" data-disciplines-card>

    <h3 class="disciplines-kicker">Specialisaties</h3>

    <div class="disciplines-list">
      <!-- zichtbaar -->
      <a class="discipline-chip" href="/online-marketeer/">Online Marketing</a>
      <a class="discipline-chip" href="/social-media-specialist/">Social Media</a>
      <a class="discipline-chip" href="/grafisch-vormgever/">Grafisch Vormgever</a>
      <a class="discipline-chip" href="/webdesigner/">Webdesigner</a>
      <a class="discipline-chip" href="/wordpress-specialist/">WordPress</a>
      <a class="discipline-chip" href="/developer/">Developer</a>
      <a class="discipline-chip" href="/seo-specialist/">SEO</a>
      <a class="discipline-chip" href="/data-analist/">Data</a>
      <a class="discipline-chip" href="/recruitment-marketeer/">Recruitment Marketing</a>
      <a class="discipline-chip" href="/recruitment-marketeer/">Fotografie</a>
      <a class="discipline-chip" href="/recruitment-marketeer/">Exact Online</a>

      <!-- verborgen -->
      <span class="disciplines-more is-hidden" data-disciplines-more>
        <a class="discipline-chip" href="/online-marketing-student/">Online marketing student</a>
        <a class="discipline-chip" href="/shopify-developer/">Shopify developer</a>
        <a class="discipline-chip" href="/website-laten-maken/">Website laten maken</a>
        <a class="discipline-chip" href="/ict-student/">ICT student</a>
        <a class="discipline-chip" href="/grafisch-ontwerper/">Grafisch ontwerper</a>
        <a class="discipline-chip" href="/sea/">SEA</a>
        <a class="discipline-chip" href="/emailmarketing/">E-mailmarketing</a>
        <a class="discipline-chip" href="/ai-specialist/">Artificial Intelligence</a>
        <a class="discipline-chip" href="/laravel-developer/">Laravel developer</a>
        <a class="discipline-chip" href="/php-developer/">PHP developer</a>
      </span>
    </div>

    <button class="disciplines-toggle" type="button" data-disciplines-toggle>
      Laad meer categorieën
    </button>

  </div>


  <!-- CARD 2: KLUSJES & UITVOEREND WERK -->
  <div class="disciplines-card" data-disciplines-card>

    <h3 class="disciplines-kicker">Klusjes &  Vakkundig</h3>

    <div class="disciplines-list">
      <!-- zichtbaar -->
      <a class="discipline-chip chip-orange" href="/verhuizers/">Verhuizers</a>
      <a class="discipline-chip chip-orange" href="/montage/">Montage</a>
      <a class="discipline-chip chip-orange" href="/sjouwers/">Sjouwers</a>
      <a class="discipline-chip chip-orange" href="/transport/">Transport</a>
      <a class="discipline-chip chip-orange" href="/timmerwerk/">Timmerwerk</a>
      <a class="discipline-chip chip-orange" href="/cable-management/">Cable management</a>
      <a class="discipline-chip chip-orange" href="/ict-verhuizing/">ICT verhuizing</a>
      <a class="discipline-chip chip-orange" href="/werkplek-montage/">Werkplek-montage</a>
      <a class="discipline-chip chip-orange" href="/handyman/">Handyman</a>

      <!-- verborgen -->
      <span class="disciplines-more is-hidden" data-disciplines-more>
        <a class="discipline-chip chip-orange" href="/floorwalkers/">Floorwalkers</a>
        <a class="discipline-chip chip-orange" href="/ontruimingen/">Ontruimingen</a>
        <a class="discipline-chip chip-orange" href="/woning-ontruimen/">Woning ontruimen</a>
        <a class="discipline-chip chip-orange" href="/bijrijders/">Bijrijders</a>
        <a class="discipline-chip chip-orange" href="/verhuisteam/">Verhuisteam</a>
        <a class="discipline-chip chip-orange" href="/bureaukabels-wegwerken/">Bureaukabels wegwerken</a>
        <a class="discipline-chip chip-orange" href="/ict-support/">ICT support</a>
        <a class="discipline-chip chip-orange" href="/administratief/">Administratief</a>
        <a class="discipline-chip chip-orange" href="/koeriers/">Koeriers</a>
        <a class="discipline-chip chip-orange" href="/hardware-migratie/">Hardware migratie</a>
      </span>
    </div>

    <button class="disciplines-toggle" type="button" data-disciplines-toggle>
      Laad meer categorieën
    </button>

  </div>

</section>



<section class="home-shortcode">
  <div class="home-shortcode-inner">

    <?php
      // Vervang de shortcode hieronder door jouw eigen shortcode
      echo do_shortcode('[si_latest_opdrachten]');
    ?>

  </div>
</section>




<!-- SECTION 2: 500px split block -->
<section class="home-split">
  <div class="split-inner">

    <!-- Links: paars vlak + pill -->
    <div class="split-left" style="background-image: url('<?php echo esc_url($hero_bg); ?>');">
    </div>

    <!-- Rechts: tekst -->
    <div class="split-right">
      <h2>Waarom Studentinhuren.nl?</h2>

      <ul class="split-benefits">
        <li>Snel opdracht plaatsen en direct zichtbaar</li>
        <li>Bereik studenten, freelancers en young professionals</li>
        <li>Filters op type, categorie en locatie</li>
        <li>Heldere communicatie en snelle reacties</li>
        <li>Professionele uitstraling voor jouw merk</li>
      </ul>

      <a class="btn-primary" href="#">Plaats een opdracht</a>
    </div>

  </div>
</section>



<style>

    .home-shortcode {
        border: 1px solid #DEDEDE; 
        box-shadow: 5px 10px 40px -5px rgba(0,0,0,0.15);
        background-color: #FBFAF8;
        padding: 24px; 
        width: 1200px;
        margin: 48px auto; 
        border-radius: 5px; 
        padding: 24px; 

    }

    .home-shortcode-inner {
        border: 1px solid #DEDEDE; 
        border-radius: 5px; 
        background: white; 
    }

</style>





<?php get_footer(); ?>

<style>
/* ==============================
   Variables
   ============================== */
:root{
  --purple:#7C5CFA;
  --bg:#FBFAF8;
  --border:#DEDEDE;
  --radius:5px;
  --btn-pad:4px;
  --text:#111;
}

/* ==============================
   Base
   ============================== */
body{
  margin: 0;
  background: var(--bg);
  color: var(--text);
  font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI",
               Roboto, Helvetica, Arial, sans-serif;
}

/* ==============================
   HERO SECTION
   ============================== */
.home-hero{
  position: relative;
  width: 100%;
  height: 400px;
  padding: 20px;

  background-size: cover;
  background-position: middle middle;
}




/* Overlay */
.hero-overlay{
  position: absolute;
  inset: 0;
  background: rgba(124, 92, 250, 0.7); /* 70% overlay */
  z-index: 1;
}

/* Inner container */
.hero-inner{
  position: relative;
  z-index: 2;
  max-width: 1050px;
  height: 100%;
  margin: 0 auto;

  display: flex;
  align-items: center;     /* verticaal centreren */
  justify-content: center; /* horizontaal centreren */
  gap: 24px;

  
}


/* 50 / 50 columns */
.hero-left,
.hero-right{
  width: 50%;
  min-width: 0;
}

/* ==============================
   LEFT CONTENT BLOCK
   ============================== */
.hero-left{
  display: flex;
  align-items: center;
  padding: 24px; 
  background-color: #FBFAF8;
  border-radius: 5px; 
  
}

.hero-left-inner{
  padding: 24px;
  background: #fff;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 24px;
  max-width: 520px;
  gap: 14px; 
}


/* Typography */
.hero-left h1{
  margin: 0 0 8px;
  font-size: 32px;
  line-height: 1.1;
  letter-spacing: -0.4px;
  color: #333;
}

.hero-left h3{
  margin: 0 0 12px;
  font-size: 16px;
  font-weight: 700;
  color: #333;
}

.hero-left p{
  margin: 0 0 16px;
  font-size: 16px;
  line-height: 1.6;
  opacity: 0.85;
}

/* ==============================
   ACTIONS / BUTTONS
   ============================== */
.hero-actions{
  display: flex;
  gap: 10px;
  margin-bottom: 10px;
}

.btn-primary,
.btn-ghost{
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px;  
  height: 38px;
  border-radius: var(--radius);
  font-weight: 600;
  font-size: 14px;
  text-decoration: none;
  cursor: pointer;
  font-family: Balgin Bold; 
}

.btn-primary{
  background: var(--purple);
  color: #fff;
}

.btn-primary:hover{
  opacity: 0.9;
}

.btn-ghost{
  background: #7C5CFA45;
  color: var(--text);
  border: 1px solid var(--purple);
  color: var(--purple) !important;
}

.btn-ghost:hover{
  background: rgba(0,0,0,0.04);
}

/* ==============================
   TRUST INDICATOR
   ============================== */
.hero-trust{
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
  opacity: 0.75;
  color: #333; 
}

.hero-trust .dot{
  width: 10px;
  height: 10px;
  border-radius: 999px;
  background: var(--purple);
}

/* ==============================
   RIGHT SIDE (EMPTY FOR NOW)
   ============================== */
.hero-right{
  /* bewust leeg */
}

/* ==============================
   RESPONSIVE
   ============================== */
@media (max-width: 900px){
  .home-hero{
    height: auto;
    padding: 16px;
  }

  .hero-inner{
    flex-direction: column;
    justify-content: center;
  }

  .hero-left,
  .hero-right{
    width: 100%;
  }

  .hero-left-inner{
    max-width: 100%;
  }

  .hero-left h1{
    font-size: 28px;
  }
}

</style>


<style>
/* ==============================
   FULL WIDTH SPLIT SECTION
   ============================== */
.home-split{
   width: 1200px;                     /* volledige schermbreedte */
  margin-left: calc(50% - 50vw);    /* breekt uit container links */
  margin-right: calc(50% - 50vw);   /* breekt uit container rechts */   
  

  height: 525px;
  margin: 24px auto;
  border-radius: 5px; 
  border: 1px solid #DEDEDE; 
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  padding: 24px; 

       background-color: var(--bg) !important; 


}

/* Inner wordt ook full-width */
.split-inner{
  width: 100%;
  height: 100%;
  display: flex;
 
}


.split-right{
  width: 50%;
  min-width: 0;
  border: 1px solid #DEDEDE; 
  border-radius: 5px; 
  margin-left: 8px; 
  background-color: white !important; 
}

/* 50/50 verdeling */
.split-left {

 width: 50%;
  min-width: 0;
  border: 1px solid #DEDEDE; 
  border-radius: 5px; 
  margin-left: 8px; 

}


/* Zorg dat padding/width logisch blijven */
*, *::before, *::after { box-sizing: border-box; }

.split-left{
  display: flex;
  align-items: center;
  justify-content: flex-end;
  border-radius: 5px; 
  border: 1px solid #DEDEDE; 
   position: relative;
  background-size: cover;        /* of contain, zie uitleg */
  background-position: center;
  background-repeat: no-repeat;

  margin-right: 4px; 


}



/* Paarse overlay */
.split-left::before{
  content: "";
  position: absolute;
  inset: 0;
  background: rgba(124, 92, 250, 0.7); /* #7C5CFA met 70% */
  z-index: 1;
  border-radius: 5px; 
}

/* Alles wat IN split-left zit boven de overlay */
.split-left > *{
  position: relative;
  z-index: 2;
}



/* De pill groter + content netjes geplaatst */
.split-pill{
  width: min(524px, 100%);     /* groter dan 420px, maar nooit buiten kolom */
  max-width: none;

  height: 320px;              /* iets ruimer dan 400 als je wilt */
 

  /* ⭐ belangrijk: content “in” het vlak zetten */
  display: flex;
  align-items: flex-start;     /* of center als je 'm verticaal wil centreren */
  justify-content: flex-start;

  /* ⭐ belangrijk: extra ruimte links vanwege de curve */
 
 
}


.pill-content{
  color: #fff;

}

.pill-kicker{
  font-size: 12px;
  letter-spacing: .6px;
  text-transform: uppercase;
  opacity: .70;
  margin-bottom: 10px;
}

.pill-title{
  font-size: 22px;
  font-weight: 800;
  line-height: 1.15;
  margin-bottom: 10px;
}

.pill-sub{
  font-size: 14px;
  line-height: 1.6;
  opacity: .9;
}

/* RIGHT: text content */
.split-right{
  padding: 34px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 14px;
  background: #FBFAF8;
}

.split-right h2{
  margin: 0;
  font-size: 26px;
  line-height: 1.15;
  color: #333;
}

.split-benefits{
  margin: 0;
  padding-left: 18px;
  display: grid;
  gap: 10px;
  opacity: .88;
}

.split-benefits li{
  line-height: 1.5;
}

/* Re-use your existing .btn-primary */
.split-right .btn-primary{
  width: fit-content;
  margin-top: 6px;
  padding: var(--btn-pad) 14px;
}

/* Responsive */
@media (max-width: 900px){
  .home-split{
    height: auto;
  }

  .split-inner{
    flex-direction: column;
  }

  .split-left,
  .split-right{
    width: 100%;
  }

  .split-left{
    justify-content: center;
  }

  .split-pill{
    width: 100%;
    max-width: 520px;
    border-radius: 999px; /* op mobiel mooier als capsule */
  }
}

</style>


<style>
 /* ==============================
   CARD
   ============================== */
.home-disciplines {
    width: 1200px; 
    background: var(bg); 
   border: 1px solid #DEDEDE;
   border-radius: 5px;
   padding: 24px; 

   display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 24px;

  margin: 48px auto;

    box-shadow: 5px 10px 40px -5px rgba(0,0,0,0.15);

}

.disciplines-card{
  background: #ffffff;
  border: 1px solid #DEDEDE;
  border-radius: 5px;
  padding: 24px;

  display: flex;
  flex-direction: column;
}

/* ==============================
   TITLE
   ============================== */
.disciplines-kicker{
  font-size: 20px;
  font-weight: 700;
  color: #333;
  margin-bottom: 16px;
  font-family: Balgin Bold; 
}

/* ==============================
   LIST
   ============================== */
.disciplines-list{
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

/* ==============================
   CHIPS (default = blauw)
   ============================== */
.discipline-chip{
  display: inline-flex;
  align-items: center;
  justify-content: center;

  padding: 8px 14px;
  border-radius: 5px;

  font-size: 14px;
  font-weight: 700;
  line-height: 1;
  text-decoration: none;

  color: #3A89FF !important;
  border: 1px solid #3A89FF;
  background: rgba(58,137,255,0.14);


}

.discipline-chip:hover{
  
}

/* ==============================
   ORANGE VARIANT
   ============================== */
.chip-orange{
  color: #7C5CFA  !important; 
  background:  #7C5CFA45!important;
  color: #7C5CFA  !important;
  border: 1px solid #7C5CFA !important;  
}


/* ==============================
   HIDDEN EXTRA CATEGORIES
   ============================== */
.disciplines-more{
  display: contents;
}

.disciplines-more.is-hidden{
  display: none;
}

/* ==============================
   LOAD MORE BUTTON
   ============================== */
.disciplines-toggle{
  margin-top: 20px;
  align-self: flex-start;

  border: 1px solid #7C5CFA;
  border-radius: 5px;

  padding: 8px 8px;

  font-size: 14px;
  font-family: Balgin Bold; 
  color: #7C5CFA;
  background: #7C5CFA45; 

  cursor: pointer;

}


/* ==============================
   DISCIPLINES TOGGLE BUTTON FIX
   ============================== */

.disciplines-toggle:hover,
.disciplines-toggle:focus,
.disciplines-toggle:active,
.disciplines-toggle:visited{
   color: #7C5CFA45 !important; 
  background: #7C5CFA !important;
  color: white !important;
}


/* ==============================
   RESPONSIVE
   ============================== */
@media (max-width: 900px){
  .home-disciplines{
    grid-template-columns: 1fr;
    gap: 20px;
    margin: 32px auto;
  }

  .disciplines-card{
    padding: 20px;
  }

  .disciplines-kicker{
    font-size: 18px;
  }
}

</style>


 <script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-disciplines-card]').forEach(function(card){
    const btn = card.querySelector('[data-disciplines-toggle]');
    const more = card.querySelector('[data-disciplines-more]');
    if(!btn || !more) return;

    btn.addEventListener('click', function(){
      const isHidden = more.classList.contains('is-hidden');

      if(isHidden){
        more.classList.remove('is-hidden');
        btn.textContent = 'Toon minder categorieën';
      } else {
        more.classList.add('is-hidden');
        btn.textContent = 'Laad meer categorieën';
      }
    });
  });
});
</script>
