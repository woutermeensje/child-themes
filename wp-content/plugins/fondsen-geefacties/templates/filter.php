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
      <h1 class="fga-title">Donatie platform - Fondsen.org</h1>
      <p>Bekijk alle geefacties of <a href="/geefactie-plaatsen/">plaats een eigen geefactie op Fondsen.org!</a></p>
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
          <div class="fga-multi" data-name="fga_type[]" data-label="Geefacties">
            <button type="button" class="fga-multi-btn" aria-expanded="false">
              <span class="fga-multi-placeholder">Geefacties: Alle soorten</span>
              <span class="fga-multi-caret" aria-hidden="true"></span>
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
                      <span class="fga-option-text"><?php echo esc_html($term->name); ?></span>
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
          <div class="fga-multi" data-name="fga_thema[]" data-label="Thema">
            <button type="button" class="fga-multi-btn" aria-expanded="false">
              <span class="fga-multi-placeholder">Thema: Alle thema's</span>
              <span class="fga-multi-caret" aria-hidden="true"></span>
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
                      <span class="fga-option-text"><?php echo esc_html($term->name); ?></span>
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

      <!-- Active chips onderaan -->
      <div class="active-filters" id="fgaActiveFilters"></div>
    </form>

  </div>
</div>

<script>
(function(){
  var form = document.getElementById('fgaFilterForm');
  if(!form) return;

  var activeWrap = document.getElementById('fgaActiveFilters');

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

  function renderActiveFilters(){
    if(!activeWrap) return;
    activeWrap.innerHTML = '';

    var chips = [];
    form.querySelectorAll('.fga-multi').forEach(function(multi){
      multi.querySelectorAll('input[type="checkbox"]:checked').forEach(function(cb){
        var row = cb.closest('.fga-multi-option');
        var textEl = row ? row.querySelector('.fga-option-text') : null;
        var text = textEl ? textEl.textContent.trim() : cb.value;

        chips.push({ multi: multi, cb: cb, text: text, value: cb.value });
      });
    });

    if(chips.length === 0){
      activeWrap.style.display = 'none';
      return;
    }

    activeWrap.style.display = 'flex';

    chips.forEach(function(c){
      var chip = document.createElement('span');
      chip.className = 'active-filter';
      chip.innerHTML = '<span class="active-filter-text">'+ c.text +'</span>';

      var x = document.createElement('button');
      x.type = 'button';
      x.className = 'active-filter-x';
      x.setAttribute('aria-label', 'Verwijderen');
      x.textContent = '×';

      x.addEventListener('click', function(){
        c.cb.checked = false;
        updateHidden(c.multi);
        renderActiveFilters();
        submit();
      });

      chip.appendChild(x);
      activeWrap.appendChild(chip);
    });
  }

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
        renderActiveFilters();
        submit();
      });
    });

    updateHidden(multi);
  });

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

  renderActiveFilters();
})();
</script>

<style>
/* ===== Filter base ===== */
.fga-wrap { max-width: 1180px; margin: 24px auto; padding: 0 16px; }
.fga-filter-card {
  background: #fff;
  border: 1px solid #E0E0E0;
  border-radius: 6px;
  box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
  padding: 18px;
}

