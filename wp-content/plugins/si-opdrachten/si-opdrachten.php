<?php
/**
 * Plugin Name: Studentinhuren.nl - Freelance Opdrachten Listings
 * Description: Custom listings + filter + templates voor freelance opdrachten.
 * Version: 0.1.0
 */

if (!defined('ABSPATH')) exit;

define('SI_OPD_PATH', plugin_dir_path(__FILE__));
define('SI_OPD_URL', plugin_dir_url(__FILE__));

require_once SI_OPD_PATH . 'includes/cpt-opdracht.php';
require_once SI_OPD_PATH . 'includes/template-loader.php';
require_once SI_OPD_PATH . 'includes/shortcode-opdrachten.php';

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('si-opdrachten', SI_OPD_URL . 'assets/css/opdrachten.css', [], '0.1.0');
    wp_enqueue_script('si-opdrachten', SI_OPD_URL . 'assets/js/opdrachten.js', [], '0.1.0', true);
});

add_filter('single_template', function ($single) {
    if (is_singular('si_opdracht')) {
        $path = si_opd_get_template_path('single-opdracht.php');
        if ($path) return $path;
    }
    return $single;
});
