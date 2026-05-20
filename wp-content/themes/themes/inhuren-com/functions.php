<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * ✅ CUSTOM HEADER: vervang Hello Elementor's standaard header
 */
add_filter('hello_elementor_page_header_enabled', '__return_false');
add_action('hello_elementor_before_header', function () {
    include get_stylesheet_directory() . '/template-parts/header.php';
});

/**
 * ✅ ENQUEUE STYLES (with Elementor check)
 */
add_action('wp_enqueue_scripts', function () {
    $dependencies = ['parent-style'];
    if (did_action('elementor/loaded') && wp_style_is('elementor-frontend', 'registered')) {
        $dependencies[] = 'elementor-frontend';
    }

    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css', [], filemtime(get_template_directory() . '/style.css'));
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', $dependencies, filemtime(get_stylesheet_directory() . '/style.css'));
    wp_enqueue_style('poppins-font', 'https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap', [], null);
    wp_enqueue_style('inter-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', [], null);
    wp_enqueue_style('custom-fonts', get_stylesheet_directory_uri() . '/fonts/fonts.css', [], filemtime(get_stylesheet_directory() . '/fonts/fonts.css'));
    if (file_exists(get_stylesheet_directory() . '/css/header.css')) {
        wp_enqueue_style('inhuren-header', get_stylesheet_directory_uri() . '/css/header.css', ['child-style'], filemtime(get_stylesheet_directory() . '/css/header.css'));
    }
    if (file_exists(get_stylesheet_directory() . '/css/gravity-forms.css')) {
        wp_enqueue_style('child-gf-styles', get_stylesheet_directory_uri() . '/css/gravity-forms.css', [], filemtime(get_stylesheet_directory() . '/css/gravity-forms.css'));
    }
    if (file_exists(get_stylesheet_directory() . '/css/elementor-forms.css')) {
        wp_enqueue_style('inhuren-elementor-forms', get_stylesheet_directory_uri() . '/css/elementor-forms.css', ['child-style'], filemtime(get_stylesheet_directory() . '/css/elementor-forms.css'));
    }
});

if (!class_exists('RN_Nav_Walker')) :
class RN_Nav_Walker extends Walker_Nav_Menu {
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? [] : (array) $item->classes;
        $has_child = in_array('menu-item-has-children', $classes, true);
        $is_active = in_array('current-menu-item', $classes, true) || in_array('current-menu-ancestor', $classes, true);
        $li_class = 'rn-nav__item';

        if ($has_child) {
            $li_class .= ' rn-nav__item--has-children';
        }

        if ($is_active) {
            $li_class .= ' is-active';
        }

        $output .= '<li class="' . esc_attr($li_class) . '">';

        $url = !empty($item->url) ? $item->url : '#';
        $title = apply_filters('the_title', $item->title, $item->ID);
        $attr_title = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $target = !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $rel = !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';

        $output .= '<a class="rn-nav__link' . ($is_active ? ' is-active' : '') . '" href="' . esc_url($url) . '"' . $attr_title . $target . $rel . '>';
        $output .= esc_html($title);

        if ($has_child) {
            $output .= '<span class="rn-nav__chev" aria-hidden="true"></span>';
        }

        $output .= '</a>';
    }

    public function start_lvl(&$output, $depth = 0, $args = null) {
        $output .= '<ul class="rn-nav__dropdown">';
    }

    public function end_lvl(&$output, $depth = 0, $args = null) {
        $output .= '</ul>';
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= '</li>';
    }
}
endif;

add_action('after_setup_theme', function () {
    register_nav_menus([
        'primary_nav' => 'Primaire navigatie',
        'footer_nav'  => 'Footer navigatie',
    ]);
});

add_shortcode('rn_header', function () {
    ob_start();
    include get_stylesheet_directory() . '/template-parts/header.php';
    return ob_get_clean();
});


// =========================================================
// Informatie aanvragen formulier
// =========================================================

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('quill-js', 'https://cdn.quilljs.com/1.3.7/quill.min.js', [], null, true);
    wp_enqueue_style('quill-css', 'https://cdn.quilljs.com/1.3.7/quill.snow.css', [], null);
});

