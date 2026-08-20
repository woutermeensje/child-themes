<?php
if (!defined('ABSPATH')) exit;

/**
 * Maakt automatisch een WP Job Manager vacature aan na inzending van Gravity Forms formulier 21.
 *
 * De veldmapping werkt op herkenbare veldlabels, omdat de lokale repo geen live GF-formulierdata
 * bevat. Exacte veld-ID's kunnen later zonder codewijziging via de filter hieronder worden gevuld.
 */

if (!defined('SJ_GF_VACATURE_FORM_ID')) {
    define('SJ_GF_VACATURE_FORM_ID', 21);
}

add_action('gform_after_submission_' . SJ_GF_VACATURE_FORM_ID, 'sj_create_job_listing_from_gravity_form_21', 10, 2);

function sj_create_job_listing_from_gravity_form_21($entry, $form) {
    $entry_id = absint($entry['id'] ?? 0);

    if (!$entry_id) {
        sj_gf21_log('Gravity Forms entry zonder ID overgeslagen.');
        return;
    }

    if (!post_type_exists('job_listing')) {
        sj_gf21_log('WP Job Manager post type job_listing bestaat niet.', ['entry_id' => $entry_id]);
        return;
    }

    $existing_job_id = sj_gf21_get_existing_job_listing_id($entry_id);
    if ($existing_job_id) {
        sj_gf21_log('Gravity Forms entry is al gekoppeld aan een vacature.', [
            'entry_id' => $entry_id,
            'job_id'   => $existing_job_id,
        ]);
        return;
    }

    $data = sj_gf21_collect_entry_data($entry, $form);

    $post_status = apply_filters('sj_gf_vacature_form_21_job_status', 'draft', $entry, $form, $data);
    $post_author = absint(apply_filters('sj_gf_vacature_form_21_post_author', get_current_user_id() ?: 1, $entry, $form, $data));

    $job_id = wp_insert_post([
        'post_title'   => $data['title'] ?: sprintf('Vacature via formulier #%d', $entry_id),
        'post_content' => $data['description'],
        'post_status'  => $post_status,
        'post_type'    => 'job_listing',
        'post_author'  => $post_author,
    ], true);

    if (is_wp_error($job_id)) {
        sj_gf21_log('Aanmaken van WPJM vacature mislukt.', [
            'entry_id' => $entry_id,
            'error'    => $job_id->get_error_message(),
        ]);
        return;
    }

    sj_gf21_apply_job_listing_meta((int) $job_id, $entry, $form, $data);
    sj_gf21_apply_job_listing_terms((int) $job_id, $data);

    if (function_exists('gform_update_meta')) {
        gform_update_meta($entry_id, 'sj_wpjm_job_listing_id', (int) $job_id, SJ_GF_VACATURE_FORM_ID);
    }

    do_action('sj_gf_vacature_form_21_job_created', (int) $job_id, $entry, $form, $data);
}

function sj_gf21_collect_entry_data($entry, $form) {
    $email       = sanitize_email(sj_gf21_get_entry_text($entry, $form, 'email'));
    $application = sj_gf21_sanitize_application(sj_gf21_get_entry_text($entry, $form, 'application'), $email);

    $data = [
        'title'              => sanitize_text_field(sj_gf21_get_entry_text($entry, $form, 'title')),
        'description'        => wp_kses_post(sj_gf21_get_entry_text($entry, $form, 'description')),
        'location'           => sanitize_text_field(sj_gf21_get_entry_text($entry, $form, 'location')),
        'company_name'       => sanitize_text_field(sj_gf21_get_entry_text($entry, $form, 'company_name')),
        'company_website'    => sj_gf21_sanitize_url(sj_gf21_get_entry_text($entry, $form, 'company_website')),
        'email'              => $email,
        'application'        => $application,
        'contact_first_name' => sanitize_text_field(sj_gf21_get_entry_text($entry, $form, 'contact_first_name')),
        'contact_last_name'  => sanitize_text_field(sj_gf21_get_entry_text($entry, $form, 'contact_last_name')),
        'salary_range'       => sanitize_text_field(sj_gf21_get_entry_text($entry, $form, 'salary_range')),
        'hours_per_week'     => sanitize_text_field(sj_gf21_get_entry_text($entry, $form, 'hours_per_week')),
        'package'            => sanitize_text_field(sj_gf21_get_entry_text($entry, $form, 'package')),
        'job_types'          => array_map('sanitize_text_field', sj_gf21_get_entry_list($entry, $form, 'job_types')),
        'sectors'            => array_map('sanitize_text_field', sj_gf21_get_entry_list($entry, $form, 'sectors')),
        'categories'         => array_map('sanitize_text_field', sj_gf21_get_entry_list($entry, $form, 'categories')),
        'company_logo_url'   => sj_gf21_get_entry_file_url($entry, $form, 'company_logo'),
        'cover_image_url'    => sj_gf21_get_entry_file_url($entry, $form, 'cover_image'),
    ];

    return apply_filters('sj_gf_vacature_form_21_entry_data', $data, $entry, $form);
}

