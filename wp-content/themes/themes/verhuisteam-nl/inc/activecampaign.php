<?php
if (!defined('ABSPATH')) exit;

if (!defined('STUDENTINHUREN_AC_AANVRAGEN_LIST_ID')) {
    define('STUDENTINHUREN_AC_AANVRAGEN_LIST_ID', 17);
}

if (!defined('STUDENTINHUREN_AC_OPDRACHT_PLAATSEN_LIST_ID')) {
    define('STUDENTINHUREN_AC_OPDRACHT_PLAATSEN_LIST_ID', 30);
}

if (!function_exists('si_ac_load_env')) {
    function si_ac_load_env(): void {
        if (getenv('ACTIVECAMPAIGN_API_URL') && getenv('ACTIVECAMPAIGN_API_KEY')) {
            return;
        }

        $env_file = ABSPATH . '.env';
        if (!file_exists($env_file)) {
            return;
        }

        foreach (file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value, " \t\n\r\0\x0B\"'"));
        }
    }
}

if (!function_exists('si_ac_request')) {
    function si_ac_request(string $method, string $endpoint, array $body = []): ?array {
        si_ac_load_env();

        $base_url = defined('ACTIVECAMPAIGN_BASE_URL') ? ACTIVECAMPAIGN_BASE_URL : getenv('ACTIVECAMPAIGN_API_URL');
        $api_key  = defined('ACTIVECAMPAIGN_API_KEY') ? ACTIVECAMPAIGN_API_KEY : getenv('ACTIVECAMPAIGN_API_KEY');
        $timeout  = defined('ACTIVECAMPAIGN_TIMEOUT') ? (int) ACTIVECAMPAIGN_TIMEOUT : 10;

        if (!$base_url || !$api_key) {
            error_log('[SI AC] Geen API-credentials geconfigureerd.');
            return null;
        }

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

        $url      = rtrim($base_url, '/') . '/api/3/' . ltrim($endpoint, '/');
        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            error_log('[SI AC] API-fout: ' . $response->get_error_message());
            return null;
        }

        $code = wp_remote_retrieve_response_code($response);
        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($code >= 400) {
            error_log('[SI AC] HTTP ' . $code . ' op ' . $endpoint . ': ' . wp_remote_retrieve_body($response));
            return null;
        }

        return is_array($data) ? $data : [];
    }
}

if (!function_exists('si_ac_subscribe_contact_to_list')) {
    function si_ac_subscribe_contact_to_list(int $list_id, array $contact): bool {
        if ($list_id <= 0) {
            return false;
        }

        $email = sanitize_email($contact['email'] ?? '');
        if (!$email || !is_email($email)) {
            return false;
        }

        $payload = [
            'email' => $email,
        ];

        if (!empty($contact['first_name'])) {
            $payload['firstName'] = sanitize_text_field($contact['first_name']);
        }

        if (!empty($contact['last_name'])) {
            $payload['lastName'] = sanitize_text_field($contact['last_name']);
        }

        if (!empty($contact['phone'])) {
            $payload['phone'] = sanitize_text_field($contact['phone']);
        }

        $result = si_ac_request('POST', 'contact/sync', [
            'contact' => $payload,
        ]);

        if (!$result || empty($result['contact']['id'])) {
            error_log('[SI AC] Contact sync mislukt voor: ' . $email);
            return false;
        }

        $contact_id = (int) $result['contact']['id'];
        $list_result = si_ac_request('POST', 'contactLists', [
            'contactList' => [
                'list'    => $list_id,
                'contact' => $contact_id,
                'status'  => 1,
            ],
        ]);

        return !empty($list_result['contactList']['id']);
    }
}
