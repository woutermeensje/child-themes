<?php
if (!defined('ABSPATH')) exit;

if (!defined('SJ_JOB_APPLICATION_POST_TYPE')) {
    define('SJ_JOB_APPLICATION_POST_TYPE', 'sj_sollicitatie');
}

/**
 * Registreer het backend-overzicht voor inzendingen via het formulier onder vacatures.
 */
add_action('init', function () {
    register_post_type(SJ_JOB_APPLICATION_POST_TYPE, [
        'labels' => [
            'name'               => 'Sollicitaties',
            'singular_name'      => 'Sollicitatie',
            'add_new'            => 'Nieuwe sollicitatie',
            'add_new_item'       => 'Nieuwe sollicitatie',
            'edit_item'          => 'Sollicitatie bekijken',
            'view_item'          => 'Bekijk sollicitatie',
            'all_items'          => 'Alle sollicitaties',
            'search_items'       => 'Zoek sollicitaties',
            'not_found'          => 'Geen sollicitaties gevonden.',
            'not_found_in_trash' => 'Geen sollicitaties in de prullenbak.',
            'menu_name'          => 'Sollicitaties',
        ],
        'public'          => false,
        'show_ui'         => true,
        'show_in_menu'    => true,
        'menu_icon'       => 'dashicons-email-alt2',
        'menu_position'   => 26,
        'supports'        => ['title'],
        'capability_type' => 'post',
        'capabilities'    => ['create_posts' => 'do_not_allow'],
        'map_meta_cap'    => true,
        'show_in_rest'    => false,
        'rewrite'         => false,
        'query_var'       => false,
    ]);
});

/**
 * Pak een POST-waarde veilig uit.
 */
function sj_job_application_post_value(string $key): string {
    if (!isset($_POST[$key]) || is_array($_POST[$key])) {
        return '';
    }

    return (string) wp_unslash($_POST[$key]);
}

function sj_job_application_form_error(string $message): void {
    $GLOBALS['sj_job_application_form_result'] = [
        'success' => false,
        'error'   => $message,
    ];
}

function sj_job_application_get_form_notice(int $job_id): array {
    $success = false;

    if (isset($_GET['sj_vraag_verstuurd']) && !is_array($_GET['sj_vraag_verstuurd'])) {
        $submitted_job_id = 0;
        if (isset($_GET['sj_job_id']) && !is_array($_GET['sj_job_id'])) {
            $submitted_job_id = absint(wp_unslash($_GET['sj_job_id']));
        }

        $success = sanitize_text_field(wp_unslash($_GET['sj_vraag_verstuurd'])) === '1'
            && (!$submitted_job_id || $submitted_job_id === $job_id);
    }

    $result = $GLOBALS['sj_job_application_form_result'] ?? [];

    return [
        'success' => $success,
        'error'   => isset($result['error']) ? (string) $result['error'] : '',
    ];
}

function sj_job_application_cv_was_uploaded(): bool {
    return isset($_FILES['vraag_cv'])
        && is_array($_FILES['vraag_cv'])
        && !empty($_FILES['vraag_cv']['name'])
        && (int) ($_FILES['vraag_cv']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
}

function sj_job_application_validate_cv_upload(): ?string {
    if (!sj_job_application_cv_was_uploaded()) {
        return null;
    }

    $file = $_FILES['vraag_cv'];
    $error_code = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($error_code !== UPLOAD_ERR_OK) {
        return 'Het CV-bestand kon niet worden geupload. Probeer het opnieuw.';
    }

    if ((int) ($file['size'] ?? 0) > 5 * MB_IN_BYTES) {
        return 'Het CV-bestand is groter dan 5 MB.';
    }

    $filename  = sanitize_file_name((string) ($file['name'] ?? ''));
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($extension, ['pdf', 'doc', 'docx'], true)) {
        return 'Upload je CV als PDF of Word-bestand.';
    }

    return null;
}

