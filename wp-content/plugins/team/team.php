<?php
/**
 * Plugin Name: Team
 * Description: Beheer teamleden en partners en toon ze met de shortcode [team].
 * Version: 1.0.0
 * Author: Modulairehuisvesting
 * Text Domain: mh-team
 */

if (!defined('ABSPATH')) exit;

define('MH_TEAM_PATH', plugin_dir_path(__FILE__));
define('MH_TEAM_URL', plugin_dir_url(__FILE__));
define('MH_TEAM_VERSION', '1.0.0');

require_once MH_TEAM_PATH . 'includes/cpt-team-member.php';
require_once MH_TEAM_PATH . 'includes/meta-fields.php';
require_once MH_TEAM_PATH . 'includes/shortcode-team.php';

add_action('wp_enqueue_scripts', function () {
    $style_path = MH_TEAM_PATH . 'assets/css/team.css';
    $dependencies = [];

    if (wp_style_is('child-style', 'enqueued') || wp_style_is('child-style', 'registered')) {
        $dependencies[] = 'child-style';
    }

    wp_enqueue_style(
        'mh-team',
        MH_TEAM_URL . 'assets/css/team.css',
        $dependencies,
        file_exists($style_path) ? filemtime($style_path) : MH_TEAM_VERSION
    );
}, 20);

register_activation_hook(__FILE__, function () {
    if (function_exists('mh_team_register_post_type')) {
        mh_team_register_post_type();
    }

    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});