add_shortcode('inhuren_info_form', function () {
    ob_start();
    $nonce = wp_create_nonce('inhuren_info_form');
    ?>
    <div class="iif-wrap">

        <h2 class="iif-title">Informatie aanvragen</h2>

        <div class="iif-success" style="display:none;">
            <p>Bedankt voor uw aanvraag! We nemen zo snel mogelijk contact met u op.</p>
        </div>

        <form class="iif-form" id="inhuren-info-form">
            <input type="hidden" name="action" value="inhuren_info_form_submit">
            <input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>">

            <div class="iif-row">
                <div class="iif-field">
                    <label for="iif_voornaam">Voornaam <span>*</span></label>
                    <input type="text" id="iif_voornaam" name="voornaam" required placeholder="Bijv. Jan">
                </div>
                <div class="iif-field">
                    <label for="iif_achternaam">Achternaam</label>
                    <input type="text" id="iif_achternaam" name="achternaam" placeholder="Bijv. de Vries">
                </div>
            </div>

            <div class="iif-row">
                <div class="iif-field">
                    <label for="iif_telefoon">Telefoonnummer</label>
                    <input type="tel" id="iif_telefoon" name="telefoon" placeholder="Bijv. 06 12345678">
                </div>
                <div class="iif-field">
                    <label for="iif_email">E-mailadres <span>*</span></label>
                    <input type="email" id="iif_email" name="email" required placeholder="Bijv. jan@bedrijf.nl">
                </div>
            </div>

            <div class="iif-field">
                <label>Bericht <span>*</span></label>
                <div id="iif-quill-editor"></div>
                <input type="hidden" id="iif_bericht" name="bericht">
            </div>

            <div class="iif-error" style="display:none;"></div>

            <button type="submit" class="iif-submit">
                <span class="iif-submit__label">Informatie aanvragen</span>
                <span class="iif-submit__loading" style="display:none;">Verzenden...</span>
            </button>
        </form>

    </div>

    <style>
    .iif-wrap {
        max-width: 720px;
        margin: 0 auto;
        font-family: 'Roboto', sans-serif;
        border: 1px solid #dedede;
        border-radius: 5px;
        padding: 32px;
        background: #ffffff;
    }

    .iif-title {
        font-family: 'Poppins', sans-serif !important;
        font-size: 22px !important;
        font-weight: 600 !important;
        color: #111827 !important;
        margin: 0 0 24px !important;
        line-height: 1.3 !important;
    }

    .iif-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 5px;
        padding: 20px 24px;
        color: #166534;
        font-size: 15px;
    }

    .iif-form { display: flex; flex-direction: column; gap: 20px; }

    .iif-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .iif-field { display: flex; flex-direction: column; gap: 6px; }

    .iif-field label {
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        font-weight: 300;
        color: #374151;
    }

    .iif-field label span { color: var(--color-primary, #0458AB); }

    .iif-field input {
        height: 44px;
        padding: 0 14px;
        border: 1px solid #dedede;
        border-radius: 5px;
        font-family: 'Roboto', sans-serif;
        font-size: 14px;
        color: #111827;
        background: #fff;
        transition: border-color .15s ease;
        outline: none;
        width: 100%;
        box-sizing: border-box;
    }

    .iif-field input:focus { border-color: var(--color-primary, #0458AB); }

    #iif-quill-editor {
        border: 1px solid #dedede;
        border-top: none;
        border-radius: 0 0 5px 5px;
        min-height: 180px;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        background: #ffffff;
    }

    .ql-toolbar.ql-snow {
        border: 1px solid #dedede !important;
        border-bottom: 1px solid #eeeeee !important;
        border-radius: 5px 5px 0 0 !important;
        background: #fafafa !important;
    }

    .ql-container.ql-snow {
        border: none !important;
        font-size: 14px !important;
        font-family: 'Poppins', sans-serif !important;
    }

    .ql-editor {
        min-height: 160px !important;
        padding: 14px 16px !important;
        line-height: 1.7 !important;
        color: #374151 !important;
    }

    .ql-editor.ql-blank::before {
        color: #9ca3af !important;
        font-style: normal !important;
        font-family: 'Poppins', sans-serif !important;
    }

    .iif-error {
        padding: 12px 16px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 5px;
        color: #b91c1c;
        font-size: 14px;
    }

    .iif-submit {
        align-self: flex-start;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 28px;
        background: var(--color-primary, #0458AB);
        color: #fff !important;
        border: none;
        border-radius: 5px;
        font-family: 'Roboto', sans-serif;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: background .15s ease;
    }

    .iif-submit:hover { background: var(--color-primary-dk, #034085); }

    @media (max-width: 600px) {
        .iif-row { grid-template-columns: 1fr; }
        .iif-submit { width: 100%; }
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var quill = new Quill('#iif-quill-editor', {
            theme: 'snow',
            placeholder: 'Omschrijf uw vraag of aanvraag...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });

        var form  = document.getElementById('inhuren-info-form');
        var wrap  = form.closest('.iif-wrap');

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            document.getElementById('iif_bericht').value = quill.root.innerHTML;

            var errorEl  = form.querySelector('.iif-error');
            var label    = form.querySelector('.iif-submit__label');
            var loading  = form.querySelector('.iif-submit__loading');

            errorEl.style.display  = 'none';
            label.style.display    = 'none';
            loading.style.display  = '';

            var data = new FormData(form);

            fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                method: 'POST',
                body: data
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    form.style.display = 'none';
                    wrap.querySelector('.iif-success').style.display = '';
                } else {
                    errorEl.textContent = res.data || 'Er is iets misgegaan. Probeer het opnieuw.';
                    errorEl.style.display = '';
                    label.style.display   = '';
                    loading.style.display = 'none';
                }
            })
            .catch(function () {
                errorEl.textContent = 'Er is een verbindingsfout opgetreden.';
                errorEl.style.display = '';
                label.style.display   = '';
                loading.style.display = 'none';
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
});

// Custom post type: Informatie aanvragen
add_action('init', function () {
    register_post_type('inhuren_aanvraag', [
        'label'               => 'Informatie aanvragen',
        'labels'              => [
            'name'               => 'Informatie aanvragen',
            'singular_name'      => 'Informatie aanvraag',
            'menu_name'          => 'Aanvragen',
            'all_items'          => 'Alle aanvragen',
            'view_item'          => 'Bekijk aanvraag',
            'search_items'       => 'Zoek aanvragen',
            'not_found'          => 'Geen aanvragen gevonden',
        ],
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_icon'           => 'dashicons-email-alt',
        'supports'            => ['title', 'editor'],
        'capability_type'     => 'post',
        'capabilities'        => ['create_posts' => 'do_not_allow'],
        'map_meta_cap'        => true,
    ]);
});

// AJAX handler
add_action('wp_ajax_inhuren_info_form_submit', 'inhuren_info_form_handler');
add_action('wp_ajax_nopriv_inhuren_info_form_submit', 'inhuren_info_form_handler');

function inhuren_info_form_handler() {
    if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'inhuren_info_form') ) {
        wp_send_json_error('Ongeldige beveiligingstoken.');
    }

    $voornaam   = sanitize_text_field($_POST['voornaam']   ?? '');
    $achternaam = sanitize_text_field($_POST['achternaam'] ?? '');
    $telefoon   = sanitize_text_field($_POST['telefoon']   ?? '');
    $email      = sanitize_email($_POST['email']           ?? '');
    $bericht    = wp_kses_post($_POST['bericht']           ?? '');

    if ( ! $voornaam || ! is_email($email) || ! $bericht ) {
        wp_send_json_error('Vul alle verplichte velden in.');
    }

    // Opslaan in database
    $post_id = wp_insert_post([
        'post_type'    => 'inhuren_aanvraag',
        'post_title'   => $voornaam . ' ' . $achternaam . ' — ' . date('d-m-Y H:i'),
        'post_content' => $bericht,
        'post_status'  => 'publish',
    ]);

    if ( $post_id ) {
        update_post_meta($post_id, '_aanvraag_voornaam',   $voornaam);
        update_post_meta($post_id, '_aanvraag_achternaam', $achternaam);
        update_post_meta($post_id, '_aanvraag_telefoon',   $telefoon);
        update_post_meta($post_id, '_aanvraag_email',      $email);
    }

    // E-mail versturen
    $to      = get_option('admin_email');
    $subject = 'Nieuwe informatie aanvraag van ' . $voornaam . ' ' . $achternaam;
    $body    = "
        <p><strong>Naam:</strong> {$voornaam} {$achternaam}</p>
        <p><strong>Telefoon:</strong> {$telefoon}</p>
        <p><strong>E-mail:</strong> {$email}</p>
        <hr>
        <p><strong>Bericht:</strong></p>
        {$bericht}
    ";

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: ' . $voornaam . ' ' . $achternaam . ' <' . $email . '>',
    ];

    wp_mail($to, $subject, $body, $headers);

    wp_send_json_success();
}


/**
 * ✅ SHORTCODE: [inhuren_opdrachten] — kaartenoverzicht van subpagina's
 * Gebruik: [inhuren_opdrachten parent="opdrachten" columns="4" limit="-1"]
 */
add_shortcode('inhuren_opdrachten', function ($atts) {
    $atts = shortcode_atts([
        'parent'  => 'opdrachten',
        'columns' => 4,
        'limit'   => -1,
    ], $atts, 'inhuren_opdrachten');

    $parent_page = get_page_by_path(sanitize_text_field($atts['parent']));
    if (!$parent_page) return '<p>Pagina &ldquo;' . esc_html($atts['parent']) . '&rdquo; niet gevonden.</p>';

    $pages = get_posts([
        'post_type'      => 'page',
        'post_parent'    => $parent_page->ID,
        'posts_per_page' => intval($atts['limit']),
        'post_status'    => 'publish',
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
    ]);

    if (empty($pages)) return '';

    $columns = max(1, min(6, intval($atts['columns'])));

    ob_start();
    ?>
    <div class="iop-grid iop-cols-<?php echo esc_attr($columns); ?>">
        <?php foreach ($pages as $page) :
            $img_url = get_the_post_thumbnail_url($page->ID, 'large');
            $link    = get_permalink($page->ID);
            $title   = get_the_title($page->ID);
            $excerpt = get_the_excerpt($page->ID);
            $style   = $img_url
                ? 'style="background-image: url(\'' . esc_url($img_url) . '\')"'
                : '';
        ?>
        <a href="<?php echo esc_url($link); ?>" class="iop-card<?php echo $img_url ? '' : ' iop-card--no-image'; ?>" <?php echo $style; ?>>
            <div class="iop-card__overlay"></div>
            <div class="iop-card__body">
                <h3 class="iop-card__title"><?php echo esc_html($title); ?></h3>
                <?php if ($excerpt) : ?>
                    <p class="iop-card__excerpt"><?php echo esc_html($excerpt); ?></p>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <style>
    .iop-grid {
        display: grid;
        gap: 16px;
        width: 100%;
    }
    .iop-cols-1 { grid-template-columns: repeat(1, 1fr); }
    .iop-cols-2 { grid-template-columns: repeat(2, 1fr); }
    .iop-cols-3 { grid-template-columns: repeat(3, 1fr); }
    .iop-cols-4 { grid-template-columns: repeat(4, 1fr); }
    .iop-cols-5 { grid-template-columns: repeat(5, 1fr); }
    .iop-cols-6 { grid-template-columns: repeat(6, 1fr); }

    .iop-card {
        position: relative;
        display: block;
        aspect-ratio: 4 / 3;
        border-radius: 12px;
        overflow: hidden;
        background-color: #0458ab;
        background-size: cover;
        background-position: center;
        text-decoration: none !important;
        transition: transform .25s ease, box-shadow .25s ease;
    }

    .iop-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.22);
    }

    .iop-card:hover .iop-card__overlay {
        opacity: 0.85;
    }

    .iop-card__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            to bottom,
            rgba(0, 0, 0, 0.02) 0%,
            rgba(0, 0, 0, 0.18) 40%,
            rgba(0, 0, 0, 0.72) 100%
        );
        opacity: 0.75;
        transition: opacity .25s ease;
    }

    .iop-card--no-image .iop-card__overlay {
        background: linear-gradient(135deg, rgba(4, 88, 171, 0.85), rgba(3, 74, 146, 0.95));
    }

    .iop-card__body {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 20px 20px 22px;
        z-index: 1;
    }

    .iop-card__title {
        margin: 0 0 4px !important;
        font-family: 'Roboto', sans-serif !important;
        font-size: 18px !important;
        font-weight: 700 !important;
        line-height: 1.25 !important;
        color: #ffffff !important;
        text-shadow: 0 1px 4px rgba(0, 0, 0, 0.4) !important;
    }

    .iop-card__excerpt {
        margin: 0 !important;
        font-family: 'Roboto', sans-serif !important;
        font-size: 13px !important;
        font-weight: 400 !important;
        line-height: 1.4 !important;
        color: rgba(255, 255, 255, 0.82) !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
    }

    @media (max-width: 960px) {
        .iop-cols-4,
        .iop-cols-3 { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 540px) {
        .iop-cols-4,
        .iop-cols-3,
        .iop-cols-2 { grid-template-columns: repeat(1, 1fr); }
        .iop-card { aspect-ratio: 16 / 9; }
    }
    </style>
    <?php
    return ob_get_clean();
});

/**
 * ✅ WPJM HELPER FUNCTIES
 */
if ( ! function_exists('srmb_get_req_value') ) {
    function srmb_get_req_value($key) {
        $filter_key = 'filter_' . $key;

        if (!empty($_GET[$key]))         return (array) $_GET[$key];
        if (!empty($_GET[$filter_key]))  return (array) $_GET[$filter_key];
        if (!empty($_POST[$filter_key])) return (array) $_POST[$filter_key];
        if (!empty($_POST[$key]))        return (array) $_POST[$key];

        return [];
    }
}

if ( ! function_exists('display_tax_terms') ) {
    function display_tax_terms($tax, $post_id) {
        $terms = wp_get_post_terms($post_id, $tax, ['fields' => 'names']);
        return !empty($terms) && !is_wp_error($terms) ? implode(', ', $terms) : '';
    }
}

if ( ! function_exists('get_secondary_imageurl') ) {
    function get_secondary_imageurl($post_id) {
        $image_id = get_post_meta($post_id, '_uncode_secondary_thumbnail_id', true);
        return $image_id ? wp_get_attachment_image_url($image_id, 'large') : '';
    }
}

/**
 * ✅ WP JOB MANAGER: TEMPLATE OVERRIDES
 */
add_filter('job_manager_locate_template', function ($template, $template_name) {
    $custom_templates = [
        'content-job_listing.php',
        'content-single-job_listing.php',
        'job-filters.php',
        'job-filter-job-types.php',
        'job-listings-start.php',
        'job-listings-end.php',
        'job-submit.php',
        'functions.php',
    ];
    $custom_path = get_stylesheet_directory() . '/wp-job-manager/' . $template_name;
    return (in_array($template_name, $custom_templates) && file_exists($custom_path)) ? $custom_path : $template;
}, 10, 2);

/**
 * ✅ REGISTER CUSTOM TAXONOMIES
 */
add_action('init', function () {
    register_taxonomy('job_company', 'job_listing', [
        'label' => 'Organisaties',
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'organisatie'],
    ]);

    register_taxonomy('job_tag', 'job_listing', [
        'label' => 'Tags',
        'hierarchical' => false,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'tag'],
    ]);
});

