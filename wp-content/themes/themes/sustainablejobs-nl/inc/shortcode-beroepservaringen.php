<?php
if (!defined('ABSPATH')) exit;

if (!defined('SJ_BEROEPSERVARING_POST_TYPE')) {
    define('SJ_BEROEPSERVARING_POST_TYPE', 'sj_beroepservaring');
}

if (!defined('SJ_BEROEPSERVARING_PAGE_META')) {
    define('SJ_BEROEPSERVARING_PAGE_META', '_sj_beroep_page_id');
}

add_action('init', function () {
    register_post_type(SJ_BEROEPSERVARING_POST_TYPE, [
        'labels' => [
            'name'               => 'Beroepservaringen',
            'singular_name'      => 'Beroepservaring',
            'add_new_item'       => 'Nieuwe beroepservaring',
            'edit_item'          => 'Beroepservaring bekijken',
            'all_items'          => 'Alle beroepservaringen',
            'search_items'       => 'Zoek beroepservaringen',
            'not_found'          => 'Geen beroepservaringen gevonden.',
            'not_found_in_trash' => 'Geen beroepservaringen in de prullenbak.',
            'menu_name'          => 'Beroepservaringen',
        ],
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_icon'           => 'dashicons-format-chat',
        'menu_position'       => 27,
        'supports'            => ['title', 'editor'],
        'capability_type'     => 'post',
        'map_meta_cap'        => true,
        'show_in_rest'        => false,
        'rewrite'             => false,
        'query_var'           => false,
        'exclude_from_search' => true,
    ]);
});

add_shortcode('sj_beroepservaringen', 'sj_beroepservaringen_shortcode');
add_shortcode('beroepservaringen', 'sj_beroepservaringen_shortcode');

function sj_beroepservaringen_client_ip(): string {
    return sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? ''));
}

function sj_beroepservaringen_clean_redirect_url(string $url): string {
    return remove_query_arg(['sj_be_status', 'sj_be_notice'], $url);
}

function sj_beroepservaringen_get_current_url(int $page_id): string {
    $url = $page_id ? get_permalink($page_id) : '';

    if (!$url) {
        $url = wp_get_referer() ?: home_url('/');
    }

    return sj_beroepservaringen_clean_redirect_url($url);
}

function sj_beroepservaringen_redirect_with_notice(string $url, array $notice): void {
    $token = wp_generate_password(20, false, false);
    set_transient('sj_be_notice_' . $token, $notice, 10 * MINUTE_IN_SECONDS);

    wp_safe_redirect(add_query_arg([
        'sj_be_status' => 'error',
        'sj_be_notice' => $token,
    ], $url) . '#sj-beroepservaringen');
    exit;
}

function sj_beroepservaringen_captcha_question(): array {
    $left    = random_int(2, 9);
    $right   = random_int(2, 9);
    $answer  = (string) ($left + $right);
    $expires = (string) (time() + (2 * HOUR_IN_SECONDS));
    $hash    = hash_hmac('sha256', $answer . '|' . $expires, wp_salt('auth'));

    return [
        'label' => sprintf('Hoeveel is %d + %d?', $left, $right),
        'token' => $expires . ':' . $hash,
    ];
}

function sj_beroepservaringen_captcha_is_valid(string $answer, string $token): bool {
    $answer = trim($answer);
    $parts  = explode(':', $token, 2);

    if (count($parts) !== 2 || $answer === '') {
        return false;
    }

    [$expires, $hash] = $parts;

    if (!ctype_digit($expires) || (int) $expires < time()) {
        return false;
    }

    $expected = hash_hmac('sha256', $answer . '|' . $expires, wp_salt('auth'));
    return hash_equals($expected, $hash);
}

function sj_beroepservaringen_is_rate_limited(int $page_id, string $email): bool {
    $ip        = sj_beroepservaringen_client_ip();
    $email_key = 'sj_be_email_' . md5($page_id . '|' . strtolower($email) . '|' . $ip);
    $ip_key    = 'sj_be_ip_' . md5($page_id . '|' . $ip);

    $email_count = (int) get_transient($email_key);
    $ip_count    = (int) get_transient($ip_key);

    if ($email_count >= 3 || $ip_count >= 10) {
        return true;
    }

    set_transient($email_key, $email_count + 1, HOUR_IN_SECONDS);
    set_transient($ip_key, $ip_count + 1, HOUR_IN_SECONDS);

    return false;
}