function sj_gf21_apply_job_listing_meta($job_id, $entry, $form, $data) {
    $entry_id = absint($entry['id'] ?? 0);
    $form_id  = absint($form['id'] ?? SJ_GF_VACATURE_FORM_ID);

    $meta = [
        '_job_location'              => $data['location'],
        '_application'               => $data['application'],
        '_company_name'              => $data['company_name'],
        '_company_website'           => $data['company_website'],
        '_company_email'             => $data['email'],
        '_job_contact_firstname'     => $data['contact_first_name'],
        '_job_contact_lastname'      => $data['contact_last_name'],
        '_job_contact_email'         => $data['email'],
        '_job_salary'                => $data['salary_range'],
        '_job_salary_range'          => $data['salary_range'],
        '_job_hours_per_week'        => $data['hours_per_week'],
        '_filled'                    => 0,
        '_featured'                  => 0,
        '_job_expires'               => '',
        '_sj_pakket'                 => $data['package'],
        '_sj_gravityforms_form_id'   => $form_id,
        '_sj_gravityforms_entry_id'  => $entry_id,
        '_sj_gravityforms_entry_url' => admin_url('admin.php?page=gf_entries&view=entry&id=' . $form_id . '&lid=' . $entry_id),
        '_sj_created_from'           => 'gravityforms_form_' . $form_id,
    ];

    if (!empty($data['company_logo_url'])) {
        $meta['_company_logo'] = $data['company_logo_url'];
    }

    if (!empty($data['cover_image_url'])) {
        $meta['_cover_image'] = $data['cover_image_url'];
    }

    foreach ($meta as $key => $value) {
        update_post_meta($job_id, $key, $value);
    }
}

function sj_gf21_apply_job_listing_terms($job_id, $data) {
    if (!empty($data['company_name'])) {
        sj_gf21_set_terms_from_values($job_id, 'job_company', [$data['company_name']], true, false);
    }

    if (!empty($data['job_types'])) {
        sj_gf21_set_terms_from_values($job_id, 'job_listing_type', $data['job_types']);
    }

    if (!empty($data['sectors'])) {
        sj_gf21_set_terms_from_values($job_id, 'job_sector', $data['sectors']);
    }

    if (!empty($data['categories'])) {
        sj_gf21_set_terms_from_values($job_id, 'job_listing_category', $data['categories']);
    }

    if (function_exists('sj_sync_organisatie_types_for_job')) {
        sj_sync_organisatie_types_for_job($job_id, true);
    }

    if (function_exists('sj_sync_sectors_for_job')) {
        sj_sync_sectors_for_job($job_id, true);
    }
}

function sj_gf21_get_existing_job_listing_id($entry_id) {
    if (function_exists('gform_get_meta')) {
        $linked_id = absint(gform_get_meta($entry_id, 'sj_wpjm_job_listing_id'));

        if ($linked_id && get_post_type($linked_id) === 'job_listing') {
            return $linked_id;
        }
    }

    $existing = get_posts([
        'post_type'      => 'job_listing',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => [
            'relation' => 'AND',
            [
                'key'   => '_sj_gravityforms_form_id',
                'value' => SJ_GF_VACATURE_FORM_ID,
            ],
            [
                'key'   => '_sj_gravityforms_entry_id',
                'value' => absint($entry_id),
            ],
        ],
    ]);

    return !empty($existing) ? absint($existing[0]) : 0;
}

