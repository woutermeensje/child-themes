<?php
if (!defined('ABSPATH')) exit;

/* ============================================================
   CUSTOM POST TYPE: si_opdracht
   Slaat elke inzending op als 'Opdracht' in de WordPress backend.
   ============================================================ */
add_action('init', function () {
    register_post_type('si_opdracht', [
        'label'               => 'Opdrachten',
        'labels'              => [
            'name'               => 'Opdrachten',
            'singular_name'      => 'Opdracht',
            'menu_name'          => 'Opdrachten',
            'all_items'          => 'Alle opdrachten',
            'view_item'          => 'Bekijk opdracht',
            'search_items'       => 'Zoek opdrachten',
            'not_found'          => 'Geen opdrachten gevonden.',
            'not_found_in_trash' => 'Geen opdrachten in de prullenbak.',
        ],
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_icon'           => 'dashicons-portfolio',
        'menu_position'       => 26,
        'supports'            => ['title'],
        'capability_type'     => 'post',
        'capabilities'        => [
            'create_posts' => 'do_not_allow',
        ],
        'map_meta_cap'        => true,
        'exclude_from_search' => true,
        'publicly_queryable'  => false,
        'has_archive'         => false,
    ]);
});

/* ── Admin kolommen ─────────────────────────────────────────── */
add_filter('manage_si_opdracht_posts_columns', function ($cols) {
    return [
        'cb'              => $cols['cb'],
        'title'           => 'Naam',
        'si_op_email'     => 'E-mail',
        'si_op_telefoon'  => 'Telefoon',
        'si_op_website'   => 'Website',
        'si_op_beschrijving' => 'Beschrijving (kort)',
        'date'            => 'Datum',
    ];
});

add_action('manage_si_opdracht_posts_custom_column', function ($col, $post_id) {
    switch ($col) {
        case 'si_op_email':
            $v = get_post_meta($post_id, '_si_op_email', true);
            echo $v ? '<a href="mailto:' . esc_attr($v) . '">' . esc_html($v) . '</a>' : '—';
            break;
        case 'si_op_telefoon':
            echo esc_html(get_post_meta($post_id, '_si_op_telefoon', true) ?: '—');
            break;
        case 'si_op_website':
            $v = get_post_meta($post_id, '_si_op_website', true);
            echo $v ? '<a href="' . esc_url($v) . '" target="_blank" rel="noopener">' . esc_html($v) . '</a>' : '—';
            break;
        case 'si_op_beschrijving':
            $b = strip_tags(get_post_meta($post_id, '_si_op_beschrijving', true));
            echo esc_html(mb_strimwidth($b, 0, 80, '…'));
            break;
    }
}, 10, 2);

add_filter('manage_edit-si_opdracht_sortable_columns', function ($cols) {
    $cols['date'] = 'date';
    return $cols;
});

/* ── Meta box: volledige inzending tonen in detailweergave ──── */
add_action('add_meta_boxes', function () {
    add_meta_box(
        'si_opdracht_details',
        'Opdrachtdetails',
        'si_opdracht_meta_box_cb',
        'si_opdracht',
        'normal',
        'high'
    );
});

function si_opdracht_meta_box_cb(WP_Post $post): void {
    $fields = [
        '_si_op_voornaam'    => 'Voornaam',
        '_si_op_achternaam'  => 'Achternaam',
        '_si_op_email'       => 'E-mail',
        '_si_op_telefoon'    => 'Telefoon',
        '_si_op_website'     => 'Website',
    ];
    echo '<table style="width:100%;border-collapse:collapse;">';
    foreach ($fields as $key => $label) {
        $val = get_post_meta($post->ID, $key, true);
        echo '<tr>';
        echo '<th style="text-align:left;padding:6px 10px 6px 0;width:130px;font-weight:600;">' . esc_html($label) . '</th>';
        echo '<td style="padding:6px 0;">';
        if ($key === '_si_op_email' && $val) {
            echo '<a href="mailto:' . esc_attr($val) . '">' . esc_html($val) . '</a>';
        } elseif ($key === '_si_op_website' && $val) {
            echo '<a href="' . esc_url($val) . '" target="_blank" rel="noopener">' . esc_html($val) . '</a>';
        } else {
            echo esc_html($val ?: '—');
        }
        echo '</td></tr>';
    }
    echo '</table>';
    $beschrijving = get_post_meta($post->ID, '_si_op_beschrijving', true);
    if ($beschrijving) {
        echo '<hr style="margin:14px 0;">';
        echo '<p style="font-weight:600;margin:0 0 8px;">Opdrachtbeschrijving</p>';
        echo '<div style="background:#f9f9f9;padding:12px;border:1px solid #ddd;border-radius:4px;">';
        echo wp_kses_post($beschrijving);
        echo '</div>';
    }
}