/**
 * ✅ FILTER SUPPORT VOOR CUSTOM TAXONOMIES
 */
add_filter('job_manager_get_listings_custom_filter', function ($query_args, $args) {
    if (!empty($args['filter_job_tag'])) {
        $query_args['tax_query'][] = [
            'taxonomy' => 'job_tag',
            'field'    => 'slug',
            'terms'    => explode(',', sanitize_text_field($args['filter_job_tag'])),
            'operator' => 'IN',
        ];
    }

    if (!empty($args['filter_job_company'])) {
        $query_args['tax_query'][] = [
            'taxonomy' => 'job_company',
            'field'    => 'slug',
            'terms'    => explode(',', sanitize_text_field($args['filter_job_company'])),
            'operator' => 'IN',
        ];
    }

    return $query_args;
}, 10, 2);

/**
 * ✅ SHORTCODE SUPPORT: [jobs bedrijf="slug"]
 */
add_filter('job_manager_get_listings_args', function($args) {
    if (!empty($args['bedrijf'])) {
        $args['filter_job_company'] = sanitize_title($args['bedrijf']);
    }
    return $args;
});

/**
 * ✅ DEBUGGING LOG (optioneel)
 */
add_filter('job_manager_get_listings_start', function ($query_args, $args) {
    if (isset($_REQUEST['filter_job_company'])) {
        error_log('✅ filter_job_company ontvangen: ' . $_REQUEST['filter_job_company']);
    }
    if (isset($_REQUEST['filter_job_tag'])) {
        error_log('✅ filter_job_tag ontvangen: ' . $_REQUEST['filter_job_tag']);
    }
    return $query_args;
}, 10, 2);

/**
 * ✅ BACKUP JOB CATEGORIES als WP Job Manager ze niet registreert
 */
add_action('init', function() {
    if (!taxonomy_exists('job_listing_category')) {
        register_taxonomy('job_listing_category', 'job_listing', [
            'labels' => [
                'name' => __('Vacaturecategorieën', 'wp-job-manager'),
                'singular_name' => __('Vacaturecategorie', 'wp-job-manager'),
                'add_new_item' => __('Nieuwe categorie toevoegen', 'wp-job-manager'),
                'edit_item' => __('Categorie bewerken', 'wp-job-manager'),
                'search_items' => __('Categorie zoeken', 'wp-job-manager'),
                'all_items' => __('Alle categorieën', 'wp-job-manager'),
            ],
            'hierarchical' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'rewrite' => ['slug' => 'vacature-categorie'],
        ]);
    }
}, 20);
