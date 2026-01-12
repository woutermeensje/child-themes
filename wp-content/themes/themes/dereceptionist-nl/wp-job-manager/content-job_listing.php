
<li class="job-listing-simple" <?php job_listing_class(); ?>>
    <div class="job-logo">
        <?php the_company_logo(); ?>
    </div>
    <div class="job-details">
        <div class="job-title-line">
            <h2 class="job-title">
                <a class="job-title-link" href="<?php the_job_permalink(); ?>"><?php wpjm_the_job_title(); ?></a>
            </h2>
            <span class="job-date"><?php echo get_the_date('d-m-Y'); ?></span>
        </div>
        <div class="job-meta">
            <?php
            $terms = wp_get_post_terms(get_the_ID(), 'job_company');
            if (!empty($terms) && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    echo '<span class="company-name">' . esc_html($term->name) . '</span>';
                }
            }
            ?>
            <span class="job-location"><?php the_job_location(); ?></span>
            <span class="job-type">
                <?php if (get_option('job_manager_enable_types')) : ?>
                    <?php $types = wpjm_get_the_job_types(); ?>
                    <?php if (!empty($types)) : foreach ($types as $type) : ?>
                        <?php echo esc_html($type->name); ?>
                    <?php endforeach; endif; ?>
                <?php endif; ?>
            </span>
        </div>
        <div class="job-description">
            <?php echo wp_trim_words(get_the_excerpt(), 12, '...'); ?>
        </div>
    </div>
</li>


<style>
.job-listing-simple {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 16px;
    margin: 0 auto 28px auto;
    width: 90%;
    border: 1px solid var(--color-bg);
    background-color: var(--color-bg);
    border-radius: 5px;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
    transition: all 0.2s ease-in-out;
}

.job-listing-simple:hover { 
    border: 1px solid var(--color-primary);
}

/* Logo blok */
.job-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100px;
    height: 100px;
    margin-left: -50px;
    background-color: var(--color-bg);
}

.job-logo img {
    width: 100px;
    height: 100px;
    object-fit: contain;
    border-radius: 5px;
    padding: 6px;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
    border: 1px solid var(--color-border);
    transition: all 0.2s ease-in-out;
    background-color: var(--color-bg);
}

/* Details blok */
.job-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.job-title {
    margin-bottom: 5px;
}

/* Vacaturetitel – link styling overrulen */
.job-title .job-title-link {
    color: var(--color-text);
    text-decoration: none;
    transition: color 0.2s ease-in-out;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 20px;
}

.job-title .job-title-link:hover {
    
}


/* Meta info */
.job-meta {
    margin-bottom: 5px;
    margin-top: 5px;
}

.company-name {
    font-family: Poppins, sans-serif;
    font-weight: 700;
    font-size: 12px; 
    color: #2C2C31;
    border: 1px solid #FCAF2B;
    background-color: #FEEAD5;
    border-radius: 5px;
    padding: 5px 10px;
    cursor: pointer; 
    margin-right: 5px;
}

a.google_map_link {
    font-family: Poppins, sans-serif;
    font-weight: 700;
    font-size: 12px; 
    color: #2C2C31;
    border: 1px solid #FCAF2B;
    background-color: #FEEAD5;
    border-radius: 5px;
    padding: 5px 10px;
    cursor: pointer;
    margin-right: 5px; 
    text-decoration: none; 
}

.job-type {
    font-family: Poppins, sans-serif;
    font-weight: 700;
    font-size: 12px; 
    color: #2C2C31;
    border: 1px solid #FCAF2B;
    background-color: #FEEAD5;
    border-radius: 5px;
    padding: 5px 10px;
    cursor: pointer; 
    margin-right: 5px;
}

/* Beschrijving */
.job-description {
    font-size: 14px; 
    line-height: 1.7;
    color: var(--color-text);
    font-family: Poppins, sans-serif;
    max-width: 100%;
    font-weight: 200;
}

.job-title-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-right: 10px;
}

.job-date {
    font-family: Poppins, sans-serif;
    font-size: 12px;
    color: var(--color-primary);
    font-weight: 200;
}

/* Responsief design */
@media only screen and (max-width: 768px) {
    .job-listing-simple {
        flex-direction: column;
        align-items: flex-start;
        padding: 20px;
    }

    .job-logo {
        margin-left: 0;
    }

    .job-title {
        font-size: 1.25rem;
    }

    .job-meta {
        font-size: 0.95rem;
    }

    .job-date {
        display: none; 
    }
}
</style>
