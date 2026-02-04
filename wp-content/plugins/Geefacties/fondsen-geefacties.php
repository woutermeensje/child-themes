<?php
/**
 * Plugin Name: Fondsen.org – Geefacties
 * Description: Publiceer en toon donatieverzoeken (geefacties) met filters + load more. Formulier loopt via Elementor Forms.
 * Version: 1.0.1
 */

if (!defined('ABSPATH')) exit;

class Fondsen_Geefacties_Plugin {

    // CPT + taxonomieën (Elementor Forms kan posts aanmaken in dit CPT)
    const CPT          = 'geefactie';
    const TAX_THEMA    = 'geefactie_thema';
    const TAX_TYPE     = 'geefactie_type';

    const ASSET_HANDLE = 'fondsen-geefacties';
    const NONCE_ACTION = 'fond_geef_nonce';

    // Meta keys (optioneel – je Elementor Form mapping kan deze vullen)
    const META_GOAL    = '_ga_goal_amount';    // doelbedrag (bijv. 7000)
    const META_RAISED  = '_ga_raised_amount';  // opgehaald (bijv. 5225)
    const META_VIEWS   = '_ga_views';          // veel gelezen (int)
    const META_TREND   = '_ga_trend_score';    // trending score (int)
    const META_STATUS  = '_ga_status';         // active|completed (voor "Toon")

    public function __construct() {
        add_action('init', [$this, 'register_cpt_and_taxonomies']);

        add_shortcode('fondsen_geefacties', [$this, 'render_shortcode']);

        add_action('wp_enqueue_scripts', [$this, 'register_assets']);

        add_action('wp_ajax_fondsen_geefacties_load_more', [$this, 'ajax_load_more']);
        add_action('wp_ajax_nopriv_fondsen_geefacties_load_more', [$this, 'ajax_load_more']);
    }

    /* ======================================================
     * CPT + Taxonomieën
     * ====================================================== */
    public function register_cpt_and_taxonomies() {

        // CPT: Geefactie
        $labels = [
            'name'               => 'Geefacties',
            'singular_name'      => 'Geefactie',
            'add_new'            => 'Nieuwe geefactie',
            'add_new_item'       => 'Nieuwe geefactie toevoegen',
            'edit_item'          => 'Geefactie bewerken',
            'new_item'           => 'Nieuwe geefactie',
            'view_item'          => 'Geefactie bekijken',
            'search_items'       => 'Geefacties zoeken',
            'not_found'          => 'Geen geefacties gevonden',
            'not_found_in_trash' => 'Geen geefacties in de prullenbak',
            'menu_name'          => 'Geefacties',
        ];

        register_post_type(self::CPT, [
            'labels'             => $labels,
            'public'             => true,
            'has_archive'        => true,
            'rewrite'            => ['slug' => 'geefacties'],
            'menu_icon'          => 'dashicons-heart',
            'supports'           => ['title', 'editor', 'excerpt', 'thumbnail', 'author', 'custom-fields'],
            'show_in_rest'       => true,
        ]);

        // Tax: Thema
        register_taxonomy(self::TAX_THEMA, [self::CPT], [
            'label'        => 'Thema',
            'public'       => true,
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite'      => ['slug' => 'geefactie-thema'],
        ]);

        // Tax: Soort geefactie
        register_taxonomy(self::TAX_TYPE, [self::CPT], [
            'label'        => 'Soort geefactie',
            'public'       => true,
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite'      => ['slug' => 'geefactie-type'],
        ]);
    }

    /* ======================================================
     * Assets
     * ====================================================== */
    public function register_assets() {

        wp_register_style(
            self::ASSET_HANDLE,
            plugin_dir_url(__FILE__) . 'assets/geefacties.css',
            [],
            '1.0.0'
        );

        wp_register_script(
            self::ASSET_HANDLE,
            plugin_dir_url(__FILE__) . 'assets/geefacties.js',
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script(self::ASSET_HANDLE, 'FOND_GEEF', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce(self::NONCE_ACTION),
        ]);
    }