function sj_beroepservaringen_post_value(string $key): string {
    if (!isset($_POST[$key]) || is_array($_POST[$key])) {
        return '';
    }

    return (string) wp_unslash($_POST[$key]);
}

add_action('template_redirect', function () {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || sj_beroepservaringen_post_value('sj_be_action') !== 'submit') {
        return;
    }

    $page_id      = absint(sj_beroepservaringen_post_value('sj_be_page_id'));
    $redirect_url = sj_beroepservaringen_get_current_url($page_id);
    $errors       = [];

    $nonce = sanitize_text_field(sj_beroepservaringen_post_value('sj_be_nonce'));
    if (!$page_id || !wp_verify_nonce($nonce, 'sj_beroepservaring_submit_' . $page_id)) {
        $errors[] = 'De beveiligingscontrole is verlopen. Probeer het formulier opnieuw te versturen.';
    }

    $page = $page_id ? get_post($page_id) : null;
    if (!$page || $page->post_type !== 'page') {
        $errors[] = 'De gekoppelde beroep-pagina kon niet worden gevonden.';
    }

    $name       = sanitize_text_field(sj_beroepservaringen_post_value('sj_be_name'));
    $email      = sanitize_email(sj_beroepservaringen_post_value('sj_be_email'));
    $experience = sanitize_textarea_field(sj_beroepservaringen_post_value('sj_be_experience'));
    $honeypot   = trim(sj_beroepservaringen_post_value('sj_be_website'));
    $started_at = absint(sj_beroepservaringen_post_value('sj_be_started_at'));
    $captcha    = sanitize_text_field(sj_beroepservaringen_post_value('sj_be_captcha'));
    $captcha_token = sanitize_text_field(sj_beroepservaringen_post_value('sj_be_captcha_token'));

    if ($name === '') {
        $errors[] = 'Vul je voornaam in.';
    }

    if (!is_email($email)) {
        $errors[] = 'Vul een geldig e-mailadres in.';
    }

    if (mb_strlen($experience) < 40) {
        $errors[] = 'Vertel iets meer over je ervaring, minimaal 40 tekens.';
    }

    if (mb_strlen($experience) > 2500) {
        $errors[] = 'Je ervaring is langer dan 2500 tekens. Maak je tekst iets korter.';
    }

    if ($honeypot !== '') {
        $errors[] = 'Je inzending kon niet worden verwerkt. Probeer het later opnieuw.';
    }

    $elapsed = time() - $started_at;
    if ($started_at <= 0 || $elapsed < 4 || $started_at > time() + 300) {
        $errors[] = 'Je inzending ging te snel. Probeer het formulier opnieuw te versturen.';
    }

    if (!sj_beroepservaringen_captcha_is_valid($captcha, $captcha_token)) {
        $errors[] = 'De anti-spam vraag is niet goed ingevuld.';
    }

    if (empty($errors) && sj_beroepservaringen_is_rate_limited($page_id, $email)) {
        $errors[] = 'Je hebt kort geleden al een ervaring ingestuurd. Probeer het later opnieuw.';
    }

    if (
        empty($errors) &&
        function_exists('wp_check_comment_disallowed_list') &&
        wp_check_comment_disallowed_list(
            $name,
            $email,
            '',
            $experience,
            sj_beroepservaringen_client_ip(),
            sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'] ?? ''))
        )
    ) {
        $errors[] = 'Je inzending kon niet worden verwerkt. Controleer je tekst en probeer het opnieuw.';
    }

    $values = [
        'name'       => $name,
        'email'      => $email,
        'experience' => $experience,
    ];

    if (!empty($errors)) {
        sj_beroepservaringen_redirect_with_notice($redirect_url, [
            'errors' => $errors,
            'values' => $values,
        ]);
    }

    $submission_id = wp_insert_post([
        'post_type'    => SJ_BEROEPSERVARING_POST_TYPE,
        'post_status'  => 'pending',
        'post_title'   => sanitize_text_field($name . ' over ' . get_the_title($page_id)),
        'post_content' => $experience,
        'post_author'  => 0,
    ], true);

    if (is_wp_error($submission_id)) {
        sj_beroepservaringen_redirect_with_notice($redirect_url, [
            'errors' => ['Je ervaring kon niet worden opgeslagen. Probeer het later opnieuw.'],
            'values' => $values,
        ]);
    }

    update_post_meta($submission_id, SJ_BEROEPSERVARING_PAGE_META, $page_id);
    update_post_meta($submission_id, '_sj_be_name', $name);
    update_post_meta($submission_id, '_sj_be_email', $email);
    update_post_meta($submission_id, '_sj_be_submitted_at', current_time('mysql'));

    $admin_email = get_option('admin_email');
    $edit_link   = get_edit_post_link($submission_id, '');
    $body        = "Nieuwe beroepservaring op Sustainablejobs.nl\n\n";
    $body       .= 'Beroep-pagina: ' . get_the_title($page_id) . "\n";
    $body       .= 'Naam: ' . $name . "\n";
    $body       .= 'E-mail: ' . $email . "\n\n";
    $body       .= "Ervaring:\n" . $experience . "\n\n";
    if ($edit_link) {
        $body .= 'Beoordelen: ' . $edit_link . "\n";
    }

    wp_mail($admin_email, 'Nieuwe beroepservaring ter beoordeling', $body);

    wp_safe_redirect(add_query_arg('sj_be_status', 'success', $redirect_url) . '#sj-beroepservaringen');
    exit;
});

