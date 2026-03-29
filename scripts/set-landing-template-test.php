<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/wp-load.php';

if (!defined('ABSPATH')) {
    fwrite(STDERR, "WordPress is niet geladen.\n");
    exit(1);
}

if (!function_exists('get_page_by_path') || !function_exists('update_post_meta')) {
    fwrite(STDERR, "Benodigde WordPress functies ontbreken.\n");
    exit(1);
}

$slugs = [
    'werkstudent-ai',
    'excel-specialist',
    'gastvrouw-inhuren',
    'logo-laten-maken',
];

$template = 'page-landing.php';

foreach ($slugs as $slug) {
    $page = get_page_by_path($slug, OBJECT, 'page');

    if (!$page instanceof WP_Post) {
        echo "Niet gevonden: {$slug}\n";
        continue;
    }

    update_post_meta($page->ID, '_wp_page_template', $template);
    echo "Bijgewerkt: {$slug} (ID {$page->ID}) -> {$template}\n";
}
