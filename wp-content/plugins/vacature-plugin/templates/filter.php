<?php if (!defined('ABSPATH')) exit; ?>

<div class="vpjobs-page">
  <form class="vpjobs-form" data-vpjobs-form>

    <!-- FILTER (100% breed) -->
    <div class="vpjobs-filters-wide">
      <div class="vpjobs-filters-box">

        <div class="vpjobs-filters-head">
          <h3 class="vpjobs-filters-title">
            <?php echo esc_html( vp_setting('filters_heading', 'Filters') ); ?>
          </h3>

          <div class="vpjobs-filter-actions">
            <button type="button" class="vpjobs-reset-inline" data-vpjobs-reset>
              <?php echo esc_html(vp_setting('reset_button_text', 'Wis alles')); ?>
            </button>
          </div>
        </div>

        <!-- Grid: zoekvelden + dropdowns -->
        <div class="vpjobs-filters-grid">

          <div class="vpjobs-filter vpjobs-filter-search">
            <input
              type="text"
              name="search_keywords"
              class="vpjobs-input"
              placeholder="Functienaam of zoekwoord"
              value="<?php echo esc_attr($keywords); ?>"
            >
          </div>

          <div class="vpjobs-filter vpjobs-filter-search">
            <input
              type="text"
              name="search_location"
              class="vpjobs-input"
              placeholder="Stad of plaats"
              value="<?php echo esc_attr($location); ?>"
            >
          </div>

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
                <option value="<?php echo esc_attr($t->slug); ?>" <?php selected(in_array($t->slug, $selected['bedrijfsnaam'] ?? [], true)); ?>>
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

        </div>

        <!-- Active filters chips -->
        <div class="vpjobs-active-filters" data-vpjobs-chips aria-live="polite"></div>

      </div>
    </div>

    <!-- RESULTS -->
    <div id="vpjobs-results" class="vpjobs-results">
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

    <input type="hidden" name="per_page" value="<?php echo esc_attr((int)$atts['per_page']); ?>">
  </form>
</div>



<style>

  /* =========================
   VP Jobs – Filter layout + custom select fix
   Desktop:
   - Rij 1: 2 zoekvelden (50% + 50%)
   - Rij 2: 5 dropdowns naast elkaar
   ========================= */

.vpjobs-page{
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  box-sizing: border-box;
  
}

.vpjobs-filters-wide{ width: 100%; }

.vpjobs-filters-box{
  border: 1px solid #DEDEDE;
  border-radius: 5px;
  background: #fff;
  padding: 20px;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  margin: 0 0 20px 0;
  box-sizing: border-box;
  background:#FBFAF8;
}

.vpjobs-filters-head{
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin: 0 0 16px 0;
}

.vpjobs-filters-title{
  margin: 0;
  font-size: 16px;
  color: #333;
  font-family: Poppins, sans-serif;
}

.vpjobs-filter-actions{ display:flex; }

.vpjobs-reset-inline{
  border: 1px solid #DEDEDE;
  background: #fff;
  padding: 12px 16px;
  border-radius: 5px;
  cursor: pointer;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  font-family: Poppins, sans-serif;
  font-size: 14px;
  font-weight: 300;
  color: #333;
}

/* =========================
   GRID – definitieve layout
   ========================= */

.vpjobs-filters-grid{
  display: grid;
  grid-template-columns: repeat(10, minmax(0, 1fr));
  gap: 16px;
  align-items: start;
  grid-auto-flow: row;
}

.vpjobs-filter{
  margin: 0;
  min-width: 0;
  position: relative;
}

/* =========================
   RIJ 1 – zoekvelden (50% + 50%)
   ========================= */

.vpjobs-filters-grid > .vpjobs-filter:nth-child(1){
  grid-column: 1 / span 5;
}

.vpjobs-filters-grid > .vpjobs-filter:nth-child(2){
  grid-column: 6 / span 5;
}

/* =========================
   RIJ 2 – dropdowns (5 naast elkaar)
   ========================= */

.vpjobs-filters-grid > .vpjobs-filter:nth-child(n+3){
  grid-column: span 2;
}

/* Zoekvelden: altijd rij 1 */
.vpjobs-filter-search{
  grid-row: 1;
}
.vpjobs-filter-search-keywords{ grid-column: 1 / span 5; }
.vpjobs-filter-search-location{ grid-column: 6 / span 5; }

/* Dropdowns: altijd rij 2, 5 naast elkaar */
.vpjobs-filters-grid > .vpjobs-filter:not(.vpjobs-filter-search){
  grid-row: 2;
  grid-column: span 2;
}