function sj_job_application_get_sanitized_data(): array {
    $message_html = wp_kses_post(sj_job_application_post_value('vraag_tekst'));
    $message_text = html_entity_decode(
        trim(wp_strip_all_tags(str_replace(['</p>', '<br>', '<br/>', '<br />'], "\n", $message_html))),
        ENT_QUOTES,
        get_bloginfo('charset') ?: 'UTF-8'
    );

    return [
        'first_name'   => sanitize_text_field(sj_job_application_post_value('vraag_voornaam')),
        'last_name'    => sanitize_text_field(sj_job_application_post_value('vraag_achternaam')),
        'email'        => sanitize_email(sj_job_application_post_value('vraag_email')),
        'phone'        => sanitize_text_field(sj_job_application_post_value('vraag_telefoon')),
        'message'      => sanitize_textarea_field($message_text),
        'message_html' => $message_html,
    ];
}

function sj_job_application_validate_data(array $data): array {
    $errors = [];

    if (empty($data['first_name'])) {
        $errors[] = 'Vul je voornaam in.';
    }

    if (empty($data['email']) || !is_email($data['email'])) {
        $errors[] = 'Vul een geldig e-mailadres in.';
    }

    if (empty($data['message'])) {
        $errors[] = 'Vul je bericht in.';
    }

    $cv_error = sj_job_application_validate_cv_upload();
    if ($cv_error) {
        $errors[] = $cv_error;
    }

    return $errors;
}

function sj_job_application_get_company_name(int $job_id): string {
    $company = get_post_meta($job_id, '_company_name', true);

    if (!$company && function_exists('get_the_company_name') && get_queried_object_id() === $job_id) {
        $company = get_the_company_name();
    }

    return sanitize_text_field((string) $company);
}

function sj_job_application_build_email_body(int $job_id, array $data, string $company): string {
    $job_title = get_the_title($job_id);
    $job_url   = get_permalink($job_id);
    $name      = trim($data['first_name'] . ' ' . $data['last_name']);

    $body  = "Vraag via de vacaturepagina:\n\n";
    $body .= "Vacature: {$job_title}\n";
    if ($company) {
        $body .= "Bedrijf: {$company}\n";
    }
    if ($job_url) {
        $body .= "Vacature URL: {$job_url}\n";
    }
    $body .= "\nVan: {$name} <{$data['email']}>";
    if (!empty($data['phone'])) {
        $body .= "\nTelefoon: {$data['phone']}";
    }
    $body .= "\n\nBericht:\n{$data['message']}";

    return $body;
}

function sj_job_application_create_submission(int $job_id, array $data) {
    $job_title     = get_the_title($job_id);
    $company       = sj_job_application_get_company_name($job_id);
    $contact_first = sanitize_text_field((string) get_post_meta($job_id, '_job_contact_firstname', true));
    $contact_last  = sanitize_text_field((string) get_post_meta($job_id, '_job_contact_lastname', true));
    $contact_email = sanitize_email((string) get_post_meta($job_id, '_job_contact_email', true));
    $recipient     = $contact_email ?: 'support@sustainablejobs.nl';
    $name          = trim($data['first_name'] . ' ' . $data['last_name']);
    $post_title    = trim($name . ' - ' . $job_title);

    $submission_id = wp_insert_post([
        'post_title'  => sanitize_text_field($post_title ?: 'Nieuwe sollicitatie'),
        'post_status' => 'pending',
        'post_type'   => SJ_JOB_APPLICATION_POST_TYPE,
        'post_author' => 0,
    ], true);

    if (is_wp_error($submission_id)) {
        return $submission_id;
    }

    update_post_meta($submission_id, '_sj_job_id', $job_id);
    update_post_meta($submission_id, '_sj_job_title', $job_title);
    update_post_meta($submission_id, '_sj_job_url', get_permalink($job_id));
    update_post_meta($submission_id, '_sj_company', $company);
    update_post_meta($submission_id, '_sj_contact_name', trim($contact_first . ' ' . $contact_last));
    update_post_meta($submission_id, '_sj_contact_email', $contact_email);
    update_post_meta($submission_id, '_sj_mail_to', $recipient);
    update_post_meta($submission_id, '_sj_first_name', $data['first_name']);
    update_post_meta($submission_id, '_sj_last_name', $data['last_name']);
    update_post_meta($submission_id, '_sj_email', $data['email']);
    update_post_meta($submission_id, '_sj_phone', $data['phone']);
    update_post_meta($submission_id, '_sj_message', $data['message']);
    update_post_meta($submission_id, '_sj_message_html', $data['message_html']);
    update_post_meta($submission_id, '_sj_submitted_at', current_time('mysql'));

    $attachments = [];

    if (sj_job_application_cv_was_uploaded()) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attachment_id = media_handle_upload('vraag_cv', $submission_id);

        if (is_wp_error($attachment_id)) {
            wp_delete_post($submission_id, true);
            return new WP_Error(
                'sj_cv_upload_failed',
                'Het CV-bestand kon niet worden verwerkt. Probeer een PDF of Word-bestand.'
            );
        }

        update_post_meta($submission_id, '_sj_cv_attachment_id', $attachment_id);

        $attachment_path = get_attached_file($attachment_id);
        if ($attachment_path && file_exists($attachment_path)) {
            $attachments[] = $attachment_path;
        }
    }

    $mail_sent = wp_mail(
        $recipient,
        'Vraag over vacature: ' . $job_title,
        sj_job_application_build_email_body($job_id, $data, $company),
        [
            'Content-Type: text/plain; charset=UTF-8',
            'Reply-To: ' . $name . ' <' . $data['email'] . '>',
            'Bcc: support@sustainablejobs.nl',
        ],
        $attachments
    );

    update_post_meta($submission_id, '_sj_mail_sent', $mail_sent ? '1' : '0');
    update_post_meta($submission_id, '_sj_mail_sent_at', current_time('mysql'));

    return (int) $submission_id;
}

