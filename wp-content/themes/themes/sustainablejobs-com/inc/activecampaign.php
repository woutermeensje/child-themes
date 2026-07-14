<?php
if (!defined('ABSPATH')) exit;

/**
 * Send an API request to ActiveCampaign.
 */
function sj_ac_request(string $method, string $endpoint, array $body = []): ?array {
    $base_url = defined('ACTIVECAMPAIGN_BASE_URL') ? ACTIVECAMPAIGN_BASE_URL : '';
    $api_key  = defined('ACTIVECAMPAIGN_API_KEY')  ? ACTIVECAMPAIGN_API_KEY  : '';
    $timeout  = defined('ACTIVECAMPAIGN_TIMEOUT')  ? (int) ACTIVECAMPAIGN_TIMEOUT : 10;

    if (!$base_url || !$api_key) {
        error_log('[AC] No API credentials configured.');
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
        error_log('[AC] API error: ' . $response->get_error_message());
        return null;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    $code = wp_remote_retrieve_response_code($response);

    if ($code >= 400) {
        error_log('[AC] HTTP ' . $code . ' on ' . $endpoint . ': ' . wp_remote_retrieve_body($response));
        return null;
    }

    return $data;
}

/**
 * Search a tag by name and create it if it does not exist.
 * The result is cached in a transient to avoid duplicate API calls.
 */
function sj_ac_get_or_create_tag(string $name): ?int {
    $cache_key = 'sj_ac_tag_' . md5(strtolower($name));
    $cached    = get_transient($cache_key);
    if ($cached !== false) return (int) $cached;

    $result = sj_ac_request('GET', 'tags?search=' . urlencode($name) . '&limit=20');
    if ($result && !empty($result['tags'])) {
        foreach ($result['tags'] as $tag) {
            if (strtolower($tag['tag']) === strtolower($name)) {
                set_transient($cache_key, $tag['id'], DAY_IN_SECONDS);
                return (int) $tag['id'];
            }
        }
    }

    $result = sj_ac_request('POST', 'tags', [
        'tag' => ['tag' => $name, 'tagType' => 'contact'],
    ]);

    if ($result && !empty($result['tag']['id'])) {
        $id = (int) $result['tag']['id'];
        set_transient($cache_key, $id, DAY_IN_SECONDS);
        return $id;
    }

    return null;
}

/**
 * Add a tag to a contact.
 */
function sj_ac_add_tag_to_contact(int $contact_id, int $tag_id): void {
    sj_ac_request('POST', 'contactTags', [
        'contactTag' => [
            'contact' => $contact_id,
            'tag'     => $tag_id,
        ],
    ]);
}

/**
 * Subscribe a job alert subscriber in ActiveCampaign:
 * 1. Create or update the contact
 * 2. Subscribe to the job alerts list
 * 3. Add the jobseeker tag if configured
 * 4. Create and add sector tags
 */
function sj_ac_subscribe_job_alert(string $voornaam, string $email, array $sector_slugs): bool {
    if (defined('ACTIVECAMPAIGN_ENABLED') && !ACTIVECAMPAIGN_ENABLED) return false;

    $list_id = defined('ACTIVECAMPAIGN_LIST_ID') ? (int) ACTIVECAMPAIGN_LIST_ID : 1;

    // 1. Contact sync.
    $result = sj_ac_request('POST', 'contact/sync', [
        'contact' => [
            'email'     => $email,
            'firstName' => $voornaam,
        ],
    ]);

    if (!$result || empty($result['contact']['id'])) {
        error_log('[AC] Contact sync failed for: ' . $email);
        return false;
    }

    $contact_id = (int) $result['contact']['id'];

    // 2. Subscribe to list.
    sj_ac_request('POST', 'contactLists', [
        'contactList' => [
            'list'    => $list_id,
            'contact' => $contact_id,
            'status'  => 1,
        ],
    ]);

    // 3. Jobseeker tag.
    $jobseeker_tag_id = defined('ACTIVECAMPAIGN_WERKZOEKENDE_TAG_ID') ? (int) ACTIVECAMPAIGN_WERKZOEKENDE_TAG_ID : 0;
    if ($jobseeker_tag_id) {
        sj_ac_add_tag_to_contact($contact_id, $jobseeker_tag_id);
    }

    // 4. Sector tags.
    foreach ($sector_slugs as $slug) {
        $term = get_term_by('slug', $slug, 'job_sector');
        if (!$term) continue;

        $tag_name = 'Sector: ' . $term->name;
        $tag_id   = sj_ac_get_or_create_tag($tag_name);

        if ($tag_id) {
            sj_ac_add_tag_to_contact($contact_id, $tag_id);
        }
    }

    return true;
}

/**
 * Create an AC message and campaign and send it to the newsletter list.
 */
function sj_ac_send_newsletter_campaign(array $jobs): bool {
    if (defined('ACTIVECAMPAIGN_ENABLED') && !ACTIVECAMPAIGN_ENABLED) return false;

    $list_id = defined('ACTIVECAMPAIGN_NEWSLETTER_LIST_ID') ? (int) ACTIVECAMPAIGN_NEWSLETTER_LIST_ID : 10;
    $week_nr = date('W');
    $jaar    = date('Y');
    $subject = "The latest sustainable jobs from week {$week_nr} — Sustainablejobs.com";
    $html    = sj_build_newsletter_email_ac($jobs);

    // 1. Create message.
    $msg = sj_ac_request('POST', 'messages', [
        'message' => [
            'fromname'       => 'Sustainablejobs.com',
            'fromemail'      => 'support@sustainablejobs.com',
            'reply2'         => 'support@sustainablejobs.com',
            'subject'        => $subject,
            'preheader_text' => 'View this week’s latest sustainable jobs.',
            'html'           => $html,
            'text'           => wp_strip_all_tags($html),
            'userid'         => '1',
        ],
    ]);

    if (empty($msg['message']['id'])) {
        error_log('[AC Newsletter] Message creation failed.');
        return false;
    }

    $message_id = (int) $msg['message']['id'];

    // 2. Create campaign and send immediately.
    $campaign = sj_ac_request('POST', 'campaigns', [
        'campaign' => [
            'type'       => 'single',
            'status'     => 1,
            'public'     => 0,
            'name'       => "Jobnewsletter week {$week_nr}, {$jaar}",
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
        error_log('[AC Newsletter] Campaign creation failed.');
        return false;
    }

    error_log('[AC Newsletter] Campaign ' . $campaign['campaign']['id'] . ' created for week ' . $week_nr . '/' . $jaar);
    return true;
}

/**
 * Build the HTML newsletter email with ActiveCampaign merge tags.
 */
function sj_build_newsletter_email_ac(array $jobs): string {
    $alle_jobs = esc_url(home_url('/jobs/'));
    $week_nr        = date('W');
    $jaar           = date('Y');

    $job_rows = '';
    foreach ($jobs as $post) {
        $title      = esc_html(get_the_title($post));
        $link       = esc_url(get_permalink($post));
        $company    = esc_html(get_post_meta($post->ID, '_company_name', true));
        $location   = esc_html(get_post_meta($post->ID, '_job_location', true));
        $terms      = wp_get_post_terms($post->ID, 'job_sector', ['fields' => 'names']);
        $sector_str = !empty($terms) && !is_wp_error($terms) ? esc_html(implode(', ', $terms)) : '';

        $meta      = array_filter([$company, $location, $sector_str]);
        $meta_html = $meta
            ? '<div style="margin-top:4px;font-size:13px;color:#666;">' . implode(' &nbsp;·&nbsp; ', $meta) . '</div>'
            : '';

        $job_rows .= "
        <tr>
          <td style=\"padding:18px 0;border-bottom:1px solid #e4ede9;\">
            <a href=\"{$link}\" style=\"font-size:16px;font-weight:700;color:#168AAD;text-decoration:none;font-family:Arial,sans-serif;\">{$title}</a>
            {$meta_html}
            <div style=\"margin-top:12px;\">
              <a href=\"{$link}\" style=\"display:inline-block;padding:8px 18px;background:#168AAD;color:#ffffff;font-size:13px;font-weight:600;border-radius:4px;text-decoration:none;font-family:Arial,sans-serif;\">View job &rarr;</a>
            </div>
          </td>
        </tr>";
    }

    return "<!DOCTYPE html>
<html lang=\"en\">
<head>
  <meta charset=\"UTF-8\">
  <meta name=\"viewport\" content=\"width=device-width,initial-scale=1.0\">
  <title>Jobnewsletter week {$week_nr} — Sustainablejobs.com</title>
</head>
<body style=\"margin:0;padding:0;background:#f2f6f4;font-family:Arial,sans-serif;\">
<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#f2f6f4;padding:32px 16px;\">
  <tr>
    <td align=\"center\">
      <table width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #dceae4;\">

        <!-- Header -->
        <tr>
          <td style=\"background:#168AAD;padding:24px 32px;\">
            <span style=\"font-family:Arial,sans-serif;font-size:20px;font-weight:700;color:#ffffff;\">Sustainablejobs.com</span>
            <span style=\"display:block;font-family:Arial,sans-serif;font-size:13px;color:rgba(255,255,255,0.75);margin-top:4px;\">Jobnewsletter &mdash; week {$week_nr}, {$jaar}</span>
          </td>
        </tr>

        <!-- Intro -->
        <tr>
          <td style=\"padding:28px 32px 8px;\">
            <p style=\"margin:0 0 10px;font-size:20px;font-weight:700;color:#168AAD;font-family:Arial,sans-serif;\">Hi %FIRSTNAME%!</p>
            <p style=\"margin:0;font-size:15px;color:#444444;line-height:1.65;font-family:Arial,sans-serif;\">These are this week's latest sustainable jobs. Discover where you can make a difference.</p>
          </td>
        </tr>

        <!-- Jobs -->
        <tr>
          <td style=\"padding:8px 32px 24px;\">
            <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">
              {$job_rows}
            </table>
          </td>
        </tr>

        <!-- CTA -->
        <tr>
          <td style=\"padding:0 32px 32px;text-align:center;\">
            <a href=\"{$alle_jobs}\" style=\"display:inline-block;padding:13px 28px;background:#168AAD;color:#ffffff;font-size:15px;font-weight:700;border-radius:6px;text-decoration:none;font-family:Arial,sans-serif;\">View all jobs</a>
          </td>
        </tr>

        <!-- Divider -->
        <tr>
          <td style=\"padding:0 32px;\"><hr style=\"border:none;border-top:1px solid #e4ede9;margin:0;\"></td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style=\"background:#f2f6f4;padding:20px 32px;text-align:center;\">
            <p style=\"margin:0 0 6px;font-size:12px;color:#999999;font-family:Arial,sans-serif;\">You are receiving this newsletter because you signed up on Sustainablejobs.com.</p>
            <p style=\"margin:0;font-size:12px;font-family:Arial,sans-serif;\">
              <a href=\"%UNSUBSCRIBELINK%\" style=\"color:#168AAD;\">Unsubscribe from the newsletter</a>
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
