<?php
if (!defined('ABSPATH')) exit;

/* ============================================================
   CUSTOM POST TYPE: si_vacature_aanvraag
   Slaat elke vacature-inzending op in de WordPress backend.
   ============================================================ */
add_action('init', function () {
    register_post_type('si_vacature_aanvraag', [
        'label'               => 'Vacature aanvragen',
        'labels'              => [
            'name'               => 'Vacature aanvragen',
            'singular_name'      => 'Vacature aanvraag',
            'menu_name'          => 'Vacature aanvragen',
            'all_items'          => 'Alle vacature aanvragen',
            'view_item'          => 'Bekijk vacature aanvraag',
            'search_items'       => 'Zoek vacature aanvragen',
            'not_found'          => 'Geen vacature aanvragen gevonden.',
            'not_found_in_trash' => 'Geen vacature aanvragen in de prullenbak.',
        ],
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_icon'           => 'dashicons-id-alt',
        'menu_position'       => 27,
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

add_filter('manage_si_vacature_aanvraag_posts_columns', function ($cols) {
    return [
        'cb'                  => $cols['cb'],
        'title'               => 'Vacature',
        'si_vac_bedrijf'      => 'Bedrijf',
        'si_vac_email'        => 'E-mail',
        'si_vac_pakket'       => 'Pakket',
        'date'                => 'Datum',
    ];
});

add_action('manage_si_vacature_aanvraag_posts_custom_column', function ($col, $post_id) {
    switch ($col) {
        case 'si_vac_bedrijf':
            echo esc_html(get_post_meta($post_id, '_si_vac_bedrijfsnaam', true) ?: '—');
            break;
        case 'si_vac_email':
            $email = get_post_meta($post_id, '_si_vac_email', true);
            echo $email ? '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>' : '—';
            break;
        case 'si_vac_pakket':
            echo esc_html(get_post_meta($post_id, '_si_vac_pakket', true) ?: '—');
            break;
    }
}, 10, 2);

add_action('add_meta_boxes', function () {
    add_meta_box(
        'si_vacature_aanvraag_details',
        'Vacaturedetails',
        'si_vacature_aanvraag_meta_box_cb',
        'si_vacature_aanvraag',
        'normal',
        'high'
    );
});

function si_vacature_aanvraag_meta_box_cb(WP_Post $post): void
{
    $fields = [
        '_si_vac_pakket'       => 'Pakket',
        '_si_vac_voornaam'     => 'Voornaam',
        '_si_vac_achternaam'   => 'Achternaam',
        '_si_vac_bedrijfsnaam' => 'Bedrijfsnaam',
        '_si_vac_email'        => 'E-mail',
        '_si_vac_locatie'      => 'Locatie',
        '_si_vac_referral'     => 'Hoe gevonden',
    ];

    echo '<table style="width:100%;border-collapse:collapse;">';
    foreach ($fields as $key => $label) {
        $val = get_post_meta($post->ID, $key, true);
        echo '<tr>';
        echo '<th style="text-align:left;padding:6px 10px 6px 0;width:150px;font-weight:600;">' . esc_html($label) . '</th>';
        echo '<td style="padding:6px 0;">';
        if ($key === '_si_vac_email' && $val) {
            echo '<a href="mailto:' . esc_attr($val) . '">' . esc_html($val) . '</a>';
        } elseif ($key === '_si_vac_type_baan') {
            echo esc_html(is_array($val) ? implode(', ', $val) : ($val ?: '—'));
        } else {
            echo esc_html($val ?: '—');
        }
        echo '</td></tr>';
    }

    $types = get_post_meta($post->ID, '_si_vac_type_baan', true);
    echo '<tr><th style="text-align:left;padding:6px 10px 6px 0;width:150px;font-weight:600;">Type baan</th><td style="padding:6px 0;">' . esc_html(is_array($types) ? implode(', ', $types) : ($types ?: '—')) . '</td></tr>';
    echo '</table>';

    $omschrijving = get_post_meta($post->ID, '_si_vac_omschrijving', true);
    $extra_info   = get_post_meta($post->ID, '_si_vac_extra_info', true);

    if ($omschrijving) {
        echo '<hr style="margin:14px 0;">';
        echo '<p style="font-weight:600;margin:0 0 8px;">Vacature omschrijving</p>';
        echo '<div style="background:#f9f9f9;padding:12px;border:1px solid #ddd;border-radius:4px;">';
        echo wp_kses_post($omschrijving);
        echo '</div>';
    }

    if ($extra_info) {
        echo '<hr style="margin:14px 0;">';
        echo '<p style="font-weight:600;margin:0 0 8px;">Aanvullende informatie</p>';
        echo '<div style="background:#f9f9f9;padding:12px;border:1px solid #ddd;border-radius:4px;">';
        echo wp_kses_post($extra_info);
        echo '</div>';
    }
}

/* ============================================================
   SHORTCODE: [vacature-plaatsen]
   ============================================================ */
add_shortcode('vacature-plaatsen', 'si_vacature_plaatsen_shortcode');

function si_vacature_plaatsen_shortcode(): string
{
    $success = isset($_GET['vacature_verstuurd']) && $_GET['vacature_verstuurd'] === '1';
    $errors  = [];

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['si_vp_nonce']) &&
        wp_verify_nonce($_POST['si_vp_nonce'], 'si_vacature_plaatsen')
    ) {
        $voornaam      = sanitize_text_field($_POST['voornaam'] ?? '');
        $achternaam    = sanitize_text_field($_POST['achternaam'] ?? '');
        $bedrijfsnaam  = sanitize_text_field($_POST['bedrijfsnaam'] ?? '');
        $email         = sanitize_email($_POST['email'] ?? '');
        $pakket        = sanitize_text_field($_POST['pakket'] ?? '');
        $vacaturetitel = sanitize_text_field($_POST['vacaturetitel'] ?? '');
        $locatie       = sanitize_text_field($_POST['locatie'] ?? '');
        $type_baan     = array_map('sanitize_text_field', (array)($_POST['type_baan'] ?? []));
        $omschrijving  = wp_kses_post($_POST['omschrijving'] ?? '');
        $extra_info    = wp_kses_post($_POST['extra_info'] ?? '');
        $referral      = sanitize_text_field($_POST['referral'] ?? '');

        if (!$voornaam)         $errors[] = 'Vul je voornaam in.';
        if (!$achternaam)       $errors[] = 'Vul je achternaam in.';
        if (!$bedrijfsnaam)     $errors[] = 'Vul je bedrijfsnaam in.';
        if (!is_email($email))  $errors[] = 'Vul een geldig e-mailadres in.';
        if (!$vacaturetitel)    $errors[] = 'Vul een vacaturetitel in.';
        if (!$omschrijving)     $errors[] = 'Vul een vacatureomschrijving in.';

        if (empty($errors)) {
            $upload      = null;
            $attachments = [];

            if (!empty($_FILES['bedrijfslogo']['tmp_name'])) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';
                require_once ABSPATH . 'wp-admin/includes/image.php';

                $upload = media_handle_upload('bedrijfslogo', 0);
                if (!is_wp_error($upload)) {
                    $path = get_attached_file($upload);
                    if ($path && file_exists($path)) {
                        $attachments[] = $path;
                    }
                }
            }

            $body  = "Nieuwe vacature via het formulier:\n\n";
            $body .= "Pakket: $pakket\n";
            $body .= "Naam: $voornaam $achternaam\n";
            $body .= "Bedrijf: $bedrijfsnaam\n";
            $body .= "E-mail: $email\n";
            $body .= "Vacaturetitel: $vacaturetitel\n";
            $body .= "Locatie: $locatie\n";
            $body .= "Type baan: " . implode(', ', $type_baan) . "\n";
            $body .= "Hoe gevonden: $referral\n\n";
            $body .= "--- Vacature omschrijving ---\n" . wp_strip_all_tags($omschrijving) . "\n\n";
            $body .= "--- Aanvullende informatie ---\n" . wp_strip_all_tags($extra_info) . "\n";

            $to = array_unique(array_filter([
                'support@student-inhuren.nl',
                get_option('admin_email'),
            ]));

            wp_mail(
                $to,
                "Nieuwe vacature: $vacaturetitel",
                $body,
                ['Content-Type: text/plain; charset=UTF-8'],
                $attachments
            );

            $post_id = wp_insert_post([
                'post_title'  => sanitize_text_field($vacaturetitel),
                'post_status' => 'publish',
                'post_type'   => 'si_vacature_aanvraag',
                'post_author' => 0,
            ]);

            if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, '_si_vac_pakket', $pakket);
                update_post_meta($post_id, '_si_vac_voornaam', $voornaam);
                update_post_meta($post_id, '_si_vac_achternaam', $achternaam);
                update_post_meta($post_id, '_si_vac_bedrijfsnaam', $bedrijfsnaam);
                update_post_meta($post_id, '_si_vac_email', $email);
                update_post_meta($post_id, '_si_vac_locatie', $locatie);
                update_post_meta($post_id, '_si_vac_type_baan', $type_baan);
                update_post_meta($post_id, '_si_vac_omschrijving', $omschrijving);
                update_post_meta($post_id, '_si_vac_extra_info', $extra_info);
                update_post_meta($post_id, '_si_vac_referral', $referral);

                if (!empty($upload) && !is_wp_error($upload)) {
                    update_post_meta($post_id, '_si_vac_logo_id', $upload);
                }
            }

            $content = $omschrijving;
            if ($extra_info) {
                $content .= '<h3>Aanvullende informatie</h3>' . $extra_info;
            }

            $job_id = wp_insert_post([
                'post_title'   => sanitize_text_field($vacaturetitel),
                'post_content' => $content,
                'post_status'  => 'draft',
                'post_type'    => 'job_listing',
                'post_author'  => 1,
            ]);

            if ($job_id && !is_wp_error($job_id)) {
                update_post_meta($job_id, '_job_location', $locatie);
                update_post_meta($job_id, '_company_name', $bedrijfsnaam);
                update_post_meta($job_id, '_company_email', $email);
                update_post_meta($job_id, '_job_salary', '');
                update_post_meta($job_id, '_filled', 0);
                update_post_meta($job_id, '_featured', 0);
                update_post_meta($job_id, '_job_expires', '');
                update_post_meta($job_id, '_si_vac_pakket', $pakket);

                if (!empty($upload) && !is_wp_error($upload)) {
                    update_post_meta($job_id, '_company_logo', wp_get_attachment_url($upload));
                    set_post_thumbnail($job_id, $upload);
                }

                if (!empty($type_baan)) {
                    $term_ids = [];
                    foreach ($type_baan as $type_name) {
                        $term = get_term_by('name', $type_name, 'job_listing_type');
                        if ($term) {
                            $term_ids[] = $term->term_id;
                        }
                    }
                    if (!empty($term_ids)) {
                        wp_set_post_terms($job_id, $term_ids, 'job_listing_type');
                    }
                }
            }

            wp_safe_redirect(add_query_arg('vacature_verstuurd', '1', get_permalink()));
            exit;
        }
    }

    ob_start();

    if ($success): ?>
    <div class="sj-vp-notice sj-vp-notice--success">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M173.66,98.34a8,8,0,0,1,0,11.32l-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35A8,8,0,0,1,173.66,98.34ZM232,128A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"/></svg>
        <div>
            <strong>Vacature succesvol verzonden!</strong>
            <p>We nemen zo snel mogelijk contact met je op.</p>
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
                <h2 class="sj-vp__title">Vacature plaatsen</h2>
                <p class="sj-vp__subtitle">Vul de gegevens in en we publiceren je vacature zo snel mogelijk. Je ontvangt een factuur per e-mail na publicatie.</p>
            </header>

            <form method="post" class="sj-vp__form" enctype="multipart/form-data" novalidate>
                <?php wp_nonce_field('si_vacature_plaatsen', 'si_vp_nonce'); ?>

                <div class="sj-vp__section">
                    <p class="sj-vp__section-title">Kies je pakket</p>
                    <div class="sj-vp__grid sj-vp__grid--2">
                        <?php
                        $pakketten = [
                            'Vacature plaatsing - €149 exl. 21% btw.' => ['label' => 'Vacature plaatsing', 'prijs' => '€149 exl. 21% btw.'],
                            'Spotlight vacature plaatsing - €199 exl. 21% btw.' => ['label' => 'Spotlight vacature plaatsing', 'prijs' => '€199 exl. 21% btw.'],
                            'Stage & Vrijwilligerswerk: Gratis' => ['label' => 'Stage & Vrijwilligerswerk', 'prijs' => 'Gratis'],
                            'Wij hebben een strippenkaart' => ['label' => 'Strippenkaart', 'prijs' => 'Via strippenkaart'],
                        ];
                        $selected_pakket = $_POST['pakket'] ?? 'Vacature plaatsing - €149 exl. 21% btw.';
                        foreach ($pakketten as $value => $info): ?>
                        <label class="sj-vp__pakket<?php echo ($selected_pakket === $value) ? ' is-selected' : ''; ?>">
                            <input type="radio" name="pakket" value="<?php echo esc_attr($value); ?>" <?php checked($selected_pakket, $value); ?> class="sj-vp__pakket-radio">
                            <span class="sj-vp__pakket-inner">
                                <span class="sj-vp__pakket-name"><?php echo esc_html($info['label']); ?></span>
                                <span class="sj-vp__pakket-prijs"><?php echo esc_html($info['prijs']); ?></span>
                            </span>
                            <span class="sj-vp__pakket-check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 256 256" fill="currentColor"><path d="M229.66,77.66l-128,128a8,8,0,0,1-11.32,0l-56-56a8,8,0,0,1,11.32-11.32L96,188.69,218.34,66.34a8,8,0,0,1,11.32,11.32Z"/></svg>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="sj-vp__pakket-note">Benieuwd naar de mogelijkheden van een strippenkaart? Bekijk <a href="<?php echo esc_url(home_url('/tarieven/')); ?>">hier</a> de mogelijkheden.</p>
                </div>

                <div class="sj-vp__section">
                    <p class="sj-vp__section-title">Contactgegevens</p>
                    <div class="sj-vp__grid sj-vp__grid--2">
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_voornaam">Voornaam <span class="sj-vp__req">*</span></label>
                            <input type="text" name="voornaam" id="sj_voornaam" class="sj-vp__input" value="<?php echo esc_attr($_POST['voornaam'] ?? ''); ?>" placeholder="Jan" required>
                        </div>
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_achternaam">Achternaam <span class="sj-vp__req">*</span></label>
                            <input type="text" name="achternaam" id="sj_achternaam" class="sj-vp__input" value="<?php echo esc_attr($_POST['achternaam'] ?? ''); ?>" placeholder="de Vries" required>
                        </div>
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_bedrijfsnaam">Bedrijfsnaam <span class="sj-vp__req">*</span></label>
                            <input type="text" name="bedrijfsnaam" id="sj_bedrijfsnaam" class="sj-vp__input" value="<?php echo esc_attr($_POST['bedrijfsnaam'] ?? ''); ?>" placeholder="Jouw organisatie" required>
                        </div>
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_email">E-mailadres <span class="sj-vp__req">*</span></label>
                            <input type="email" name="email" id="sj_email" class="sj-vp__input" value="<?php echo esc_attr($_POST['email'] ?? ''); ?>" placeholder="jan@bedrijf.nl" required>
                        </div>
                    </div>
                </div>

                <div class="sj-vp__section">
                    <p class="sj-vp__section-title">Vacature informatie</p>
                    <div class="sj-vp__grid sj-vp__grid--1">
                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_vacaturetitel">Vacaturetitel <span class="sj-vp__req">*</span></label>
                            <input type="text" name="vacaturetitel" id="sj_vacaturetitel" class="sj-vp__input" value="<?php echo esc_attr($_POST['vacaturetitel'] ?? ''); ?>" placeholder="Bijv. Werkstudent Marketing" required>
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_locatie">Locatie</label>
                            <input type="text" name="locatie" id="sj_locatie" class="sj-vp__input" value="<?php echo esc_attr($_POST['locatie'] ?? ''); ?>" placeholder="Amsterdam, Hybrid, Remote...">
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" id="sj_type_baan_label">Type baan</label>
                            <?php
                            $types = ['Fulltime', 'Parttime', 'Project', 'Stage', 'Vrijwilligerswerk'];
                            $selected_types = (array)($_POST['type_baan'] ?? []);
                            ?>
                            <div class="sj-vp__ms-hidden" aria-hidden="true">
                                <?php foreach ($types as $t): ?>
                                <input type="checkbox" name="type_baan[]" value="<?php echo esc_attr($t); ?>" class="sj-vp__ms-cb" <?php checked(in_array($t, $selected_types, true)); ?>>
                                <?php endforeach; ?>
                            </div>
                            <div class="sj-vp__ms" id="sj_type_baan_ms" aria-labelledby="sj_type_baan_label" role="group">
                                <div class="sj-vp__ms-trigger" tabindex="0" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="sj-vp__ms-placeholder">Selecteer type(s)...</span>
                                    <span class="sj-vp__ms-tags"></span>
                                    <svg class="sj-vp__ms-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z"/></svg>
                                </div>
                                <ul class="sj-vp__ms-dropdown" role="listbox" aria-multiselectable="true">
                                    <?php foreach ($types as $t): ?>
                                    <li class="sj-vp__ms-option<?php echo in_array($t, $selected_types, true) ? ' is-selected' : ''; ?>" role="option" aria-selected="<?php echo in_array($t, $selected_types, true) ? 'true' : 'false'; ?>" data-value="<?php echo esc_attr($t); ?>">
                                        <span class="sj-vp__ms-opt-check" aria-hidden="true">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 256 256" fill="currentColor"><path d="M229.66,77.66l-128,128a8,8,0,0,1-11.32,0l-56-56a8,8,0,0,1,11.32-11.32L96,188.69,218.34,66.34a8,8,0,0,1,11.32,11.32Z"/></svg>
                                        </span>
                                        <span class="sj-vp__ms-opt-text"><?php echo esc_html($t); ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_omschrijving_hidden">Vacature omschrijving <span class="sj-vp__req">*</span></label>
                            <div class="sj-vp__quill-wrap">
                                <div id="sj_quill_omschrijving" class="sj-vp__quill-editor" style="min-height:220px;"></div>
                            </div>
                            <textarea name="omschrijving" id="sj_omschrijving_hidden" class="sj-vp__quill-hidden" aria-hidden="true"><?php echo esc_textarea($_POST['omschrijving'] ?? ''); ?></textarea>
                            <span class="sj-vp__hint">Beschrijf de functie, vereisten en wat je organisatie biedt.</span>
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_extra_info_hidden">Aanvullende informatie <span class="sj-vp__opt">(optioneel)</span></label>
                            <div class="sj-vp__quill-wrap">
                                <div id="sj_quill_extra_info" class="sj-vp__quill-editor" style="min-height:140px;"></div>
                            </div>
                            <textarea name="extra_info" id="sj_extra_info_hidden" class="sj-vp__quill-hidden" aria-hidden="true"><?php echo esc_textarea($_POST['extra_info'] ?? ''); ?></textarea>
                            <span class="sj-vp__hint">Secundaire arbeidsvoorwaarden, cultuur, of andere relevante informatie.</span>
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_bedrijfslogo">Bedrijfslogo uploaden</label>
                            <label class="sj-vp__upload" for="sj_bedrijfslogo">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M240,136v64a16,16,0,0,1-16,16H32a16,16,0,0,1-16-16V136a16,16,0,0,1,16-16H80a8,8,0,0,1,0,16H32v64H224V136H176a8,8,0,0,1,0-16h48A16,16,0,0,1,240,136ZM85.66,77.66,120,43.31V128a8,8,0,0,0,16,0V43.31l34.34,34.35a8,8,0,0,0,11.32-11.32l-48-48a8,8,0,0,0-11.32,0l-48,48A8,8,0,0,0,85.66,77.66Z"/></svg>
                                <span class="sj-vp__upload-label">Kies bestand</span>
                                <span class="sj-vp__upload-name">Geen bestand gekozen</span>
                                <input type="file" name="bedrijfslogo" id="sj_bedrijfslogo" accept="image/*" class="sj-vp__upload-input">
                            </label>
                            <span class="sj-vp__hint">PNG of JPG, liefst vierkant. Max. 2 MB.</span>
                        </div>

                        <div class="sj-vp__field">
                            <label class="sj-vp__label" for="sj_referral">Hoe heb je ons gevonden?</label>
                            <input type="text" name="referral" id="sj_referral" class="sj-vp__input" value="<?php echo esc_attr($_POST['referral'] ?? ''); ?>" placeholder="Via Google, LinkedIn, via-via...">
                        </div>
                    </div>
                </div>

                <footer class="sj-vp__footer">
                    <button type="submit" class="sj-vp__submit">Vacature versturen</button>
                    <p class="sj-vp__footer-note">Je ontvangt een factuur per e-mail na publicatie. Vragen? Mail naar <a href="mailto:support@student-inhuren.nl">support@student-inhuren.nl</a>.</p>
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

            var omschrijvingHidden = document.getElementById('sj_omschrijving_hidden');
            var extraInfoHidden = document.getElementById('sj_extra_info_hidden');
            var quillOmschrijving = new Quill('#sj_quill_omschrijving', {
                theme: 'snow',
                modules: { toolbar: toolbarOptions },
                placeholder: 'Beschrijf de functie, taken, vereisten en wat je organisatie biedt...'
            });
            var quillExtraInfo = new Quill('#sj_quill_extra_info', {
                theme: 'snow',
                modules: { toolbar: toolbarOptions },
                placeholder: 'Secundaire arbeidsvoorwaarden, cultuur, of andere relevante informatie...'
            });

            if (omschrijvingHidden && omschrijvingHidden.value) {
                quillOmschrijving.clipboard.dangerouslyPasteHTML(omschrijvingHidden.value);
            }
            if (extraInfoHidden && extraInfoHidden.value) {
                quillExtraInfo.clipboard.dangerouslyPasteHTML(extraInfoHidden.value);
            }

            quillOmschrijving.on('text-change', function () {
                if (omschrijvingHidden) omschrijvingHidden.value = quillOmschrijving.root.innerHTML;
            });
            quillExtraInfo.on('text-change', function () {
                if (extraInfoHidden) extraInfoHidden.value = quillExtraInfo.root.innerHTML;
            });

            var form = document.querySelector('.sj-vp__form');
            if (form) {
                form.addEventListener('submit', function () {
                    if (omschrijvingHidden) omschrijvingHidden.value = quillOmschrijving.root.innerHTML;
                    if (extraInfoHidden) extraInfoHidden.value = quillExtraInfo.root.innerHTML;
                });
            }
        }

        initQuill();

        document.querySelectorAll('.sj-vp__pakket-radio').forEach(function (radio) {
            radio.addEventListener('change', function () {
                document.querySelectorAll('.sj-vp__pakket').forEach(function (el) {
                    el.classList.remove('is-selected');
                });
                if (radio.checked) {
                    radio.closest('.sj-vp__pakket').classList.add('is-selected');
                }
            });
        });

        var fileInput = document.getElementById('sj_bedrijfslogo');
        var fileName = document.querySelector('.sj-vp__upload-name');
        if (fileInput && fileName) {
            fileInput.addEventListener('change', function () {
                fileName.textContent = fileInput.files.length ? fileInput.files[0].name : 'Geen bestand gekozen';
            });
        }

        var ms = document.getElementById('sj_type_baan_ms');
        var trigger = ms && ms.querySelector('.sj-vp__ms-trigger');
        var tagsEl = ms && ms.querySelector('.sj-vp__ms-tags');
        var placeholder = ms && ms.querySelector('.sj-vp__ms-placeholder');
        var options = ms ? Array.from(ms.querySelectorAll('.sj-vp__ms-option')) : [];
        var checkboxes = ms ? Array.from(ms.closest('.sj-vp__field').querySelectorAll('.sj-vp__ms-cb')) : [];

        function msSync() {
            if (!tagsEl || !placeholder) return;
            tagsEl.innerHTML = '';
            var selected = options.filter(function (o) { return o.classList.contains('is-selected'); });
            placeholder.style.display = selected.length ? 'none' : '';
            selected.forEach(function (opt) {
                var tag = document.createElement('span');
                tag.className = 'sj-vp__ms-tag';
                tag.textContent = opt.dataset.value;
                var rm = document.createElement('button');
                rm.type = 'button';
                rm.className = 'sj-vp__ms-tag-remove';
                rm.setAttribute('aria-label', 'Verwijder ' + opt.dataset.value);
                rm.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 256 256" fill="currentColor"><path d="M205.66,194.34a8,8,0,0,1-11.32,11.32L128,139.31,61.66,205.66a8,8,0,0,1-11.32-11.32L116.69,128,50.34,61.66A8,8,0,0,1,61.66,50.34L128,116.69l66.34-66.35a8,8,0,0,1,11.32,11.32L139.31,128Z"/></svg>';
                rm.addEventListener('click', function (e) {
                    e.stopPropagation();
                    opt.classList.remove('is-selected');
                    opt.setAttribute('aria-selected', 'false');
                    var cb = checkboxes.find(function (c) { return c.value === opt.dataset.value; });
                    if (cb) cb.checked = false;
                    msSync();
                });
                tag.appendChild(rm);
                tagsEl.appendChild(tag);
            });
        }

        function msOpen() {
            ms.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
        }
        function msClose() {
            ms.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
        }

        if (trigger) {
            trigger.addEventListener('click', function () {
                ms.classList.contains('is-open') ? msClose() : msOpen();
            });
            trigger.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    ms.classList.contains('is-open') ? msClose() : msOpen();
                }
                if (e.key === 'Escape') msClose();
            });
        }

        options.forEach(function (opt) {
            opt.addEventListener('click', function () {
                var isSelected = opt.classList.toggle('is-selected');
                opt.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                var cb = checkboxes.find(function (c) { return c.value === opt.dataset.value; });
                if (cb) cb.checked = isSelected;
                msSync();
            });
        });

        document.addEventListener('click', function (e) {
            if (ms && !ms.contains(e.target)) msClose();
        });

        msSync();
    })();
    </script>
    <?php endif;

    return ob_get_clean();
}
