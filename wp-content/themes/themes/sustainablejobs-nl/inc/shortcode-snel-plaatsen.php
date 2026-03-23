<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [sj_snel_plaatsen]
 * Snel een vacature plaatsen via e-mailadres + URL.
 */
add_shortcode('sj_snel_plaatsen', 'sj_snel_plaatsen_shortcode');

function sj_snel_plaatsen_shortcode(): string {

    $success = false;
    $errors  = [];

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['sj_sp_nonce']) &&
        wp_verify_nonce($_POST['sj_sp_nonce'], 'sj_snel_plaatsen')
    ) {
        $email  = sanitize_email($_POST['sp_email']  ?? '');
        $url    = esc_url_raw($_POST['sp_url']       ?? '');
        $pakket = sanitize_text_field($_POST['sp_pakket'] ?? '');

        if (!is_email($email)) $errors[] = 'Vul een geldig e-mailadres in.';
        if (!$url)             $errors[] = 'Vul de URL van de vacature in.';

        if (empty($errors)) {
            $body  = "Nieuwe snel-plaatsen aanvraag:\n\n";
            $body .= "Pakket: $pakket\n";
            $body .= "E-mail: $email\n";
            $body .= "Vacature URL: $url\n";

            $headers = ['Content-Type: text/plain; charset=UTF-8'];
            $to = array_unique(array_filter([
                'support@sustainablejobs.nl',
                get_option('admin_email'),
            ]));
            wp_mail($to, 'Snel plaatsen aanvraag', $body, $headers);

            /* Sla op als CPT */
            $post_id = wp_insert_post([
                'post_title'  => $url,
                'post_status' => 'pending',
                'post_type'   => 'sj_vacature',
                'post_author' => 0,
            ]);

            if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, '_sj_email',        $email);
                update_post_meta($post_id, '_sj_pakket',       $pakket ?: 'Snel plaatsen');
                update_post_meta($post_id, '_sj_vacature_url', $url);
            }

            $success = true;
        }
    }

    ob_start();

    if ($success): ?>

    <div class="sj-vp-notice sj-vp-notice--success">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M173.66,98.34a8,8,0,0,1,0,11.32l-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35A8,8,0,0,1,173.66,98.34ZM232,128A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"/></svg>
        <div>
            <strong>Aanvraag ontvangen!</strong>
            <p>We zetten je vacature zo snel mogelijk online. Je hoort van ons.</p>
        </div>
    </div>

    <?php else: ?>

    <?php if (!empty($errors)): ?>
    <div class="sj-vp-notice sj-vp-notice--error">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M236.8,188.09,149.35,36.22a24.76,24.76,0,0,0-42.7,0L19.2,188.09a23.51,23.51,0,0,0,0,23.72A24.35,24.35,0,0,0,40.55,224h174.9a24.35,24.35,0,0,0,21.33-12.19A23.51,23.51,0,0,0,236.8,188.09ZM120,104a8,8,0,0,1,16,0v40a8,8,0,0,1-16,0Zm8,88a12,12,0,1,1,12-12A12,12,0,0,1,128,192Z"/></svg>
        <div>
            <strong>Er zijn een paar fouten:</strong>
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?php echo esc_html($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <div class="sj-vp">
        <div class="sj-vp__block">

            <header class="sj-vp__header">
                <h2 class="sj-vp__title">Vacature Plaatsen (Verkort)</h2>
                <p class="sj-vp__subtitle">Ben je al bekend met onze organisatie (of heb je geen zin om het vacatureformulier in te vullen)? Deel dan alleen de link naar de vacature, dan doen wij de rest.</p>
            </header>

            <form method="post" class="sj-vp__form" novalidate>
                <?php wp_nonce_field('sj_snel_plaatsen', 'sj_sp_nonce'); ?>

                <div class="sj-vp__section">
                    <div class="sj-vp__grid sj-vp__grid--1">

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sp_pakket">Pakket <span class="sj-vp__req">*</span></label>
                            <select name="sp_pakket" id="sp_pakket" class="sj-vp__input" required>
                                <option value="" disabled <?php selected($_POST['sp_pakket'] ?? '', ''); ?>>Kies een pakket...</option>
                                <option value="Standaard Vacature: €275 excl. btw"         <?php selected($_POST['sp_pakket'] ?? '', 'Standaard Vacature: €275 excl. btw'); ?>>Standaard — €275,00 excl. btw</option>
                                <option value="Spotlight Vacature: €375 excl. btw"         <?php selected($_POST['sp_pakket'] ?? '', 'Spotlight Vacature: €375 excl. btw'); ?>>Spotlight — €375,00 excl. btw</option>
                                <option value="Stage & Vrijwilligerswerk: Gratis"           <?php selected($_POST['sp_pakket'] ?? '', 'Stage & Vrijwilligerswerk: Gratis'); ?>>Stage & Vrijwilligerswerk — Gratis</option>
                                <option value="Wij zijn lid van Sustainablejobs.nl: Gratis" <?php selected($_POST['sp_pakket'] ?? '', 'Wij zijn lid van Sustainablejobs.nl: Gratis'); ?>>Wij zijn lid — Gratis voor leden</option>
                                <option value="Wij hebben een strippenkaart"                <?php selected($_POST['sp_pakket'] ?? '', 'Wij hebben een strippenkaart'); ?>>Strippenkaart — Via strippenkaart</option>
                            </select>
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sp_email">E-mailadres <span class="sj-vp__req">*</span></label>
                            <input type="email" name="sp_email" id="sp_email" class="sj-vp__input"
                                   value="<?php echo esc_attr($_POST['sp_email'] ?? ''); ?>"
                                   placeholder="jan@bedrijf.nl" required>
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sp_url">Link naar de vacature <span class="sj-vp__req">*</span></label>
                            <input type="url" name="sp_url" id="sp_url" class="sj-vp__input"
                                   value="<?php echo esc_attr($_POST['sp_url'] ?? ''); ?>"
                                   placeholder="https://jouwbedrijf.nl/vacatures/functienaam" required>
                        </div>

                    </div>
                </div>

                <footer class="sj-vp__footer">
                    <button type="submit" class="sj-vp__submit">Vacature doorsturen</button>
                    <p class="sj-vp__footer-note">Nog makkelijker? Stuur de vacature gewoon per e-mail naar <a href="mailto:support@sustainablejobs.nl">support@sustainablejobs.nl</a>.</p>
                </footer>

            </form>

        </div>
    </div>

    <?php endif;

    return ob_get_clean();
}
