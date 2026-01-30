<?php if (!defined('ABSPATH')) exit; ?>

<div class="vpjobs-filters">
  <div class="vpjobs-filter-header" style="padding: 0 20px 8px 20px;">
    <h2>Bekijk vacatures</h2>
    <p>Filter op functie, locatie en taxonomieën.</p>
  </div>

  <form class="vpjobs-form" data-vpjobs-form>
    <div class="vpjobs-search-basic">
      <div class="vpjobs-search-keywords">
        <input type="text" name="search_keywords" placeholder="Functienaam of zoekwoord"
               value="<?php echo esc_attr($keywords); ?>" />
      </div>

      <div class="vpjobs-search-location">
        <input type="text" name="search_location" placeholder="Stad of plaats"
               value="<?php echo esc_attr($location); ?>" />
      </div>
    </div>

    <div class="vpjobs-filter-box">

      <div class="vpjobs-filter">
        <select name="job_type[]" class="vpjs-select" data-placeholder="Job type" multiple>
          <?php foreach (get_terms([ 'taxonomy' => 'vp_job_type', 'hide_empty' => true ]) as $t): ?>
            <option value="<?php echo esc_attr($t->slug); ?>" <?php selected(in_array($t->slug, $selected['job_type'], true)); ?>>
              <?php echo esc_html($t->name); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="vpjobs-filter">
        <select name="categorie[]" class="vpjs-select" data-placeholder="Categorie" multiple>
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
        <select name="bedrijf[]" class="vpjs-select" data-placeholder="Bedrijf" multiple>
          <?php foreach (get_terms([ 'taxonomy' => 'vp_company', 'hide_empty' => true ]) as $t): ?>
            <option value="<?php echo esc_attr($t->slug); ?>" <?php selected(in_array($t->slug, $selected['bedrijf'], true)); ?>>
              <?php echo esc_html($t->name); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="vpjobs-filter-actions">
        <button type="button" class="vpjobs-reset" data-vpjobs-reset>Reset filters</button>
      </div>

    </div>

    <input type="hidden" name="per_page" value="<?php echo esc_attr((int)$atts['per_page']); ?>">
  </form>

  <div class="vpjobs-active-filters" data-vpjobs-chips aria-live="polite"></div>
</div>