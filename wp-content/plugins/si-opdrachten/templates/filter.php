<?php if (!defined('ABSPATH')) exit;

// fallback als deze variabelen niet zijn meegegeven vanuit shortcode/template
$search         = isset($search) ? $search : '';
$cats_selected  = isset($cats_selected) && is_array($cats_selected) ? $cats_selected : [];
$types_selected = isset($types_selected) && is_array($types_selected) ? $types_selected : [];

// Terms ophalen
$categorieen = get_terms([
  'taxonomy'   => 'si_opdracht_categorie',
  'hide_empty' => false,
]);

$types = get_terms([
  'taxonomy'   => 'si_opdracht_type',
  'hide_empty' => false,
]);
?>



<!-- SUMMARY BLOK (boven de filter) -->
<div class="summary-website">
  <div class="summary-website-extra">

  <div class="summary-left">
    <p class="summary-text">
      Hét platform voor het vinden van een student, flexwerker of freelancer!
    </p>
  </div>
  <div class="summary-right">
    <a class="si-filter-header-btn" href="/freelance-platform/">
      Opties bekijken
    </a>
  </div>
</div>
  </div>



<!-- FILTER BLOK -->
<div class="si-opdrachten-filter-wrap">

<div class="filter-header-extra">

  <div class="si-filter-header">
    <div class="si-filter-header-top">
      <div class="si-filter-header-text">
        <h1>Doorzoek alle opdrachten</h1>
        <p>
          Of <a href="/updates-ontvangen/" class="si-filter-header-link">schrijf je in</a> en ontvang de laatste opdrachten!
        </p>
      </div>
    </div>
  </div>

  <form class="si-opdrachten-filter" method="get">

    <div class="si-filter-top-row">

      <!-- Zoek -->
      <div class="si-filter-item si-filter-search">
        <label for="si_search">Zoek</label>
        <input
          id="si_search"
          name="si_search"
          type="text"
          placeholder="Zoek op titel, trefwoord..."
          value="<?php echo esc_attr($search); ?>"
        >
      </div>

      <!-- MULTI 1: Categorie -->
      <div class="si-filter-item si-filter-categorie">
        <label>Categorie</label>

        <div class="si-multi" data-name="si_categorie[]">
          <button type="button" class="si-multi-btn" aria-expanded="false">
            <span class="si-multi-placeholder">Selecteer categorie</span>
            <span class="si-multi-tags" aria-hidden="true"></span>
            <span class="si-multi-caret">▾</span>
          </button>

          <div class="si-multi-panel" role="listbox">
            <div class="si-multi-options">
              <?php if (!is_wp_error($categorieen) && !empty($categorieen)) : ?>
                <?php foreach ($categorieen as $cat): ?>
                  <label class="si-multi-option">
                    <input
                      type="checkbox"
                      value="<?php echo esc_attr($cat->slug); ?>"
                      <?php checked(in_array($cat->slug, $cats_selected, true)); ?>
                    >
                    <span><?php echo esc_html($cat->name); ?></span>
                  </label>
                <?php endforeach; ?>
              <?php else : ?>
                <div class="si-multi-empty">Nog geen categorieën.</div>
              <?php endif; ?>
            </div>
          </div>

          <div class="si-multi-hidden"></div>
        </div>
      </div>

      <!-- MULTI 2: Type opdracht -->
      <div class="si-filter-item si-filter-type">
        <label>Type opdracht</label>

        <div class="si-multi" data-name="si_type[]">
          <button type="button" class="si-multi-btn" aria-expanded="false">
            <span class="si-multi-placeholder">Selecteer type</span>
            <span class="si-multi-tags" aria-hidden="true"></span>
            <span class="si-multi-caret">▾</span>
          </button>

          <div class="si-multi-panel" role="listbox">
            <div class="si-multi-options">
              <?php if (!is_wp_error($types) && !empty($types)) : ?>
                <?php foreach ($types as $t): ?>
                  <label class="si-multi-option">
                    <input
                      type="checkbox"
                      value="<?php echo esc_attr($t->slug); ?>"
                      <?php checked(in_array($t->slug, $types_selected, true)); ?>
                    >
                    <span><?php echo esc_html($t->name); ?></span>
                  </label>
                <?php endforeach; ?>
              <?php else : ?>
                <div class="si-multi-empty">Nog geen types.</div>
              <?php endif; ?>
            </div>
          </div>

          <div class="si-multi-hidden"></div>
        </div>
      </div>

    </div>

    <div class="si-filter-reset-row">
      <a class="si-reset-link" href="<?php echo esc_url(get_permalink()); ?>">Reset</a>
    </div>

  </form>
  </div>

</div>


<style>
/* ==============================
   SUMMARY BLOK
   ============================== */