/* =========================
   Input styling (zelfde look)
   ========================= */

.vpjobs-input{
  width: 100%;
  padding: 12px 14px;
  border: 1px solid #DEDEDE !important;
  border-radius: 5px;
  background: #fff;
  font-size: 14px;
  font-family: Poppins, sans-serif;
  font-weight: 300;
  color: #333;
  min-height: 48px;
  box-sizing: border-box;
  -webkit-appearance: none;
  appearance: none;
}

.vpjobs-input::placeholder{
  color: #333;
  opacity: 0.85;
}

.vpjobs-input:focus{
  outline: none;
  border-color: #DEDEDE;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
}

/* =========================
   CRUCIAAL: verberg de originele select
   (anders zie je die “lijst” zoals in je screenshot)
   ========================= */

/* Als jouw JS de select NIET altijd de class vp-hidden-select geeft,
   verberg dan ook direct de bronselects (.vpjs-select). */
select.vpjs-select{
  position: absolute !important;
  left: -9999px !important;
  width: 1px !important;
  height: 1px !important;
  opacity: 0 !important;
  pointer-events: none !important;
}

/* Als jouw JS wél vp-hidden-select toevoegt: ook goed */
.vp-hidden-select{
  position: absolute !important;
  left: -9999px !important;
  width: 1px !important;
  height: 1px !important;
  opacity: 0 !important;
  pointer-events: none !important;
}

/* =========================
   Custom select (vanilla) – behoud classes
   ========================= */

.vp-select-wrap{ position: relative; width: 100%; min-width: 0; }
.vp-select{ position: relative; width: 100%; min-width: 0; }

.vp-select-btn{
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 14px;
  border: 1px solid #DEDEDE;
  border-radius: 5px;
  background: #fff;
  cursor: pointer;
  user-select: none;
  min-height: 48px;
  box-sizing: border-box;
}

span.vp-placeholder{
  color: #333 !important;
  font-weight: 300;
  font-family: Poppins, sans-serif;
  font-size: 14px;
}

.vp-chev{
  width: 10px;
  height: 10px;
  border-right: 2px solid #111;
  border-bottom: 2px solid #111;
  transform: rotate(45deg);
  transition: transform .2s ease;
  flex: 0 0 auto;
}

.vp-select.active .vp-chev{ transform: rotate(-135deg); }

.vp-options{
  display: none;
  position: absolute;
  left: 0;
  right: 0;
  margin-top: 8px;
  background: #fff;
  border: 1px solid #DEDEDE;
  border-radius: 5px;
  padding: 8px;
  max-height: 280px;
  overflow: auto;
  z-index: 9999;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  box-sizing: border-box;
}

.vp-select.active .vp-options{ display: block; }

.vp-option{
  padding: 8px 12px;
  border-radius: 5px;
  cursor: pointer;
}
.vp-option:hover{ background: #f2f2f2; }
.vp-option.is-selected{ background: #f7fbff; }

/* =========================
   Chips
   ========================= */

.vpjobs-active-filters{
  display: none;
  flex-wrap: wrap;
  gap: 8px;
  padding-top: 16px;
}

.vpjobs-active-filter{
  display: inline-flex;
  align-items: center;
  gap: 8px;
  border: 1px solid #DEDEDE;
  border-radius: 5px;
  padding: 8px 12px;
  background: #fff;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  font-family: Poppins, sans-serif;
  font-size: 14px;
}

.vpjobs-chip-x{
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 18px;
  line-height: 1;
}

/* =========================
   Responsive
   ========================= */

/* Tablet: 2 kolommen
   - Rij 1: zoekvelden naast elkaar
   - Rij 2+: dropdowns 2 per rij
*/
@media (max-width: 1024px){
  .vpjobs-filters-grid{
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .vpjobs-filter-search{ grid-row: auto; }
  .vpjobs-filter-search-keywords{ grid-column: span 1; }
  .vpjobs-filter-search-location{ grid-column: span 1; }

  .vpjobs-filters-grid > .vpjobs-filter:not(.vpjobs-filter-search){
    grid-row: auto;
    grid-column: span 1;
  }
}

/* Mobile: 1 kolom */
@media (max-width: 640px){
  .vpjobs-filters-grid{
    grid-template-columns: 1fr;
  }

  .vpjobs-filter-search-keywords,
  .vpjobs-filter-search-location{
    grid-column: span 1;
  }

  .vpjobs-filters-grid > .vpjobs-filter:not(.vpjobs-filter-search){
    grid-column: span 1;
  }
}
</style>