function sj_gf21_get_field_mapping($form = null) {
    $mapping = [
        'title' => [
            'ids'    => [],
            'labels' => ['vacaturetitel', 'vacature titel', 'functietitel', 'functie titel', 'titel vacature', 'job title'],
        ],
        'description' => [
            'ids'    => [],
            'labels' => ['vacature omschrijving', 'omschrijving vacature', 'vacaturetekst', 'vacature tekst', 'functieomschrijving', 'omschrijving'],
        ],
        'location' => [
            'ids'    => [],
            'labels' => ['locatie', 'standplaats', 'plaats', 'werkplek', 'job location'],
        ],
        'company_name' => [
            'ids'    => [],
            'labels' => ['bedrijfsnaam', 'organisatie naam', 'organisatienaam', 'naam organisatie', 'bedrijf organisatie', 'company name'],
        ],
        'company_website' => [
            'ids'    => [],
            'labels' => ['bedrijfswebsite', 'organisatie website', 'website organisatie', 'website bedrijf', 'company website'],
        ],
        'email' => [
            'ids'    => [],
            'labels' => ['e-mailadres', 'e-mail adres', 'emailadres', 'email adres', 'contact e-mail', 'contact email', 'e-mail', 'email'],
        ],
        'application' => [
            'ids'    => [],
            'labels' => ['sollicitatielink', 'sollicitatie link', 'sollicitatie url', 'reageren via', 'application url', 'application email', 'apply url'],
        ],
        'contact_first_name' => [
            'ids'    => [],
            'labels' => ['voornaam', 'voornaam contactpersoon', 'contact voornaam', 'first name'],
        ],
        'contact_last_name' => [
            'ids'    => [],
            'labels' => ['achternaam', 'achternaam contactpersoon', 'contact achternaam', 'last name'],
        ],
        'salary_range' => [
            'ids'    => [],
            'labels' => ['salarisrange', 'salaris range', 'salarisindicatie', 'salaris indicatie', 'salaris', 'beloning'],
        ],
        'hours_per_week' => [
            'ids'    => [],
            'labels' => ['uren per week', 'aantal uur', 'aantal uren', 'contracturen', 'uren'],
        ],
        'package' => [
            'ids'    => [],
            'labels' => ['pakket', 'plaatsingspakket', 'vacaturepakket', 'vacature pakket'],
        ],
        'job_types' => [
            'ids'    => [],
            'labels' => ['type baan', 'dienstverband', 'dienstverbanden', 'soort dienstverband', 'job type', 'employment type'],
        ],
        'sectors' => [
            'ids'    => [],
            'labels' => ['sector', 'sectoren', 'branche', 'branches', 'vakgebied'],
        ],
        'categories' => [
            'ids'    => [],
            'labels' => ['categorie', 'categorieen', 'vacaturecategorie', 'vacature categorie'],
        ],
        'company_logo' => [
            'ids'         => [],
            'labels'      => ['bedrijfslogo', 'organisatie logo', 'logo organisatie', 'company logo', 'logo'],
            'field_types' => ['fileupload', 'post_image'],
        ],
        'cover_image' => [
            'ids'         => [],
            'labels'      => ['uitgelichte afbeelding', 'cover afbeelding', 'cover image', 'vacature afbeelding', 'afbeelding vacature'],
            'field_types' => ['fileupload', 'post_image'],
        ],
    ];

    return apply_filters('sj_gf_vacature_form_21_field_mapping', $mapping, $form);
}

function sj_gf21_get_entry_text($entry, $form, $mapping_key) {
    $values = sj_gf21_find_entry_values($entry, $form, $mapping_key);

    foreach ($values as $value) {
        $text = sj_gf21_value_to_text($value);
        if ($text !== '') {
            return $text;
        }
    }

    return '';
}

function sj_gf21_get_entry_list($entry, $form, $mapping_key) {
    $values = sj_gf21_find_entry_values($entry, $form, $mapping_key);
    $items  = [];

    foreach ($values as $value) {
        $items = array_merge($items, sj_gf21_parse_list_value($value));
    }

    return sj_gf21_unique_non_empty($items);
}

function sj_gf21_get_entry_file_url($entry, $form, $mapping_key) {
    $values = sj_gf21_find_entry_values($entry, $form, $mapping_key);

    foreach ($values as $value) {
        $urls = sj_gf21_extract_urls($value);
        if (!empty($urls)) {
            return esc_url_raw($urls[0]);
        }
    }

    return '';
}

function sj_gf21_find_entry_values($entry, $form, $mapping_key) {
    $mapping = sj_gf21_get_field_mapping($form);
    $config  = $mapping[$mapping_key] ?? [];
    $values  = [];

    foreach ((array) ($config['ids'] ?? []) as $field_id) {
        $values = array_merge($values, sj_gf21_get_values_by_id($entry, $form, (string) $field_id));
    }

    if (!empty($values)) {
        return sj_gf21_unique_non_empty($values);
    }

    foreach (sj_gf21_get_form_fields($form) as $field) {
        if (!sj_gf21_field_type_is_allowed($field, $config)) {
            continue;
        }

        if (sj_gf21_field_matches_config($field, $config)) {
            $values = array_merge($values, sj_gf21_get_field_values($entry, $field));
        }

        foreach ((array) sj_gf21_field_prop($field, 'inputs', []) as $input) {
            if (sj_gf21_input_matches_config($input, $config)) {
                $input_id = (string) sj_gf21_input_prop($input, 'id', '');
                $values[] = sj_gf21_get_entry_raw_value($entry, $input_id);
            }
        }
    }

    return sj_gf21_unique_non_empty($values);
}

