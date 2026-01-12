<?php

/**
 * Single job listing widget content.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/content-widget-job_listing.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @version     1.31.1
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
global $post;
?>
<li <?php job_listing_class(); ?>>
    <a href="<?php the_job_permalink(); ?>">
        <div class="row col-no-gutter">
            <div class="wpb_row row-inner">
                <div class="wpb_column col-lg-4">
                    <div class="company-logo-wrapper">
                        <?php the_post_thumbnail(); ?>
                    </div>
                </div>
                <div class="wpb_column col-lg-8">
                    <div class="job_listing_content">
                        <h3 class="h6"><?php wpjm_the_job_title(); ?></h3>
                        <ul class="job_meta">
                            <li>
                                <i class="fa fa-map-marker"></i> <?php the_job_location(false); ?>
                            </li>
                            <?php if (display_tax_terms('job_listing_type', $post->ID)) { ?>
                                <li>
                                    <i class="fa fa-clock"></i> <?php echo display_tax_terms('job_listing_type', $post->ID); ?>
                                </li>
                            <?php } ?>
                            <li>
                                <i class="fa fa-calendar"></i> <?php the_job_publish_date(); ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </a>
</li>