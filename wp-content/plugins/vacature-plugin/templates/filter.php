<?php if (!defined('ABSPATH')) exit; ?>

<div class="vpjobs-page">
  <form class="vpjobs-form" data-vpjobs-form>

    <!-- FILTER (100% breed) -->
    <div class="vpjobs-filters-wide">
      <div class="vpjobs-filters-box">

        <div class="vpjobs-filters-head">

          <div class="vpjobs-filters-head-left">
            <h3 class="vpjobs-filters-title">
              <?php echo esc_html( vp_setting('filters_heading', 'Filters') ); ?>
            </h3>

            <?php
              $note_text  = vp_setting('filters_newsletter_text', 'Of schrijf je in voor de');
              $link_text  = vp_setting('filters_newsletter_link_text', 'vacature nieuwsbrief');
              $link_url   = vp_setting('filters_newsletter_url', '');
              $site_name  = vp_setting('filters_newsletter_site', 'Recruiternext.nl');

              if ($link_text && $link_url):
            ?>
              <p class="vpjobs-filters-note">
                <?php echo esc_html($note_text); ?>
                <a href="<?php echo esc_url($link_url); ?>">
                  <?php echo esc_html($link_text); ?>
                </a>
                <?php if ($site_name): ?>
                  <?php echo esc_html(' van ' . $site_name); ?>
                <?php endif; ?>
              </p>
            <?php endif; ?>
          </div>

          <div class="vpjobs-filter-actions">
            <button type="button" class="vpjobs-reset-inline" data-vpjobs-reset>
              <?php echo esc_html(vp_setting('reset_button_text', 'Wis alles')); ?>
            </button>
          </div>

        </div>

        <!-- Grid: zoekvelden + dropdowns -->
        <div class="vpjobs-filters-grid">

          <div class="vpjobs-filter vpjobs-filter-search vpjobs-filter-search-keywords">
            <input
              type="text"
              name="search_keywords"
              class="vpjobs-input"
              placeholder="Functienaam of zoekwoord"
              value="<?php echo esc_attr($keywords); ?>"
            >
          </div>

          <div class="vpjobs-filter vpjobs-filter-search vpjobs-filter-search-location">
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
   VP Jobs – Filter layout
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
  background: #FBFAF8;
  padding: 20px;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  margin: 0 0 20px 0;
  box-sizing: border-box;
}

/* =========================
   Header (titel + nieuwsbrief + reset)
   ========================= */
.vpjobs-filters-head{
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin: 0 0 16px 0;
}

.vpjobs-filters-head-left{
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-width: 0;
}

.vpjobs-filters-title{
  margin: 0;
  font-size: 16px;
  color: #333;
  font-family: Poppins, sans-serif;
  font-weight: 600;
}

.vpjobs-filters-note{
  margin: 0;
  font-family: Poppins, sans-serif;
  font-size: 14px;
  color: #555;
  line-height: 1.35;
}

.vpjobs-filters-note a{
  color: #0B4DB8;
  font-weight: 400;
  text-decoration: none;
}

.vpjobs-filters-note a:hover{ text-decoration: none; }

.vpjobs-filter-actions{ display:flex; }

.vpjobs-reset-inline{
  border: 1px solid #DEDEDE;
  background: #fff;
  padding: 8px 12px;
  border-radius: 5px;
  cursor: pointer;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
  font-family: Poppins, sans-serif;
  font-size: 14px;
  font-weight: 600;
  color: #333;
  white-space: nowrap;
}

.vpjobs-reset-inline:hover {
    background-color: white; 
    color: #333; 
}


/* =========================
   GRID – desktop
   ========================= */
.vpjobs-filters-grid{
  display: grid;
  grid-template-columns: repeat(10, minmax(0, 1fr));
  gap: 16px;
  align-items: start;
}

/* Zoekvelden: 50/50 */
.vpjobs-filter-search-keywords{ grid-column: 1 / span 5; }
.vpjobs-filter-search-location{ grid-column: 6 / span 5; }

/* Dropdowns: 5 naast elkaar */
.vpjobs-filters-grid > .vpjobs-filter:not(.vpjobs-filter-search){
  grid-column: span 2;
}

.vpjobs-filter{ margin: 0; min-width: 0; position: relative; }

/* =========================
   Inputs
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
   Verberg originele select (bron)
   ========================= */
select.vpjs-select{
  position: absolute !important;
  left: -9999px !important;
  width: 1px !important;
  height: 1px !important;
  opacity: 0 !important;
  pointer-events: none !important;
}

.vp-hidden-select{
  position: absolute !important;
  left: -9999px !important;
  width: 1px !important;
  height: 1px !important;
  opacity: 0 !important;
  pointer-events: none !important;
}

/* =========================
   Custom select (vanilla)
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

/* =========================
   Responsive
   ========================= */

/* Tablet: 2 kolommen */
@media (max-width: 1024px){
  .vpjobs-filters-grid{
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }

  .vpjobs-filter-search-keywords,
  .vpjobs-filter-search-location{
    grid-column: span 1;
  }

  .vpjobs-filters-grid > .vpjobs-filter:not(.vpjobs-filter-search){
    grid-column: span 1;
  }

  .vpjobs-input,
  .vp-select-btn{
    min-height: 46px;
    padding: 11px 13px;
  }
}

/* Mobiel: 1 kolom */
@media (max-width: 640px){
  .vpjobs-page{ padding: 12px; }
  .vpjobs-filters-box{ padding: 14px; }

  .vpjobs-filters-head{
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
  }

  .vpjobs-filter-actions{ justify-content: flex-end; }

  .vpjobs-reset-inline{
    width: 100%;
    text-align: center;
  }

  .vpjobs-filters-grid{
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .vpjobs-filter-search-keywords,
  .vpjobs-filter-search-location,
  .vpjobs-filters-grid > .vpjobs-filter:not(.vpjobs-filter-search){
    grid-column: 1 / -1;
  }

  .vpjobs-input,
  .vp-select-btn{
    width: 100%;
    min-height: 46px;
  }
}


span.vp-placeholder {
    font-weight: 700 !important;
}


.vp-option {
    font-weight: 700 !important;
    font-size: 14px; 
}

</style>

<style>
  .vpjobs-active-filter {
    background: white !important; 
    border: 1px solid #DEDEDE !important; 
    color: #333 !important; 
    font-family: Inter !important; 
    font-weight: 700 !important; 
    padding: 8px !important; 
    border-radius: 999px !important; 
    font-size: 14px !important; 
    
  }

  .vpjobs-chip-x {
    font-size: 14px !important; 
    font-weight: 700 !important; 
    color: #845EC2 !important; 
    padding: 2px !important;
    border: none; 
  }

  .vpjobs-chip-x:hover {
    background: none !important; 
  }
</style>