.summary-website{
  max-width: 1050px;
  margin: 20px auto;
  padding: 24px;
  background: #FBFAF8;
  border: 1px solid #E0E0E0;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.10);
  border-radius: 5px;
  
  
}

  .summary-website-extra {
      border: 1px solid #DEDEDE; 
      background-color: white;
      padding: 24px; 
      width: 100%; 
      border-radius: 5px;
      
      display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  box-sizing: border-box;
    }



.summary-left,
.summary-right{
  display: flex;
  align-items: center;
}

.summary-left{
  flex: 1 1 auto;
  min-width: 0;
}

.summary-text{
  font-family: Balgin Bold;
  font-size: 18px;
  font-weight: 700;
  color: #333;
  margin: 0;
  line-height: 1.3;
}

/* CTA knop (paars) */
.si-filter-header-btn{
  flex: 0 0 auto;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px; 
  border-radius: 5px;
  background: rgba(124, 92, 250, 0.12);
  color: #7C5CFA !important;
  border: 1px solid #7C5CFA;
  text-decoration: none !important;
  font-family: Balgin Bold; 
  font-size: 14px;
  line-height: 1;
  box-shadow: 0 10px 24px rgba(0,0,0,0.14);
  transition: transform .15s ease, filter .15s ease, box-shadow .15s ease;
}

.si-filter-header-btn:hover{
  transform: translateY(-1px);
  filter: brightness(0.98);
  box-shadow: 0 12px 28px rgba(124, 92, 250, 0.18);
}

@media (max-width: 768px){

.summary-website {
  width: 95%; 
}


  .summary-website-extra{
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
    padding: 14px;
    
  }

  .summary-left,
  .summary-right{
    width: 100%;
  }

  .summary-text{
    font-size: 15px;
    line-height: 1.5;
  }

  .summary-right{
    display: flex;
    justify-content: flex-start;
  }

  .si-filter-header-btn{
    width: 100%;
    text-align: center;
    padding: 10px 14px;
    font-size: 15px;
  }
}


/* ==============================
   FILTER WRAP
   ============================== */
.si-opdrachten-filter-wrap{
  max-width: 1050px;
  margin: 20px auto;
  padding: 24px;
  background: #FBFAF8;
  border: 1px solid #E0E0E0;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.10);
  border-radius: 5px;
  box-sizing: border-box;
}


.filter-header-extra {
    background: white; 
    border-radius: 5px; 
    border: 1px solid #DEDEDE; 
    padding: 24px; 
}

/* Header */
.si-filter-header h1{
  font-family: Balgin Bold;
  font-size: 28px;
  line-height: 1.2;
  margin: 0 0 10px 0;
  color: #333 !important;
  font-weight: 700;
}

.si-filter-header p{
  font-family: 'Poppins', sans-serif;
  font-size: 15px;
  margin: 0 0 18px 0;
  color: #333;
}

.si-filter-header-link{
  color: #0456ABFA !important;
  text-decoration: none !important;
  font-weight: 400;
}

/* Layout: 50/25/25 */
.si-filter-top-row{
  display: flex;
  gap: 20px;
  align-items: flex-end;
}

/* Labels */
.si-filter-item label{
  display: block;
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  margin-bottom: 6px;
  color: #333;
}

/* widths */
.si-filter-search{
  flex: 0 0 calc(50% - 10px);
  min-width: 320px;
}
.si-filter-categorie,
.si-filter-type{
  flex: 1 1 0;
  min-width: 220px;
}

/* Text input */
.si-filter-item input[type="text"]{
  width: 100%;
  padding: 12px 14px;
  font-size: 16px;
  border: 1px solid #E0E0E0;
  border-radius: 6px;
  background: #fff;
  color: #333;
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
  font-family: 'Poppins', sans-serif;
  box-sizing: border-box;
}

.si-filter-item input:focus{
  outline: none;
  border-color: #7C5CFA;
  box-shadow: 0 2px 8px rgba(124, 92, 250, 0.25);
}

/* ==============================
   Custom multi-select
   ============================== */
.si-multi{
  position: relative;
  width: 100%;
}

.si-multi-btn{
  width: 100%;
  padding: 12px 14px;
  font-size: 16px;
  border: 1px solid #E0E0E0;
  border-radius: 6px;
  background: #fff;
  color: #333;
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
  font-family: 'Poppins', sans-serif;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  box-sizing: border-box;
}

button.si-multi-btn:hover {
  background: white; 
  color: #333; 
}

button.si-multi-btn:active {
   background: white; 
  color: #333; 
}


button.si-multi-btn:focus-visible {
    background: white; 
  color: #333; 
}

button.si-multi-btn[aria-expanded="true"]{
  background: #fff !important;
  color: #333 !important;
  border-color: #DEDEDE !important;
}



