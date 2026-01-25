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

        <div class="hero-trust">
          <span class="dot"></span>
          <span>Gemiddeld binnen 24 uur reacties</span>
        </div>

      </div>
    </div>

    <!-- Rechts: leeg (ruimte voor later) -->
    <div class="hero-right"></div>

  </div>
</section>


<section class="home-disciplines">
  <div class="disciplines-inner">

    <span class="disciplines-kicker">
      Het platform voor het vinden van een:
    </span>

    <div class="disciplines-list">
      <span class="discipline-chip">Online marketeer</span>
      <span class="discipline-chip">Social media specialist</span>
      <span class="discipline-chip">Grafisch vormgever</span>
      <span class="discipline-chip">Webdesigner</span>
      <span class="discipline-chip">WordPress specialist</span>
      <span class="discipline-chip">Developer</span>
      <span class="discipline-chip">SEO-specialist</span>
      <span class="discipline-chip">Data-analist</span>
      <span class="discipline-chip"><span>Recruitment marketeer</span>
    </div>

  </div>
</section>

<style>

  
</style>



<section class="home-shortcode">
  <div class="home-shortcode-inner">

    <?php
      // Vervang de shortcode hieronder door jouw eigen shortcode
      echo do_shortcode('[si_latest_opdrachten]');
    ?>

  </div>
</section>

<style>

    .home-shortcode {
        border: 1px solid #DEDEDE; 
        box-shadow: 5px 10px 40px -5px rgba(0,0,0,0.15);
        background-color: #FBFAF8;
        padding: 24px; 
        width: 1200px;
        margin: 24px auto; 
        border-radius: 5px; 
    }
</style>



<!-- SECTION 2: 500px split block -->
<section class="home-split">
  <div class="split-inner">

    <!-- Links: paars vlak + pill -->
    <div class="split-left" >
      <div class="split-pill" style="background-image: url('<?php echo esc_url($hero_bg); ?>');">
        <div class="pill-content">
          <div class="pill-kicker">Voor opdrachtgevers</div>
          <div class="pill-title">Alles geregeld in één platform</div>
          <div class="pill-sub">Plaatsen, beheren en reacties ontvangen — zonder gedoe.</div>
        </div>
      </div>
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
  padding: 4px 4px; 
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
   width: 100vw;                     /* volledige schermbreedte */
  margin-left: calc(50% - 50vw);    /* breekt uit container links */
  margin-right: calc(50% - 50vw);   /* breekt uit container rechts */   
  

  height: 525px;
  margin: 24px auto;
  background-color: var(--bg);
  border-radius: 5px; 
  border: 1px solid #DEDEDE; 
  box-shadow: 0 10px 40px 05px rgba(0,0,0,0.15);

}

/* Inner wordt ook full-width */
.split-inner{
  width: 100%;
  height: 100%;
  display: flex;
}

/* 50/50 verdeling */
.split-left,
.split-right{
  width: 50%;
  min-width: 0;
}


/* Zorg dat padding/width logisch blijven */
*, *::before, *::after { box-sizing: border-box; }

.split-left{
background: #7C5CFA;
  display: flex;
  align-items: center;
  justify-content: flex-end;
}


/* De pill groter + content netjes geplaatst */
.split-pill{
  width: min(524px, 100%);     /* groter dan 420px, maar nooit buiten kolom */
  max-width: none;

  height: 320px;              /* iets ruimer dan 400 als je wilt */
 
  position: relative;
  background-size: cover;        /* of contain, zie uitleg */
  background-position: center;
  background-repeat: no-repeat;

  opacity: 95%; 

  border-top-left-radius: 72px;
  border-bottom-left-radius: 5px;
  border-top-right-radius: 5px;
  border-bottom-right-radius: 72px;

  /* ⭐ belangrijk: content “in” het vlak zetten */
  display: flex;
  align-items: flex-start;     /* of center als je 'm verticaal wil centreren */
  justify-content: flex-start;

  /* ⭐ belangrijk: extra ruimte links vanwege de curve */
  padding: 70px 48px 48px 140px;
  margin-right: 24px; 
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
   DISCIPLINES STRIP (FULL WIDTH)
   ============================== */
.home-disciplines{
  width: 100vw;
  height: 124px;

  margin-left: calc(50% - 50vw);
  margin-right: calc(50% - 50vw);

  background: var(--purple);
  display: flex;
  align-items: center;
}

/* Inner content */
.disciplines-inner{
  max-width: 1050px;
  width: 100%;
  margin: 0 auto;

  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

/* Intro text */
.disciplines-kicker{
  color: #EDE9FF;
  font-size: 14px;
  font-weight: 600;
  white-space: nowrap;
}

/* List of disciplines */
.disciplines-list{
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

  .discipline-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 8px 12px;
    border-radius: 5px;
    border: 1px solid var(--border);
    background: #FBFAF8;
    text-decoration: none;

    color: #7C5CFA !important;
    font-weight: 700;
    font-size: 14px;
    line-height: 1;
    transition: transform .15s ease, background .15s ease, border-color .15s ease;
    }


/* ==============================
   RESPONSIVE
   ============================== */
@media (max-width: 900px){
  .home-disciplines{
    height: auto;
    padding: 14px 16px;
  }

  .disciplines-inner{
    flex-direction: column;
    align-items: flex-start;
  }

  .disciplines-list{
    gap: 8px;
  }
}

</style>