    /* ======================================================
     * Shortcode
     * ====================================================== */
    public function render_shortcode($atts) {

        // Safety: zorg dat assets altijd geregistreerd zijn (soms rendert Elementor shortcodes vroeg)
        if (!wp_style_is(self::ASSET_HANDLE, 'registered') || !wp_script_is(self::ASSET_HANDLE, 'registered')) {
            $this->register_assets();
        }

        // Alleen laden wanneer shortcode gebruikt wordt
        wp_enqueue_style(self::ASSET_HANDLE);
        wp_enqueue_script(self::ASSET_HANDLE);

        $atts = shortcode_atts([
            'per_page' => 18,
            'thema'    => '', // prefilter via shortcode: thema="zorg,jeugd"
            'type'     => '', // type="sponsorloop,collecte"
        ], $atts, 'fondsen_geefacties');

        // GET heeft voorrang
        $search = isset($_GET['ga_search']) ? sanitize_text_field(wp_unslash($_GET['ga_search'])) : '';

        $thema_selected = isset($_GET['ga_thema'])
            ? (array) wp_unslash($_GET['ga_thema'])
            : array_filter(array_map('trim', explode(',', (string) $atts['thema'])));

        $type_selected = isset($_GET['ga_type'])
            ? (array) wp_unslash($_GET['ga_type'])
            : array_filter(array_map('trim', explode(',', (string) $atts['type'])));

        $toon = isset($_GET['ga_toon']) ? sanitize_text_field(wp_unslash($_GET['ga_toon'])) : 'all';
        $sort = isset($_GET['ga_sort']) ? sanitize_text_field(wp_unslash($_GET['ga_sort'])) : 'trending';

        $query = new WP_Query(
            $this->build_query_args([
                'paged'    => 1,
                'per_page' => (int) $atts['per_page'],
                's'        => $search,
                'thema'    => $thema_selected,
                'type'     => $type_selected,
                'toon'     => $toon,
                'sort'     => $sort,
            ])
        );

        $data = [
            'search'          => $search,
            'thema_selected'  => $this->sanitize_slugs_array($thema_selected),
            'type_selected'   => $this->sanitize_slugs_array($type_selected),
            'toon'            => $toon,
            'sort'            => $sort,
            'posts'           => $query->posts,
            'max_pages'       => (int) $query->max_num_pages,
            'per_page'        => (int) $atts['per_page'],
        ];

        // Terms voor dropdowns
        $themas = get_terms([
            'taxonomy'   => self::TAX_THEMA,
            'hide_empty' => false,
        ]);

        $types = get_terms([
            'taxonomy'   => self::TAX_TYPE,
            'hide_empty' => false,
        ]);

        // Variabelen die templates verwachten
        $search_query    = $data['search'] ?? '';
        $selected_thema  = $data['thema_selected'] ?? [];
        $selected_types  = $data['type_selected'] ?? [];
        $selected_toon   = $data['toon'] ?? 'all';
        $selected_sort   = $data['sort'] ?? 'trending';

        ob_start();

        include $this->plugin_path('templates/geefacties-filter.php');
        include $this->plugin_path('templates/geefacties-listing.php');

        $html = ob_get_clean();

        wp_reset_postdata();

        return $html;
    }

    /* ======================================================
     * AJAX Load More
     * ====================================================== */
    public function ajax_load_more() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');

        $page     = isset($_POST['page']) ? (int) $_POST['page'] : 1;
        $per_page = isset($_POST['per_page']) ? (int) $_POST['per_page'] : 18;

        $ga_search = isset($_POST['ga_search']) ? sanitize_text_field(wp_unslash($_POST['ga_search'])) : '';
        $ga_thema  = isset($_POST['ga_thema']) ? (array) wp_unslash($_POST['ga_thema']) : [];
        $ga_type   = isset($_POST['ga_type']) ? (array) wp_unslash($_POST['ga_type']) : [];
        $ga_toon   = isset($_POST['ga_toon']) ? sanitize_text_field(wp_unslash($_POST['ga_toon'])) : 'all';
        $ga_sort   = isset($_POST['ga_sort']) ? sanitize_text_field(wp_unslash($_POST['ga_sort'])) : 'trending';

