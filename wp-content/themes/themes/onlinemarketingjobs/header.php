<?php
/**
 * Onlinemarketingjobs.nl header override.
 *
 * Keeps Hello Elementor's document setup, then renders the child-theme
 * navigation directly so the full-width header is not dependent on an
 * Elementor theme-builder header or shortcode placement.
 */
if (!defined('ABSPATH')) {
    exit;
}

$viewport_content = apply_filters('hello_elementor_viewport_content', 'width=device-width, initial-scale=1');
$enable_skip_link  = apply_filters('hello_elementor_enable_skip_link', true);
$skip_link_url     = apply_filters('hello_elementor_skip_link_url', '#content');
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="<?php echo esc_attr($viewport_content); ?>">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<?php if ($enable_skip_link) : ?>
<a class="skip-link screen-reader-text" href="<?php echo esc_url($skip_link_url); ?>"><?php esc_html_e('Skip to content', 'hello-elementor'); ?></a>
<?php endif; ?>

<?php
if (!function_exists('hello_elementor_display_header_footer') || hello_elementor_display_header_footer()) {
    include get_stylesheet_directory() . '/template-parts/header.php';
}