function sj_gf21_get_values_by_id($entry, $form, $field_id) {
    $values = [];
    $value  = sj_gf21_get_entry_raw_value($entry, $field_id);

    if (sj_gf21_value_to_text($value) !== '') {
        $values[] = $value;
        return $values;
    }

    if (strpos($field_id, '.') !== false) {
        return $values;
    }

    foreach (sj_gf21_get_form_fields($form) as $field) {
        if ((string) sj_gf21_field_prop($field, 'id', '') === $field_id) {
            return sj_gf21_get_field_values($entry, $field);
        }
    }

    return $values;
}

function sj_gf21_get_field_values($entry, $field) {
    $values   = [];
    $field_id = (string) sj_gf21_field_prop($field, 'id', '');

    if ($field_id !== '') {
        $value = sj_gf21_get_entry_raw_value($entry, $field_id);
        if (sj_gf21_value_to_text($value) !== '') {
            $values[] = $value;
        }
    }

    foreach ((array) sj_gf21_field_prop($field, 'inputs', []) as $input) {
        $input_id = (string) sj_gf21_input_prop($input, 'id', '');
        if ($input_id === '') {
            continue;
        }

        $value = sj_gf21_get_entry_raw_value($entry, $input_id);
        if (sj_gf21_value_to_text($value) !== '') {
            $values[] = $value;
        }
    }

    return $values;
}

function sj_gf21_get_entry_raw_value($entry, $key) {
    $key = (string) $key;

    return ($key !== '' && is_array($entry) && array_key_exists($key, $entry)) ? $entry[$key] : '';
}

function sj_gf21_get_form_fields($form) {
    return is_array($form) && !empty($form['fields']) && is_array($form['fields']) ? $form['fields'] : [];
}

function sj_gf21_field_prop($field, $prop, $default = null) {
    if (is_object($field) && isset($field->{$prop})) {
        return $field->{$prop};
    }

    if (is_array($field) && array_key_exists($prop, $field)) {
        return $field[$prop];
    }

    return $default;
}

function sj_gf21_input_prop($input, $prop, $default = null) {
    if (is_object($input) && isset($input->{$prop})) {
        return $input->{$prop};
    }

    if (is_array($input) && array_key_exists($prop, $input)) {
        return $input[$prop];
    }

    return $default;
}

function sj_gf21_field_matches_config($field, $config) {
    $labels = [
        sj_gf21_field_prop($field, 'label', ''),
        sj_gf21_field_prop($field, 'adminLabel', ''),
        sj_gf21_field_prop($field, 'inputName', ''),
    ];

    foreach ($labels as $label) {
        if (sj_gf21_label_matches($label, $config['labels'] ?? [])) {
            return true;
        }
    }

    return false;
}

function sj_gf21_input_matches_config($input, $config) {
    $labels = [
        sj_gf21_input_prop($input, 'label', ''),
        sj_gf21_input_prop($input, 'name', ''),
        sj_gf21_input_prop($input, 'customLabel', ''),
    ];

    foreach ($labels as $label) {
        if (sj_gf21_label_matches($label, $config['labels'] ?? [])) {
            return true;
        }
    }

    return false;
}

function sj_gf21_field_type_is_allowed($field, $config) {
    $allowed_types = (array) ($config['field_types'] ?? []);

    if (empty($allowed_types)) {
        return true;
    }

    $type       = (string) sj_gf21_field_prop($field, 'type', '');
    $input_type = (string) sj_gf21_field_prop($field, 'inputType', '');
    $input_type = $input_type ?: $type;

    return in_array($type, $allowed_types, true) || in_array($input_type, $allowed_types, true);
}

function sj_gf21_label_matches($label, $aliases) {
    $label = sj_gf21_normalize_label($label);

    if ($label === '') {
        return false;
    }

    foreach ((array) $aliases as $alias) {
        $alias = sj_gf21_normalize_label($alias);

        if ($alias !== '' && ($label === $alias || strpos($label, $alias) !== false)) {
            return true;
        }
    }

    return false;
}

function sj_gf21_normalize_label($label) {
    $label = is_scalar($label) ? (string) $label : '';
    $label = function_exists('remove_accents') ? remove_accents($label) : $label;
    $label = strtolower(wp_strip_all_tags($label));
    $label = preg_replace('/[^a-z0-9]+/', ' ', $label);

    return trim((string) $label);
}