/* ============================================================
   SHORTCODE: [si_opdracht_plaatsen]
   ============================================================ */
add_shortcode('si_opdracht_plaatsen', 'si_opdracht_plaatsen_shortcode');

function si_opdracht_plaatsen_shortcode(): string {

    $errors = [];

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['si_op_nonce']) &&
        wp_verify_nonce($_POST['si_op_nonce'], 'si_opdracht_plaatsen')
    ) {
        $voornaam     = sanitize_text_field($_POST['voornaam']     ?? '');
        $achternaam   = sanitize_text_field($_POST['achternaam']   ?? '');
        $email        = sanitize_email($_POST['email']             ?? '');
        $telefoon     = sanitize_text_field($_POST['telefoon']     ?? '');
        $website      = esc_url_raw($_POST['website']             ?? '');
        $beschrijving = wp_kses_post($_POST['beschrijving']        ?? '');

        if (!si_rich_text_has_content($beschrijving)) {
            $beschrijving = '';
        }

        if (!$voornaam)           $errors[] = 'Vul je voornaam in.';
        if (!$achternaam)         $errors[] = 'Vul je achternaam in.';
        if (!is_email($email))    $errors[] = 'Vul een geldig e-mailadres in.';
        if (!$beschrijving)       $errors[] = 'Vul een opdrachtbeschrijving in.';

        if (empty($errors)) {
            $submission_id = sanitize_text_field($_POST['si_submission_id'] ?? '');

            if (si_is_duplicate_form_submission('opdracht_plaatsen', [
                'submission_id' => $submission_id,
                'voornaam'      => $voornaam,
                'achternaam'    => $achternaam,
                'email'         => strtolower($email),
                'telefoon'      => $telefoon,
                'website'       => $website,
                'beschrijving'  => wp_strip_all_tags($beschrijving),
            ])) {
                si_redirect_or_fallback(home_url('/bedankt-opdracht-plaatsen/'));
            }

            // ── Opslaan in de database ──────────────────────────
            $post_id = wp_insert_post([
                'post_type'   => 'si_opdracht',
                'post_title'  => sanitize_text_field("$voornaam $achternaam"),
                'post_status' => 'publish',
                'post_author' => 0,
            ]);

            if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, '_si_op_voornaam',    $voornaam);
                update_post_meta($post_id, '_si_op_achternaam',  $achternaam);
                update_post_meta($post_id, '_si_op_email',       $email);
                update_post_meta($post_id, '_si_op_telefoon',    $telefoon);
                update_post_meta($post_id, '_si_op_website',     $website);
                update_post_meta($post_id, '_si_op_beschrijving', $beschrijving);
            }

            // ── E-mailnotificatie ────────────────────────────────
            $body = si_build_admin_email(
                "Nieuwe opdracht van $voornaam $achternaam",
                'Er is een nieuwe opdracht geplaatst via het formulier op Studentinhuren.nl.',
                [
                    ['label' => 'Naam', 'value' => "$voornaam $achternaam"],
                    ['label' => 'E-mail', 'value' => $email, 'type' => 'email'],
                    ['label' => 'Telefoon', 'value' => $telefoon, 'type' => 'tel'],
                    ['label' => 'Website', 'value' => $website, 'type' => 'url'],
                ],
                'Opdrachtbeschrijving',
                $beschrijving,
                (int) $post_id
            );

            wp_mail(
                get_option('admin_email'),
                "Nieuwe opdracht van $voornaam $achternaam",
                $body,
                si_admin_mail_headers($email)
            );

            si_ac_subscribe_contact_to_list(STUDENTINHUREN_AC_OPDRACHT_PLAATSEN_LIST_ID, [
                'email'      => $email,
                'first_name' => $voornaam,
                'last_name'  => $achternaam,
                'phone'      => $telefoon,
            ]);

            si_redirect_or_fallback(home_url('/bedankt-opdracht-plaatsen/'));
        }
    }

    /* ── HTML ───────────────────────────────────────────────── */
    ob_start();

    if (!empty($errors)): ?>
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
                <h2 class="sj-vp__title">Opdracht plaatsen</h2>
                <p class="sj-vp__subtitle">Vul de gegevens in en we nemen zo snel mogelijk contact met je op over je opdracht.</p>
            </header>

            <form method="post" class="si-op__form" novalidate>
                <?php wp_nonce_field('si_opdracht_plaatsen', 'si_op_nonce'); ?>
                <input type="hidden" name="si_submission_id" value="<?php echo esc_attr($_POST['si_submission_id'] ?? wp_generate_uuid4()); ?>">

                <div class="sj-vp__section">
                    <p class="sj-vp__section-title">Contactgegevens</p>
                    <div class="sj-vp__grid sj-vp__grid--2">
                    <div class="sj-vp__field">
                        <label class="sj-vp__label" for="si_op_voornaam">Voornaam <span class="sj-vp__req">*</span></label>
                        <input type="text" name="voornaam" id="si_op_voornaam" class="sj-vp__input"
                               value="<?php echo esc_attr($_POST['voornaam'] ?? ''); ?>" required>
                    </div>
                    <div class="sj-vp__field">
                        <label class="sj-vp__label" for="si_op_achternaam">Achternaam <span class="sj-vp__req">*</span></label>
                        <input type="text" name="achternaam" id="si_op_achternaam" class="sj-vp__input"
                               value="<?php echo esc_attr($_POST['achternaam'] ?? ''); ?>" required>
                    </div>
                    <div class="sj-vp__field">
                        <label class="sj-vp__label" for="si_op_email">E-mailadres <span class="sj-vp__req">*</span></label>
                        <input type="email" name="email" id="si_op_email" class="sj-vp__input"
                               value="<?php echo esc_attr($_POST['email'] ?? ''); ?>" required>
                    </div>
                    <div class="sj-vp__field">
                        <label class="sj-vp__label" for="si_op_telefoon">Telefoonnummer</label>
                        <input type="tel" name="telefoon" id="si_op_telefoon" class="sj-vp__input"
                               value="<?php echo esc_attr($_POST['telefoon'] ?? ''); ?>">
                    </div>
                    </div>
                </div>

                <div class="sj-vp__section">
                    <p class="sj-vp__section-title">Opdracht informatie</p>
                    <div class="sj-vp__grid sj-vp__grid--1">
                    <div class="sj-vp__field">
                        <label class="sj-vp__label" for="si_op_website">Link naar website <span class="sj-vp__opt">(optioneel)</span></label>
                        <input type="url" name="website" id="si_op_website" class="sj-vp__input"
                               value="<?php echo esc_attr($_POST['website'] ?? ''); ?>">
                    </div>
                    <div class="sj-vp__field">
                        <label class="sj-vp__label" for="si_op_beschrijving_hidden">Opdrachtbeschrijving <span class="sj-vp__req">*</span></label>
                        <div class="sj-vp__quill-wrap">
                            <div id="si_quill_opdracht" class="sj-vp__quill-editor" style="min-height:200px;"></div>
                        </div>
                        <textarea name="beschrijving" id="si_op_beschrijving_hidden" class="sj-vp__quill-hidden" aria-hidden="true"><?php echo esc_textarea($_POST['beschrijving'] ?? ''); ?></textarea>
                        <span class="sj-vp__hint">Beschrijf de opdracht, gewenste inzet en eventueel de context of planning.</span>
                    </div>
                    </div>
                </div>

                <footer class="sj-vp__footer">
                    <button type="submit" class="sj-vp__submit">Opdracht versturen</button>
                    <p class="sj-vp__footer-note">Na je aanvraag nemen we contact met je op. Vragen? Mail naar <a href="mailto:support@student-inhuren.nl">support@student-inhuren.nl</a>.</p>
                </footer>

            </form>

        </div>
    </div>

    <script>
    (function () {
        function initQuillOpdracht() {
            if (typeof Quill === 'undefined') { setTimeout(initQuillOpdracht, 80); return; }

            var toolbarOptions = [
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['link'],
                ['clean']
            ];

            var beschrijvingHidden = document.getElementById('si_op_beschrijving_hidden');
            var quillOpdracht      = new Quill('#si_quill_opdracht', {
                theme: 'snow',
                modules: { toolbar: toolbarOptions }
            });

            if (beschrijvingHidden && beschrijvingHidden.value) {
                quillOpdracht.clipboard.dangerouslyPasteHTML(beschrijvingHidden.value);
            }

            quillOpdracht.on('text-change', function () {
                if (beschrijvingHidden) beschrijvingHidden.value = quillOpdracht.root.innerHTML;
            });

            var form = beschrijvingHidden ? beschrijvingHidden.closest('form') : null;
            if (form) {
                form.addEventListener('submit', function (event) {
                    if (form.dataset.siSubmitting === '1') {
                        event.preventDefault();
                        return;
                    }

                    if (beschrijvingHidden) beschrijvingHidden.value = quillOpdracht.root.innerHTML;
                    form.dataset.siSubmitting = '1';

                    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (button) {
                        button.disabled = true;
                        if (button.tagName === 'BUTTON') {
                            button.textContent = 'Bezig met versturen...';
                        }
                    });
                });
            }
        }
        initQuillOpdracht();
    })();
    </script>

    <?php
    return ob_get_clean();
}
