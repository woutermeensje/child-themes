<li class="job-listing-simple" <?php job_listing_class(); ?>>
    <div class="job-details">
        <div class="job-title-line">
            <h2 class="job-title">
                <a href="<?php the_job_permalink(); ?>"><?php wpjm_the_job_title(); ?></a>
            </h2>
        </div>

          <div class="job-description">
<?php
$intro = get_post_meta( get_the_ID(), '_intro_text', true );
// fallback op oud key indien ooit zonder underscore opgeslagen
if ( ! $intro ) {
    $intro = get_post_meta( get_the_ID(), 'intro_text', true );
}
?>
<?php if ( ! empty( $intro ) ) : ?>
    <div class="job-intro">
        <?php echo esc_html( wp_trim_words( wp_strip_all_tags( $intro ), 75, '…' ) ); ?>
    </div>
<?php else : ?>
    <div class="job-intro">
        <?php echo esc_html( wp_trim_words( get_the_excerpt(), 20, '…' ) ); ?>
    </div>
<?php endif; ?>
        </div>

        <div class="meta-informatie">
                <span class="job-date">🗓️ <?php echo get_the_date('d-m-Y'); ?></span> |
                 <span class="job-location">📍<?php the_job_location(); ?></span> |
                  <span class="job-type">⏱️ 
                <?php if (get_option('job_manager_enable_types')) : ?>
                    <?php $types = wpjm_get_the_job_types(); ?>
                    <?php if (!empty($types)) :
                        foreach ($types as $type) :
                            echo esc_html($type->name);
                        endforeach;
                    endif; ?>
                <?php endif; ?>
            </span>
        </div>
    </div>
</li>


<style>
/* ===== Grid: 3 naast elkaar ===== */
ul.job_listings {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 20px;
    padding-left: 0; /* list-style spacing weg */
}

/* Kaart / kaartje */
.job-listing-simple {
    list-style: none;
    display: block;
    padding: 16px;
    margin: 0; /* grid regelt spacing */
    width: 100%; /* geen vaste breedte in grid */
    border: 1px solid var(--color-bg);
    background-color: var(--color-bg);
    border-radius: 0px;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
    transition: border-color 0.2s ease-in-out, transform 0.2s ease-in-out;
}

.job-listing-simple:hover { 
    border-color: var(--color-primary);
    transform: translateY(-2px);
}

/* Details blok */
.job-details {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* Titel */
.job-title {
    font-size: 24px;
    line-height: 1.5;
    color: var(--color-text);
    margin: 0 0 4px 0;
}

.job-intro {
    font-family: Poppins; 
    font-size: 15px;   
}

.job-title a {
    color: var(--color-text);
    text-decoration: none;
    transition: color 0.2s ease-in-out;
    font-family: Balgin Bold; 
}

.job-title a:hover {
    color: var(--color-primary);
}

/* Titel + datum regel */
.job-title-line {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 12px;
}

.job-date {
    font-family: Poppins, sans-serif;
    font-size: 14px;
    color: #333333;
    font-weight: 400;
}

a.google_map_link {
    font-family: Poppins, sans-serif;
    font-size: 14px;
    color: #333333;
    font-weight: 400
    text-decoration: none !important; 
}

span.job-location {
       font-family: Poppins, sans-serif;
    font-size: 14px;
    color: #333333;
    font-weight: 400;
}

/* Meta */

.meta-informatie {
    margin-top: 12px; 
}


span.job-type {
    font-family: Poppins, sans-serif;
    font-size: 14px;
    color: #333333;
    font-weight: 400;
}


/* Inleidende tekst */
.job-description {
    font-size: 14px; 
    line-height: 1.7;
    color: var(--color-text);
    font-family: Poppins, sans-serif;
    font-weight: 300;
    margin-top: 12px; 
}

/* ===== Responsief ===== */
@media (max-width: 1024px) {
    ul.job_listings {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    ul.job_listings {
        grid-template-columns: 1fr;
    }
    .job-date { display: none; }
}
</style>