function sj_gf21_value_to_text($value, $separator = ', ') {
    if (is_array($value)) {
        $parts = [];

        foreach ($value as $item) {
            $text = sj_gf21_value_to_text($item, $separator);
            if ($text !== '') {
                $parts[] = $text;
            }
        }

        return trim(implode($separator, $parts));
    }

    $value = trim((string) $value);

    if ($value === '') {
        return '';
    }

    $decoded = sj_gf21_decode_json($value);
    if (is_array($decoded)) {
        return sj_gf21_value_to_text($decoded, $separator);
    }

    return $value;
}

function sj_gf21_parse_list_value($value) {
    if (is_array($value)) {
        $items = [];

        foreach ($value as $item) {
            $items = array_merge($items, sj_gf21_parse_list_value($item));
        }

        return $items;
    }

    $value = trim((string) $value);

    if ($value === '') {
        return [];
    }

    $decoded = sj_gf21_decode_json($value);
    if (is_array($decoded)) {
        return sj_gf21_parse_list_value($decoded);
    }

    return array_map('trim', preg_split('/[,;\n\r]+/', $value));
}

function sj_gf21_decode_json($value) {
    $value = trim((string) $value);

    if ($value === '' || !in_array($value[0], ['[', '{'], true)) {
        return null;
    }

    $decoded = json_decode($value, true);

    return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
}

function sj_gf21_extract_urls($value) {
    if (is_array($value)) {
        $urls = [];

        foreach ($value as $item) {
            $urls = array_merge($urls, sj_gf21_extract_urls($item));
        }

        return sj_gf21_unique_non_empty($urls);
    }

    $value = trim((string) $value);

    if ($value === '') {
        return [];
    }

    $decoded = sj_gf21_decode_json($value);
    if (is_array($decoded)) {
        return sj_gf21_extract_urls($decoded);
    }

    preg_match_all('#https?://[^\s,"\']+#i', $value, $matches);

    if (empty($matches[0])) {
        return [];
    }

    return sj_gf21_unique_non_empty(array_map('esc_url_raw', $matches[0]));
}

function sj_gf21_unique_non_empty($items) {
    $clean = [];

    foreach ((array) $items as $item) {
        $item = sj_gf21_value_to_text($item);
        if ($item !== '') {
            $clean[] = $item;
        }
    }

    return array_values(array_unique($clean));
}

function sj_gf21_sanitize_application($value, $fallback_email = '') {
    $value = trim((string) $value);

    if (is_email($value)) {
        return sanitize_email($value);
    }

    $url = sj_gf21_sanitize_url($value);
    if ($url) {
        return $url;
    }

    return is_email($fallback_email) ? sanitize_email($fallback_email) : '';
}

function sj_gf21_sanitize_url($value) {
    $value = trim((string) $value);

    if ($value === '') {
        return '';
    }

    if (!preg_match('#^[a-z][a-z0-9+.-]*://#i', $value) && strpos($value, '.') !== false) {
        $value = 'https://' . $value;
    }

    return esc_url_raw($value);
}

function sj_gf21_set_terms_from_values($post_id, $taxonomy, $values, $create_missing = false, $split_values = true) {
    if (!taxonomy_exists($taxonomy)) {
        return;
    }

    $term_ids = [];
    $values   = $split_values ? sj_gf21_parse_list_value($values) : (array) $values;

    foreach ($values as $value) {
        $name = sanitize_text_field($value);

        if ($name === '') {
            continue;
        }

        $term = is_numeric($name) ? get_term(absint($name), $taxonomy) : false;

        if (!$term || is_wp_error($term)) {
            $term = get_term_by('slug', sanitize_title($name), $taxonomy);
        }

        if (!$term || is_wp_error($term)) {
            $term = get_term_by('name', $name, $taxonomy);
        }

        if ((!$term || is_wp_error($term)) && $create_missing) {
            $inserted = wp_insert_term($name, $taxonomy);
            if (!is_wp_error($inserted) && !empty($inserted['term_id'])) {
                $term_ids[] = absint($inserted['term_id']);
            }
            continue;
        }

        if ($term && !is_wp_error($term)) {
            $term_ids[] = absint($term->term_id);
        }
    }

    $term_ids = array_values(array_unique(array_filter($term_ids)));

    if (!empty($term_ids)) {
        wp_set_object_terms($post_id, $term_ids, $taxonomy, false);
    }
}

function sj_gf21_log($message, $context = []) {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }

    if (!empty($context)) {
        $message .= ' ' . wp_json_encode($context);
    }

    error_log('[SJ GF21 -> WPJM] ' . $message);
}