/**
 * Verwerk het formulier vroeg genoeg om na succes veilig te kunnen redirecten.
 */
add_action('template_redirect', function () {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || empty($_POST['sj_vraag_nonce'])) {
        return;
    }

    $job_id = (int) get_queried_object_id();

    if (!$job_id || get_post_type($job_id) !== 'job_listing') {
        return;
    }

    $nonce = sanitize_text_field(wp_unslash($_POST['sj_vraag_nonce']));
    if (!wp_verify_nonce($nonce, 'sj_stel_vraag_' . $job_id)) {
        sj_job_application_form_error('De beveiligingscontrole is verlopen. Probeer het formulier opnieuw te versturen.');
        return;
    }

    $data   = sj_job_application_get_sanitized_data();
    $errors = sj_job_application_validate_data($data);

    if (!empty($errors)) {
        sj_job_application_form_error(implode(' ', $errors));
        return;
    }

    $submission_id = sj_job_application_create_submission($job_id, $data);

    if (is_wp_error($submission_id)) {
        sj_job_application_form_error($submission_id->get_error_message());
        return;
    }

    $redirect = add_query_arg([
        'sj_vraag_verstuurd' => '1',
        'sj_job_id'          => $job_id,
    ], get_permalink($job_id));
    wp_safe_redirect($redirect . '#sj-vraag');
    exit;
});

add_filter('manage_' . SJ_JOB_APPLICATION_POST_TYPE . '_posts_columns', function ($columns) {
    return [
        'cb'      => $columns['cb'],
        'title'   => 'Inzending',
        'sj_job'  => 'Vacature',
        'sj_name' => 'Naam',
        'sj_mail' => 'E-mail',
        'sj_phone'=> 'Telefoon',
        'sj_cv'   => 'CV',
        'sj_sent' => 'Verzonden',
        'date'    => 'Datum',
    ];
});

