<?php if (!defined('ABSPATH')) exit; ?>
<?php
$filters = $data['filters'];
$themas  = $data['themas'];
$types   = $data['types'];

$selected_thema = $filters['thema'] ?? [];
$selected_type  = $filters['type'] ?? [];
$search_value   = $filters['q'] ?? '';
?>

<div class="fga-wrap">
  <div class="fga-filter-card">

    <div class="fga-head">
      <h1 class="fga-title">Geefacties die jouw hulp kunnen gebruiken</h1>
    </div>

    <form id="fgaFilterForm" class="fga-filter" method="get" action="">
      <div class="fga-row fga-row--top">
        <div class="fga-item fga-item--search">
          <input
            id="fga_q"
            name="fga_q"
            type="text"
            placeholder="Zoek..."
            value="<?php echo esc_attr($search_value); ?>"
            autocomplete="off"
          >
          <span class="fga-search-icon" aria-hidden="true">🔍</span>
        </div>

        <!-- Soort geefactie (multi) -->
        <div class="fga-item">
          <div class="fga-multi" data-name="fga_type[]">
            <button type="button" class="fga-multi-btn" aria-expanded="false">
              <span class="fga-multi-placeholder">Geefacties: Alle soorten</span>
              <span class="fga-multi-caret">▾</span>
            </button>

            <div class="fga-multi-panel">
              <div class="fga-multi-options">
                <?php if (!empty($types)) : ?>
                  <?php foreach ($types as $term): ?>
                    <label class="fga-multi-option">
                      <input
                        type="checkbox"
                        value="<?php echo esc_attr($term->slug); ?>"
                        <?php checked(in_array($term->slug, $selected_type, true)); ?>
                      >
                      <span><?php echo esc_html($term->name); ?></span>
                    </label>
                  <?php endforeach; ?>
                <?php else : ?>
                  <div class="fga-multi-empty">Nog geen soorten.</div>
                <?php endif; ?>
              </div>
            </div>

            <div class="fga-multi-hidden"></div>
          </div>
        </div>
      </div>

      <div class="fga-row fga-row--bottom">
        <!-- Thema (multi) -->
        <div class="fga-item">
          <div class="fga-multi" data-name="fga_thema[]">
            <button type="button" class="fga-multi-btn" aria-expanded="false">
              <span class="fga-multi-placeholder">Thema: Alle thema's</span>
              <span class="fga-multi-caret">▾</span>
            </button>

            <div class="fga-multi-panel">
              <div class="fga-multi-options">
                <?php if (!empty($themas)) : ?>
                  <?php foreach ($themas as $term): ?>
                    <label class="fga-multi-option">
                      <input
                        type="checkbox"
                        value="<?php echo esc_attr($term->slug); ?>"
                        <?php checked(in_array($term->slug, $selected_thema, true)); ?>
                      >
                      <span><?php echo esc_html($term->name); ?></span>
                    </label>
                  <?php endforeach; ?>
                <?php else : ?>
                  <div class="fga-multi-empty">Nog geen thema's.</div>
                <?php endif; ?>
              </div>
            </div>

            <div class="fga-multi-hidden"></div>
          </div>
        </div>

        <div class="fga-item fga-item--reset">
          <a class="fga-reset" href="<?php echo esc_url(get_permalink()); ?>">Reset</a>
        </div>
      </div>

      <!-- hidden inputs worden via JS ingevuld -->
    </form>

  </div>
</div>

<script>
(function(){
  var form = document.getElementById('fgaFilterForm');
  if(!form) return;

  function closeAll(except){
    form.querySelectorAll('.fga-multi.is-open').forEach(function(m){
      if(except && m === except) return;
      m.classList.remove('is-open');
      var btn = m.querySelector('.fga-multi-btn');
      if(btn) btn.setAttribute('aria-expanded','false');
    });
  }

  function updateHidden(multi){
    var name = multi.getAttribute('data-name');
    var wrap = multi.querySelector('.fga-multi-hidden');
    if(!wrap) return;
    wrap.innerHTML = '';

    var checked = Array.from(multi.querySelectorAll('input[type="checkbox"]:checked'));
    checked.forEach(function(cb){
      var input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      input.value = cb.value;
      wrap.appendChild(input);
    });

    var ph = multi.querySelector('.fga-multi-placeholder');
    if(ph){
      if(!ph.dataset.default) ph.dataset.default = ph.textContent.trim();
      if(checked.length === 0){
        ph.textContent = ph.dataset.default;
      } else {
        ph.textContent = (checked.length === 1) ? '1 geselecteerd' : (checked.length + ' geselecteerd');
      }
    }
  }

  function submit(){ form.submit(); }

  // init
  form.querySelectorAll('.fga-multi').forEach(function(multi){
    var btn = multi.querySelector('.fga-multi-btn');
    if(!btn) return;

    btn.addEventListener('click', function(e){
      e.preventDefault();
      var open = multi.classList.contains('is-open');
      closeAll(multi);
      multi.classList.toggle('is-open', !open);
      btn.setAttribute('aria-expanded', (!open) ? 'true' : 'false');
    });

    multi.querySelectorAll('input[type="checkbox"]').forEach(function(cb){
      cb.addEventListener('change', function(){
        updateHidden(multi);
        submit();
      });
    });

    updateHidden(multi);
  });

  // debounce search
  var q = document.getElementById('fga_q');
  if(q){
    var t;
    q.addEventListener('input', function(){
      clearTimeout(t);
      t = setTimeout(submit, 450);
    });
  }

  document.addEventListener('click', function(e){
    var inside = e.target.closest('#fgaFilterForm .fga-multi');
    if(!inside) closeAll(null);
  });

  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape') closeAll(null);
  });
})();
</script>


