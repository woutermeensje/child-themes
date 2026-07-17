<?php
if (!defined('ABSPATH')) exit;

/**
 * Stuur een API-verzoek naar ActiveCampaign.
 */
function fn_ac_request(string $method, string $endpoint, array $body = []): ?array {
    $base_url = defined('ACTIVECAMPAIGN_BASE_URL') ? ACTIVECAMPAIGN_BASE_URL : '';
    $api_key  = defined('ACTIVECAMPAIGN_API_KEY')  ? ACTIVECAMPAIGN_API_KEY  : '';
    $timeout  = defined('ACTIVECAMPAIGN_TIMEOUT')  ? (int) ACTIVECAMPAIGN_TIMEOUT : 10;

    if (!$base_url || !$api_key) {
        error_log('[Impact Vacatures AC] Geen API-credentials geconfigureerd.');
        return null;
    }

    $url  = rtrim($base_url, '/') . '/api/3/' . ltrim($endpoint, '/');
    $args = [
        'method'  => strtoupper($method),
        'timeout' => $timeout,
        'headers' => [
            'Api-Token'    => $api_key,
            'Content-Type' => 'application/json',
        ],
    ];

    if (!empty($body)) {
        $args['body'] = wp_json_encode($body);
    }

    $response = wp_remote_request($url, $args);

    if (is_wp_error($response)) {
        error_log('[Impact Vacatures AC] API-fout: ' . $response->get_error_message());
        return null;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    $code = wp_remote_retrieve_response_code($response);

    if ($code >= 400) {
        error_log('[Impact Vacatures AC] HTTP ' . $code . ' op ' . $endpoint . ': ' . wp_remote_retrieve_body($response));
        return null;
    }

    return $data;
}

/**
 * Schrijf een job alert abonnee in bij ActiveCampaign (lijst 7).
 */
function fn_ac_subscribe_job_alert(string $voornaam, string $email, array $sector_slugs): bool {
    $list_id = defined('IMPACT_VACATURES_AC_JOB_ALERT_LIST_ID') ? (int) IMPACT_VACATURES_AC_JOB_ALERT_LIST_ID : 0;

    $result = fn_ac_request('POST', 'contact/sync', [
        'contact' => ['email' => $email, 'firstName' => $voornaam],
    ]);

    if (empty($result['contact']['id'])) {
        error_log('[Impact Vacatures AC] Contact sync mislukt voor: ' . $email);
        return false;
    }

    $contact_id = (int) $result['contact']['id'];

    fn_ac_request('POST', 'contactLists', [
        'contactList' => [
            'list'    => $list_id,
            'contact' => $contact_id,
            'status'  => 1,
        ],
    ]);

    return true;
}

/**
 * Schrijf een nieuwsbrief-abonnee in bij ActiveCampaign.
 */
function fn_ac_subscribe_newsletter(string $voornaam, string $email): bool {
    $list_id = defined('IMPACT_VACATURES_AC_NEWSLETTER_LIST_ID') ? (int) IMPACT_VACATURES_AC_NEWSLETTER_LIST_ID : 0;

    $result = fn_ac_request('POST', 'contact/sync', [
        'contact' => ['email' => $email, 'firstName' => $voornaam],
    ]);

    if (empty($result['contact']['id'])) {
        error_log('[Impact Vacatures AC] Contact sync mislukt voor: ' . $email);
        return false;
    }

    fn_ac_request('POST', 'contactLists', [
        'contactList' => [
            'list'    => $list_id,
            'contact' => (int) $result['contact']['id'],
            'status'  => 1,
        ],
    ]);

    return true;
}

/**
 * Maak een ActiveCampaign message + campaign aan en verstuur naar de nieuwsbrief-lijst.
 */
function fn_ac_send_newsletter_campaign(array $vacatures): bool {
    $list_id = defined('IMPACT_VACATURES_AC_NEWSLETTER_LIST_ID') ? (int) IMPACT_VACATURES_AC_NEWSLETTER_LIST_ID : 0;

    $week_nr = date('W');
    $jaar    = date('Y');
    $subject = "De nieuwste vacatures van de afgelopen twee weken — Impact Vacatures";
    $html    = fn_build_newsletter_email_ac($vacatures);

    $msg = fn_ac_request('POST', 'messages', [
        'message' => [
            'fromname'       => 'Impact Vacatures',
            'fromemail'      => 'nieuwsbrief@impactvacatures.nl',
            'reply2'         => 'nieuwsbrief@impactvacatures.nl',
            'subject'        => $subject,
            'preheader_text' => 'Bekijk de nieuwste vacatures van de afgelopen twee weken.',
            'html'           => $html,
            'text'           => wp_strip_all_tags($html),
            'userid'         => '1',
        ],
    ]);

    if (empty($msg['message']['id'])) {
        error_log('[Impact Vacatures AC Newsletter] Message aanmaken mislukt.');
        return false;
    }

    $message_id = (int) $msg['message']['id'];

    $campaign = fn_ac_request('POST', 'campaigns', [
        'campaign' => [
            'type'       => 'single',
            'status'     => 1,
            'public'     => 0,
            'name'       => "Impact Vacatures Nieuwsbrief week {$week_nr}, {$jaar}",
            'senddate'   => gmdate('Y-m-d H:i:s'),
            'htmlunsub'  => 0,
            'listid'     => (string) $list_id,
            'messageid'  => (string) $message_id,
            'segmentid'  => 0,
            'tracklinks' => 'all',
            'trackreads' => 1,
        ],
    ]);

    if (empty($campaign['campaign']['id'])) {
        error_log('[Impact Vacatures AC Newsletter] Campaign aanmaken mislukt.');
        return false;
    }

    error_log('[Impact Vacatures AC Newsletter] Campaign ' . $campaign['campaign']['id'] . ' aangemaakt voor week ' . $week_nr . '/' . $jaar);
    return true;
}

/**
 * Bouw de HTML nieuwsbrief-e-mail op met ActiveCampaign merge tags.
 */
function fn_build_newsletter_email_ac(array $vacatures): string {
    $alle_vacatures = esc_url(home_url('/vacatures/'));
    $week_nr        = date('W');
    $jaar           = date('Y');

    $vacature_rows = '';
    foreach ($vacatures as $post) {
        $title      = esc_html(get_the_title($post));
        $link       = esc_url(get_permalink($post));
        $company    = esc_html(get_post_meta($post->ID, '_company_name', true));
        $location   = esc_html(get_post_meta($post->ID, '_job_location', true));

        $meta      = array_filter([$company, $location]);
        $meta_html = $meta
            ? '<div style="margin-top:4px;font-size:13px;color:#666;">' . implode(' &nbsp;·&nbsp; ', $meta) . '</div>'
            : '';

        $vacature_rows .= "
        <tr>
          <td style=\"padding:18px 0;border-bottom:1px solid #E0E0E0;\">
            <a href=\"{$link}\" style=\"font-size:16px;font-weight:700;color:#0884CC;text-decoration:none;font-family:Arial,sans-serif;\">{$title}</a>
            {$meta_html}
            <div style=\"margin-top:12px;\">
              <a href=\"{$link}\" style=\"display:inline-block;padding:8px 18px;background:#FF8C2C;color:#ffffff;font-size:13px;font-weight:600;border-radius:4px;text-decoration:none;font-family:Arial,sans-serif;\">Bekijk vacature &rarr;</a>
            </div>
          </td>
        </tr>";
    }

    return "<!DOCTYPE html>
<html lang=\"nl\">
<head>
  <meta charset=\"UTF-8\">
  <meta name=\"viewport\" content=\"width=device-width,initial-scale=1.0\">
  <title>Vacaturenieuwsbrief — Impact Vacatures</title>
</head>
<body style=\"margin:0;padding:0;background:#f5f5f5;font-family:Arial,sans-serif;\">
<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#f5f5f5;padding:32px 16px;\">
  <tr>
    <td align=\"center\">
      <table width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #E0E0E0;\">

        <tr>
          <td style=\"background:#0884CC;padding:24px 32px;\">
            <span style=\"font-family:Arial,sans-serif;font-size:20px;font-weight:700;color:#ffffff;letter-spacing:-0.3px;\">Impact Vacatures</span>
            <span style=\"display:block;font-family:Arial,sans-serif;font-size:13px;color:rgba(255,255,255,0.75);margin-top:4px;\">Vacaturenieuwsbrief &mdash; week {$week_nr}, {$jaar}</span>
          </td>
        </tr>

        <tr>
          <td style=\"padding:28px 32px 8px;\">
            <p style=\"margin:0 0 10px;font-size:20px;font-weight:700;color:#0884CC;font-family:Arial,sans-serif;\">Hoi %FIRSTNAME%!</p>
            <p style=\"margin:0;font-size:15px;color:#333333;line-height:1.65;font-family:Arial,sans-serif;\">Dit zijn de nieuwste vacatures van de afgelopen twee weken op Impact Vacatures. Ontdek jouw volgende stap in de non-profit sector.</p>
          </td>
        </tr>

        <tr>
          <td style=\"padding:8px 32px 24px;\">
            <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">
              {$vacature_rows}
            </table>
          </td>
        </tr>

        <tr>
          <td style=\"padding:0 32px 32px;text-align:center;\">
            <a href=\"{$alle_vacatures}\" style=\"display:inline-block;padding:13px 28px;background:#FF8C2C;color:#ffffff;font-size:15px;font-weight:700;border-radius:6px;text-decoration:none;font-family:Arial,sans-serif;\">Bekijk alle vacatures</a>
          </td>
        </tr>

        <tr>
          <td style=\"padding:0 32px;\"><hr style=\"border:none;border-top:1px solid #E0E0E0;margin:0;\"></td>
        </tr>

        <tr>
          <td style=\"background:#f5f5f5;padding:20px 32px;text-align:center;\">
            <p style=\"margin:0 0 6px;font-size:12px;color:#999999;font-family:Arial,sans-serif;\">Je ontvangt deze nieuwsbrief omdat je je hebt aangemeld op Impact Vacatures.</p>
            <p style=\"margin:0;font-size:12px;font-family:Arial,sans-serif;\">
              <a href=\"%UNSUBSCRIBELINK%\" style=\"color:#0884CC;\">Afmelden voor de nieuwsbrief</a>
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>";
}