.fga-title { margin: 0 0 14px; font-size: 34px; line-height: 1.1; color: #333; font-family: Inter, system-ui, sans-serif; }

.fga-head p{
  font-family: Poppins, system-ui, sans-serif;
  font-size: 15px;
  color: #333;
  margin: 10px 0 0;
}
.fga-head a{ color:#0884CC; text-decoration:none; }
.fga-head a:hover{ text-decoration:underline; }

/* Layout */
.fga-filter { width: 100%; margin-top: 10px; }
.fga-row { display: grid; grid-template-columns: 1fr 320px; gap: 14px; margin-top: 12px; }
.fga-row--bottom { grid-template-columns: 320px 1fr; align-items: start; }
.fga-item { position: relative; }

/* Search (fondsen style) */
.fga-item--search{ position:relative; }
.fga-item--search input{
  width:100%;
  padding:12px 14px 12px 38px;
  font-size:16px;
  border:1px solid #dedede;
  border-radius:0;
  background:#fff;
  color:#222;
  box-shadow:0 2px 6px rgba(0,0,0,0.08);
  transition:border-color .2s ease, box-shadow .2s ease;
  font-family:Poppins, system-ui, sans-serif;
  font-weight:400;
}
.fga-item--search input:focus{
  outline:none;
  border-color:#0a6b8d;
  box-shadow:0 2px 8px rgba(10,107,141,0.25);
}
.fga-item--search input::placeholder{
  color:#333;
  font-size:16px;
  font-style:italic;
}
.fga-search-icon{
  position:absolute;
  left:10px;
  top:50%;
  transform:translateY(-50%);
  font-size:16px;
  color:#0a6b8d;
  pointer-events:none;
}

/* Multi button (sj-style) */
.fga-multi-btn{
  width:100%;
  display:flex;
  align-items:center;
  justify-content:space-between;
  border-radius:6px;
  border:1px solid #E0E0E0 !important;
  background:#fff !important;
  padding:12px 12px;
  min-height:44px;
  cursor:pointer;
  user-select:none;
  font-family:Poppins, system-ui, sans-serif;
  font-weight:700;
  font-size:16px;
  color:#111;
}

.fga-multi-placeholder{
  font-family:Poppins, system-ui, sans-serif;
  font-weight:400;
  color:#333;
  font-size:15px;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}

.fga-multi-caret{
  width:10px;
  height:10px;
  border-right:2px solid #111;
  border-bottom:2px solid #111;
  transform:rotate(45deg);
  transition:transform .2s ease;
  margin-left:10px;
}
.fga-multi.is-open .fga-multi-caret{ transform:rotate(-135deg); }

/* Panel */
.fga-multi-panel{
  display:none;
  position:absolute;
  left:0;
  right:0;
  margin-top:10px;
  background:#fff;
  border:1px solid #E0E0E0;
  border-radius:8px;
  box-shadow:0 10px 40px -5px rgba(0,0,0,0.15);
  padding:8px;
  max-height:280px;
  overflow:auto;
  z-index:9999;
}
.fga-multi.is-open .fga-multi-panel{ display:block; }
.fga-multi-options{ display:flex; flex-direction:column; gap:2px; }
.fga-multi-empty{ padding:10px; opacity:.7; font-family:Poppins, system-ui, sans-serif; }

/* ✅ Options: clean list like your 2nd screenshot */
.fga-multi-option{
  display:block;
  padding:10px 12px;
  border-radius:8px;
  cursor:pointer;
}
.fga-multi-option:hover{ background:#f2f2f2; }

/* hide checkbox but keep it functional */
.fga-multi-option input[type="checkbox"]{
  position:absolute;
  left:-9999px;
  opacity:0;
}

/* option text */
.fga-option-text{
  display:block;
  font-family:Poppins, system-ui, sans-serif;
  font-weight:700;
  color:#333;
  font-size:16px;
  line-height:1.25;
}

/* ✅ selected highlight like your example (soft gray block) */
.fga-multi-option input[type="checkbox"]:checked + .fga-option-text{
  background:#DEDEDE;
  border-radius:10px;
  padding:14px 16px;
}

/* Reset */
.fga-reset{ display:inline-block; margin-top: 6px; color:#0884CC; text-decoration:none; font-family:Poppins, system-ui, sans-serif; }
.fga-reset:hover{ text-decoration:underline; }

/* Active filters chips */
.active-filters{
  display:none;
  flex-wrap:wrap;
  gap:10px;
  margin:14px 0 0;
}
.active-filter{
  display:inline-flex;
  align-items:center;
  gap:6px;
  background:#ffffff;
  border:1px solid #d7e6ff;
  border-radius:999px;
  padding:8px 12px;
  font-size:16px;
  color:#111;
  font-weight:700;
}
.active-filter:hover{ border-color:#0884CC; }
.active-filter-text{ color:#333; }
.active-filter-x{
  border:none;
  background:transparent;
  cursor:pointer;
  color:#0884CC;
  font-weight:900;
  font-size:20px;
  line-height:1;
  padding:0;
  margin-left:6px;
}

/* Responsive */
@media (max-width: 980px){
  .fga-row{ grid-template-columns: 1fr; }
  .fga-row--bottom{ grid-template-columns: 1fr; }
  .fga-title{ font-size: 28px; }
}
</style>