function sj_beroepservaringen_get_notice(): array {
    $status = isset($_GET['sj_be_status']) && !is_array($_GET['sj_be_status'])
        ? sanitize_key(wp_unslash($_GET['sj_be_status']))
        : '';

    if ($status === 'success') {
        return [
            'type'    => 'success',
            'message' => 'Dank je wel. Je ervaring is ontvangen en verschijnt na controle op de pagina.',
            'errors'  => [],
            'values'  => [],
        ];
    }

    if ($status !== 'error') {
        return ['type' => '', 'message' => '', 'errors' => [], 'values' => []];
    }

    $token = isset($_GET['sj_be_notice']) && !is_array($_GET['sj_be_notice'])
        ? sanitize_text_field(wp_unslash($_GET['sj_be_notice']))
        : '';
    $notice = $token ? get_transient('sj_be_notice_' . $token) : null;

    if ($token) {
        delete_transient('sj_be_notice_' . $token);
    }

    if (!is_array($notice)) {
        return [
            'type'    => 'error',
            'message' => 'Je ervaring kon niet worden verwerkt. Probeer het opnieuw.',
            'errors'  => [],
            'values'  => [],
        ];
    }

    return [
        'type'    => 'error',
        'message' => '',
        'errors'  => array_map('sanitize_text_field', (array) ($notice['errors'] ?? [])),
        'values'  => [
            'name'       => sanitize_text_field($notice['values']['name'] ?? ''),
            'email'      => sanitize_email($notice['values']['email'] ?? ''),
            'experience' => sanitize_textarea_field($notice['values']['experience'] ?? ''),
        ],
    ];
}

function sj_beroepservaringen_get_items(int $page_id): array {
    return get_posts([
        'post_type'      => SJ_BEROEPSERVARING_POST_TYPE,
        'post_status'    => 'publish',
        'posts_per_page' => 20,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => [[
            'key'     => SJ_BEROEPSERVARING_PAGE_META,
            'value'   => $page_id,
            'compare' => '=',
        ]],
    ]);
}

