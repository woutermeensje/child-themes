<?php if (!defined('ABSPATH')) exit; ?>

<div class="vpjobs-page">

  <!-- Searchbar bovenaan (full width) -->
  <form class="vpjobs-form" data-vpjobs-form>

    <div class="vpjobs-searchbar">
      <div class="vpjobs-search-keywords">
        <input type="text" name="search_keywords" placeholder="Functienaam of zoekwoord"
               value="<?php echo esc_attr($keywords); ?>" />
      </div>

      <div class="vpjobs-search-location">
        <input type="text" name="search_location" placeholder="Stad of plaats"
               value="<?php echo esc_attr($location); ?>" />
      </div>

      <button type="button" class="vpjobs-reset-inline" data-vpjobs-reset>Wis alles</button>
    </div>

    <!-- Layout: sidebar links + results rechts -->
    <div class="vpjobs-layout">

      <aside class="vpjobs-sidebar">
        <div class="vpjobs-sidebar-box">
          <h3 class="vpjobs-sidebar-title">Filters</h3>

          <div class="vpjobs-filter">
            <select name="job_type[]" class="vpjs-select" data-placeholder="Dienstverband" multiple>
              <?php foreach (get_terms([ 'taxonomy' => 'vp_job_type', 'hide_empty' => true ]) as $t): ?>
                <option value="<?php echo esc_attr($t->slug); ?>" <?php selected(in_array($t->slug, $selected['job_type'], true)); ?>>
                  <?php echo esc_html($t->name); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="vpjobs-filter">
            <select name="categorie[]" class="vpjs-select" data-placeholder="Functie / categorie" multiple>
              <?php foreach (get_terms([ 'taxonomy' => 'vp_category', 'hide_empty' => true ]) as $t): ?>
                <option value="<?php echo esc_attr($t->slug); ?>" <?php selected(in_array($t->slug, $selected['categorie'], true)); ?>>
                  <?php echo esc_html($t->name); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="vpjobs-filter">
            <select name="org_type[]" class="vpjs-select" data-placeholder="Type organisatie" multiple>
              <?php foreach (get_terms([ 'taxonomy' => 'vp_org_type', 'hide_empty' => true ]) as $t): ?>
                <option value="<?php echo esc_attr($t->slug); ?>" <?php selected(in_array($t->slug, $selected['org_type'], true)); ?>>
                  <?php echo esc_html($t->name); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="vpjobs-filter">
          <select name="bedrijfsnaam[]" class="vpjs-select" data-placeholder="Bedrijfsnaam" multiple>
            <?php foreach (get_terms([ 'taxonomy' => 'bedrijfsnaam', 'hide_empty' => false ]) as $t): ?>
              <option value="<?php echo esc_attr($t->slug); ?>"
                <?php selected(in_array($t->slug, $selected['bedrijfsnaam'] ?? [], true)); ?>>
                <?php echo esc_html($t->name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

          <div class="vpjobs-filter">
          <select name="regio[]" class="vpjs-select" data-placeholder="Regio" multiple>
            <?php foreach (get_terms([ 'taxonomy' => 'vp_regio', 'hide_empty' => false ]) as $t): ?>
              <option value="<?php echo esc_attr($t->slug); ?>" <?php selected(in_array($t->slug, $selected['regio'], true)); ?>>
                <?php echo esc_html($t->name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

          <!-- Active filters chips -->
          <div class="vpjobs-active-filters" data-vpjobs-chips aria-live="polite"></div>
        </div>
      </aside>

      <main class="vpjobs-main">

       

        <div id="vpjobs-results">
          <?php
            echo vp_template('listings.php', [
              'query' => VP_AJAX::build_query([
                'keywords' => $keywords,
                'location' => $location,
                'selected' => $selected,
                'per_page' => (int)$atts['per_page'],
                'paged'    => 1,
              ]),
            ]);
          ?>
        </div>

      </main>

    </div>

    <input type="hidden" name="per_page" value="<?php echo esc_attr((int)$atts['per_page']); ?>">
  </form>

</div>

<style>

  /* =========================
   Vacature Plugin - FILTER UI
   ========================= */

/* Wrapper */
.vpjobs-wrap { width: 100%; }

/* Page wrapper */
.vpjobs-page{
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

/* Searchbar bovenaan */
.vpjobs-searchbar{
  display: flex;
  gap: 20px;
  align-items: center;
  margin: 0 0 20px 0;
}

.vpjobs-searchbar input[type="text"]{
  width: 100%;
  padding: 12px 14px;
  border: 1px solid #DEDEDE;
  border-radius: 5px;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  background: #fff;
}

.vpjobs-search-keywords,
.vpjobs-search-location{
  flex: 1 1 0;
}

.vpjobs-reset-inline{
  flex: 0 0 auto;
  border: 1px solid #DEDEDE;
  background: #fff;
  padding: 12px 16px;
  border-radius: 5px;
  cursor: pointer;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
}

/* Layout: sidebar + main */
.vpjobs-layout{
  display: flex;
  gap: 20px;
  align-items: flex-start;
}

.vpjobs-sidebar{ flex: 0 0 30%; }
.vpjobs-main{ flex: 1 1 70%; }

/* Sidebar box */
.vpjobs-sidebar-box{
  border: 1px solid #DEDEDE;
  border-radius: 5px;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  background: #fff;
  padding: 20px;
}

.vpjobs-sidebar-title{
  margin: 0 0 16px 0;
  font-size: 16px;
  color: #333; 
}

/* Heading boven listings (staat in filter.php boven #vpjobs-results) */
.vpjobs-results-title{
  margin: 0 0 20px 0;
  font-size: 22px;
}

/* Filters spacing */
.vpjobs-filter{
  margin: 0 0 16px 0;
}

/* Optional: oude container styles (als je ze nog ergens gebruikt) */
.vpjobs-filters{
  width: 100%;
  padding: 20px 0;
  margin: 20px auto;
  background: #fff;
  border: 1px solid #DEDEDE;
  border-radius: 5px;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
}

.vpjobs-filter-header h2{ margin: 0 0 8px 0; font-size: 20px; }
.vpjobs-filter-header p{ margin: 0 0 8px 0; color:#333; font-size:14px; }

/* (Legacy) search-basic (als je die nog gebruikt i.p.v. searchbar) */
.vpjobs-search-basic{
  display:flex;
  gap:16px;
  padding: 0 20px;
}

.vpjobs-search-basic input[type="text"]{
  width:100%;
  padding: 12px 14px;
  border: 1px solid #DEDEDE;
  border-radius: 5px;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  background:#fff;
}

/* (Legacy) filter-box grid (als je die nog gebruikt) */
.vpjobs-filter-box{
  display:flex;
  flex-wrap:wrap;
  gap:16px;
  padding:20px;
}

.vpjobs-filter-actions{ display:flex; align-items:center; }

.vpjobs-reset{
  border:1px solid #DEDEDE;
  background:#fff;
  padding: 8px 16px;
  border-radius:5px;
  cursor:pointer;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
}
.vpjobs-reset:hover{ opacity:0.95; }

/* Custom select (neutral) */
.vp-hidden-select{
  position:absolute !important;
  left:-9999px !important;
  width:1px !important;
  height:1px !important;
  opacity:0 !important;
}

.vp-select-wrap{ position:relative; width:100%; }
.vp-select{ position:relative; width:100%; }

.vp-select-btn{
  width:100%;
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding: 12px 14px;
  border:1px solid #DEDEDE;
  border-radius:5px;
  background:#fff;
  cursor:pointer;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  user-select:none;
}

.vp-chev{
  width:10px;
  height:10px;
  border-right:2px solid #111;
  border-bottom:2px solid #111;
  transform: rotate(45deg);
  transition: transform .2s ease;
}
.vp-select.active .vp-chev{ transform: rotate(-135deg); }

.vp-options{
  display:none;
  position:absolute;
  left:0; right:0;
  margin-top:8px;
  background:#fff;
  border:1px solid #DEDEDE;
  border-radius:5px;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  padding:8px;
  max-height:280px;
  overflow:auto;
  z-index:9999;
}
.vp-select.active .vp-options{ display:block; }

.vp-option{
  padding:8px 12px;
  border-radius:5px;
  cursor:pointer;
}
.vp-option:hover{ background:#f2f2f2; }
.vp-option.is-selected{ background:#f7fbff; }

/* Active filter chips */
.vpjobs-active-filters{
  display:none;
  flex-wrap:wrap;
  gap:8px;
  padding: 16px 0 0 0; /* sidebar variant */
}

.vpjobs-active-filter{
  display:inline-flex;
  align-items:center;
  gap:8px;
  border:1px solid #DEDEDE;
  border-radius: 999px;
  padding: 8px 12px;
  background:#fff;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
}

.vpjobs-chip-x{
  border:none;
  background:transparent;
  cursor:pointer;
  font-size:18px;
  line-height:1;
}

/* Mobile */
@media (max-width: 900px){
  .vpjobs-searchbar{ flex-direction: column; }
  .vpjobs-layout{ flex-direction: column; }
  .vpjobs-sidebar, .vpjobs-main{ flex: 1 1 100%; }
}

@media (max-width:768px){
  .vpjobs-search-basic{flex-direction:column;}
}
</style>