add_action('manage_' . SJ_JOB_APPLICATION_POST_TYPE . '_posts_custom_column', function ($column, $post_id) {
    switch ($column) {
        case 'sj_job':
            $job_id    = (int) get_post_meta($post_id, '_sj_job_id', true);
            $job_title = get_post_meta($post_id, '_sj_job_title', true) ?: ($job_id ? get_the_title($job_id) : '');
            $job_url   = get_post_meta($post_id, '_sj_job_url', true);

            if ($job_id && $job_title) {
                $edit_link = get_edit_post_link($job_id);
                echo $edit_link
                    ? '<a href="' . esc_url($edit_link) . '">' . esc_html($job_title) . '</a>'
                    : esc_html($job_title);

                if ($job_url) {
                    echo '<br><a href="' . esc_url($job_url) . '" target="_blank" rel="noopener" style="font-size:12px;">Bekijk vacature</a>';
                }
            } else {
                echo '&mdash;';
            }
            break;

        case 'sj_name':
            $name = trim(get_post_meta($post_id, '_sj_first_name', true) . ' ' . get_post_meta($post_id, '_sj_last_name', true));
            echo $name ? esc_html($name) : '&mdash;';
            break;

        case 'sj_mail':
            $email = get_post_meta($post_id, '_sj_email', true);
            echo $email ? '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>' : '&mdash;';
            break;

        case 'sj_phone':
            $phone = get_post_meta($post_id, '_sj_phone', true);
            echo $phone ? esc_html($phone) : '&mdash;';
            break;

        case 'sj_cv':
            $attachment_id = (int) get_post_meta($post_id, '_sj_cv_attachment_id', true);
            $url = $attachment_id ? wp_get_attachment_url($attachment_id) : '';
            echo $url ? '<a href="' . esc_url($url) . '" target="_blank" rel="noopener">Bekijk CV</a>' : '&mdash;';
            break;

        case 'sj_sent':
            $mail_sent = get_post_meta($post_id, '_sj_mail_sent', true) === '1';
            echo $mail_sent
                ? '<span style="color:#00a32a;font-weight:600;">Ja</span>'
                : '<span style="color:#d63638;">Nee</span>';
            break;
    }
}, 10, 2);

add_action('add_meta_boxes', function () {
    add_meta_box(
        'sj_job_application_details',
        'Inzendingsdetails',
        'sj_job_application_details_meta_box',
        SJ_JOB_APPLICATION_POST_TYPE,
        'normal',
        'high'
    );

    add_meta_box(
        'sj_job_application_message',
        'Bericht / motivatie',
        'sj_job_application_message_meta_box',
        SJ_JOB_APPLICATION_POST_TYPE,
        'normal',
        'default'
    );

    add_meta_box(
        'sj_job_application_cv',
        'CV',
        'sj_job_application_cv_meta_box',
        SJ_JOB_APPLICATION_POST_TYPE,
        'side',
        'default'
    );

    add_meta_box(
        'sj_job_application_delivery',
        'Verzending',
        'sj_job_application_delivery_meta_box',
        SJ_JOB_APPLICATION_POST_TYPE,
        'side',
        'default'
    );
});

