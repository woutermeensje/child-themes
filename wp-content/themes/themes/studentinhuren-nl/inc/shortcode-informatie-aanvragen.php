<?php
if (!defined('ABSPATH')) exit;

/* ============================================================
   CUSTOM POST TYPE: si_aanvraag
   Slaat elke inzending op als 'Aanvraag' in de WordPress backend.
   ============================================================ */
add_action('init', function () {
    register_post_type('si_aanvraag', [
        'label'               => 'Aanvragen',
        'labels'              => [
            'name'               => 'Aanvragen',
            'singular_name'      => 'Aanvraag',
            'menu_name'          => 'Aanvragen',
            'all_items'          => 'Alle aanvragen',
            'view_item'          => 'Bekijk aanvraag',
            'search_items'       => 'Zoek aanvragen',
            'not_found'          => 'Geen aanvragen gevonden.',
            'not_found_in_trash' => 'Geen aanvragen in de prullenbak.',
        ],
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_icon'           => 'dashicons-email-alt',
        'menu_position'       => 25,
        'supports'            => ['title'],
        'capability_type'     => 'post',
        'capabilities'        => [
            'create_posts' => 'do_not_allow',   // Alleen via formulier aanmaken
        ],
        'map_meta_cap'        => true,
        'exclude_from_search' => true,
        'publicly_queryable'  => false,
        'has_archive'         => false,
    ]);
});

/* ── Admin kolommen ─────────────────────────────────────────── */
add_filter('manage_si_aanvraag_posts_columns', function ($cols) {
    return [
        'cb'           => $cols['cb'],
        'title'        => 'Naam',
        'si_email'     => 'E-mail',
        'si_telefoon'  => 'Telefoon',
        'si_bericht'   => 'Bericht (kort)',
        'date'         => 'Datum',
    ];
});

add_action('manage_si_aanvraag_posts_custom_column', function ($col, $post_id) {
    switch ($col) {
        case 'si_email':
            $v = get_post_meta($post_id, '_si_email', true);
            echo $v ? '<a href="mailto:' . esc_attr($v) . '">' . esc_html($v) . '</a>' : '—';
            break;
        case 'si_telefoon':
            echo esc_html(get_post_meta($post_id, '_si_telefoon', true) ?: '—');
            break;
        case 'si_bericht':
            $b = strip_tags(get_post_meta($post_id, '_si_bericht', true));
            echo esc_html(mb_strimwidth($b, 0, 80, '…'));
            break;
    }
}, 10, 2);

add_filter('manage_edit-si_aanvraag_sortable_columns', function ($cols) {
    $cols['date'] = 'date';
    return $cols;
});

/* ── Meta box: volledige inzending tonen in detailweergave ──── */
add_action('add_meta_boxes', function () {
    add_meta_box(
        'si_aanvraag_details',
        'Aanvraagdetails',
        'si_aanvraag_meta_box_cb',
        'si_aanvraag',
        'normal',
        'high'
    );
});

function si_aanvraag_meta_box_cb(WP_Post $post): void {
    $fields = [
        '_si_voornaam'  => 'Voornaam',
        '_si_achternaam'=> 'Achternaam',
        '_si_email'     => 'E-mail',
        '_si_telefoon'  => 'Telefoon',
    ];
    echo '<table style="width:100%;border-collapse:collapse;">';
    foreach ($fields as $key => $label) {
        $val = get_post_meta($post->ID, $key, true);
        echo '<tr>';
        echo '<th style="text-align:left;padding:6px 10px 6px 0;width:130px;font-weight:600;">' . esc_html($label) . '</th>';
        echo '<td style="padding:6px 0;">';
        if ($key === '_si_email' && $val) {
            echo '<a href="mailto:' . esc_attr($val) . '">' . esc_html($val) . '</a>';
        } else {
            echo esc_html($val ?: '—');
        }
        echo '</td></tr>';
    }
    echo '</table>';
    $bericht = get_post_meta($post->ID, '_si_bericht', true);
    if ($bericht) {
        echo '<hr style="margin:14px 0;">';
        echo '<p style="font-weight:600;margin:0 0 8px;">Bericht</p>';
        echo '<div style="background:#f9f9f9;padding:12px;border:1px solid #ddd;border-radius:4px;">';
        echo wp_kses_post($bericht);
        echo '</div>';
    }
}

/* ============================================================
   SHORTCODE: [si_informatie_aanvragen]
   ============================================================ */
add_shortcode('si_informatie_aanvragen', 'si_informatie_aanvragen_shortcode');

