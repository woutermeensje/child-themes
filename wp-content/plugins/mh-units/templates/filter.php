<?php if (!defined('ABSPATH')) exit;

// fallback als deze variabelen niet zijn meegegeven vanuit shortcode
$search = isset($search) ? $search : '';
$types_selected = isset($types_selected) && is_array($types_selected) ? $types_selected : [];
$condities_selected = isset($condities_selected) && is_array($condities_selected) ? $condities_selected : [];

$types = get_terms([
    'taxonomy' => 'mh_unit_type',
    'hide_empty' => false,
]);

$condities = get_terms([
    'taxonomy' => 'mh_unit_conditie',
    'hide_empty' => false,
]);
?>

<div class="mh-units-filter-wrap">

  <div class="mh-filter-header">
    <h1>Doorzoek alle modulaire units</h1>
    <p>Of plaats jouw units in het netwerk van ModulaireHuisvesting.nl</p>
  </div>

  <form class="mh-units-filter" method="get">

      <div class="mh-filter-top-row">

          <!-- Zoek (50%) -->
          <div class="mh-filter-item mh-filter-search">
              <label for="mh_search">Zoek</label>
              <input id="mh_search" name="mh_search" type="text"
                     placeholder="Zoek op naam, trefwoord..."
                     value="<?php echo esc_attr($search); ?>">
          </div>

          <!-- MULTI 1: Type unit -->
          <div class="mh-filter-item mh-filter-type">
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
                                  <input type="checkbox"
                                         value="<?php echo esc_attr($t->slug); ?>"
                                         <?php checked(in_array($t->slug, $types_selected, true)); ?>>
                                  <span><?php echo esc_html($t->name); ?></span>
                              </label>
                          <?php endforeach; ?>
                      </div>
                  </div>

                  <div class="mh-multi-hidden"></div>
              </div>
          </div>

          <!-- MULTI 2: Conditie -->
          <div class="mh-filter-item mh-filter-conditie">
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
                                  <input type="checkbox"
                                         value="<?php echo esc_attr($c->slug); ?>"
                                         <?php checked(in_array($c->slug, $condities_selected, true)); ?>>
                                  <span><?php echo esc_html($c->name); ?></span>
                              </label>
                          <?php endforeach; ?>
                      </div>
                  </div>

                  <div class="mh-multi-hidden"></div>
              </div>
          </div>

      </div>

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

/* ✅ Nieuwe top row: 50% / 25% / 25% */
.mh-filter-top-row{
  display: flex;
  gap: 20px;
  align-items: flex-end;
}

/* Labels */
.mh-filter-item label{
  display: block;
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  margin-bottom: 6px;
  color: #333;
}

/* Kolombreedtes */
.mh-filter-search{
  flex: 0 0 calc(50% - 10px); /* -10px = helft van gap (20px) */
  min-width: 320px;
}

.mh-filter-type,
.mh-filter-conditie{
  flex: 1 1 0;  /* beide nemen gelijk deel van resterende ruimte (25/25) */
  min-width: 220px;
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
  box-sizing: border-box;
}

/* Focus */
.mh-filter-item input:focus{
  outline: none;
  border-color: #0a6b8d;
  box-shadow: 0 2px 8px rgba(10, 107, 141, 0.25);
}

/* ===== Custom multiselect ===== */
.mh-multi{
  position: relative;
  width: 100%;              /* ✅ geen vaste 260px meer */
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
  box-sizing: border-box;
}

.mh-multi-placeholder{
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.mh-multi-tags{ display:none; }
.mh-multi-caret{ opacity:.7; }

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

/* Reset onder filters */
.mh-filter-reset-row{
  margin-top: 16px;
}

.reset-link{
  font-family: 'Poppins', sans-serif;
  color: #333;
  text-decoration: underline;
}

/* ✅ Responsive: stacken op mobiel/tablet */
@media (max-width: 900px){
  .mh-filter-top-row{
    flex-direction: column;
    align-items: stretch;
  }

  .mh-filter-search,
  .mh-filter-type,
  .mh-filter-conditie{
    flex: 1 1 auto;
    width: 100%;
    min-width: 0;
  }
}

@media (max-width: 600px){
  .mh-units-filter-wrap{
    padding: 16px;
  }
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