        $query = new WP_Query(
            $this->build_query_args([
                'paged'    => max(1, $page),
                'per_page' => max(1, $per_page),
                's'        => $ga_search,
                'thema'    => $ga_thema,
                'type'     => $ga_type,
                'toon'     => $ga_toon,
                'sort'     => $ga_sort,
            ])
        );

        ob_start();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                include $this->plugin_path('templates/geefacties-card.php');
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

        $args = [
            'post_type'      => self::CPT,
            // Op front-end alleen gepubliceerd. Voor ingelogde editors ook concept/pending tonen (handig voor testen).
            'post_status'    => (is_user_logged_in() && current_user_can('edit_posts'))
                ? ['publish','pending','draft','future','private']
                : 'publish',
            'posts_per_page' => max(1, (int) ($params['per_page'] ?? 18)),
            'paged'          => max(1, (int) ($params['paged'] ?? 1)),
            'ignore_sticky_posts' => true,
        ];

        if (!empty($params['s'])) {
            $args['s'] = sanitize_text_field($params['s']);
        }

        // Tax filters
        $tax_query = [];

        $thema = $this->sanitize_slugs_array($params['thema'] ?? []);
        if ($thema) {
            $tax_query[] = [
                'taxonomy' => self::TAX_THEMA,
                'field'    => 'slug',
                'terms'    => $thema,
            ];
        }

        $type = $this->sanitize_slugs_array($params['type'] ?? []);
        if ($type) {
            $tax_query[] = [
                'taxonomy' => self::TAX_TYPE,
                'field'    => 'slug',
                'terms'    => $type,
            ];
        }

        if ($tax_query) {
            $args['tax_query'] = $tax_query;
        }

        // Toon (status)
        $toon = isset($params['toon']) ? sanitize_text_field($params['toon']) : 'all';
        if ($toon && $toon !== 'all') {
            $args['meta_query'] = [
                [
                    'key'     => self::META_STATUS,
                    'value'   => $toon,
                    'compare' => '=',
                ]
            ];
        }

        // Sorteren
        $sort = isset($params['sort']) ? sanitize_text_field($params['sort']) : 'trending';
        switch ($sort) {
            case 'nieuw':
                $args['orderby'] = 'date';
                $args['order']   = 'DESC';
                break;

            case 'veelgelezen':
                $args['meta_key'] = self::META_VIEWS;
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                break;

            case 'trending':
            default:
                // Als META_TREND niet gevuld is, valt WP terug op 0; daarom 2e orderby op date.
                $args['meta_key'] = self::META_TREND;
                $args['orderby']  = [
                    'meta_value_num' => 'DESC',
                    'date'           => 'DESC',
                ];
                break;
        }

        return $args;
    }

    private function sanitize_slugs_array($values) {
        if (!is_array($values)) return [];
        return array_values(array_filter(array_map('sanitize_title', $values)));
    }

    private function plugin_path($relative) {
        return plugin_dir_path(__FILE__) . ltrim($relative, '/');
    }

    /* ======================================================
     * Helpers voor templates
     * ====================================================== */
    public static function get_amount_int($post_id, $meta_key) {
        $raw = get_post_meta($post_id, $meta_key, true);
        if ($raw === '' || $raw === null) return 0;
        // Sta ook bedragen met komma/punt toe
        $raw = str_replace(['.', ' '], ['', ''], (string) $raw);
        $raw = str_replace(',', '.', $raw);
        return (int) round((float) $raw);
    }

    public static function euro($amount_int) {
        // NL formatting, zonder centen
        return '€ ' . number_format((int) $amount_int, 0, ',', '.');
    }
}

new Fondsen_Geefacties_Plugin();
