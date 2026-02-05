<?php
/**
 * Plugin Name: Fondsen.org – Geefacties
 * Description: Publiceer en toon donatieverzoeken (geefacties) met filters + load more. Formulier loopt via Elementor Forms.
 * Version: 1.0.2
 */

if (!defined('ABSPATH')) exit;

class Fondsen_Geefacties_Plugin {

    // CPT + taxonomieën
    const CPT          = 'geefactie';
    const TAX_THEMA    = 'geefactie_thema';
    const TAX_TYPE     = 'geefactie_type';

    const ASSET_HANDLE = 'fondsen-geefacties';
    const NONCE_ACTION = 'fond_geef_nonce';

    // Meta keys (optioneel – Elementor Form mapping kan deze vullen)
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
            'labels'       => $labels,
            'public'       => true,
            'has_archive'  => true,
            'rewrite'      => ['slug' => 'geefacties'],
            'menu_icon'    => 'dashicons-heart',
            'supports'     => ['title', 'editor', 'excerpt', 'thumbnail', 'author', 'custom-fields'],
            'show_in_rest' => true,
        ]);

        register_taxonomy(self::TAX_THEMA, [self::CPT], [
            'label'        => 'Thema',
            'public'       => true,
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite'      => ['slug' => 'geefactie-thema'],
        ]);

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
            '1.0.2'
        );

        wp_register_script(
            self::ASSET_HANDLE,
            plugin_dir_url(__FILE__) . 'assets/geefacties.js',
            ['jquery'],
            '1.0.2',
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

        // Safety: zorg dat assets altijd geregistreerd zijn (Elementor kan vroeg renderen)
        if (!wp_style_is(self::ASSET_HANDLE, 'registered') || !wp_script_is(self::ASSET_HANDLE, 'registered')) {
            $this->register_assets();
        }

        wp_enqueue_style(self::ASSET_HANDLE);
        wp_enqueue_script(self::ASSET_HANDLE);

        $atts = shortcode_atts([
            'per_page' => 18,
            'thema'    => '', // prefilter via shortcode: thema="zorg,jeugd"
            'type'     => '', // prefilter via shortcode: type="sponsorloop,collecte" (single werkt ook)
        ], $atts, 'fondsen_geefacties');

        // --- GET heeft voorrang ---
        $search = isset($_GET['ga_search'])
            ? sanitize_text_field(wp_unslash($_GET['ga_search']))
            : '';

        // Thema multi: ga_thema[] of shortcode thema="a,b"
        $thema_selected = [];
        if (isset($_GET['ga_thema'])) {
            $thema_selected = (array) wp_unslash($_GET['ga_thema']);
        } elseif (!empty($atts['thema'])) {
            $thema_selected = array_map('trim', explode(',', (string) $atts['thema']));
        }
        $thema_selected = $this->sanitize_slugs_array($thema_selected);

        // Type single: ga_type (string) of shortcode type="a"
        // (Als iemand tóch ga_type[] gebruikt, vangen we dat ook af)
        $type_selected = [];
        if (isset($_GET['ga_type'])) {
            $raw = wp_unslash($_GET['ga_type']);
            if (is_array($raw)) {
                $type_selected = $raw;
            } else {
                $type_selected = [$raw];
            }
        } elseif (!empty($atts['type'])) {
            // shortcode type kan comma separated zijn, maar in UI is het single
            $type_selected = array_map('trim', explode(',', (string) $atts['type']));
        }
        $type_selected = $this->sanitize_slugs_array($type_selected);

        $toon = isset($_GET['ga_toon']) ? sanitize_text_field(wp_unslash($_GET['ga_toon'])) : 'all';
        if (!in_array($toon, ['all','active','completed'], true)) {
            $toon = 'all';
        }

        $sort = isset($_GET['ga_sort']) ? sanitize_text_field(wp_unslash($_GET['ga_sort'])) : 'trending';
        if (!in_array($sort, ['trending','nieuw','veelgelezen'], true)) {
            $sort = 'trending';
        }

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
            'thema_selected'  => $thema_selected,
            'type_selected'   => $type_selected,
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

        // Thema multi
        $ga_thema = [];
        if (isset($_POST['ga_thema'])) {
            $ga_thema = $this->sanitize_slugs_array((array) wp_unslash($_POST['ga_thema']));
        }

        // Type single (maar vangen array ook af)
        $ga_type = [];
        if (isset($_POST['ga_type'])) {
            $raw = wp_unslash($_POST['ga_type']);
            $ga_type = is_array($raw) ? $raw : [$raw];
            $ga_type = $this->sanitize_slugs_array($ga_type);
        }

        $ga_toon = isset($_POST['ga_toon']) ? sanitize_text_field(wp_unslash($_POST['ga_toon'])) : 'all';
        if (!in_array($ga_toon, ['all','active','completed'], true)) {
            $ga_toon = 'all';
        }

        $ga_sort = isset($_POST['ga_sort']) ? sanitize_text_field(wp_unslash($_POST['ga_sort'])) : 'trending';
        if (!in_array($ga_sort, ['trending','nieuw','veelgelezen'], true)) {
            $ga_sort = 'trending';
        }

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
            'post_type'           => self::CPT,
            // Op front-end alleen published. Voor ingelogde editors ook drafts/pending tonen (handig testen)
            'post_status'         => (is_user_logged_in() && current_user_can('edit_posts'))
                ? ['publish','pending','draft','future','private']
                : 'publish',
            'posts_per_page'      => max(1, (int) ($params['per_page'] ?? 18)),
            'paged'               => max(1, (int) ($params['paged'] ?? 1)),
            'ignore_sticky_posts' => true,
        ];

        if (!empty($params['s'])) {
            $args['s'] = sanitize_text_field($params['s']);
        }

        // Tax filters (alleen als er echt slugs zijn)
        $tax_query = ['relation' => 'AND'];

        $thema = $this->sanitize_slugs_array($params['thema'] ?? []);
        if (!empty($thema)) {
            $tax_query[] = [
                'taxonomy' => self::TAX_THEMA,
                'field'    => 'slug',
                'terms'    => $thema,
            ];
        }

        $type = $this->sanitize_slugs_array($params['type'] ?? []);
        if (!empty($type)) {
            $tax_query[] = [
                'taxonomy' => self::TAX_TYPE,
                'field'    => 'slug',
                'terms'    => $type,
            ];
        }

        if (count($tax_query) > 1) {
            $args['tax_query'] = $tax_query;
        }

        // Toon (status) — 'all' mag NOOIT filteren
        $toon = isset($params['toon']) ? sanitize_text_field($params['toon']) : 'all';
        if (in_array($toon, ['active','completed'], true)) {
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
                $args['meta_key'] = self::META_TREND;
                $args['orderby']  = [
                    'meta_value_num' => 'DESC',
                    'date'           => 'DESC',
                ];
                $args['order'] = 'DESC';
                break;
        }

        return $args;
    }

    private function sanitize_slugs_array($values) {
        if (!is_array($values)) return [];
        $values = array_map('sanitize_title', $values);
        return array_values(array_filter($values, function($v){ return $v !== ''; }));
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

        $raw = (string) $raw;
        $raw = str_replace([' ', '.'], ['', ''], $raw);
        $raw = str_replace(',', '.', $raw);

        return (int) round((float) $raw);
    }

    public static function euro($amount_int) {
        return '€ ' . number_format((int) $amount_int, 0, ',', '.');
    }
}

new Fondsen_Geefacties_Plugin();