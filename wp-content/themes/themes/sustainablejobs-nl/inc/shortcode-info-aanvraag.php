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

    <div class="sj-ia">

        <?php if ($success): ?>

        <div class="sj-ia__notice sj-ia__notice--success">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M173.66,98.34a8,8,0,0,1,0,11.32l-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35A8,8,0,0,1,173.66,98.34ZM232,128A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"/></svg>
            <div>
                <strong>Aanvraag verstuurd!</strong>
                <p>We nemen zo snel mogelijk contact met je op.</p>
            </div>
        </div>

        <?php else: ?>

            <?php if (!empty($errors)): ?>
            <div class="sj-ia__notice sj-ia__notice--error">
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

            <div class="sj-ia__block">
                <header class="sj-ia__header">
                    <h2 class="sj-ia__title">Informatie aanvragen</h2>
                    <p class="sj-ia__subtitle">Vul het formulier in en we nemen zo snel mogelijk contact met je op.</p>
                </header>

                <form method="post" class="sj-ia__form" novalidate>
                    <?php wp_nonce_field('sj_info_aanvraag', 'sj_ia_nonce'); ?>

                    <div class="sj-ia__field">
                        <label class="sj-ia__label" for="ia_voornaam">Voornaam <span class="sj-ia__req">*</span></label>
                        <input type="text" name="ia_voornaam" id="ia_voornaam" class="sj-ia__input"
                               required
                               value="<?php echo esc_attr($_POST['ia_voornaam'] ?? ''); ?>">
                    </div>

                    <div class="sj-ia__field">
                        <label class="sj-ia__label" for="ia_achternaam">Achternaam <span class="sj-ia__req">*</span></label>
                        <input type="text" name="ia_achternaam" id="ia_achternaam" class="sj-ia__input"
                               required
                               value="<?php echo esc_attr($_POST['ia_achternaam'] ?? ''); ?>">
                    </div>

                    <div class="sj-ia__field">
                        <label class="sj-ia__label" for="ia_email">E-mailadres <span class="sj-ia__req">*</span></label>
                        <input type="email" name="ia_email" id="ia_email" class="sj-ia__input"
                               required
                               value="<?php echo esc_attr($_POST['ia_email'] ?? ''); ?>">
                    </div>

                    <div class="sj-ia__field">
                        <label class="sj-ia__label" for="ia_telefoon">Telefoonnummer</label>
                        <input type="tel" name="ia_telefoon" id="ia_telefoon" class="sj-ia__input"
                               value="<?php echo esc_attr($_POST['ia_telefoon'] ?? ''); ?>">
                    </div>

                    <div class="sj-ia__field">
                        <label class="sj-ia__label" for="ia_bericht">Bericht <span class="sj-ia__req">*</span></label>
                        <textarea name="ia_bericht" id="ia_bericht" class="sj-ia__input sj-ia__textarea"
                                  required><?php echo esc_textarea($_POST['ia_bericht'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="sj-ia__submit">Versturen</button>
                </form>
            </div>

        <?php endif; ?>
    </div>

    <style>
    .sj-ia {
        max-width: 420px;
        font-family: 'Poppins', sans-serif;
        box-sizing: border-box;
    }

    .sj-ia *,
    .sj-ia *::before,
    .sj-ia *::after { box-sizing: border-box; }

    .sj-ia__block {
        padding: 28px;
    }

    .sj-ia__header {
        margin-bottom: 24px;
        padding-bottom: 18px;
        border-bottom: 1px solid #DEDEDE;
    }

    .sj-ia__title {
        font-family: 'Inter', sans-serif;
        font-size: 20px;
        font-weight: 700;
        color: var(--color-primary, #2C8FAF);
        margin: 0 0 6px;
        line-height: 1.2;
    }

    .sj-ia__subtitle {
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        font-weight: 400;
        color: #6b7280;
        margin: 0;
        line-height: 1.5;
    }

    .sj-ia__form {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .sj-ia__field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .sj-ia__label {
        font-size: 14px;
        font-weight: 500;
        color: #333;
        line-height: 1.4;
    }

    .sj-ia__req {
        color: var(--color-primary, #2C8FAF);
        margin-left: 2px;
    }

    .sj-ia__input {
        width: 100% !important;
        padding: 10px 12px !important;
        font-family: 'Poppins', sans-serif !important;
        font-size: 14px !important;
        font-weight: 400 !important;
        color: #333 !important;
        background: #fff !important;
        border: 1px solid #DEDEDE !important;
        border-radius: 5px !important;
        outline: none !important;
        box-shadow: none !important;
        transition: border-color .2s ease, box-shadow .2s ease;
        line-height: 1.5 !important;
        height: auto !important;
    }

    .sj-ia__input:focus {
        border-color: var(--color-primary, #2C8FAF) !important;
        box-shadow: 0 0 0 3px rgba(44, 143, 175, .15) !important;
    }

    .sj-ia__input::placeholder {
        color: #9ca3af !important;
        font-weight: 300 !important;
    }

    .sj-ia__textarea {
        min-height: 130px;
        resize: vertical;
    }

    .sj-ia__submit {
        align-self: flex-start;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-family: 'Poppins', sans-serif;
        font-size: 15px;
        font-weight: 600;
        color: #ffffff !important;
        background: var(--color-primary, #2C8FAF) !important;
        border: none !important;
        border-radius: 5px !important;
        padding: 11px 28px !important;
        cursor: pointer;
        box-shadow: none !important;
        transition: opacity .15s ease;
        text-decoration: none !important;
        margin-top: 4px;
    }

    .sj-ia__submit:hover {
        opacity: 0.88;
        background: var(--color-primary, #2C8FAF) !important;
    }

    .sj-ia__notice {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 16px 18px;
        border-radius: 5px;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        line-height: 1.5;
        margin-bottom: 16px;
    }

    .sj-ia__notice--success {
        background: #edfdf4;
        border: 1px solid #a3e6c3;
        color: #1a6640;
    }

    .sj-ia__notice--error {
        background: #fff5f5;
        border: 1px solid #f5b7b7;
        color: #9b2020;
    }

    .sj-ia__notice svg { flex-shrink: 0; margin-top: 2px; }
    .sj-ia__notice strong { display: block; margin-bottom: 4px; font-weight: 600; }
    .sj-ia__notice p,
    .sj-ia__notice ul { margin: 0; }
    .sj-ia__notice ul { padding-left: 18px; }

    @media (max-width: 480px) {
        .sj-ia { max-width: 100%; }
        .sj-ia__block { padding: 20px; }
        .sj-ia__submit { width: 100%; align-self: stretch; }
    }
    </style>

    <?php
    return ob_get_clean();
}
