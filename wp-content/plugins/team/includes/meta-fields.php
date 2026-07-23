<?php
if (!defined('ABSPATH')) exit;

function mh_team_entry_types(): array {
    return [
        'team_member' => 'Teamlid',
        'partner'     => 'Partner',
    ];
}

function mh_team_sanitize_entry_type($value): string {
    $value = sanitize_key((string) $value);

    return array_key_exists($value, mh_team_entry_types()) ? $value : 'team_member';
}

function mh_team_meta_keys(): array {
    return [
        '_mh_team_entry_type' => [
            'sanitize_callback' => 'mh_team_sanitize_entry_type',
            'type'              => 'string',
        ],
        '_mh_team_first_name' => [
            'sanitize_callback' => 'sanitize_text_field',
            'type'              => 'string',
        ],
        '_mh_team_last_name' => [
            'sanitize_callback' => 'sanitize_text_field',
            'type'              => 'string',
        ],
        '_mh_team_email' => [
            'sanitize_callback' => 'sanitize_email',
            'type'              => 'string',
        ],
        '_mh_team_phone' => [
            'sanitize_callback' => 'sanitize_text_field',
            'type'              => 'string',
        ],
        '_mh_team_role' => [
            'sanitize_callback' => 'sanitize_text_field',
            'type'              => 'string',
        ],
    ];
}

add_action('init', function () {
    foreach (mh_team_meta_keys() as $meta_key => $args) {
        register_post_meta('mh_team_member', $meta_key, [
            'single'            => true,
            'type'              => $args['type'],
            'show_in_rest'      => true,
            'sanitize_callback' => $args['sanitize_callback'],
            'auth_callback'     => function () {
                return current_user_can('edit_posts');
            },
        ]);
    }
});

add_action('add_meta_boxes', function () {
    add_meta_box(
        'mh_team_member_details',
        'Teamgegevens',
        'mh_team_member_details_callback',
        'mh_team_member',
        'normal',
        'high'
    );
});

function mh_team_member_details_callback(WP_Post $post): void {
    wp_nonce_field('mh_team_member_details_save', 'mh_team_member_details_nonce');

    $entry_type = get_post_meta($post->ID, '_mh_team_entry_type', true);
    $entry_type = $entry_type !== '' ? mh_team_sanitize_entry_type($entry_type) : 'team_member';

    $fields = [
        'mh_team_first_name' => [
            'label'       => 'Voornaam',
            'meta_key'    => '_mh_team_first_name',
            'type'        => 'text',
            'placeholder' => 'Bijv. Sanne',
        ],
        'mh_team_last_name' => [
            'label'       => 'Achternaam',
            'meta_key'    => '_mh_team_last_name',
            'type'        => 'text',
            'placeholder' => 'Bijv. Jansen',
        ],
        'mh_team_email' => [
            'label'       => 'E-mailadres',
            'meta_key'    => '_mh_team_email',
            'type'        => 'email',
            'placeholder' => 'naam@modulairehuisvesting.nl',
        ],
        'mh_team_phone' => [
            'label'       => 'Telefoonnummer',
            'meta_key'    => '_mh_team_phone',
            'type'        => 'tel',
            'placeholder' => '085 239 20 40',
        ],
        'mh_team_role' => [
            'label'       => 'Functietitel / partnercategorie',
            'meta_key'    => '_mh_team_role',
            'type'        => 'text',
            'placeholder' => 'Bijv. Adviseur modulaire units, leverancier of transporteur',
        ],
    ];
    ?>
    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px 18px;">
        <p style="grid-column:1 / -1;margin:0;">
            <label for="mh_team_entry_type" style="display:block;font-weight:600;margin:0 0 6px;">
                Soort
            </label>
            <select id="mh_team_entry_type" name="mh_team_entry_type" style="width:100%;max-width:320px;">
                <?php foreach (mh_team_entry_types() as $value => $label) : ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php selected($entry_type, $value); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <?php foreach ($fields as $name => $field) : ?>
            <p style="margin:0;">
                <label for="<?php echo esc_attr($name); ?>" style="display:block;font-weight:600;margin:0 0 6px;">
                    <?php echo esc_html($field['label']); ?>
                </label>
                <input
                    id="<?php echo esc_attr($name); ?>"
                    type="<?php echo esc_attr($field['type']); ?>"
                    name="<?php echo esc_attr($name); ?>"
                    value="<?php echo esc_attr(get_post_meta($post->ID, $field['meta_key'], true)); ?>"
                    placeholder="<?php echo esc_attr($field['placeholder']); ?>"
                    style="width:100%;"
                />
            </p>
        <?php endforeach; ?>
    </div>
    <p style="margin:14px 0 0;color:#666;font-size:12px;">
        Gebruik de titel voor de volledige naam of bedrijfsnaam. Gebruik de editor hieronder voor de beschrijving. Stel de profielfoto of het partnerlogo in via de box "Profielfoto".
    </p>
    <?php
}

add_action('save_post_mh_team_member', function ($post_id) {
    if (!isset($_POST['mh_team_member_details_nonce'])) return;
    if (!wp_verify_nonce($_POST['mh_team_member_details_nonce'], 'mh_team_member_details_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $fields = [
        'mh_team_entry_type' => [
            'meta_key' => '_mh_team_entry_type',
            'sanitize' => 'mh_team_sanitize_entry_type',
        ],
        'mh_team_first_name' => [
            'meta_key' => '_mh_team_first_name',
            'sanitize' => 'sanitize_text_field',
        ],
        'mh_team_last_name' => [
            'meta_key' => '_mh_team_last_name',
            'sanitize' => 'sanitize_text_field',
        ],
        'mh_team_email' => [
            'meta_key' => '_mh_team_email',
            'sanitize' => 'sanitize_email',
        ],
        'mh_team_phone' => [
            'meta_key' => '_mh_team_phone',
            'sanitize' => 'sanitize_text_field',
        ],
        'mh_team_role' => [
            'meta_key' => '_mh_team_role',
            'sanitize' => 'sanitize_text_field',
        ],
    ];

    foreach ($fields as $field_name => $field) {
        $value = isset($_POST[$field_name]) ? wp_unslash($_POST[$field_name]) : '';
        update_post_meta($post_id, $field['meta_key'], call_user_func($field['sanitize'], $value));
    }
});