<style>

    /* ===== Filter (templates/filter.php) ===== */

/* Wrapper */
.fga-wrap { max-width: 1180px; margin: 24px auto; padding: 0 16px; }
.fga-filter-card { background: #fff; border: 1px solid #e6e6e6; border-radius: 5px; box-shadow: 0 10px 30px rgba(0,0,0,.06); padding: 18px; }

.fga-title { margin: 0 0 14px; font-size: 34px; line-height: 1.1; color: #1f2937; }

/* Filter layout */
.fga-filter { width: 100%; }
.fga-row { display: grid; grid-template-columns: 1fr 320px; gap: 14px; margin-top: 12px; }
.fga-row--bottom { grid-template-columns: 320px 1fr; align-items: start; }
.fga-item { position: relative; }

.fga-item--search input {
  width: 100%;
  height: 46px;
  border: 1px solid #d9d9d9;
  border-radius: 5px;
  padding: 0 44px 0 14px;
  font-size: 16px;
  outline: none;
}
.fga-search-icon { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); opacity: .45; }

/* Multi */
.fga-multi-btn{
  width: 100%;
  height: 46px;
  border: 1px solid #d9d9d9;
  border-radius: 5px;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 12px 0 14px;
  font-size: 16px;
  cursor: pointer;
}
.fga-multi-caret { opacity: .7; }

.fga-multi-panel{
  display: none;
  position: absolute;
  z-index: 50;
  top: calc(100% + 8px);
  left: 0;
  right: 0;
  background: #fff;
  border: 1px solid #e6e6e6;
  border-radius: 5px;
  box-shadow: 0 18px 50px rgba(0,0,0,.12);
  max-height: 280px;
  overflow: auto;
  padding: 10px;
}
.fga-multi.is-open .fga-multi-panel{ display:block; }
.fga-multi-option{ display:flex; gap:10px; align-items:center; padding: 8px 8px; border-radius: 5px; cursor: pointer; }
.fga-multi-option:hover{ background:#f6f6f6; }
.fga-multi-option input{ transform: scale(1.05); }
.fga-multi-empty{ padding: 10px; opacity: .7; }

.fga-reset { display:inline-block; margin-top: 6px; color: #0884CC; text-decoration: none; }
.fga-reset:hover { text-decoration: underline; }

/* Responsive (filter) */
@media (max-width: 980px){
  .fga-row{ grid-template-columns: 1fr; }
  .fga-row--bottom{ grid-template-columns: 1fr; }
  .fga-title{ font-size: 28px; }
}

/* ===== Update: dropdown buttons (white + Poppins 700) ===== */

/* Button (closed state) */
#fgaFilterForm .fga-multi-btn{
  background: #fff !important;
  color: #1f2937;
  font-family: Poppins, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif !important;
  font-weight: 700 !important;
  border-color: #d9d9d9;
}

/* Placeholder text in button */
#fgaFilterForm .fga-multi-placeholder{
  font-weight: 700 !important;
}

/* Caret */
#fgaFilterForm .fga-multi-caret{
  opacity: .7;
}

/* Dropdown panel */
#fgaFilterForm .fga-multi-panel{
  background: #fff !important;
}

/* Dropdown options */
#fgaFilterForm .fga-multi-option span{
  font-family: Poppins, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif !important;
  font-weight: 700 !important;
}

/* ===== Fondsen.org–achtige dropdown styling ===== */

/* Verberg checkbox zelf */
#fgaFilterForm .fga-multi-option input[type="checkbox"] {
  display: none;
}

/* Optie rij */
#fgaFilterForm .fga-multi-option {
  display: flex;
  align-items: center;
  padding: 12px 14px;
  border-radius: 5px;
  cursor: pointer;
  font-family: Poppins, system-ui, sans-serif;
  font-weight: 600;
  font-size: 15px;
  color: #1f2937;
  transition: background 0.15s ease, color 0.15s ease;
}

/* Hover zoals Fondsen.org */
#fgaFilterForm .fga-multi-option:hover {
  background: #f3f4f6;
}

/* Actieve (geselecteerde) optie */
#fgaFilterForm .fga-multi-option input[type="checkbox"]:checked + span {
  background: #f3f4f6;
  border-radius: 5px;
  padding: 6px 8px;
}

/* Zorg dat span de volledige breedte gebruikt */
#fgaFilterForm .fga-multi-option span {
  width: 100%;
  display: block;
}

/* Dropdown container rustiger */
#fgaFilterForm .fga-multi-panel {
  padding: 8px;
  border-radius: 5px;
}

/* Button (gesloten state) meer "select look" */
#fgaFilterForm .fga-multi-btn {
  font-family: Poppins, system-ui, sans-serif;
  font-weight: 700;
  font-size: 15px;
  background: #fff;
  border: 1px solid #d1d5db;
}

/* Caret iets subtieler */
#fgaFilterForm .fga-multi-caret {
  font-size: 12px;
  opacity: 0.6;
}



</style>
