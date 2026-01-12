<?php
if (!defined('ABSPATH')) exit;

wp_enqueue_script('wp-job-manager-ajax-filters');
do_action('job_manager_job_filters_before', $atts);

// Veilige fallbacks voor zoekvelden
$keywords = isset($_GET['search_keywords']) ? sanitize_text_field($_GET['search_keywords']) : '';
$location = isset($_GET['search_location']) ? sanitize_text_field($_GET['search_location']) : '';

// ✅ Vul $selected met waarden vanuit: $_GET > $_POST > Shortcode
$selected = [
    'job_company'           => [],
    'job_tag'               => [],
    'job_sector'            => [],
    'job_types'             => [],
    'skills'                => [], // <— vervangen
    'job_listing_category'  => [],
];

$shortcode_atts = shortcode_atts([
    'job_company'           => '',
    'job_tag'               => '',
    'job_sector'            => '',
    'job_listing_type'      => '',
    'skills'                => '', // <— vervangen
    'job_listing_category'  => '',
], $atts);

foreach ($selected as $key => &$value) {
    $shortcode_key = $key === 'job_types' ? 'job_listing_type' : $key;

    if (!empty($_GET[$key])) {
        $value = (array) $_GET[$key];
    } elseif (!empty($_POST['filter_' . $key])) {
        $value = (array) $_POST['filter_' . $key];
    } elseif (!empty($shortcode_atts[$shortcode_key])) {
        $value = explode(',', sanitize_text_field($shortcode_atts[$shortcode_key]));
    }
}
?>