function sj_job_application_admin_table_styles(): void {
    ?>
    <style>
    .sj-app-meta-table { width: 100%; border-collapse: collapse; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
    .sj-app-meta-table th { width: 180px; padding: 10px 12px; background: #f9fafb; border: 1px solid #e5e7eb; font-weight: 600; font-size: 13px; color: #374151; text-align: left; vertical-align: top; }
    .sj-app-meta-table td { padding: 10px 12px; border: 1px solid #e5e7eb; font-size: 13px; color: #111827; vertical-align: top; }
    .sj-app-meta-table td a { color: #168AAD; }
    .sj-app-rich-content { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; font-size: 14px; line-height: 1.7; color: #111827; padding: 14px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 5px; }
    .sj-app-rich-content p:first-child { margin-top: 0; }
    .sj-app-rich-content p:last-child { margin-bottom: 0; }
    </style>
    <?php
}

function sj_job_application_details_meta_box(WP_Post $post): void {
    sj_job_application_admin_table_styles();

    $job_id       = (int) get_post_meta($post->ID, '_sj_job_id', true);
    $job_title    = get_post_meta($post->ID, '_sj_job_title', true) ?: ($job_id ? get_the_title($job_id) : '');
    $job_url      = get_post_meta($post->ID, '_sj_job_url', true);
    $submitted_at = get_post_meta($post->ID, '_sj_submitted_at', true) ?: $post->post_date;
    $timestamp    = $submitted_at ? strtotime($submitted_at) : false;

    $job_display = $job_title ?: '';
    if ($job_display && $job_url) {
        $job_display = '<a href="' . esc_url($job_url) . '" target="_blank" rel="noopener">' . esc_html($job_display) . '</a>';
    } elseif ($job_display) {
        $job_display = esc_html($job_display);
    }

    $email = get_post_meta($post->ID, '_sj_email', true);
    $contact_email = get_post_meta($post->ID, '_sj_contact_email', true);

    $fields = [
        'Datum'                 => $timestamp ? esc_html(wp_date('d M Y H:i', $timestamp)) : '',
        'Actie'                 => esc_html('Formulier onder vacature verstuurd'),
        'Voornaam'              => esc_html(get_post_meta($post->ID, '_sj_first_name', true)),
        'Achternaam'            => esc_html(get_post_meta($post->ID, '_sj_last_name', true)),
        'E-mailadres'           => $email ? '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>' : '',
        'Telefoonnummer'        => esc_html(get_post_meta($post->ID, '_sj_phone', true)),
        'Vacature'              => $job_display,
        'Bedrijf'               => esc_html(get_post_meta($post->ID, '_sj_company', true)),
        'Contactpersoon'        => esc_html(get_post_meta($post->ID, '_sj_contact_name', true)),
        'E-mail contactpersoon' => $contact_email ? '<a href="mailto:' . esc_attr($contact_email) . '">' . esc_html($contact_email) . '</a>' : '',
    ];
    ?>
    <table class="sj-app-meta-table">
        <?php foreach ($fields as $label => $value): ?>
            <tr>
                <th><?php echo esc_html($label); ?></th>
                <td><?php echo $value ? $value : '&mdash;'; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php
}

function sj_job_application_message_meta_box(WP_Post $post): void {
    sj_job_application_admin_table_styles();

    $message_html = get_post_meta($post->ID, '_sj_message_html', true);
    $message      = get_post_meta($post->ID, '_sj_message', true);
    ?>
    <div class="sj-app-rich-content">
        <?php
        if ($message_html) {
            echo wp_kses_post($message_html);
        } elseif ($message) {
            echo wpautop(esc_html($message));
        } else {
            echo '<p style="color:#9ca3af;">Geen bericht ingevuld.</p>';
        }
        ?>
    </div>
    <?php
}

function sj_job_application_cv_meta_box(WP_Post $post): void {
    $attachment_id = (int) get_post_meta($post->ID, '_sj_cv_attachment_id', true);
    $url = $attachment_id ? wp_get_attachment_url($attachment_id) : '';

    if ($url) {
        echo '<p><a class="button button-primary" href="' . esc_url($url) . '" target="_blank" rel="noopener">CV openen</a></p>';

        $edit_link = get_edit_post_link($attachment_id);
        if ($edit_link) {
            echo '<p><a href="' . esc_url($edit_link) . '">Bekijk in mediabibliotheek</a></p>';
        }
    } else {
        echo '<p style="color:#9ca3af;margin:0;">Geen CV meegestuurd.</p>';
    }
}

function sj_job_application_delivery_meta_box(WP_Post $post): void {
    $mail_to      = get_post_meta($post->ID, '_sj_mail_to', true);
    $mail_sent    = get_post_meta($post->ID, '_sj_mail_sent', true) === '1';
    $mail_sent_at = get_post_meta($post->ID, '_sj_mail_sent_at', true);
    $timestamp    = $mail_sent_at ? strtotime($mail_sent_at) : false;
    ?>
    <p>
        <strong>Status:</strong>
        <?php echo $mail_sent ? '<span style="color:#00a32a;">verzonden</span>' : '<span style="color:#d63638;">niet verzonden</span>'; ?>
    </p>
    <p><strong>Naar:</strong><br><?php echo $mail_to ? esc_html($mail_to) : '&mdash;'; ?></p>
    <p><strong>Datum:</strong><br><?php echo $timestamp ? esc_html(wp_date('d M Y H:i', $timestamp)) : '&mdash;'; ?></p>
    <?php
}

add_action('admin_head', function () {
    global $post_type;

    if ($post_type !== SJ_JOB_APPLICATION_POST_TYPE) {
        return;
    }

    echo '<style>
        #submitdiv .misc-pub-section:not(.misc-pub-post-status) { display:none; }
        #submitdiv #publish { display:none; }
        #submitdiv #save-post { width:100%; text-align:center; }
    </style>';
});

add_action('admin_notices', function () {
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== SJ_JOB_APPLICATION_POST_TYPE) {
        return;
    }

    $count = wp_count_posts(SJ_JOB_APPLICATION_POST_TYPE);
    $pending = (int) ($count->pending ?? 0);
    if ($pending > 0) {
        printf(
            '<div class="notice notice-info"><p><strong>%d nieuwe sollicitatie%s</strong> wacht%s op beoordeling.</p></div>',
            $pending,
            $pending === 1 ? '' : 's',
            $pending === 1 ? '' : 'en'
        );
    }
});
