<?php
/**
 * Plugin Name: Fondsen.org – Organisaties Directory
 * Description: Directory + filters + load more voor organisaties (pages).
 * Version: 1.1.1
 */

if (!defined('ABSPATH')) exit;

class Fondsen_Organisaties_Directory {

    const TAX_SECTOR   = 'job_sector';
    const TAX_TYPE_ORG = 'organization_type';

    const ASSET_HANDLE = 'fondsen-organisaties-directory';
    const NONCE_ACTION = 'fond_dir_nonce';

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

        // CSS (optioneel, maar vrijwel altijd handig)
        wp_register_style(
            self::ASSET_HANDLE,
            plugin_dir_url(__FILE__) . 'assets/organisaties.css',
            [],
            '1.1.1'
        );

        // JS
        wp_register_script(
            self::ASSET_HANDLE,
            plugin_dir_url(__FILE__) . 'assets/organisaties.js',
            ['jquery'],
            '1.1.1',
            true
        );

        wp_localize_script(self::ASSET_HANDLE, 'FOND_DIR', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce(self::NONCE_ACTION),
        ]);
    }

    /* ======================================================
     * Shortcode
     * ====================================================== */
    public function render_shortcode($atts) {

        // Alleen laden wanneer shortcode gebruikt wordt
        wp_enqueue_style(self::ASSET_HANDLE);
        wp_enqueue_script(self::ASSET_HANDLE);

        $atts = shortcode_atts([
            'per_page' => 30,            // 3 × 10
            'sector'   => '',            // "klimaat,gezondheid"
            'type'     => '',            // "ngo,stichting"
        ], $atts, 'fondsen_organisaties');

        // GET filters hebben voorrang
        $search = isset($_GET['org_search']) ? sanitize_text_field(wp_unslash($_GET['org_search'])) : '';

        $sector_selected = isset($_GET['org_sector'])
            ? (array) wp_unslash($_GET['org_sector'])
            : array_filter(array_map('trim', explode(',', (string) $atts['sector'])));

        $type_selected = isset($_GET['org_type'])
            ? (array) wp_unslash($_GET['org_type'])
            : array_filter(array_map('trim', explode(',', (string) $atts['type'])));

        // Query
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

        // Terms voor filters
        $types = get_terms([
            'taxonomy'   => self::TAX_TYPE_ORG,
            'hide_empty' => false,
        ]);

        $sectors = get_terms([
            'taxonomy'   => self::TAX_SECTOR,
            'hide_empty' => false,
        ]);

        // Variabelen die templates verwachten
        $search_query    = $data['search'] ?? '';
        $selected_types  = $data['types_selected'] ?? [];
        $selected_sector = $data['sectors_selected'] ?? [];

        ob_start();

        include $this->plugin_path('templates/organisaties-filter.php');
        include $this->plugin_path('templates/organisaties-listing.php');

        $html = ob_get_clean();

        // Belangrijk: global $post resetten (ook al loopen je templates mogelijk met setup_postdata)
        wp_reset_postdata();

        return $html;
    }

    /* ======================================================
     * AJAX Load More
     * ====================================================== */
    public function ajax_load_more() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');

        $page     = isset($_POST['page']) ? (int) $_POST['page'] : 1;
        $per_page = isset($_POST['per_page']) ? (int) $_POST['per_page'] : 30;

        $org_search = isset($_POST['org_search']) ? sanitize_text_field(wp_unslash($_POST['org_search'])) : '';
        $org_sector = isset($_POST['org_sector']) ? (array) wp_unslash($_POST['org_sector']) : [];
        $org_type   = isset($_POST['org_type']) ? (array) wp_unslash($_POST['org_type']) : [];

        $query = new WP_Query(
            $this->build_query_args([
                'paged'      => max(1, $page),
                'per_page'   => max(1, $per_page),
                's'          => $org_search,
                'org_sector' => $org_sector,
                'org_type'   => $org_type,
            ])
        );

        ob_start();

        // Cruciaal: WP Loop gebruiken zodat the_title/the_permalink/etc altijd werken in AJAX
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                include $this->plugin_path('templates/organisaties-grid-item.php');
            }
        }

        wp_reset_postdata();

        $html = ob_get_clean();

        wp_send_json_success([
            'html'      => $html,
            'has_more'  => ($query->max_num_pages > $page),
            'next_page' => $page + 1,
        ]);
    }

    /* ======================================================
     * Query helpers
     * ====================================================== */
    private function build_query_args($params = []) {

        $parent_id = $this->get_parent_organisaties_id();

        // Als parent niet bestaat, liever niks tonen dan alle top-level pagina’s
        if (!$parent_id) {
            return [
                'post_type'      => 'page',
                'post_status'    => 'publish',
                'posts_per_page' => 0,
            ];
        }

        $args = [
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'post_parent'    => (int) $parent_id,
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
