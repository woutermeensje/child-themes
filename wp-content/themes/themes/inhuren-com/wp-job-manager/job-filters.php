<?php
if (!defined('ABSPATH')) exit;

wp_enqueue_script('wp-job-manager-ajax-filters');

do_action('job_manager_job_filters_before', $atts);
?>

<form class="job_filters">
    <?php do_action('job_manager_job_filters_start', $atts); ?>

    <div class="filter-header" style="padding: 0 20px 10px 20px;">
        <h2>Bekijk alle Vacatures & Opdrachten op Inhuren.com!</h2>
        <p>
            Of schrijf je in voor de <a href="https://inhuren.com/updates/" target="_blank" class="unstyled-newsletter-link">automatische updates</a>!
        </p>
    </div>
    

    <div class="search-basic">
        <?php do_action('job_manager_job_filters_search_jobs_start', $atts); ?>

        <div class="search_keywords">
            <input type="text" name="search_keywords" id="search_keywords" placeholder="Functienaam, sector of onderwerp.." value="<?php echo esc_attr($keywords); ?>" />
        </div>

        <div class="search_location">
            <input type="text" name="search_location" id="search_location" placeholder="Stad of plaats" value="<?php echo esc_attr($location); ?>" />
        </div>

       

        <?php
        // ✅ Voeg verborgen categorievelden toe als de shortcode categories bevat
        if (!empty($categories)) :
            foreach ($categories as $category) :
                echo '<input type="hidden" name="search_categories[]" value="' . esc_attr(sanitize_title($category)) . '" />';
            endforeach;
        endif;
        ?>


<?php
if (!empty($atts['bedrijf'])) :
    $bedrijven = explode(',', $atts['bedrijf']);
    foreach ($bedrijven as $bedrijf) :
        echo '<input type="hidden" name="filter_job_company" value="' . esc_attr(sanitize_title($bedrijf)) . '" />';
    endforeach;
endif;
?>

        <?php do_action('job_manager_job_filters_search_jobs_end', $atts); ?>

    
    </div>

     
</form>



<?php do_action('job_manager_job_filters_after', $atts); ?>






<style>
/* Container blijft 100% breed */
.job_filters {
    width: 90%;
    padding: 20px 0;
    margin: 0 auto;
    margin-top: 20px;
    margin-bottom: 20px;
    background-color: white;
    border: 1px solid #0a6b8d;
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
    font-family: Balgin Bold; 
    font-size: 25px;
    color: #333333;
    margin-bottom: 15px;
    display: inline;
    background: linear-gradient(transparent 60%, #0456AB66 60%);
    font-weight: bold;
    border-radius: 2px;
}

body .filter-header a.unstyled-newsletter-link {
    color: #0a6b8d;
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