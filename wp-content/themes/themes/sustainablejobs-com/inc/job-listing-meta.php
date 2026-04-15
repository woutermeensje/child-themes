<?php
if (!defined('ABSPATH')) exit;

/* ============================================================
   Extra meta fields for job_listing posts
   - Salary range, hours per week, contact person
============================================================ */

add_action('add_meta_boxes', function () {
    add_meta_box(
        'sc_job_extra_info',
        'Job details',
        'sc_job_extra_info_meta_box',
        'job_listing',
        'normal',
        'high'
    );
    add_meta_box(
        'sc_job_contact',
        'Contact person',
        'sc_job_contact_meta_box',
        'job_listing',
        'normal',
        'default'
    );
});

/* ── Job details meta box ───────────────────────────── */
function sc_job_extra_info_meta_box($post) {
    wp_nonce_field('sc_job_extra_info_save', 'sc_job_extra_info_nonce');
    $salary = get_post_meta($post->ID, '_job_salary_range', true);
    $hours  = get_post_meta($post->ID, '_job_hours_per_week', true);
    ?>
    <style>
    .sj-meta-field { margin-bottom: 14px; }
    .sj-meta-field label { display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px; color: #374151; }
    .sj-meta-field input, .sj-meta-field select { width: 100%; padding: 7px 10px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px; }
    </style>
    <div class="sj-meta-field">
        <label for="sc_salary_range">Salary range</label>
        <input type="text" id="sc_salary_range" name="sc_salary_range"
               value="<?php echo esc_attr($salary); ?>" placeholder="E.g. €3,000 – €4,500 per month">
    </div>
    <div class="sj-meta-field">
        <label for="sc_hours_per_week">Hours per week</label>
        <input type="text" id="sc_hours_per_week" name="sc_hours_per_week"
               value="<?php echo esc_attr($hours); ?>" placeholder="E.g. 32–40 hours">
    </div>
    <?php
}

/* ── Contact person meta box ────────────────────────── */
function sc_job_contact_meta_box($post) {
    wp_nonce_field('sc_job_contact_save', 'sc_job_contact_nonce');
    $firstname = get_post_meta($post->ID, '_job_contact_firstname', true);
    $lastname  = get_post_meta($post->ID, '_job_contact_lastname', true);
    $email     = get_post_meta($post->ID, '_job_contact_email', true);
    ?>
    <div class="sj-meta-field">
        <label for="sc_contact_firstname">First name</label>
        <input type="text" id="sc_contact_firstname" name="sc_contact_firstname"
               value="<?php echo esc_attr($firstname); ?>" placeholder="First name">
    </div>
    <div class="sj-meta-field">
        <label for="sc_contact_lastname">Last name</label>
        <input type="text" id="sc_contact_lastname" name="sc_contact_lastname"
               value="<?php echo esc_attr($lastname); ?>" placeholder="Last name">
    </div>
    <div class="sj-meta-field">
        <label for="sc_contact_email">Email address</label>
        <input type="email" id="sc_contact_email" name="sc_contact_email"
               value="<?php echo esc_attr($email); ?>" placeholder="name@company.com">
    </div>
    <?php
}

/* ── Save ───────────────────────────────────────────── */
add_action('save_post_job_listing', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (
        isset($_POST['sc_job_extra_info_nonce']) &&
        wp_verify_nonce($_POST['sc_job_extra_info_nonce'], 'sc_job_extra_info_save')
    ) {
        update_post_meta($post_id, '_job_salary_range',   sanitize_text_field($_POST['sc_salary_range']    ?? ''));
        update_post_meta($post_id, '_job_hours_per_week', sanitize_text_field($_POST['sc_hours_per_week']  ?? ''));
    }

    if (
        isset($_POST['sc_job_contact_nonce']) &&
        wp_verify_nonce($_POST['sc_job_contact_nonce'], 'sc_job_contact_save')
    ) {
        update_post_meta($post_id, '_job_contact_firstname', sanitize_text_field($_POST['sc_contact_firstname'] ?? ''));
        update_post_meta($post_id, '_job_contact_lastname',  sanitize_text_field($_POST['sc_contact_lastname']  ?? ''));
        update_post_meta($post_id, '_job_contact_email',     sanitize_email($_POST['sc_contact_email']          ?? ''));
    }
});