function sj_beroepservaringen_shortcode($atts): string {
    $atts = shortcode_atts([
        'page_id' => 0,
        'title'   => 'Ervaringen uit de praktijk',
    ], $atts, 'sj_beroepservaringen');

    $page_id = absint($atts['page_id']) ?: get_the_ID();
    if (!$page_id || get_post_type($page_id) !== 'page') {
        return '';
    }

    $items    = sj_beroepservaringen_get_items($page_id);
    $notice   = sj_beroepservaringen_get_notice();
    $values   = $notice['values'] ?? [];
    $captcha  = sj_beroepservaringen_captcha_question();
    $form_id  = wp_unique_id('sj_be_form_');
    $field_id = wp_unique_id('sj_be_');

    ob_start();
    sj_beroepservaringen_print_assets();
    ?>
    <section class="sj-beroepservaringen" id="sj-beroepservaringen">
        <?php if (!empty($items)): ?>
            <div class="sj-beroepservaringen__list" aria-label="Ervaringen met dit beroep">
                <?php foreach ($items as $item):
                    $name = get_post_meta($item->ID, '_sj_be_name', true) ?: 'Anoniem';
                    ?>
                    <article class="sj-beroepservaringen__item">
                        <div class="sj-beroepservaringen__item-meta">
                            <strong><?php echo esc_html($name); ?></strong>
                            <time datetime="<?php echo esc_attr(get_the_date('c', $item)); ?>"><?php echo esc_html(get_the_date('j F Y', $item)); ?></time>
                        </div>
                        <div class="sj-beroepservaringen__item-content">
                            <?php echo wpautop(esc_html($item->post_content)); ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="sj-beroepservaringen__form" id="<?php echo esc_attr($form_id); ?>" method="post" action="<?php echo esc_url(get_permalink($page_id)); ?>#sj-beroepservaringen" novalidate>
            <h3>Deel jouw ervaring met dit beroep</h3>

            <?php if ($notice['type'] === 'success'): ?>
                <div class="sj-beroepservaringen__notice sj-beroepservaringen__notice--success">
                    <?php echo esc_html($notice['message']); ?>
                </div>
            <?php elseif ($notice['type'] === 'error'): ?>
                <div class="sj-beroepservaringen__notice sj-beroepservaringen__notice--error">
                    <?php if (!empty($notice['errors'])): ?>
                        <strong>Controleer je inzending:</strong>
                        <ul>
                            <?php foreach ($notice['errors'] as $error): ?>
                                <li><?php echo esc_html($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <?php echo esc_html($notice['message']); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php wp_nonce_field('sj_beroepservaring_submit_' . $page_id, 'sj_be_nonce'); ?>
            <input type="hidden" name="sj_be_action" value="submit">
            <input type="hidden" name="sj_be_page_id" value="<?php echo esc_attr((string) $page_id); ?>">
            <input type="hidden" name="sj_be_started_at" value="<?php echo esc_attr((string) time()); ?>">
            <input type="hidden" name="sj_be_captcha_token" value="<?php echo esc_attr($captcha['token']); ?>">

            <div class="sj-beroepservaringen__trap" aria-hidden="true">
                <label for="<?php echo esc_attr($field_id); ?>website">Website</label>
                <input type="text" id="<?php echo esc_attr($field_id); ?>website" name="sj_be_website" value="" tabindex="-1" autocomplete="off">
            </div>

            <div class="sj-beroepservaringen__grid">
                <div class="sj-beroepservaringen__field">
                    <label for="<?php echo esc_attr($field_id); ?>name">Voornaam <span>*</span></label>
                    <input type="text" id="<?php echo esc_attr($field_id); ?>name" name="sj_be_name" value="<?php echo esc_attr($values['name'] ?? ''); ?>" autocomplete="given-name" required>
                </div>

                <div class="sj-beroepservaringen__field">
                    <label for="<?php echo esc_attr($field_id); ?>email">E-mailadres <span>*</span></label>
                    <input type="email" id="<?php echo esc_attr($field_id); ?>email" name="sj_be_email" value="<?php echo esc_attr($values['email'] ?? ''); ?>" autocomplete="email" required>
                    <small>Je e-mailadres wordt niet gepubliceerd.</small>
                </div>
            </div>

            <div class="sj-beroepservaringen__field">
                <label for="<?php echo esc_attr($field_id); ?>experience">Mijn ervaring met dit beroep <span>*</span></label>
                <textarea id="<?php echo esc_attr($field_id); ?>experience" name="sj_be_experience" rows="7" maxlength="2500" required><?php echo esc_textarea($values['experience'] ?? ''); ?></textarea>
            </div>

            <div class="sj-beroepservaringen__footer">
                <div class="sj-beroepservaringen__field sj-beroepservaringen__field--captcha">
                    <label for="<?php echo esc_attr($field_id); ?>captcha">Anti-spam vraag: <?php echo esc_html($captcha['label']); ?> <span>*</span></label>
                    <input type="number" id="<?php echo esc_attr($field_id); ?>captcha" name="sj_be_captcha" inputmode="numeric" required>
                </div>

                <button type="submit">Ervaring insturen</button>
            </div>
        </form>
    </section>
    <?php
    return ob_get_clean();
}

function sj_beroepservaringen_print_assets(): void {
    static $printed = false;
    if ($printed) return;
    $printed = true;
    ?>
    <style>
    .sj-beroepservaringen,
    .sj-beroepservaringen * { box-sizing: border-box; }
    .sj-beroepservaringen {
        margin: 0;
        color: #333;
        font-family: 'Poppins', system-ui, sans-serif;
    }
    .sj-beroepservaringen__header {
        margin: 0;
    }
    .sj-beroepservaringen__header h2,
    .sj-beroepservaringen__form h3 {
        margin: 0 0 8px;
        color: #333;
        font-family: 'Work Sans', system-ui, sans-serif;
        font-weight: 700;
        line-height: 1.25;
    }
    .sj-beroepservaringen__header h2 {
        font-size: 26px;
    }
    .sj-beroepservaringen__form h3 {
        font-size: 21px;
    }
    .sj-beroepservaringen__header p {
        max-width: 720px;
        margin: 0;
        font-size: 15px;
        line-height: 1.65;
    }
    .sj-beroepservaringen__list {
        display: grid;
        gap: 14px;
        margin-bottom: 24px;
    }
    .sj-beroepservaringen__item {
        border: 0;
        border-radius: 0;
        padding: 0;
        background: transparent;
    }
    .sj-beroepservaringen__item-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 12px;
        align-items: baseline;
        margin-bottom: 8px;
    }
    .sj-beroepservaringen__item-meta strong {
        font-size: 15px;
    }
    .sj-beroepservaringen__item-meta time {
        color: #666;
        font-size: 13px;
    }
    .sj-beroepservaringen__item-content,
    .sj-beroepservaringen__item-content p {
        margin: 0;
        font-size: 15px;
        line-height: 1.7;
    }
    .sj-beroepservaringen__item-content p + p {
        margin-top: 10px;
    }
    .sj-beroepservaringen__form {
        border: 0;
        border-radius: 0;
        padding: 0;
        background: transparent;
    }
    .sj-beroepservaringen__grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
    .sj-beroepservaringen__field {
        margin-bottom: 16px;
    }
    .sj-beroepservaringen__field label {
        display: block;
        margin-bottom: 7px;
        font-size: 14px;
        font-weight: 600;
    }
    .sj-beroepservaringen__field label span {
        color: #168AAD;
    }
    .sj-beroepservaringen__field input,
    .sj-beroepservaringen__field textarea {
        width: 100%;
        border: 1px solid #D8D8D8;
        border-radius: 6px;
        padding: 8px 12px;
        background: #fff;
        color: #333;
        font: inherit;
        line-height: 1.45;
    }
    .sj-beroepservaringen__field textarea {
        min-height: 160px;
        resize: vertical;
    }
    .sj-beroepservaringen__field input:focus,
    .sj-beroepservaringen__field textarea:focus {
        border-color: #168AAD;
        outline: 2px solid rgba(22, 138, 173, .16);
    }
    .sj-beroepservaringen__field small {
        display: block;
        margin-top: 6px;
        color: #666;
        font-size: 12px;
    }
    .sj-beroepservaringen__footer {
        display: flex;
        gap: 16px;
        align-items: flex-end;
        justify-content: space-between;
    }
    .sj-beroepservaringen__field--captcha {
        flex: 1 1 260px;
        max-width: 360px;
        margin-bottom: 0;
    }
    .sj-beroepservaringen__footer button {
        flex: 0 0 auto;
        min-height: 48px;
        border: 0;
        border-radius: 6px;
        padding: 0 22px;
        background: #168AAD;
        color: #fff;
        cursor: pointer;
        font-family: 'Poppins', system-ui, sans-serif;
        font-size: 15px;
        font-weight: 700;
    }
    .sj-beroepservaringen__footer button:hover,
    .sj-beroepservaringen__footer button:focus {
        background: #117994;
    }
    .sj-beroepservaringen__notice {
        margin: 0 0 16px;
        border-radius: 6px;
        padding: 13px 15px;
        font-size: 14px;
        line-height: 1.55;
    }
    .sj-beroepservaringen__notice--success {
        border: 1px solid #A7D7B8;
        background: #EAF8EF;
        color: #166534;
    }
    .sj-beroepservaringen__notice--error {
        border: 1px solid #F0B4B4;
        background: #FDECEC;
        color: #8A1F1F;
    }
    .sj-beroepservaringen__notice ul {
        margin: 6px 0 0 18px;
    }
    .sj-beroepservaringen__trap {
        position: absolute;
        left: -9999px;
        width: 1px;
        height: 1px;
        overflow: hidden;
    }
    @media (max-width: 720px) {
        .sj-beroepservaringen__form {
            padding: 0;
        }
        .sj-beroepservaringen__grid,
        .sj-beroepservaringen__footer {
            grid-template-columns: 1fr;
            display: grid;
        }
        .sj-beroepservaringen__field--captcha {
            max-width: none;
        }
        .sj-beroepservaringen__footer button {
            width: 100%;
        }
    }
    </style>
    <?php
}

