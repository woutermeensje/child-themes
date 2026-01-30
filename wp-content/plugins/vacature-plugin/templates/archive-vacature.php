<?php
if (!defined('ABSPATH')) exit;

get_header();

echo '<div class="vpjobs-wrap" style="max-width:1100px;margin:0 auto;padding:20px;">';
echo '<h1 style="margin:0 0 16px 0;">Vacatures</h1>';

// Je kunt hier óf een shortcode renderen, óf direct een WP_Query doen.
// Shortcode is het makkelijkst + hergebruik filter UI:
echo do_shortcode('[vacature_plugin per_page="12"]');

echo '</div>';

get_footer();