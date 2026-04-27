<?php
/*
Template Name: Landingspagina Template
*/
if (!defined('ABSPATH')) exit;
get_header();

while (have_posts()) :
    the_post();
    include get_stylesheet_directory() . '/template-parts/landing-template.php';
endwhile;

get_footer();