add_filter('manage_' . SJ_BEROEPSERVARING_POST_TYPE . '_posts_columns', function ($columns) {
    return [
        'cb'         => $columns['cb'],
        'title'      => 'Inzending',
        'sj_page'    => 'Beroep-pagina',
        'sj_name'    => 'Voornaam',
        'sj_email'   => 'E-mailadres',
        'date'       => 'Datum',
    ];
});

add_action('manage_' . SJ_BEROEPSERVARING_POST_TYPE . '_posts_custom_column', function ($column, $post_id) {
    if ($column === 'sj_page') {
        $page_id = (int) get_post_meta($post_id, SJ_BEROEPSERVARING_PAGE_META, true);
        $title   = $page_id ? get_the_title($page_id) : '';
        $link    = $page_id ? get_edit_post_link($page_id) : '';

        if ($title && $link) {
            echo '<a href="' . esc_url($link) . '">' . esc_html($title) . '</a>';
        } else {
            echo '-';
        }
    }

    if ($column === 'sj_name') {
        echo esc_html(get_post_meta($post_id, '_sj_be_name', true) ?: '-');
    }

    if ($column === 'sj_email') {
        $email = get_post_meta($post_id, '_sj_be_email', true);
        echo $email ? '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>' : '-';
    }
}, 10, 2);