function si_informatie_aanvragen_shortcode(): string {

    $success = false;
    $errors  = [];

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['si_ia_nonce']) &&
        wp_verify_nonce($_POST['si_ia_nonce'], 'si_informatie_aanvragen')
    ) {
        $voornaam   = sanitize_text_field($_POST['voornaam']   ?? '');
        $achternaam = sanitize_text_field($_POST['achternaam'] ?? '');
        $email      = sanitize_email($_POST['email']           ?? '');
        $telefoon   = sanitize_text_field($_POST['telefoon']   ?? '');
        $bericht_raw = sanitize_textarea_field(wp_unslash($_POST['bericht'] ?? ''));
        $bericht     = wpautop($bericht_raw);

        if (!si_rich_text_has_content($bericht)) {
            $bericht = '';
        }

        if (!$voornaam)        $errors[] = 'Vul je voornaam in.';
        if (!$achternaam)      $errors[] = 'Vul je achternaam in.';
        if (!is_email($email)) $errors[] = 'Vul een geldig e-mailadres in.';
        if (!$bericht)         $errors[] = 'Vul een bericht in.';

        if (empty($errors)) {
            $submission_id = sanitize_text_field($_POST['si_submission_id'] ?? '');

            if (si_is_duplicate_form_submission('informatie_aanvragen', [
                'submission_id' => $submission_id,
                'voornaam'      => $voornaam,
                'achternaam'    => $achternaam,
                'email'         => strtolower($email),
                'telefoon'      => $telefoon,
                'bericht'       => wp_strip_all_tags($bericht),
            ])) {
                si_redirect_or_fallback(home_url('/bedankt-informatie-aanvraag/'));
            }

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
            $body = si_build_admin_email(
                "Informatieaanvraag van $voornaam $achternaam",
                'Er is een nieuwe informatieaanvraag binnengekomen via het formulier op Studentinhuren.nl.',
                [
                    ['label' => 'Naam', 'value' => "$voornaam $achternaam"],
                    ['label' => 'E-mail', 'value' => $email, 'type' => 'email'],
                    ['label' => 'Telefoon', 'value' => $telefoon, 'type' => 'tel'],
                ],
                'Bericht',
                $bericht,
                (int) $post_id
            );

            $mail_sent = wp_mail(
                si_admin_notification_recipients(),
                "Informatieaanvraag van $voornaam $achternaam",
                $body,
                si_admin_mail_headers($email)
            );

            if (!$mail_sent) {
                error_log('[SI formulier] Notificatie informatieaanvraag kon niet worden verzonden.');
            }

            si_ac_subscribe_contact_to_list(STUDENTINHUREN_AC_AANVRAGEN_LIST_ID, [
                'email'      => $email,
                'first_name' => $voornaam,
                'last_name'  => $achternaam,
                'phone'      => $telefoon,
            ]);

            si_redirect_or_fallback(home_url('/bedankt-informatie-aanvraag/'));
        }
    }

    /* ── HTML ───────────────────────────────────────────────── */
    ob_start();

    if (!empty($errors)): ?>
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
                <input type="hidden" name="si_submission_id" value="<?php echo esc_attr($_POST['si_submission_id'] ?? wp_generate_uuid4()); ?>">

                <div class="si-ia__grid si-ia__grid--2">
                    <div class="si-ia__field">
                        <label class="si-ia__label" for="si_voornaam">Voornaam <span class="si-ia__req">*</span></label>
                        <input type="text" name="voornaam" id="si_voornaam" class="si-ia__input"
                               value="<?php echo esc_attr($_POST['voornaam'] ?? ''); ?>" required>
                    </div>
                    <div class="si-ia__field">
                        <label class="si-ia__label" for="si_achternaam">Achternaam <span class="si-ia__req">*</span></label>
                        <input type="text" name="achternaam" id="si_achternaam" class="si-ia__input"
                               value="<?php echo esc_attr($_POST['achternaam'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="si-ia__grid si-ia__grid--2">
                    <div class="si-ia__field">
                        <label class="si-ia__label" for="si_email">E-mailadres <span class="si-ia__req">*</span></label>
                        <input type="email" name="email" id="si_email" class="si-ia__input"
                               value="<?php echo esc_attr($_POST['email'] ?? ''); ?>" required>
                    </div>
                    <div class="si-ia__field">
                        <label class="si-ia__label" for="si_telefoon">Telefoonnummer</label>
                        <input type="tel" name="telefoon" id="si_telefoon" class="si-ia__input"
                               value="<?php echo esc_attr($_POST['telefoon'] ?? ''); ?>">
                    </div>
                </div>

                <div class="si-ia__grid si-ia__grid--1">
                    <div class="si-ia__field">
                        <label class="si-ia__label" for="si_bericht">Bericht <span class="si-ia__req">*</span></label>
                        <textarea name="bericht" id="si_bericht" class="si-ia__input si-ia__textarea" required><?php echo esc_textarea(wp_unslash($_POST['bericht'] ?? '')); ?></textarea>
                    </div>
                </div>

                <footer class="si-ia__footer">
                    <button type="submit" class="si-ia__submit">Aanvraag versturen</button>
                </footer>

            </form>

        </div>
    </div>
    <?php
    return ob_get_clean();
}
