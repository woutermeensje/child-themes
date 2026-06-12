<?php
if (!defined('ABSPATH')) exit;

/* ============================================================
   Extra meta velden voor job_listing posts
   - Salarisrange, uren per week, contactpersoon
============================================================ */

add_action('add_meta_boxes', function () {
    add_meta_box(
        'sj_job_extra_info',
        'Vacature details',
        'sj_job_extra_info_meta_box',
        'job_listing',
        'normal',
        'high'
    );
    add_meta_box(
        'sj_job_contact',
        'Contactpersoon',
        'sj_job_contact_meta_box',
        'job_listing',
        'normal',
        'default'
    );
});

/* ── Vacature details meta box ───────────────────────────── */
function sj_job_extra_info_meta_box($post) {
    wp_nonce_field('sj_job_extra_info_save', 'sj_job_extra_info_nonce');
    $salary = get_post_meta($post->ID, '_job_salary_range', true) ?: get_post_meta($post->ID, '_job_salary', true);
    $hours  = get_post_meta($post->ID, '_job_hours_per_week', true);
    ?>
    <style>
    .sj-meta-field { margin-bottom: 14px; }
    .sj-meta-field label { display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px; color: #374151; }
    .sj-meta-field input, .sj-meta-field select { width: 100%; padding: 7px 10px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px; }
    </style>
    <div class="sj-meta-field">
        <label for="sj_salary_range">Salarisrange</label>
        <input type="text" id="sj_salary_range" name="sj_salary_range"
               value="<?php echo esc_attr($salary); ?>" placeholder="Bijv. 3000 - 4500 per maand">
    </div>
    <div class="sj-meta-field">
        <label for="sj_hours_per_week">Uren per week</label>
        <input type="text" id="sj_hours_per_week" name="sj_hours_per_week"
               value="<?php echo esc_attr($hours); ?>" placeholder="Bijv. 32-40 uur">
    </div>
    <?php
}

/* ── Contactpersoon meta box ─────────────────────────────── */
function sj_job_contact_meta_box($post) {
    wp_nonce_field('sj_job_contact_save', 'sj_job_contact_nonce');
    $firstname = get_post_meta($post->ID, '_job_contact_firstname', true) ?: get_post_meta($post->ID, '_contact_first_name', true);
    $lastname  = get_post_meta($post->ID, '_job_contact_lastname', true) ?: get_post_meta($post->ID, '_contact_last_name', true);
    $email     = get_post_meta($post->ID, '_job_contact_email', true) ?: get_post_meta($post->ID, '_contact_email', true);
    ?>
    <div class="sj-meta-field">
        <label for="sj_contact_firstname">Voornaam contactpersoon</label>
        <input type="text" id="sj_contact_firstname" name="sj_contact_firstname"
               value="<?php echo esc_attr($firstname); ?>" placeholder="Voornaam">
    </div>
    <div class="sj-meta-field">
        <label for="sj_contact_lastname">Achternaam contactpersoon</label>
        <input type="text" id="sj_contact_lastname" name="sj_contact_lastname"
               value="<?php echo esc_attr($lastname); ?>" placeholder="Achternaam">
    </div>
    <div class="sj-meta-field">
        <label for="sj_contact_email">E-mailadres contactpersoon</label>
        <input type="email" id="sj_contact_email" name="sj_contact_email"
               value="<?php echo esc_attr($email); ?>" placeholder="naam@bedrijf.nl">
    </div>
    <?php
}

/* ── Opslaan ─────────────────────────────────────────────── */
add_action('save_post_job_listing', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (
        isset($_POST['sj_job_extra_info_nonce']) &&
        wp_verify_nonce($_POST['sj_job_extra_info_nonce'], 'sj_job_extra_info_save')
    ) {
        update_post_meta($post_id, '_job_salary_range',    sanitize_text_field($_POST['sj_salary_range']    ?? ''));
        update_post_meta($post_id, '_job_hours_per_week',  sanitize_text_field($_POST['sj_hours_per_week']  ?? ''));
    }

    if (
        isset($_POST['sj_job_contact_nonce']) &&
        wp_verify_nonce($_POST['sj_job_contact_nonce'], 'sj_job_contact_save')
    ) {
        $first_name = sanitize_text_field($_POST['sj_contact_firstname'] ?? '');
        $last_name  = sanitize_text_field($_POST['sj_contact_lastname']  ?? '');
        $email      = sanitize_email($_POST['sj_contact_email']          ?? '');

        update_post_meta($post_id, '_job_contact_firstname', $first_name);
        update_post_meta($post_id, '_job_contact_lastname',  $last_name);
        update_post_meta($post_id, '_job_contact_email',     $email);

        update_post_meta($post_id, '_contact_first_name', $first_name);
        update_post_meta($post_id, '_contact_last_name',  $last_name);
        update_post_meta($post_id, '_contact_email',      $email);
    }
});
