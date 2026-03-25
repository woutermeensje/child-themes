<?php
/**
 * Child theme header.php — vervangt Hello Elementor's header.php volledig.
 * Ons navigatie-menu wordt hier direct ingeladen zodat het op elke pagina
 * verschijnt, zonder afhankelijkheid van hooks of filters.
 */
if (!defined('ABSPATH')) exit;

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
// Toon ons custom menu ALLEEN als Elementor Pro geen eigen header heeft ingesteld.
// Als elementor_theme_do_location('header') true geeft, gebruikt de pagina een
// Elementor Pro header-template en laten we die zijn werk doen.
if (!function_exists('elementor_theme_do_location') || !elementor_theme_do_location('header')) {
    include get_stylesheet_directory() . '/template-parts/header.php';
}
?>
