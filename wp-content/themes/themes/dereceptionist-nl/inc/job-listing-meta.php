<?php
if (!defined('ABSPATH')) exit;

/* ============================================================
   Extra meta velden voor job_listing posts
   - Salarisrange, uren per week, contactpersoon
============================================================ */

add_action('add_meta_boxes', function () {
    add_meta_box(
        'dr_job_extra_info',
        'Vacature details',
        'dr_job_extra_info_meta_box',
        'job_listing',
        'normal',
        'high'
    );
    add_meta_box(
        'dr_job_contact',
        'Contactpersoon',
        'dr_job_contact_meta_box',
        'job_listing',
        'normal',
        'default'
    );
});

/* ── Vacature details meta box ───────────────────────────── */
function dr_job_extra_info_meta_box($post) {
    wp_nonce_field('dr_job_extra_info_save', 'dr_job_extra_info_nonce');
    $salary = get_post_meta($post->ID, '_job_salary_range', true);
    $hours  = get_post_meta($post->ID, '_job_hours_per_week', true);
    ?>
    <style>
    .dr-meta-field { margin-bottom: 14px; }
    .dr-meta-field label { display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px; color: #374151; }
    .dr-meta-field input, .dr-meta-field select { width: 100%; padding: 7px 10px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px; }
    </style>
    <div class="dr-meta-field">
        <label for="dr_salary_range">Salarisrange</label>
        <input type="text" id="dr_salary_range" name="dr_salary_range"
               value="<?php echo esc_attr($salary); ?>" placeholder="Bijv. €3.000 – €4.500 per maand">
    </div>
    <div class="dr-meta-field">
        <label for="dr_hours_per_week">Uren per week</label>
        <input type="text" id="dr_hours_per_week" name="dr_hours_per_week"
               value="<?php echo esc_attr($hours); ?>" placeholder="Bijv. 32–40 uur">
    </div>
    <?php
}

/* ── Contactpersoon meta box ─────────────────────────────── */
function dr_job_contact_meta_box($post) {
    wp_nonce_field('dr_job_contact_save', 'dr_job_contact_nonce');
    $firstname = get_post_meta($post->ID, '_job_contact_firstname', true);
    $lastname  = get_post_meta($post->ID, '_job_contact_lastname', true);
    $email     = get_post_meta($post->ID, '_job_contact_email', true);
    ?>
    <div class="dr-meta-field">
        <label for="dr_contact_firstname">Voornaam contactpersoon</label>
        <input type="text" id="dr_contact_firstname" name="dr_contact_firstname"
               value="<?php echo esc_attr($firstname); ?>" placeholder="Voornaam">
    </div>
    <div class="dr-meta-field">
        <label for="dr_contact_lastname">Achternaam contactpersoon</label>
        <input type="text" id="dr_contact_lastname" name="dr_contact_lastname"
               value="<?php echo esc_attr($lastname); ?>" placeholder="Achternaam">
    </div>
    <div class="dr-meta-field">
        <label for="dr_contact_email">E-mailadres contactpersoon</label>
        <input type="email" id="dr_contact_email" name="dr_contact_email"
               value="<?php echo esc_attr($email); ?>" placeholder="naam@bedrijf.nl">
    </div>
    <?php
}

/* ── Opslaan ─────────────────────────────────────────────── */
add_action('save_post_job_listing', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (
        isset($_POST['dr_job_extra_info_nonce']) &&
        wp_verify_nonce($_POST['dr_job_extra_info_nonce'], 'dr_job_extra_info_save')
    ) {
        update_post_meta($post_id, '_job_salary_range',    sanitize_text_field($_POST['dr_salary_range']    ?? ''));
        update_post_meta($post_id, '_job_hours_per_week',  sanitize_text_field($_POST['dr_hours_per_week']  ?? ''));
    }

    if (
        isset($_POST['dr_job_contact_nonce']) &&
        wp_verify_nonce($_POST['dr_job_contact_nonce'], 'dr_job_contact_save')
    ) {
        update_post_meta($post_id, '_job_contact_firstname', sanitize_text_field($_POST['dr_contact_firstname'] ?? ''));
        update_post_meta($post_id, '_job_contact_lastname',  sanitize_text_field($_POST['dr_contact_lastname']  ?? ''));
        update_post_meta($post_id, '_job_contact_email',     sanitize_email($_POST['dr_contact_email']          ?? ''));
    }
});
