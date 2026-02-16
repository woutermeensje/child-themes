<?php
if (!defined('ABSPATH')) exit;

if (!isset($query)) return;
?>

<div class="vp-job-listings">
<?php if ($query->have_posts()) : ?>
    <?php while ($query->have_posts()) : $query->the_post(); ?>

        <?php
        $logo_url = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');

        $location  = get_post_meta(get_the_ID(), '_vp_location', true);

        $companies = get_the_terms(get_the_ID(), 'vp_company');
        $job_types = get_the_terms(get_the_ID(), 'vp_job_type');
        ?>

        <div class="vp-job-card">
            
            <div class="vp-job-logo">
                <?php if ($logo_url): ?>
                    <img src="<?php echo esc_url($logo_url); ?>" alt="<?php the_title_attribute(); ?>">
                <?php else: ?>
                    <div class="vp-logo-placeholder"></div>
                <?php endif; ?>
            </div>

            <div class="vp-job-content">
               
                    <a href="<?php the_permalink(); ?>" class="vp-job-title"><?php the_title(); ?></a>
               

                <div class="vp-job-excerpt">
                    <?php echo wp_trim_words(get_the_excerpt(), 12); ?>
                </div>

                <div class="vp-job-tags">
                    <?php if ($companies && !is_wp_error($companies)) : ?>
                        <span class="vp-tag vp-company">
                            <?php echo esc_html($companies[0]->name); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($location) : ?>
                        <span class="vp-tag vp-location">
                            <?php echo esc_html($location); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($job_types && !is_wp_error($job_types)) : ?>
                        <span class="vp-tag vp-type">
                            <?php echo esc_html($job_types[0]->name); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="vp-job-date">
                <?php echo get_the_date('d-m-Y'); ?>
            </div>

        </div>

    <?php endwhile; wp_reset_postdata(); ?>
<?php else : ?>
    <p>Geen vacatures gevonden.</p>
<?php endif; ?>
</div>


<style>
.vp-job-listings {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.vp-job-card {

    background: #fff;
    padding: 24px;
    border-radius: 5px;
    border: 1px solid #DEDEDE; 
    box-shadow: 0 10px 40px -5px rgba(0,0,0,0.15);
    

}


.vp-job-card:hover {
    border: 1px solid #333; 
}

.vp-job-card{
  display: flex;
  align-items: flex-start;
  gap: 24px;
  position: relative;
}

.vp-job-logo{
  flex: 0 0 110px;
  width: 110px;
  height: 110px;

  margin-left: -72px;   /* 👈 25% van 110px */
  
  background: #ffffff;
  border: 1px solid #DEDEDE;
  border-radius: 12px;
  padding: 14px;

  display: flex;
  align-items: center;
  justify-content: center;

  box-sizing: border-box;
}

.vp-job-logo img{
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  display: block;
}

.vp-job-content{
  flex: 1;
}




.vp-logo-placeholder {
    width: 50px;
    height: 50px;
    background: #ddd;
    border-radius: 8px;
}

.vp-job-content {
    flex: 1;
}

a.vp-job-title {
    font-size: 20px !important;
    margin: 0 0 8px;
    font-family: Inter !important; 
    font-weight: 700 !important; 
    color: #333 !important; 
}

.vp-job-title a {
    text-decoration: none;
    color: #333;
}

.vp-job-excerpt {
    color: #666;
    margin-bottom: 14px;
}

.vp-job-tags {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.vp-job-tags {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.vp-tag {
    display: inline-block;
    padding: 6px 12px;
    background-color: white; /* jouw blauw */
    color: #333;
    font-size: 14px;
    border-radius: 999px;
    font-weight: 700;
    box-shadow: 0px 10px 40px -5px rgba(0,0,0,0.15);
    border: 1px solid #DEDEDE; 
}

.vp-job-date {
    position: absolute;
    right: 24px;
    top: 24px;
    font-size: 14px;
    color: #6aa0b5;
}

/* =========================
   Mobile / Tablet (clean + consistent)
   ========================= */

/* Tablet */
@media (max-width: 1024px){
  .vp-job-card{
    padding: 18px;
    gap: 16px;
    overflow: visible; /* voorkom afkappen van overlap */
  }

  .vp-job-logo{
    width: 72px;
    min-width: 72px;
    height: 72px;
    margin-left: -18px; /* ~25% van 72px */
    border-radius: 10px;
    padding: 10px;
  }

  .vp-job-logo img{
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
  }

  a.vp-job-title{
    font-size: 18px !important;
    line-height: 1.25;
  }

  .vp-job-excerpt{
    font-size: 14px;
    line-height: 1.4;
  }

  .vp-tag{
    font-size: 13px;
    padding: 6px 10px;
  }

  .vp-job-date{
    right: 18px;
    top: 18px;
    font-size: 13px;
  }
}

/* Mobile */
@media (max-width: 640px){
  .vp-job-listings{
    gap: 16px;
  }

  .vp-job-card{
    padding: 14px;
    gap: 12px;
    align-items: flex-start;
    overflow: visible; /* voorkom afkappen van overlap */
  }

  .vp-job-logo{
   display: none !important; 
  }

  .vp-job-logo img{
    
  }

  /* Datum minder "in de weg" op mobiel */
  .vp-job-date{
    position: static;
    margin-left: auto;
    font-size: 12px;
    line-height: 1;
    color: #6aa0b5;
    white-space: nowrap;
  }

  /* Title + excerpt compacter */
  a.vp-job-title{
    font-size: 16px !important;
    line-height: 1.25;
    display: inline-block;
    margin: 0 0 6px 0;
  }

  .vp-job-excerpt{
    margin-bottom: 10px;
    font-size: 13px;
    line-height: 1.35;
  }

  .vp-job-tags{
    gap: 8px;
  }

  .vp-tag{
    font-size: 12px;
    padding: 5px 9px;
  }
}

/* Extra small phones */
@media (max-width: 380px){
  .vp-job-card{
    padding: 12px;
    gap: 10px;
    overflow: visible;
  }

  .vp-job-logo{
    display: none;
  }

  a.vp-job-title{
    font-size: 15px !important;
  }

  .vp-tag{
    font-size: 11px;
    padding: 5px 8px;
  }
}


</style>
