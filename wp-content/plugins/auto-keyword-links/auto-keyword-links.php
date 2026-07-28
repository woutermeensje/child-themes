<?php
/**
 * Plugin Name: Auto Keyword Links
 * Description: Linkt automatisch het eerste voorkomen van ingestelde keywords in de content naar een vaste URL. Beheer via Instellingen > Keyword Links.
 * Version: 0.1.0
 */

if (!defined('ABSPATH')) exit;

define('AKL_PATH', plugin_dir_path(__FILE__));
define('AKL_URL', plugin_dir_url(__FILE__));

require_once AKL_PATH . 'includes/settings-page.php';
require_once AKL_PATH . 'includes/content-filter.php';