.si-multi-placeholder{
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.si-multi-tags{ display:none; }
.si-multi-caret{ opacity: .7; }

.si-multi-panel{
  display: none;
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  right: 0;
  background: #fff;
  border: 1px solid #E0E0E0;
  border-radius: 10px;
  box-shadow: 0 14px 40px rgba(0,0,0,0.12);
  z-index: 9999;
  padding: 10px;
}

.si-multi.is-open .si-multi-panel{ display:block; }

.si-multi-options{
  max-height: 240px;
  overflow: auto;
  padding-right: 6px;
}

.si-multi-option{
  display: flex;
  align-items: center;
  padding: 10px 10px;
  border-radius: 8px;
  cursor: pointer;
  user-select: none;
  font-family: 'Poppins', sans-serif;
  position: relative;
}

.si-multi-option:hover{ background: #f6f7f9; }

/* hide checkboxes but keep functionality */
.si-multi-option input[type="checkbox"]{
  position: absolute;
  opacity: 0;
  pointer-events: none;
  width: 1px;
  height: 1px;
}

.si-multi-option input[type="checkbox"]:checked + span{
  font-weight: 600;
}

.si-multi-option:has(input[type="checkbox"]:checked){
  background: rgba(124, 92, 250, 0.10);
  border: 1px solid rgba(124, 92, 250, 0.25);
}

.si-multi-option:after{
  content: "";
  width: 10px;
  height: 10px;
  border-radius: 999px;
  border: 0;
  margin-left: auto;
}

.si-multi-option:has(input[type="checkbox"]:checked):after{
  background: #7C5CFA;
}

.si-multi-empty{
  font-family: 'Poppins', sans-serif;
  font-size: 14px;
  padding: 8px 4px;
  color: #666;
}

/* Reset */
.si-filter-reset-row{ margin-top: 16px; }
.si-reset-link{
  font-family: 'Poppins', sans-serif;
  color: #333 !important;
  text-decoration: none !important;
  font-weight: 700;
}

/* Responsive filter */
@media (max-width: 900px){
  .si-filter-top-row{
    flex-direction: column;
    align-items: stretch;
  }
  .si-filter-search,
  .si-filter-categorie,
  .si-filter-type{
    width: 100%;
    min-width: 0;
    flex: 1 1 auto;
  }
}

@media (max-width: 600px){
  .si-opdrachten-filter-wrap{
    padding: 16px;
    width: 95%; 
  }
}
</style>


<script>
(function(){
  function closeAll(except){
    document.querySelectorAll('.si-multi.is-open').forEach(el=>{
      if(except && el === except) return;
      el.classList.remove('is-open');
      const btn = el.querySelector('.si-multi-btn');
      if(btn) btn.setAttribute('aria-expanded','false');
    });
  }

  function updateHiddenInputs(multi){
    const name = multi.getAttribute('data-name'); // e.g. si_categorie[]
    const hiddenWrap = multi.querySelector('.si-multi-hidden');
    hiddenWrap.innerHTML = '';

    const checked = Array.from(multi.querySelectorAll('input[type="checkbox"]:checked'));
    checked.forEach(cb=>{
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      input.value = cb.value;
      hiddenWrap.appendChild(input);
    });

    // Placeholder "x geselecteerd"
    const placeholderEl = multi.querySelector('.si-multi-placeholder');
    if(!placeholderEl) return;

    if(checked.length === 0){
      placeholderEl.textContent = placeholderEl.getAttribute('data-default') || placeholderEl.textContent;
      return;
    }

    placeholderEl.textContent = checked.length === 1 ? '1 geselecteerd' : checked.length + ' geselecteerd';
  }

  function submitFormFrom(el){
    const form = el.closest('form');
    if(form) form.submit();
  }

  // Init
  document.querySelectorAll('.si-multi').forEach(multi=>{
    const placeholderEl = multi.querySelector('.si-multi-placeholder');
    if(placeholderEl){
      placeholderEl.setAttribute('data-default', placeholderEl.textContent.trim());
    }

    const btn = multi.querySelector('.si-multi-btn');
    btn.addEventListener('click', function(e){
      e.preventDefault();
      const isOpen = multi.classList.contains('is-open');
      closeAll(multi);
      multi.classList.toggle('is-open', !isOpen);
      btn.setAttribute('aria-expanded', (!isOpen) ? 'true' : 'false');
    });

    multi.querySelectorAll('input[type="checkbox"]').forEach(cb=>{
      cb.addEventListener('change', function(){
        updateHiddenInputs(multi);
        submitFormFrom(multi);
      });
    });

    updateHiddenInputs(multi);
  });

  // Live search debounce
  const searchInput = document.querySelector('#si_search');
  if(searchInput){
    let t;
    searchInput.addEventListener('input', function(){
      clearTimeout(t);
      t = setTimeout(function(){
        submitFormFrom(searchInput);
      }, 450);
    });
  }

  // Outside click closes
  document.addEventListener('click', function(e){
    const inside = e.target.closest('.si-multi');
    if(!inside) closeAll(null);
  });

  // Escape closes
  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape') closeAll(null);
  });
})();
</script>
