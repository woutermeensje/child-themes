<?php
/**
 * Plugin Name: Fondsen.org – Organisaties Directory
 * Description: Directory + filters + load more voor organisaties (pages).
 * Version: 1.1.0
 */

if ( ! defined('ABSPATH') ) exit;

class Fondsen_Organisaties_Directory {

    const TAX_SECTOR   = 'job_sector';
    const TAX_TYPE_ORG = 'organization_type';

    public function __construct() {
        add_shortcode('fondsen_organisaties', [$this, 'render_shortcode']);

        add_action('wp_enqueue_scripts', [$this, 'register_assets']);

        add_action('wp_ajax_fondsen_org_dir_load_more', [$this, 'ajax_load_more']);
        add_action('wp_ajax_nopriv_fondsen_org_dir_load_more', [$this, 'ajax_load_more']);
    }

    /* ======================================================
     * Assets
     * ====================================================== */
    public function register_assets() {
        $handle = 'fondsen-organisaties-directory';

        wp_register_script(
            $handle,
            plugin_dir_url(__FILE__) . 'assets/organisaties.js',
            ['jquery'],
            '1.1.0',
            true
        );

        wp_localize_script($handle, 'FOND_DIR', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('fond_dir_nonce'),
        ]);
    }

    /* ======================================================
     * Shortcode
     * ====================================================== */
    public function render_shortcode($atts) {

        wp_enqueue_script('fondsen-organisaties-directory');

        $atts = shortcode_atts([
            'per_page' => 30,            // 3 × 10
            'sector'   => '',            // "klimaat,gezondheid"
            'type'     => '',            // "ngo,stichting"
        ], $atts, 'fondsen_organisaties');

        // GET filters hebben voorrang
        $search = isset($_GET['org_search']) ? sanitize_text_field($_GET['org_search']) : '';

        $sector_selected = isset($_GET['org_sector'])
            ? (array) $_GET['org_sector']
            : array_filter(array_map('trim', explode(',', $atts['sector'])));

        $type_selected = isset($_GET['org_type'])
            ? (array) $_GET['org_type']
            : array_filter(array_map('trim', explode(',', $atts['type'])));

        $query = new WP_Query(
            $this->build_query_args([
                'paged'      => 1,
                'per_page'   => (int) $atts['per_page'],
                's'          => $search,
                'org_sector' => $sector_selected,
                'org_type'   => $type_selected,
            ])
        );

        $data = [
            'search'           => $search,
            'sectors_selected' => $this->sanitize_slugs_array($sector_selected),
            'types_selected'   => $this->sanitize_slugs_array($type_selected),
            'posts'            => $query->posts,
            'max_pages'        => (int) $query->max_num_pages,
            'per_page'         => (int) $atts['per_page'],
        ];

        ob_start();


                // Terms ophalen voor dropdowns/multiselect
        $types = get_terms([
        'taxonomy'   => self::TAX_TYPE_ORG,
        'hide_empty' => false,
        ]);

        $sectors = get_terms([
        'taxonomy'   => self::TAX_SECTOR,
        'hide_empty' => false,
        ]);

        // Variabelen die de templates verwachten
        $search_query    = $data['search'] ?? '';
        $selected_types  = $data['types_selected'] ?? [];
        $selected_sector = $data['sectors_selected'] ?? [];


        include $this->plugin_path('templates/organisaties-filter.php');
        include $this->plugin_path('templates/organisaties-listing.php');

        return ob_get_clean();
    }

    /* ======================================================
     * AJAX Load More
     * ====================================================== */
    public function ajax_load_more() {
        check_ajax_referer('fond_dir_nonce', 'nonce');

        $page     = isset($_POST['page']) ? (int) $_POST['page'] : 1;
        $per_page = isset($_POST['per_page']) ? (int) $_POST['per_page'] : 30;

        $query = new WP_Query(
            $this->build_query_args([
                'paged'      => $page,
                'per_page'   => $per_page,
                's'          => sanitize_text_field($_POST['org_search'] ?? ''),
                'org_sector' => (array) ($_POST['org_sector'] ?? []),
                'org_type'   => (array) ($_POST['org_type'] ?? []),
            ])
        );

        ob_start();
        foreach ($query->posts as $post) {
            setup_postdata($post);
            include $this->plugin_path('templates/organisaties-grid-item.php');
        }
        wp_reset_postdata();

        wp_send_json_success([
            'html'      => ob_get_clean(),
            'has_more'  => $page < (int) $query->max_num_pages,
            'next_page' => $page + 1,
        ]);
    }

    /* ======================================================
     * Query helpers
     * ====================================================== */
    private function build_query_args($params = []) {

        $parent_id = $this->get_parent_organisaties_id();

        $args = [
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'post_parent'    => $parent_id,
            'posts_per_page' => max(1, (int) ($params['per_page'] ?? 30)),
            'paged'          => max(1, (int) ($params['paged'] ?? 1)),
        ];

        if (!empty($params['s'])) {
            $args['s'] = sanitize_text_field($params['s']);
        }

        $tax_query = [];

        $sector = $this->sanitize_slugs_array($params['org_sector'] ?? []);
        if ($sector) {
            $tax_query[] = [
                'taxonomy' => self::TAX_SECTOR,
                'field'    => 'slug',
                'terms'    => $sector,
            ];
        }

        $type = $this->sanitize_slugs_array($params['org_type'] ?? []);
        if ($type) {
            $tax_query[] = [
                'taxonomy' => self::TAX_TYPE_ORG,
                'field'    => 'slug',
                'terms'    => $type,
            ];
        }

        if ($tax_query) {
            $args['tax_query'] = $tax_query;
        }

        return $args;
    }

    private function sanitize_slugs_array($values) {
        if (!is_array($values)) return [];
        return array_values(array_filter(array_map('sanitize_title', $values)));
    }

    private function get_parent_organisaties_id() {
        $page = get_page_by_path('organisaties');
        return $page ? (int) $page->ID : 0;
    }

    private function plugin_path($relative) {
        return plugin_dir_path(__FILE__) . ltrim($relative, '/');
    }
}

new Fondsen_Organisaties_Directory();
