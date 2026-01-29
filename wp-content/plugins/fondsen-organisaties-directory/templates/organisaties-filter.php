<?php if (!defined('ABSPATH')) exit; ?>

<div class="si-opdrachten-filter-wrap org-directory-filter-wrap">
  <div class="filter-header-extra">

    <div class="si-filter-header">
      <div class="si-filter-header-top">
        <div class="si-filter-header-text">
          <h1>Doorzoek Fondsen, Goede Doelen, Stichtingen & Verenigingen</h1>
          <p>Zoek en filter op type organisatie en sector.</p>
        </div>
      </div>
    </div>

    <form id="orgFilterForm" class="si-opdrachten-filter org-directory-filter" method="get">

      <div class="si-filter-top-row">

        <!-- Zoek -->
        <div class="si-filter-item si-filter-search">
          <label for="org_search">Zoek</label>
          <input
            id="org_search"
            name="org_search"
            type="text"
            placeholder="Naam, onderwerp, trefwoord..."
            value="<?php echo esc_attr($search_query); ?>"
            autocomplete="off"
          >
        </div>

        <!-- MULTI 1: Type organisatie -->
        <div class="si-filter-item si-filter-categorie org-filter-type">
          <label>Type organisatie</label>

          <div class="si-multi" data-name="org_type[]">
            <button type="button" class="si-multi-btn" aria-expanded="false">
              <span class="si-multi-placeholder">Selecteer type organisatie</span>
              <span class="si-multi-tags" aria-hidden="true"></span>
              <span class="si-multi-caret">▾</span>
            </button>

            <div class="si-multi-panel" role="listbox">
              <div class="si-multi-options">
                <?php if (!is_wp_error($types) && !empty($types)) : ?>
                  <?php foreach ($types as $term): ?>
                    <label class="si-multi-option">
                      <input
                        type="checkbox"
                        value="<?php echo esc_attr($term->slug); ?>"
                        <?php checked(in_array($term->slug, $selected_types, true)); ?>
                      >
                      <span><?php echo esc_html($term->name); ?></span>
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

        <!-- MULTI 2: Sector -->
        <div class="si-filter-item si-filter-type org-filter-sector">
          <label>Sector</label>

          <div class="si-multi" data-name="org_sector[]">
            <button type="button" class="si-multi-btn" aria-expanded="false">
              <span class="si-multi-placeholder">Selecteer sector</span>
              <span class="si-multi-tags" aria-hidden="true"></span>
              <span class="si-multi-caret">▾</span>
            </button>

            <div class="si-multi-panel" role="listbox">
              <div class="si-multi-options">
                <?php if (!is_wp_error($sectors) && !empty($sectors)) : ?>
                  <?php foreach ($sectors as $term): ?>
                    <label class="si-multi-option">
                      <input
                        type="checkbox"
                        value="<?php echo esc_attr($term->slug); ?>"
                        <?php checked(in_array($term->slug, $selected_sector, true)); ?>
                      >
                      <span><?php echo esc_html($term->name); ?></span>
                    </label>
                  <?php endforeach; ?>
                <?php else : ?>
                  <div class="si-multi-empty">Nog geen sectoren.</div>
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
    const name = multi.getAttribute('data-name'); // org_type[] / org_sector[]
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

    const placeholderEl = multi.querySelector('.si-multi-placeholder');
    if(!placeholderEl) return;

    if(!placeholderEl.getAttribute('data-default')){
      placeholderEl.setAttribute('data-default', placeholderEl.textContent.trim());
    }

    if(checked.length === 0){
      placeholderEl.textContent = placeholderEl.getAttribute('data-default');
      return;
    }

    placeholderEl.textContent = checked.length === 1 ? '1 geselecteerd' : checked.length + ' geselecteerd';
  }

  function submitFormFrom(el){
    const form = el.closest('form');
    if(form) form.submit();
  }

  // Init multis
  document.querySelectorAll('.si-multi').forEach(multi=>{
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

  // Live search debounce (org_search)
  const searchInput = document.querySelector('#org_search');
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



<style>

    :root {
  --fondsen-blue: #0884CC;
  --fondsen-blue-soft: rgba(8, 132, 204, 0.12);
  --fondsen-blue-border: rgba(8, 132, 204, 0.35);
}

    /* ==============================
   FILTER WRAP (SI look)
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

.filter-header-extra{
  background: #fff;
  border-radius: 5px;
  border: 1px solid #DEDEDE;
  padding: 24px;
}

.si-filter-header h1{
  font-family: Balgin Bold, sans-serif;
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

.si-multi-btn[aria-expanded="true"]{
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

/* Responsive */
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