<?php
if (!defined('ABSPATH')) exit;

/*
 * Adds Featured Employer flags to job_company taxonomy terms.
 * The _uitgelichte_employer meta key is kept for compatibility with existing data.
 */

/* Admin: edit term fields. */
add_action('job_company_edit_form_fields', function (WP_Term $term) {
    $featured  = get_term_meta($term->term_id, '_uitgelichte_employer', true);
    $recruiter = get_term_meta($term->term_id, '_recruitment_partner', true);
    ?>
    <tr class="form-field">
        <th scope="row">
            <label for="sj_uitgelichte_employer">Featured Employer</label>
        </th>
        <td>
            <?php wp_nonce_field('sj_save_uitgelichte_employer', 'sj_uitgelichte_employer_nonce'); ?>
            <label>
                <input type="checkbox" name="sj_uitgelichte_employer" id="sj_uitgelichte_employer" value="1"
                    <?php checked('1', $featured); ?>>
                Mark this organization as a featured employer
            </label>
            <p class="description">Jobs from this organization receive a special border color in the job list.</p>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row">
            <label for="sj_recruitment_partner">Recruitment Partner</label>
        </th>
        <td>
            <label>
                <input type="checkbox" name="sj_recruitment_partner" id="sj_recruitment_partner" value="1"
                    <?php checked('1', $recruiter); ?>>
                Mark this organization as a recruitment partner
            </label>
            <p class="description">Jobs from this organization receive the "Recruitment" label in the job list.</p>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row">
            <label for="sj_activisme">Activism</label>
        </th>
        <td>
            <label>
                <input type="checkbox" name="sj_activisme" id="sj_activisme" value="1"
                    <?php checked('1', get_term_meta($term->term_id, '_activisme', true)); ?>>
                Mark this organization as an activist organization
            </label>
            <p class="description">Jobs from this organization receive a green border color in the job list.</p>
        </td>
    </tr>
    <?php
});

/* Admin: add term fields. */
add_action('job_company_add_form_fields', function () {
    ?>
    <div class="form-field">
        <label for="sj_uitgelichte_employer">
            <input type="checkbox" name="sj_uitgelichte_employer" id="sj_uitgelichte_employer" value="1">
            Featured Employer
        </label>
        <p>Jobs from this organization receive a special border color in the job list.</p>
    </div>
    <div class="form-field">
        <label for="sj_recruitment_partner">
            <input type="checkbox" name="sj_recruitment_partner" id="sj_recruitment_partner" value="1">
            Recruitment Partner
        </label>
        <p>Jobs from this organization receive the "Recruitment" label in the job list.</p>
    </div>
    <div class="form-field">
        <label for="sj_activisme">
            <input type="checkbox" name="sj_activisme" id="sj_activisme" value="1">
            Activism
        </label>
        <p>Jobs from this organization receive a green border color in the job list.</p>
    </div>
    <?php
});

/* Save. */
add_action('edited_job_company', 'sj_save_uitgelichte_employer_meta');
add_action('created_job_company', 'sj_save_uitgelichte_employer_meta');

function sj_save_uitgelichte_employer_meta(int $term_id): void {
    if (
        !isset($_POST['sj_uitgelichte_employer_nonce']) ||
        !wp_verify_nonce($_POST['sj_uitgelichte_employer_nonce'], 'sj_save_uitgelichte_employer')
    ) {
        return;
    }

    $value = isset($_POST['sj_uitgelichte_employer']) ? '1' : '0';
    update_term_meta($term_id, '_uitgelichte_employer', $value);

    $recruiter = isset($_POST['sj_recruitment_partner']) ? '1' : '0';
    update_term_meta($term_id, '_recruitment_partner', $recruiter);

    $activisme = isset($_POST['sj_activisme']) ? '1' : '0';
    update_term_meta($term_id, '_activisme', $activisme);
}

/* Helper: is this organization an activist organization? */
function sj_is_activisme(int $post_id): bool {
    $terms = get_the_terms($post_id, 'job_company');
    if (empty($terms) || is_wp_error($terms)) {
        return false;
    }
    foreach ($terms as $term) {
        if ('1' === get_term_meta($term->term_id, '_activisme', true)) {
            return true;
        }
    }
    return false;
}

function sj_is_recruitment_partner(int $post_id): bool {
    $terms = get_the_terms($post_id, 'job_company');
    if (empty($terms) || is_wp_error($terms)) {
        return false;
    }
    foreach ($terms as $term) {
        if ('1' === get_term_meta($term->term_id, '_recruitment_partner', true)) {
            return true;
        }
    }
    return false;
}

function sj_is_featured_employer(int $post_id): bool {
    $terms = get_the_terms($post_id, 'job_company');
    if (empty($terms) || is_wp_error($terms)) {
        return false;
    }
    foreach ($terms as $term) {
        if ('1' === get_term_meta($term->term_id, '_uitgelichte_employer', true)) {
            return true;
        }
    }
    return false;
}
