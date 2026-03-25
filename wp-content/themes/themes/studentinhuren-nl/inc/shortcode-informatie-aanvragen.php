<?php
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [si_informatie_aanvragen]
 * Informatieaanvraagformulier met Quill rich text editor.
 */
add_shortcode('si_informatie_aanvragen', 'si_informatie_aanvragen_shortcode');

function si_informatie_aanvragen_shortcode(): string {

    /* ── Verwerking ─────────────────────────────────────────── */
    $success = false;
    $errors  = [];

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['si_ia_nonce']) &&
        wp_verify_nonce($_POST['si_ia_nonce'], 'si_informatie_aanvragen')
    ) {
        $voornaam     = sanitize_text_field($_POST['voornaam']     ?? '');
        $achternaam   = sanitize_text_field($_POST['achternaam']   ?? '');
        $email        = sanitize_email($_POST['email']             ?? '');
        $telefoon     = sanitize_text_field($_POST['telefoon']     ?? '');
        $bericht      = wp_kses_post($_POST['bericht']             ?? '');

        if (!$voornaam)        $errors[] = 'Vul je voornaam in.';
        if (!$achternaam)      $errors[] = 'Vul je achternaam in.';
        if (!is_email($email)) $errors[] = 'Vul een geldig e-mailadres in.';
        if (!$bericht)         $errors[] = 'Vul een bericht in.';

        if (empty($errors)) {
            $body  = "Nieuwe informatieaanvraag via het formulier:\n\n";
            $body .= "Naam: $voornaam $achternaam\n";
            $body .= "E-mail: $email\n";
            $body .= "Telefoon: $telefoon\n\n";
            $body .= "--- Bericht ---\n" . strip_tags($bericht) . "\n";

            $headers = [
                'Content-Type: text/plain; charset=UTF-8',
                'Reply-To: ' . $email,
            ];

            wp_mail(
                get_option('admin_email'),
                "Informatieaanvraag van $voornaam $achternaam",
                $body,
                $headers
            );

            wp_redirect(home_url('/bedankt-informatie-aanvraag/'));
            exit;
        }
    }

    /* ── HTML opbouwen ──────────────────────────────────────── */
    ob_start();

    if ($success): ?>

    <div class="si-ia-notice si-ia-notice--success">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M173.66,98.34a8,8,0,0,1,0,11.32l-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35A8,8,0,0,1,173.66,98.34ZM232,128A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"/></svg>
        <div>
            <strong>Aanvraag succesvol verzonden!</strong>
            <p>We nemen zo snel mogelijk contact met je op.</p>
        </div>
    </div>

    <?php else: ?>

    <?php if (!empty($errors)): ?>
    <div class="si-ia-notice si-ia-notice--error">
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

    <div class="si-ia">
        <div class="si-ia__block">

            <header class="si-ia__header">
                <h2 class="si-ia__title">Informatie aanvragen</h2>
            </header>

            <form method="post" class="si-ia__form" novalidate>
                <?php wp_nonce_field('si_informatie_aanvragen', 'si_ia_nonce'); ?>

                <div class="si-ia__grid si-ia__grid--2">
                    <div class="si-ia__field">
                        <label class="si-ia__label" for="si_voornaam">Voornaam <span class="si-ia__req">*</span></label>
                        <input type="text" name="voornaam" id="si_voornaam" class="si-ia__input"
                               value="<?php echo esc_attr($_POST['voornaam'] ?? ''); ?>" placeholder="Jan" required>
                    </div>
                    <div class="si-ia__field">
                        <label class="si-ia__label" for="si_achternaam">Achternaam <span class="si-ia__req">*</span></label>
                        <input type="text" name="achternaam" id="si_achternaam" class="si-ia__input"
                               value="<?php echo esc_attr($_POST['achternaam'] ?? ''); ?>" placeholder="de Vries" required>
                    </div>
                </div>

                <div class="si-ia__grid si-ia__grid--2">
                    <div class="si-ia__field">
                        <label class="si-ia__label" for="si_email">E-mailadres <span class="si-ia__req">*</span></label>
                        <input type="email" name="email" id="si_email" class="si-ia__input"
                               value="<?php echo esc_attr($_POST['email'] ?? ''); ?>" placeholder="jan@bedrijf.nl" required>
                    </div>
                    <div class="si-ia__field">
                        <label class="si-ia__label" for="si_telefoon">Telefoonnummer</label>
                        <input type="tel" name="telefoon" id="si_telefoon" class="si-ia__input"
                               value="<?php echo esc_attr($_POST['telefoon'] ?? ''); ?>" placeholder="+31 6 00000000">
                    </div>
                </div>

                <div class="si-ia__grid si-ia__grid--1">
                    <div class="si-ia__field">
                        <label class="si-ia__label" for="si_bericht_hidden">Bericht <span class="si-ia__req">*</span></label>
                        <div class="si-ia__quill-wrap">
                            <div id="si_quill_bericht" class="si-ia__quill-editor" style="min-height:200px;"></div>
                        </div>
                        <textarea name="bericht" id="si_bericht_hidden" class="si-ia__quill-hidden" aria-hidden="true"><?php echo esc_textarea($_POST['bericht'] ?? ''); ?></textarea>
                    </div>
                </div>

                <footer class="si-ia__footer">
                    <button type="submit" class="si-ia__submit">Aanvraag versturen</button>
                </footer>

            </form>

        </div>
    </div>

    <script>
    (function () {
        function initQuill() {
            if (typeof Quill === 'undefined') {
                setTimeout(initQuill, 80);
                return;
            }

            var toolbarOptions = [
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['link'],
                ['clean']
            ];

            var berichtHidden = document.getElementById('si_bericht_hidden');
            var quillBericht  = new Quill('#si_quill_bericht', {
                theme: 'snow',
                modules: { toolbar: toolbarOptions },
                placeholder: 'Schrijf hier je bericht...'
            });

            if (berichtHidden && berichtHidden.value) {
                quillBericht.clipboard.dangerouslyPasteHTML(berichtHidden.value);
            }

            quillBericht.on('text-change', function () {
                if (berichtHidden) {
                    berichtHidden.value = quillBericht.root.innerHTML;
                }
            });

            var form = document.querySelector('.si-ia__form');
            if (form) {
                form.addEventListener('submit', function () {
                    if (berichtHidden) berichtHidden.value = quillBericht.root.innerHTML;
                });
            }
        }

        initQuill();
    })();
    </script>

    <?php endif; ?>

    <?php
    return ob_get_clean();
}
