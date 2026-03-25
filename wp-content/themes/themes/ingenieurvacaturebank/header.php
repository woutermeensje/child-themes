<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e('Skip to content', 'ingenieurvacaturebank'); ?></a>
<?php
if (!function_exists('elementor_theme_do_location') || !elementor_theme_do_location('header')) {
    include get_stylesheet_directory() . '/template-parts/header.php';
}
