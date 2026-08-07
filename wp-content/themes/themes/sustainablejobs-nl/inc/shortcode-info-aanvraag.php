<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [sj_info_aanvraag]
 * Informatieaanvraagformulier — 35% van 1200px breed blok.
 */
add_shortcode('sj_info_aanvraag', 'sj_info_aanvraag_shortcode');

function sj_info_aanvraag_shortcode(): string {
    $errors  = [];
    $success = false;

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['sj_ia_nonce']) &&
        wp_verify_nonce($_POST['sj_ia_nonce'], 'sj_info_aanvraag')
    ) {
        $voornaam    = sanitize_text_field($_POST['ia_voornaam']    ?? '');
        $achternaam  = sanitize_text_field($_POST['ia_achternaam']  ?? '');
        $telefoon    = sanitize_text_field($_POST['ia_telefoon']    ?? '');
        $email       = sanitize_email($_POST['ia_email']            ?? '');
        $bericht     = sanitize_textarea_field($_POST['ia_bericht'] ?? '');

        if (!$voornaam)        $errors[] = 'Vul je voornaam in.';
        if (!$achternaam)      $errors[] = 'Vul je achternaam in.';
        if (!is_email($email)) $errors[] = 'Vul een geldig e-mailadres in.';
        if (!$bericht)         $errors[] = 'Vul je bericht in.';

        if (empty($errors)) {
            $admin_email = get_option('admin_email');
            $onderwerp   = 'Nieuwe informatieaanvraag via Sustainablejobs.nl';

            $body  = "Nieuwe informatieaanvraag:\n\n";
            $body .= "Naam:        {$voornaam} {$achternaam}\n";
            $body .= "E-mail:      {$email}\n";
            if ($telefoon) $body .= "Telefoon:    {$telefoon}\n";
            $body .= "\nBericht:\n{$bericht}";

            wp_mail(
                $admin_email,
                $onderwerp,
                $body,
                [
                    'Content-Type: text/plain; charset=UTF-8',
                    "Reply-To: {$voornaam} {$achternaam} <{$email}>",
                ]
            );

            $success = true;
        }
    }

    ob_start();
    ?>

    <div class="sj-vp sj-vp--info-aanvraag">

        <?php if ($success): ?>

        <div class="sj-vp-notice sj-vp-notice--success">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M173.66,98.34a8,8,0,0,1,0,11.32l-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35A8,8,0,0,1,173.66,98.34ZM232,128A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"/></svg>
            <div>
                <strong>Aanvraag verstuurd!</strong>
                <p>We nemen zo snel mogelijk contact met je op.</p>
            </div>
        </div>

        <?php else: ?>

            <?php if (!empty($errors)): ?>
            <div class="sj-vp-notice sj-vp-notice--error">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M236.8,188.09,149.35,36.22a24.76,24.76,0,0,0-42.7,0L19.2,188.09a23.51,23.51,0,0,0,0,23.72A24.35,24.35,0,0,0,40.55,224h174.9a24.35,24.35,0,0,0,21.33-12.19A23.51,23.51,0,0,0,236.8,188.09ZM120,104a8,8,0,0,1,16,0v40a8,8,0,0,1-16,0Zm8,88a12,12,0,1,1,12-12A12,12,0,0,1,128,192Z"/></svg>
                <div>
                    <strong>Controleer de volgende velden:</strong>
                    <ul>
                        <?php foreach ($errors as $e): ?>
                            <li><?php echo esc_html($e); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <div class="sj-vp__block">
                <header class="sj-vp__header">
                    <h2 class="sj-vp__title">Informatie aanvragen</h2>
                    <p class="sj-vp__subtitle">Vul het formulier in en we nemen zo snel mogelijk contact met je op.</p>
                </header>

                <form method="post" class="sj-vp__form" novalidate>
                    <?php wp_nonce_field('sj_info_aanvraag', 'sj_ia_nonce'); ?>

                    <div class="sj-vp__section">
                        <p class="sj-vp__section-title">Contactgegevens</p>
                        <div class="sj-vp__grid sj-vp__grid--2">
                            <div class="sj-vp__field">
                                <label class="sj-vp__label" for="ia_voornaam">Voornaam <span class="sj-vp__req">*</span></label>
                                <input type="text" name="ia_voornaam" id="ia_voornaam" class="sj-vp__input"
                                       required
                                       value="<?php echo esc_attr($_POST['ia_voornaam'] ?? ''); ?>">
                            </div>

                            <div class="sj-vp__field">
                                <label class="sj-vp__label" for="ia_achternaam">Achternaam <span class="sj-vp__req">*</span></label>
                                <input type="text" name="ia_achternaam" id="ia_achternaam" class="sj-vp__input"
                                       required
                                       value="<?php echo esc_attr($_POST['ia_achternaam'] ?? ''); ?>">
                            </div>

                            <div class="sj-vp__field">
                                <label class="sj-vp__label" for="ia_email">E-mailadres <span class="sj-vp__req">*</span></label>
                                <input type="email" name="ia_email" id="ia_email" class="sj-vp__input"
                                       required
                                       value="<?php echo esc_attr($_POST['ia_email'] ?? ''); ?>">
                            </div>

                            <div class="sj-vp__field">
                                <label class="sj-vp__label" for="ia_telefoon">Telefoonnummer</label>
                                <input type="tel" name="ia_telefoon" id="ia_telefoon" class="sj-vp__input"
                                       value="<?php echo esc_attr($_POST['ia_telefoon'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="sj-vp__section">
                        <p class="sj-vp__section-title">Bericht</p>
                        <div class="sj-vp__grid sj-vp__grid--1">
                            <div class="sj-vp__field">
                                <label class="sj-vp__label" for="ia_bericht">Bericht <span class="sj-vp__req">*</span></label>
                                <textarea name="ia_bericht" id="ia_bericht" class="sj-vp__input sj-vp__textarea"
                                          required><?php echo esc_textarea($_POST['ia_bericht'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <footer class="sj-vp__footer">
                        <button type="submit" class="sj-vp__submit">Versturen</button>
                    </footer>
                </form>
            </div>

        <?php endif; ?>
    </div>

    <?php
    return ob_get_clean();
}
