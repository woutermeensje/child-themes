<?php
if (!defined('ABSPATH')) exit;

/**
 * Stuur een API-verzoek naar ActiveCampaign.
 */
function sj_ac_request(string $method, string $endpoint, array $body = []): ?array {
    $base_url = defined('ACTIVECAMPAIGN_BASE_URL') ? ACTIVECAMPAIGN_BASE_URL : '';
    $api_key  = defined('ACTIVECAMPAIGN_API_KEY')  ? ACTIVECAMPAIGN_API_KEY  : '';
    $timeout  = defined('ACTIVECAMPAIGN_TIMEOUT')  ? (int) ACTIVECAMPAIGN_TIMEOUT : 10;

    if (!$base_url || !$api_key) {
        error_log('[AC] Geen API-credentials geconfigureerd.');
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
        error_log('[AC] API-fout: ' . $response->get_error_message());
        return null;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    $code = wp_remote_retrieve_response_code($response);

    if ($code >= 400) {
        error_log('[AC] HTTP ' . $code . ' op ' . $endpoint . ': ' . wp_remote_retrieve_body($response));
        return null;
    }

    return $data;
}

/**
 * Zoek een tag op naam, maak hem aan als hij niet bestaat.
 * Resultaat wordt gecached in een transient om dubbele API-calls te voorkomen.
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
 * Voeg een tag toe aan een contact.
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
 * Schrijf een job-alert abonnee in bij ActiveCampaign:
 * 1. Contact aanmaken / bijwerken
 * 2. Inschrijven op de job-alerts lijst
 * 3. Werkzoekende-tag toevoegen (indien ingesteld)
 * 4. Sector-tags aanmaken + toevoegen
 */
function sj_ac_subscribe_job_alert(string $voornaam, string $email, array $sector_slugs): bool {
    if (defined('ACTIVECAMPAIGN_ENABLED') && !ACTIVECAMPAIGN_ENABLED) return false;

    $list_id = defined('ACTIVECAMPAIGN_LIST_ID') ? (int) ACTIVECAMPAIGN_LIST_ID : 1;

    // 1. Contact sync (aanmaken of bijwerken)
    $result = sj_ac_request('POST', 'contact/sync', [
        'contact' => [
            'email'     => $email,
            'firstName' => $voornaam,
        ],
    ]);

    if (!$result || empty($result['contact']['id'])) {
        error_log('[AC] Contact sync mislukt voor: ' . $email);
        return false;
    }

    $contact_id = (int) $result['contact']['id'];

    // 2. Inschrijven op lijst
    sj_ac_request('POST', 'contactLists', [
        'contactList' => [
            'list'    => $list_id,
            'contact' => $contact_id,
            'status'  => 1,
        ],
    ]);

    // 3. Werkzoekende-tag
    $werkzoekende_tag_id = defined('ACTIVECAMPAIGN_WERKZOEKENDE_TAG_ID') ? (int) ACTIVECAMPAIGN_WERKZOEKENDE_TAG_ID : 0;
    if ($werkzoekende_tag_id) {
        sj_ac_add_tag_to_contact($contact_id, $werkzoekende_tag_id);
    }

    // 4. Sector-tags
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
