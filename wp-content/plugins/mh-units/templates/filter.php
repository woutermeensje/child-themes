<?php if (!defined('ABSPATH')) exit;

$types = get_terms([
    'taxonomy' => 'mh_unit_type',
    'hide_empty' => false,
]);

// Voor nu demo-opties voor 2 extra multiselects (later vervangen door echte taxonomie/meta bronnen)
$condities = ['Nieuwstaat', 'Goed', 'Gebruikt', 'Opknapper'];
$afmetingen = ['< 15 m²', '15–30 m²', '30–50 m²', '> 50 m²'];
?>

<div class="mh-units-filter-wrap">

  <div class="mh-filter-header">
    <h1>Doorzoek alle modulaire units</h1>
    <p>Vul hier je eigen tekst in over de units, levering, types, of wat je maar wil.</p>
  </div>

  <form class="mh-units-filter" method="get">

      <!-- 1) Zoekbalk op eigen lijn -->
      <div class="mh-filter-search-row">
          <div class="mh-filter-item mh-filter-search">
              <label for="mh_search">Zoek</label>
              <input id="mh_search" name="mh_search" type="text"
                     placeholder="Zoek op naam, trefwoord..."
                     value="<?php echo esc_attr($search); ?>">
          </div>
      </div>

      <!-- 2) Drie multiselects eronder -->
      <div class="mh-units-filter-row mh-filter-multi-row">

          <!-- MULTI 1: Type unit (op basis van taxonomy terms) -->
          <div class="mh-filter-item">
              <label>Type unit</label>

              <div class="mh-multi" data-name="mh_type[]">
                  <button type="button" class="mh-multi-btn" aria-expanded="false">
                      <span class="mh-multi-placeholder">Selecteer type</span>
                      <span class="mh-multi-tags" aria-hidden="true"></span>
                      <span class="mh-multi-caret">▾</span>
                  </button>

                  <div class="mh-multi-panel" role="listbox">
                      <div class="mh-multi-options">
                          <?php foreach ($types as $t): ?>
                              <label class="mh-multi-option">
                                  <input type="checkbox" value="<?php echo esc_attr($t->slug); ?>">
                                  <span><?php echo esc_html($t->name); ?></span>
                              </label>
                          <?php endforeach; ?>
                      </div>
                  </div>

                  <div class="mh-multi-hidden"></div>
              </div>
          </div>

          <!-- MULTI 2: Conditie (demo) -->
          <div class="mh-filter-item">
              <label>Conditie</label>

              <div class="mh-multi" data-name="mh_conditie[]">
                  <button type="button" class="mh-multi-btn" aria-expanded="false">
                      <span class="mh-multi-placeholder">Selecteer conditie</span>
                      <span class="mh-multi-tags" aria-hidden="true"></span>
                      <span class="mh-multi-caret">▾</span>
                  </button>

                  <div class="mh-multi-panel" role="listbox">
                      <div class="mh-multi-options">
                          <?php foreach ($condities as $c): ?>
                              <label class="mh-multi-option">
                                  <input type="checkbox" value="<?php echo esc_attr($c); ?>">
                                  <span><?php echo esc_html($c); ?></span>
                              </label>
                          <?php endforeach; ?>
                      </div>
                  </div>

                  <div class="mh-multi-hidden"></div>
              </div>
          </div>

          <!-- MULTI 3: Afmeting (demo) -->
          <div class="mh-filter-item">
              <label>Afmeting</label>

              <div class="mh-multi" data-name="mh_afmeting[]">
                  <button type="button" class="mh-multi-btn" aria-expanded="false">
                      <span class="mh-multi-placeholder">Selecteer afmeting</span>
                      <span class="mh-multi-tags" aria-hidden="true"></span>
                      <span class="mh-multi-caret">▾</span>
                  </button>

                  <div class="mh-multi-panel" role="listbox">
                      <div class="mh-multi-options">
                          <?php foreach ($afmetingen as $a): ?>
                              <label class="mh-multi-option">
                                  <input type="checkbox" value="<?php echo esc_attr($a); ?>">
                                  <span><?php echo esc_html($a); ?></span>
                              </label>
                          <?php endforeach; ?>
                      </div>
                  </div>

                  <div class="mh-multi-hidden"></div>
              </div>
          </div>

      </div>

      <!-- ✅ Reset knop onder de filters -->
      <div class="mh-filter-reset-row">
          <a class="reset-link" href="<?php echo esc_url(get_permalink()); ?>">Reset</a>
      </div>

  </form>

</div>

<style>
/* ✅ Container zoals Fondsen blok */
.mh-units-filter-wrap{
  max-width: 1050px;
  margin: 20px auto;
  padding: 24px;
  background: #fff;
  border: 1px solid #E0E0E0;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.10);
}

/* Header */
.mh-filter-header h1{
  font-family: 'Poppins', sans-serif;
  font-size: 28px;
  line-height: 1.2;
  margin: 0 0 10px 0;
  color: #333 !important;
  font-weight: 700;
}