add_action('add_meta_boxes', function () {
    add_meta_box(
        'sj_beroepservaring_details',
        'Details beroepservaring',
        'sj_beroepservaring_details_metabox',
        SJ_BEROEPSERVARING_POST_TYPE,
        'side',
        'default'
    );
});

function sj_beroepservaring_details_metabox($post): void {
    $page_id      = (int) get_post_meta($post->ID, SJ_BEROEPSERVARING_PAGE_META, true);
    $name         = get_post_meta($post->ID, '_sj_be_name', true);
    $email        = get_post_meta($post->ID, '_sj_be_email', true);
    $submitted_at = get_post_meta($post->ID, '_sj_be_submitted_at', true);
    ?>
    <p><strong>Beroep-pagina</strong><br>
        <?php if ($page_id): ?>
            <a href="<?php echo esc_url(get_edit_post_link($page_id)); ?>"><?php echo esc_html(get_the_title($page_id)); ?></a>
        <?php else: ?>
            -
        <?php endif; ?>
    </p>
    <p><strong>Voornaam</strong><br><?php echo esc_html($name ?: '-'); ?></p>
    <p><strong>E-mailadres</strong><br>
        <?php echo $email ? '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>' : '-'; ?>
    </p>
    <p><strong>Ingestuurd op</strong><br><?php echo $submitted_at ? esc_html(mysql2date('d M Y H:i', $submitted_at)) : '-'; ?></p>
    <p class="description">Publiceer deze inzending om hem zichtbaar te maken op de gekoppelde beroep-pagina.</p>
    <?php
}
