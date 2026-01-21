<?php
/**
 * Plugin Name: Feedback Plugin SJ
 * Description: Fixed feedback button + quick form (email + message) on every page.
 * Version: 0.2.0
 * Author: Sustainablejobs.nl
 */

if (!defined('ABSPATH')) exit;

define('FPSJ_NONCE_ACTION', 'fpsj_submit_feedback');

add_action('wp_enqueue_scripts', function () {
    if (is_admin()) return;

    $base = plugin_dir_url(__FILE__);

    wp_enqueue_style(
        'feedback-plugin-sj',
        $base . 'assets/fixed.css',
        [],
        '0.2.0'
    );

    wp_enqueue_script(
        'feedback-plugin-sj',
        $base . 'assets/fixed.js',
        [],
        '0.2.0',
        true
    );

    wp_localize_script('feedback-plugin-sj', 'FPSJ', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce(FPSJ_NONCE_ACTION),
        'labels'   => [
            'sending' => 'Versturen...',
            'sent'    => 'Dankjewel! Feedback ontvangen.',
            'error'   => 'Oeps, versturen lukte niet. Probeer het later opnieuw.',
        ],
    ]);
}, 20);

add_action('wp_footer', function () {
    if (is_admin()) return;

    ?>
    <div id="sj-feedback-root" class="fpsj" aria-live="polite">
        <button id="sj-feedback-btn" class="fpsj__btn" type="button" aria-expanded="false" aria-controls="sj-feedback-panel">
            Stel je vraag<br>of geef feedback!
        </button>

        <div id="sj-feedback-panel" class="fpsj__panel" hidden>
            <div class="fpsj__header">
                <div class="fpsj__title">Stel jouw vraag of deel jouw feedback.</div>
                <button type="button" class="fpsj__close" aria-label="Sluiten">×</button>
            </div>

            <form class="fpsj__form" autocomplete="off">
                <!-- honeypot (anti-spam) -->
                <input type="text" name="website" class="fpsj__hp" tabindex="-1" autocomplete="off" aria-hidden="true">

                <label class="fpsj__label" for="fpsj-email">E-mailadres (optioneel)</label>
                <input id="fpsj-email" name="email" type="email" class="fpsj__input" placeholder="jij@bedrijf.nl">

                <label class="fpsj__label" for="fpsj-message">Jouw feedback</label>
                <textarea id="fpsj-message" name="message" class="fpsj__textarea" rows="4"
                          placeholder="Wat kan beter op Sustainablejobs.nl?"></textarea>

                <input type="hidden" name="page_url" value="<?php echo esc_url( (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ); ?>">

                <button class="fpsj__submit" type="submit">Verstuur</button>

                <div class="fpsj__status" role="status" aria-live="polite"></div>
                <div class="fpsj__privacy">We gebruiken je feedback alleen om de site te verbeteren.</div>
            </form>
        </div>
    </div>
    <?php
}, 9999);

/**
 * AJAX handlers
 */
add_action('wp_ajax_fpsj_submit', 'fpsj_submit');
add_action('wp_ajax_nopriv_fpsj_submit', 'fpsj_submit');

function fpsj_submit() {
    check_ajax_referer(FPSJ_NONCE_ACTION, 'nonce');

    // rate limit: 1 per 30 sec per IP
    $ip = fpsj_get_ip();
    $rl_key = 'fpsj_rl_' . md5($ip ?: 'noip');
    if (get_transient($rl_key)) {
        wp_send_json_error(['message' => 'Te snel achter elkaar. Probeer het zo opnieuw.'], 429);
    }
    set_transient($rl_key, 1, 30);

    $honeypot = isset($_POST['website']) ? trim(wp_unslash($_POST['website'])) : '';
    if ($honeypot !== '') {
        // bot → doen alsof het gelukt is
        wp_send_json_success(['ok' => true]);
    }

    $email   = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $message = isset($_POST['message']) ? trim(wp_kses_post(wp_unslash($_POST['message']))) : '';
    $page_url = isset($_POST['page_url']) ? esc_url_raw(wp_unslash($_POST['page_url'])) : '';

    if ($message === '' || mb_strlen(wp_strip_all_tags($message)) < 5) {
        wp_send_json_error(['message' => 'Vul alsjeblieft wat feedback in.'], 400);
    }

    // Mail naar admin
    $admin_email = get_option('admin_email');
    $subject = 'Nieuwe feedback op Sustainablejobs.nl';
    $body =
        "Pagina: " . ($page_url ?: '-') . "\n" .
        "E-mail: " . ($email ?: '-') . "\n" .
        "IP: " . ($ip ?: '-') . "\n\n" .
        "Feedback:\n" . wp_strip_all_tags($message) . "\n";

    wp_mail($admin_email, $subject, $body);

    wp_send_json_success(['ok' => true]);
}

function fpsj_get_ip() {
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($keys as $k) {
        if (!empty($_SERVER[$k])) {
            $raw = wp_unslash($_SERVER[$k]);
            $ip = trim(explode(',', $raw)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '';
}
