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
    wp_enqueue_style('roboto-font', 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap', [], null);
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
    wp_enqueue_style('quill-css', 'https://cdn.quilljs.com/1.3.7/quill.snow.css', [], '1.3.7');
    wp_enqueue_script('quill-js', 'https://cdn.quilljs.com/1.3.7/quill.min.js', [], '1.3.7', true);
    if (file_exists(get_stylesheet_directory() . '/css/informatie-aanvragen.css')) {
        wp_enqueue_style('inhuren-info-form', get_stylesheet_directory_uri() . '/css/informatie-aanvragen.css', ['child-style', 'quill-css'], filemtime(get_stylesheet_directory() . '/css/informatie-aanvragen.css'));
    }
    if (is_page_template('opdrachten-landingspagina.php') && file_exists(get_stylesheet_directory() . '/css/opdrachten-landingspagina.css')) {
        wp_enqueue_style('inhuren-opdrachten-landingspagina', get_stylesheet_directory_uri() . '/css/opdrachten-landingspagina.css', ['child-style'], filemtime(get_stylesheet_directory() . '/css/opdrachten-landingspagina.css'));
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

add_shortcode('inhuren_info_form', function ($atts) {
    $atts = shortcode_atts([
        'title'  => '',
        'intro'  => '',
        'button' => 'Informatie aanvragen',
    ], $atts, 'inhuren_info_form');

    $script_path = get_stylesheet_directory() . '/js/informatie-aanvragen.js';
    if (file_exists($script_path)) {
        wp_enqueue_script(
            'inhuren-info-form',
            get_stylesheet_directory_uri() . '/js/informatie-aanvragen.js',
            ['quill-js'],
            filemtime($script_path),
            true
        );

        static $script_config_added = false;
        if (!$script_config_added) {
            wp_add_inline_script(
                'inhuren-info-form',
                'window.InhurenInfoFormConfig = ' . wp_json_encode([
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                ]) . ';',
                'before'
            );
            $script_config_added = true;
        }
    }

    $form_id = 'iif-' . wp_generate_uuid4();
    $current_url = is_singular() ? get_permalink() : home_url(add_query_arg([], $GLOBALS['wp']->request ?? ''));
    $button_style = implode('; ', [
        'align-self: flex-start !important',
        'appearance: none !important',
        '-webkit-appearance: none !important',
        'display: inline-flex !important',
        'align-items: center !important',
        'justify-content: center !important',
        'min-height: 42px !important',
        'padding: 8px 20px !important',
        'border: 1.5px solid #0458AB !important',
        'border-radius: 8px !important',
        'background: #0458AB !important',
        'background-color: #0458AB !important',
        'color: #ffffff !important',
        'font-family: Roboto, sans-serif !important',
        'font-size: 15px !important',
        'font-weight: 700 !important',
        'line-height: 1.5 !important',
        'letter-spacing: 0 !important',
        'text-align: center !important',
        'text-decoration: none !important',
        'text-transform: none !important',
        'white-space: nowrap !important',
        'box-shadow: none !important',
        'cursor: pointer !important',
    ]) . ';';
    $button_text_style = 'font-family: Roboto, sans-serif !important; font-weight: 700 !important;';

    ob_start();
    ?>
    <section class="iif-wrap" <?php echo $atts['title'] ? 'aria-labelledby="' . esc_attr($form_id) . '-title"' : 'aria-label="Informatie aanvragen"'; ?>>
        <div class="iif-card">
            <?php if ($atts['title']) : ?>
                <h2 class="iif-title" id="<?php echo esc_attr($form_id); ?>-title"><?php echo esc_html($atts['title']); ?></h2>
            <?php endif; ?>

            <?php if ($atts['intro']) : ?>
                <p class="iif-intro"><?php echo esc_html($atts['intro']); ?></p>
            <?php endif; ?>

            <div class="iif-success" data-iif-success hidden tabindex="-1">
                <strong>Bedankt voor je aanvraag.</strong>
                <span>We hebben je bericht ontvangen en nemen zo snel mogelijk contact met je op.</span>
            </div>

            <form class="iif-form" data-iif-form novalidate>
                <input type="hidden" name="action" value="inhuren_info_form_submit">
                <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('inhuren_info_form')); ?>">
                <input type="hidden" name="page_url" value="<?php echo esc_url($current_url); ?>">

                <div class="iif-honeypot" aria-hidden="true">
                    <label for="<?php echo esc_attr($form_id); ?>-website">Website</label>
                    <input type="text" id="<?php echo esc_attr($form_id); ?>-website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="iif-row">
                    <div class="iif-field">
                        <label for="<?php echo esc_attr($form_id); ?>-voornaam">Voornaam <span>*</span></label>
                        <input type="text" id="<?php echo esc_attr($form_id); ?>-voornaam" name="voornaam" required autocomplete="given-name">
                    </div>
                    <div class="iif-field">
                        <label for="<?php echo esc_attr($form_id); ?>-achternaam">Achternaam <span>*</span></label>
                        <input type="text" id="<?php echo esc_attr($form_id); ?>-achternaam" name="achternaam" required autocomplete="family-name">
                    </div>
                </div>

                <div class="iif-row">
                    <div class="iif-field">
                        <label for="<?php echo esc_attr($form_id); ?>-email">E-mailadres <span>*</span></label>
                        <input type="email" id="<?php echo esc_attr($form_id); ?>-email" name="email" required autocomplete="email">
                    </div>
                    <div class="iif-field">
                        <label for="<?php echo esc_attr($form_id); ?>-telefoon">Telefoonnummer</label>
                        <input type="tel" id="<?php echo esc_attr($form_id); ?>-telefoon" name="telefoon" autocomplete="tel">
                    </div>
                </div>

                <div class="iif-field">
                    <label id="<?php echo esc_attr($form_id); ?>-bericht-label" for="<?php echo esc_attr($form_id); ?>-bericht-fallback">Bericht <span>*</span></label>
                    <div class="iif-richtext" id="<?php echo esc_attr($form_id); ?>-bericht-editor" data-iif-editor aria-labelledby="<?php echo esc_attr($form_id); ?>-bericht-label"></div>
                    <textarea class="iif-message-fallback" id="<?php echo esc_attr($form_id); ?>-bericht-fallback" name="bericht_plain" rows="7" required placeholder="Omschrijf kort je vraag of aanvraag."></textarea>
                    <input type="hidden" name="bericht" data-iif-message>
                </div>

                <div class="iif-error" data-iif-error hidden></div>

                <button type="submit" class="iif-submit" style="<?php echo esc_attr($button_style); ?>">
                    <span data-iif-submit-label style="<?php echo esc_attr($button_text_style); ?>"><?php echo esc_html($atts['button']); ?></span>
                    <span data-iif-submit-loading style="<?php echo esc_attr($button_text_style); ?>" hidden>Verzenden...</span>
                </button>
            </form>
        </div>
    </section>
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

add_action('add_meta_boxes', function () {
    add_meta_box(
        'inhuren_aanvraag_details',
        'Aanvraaggegevens',
        'inhuren_aanvraag_details_metabox',
        'inhuren_aanvraag',
        'normal',
        'high'
    );
});

function inhuren_aanvraag_details_metabox($post) {
    $fields = [
        'Voornaam'         => get_post_meta($post->ID, '_aanvraag_voornaam', true),
        'Achternaam'       => get_post_meta($post->ID, '_aanvraag_achternaam', true),
        'E-mail'           => get_post_meta($post->ID, '_aanvraag_email', true),
        'Telefoon'         => get_post_meta($post->ID, '_aanvraag_telefoon', true),
        'Pagina'           => get_post_meta($post->ID, '_aanvraag_page_url', true),
    ];
    ?>
    <table class="widefat striped">
        <tbody>
            <?php foreach ($fields as $label => $value) : ?>
                <tr>
                    <th scope="row" style="width:180px;"><?php echo esc_html($label); ?></th>
                    <td>
                        <?php if ($label === 'Pagina' && $value) : ?>
                            <a href="<?php echo esc_url($value); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($value); ?></a>
                        <?php else : ?>
                            <?php echo esc_html($value ?: '-'); ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}

add_filter('manage_inhuren_aanvraag_posts_columns', function ($columns) {
    return [
        'cb'        => $columns['cb'] ?? '<input type="checkbox" />',
        'title'     => 'Aanvraag',
        'email'     => 'E-mail',
        'phone'     => 'Telefoon',
        'date'      => $columns['date'] ?? 'Datum',
    ];
});

add_action('manage_inhuren_aanvraag_posts_custom_column', function ($column, $post_id) {
    if ($column === 'email') {
        $email = get_post_meta($post_id, '_aanvraag_email', true);
        echo $email ? '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>' : '-';
    }

    if ($column === 'phone') {
        echo esc_html(get_post_meta($post_id, '_aanvraag_telefoon', true) ?: '-');
    }
}, 10, 2);

function inhuren_info_form_handler() {
    $post_value = static function ($key, $default = '') {
        if (!isset($_POST[$key]) || is_array($_POST[$key])) {
            return $default;
        }

        return wp_unslash($_POST[$key]);
    };

    if (!wp_verify_nonce(sanitize_text_field($post_value('nonce')), 'inhuren_info_form')) {
        wp_send_json_error('Ongeldige beveiligingstoken.');
    }

    $honeypot = sanitize_text_field($post_value('website'));
    if ($honeypot !== '') {
        wp_send_json_success();
    }

    $voornaam   = sanitize_text_field($post_value('voornaam'));
    $achternaam = sanitize_text_field($post_value('achternaam'));
    $telefoon   = sanitize_text_field($post_value('telefoon'));
    $email      = sanitize_email($post_value('email'));
    $bericht    = wp_kses_post($post_value('bericht'));
    $bericht_plain = sanitize_textarea_field($post_value('bericht_plain'));
    $page_url   = esc_url_raw($post_value('page_url'));

    if (!$bericht && $bericht_plain) {
        $bericht = esc_html($bericht_plain);
    }

    if (!$voornaam || !$achternaam || !is_email($email) || !trim(wp_strip_all_tags($bericht))) {
        wp_send_json_error('Vul alle verplichte velden in.');
    }

    $message_html = wpautop(wp_kses_post($bericht));
    $post_content = sprintf(
        '<p><strong>Naam:</strong> %s</p>
        <p><strong>E-mail:</strong> %s</p>
        <p><strong>Telefoon:</strong> %s</p>
        <p><strong>Pagina:</strong> %s</p>
        <hr>
        <p><strong>Bericht:</strong></p>%s',
        esc_html(trim($voornaam . ' ' . $achternaam)),
        esc_html($email),
        esc_html($telefoon ?: '-'),
        $page_url ? '<a href="' . esc_url($page_url) . '">' . esc_html($page_url) . '</a>' : '-',
        $message_html
    );

    $post_id = wp_insert_post([
        'post_type'    => 'inhuren_aanvraag',
        'post_title'   => sprintf('%s %s - %s', $voornaam, $achternaam, current_time('d-m-Y H:i')),
        'post_content' => $post_content,
        'post_status'  => 'publish',
    ], true);

    if (is_wp_error($post_id)) {
        wp_send_json_error('De aanvraag kon niet worden opgeslagen. Probeer het opnieuw.');
    }

    update_post_meta($post_id, '_aanvraag_voornaam', $voornaam);
    update_post_meta($post_id, '_aanvraag_achternaam', $achternaam);
    update_post_meta($post_id, '_aanvraag_telefoon', $telefoon);
    update_post_meta($post_id, '_aanvraag_email', $email);
    update_post_meta($post_id, '_aanvraag_page_url', $page_url);

    $to = apply_filters('inhuren_info_form_recipient', get_option('admin_email'), $post_id);
    $subject = sprintf('Nieuwe informatie aanvraag van %s %s', $voornaam, $achternaam);
    $body = sprintf(
        '<p><strong>Naam:</strong> %s</p>
        <p><strong>E-mail:</strong> %s</p>
        <p><strong>Telefoon:</strong> %s</p>
        <p><strong>Pagina:</strong> %s</p>
        <hr>
        <p><strong>Bericht:</strong></p>%s',
        esc_html(trim($voornaam . ' ' . $achternaam)),
        esc_html($email),
        esc_html($telefoon ?: '-'),
        $page_url ? '<a href="' . esc_url($page_url) . '">' . esc_html($page_url) . '</a>' : '-',
        $message_html
    );

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: ' . sanitize_text_field(trim($voornaam . ' ' . $achternaam)) . ' <' . $email . '>',
    ];

    wp_mail($to, $subject, $body, $headers);

    wp_send_json_success([
        'message' => 'Bedankt voor je aanvraag. We nemen zo snel mogelijk contact met je op.',
    ]);
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
