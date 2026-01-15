<?php
/**
 * Plugin Name: Fondsen.org Organisaties Directory
 * Description: Shortcode [organisaties] die childpagina's onder /organisaties/ toont met filters (zoek, type organisatie, sector).
 * Version: 1.0.0
 * Author: Fondsen.org
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Fondsen_Organisaties_Directory {

    const SHORTCODE = 'organisaties';

    // Pas dit aan als jouw sector-taxonomy anders heet
    const TAX_SECTOR = 'job_sector';

    // Nieuwe taxonomy voor "type organisatie"
    const TAX_TYPE_ORG = 'org_type';

    public function __construct() {
        add_action( 'init', [ $this, 'register_taxonomy_org_type' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
        add_shortcode( self::SHORTCODE, [ $this, 'render_shortcode' ] );
    }

    public function register_taxonomy_org_type() {
        $labels = [
            'name'              => 'Type organisatie',
            'singular_name'     => 'Type organisatie',
            'search_items'      => 'Zoek types',
            'all_items'         => 'Alle types',
            'edit_item'         => 'Bewerk type',
            'update_item'       => 'Update type',
            'add_new_item'      => 'Nieuw type toevoegen',
            'new_item_name'     => 'Nieuwe type naam',
            'menu_name'         => 'Type organisatie',
        ];

        register_taxonomy(self::TAX_TYPE_ORG, ['page'], [
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rewrite'           => ['slug' => 'type-organisatie'],
        ]);
    }

    public function register_assets() {
        $handle = 'fondsen-organisaties-directory';
        $src    = plugin_dir_url(__FILE__) . 'assets/organisaties.js';

        wp_register_script($handle, $src, [], '1.0.0', true);
    }

    private function plugin_path($relative = '') {
        return plugin_dir_path(__FILE__) . ltrim($relative, '/');
    }

    private function get_parent_organisaties_id() {
        $page = get_page_by_path('organisaties');
        return ($page instanceof WP_Post) ? (int) $page->ID : 0;
    }

    private function sanitize_slugs_array($value) {
        $arr = is_array($value) ? $value : [];
        $arr = array_map('wp_unslash', $arr);
        $arr = array_map('sanitize_text_field', $arr);
        $arr = array_filter($arr);
        return array_values($arr);
    }

    public function render_shortcode($atts) {
        $parent_id = $this->get_parent_organisaties_id();
        if ( ! $parent_id ) {
            return '<p>Parentpagina /organisaties/ niet gevonden.</p>';
        }

        // --- Filters uit URL ---
        $search_query   = isset($_GET['org_search']) ? sanitize_text_field( wp_unslash($_GET['org_search']) ) : '';
        $selected_types = isset($_GET['org_type'])   ? $this->sanitize_slugs_array($_GET['org_type']) : [];
        $selected_sector= isset($_GET['org_sector']) ? $this->sanitize_slugs_array($_GET['org_sector']) : [];

        // Terms ophalen
        $types = get_terms([
            'taxonomy'   => self::TAX_TYPE_ORG,
            'hide_empty' => true,
        ]);

        $sectors = get_terms([
            'taxonomy'   => self::TAX_SECTOR,
            'hide_empty' => true,
        ]);

        // Query
        $args = [
            'post_type'      => 'page',
            'post_parent'    => $parent_id,
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ];

        if ( $search_query !== '' ) {
            $args['s'] = $search_query;
        }

        $tax_query = [ 'relation' => 'AND' ];

        if ( ! empty($selected_types) ) {
            $tax_query[] = [
                'taxonomy' => self::TAX_TYPE_ORG,
                'field'    => 'slug',
                'terms'    => $selected_types,
                'operator' => 'IN',
            ];
        }

        if ( ! empty($selected_sector) ) {
            $tax_query[] = [
                'taxonomy' => self::TAX_SECTOR,
                'field'    => 'slug',
                'terms'    => $selected_sector,
                'operator' => 'IN',
            ];
        }

        if ( count($tax_query) > 1 ) {
            $args['tax_query'] = $tax_query;
        }

        $query = new WP_Query($args);

        // Assets alleen laden als shortcode gebruikt wordt
        wp_enqueue_script('fondsen-organisaties-directory');

        // Data doorgeven aan JS (optioneel, handig voor uitbreiden)
        wp_localize_script('fondsen-organisaties-directory', 'FondsenOrgDir', [
            'autoSubmitDelay' => 350,
        ]);

        // Data beschikbaar maken voor templates
        $context = [
            'search_query'    => $search_query,
            'types'           => $types,
            'sectors'         => $sectors,
            'selected_types'  => $selected_types,
            'selected_sector' => $selected_sector,
            'query'           => $query,
        ];

        ob_start();

        $filter_template  = $this->plugin_path('templates/organisaties-filter.php');
        $listing_template = $this->plugin_path('templates/organisaties-listing.php');

        if ( file_exists($filter_template) ) {
            include $filter_template;
        } else {
            echo '<p>Template ontbreekt: organisaties-filter.php</p>';
        }

        if ( file_exists($listing_template) ) {
            include $listing_template;
        } else {
            echo '<p>Template ontbreekt: organisaties-listing.php</p>';
        }

        wp_reset_postdata();

        return ob_get_clean();
    }
}

new Fondsen_Organisaties_Directory();
