<?php
if (!defined('ABSPATH')) exit;

/* ============================================================
   SHORTCODE: [si_informatie_aanvragen_compact]
   Compact formulier voor smalle blokken (max ~300px).
   Velden: voornaam, achternaam, email, telefoon — gestapeld.
   ============================================================ */
add_shortcode('si_informatie_aanvragen_compact', 'si_informatie_aanvragen_compact_shortcode');

function si_informatie_aanvragen_compact_shortcode(): string {

    $errors = [];

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['si_iac_nonce']) &&
        wp_verify_nonce($_POST['si_iac_nonce'], 'si_informatie_aanvragen_compact')
    ) {
        $voornaam   = sanitize_text_field($_POST['iac_voornaam']   ?? '');
        $achternaam = sanitize_text_field($_POST['iac_achternaam'] ?? '');
        $email      = sanitize_email($_POST['iac_email']           ?? '');
        $telefoon   = sanitize_text_field($_POST['iac_telefoon']   ?? '');
        $bericht    = wp_kses_post($_POST['iac_bericht']           ?? '');

        if (!si_rich_text_has_content($bericht)) {
            $bericht = '';
        }

        if (!$voornaam)        $errors[] = 'Vul je voornaam in.';
        if (!$achternaam)      $errors[] = 'Vul je achternaam in.';
        if (!is_email($email)) $errors[] = 'Vul een geldig e-mailadres in.';

        if (empty($errors)) {

            // ── Opslaan in de database ──────────────────────────
            $post_id = wp_insert_post([
                'post_type'   => 'si_aanvraag',
                'post_title'  => sanitize_text_field("$voornaam $achternaam"),
                'post_status' => 'publish',
                'post_author' => 0,
            ]);

            if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, '_si_voornaam',   $voornaam);
                update_post_meta($post_id, '_si_achternaam', $achternaam);
                update_post_meta($post_id, '_si_email',      $email);
                update_post_meta($post_id, '_si_telefoon',   $telefoon);
                update_post_meta($post_id, '_si_bericht',    $bericht);
            }

            // ── E-mailnotificatie ────────────────────────────────
            $body  = "Nieuwe informatieaanvraag (compact formulier):\n\n";
            $body .= "Naam: $voornaam $achternaam\n";
            $body .= "E-mail: $email\n";
            $body .= "Telefoon: $telefoon\n";
            if ($bericht) {
                $body .= "\n--- Bericht ---\n" . wp_strip_all_tags($bericht) . "\n";
            }

            wp_mail(
                get_option('admin_email'),
                "Informatieaanvraag van $voornaam $achternaam",
                $body,
                [
                    'Content-Type: text/plain; charset=UTF-8',
                    'Reply-To: ' . $email,
                ]
            );

            si_redirect_or_fallback(home_url('/bedankt-informatie-aanvraag/'));
        }
    }

    /* ── HTML ───────────────────────────────────────────────── */
    ob_start();

    if (!empty($errors)): ?>
    <div class="si-iac-notice si-iac-notice--error">
        <strong>Fout:</strong>
        <ul>
            <?php foreach ($errors as $e): ?>
                <li><?php echo esc_html($e); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="si-iac">
        <h2 class="si-iac__title">Informatie aanvragen</h2>
        <form method="post" class="si-iac__form" novalidate>
            <?php wp_nonce_field('si_informatie_aanvragen_compact', 'si_iac_nonce'); ?>

            <div class="si-iac__field">
                <label class="si-iac__label" for="iac_voornaam">Voornaam <span class="si-iac__req">*</span></label>
                <input type="text" name="iac_voornaam" id="iac_voornaam" class="si-iac__input"
                       value="<?php echo esc_attr($_POST['iac_voornaam'] ?? ''); ?>" required>
            </div>

            <div class="si-iac__field">
                <label class="si-iac__label" for="iac_achternaam">Achternaam <span class="si-iac__req">*</span></label>
                <input type="text" name="iac_achternaam" id="iac_achternaam" class="si-iac__input"
                       value="<?php echo esc_attr($_POST['iac_achternaam'] ?? ''); ?>" required>
            </div>

            <div class="si-iac__field">
                <label class="si-iac__label" for="iac_email">E-mailadres <span class="si-iac__req">*</span></label>
                <input type="email" name="iac_email" id="iac_email" class="si-iac__input"
                       value="<?php echo esc_attr($_POST['iac_email'] ?? ''); ?>" required>
            </div>

            <div class="si-iac__field">
                <label class="si-iac__label" for="iac_telefoon">Telefoonnummer</label>
                <input type="tel" name="iac_telefoon" id="iac_telefoon" class="si-iac__input"
                       value="<?php echo esc_attr($_POST['iac_telefoon'] ?? ''); ?>">
            </div>

            <div class="si-iac__field">
                <label class="si-iac__label" for="iac_bericht_hidden">Bericht</label>
                <div class="si-iac__quill-wrap si-ia__quill-wrap">
                    <div id="si_iac_quill_bericht" class="si-iac__quill-editor si-ia__quill-editor" style="min-height:140px;"></div>
                </div>
                <textarea name="iac_bericht" id="iac_bericht_hidden" class="si-ia__quill-hidden" aria-hidden="true"><?php echo esc_textarea($_POST['iac_bericht'] ?? ''); ?></textarea>
            </div>

            <div class="si-iac__footer">
                <button type="submit" class="si-iac__submit">Aanvraag versturen</button>
            </div>

        </form>
    </div>

    <script>
    (function () {
        function initCompactQuill() {
            if (typeof Quill === 'undefined') { setTimeout(initCompactQuill, 80); return; }

            var target = document.getElementById('si_iac_quill_bericht');
            var berichtHidden = document.getElementById('iac_bericht_hidden');
            var form = berichtHidden ? berichtHidden.closest('form') : null;

            if (!target || !berichtHidden || !form || target.dataset.quillReady === '1') {
                return;
            }

            target.dataset.quillReady = '1';

            var toolbarOptions = [
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['link'],
                ['clean']
            ];

            var quillBericht = new Quill(target, {
                theme: 'snow',
                modules: { toolbar: toolbarOptions }
            });

            if (berichtHidden.value) {
                quillBericht.clipboard.dangerouslyPasteHTML(berichtHidden.value);
            }

            quillBericht.on('text-change', function () {
                berichtHidden.value = quillBericht.root.innerHTML;
            });

            form.addEventListener('submit', function () {
                berichtHidden.value = quillBericht.root.innerHTML;
            });
        }

        initCompactQuill();
    })();
    </script>

    <?php
    return ob_get_clean();
}
