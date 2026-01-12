<?php
if (!defined('ABSPATH')) exit;

wp_enqueue_script('wp-job-manager-ajax-filters');
do_action('job_manager_job_filters_before', $atts);

// ✅ Vul $selected met waarden vanuit: $_GET > $_POST > Shortcode
$selected = [
    'job_company'        => [],
    'job_sector'         => [],
    'job_regio'          => [],
    'job_name'           => [],
    'job_types'          => [], // WPJM types (job_listing_type)
];

$shortcode_atts = shortcode_atts([
    'job_company'      => '',
    'job_sector'       => '',
    'job_regio'        => '',
    'job_name'         => '',
    'job_listing_type' => '',
], $atts);

foreach ($selected as $key => &$value) {
    $shortcode_key = ($key === 'job_types') ? 'job_listing_type' : $key;

    if (!empty($_GET[$key])) {
        $value = (array) $_GET[$key];
    } elseif (!empty($_POST['filter_' . $key])) {
        $value = (array) $_POST['filter_' . $key];
    } elseif (!empty($shortcode_atts[$shortcode_key])) {
        $value = explode(',', sanitize_text_field($shortcode_atts[$shortcode_key]));
    }
}
unset($value);
?>

<form class="job_filters">
    <?php do_action('job_manager_job_filters_start', $atts); ?>

    <div class="filter-header" style="padding: 0 20px 10px 20px;">
        <h2>Bekijk alle vacatures en opdrachten!</h2>
        <p>Of stel een <a href="https://dereceptionist.nl/inschrijven/" target="_blank" class="unstyled-newsletter-link">vacature alert in!</a></p>
    </div>

    <div class="search-basic">
        <?php do_action('job_manager_job_filters_search_jobs_start', $atts); ?>

        <div class="search_keywords">
            <input type="text" name="search_keywords" id="search_keywords" placeholder="Functienaam, sector of onderwerp.." value="<?php echo esc_attr($keywords); ?>" />
        </div>

        <div class="search_location">
            <input type="text" name="search_location" id="search_location" placeholder="Stad of plaats" value="<?php echo esc_attr($location); ?>" />
        </div>

        <?php do_action('job_manager_job_filters_search_jobs_end', $atts); ?>
    </div>

    <div class="filter-box">

        <!-- Dienstverband / Job Types (WPJM) -->
        <div class="job_type">
            <select name="filter_job_types" id="filter_job_types" class="job_types" data-placeholder="Dienstverband">
                <option value=""><?php _e('Selecteer dienstverband', 'wp-job-manager'); ?></option>
                <?php foreach (get_job_listing_types() as $type) : ?>
                    <option value="<?php echo esc_attr($type->slug); ?>" <?php selected(in_array($type->slug, $selected['job_types'])); ?>>
                        <?php echo esc_html($type->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Sector -->
        <div class="job_sector">
            <select name="filter_job_sector" id="filter_job_sector" class="job_sector" data-placeholder="Sector">
                <option value=""><?php _e('Selecteer sector', 'wp-job-manager'); ?></option>
                <?php foreach (get_terms(['taxonomy' => 'job_sector', 'hide_empty' => true]) as $term) : ?>
                    <option value="<?php echo esc_attr($term->slug); ?>" <?php selected(in_array($term->slug, $selected['job_sector'])); ?>>
                        <?php echo esc_html($term->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Regio -->
        <div class="job_regio">
            <select name="filter_job_regio" id="filter_job_regio" class="job_regio" data-placeholder="Regio">
                <option value=""><?php _e('Selecteer regio', 'wp-job-manager'); ?></option>
                <?php foreach (get_terms(['taxonomy' => 'job_regio', 'hide_empty' => true]) as $term) : ?>
                    <option value="<?php echo esc_attr($term->slug); ?>" <?php selected(in_array($term->slug, $selected['job_regio'])); ?>>
                        <?php echo esc_html($term->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Beroep / Job Name -->
        <div class="job_name">
            <select name="filter_job_name" id="filter_job_name" class="job_name" data-placeholder="Beroep">
                <option value=""><?php _e('Selecteer beroep', 'wp-job-manager'); ?></option>
                <?php foreach (get_terms(['taxonomy' => 'job_name', 'hide_empty' => true]) as $term) : ?>
                    <option value="<?php echo esc_attr($term->slug); ?>" <?php selected(in_array($term->slug, $selected['job_name'])); ?>>
                        <?php echo esc_html($term->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Organisatie -->
        <div class="job_company">
            <select name="filter_job_company" id="filter_job_company" class="job_company" data-placeholder="Organisatie">
                <option value=""><?php _e('Selecteer organisatie', 'wp-job-manager'); ?></option>
                <?php foreach (get_terms(['taxonomy' => 'job_company', 'hide_empty' => true]) as $term) : ?>
                    <option value="<?php echo esc_attr($term->slug); ?>" <?php selected(in_array($term->slug, $selected['job_company'])); ?>>
                        <?php echo esc_html($term->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

    </div>
</form>

<?php do_action('job_manager_job_filters_after', $atts); ?>

<script>
jQuery(function($) {

    // Init Select2 (zelfde aanpak als Sustainablejobs.nl)
    $('#filter_job_types, #filter_job_sector, #filter_job_regio, #filter_job_name, #filter_job_company').select2({
        width: '100%',
        allowClear: true,
        placeholder: function() { return $(this).data('placeholder'); }
    });

    // Wijziging -> update resultaten
    $('#filter_job_types, #filter_job_sector, #filter_job_regio, #filter_job_name, #filter_job_company').on('change', function() {
        $('.job_filters').trigger('submit');
    });

    // Tekstvelden: Enter = filteren, live input = debounce kan als je wilt
    $('.job_filters').on('submit', function(e) {
        e.preventDefault();
        if (typeof job_manager_job_filters !== 'undefined') {
            job_manager_job_filters.filter_jobs(); // ✅ officiële WPJM functie
        } else {
            // Fallback naar core event
            $(document.body).trigger('update_results', [1, false]);
        }
    });
});
</script>

<!-- Styling 1-op-1 in de lijn van Sustainablejobs.nl (aangepast waar nodig) -->
<style>
/* z-index fix */
.select2-container, .select2-dropdown { z-index: 9999; }

.job_filters {
    width: 90%;
    padding: 20px 0;
    margin: 20px auto;
    background-color: var(--color-bg, #fff);
    border: 1px solid var(--color-primary, #0a6b8d);
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
}

.filter-header { padding: 0 20px 10px 20px; }
.filter-header p {
    font-family: Poppins, sans-serif;
    font-size: 15px;
    color: var(--color-text, #333);
    margin: 10px 0;
}
.filter-header h2 {
    font-family: Inter, sans-serif;
    font-size: 25px;
    color: var(--color-text, #333);
    margin-bottom: 15px;
    display: inline;
    background: linear-gradient(transparent 60%, #FEEAD5) 60%);
    font-weight: 700;
    border-radius: 2px;

    
}
.filter-header a.unstyled-newsletter-link {
    color: var(--color-primary, #0a6b8d);
    text-decoration: none;
    font-weight: 400;
    font-family: Poppins, sans-serif;
}
.filter-header a.unstyled-newsletter-link:hover { color: var(--color-roze, #e91e63); text-decoration: none; }

/* Zoekbalk */
.search-basic {
    display: flex;
    gap: 20px;
    padding: 0 20px;
}
.search_location, .search_keywords {
    flex-basis: 50%;
    max-width: 50%;
    display: flex; align-items: center;
    position: relative;
}
@media (max-width: 768px) {
    .search-basic { flex-direction: column; }
    .search-basic > div { max-width: 100%; flex-basis: 100%; }
}
.search-basic input[type="text"] {
    width: 100%;
    padding: 12px 14px 12px 38px;
    font-size: 16px;
    border: 1px solid var(--color-border-light, #ccc);
    background-color: var(--color-bg, #fff);
    color: var(--color-text, #222);
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    transition: border-color .2s ease, box-shadow .2s ease;
}
.search-basic input[type="text"]::placeholder { color: var(--color-text-muted, #777); }
.search-basic input[type="text"]:focus {
    outline: none;
    border-color: var(--color-primary, #0a6b8d);
    box-shadow: 0 2px 8px rgba(10,107,141,.25);
}
.search_keywords::before, .search_location::before {
    position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
    font-size: 16px; color: var(--color-primary,#0a6b8d); pointer-events: none;
}
.search_keywords::before { content: '🔍'; }
.search_location::before { content: '📍'; }



</style>



<style>
/* ===========================
   FILTER-BOX GRID
   =========================== */

.filter-box {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
    margin-top: 10px;
    padding: 20px 20px;
}

@media (max-width: 1024px) {
    .filter-box {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .filter-box {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .filter-box {
        grid-template-columns: 1fr;
    }
}

/* ===========================
   FILTER SELECT WRAPPERS
   =========================== */

.filter-box .job_type,
.filter-box .job_sector,
.filter-box .job_regio,
.filter-box .job_name,
.filter-box .job_company {
    position: relative;
    width: 100%;
}

/* ===========================
   SELECT2 STYLE (zoals Sustainablejobs.nl)
   =========================== */

/* Zorgt dat Select2 dezelfde "pill" stijl krijgt */
.filter-box .select2-container--default .select2-selection--single {
    height: 44px;
    border: 1px solid #DFBCE2;
    background-color: var(--color-bg, #fff);
    color: var(--color-text, #222);
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    padding: 0 40px 0 16px;
    font-size: 14px;
    transition: all 0.15s ease;
}

/* Tekst in select */
.filter-box .select2-selection__rendered {
    color: #111827;
    padding-left: 0 !important;
}

/* Placeholder */
.filter-box .select2-selection__placeholder {
    color: #333333 !important;
    font-weight: 400 !important; 
    font-family: Poppins; 
    font-size: 15px; 
}

/* Pijltje */
.filter-box .select2-selection__arrow {
    right: 14px !important;
    top: 50% !important;
    transform: translateY(-50%);
}

/* Hover state */
.filter-box .select2-container--default .select2-selection--single:hover {
    border-color: #d1d5db;
    background-color: #fafafa;
}

/* Focus / open state */
.filter-box .select2-container--default.select2-container--open .select2-selection--single {
    border-color: #2563eb; /* pas aan naar jouw primaire kleur */
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
}

/* Dropdown container */
.filter-box .select2-container--default .select2-dropdown {
    border-radius: 12px;
    border-color: #e5e7eb;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
    overflow: hidden;
}

/* Zoekveldje in dropdown */
.filter-box .select2-search--dropdown .select2-search__field {
    border-radius: 50px;
    border: 1px solid #e5e7eb;
    font-size: 13px;
    padding: 6px 10px;
}

/* Items */
.filter-box .select2-results__option {
    padding: 8px 10px;
    font-size: 14px;
}

/* Hover */
.filter-box .select2-results__option--highlighted {
    background-color: #2563eb !important;
    color: #fff !important;
}

/* Geselecteerd */
.filter-box .select2-results__option--selected {
    background-color: #eff6ff !important;
    color: #111827 !important;
}

/* ===========================
   ALS SELECT2 NIET LAADT → fallback styling
   =========================== */
.filter-box select {
    width: 100%;
    height: 44px;
    border-radius: 50px;
    border: 1px solid #e5e7eb;
    padding: 0 16px;
    font-size: 14px;
    background: #fff;
    appearance: none;
    color: #111827;
}

</style>
 