.mh-filter-header p{
  font-family: 'Poppins', sans-serif;
  font-size: 15px;
  margin: 0 0 18px 0;
  color: #333;
}

/* 1) Zoekbalk op eigen lijn */
.mh-filter-search-row{
  display: flex;
  margin-bottom: 18px;
}

.mh-filter-search{
  width: 50%;
  min-width: 320px;
}

/* Rij met multiselects */
.mh-units-filter-row{
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
  align-items: flex-end;

}

/* ✅ Reset knop onder filters */
.mh-filter-reset-row{
  margin-top: 16px;
}

.reset-link {
  font-family: 'Poppins', sans-serif;
  font-size: 15px;
  color: #333 !important;
  text-decoration: none !important;
  font-weight: 700; 
}


/* Labels */
.mh-filter-item label{
  display: block;
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  margin-bottom: 6px;
  color: #333;
}

/* Zoek input */
.mh-filter-item input[type="text"]{
  width: 100%;
  padding: 12px 14px;
  font-size: 16px;
  border: 1px solid #E0E0E0;
  border-radius: 6px;
  background: #fff;
  color: #333;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
  font-family: 'Poppins', sans-serif;
}

/* Focus */
.mh-filter-item input:focus{
  outline: none;
  border-color: #0a6b8d;
  box-shadow: 0 2px 8px rgba(10, 107, 141, 0.25);
}

/* Reset button */
.mh-btn-ghost{
  padding: 12px 16px;
  border-radius: 6px;
  border: 1px solid #E0E0E0;
  background: #fff;
  color: #333;
  font-family: 'Poppins', sans-serif;
  text-decoration: none;
  display: inline-block;
}

.mh-btn-ghost:hover{
  border-color: #bdbdbd;
}

/* ===== Custom multiselect (Fondsen-like) ===== */
.mh-multi{
  position: relative;
  width: 260px;
}

.mh-multi-btn{
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
}

.mh-multi-placeholder{
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.mh-multi-tags{
  display: none; /* jij wilde eerder geen chips; als je later chips wil: display:flex */
}

.mh-multi-caret{
  opacity: .7;
}

.mh-multi-panel{
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

.mh-multi.is-open .mh-multi-panel{
  display: block;
}

.mh-multi-options{
  max-height: 240px;
  overflow: auto;
  padding-right: 6px;
}

.mh-multi-option{
  display: flex;
  gap: 10px;
  align-items: center;
  padding: 8px 8px;
  border-radius: 8px;
  cursor: pointer;
  user-select: none;
  font-family: 'Poppins', sans-serif;
}

.mh-multi-option:hover{
  background: #f6f7f9;
}

.mh-multi-option input{
  width: 16px;
  height: 16px;
}

/* Mobile */
@media (max-width: 900px){
  .mh-filter-search{ width: 100%; }
  .mh-multi{ width: 100%; }
}

@media (max-width: 600px){
  .mh-units-filter-wrap{ padding: 16px; }
}
</style>

<script>
(function(){
  function closeAll(except){
    document.querySelectorAll('.mh-multi.is-open').forEach(el=>{
      if(except && el === except) return;
      el.classList.remove('is-open');
      const btn = el.querySelector('.mh-multi-btn');
      if(btn) btn.setAttribute('aria-expanded','false');
    });
  }

  function updateHiddenInputs(multi){
    const name = multi.getAttribute('data-name'); // e.g. mh_type[]
    const hiddenWrap = multi.querySelector('.mh-multi-hidden');
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
    const placeholderEl = multi.querySelector('.mh-multi-placeholder');
    if(!placeholderEl) return;

    if(checked.length === 0){
      placeholderEl.textContent = placeholderEl.getAttribute('data-default') || placeholderEl.textContent;
      return;
    }

    placeholderEl.textContent = checked.length === 1
      ? '1 geselecteerd'
      : checked.length + ' geselecteerd';
  }

  // Init: sla default placeholder op
  document.querySelectorAll('.mh-multi').forEach(multi=>{
    const placeholderEl = multi.querySelector('.mh-multi-placeholder');
    if(placeholderEl){
      placeholderEl.setAttribute('data-default', placeholderEl.textContent.trim());
    }

    // Open/close
    const btn = multi.querySelector('.mh-multi-btn');
    btn.addEventListener('click', function(e){
      e.preventDefault();
      const isOpen = multi.classList.contains('is-open');
      closeAll(multi);
      multi.classList.toggle('is-open', !isOpen);
      btn.setAttribute('aria-expanded', (!isOpen) ? 'true' : 'false');
    });

    // Change
    multi.querySelectorAll('input[type="checkbox"]').forEach(cb=>{
      cb.addEventListener('change', function(){
        updateHiddenInputs(multi);
      });
    });

    // initial
    updateHiddenInputs(multi);
  });

  // Close on outside click
  document.addEventListener('click', function(e){
    const inside = e.target.closest('.mh-multi');
    if(!inside) closeAll(null);
  });

  // Escape close
  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape') closeAll(null);
  });
})();
</script>