<form class="job_filters">
    <?php do_action('job_manager_job_filters_start', $atts); ?>

    <div class="filter-header" style="padding: 0 20px 10px 20px;">
        <h2>Doorzoek alle Opdrachten, Vacatures en Projecten!</h2>
        <p>Of ontvang de laatste <a href="https://student-inhuren.nl/updates/" target="_blank" class="unstyled-newsletter-link">vacatures en opdrachten</a> in de e-mail!</p>
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

        <div class="job_sector">
            <select name="filter_job_sector" id="filter_job_sector" class="job_sector" data-placeholder="Categorie">
                <option value=""><?php _e('Selecteer sector', 'wp-job-manager'); ?></option>
                <?php foreach (get_terms(['taxonomy' => 'job_sector', 'hide_empty' => true]) as $term) : ?>
                    <option value="<?php echo esc_attr($term->slug); ?>" <?php selected(in_array($term->slug, $selected['job_sector'])); ?>>
                        <?php echo esc_html($term->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="job_skills">
            <select name="filter_skills" id="filter_skills" class="job_skills" data-placeholder="Skills">
                <option value=""><?php _e('Selecteer skill', 'wp-job-manager'); ?></option>
                <?php foreach (get_terms(['taxonomy' => 'skills', 'hide_empty' => true]) as $term) : ?>
                    <option value="<?php echo esc_attr($term->slug); ?>" <?php selected(in_array($term->slug, $selected['skills'])); ?>>
                        <?php echo esc_html($term->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="job_company">
            <select name="filter_job_company" id="filter_job_company" class="job_company" data-placeholder="Organisatie">
                <option value=""><?php _e('💼 Selecteer organisatie', 'wp-job-manager'); ?></option>
                <?php foreach (get_terms(['taxonomy' => 'job_company', 'hide_empty' => true]) as $term) : ?>
                    <option value="<?php echo esc_attr($term->slug); ?>" <?php selected(in_array($term->slug, $selected['job_company'])); ?>>
                        <?php echo esc_html($term->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <style>
            .job_company {
                display: none !important; 
            }
        </style>

        <div class="job_tag">
            <select name="filter_job_tag" id="filter_job_tag" class="job_tag" data-placeholder="Tags">
                <option value=""><?php _e('Selecteer tag', 'wp-job-manager'); ?></option>
                <?php foreach (get_terms(['taxonomy' => 'job_tag', 'hide_empty' => true]) as $term) : ?>
                    <option value="<?php echo esc_attr($term->slug); ?>" <?php selected(in_array($term->slug, $selected['job_tag'])); ?>>
                        <?php echo esc_html($term->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

    </div>
</form>

<?php do_action('job_manager_job_filters_after', $atts); ?>

<script>
jQuery(document).ready(function($) {
    // Initialiseer Select2
    $('#filter_skills, #filter_job_types, #filter_job_company, #filter_job_tag, #filter_job_sector').select2({
        width: '100%',
        allowClear: true,
        placeholder: function() {
            return $(this).data('placeholder');
        }
    });

    // Trigger AJAX filter update bij wijziging van selectievakjes
    $('#filter_skills, #filter_job_types, #filter_job_company, #filter_job_tag, #filter_job_sector').on('change', function() {
        $('.job_filters').trigger('submit');
    });

    // Zorg dat formulierverzending de WPJM-filters activeert
    $('.job_filters').on('submit', function(e) {
        e.preventDefault();
        if (typeof job_manager_job_filters !== 'undefined') {
            job_manager_job_filters.filter_jobs(); // ✅ Bel de officiële WPJM functie
        } else {
            console.warn('job_manager_job_filters niet gevonden!');
        }
    });
});
</script>




<style>
    .filter-box > .job_category {
    flex: 1 1 18%;
    min-width: 160px;
}

.job_category select {
    width: 100%;
}


/* ===== FILTER KNOP STIJLING ZOALS DE SCREENSHOT ===== */
.select2-container--default .select2-selection--single {
    border-radius: 50px !important;
    border: 1px solid #e3cfe2  !important;
    background-color: #fcfbfa;
    padding: 8px 16px;
    height: auto;
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 10px 40px -5px #ddd inset;
    transition: all 0.2s ease;
}

/* Placeholder dikgedrukt */
.select2-selection__placeholder {
    color: #000;
    font-weight: 700;
    font-family: 'Poppins', sans-serif;
}

/* Tekst binnen geselecteerde filter */
.select2-selection__rendered {
    font-weight: 700;
    font-family: 'Poppins', sans-serif;
    padding-left: 2px;
}

/* Pijl rechts: zwart, iets groter */
.select2-selection__arrow b {
    border-color: #111 transparent transparent transparent !important;
    border-width: 6px 5px 0 5px !important;
}

/* Placeholder styling van de multiselect */
span.select2-selection__placeholder {
    font-weight: 700 !important; 
    font-family; Balgin Bold; 
    color: #333333 !important; 
}


</style>

<style>

/* Zet de filter-box netjes in een rij met gelijke breedte */
.filter-box {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    padding: 20px;
}

/* Alle filter-items krijgen gelijke breedte */
.filter-box > div {
    flex: 1 1 calc(100% / 6 - 16px)  !important;
    min-width: 120px !important;
}

/* Select2 containers vullen de volledige breedte */
.select2-container {
    width: 100% !important;
}

/* ========== ALGEMENE INPUT & SELECT2-STYLING ========== */
.job_filters select,
.select2-container .select2-selection--single,
.select2-container .select2-selection--multiple {
    background-color: white;
    padding: 12px 14px;
    min-height: 44px;
    font-size: 15px;
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
    width: 100%;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.10);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.select2-container--default .select2-selection--single .select2-selection__rendered,
.select2-container--default .select2-selection--multiple .select2-selection__rendered {
    color: #333;
    line-height: 1.3;
    padding-left: 2px;
}

/* Placeholder-stijl */
.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #777;
    font-weight: 400;
}

/* Tags binnen multiple select */
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #0a6b8d;
    color: white;
    border: none;
    border-radius: 2px;
    padding: 2px 6px;
    margin: 2px;
    font-size: 13px;
}

/* Pijl (dropdown icoon) */
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 100%;
    right: 10px;
}

/* Focus state */
.select2-container--default .select2-selection--single:focus,
.select2-container--default .select2-selection--multiple:focus {
    border-color: #0a6b8d !important;
    box-shadow: 0 0 0 2px rgba(10, 107, 141, 0.2);
    outline: none;
}



.filter-box select,
.filter-box .select2-container {
    margin-top: 4px;
}
label {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 4px;
    display: block;
    font-family: 'Poppins', sans-serif;
}


@media (max-width: 768px) {
    .filter-box {
        display: flex;
        flex-direction: column;
        gap: 16px;
        padding: 20px;
    }

    .filter-box > div {
        width: 100% !important;
    }

    .select2-container {
        width: 100% !important;
    }

    .search-basic {
        flex-direction: column;
        gap: 16px;
        padding: 0 20px;
    }

    .search-basic > div {
        width: 100%;
        max-width: 100%;
    }

    .search-basic input[type="text"] {
        width: 100%;
    }
}




</style>


<style>

.filter-box {
    padding: 20px;
    display: flex; 
}

.categories_filter {
    width: 50%; 

}

.job_types {
    width: 50%; 
}
/* Container blijft 100% breed */
.job_filters {
    
    padding: 20px 0;
    margin: 0 auto;
    margin-top: 20px;
    margin-bottom: 20px;
    background-color: white;
    border: 1px solid #3A89FF;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);

}

.filter-header {
    padding: 0 20px 10px 20px;
}

.filter-header p {
    font-family: Poppins; 
    font-size: 15px;
    color: #333333;
    margin-bottom: 10px;
    margin-top: 10px;
}

.filter-header h2 {
    font-family: 'Inter', sans-serif;
    font-size: 25px;
    color: #333333;
    margin-bottom: 15px;
    display: inline;
    font-weight: bold;
    border-radius: 2px;
}

body .filter-header a.unstyled-newsletter-link {
    color: #3A89FF;
    text-decoration: none;
    font-weight: 400;
    font-family: "Poppins", sans-serif;
}


body .filter-header a.unstyled-newsletter-link:hover {
    color: var(--color-roze) !important;
    text-decoration: none; 
}


/* Flexbox voor de twee velden */
.search-basic {
    display: flex; 
    justify-content: left;
    gap: 20px;
    padding: 0 20px;
}

/* Beide velden naast elkaar */
.search_location,
.search_keywords {
    flex-basis: 50%;
    max-width: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
}

@media (max-width: 768px) {
    .search-basic {
        flex-direction: column;
        
    }

    .search-basic > div {
        max-width: 100%;
        flex-basis: 1000%;
    }
}

/* Inputvelden gestyled met schaduw en icon ruimte */
.search-basic input[type="text"] {
    width: 100%;
    padding: 12px 14px 12px 38px; /* ruimte voor icoon links */
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 0;
    background-color: white;
    color: #222;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.search-basic input[type="text"]::placeholder {
    color: #777;
}

/* Focus state */
.search-basic input[type="text"]:focus {
    outline: none;
    border-color: #0a6b8d;
    box-shadow: 0 2px 8px rgba(10, 107, 141, 0.25);
}

/* Vergrootglas icoon */
.search_keywords::before {
    content: '🔍';
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
    color: #0a6b8d;
    pointer-events: none;
}

/* Locatie icoon */
.search_location::before {
    content: '📍';
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
    color: #0a6b8d;
    pointer-events: none;
}



    .categories-box {
        width: 90%;
        margin-top: 24px;
        margin-bottom: 24px;
        margin-left: auto;
        margin-right: auto;
    }

    .categorie {     
    }

.categorie a {
        font-family: Poppins, sans-serif;
        font-weight: 700;
        font-size: 14px; 
        color: var(--color-primary) !important;
        border: 1px solid var(--color-primary);
        background-color: var(--color-tertiary);
        border-radius: 50px;
        padding: 10px 14px;
        cursor: pointer; 
        margin-right: 8px;
        margin-left: 8px;
}

.categorie a:hover {
  background-color: var(--color-primary);
  color: white !important;
    border: 2px solid var(--color-tertiary);

}

</style>