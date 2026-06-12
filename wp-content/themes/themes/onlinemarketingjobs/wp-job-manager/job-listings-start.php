<?php
if (!defined('ABSPATH')) exit;
?>
<div class="omj-listings-header">
    <?php if (!empty($jobs) && isset($jobs->found_posts) && $jobs->found_posts > 0): ?>
        <span class="omj-listings-count">
            <?php echo number_format_i18n($jobs->found_posts); ?> openstaande vacature<?php echo $jobs->found_posts !== 1 ? 's' : ''; ?>
        </span>
    <?php endif; ?>
</div>
<ul class="job_listings">
