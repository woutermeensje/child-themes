<?php
if (!defined('ABSPATH')) exit;

/*
 * Voegt een "Uitgelichte Werkgever" vinkje toe aan job_company taxonomy terms.
 * De meta-sleutel _uitgelichte_werkgever (1/0) wordt opgeslagen als term meta.
 * Vacatures van uitgelichte werkgevers krijgen de class job-card--featured-employer,
 * die een 3px primaire border toekent.
 */

/* ── Admin: veld in "Term bewerken" scherm ───────────────────── */
add_action('job_company_edit_form_fields', function (WP_Term $term) {
    $featured  = get_term_meta($term->term_id, '_uitgelichte_werkgever', true);
    $recruiter = get_term_meta($term->term_id, '_recruitment_partner', true);
    ?>
    <tr class="form-field">
        <th scope="row">
            <label for="sj_uitgelichte_werkgever">Uitgelichte Werkgever</label>
        </th>
        <td>
            <?php wp_nonce_field('sj_save_uitgelichte_werkgever', 'sj_uitgelichte_werkgever_nonce'); ?>
            <label>
                <input type="checkbox" name="sj_uitgelichte_werkgever" id="sj_uitgelichte_werkgever" value="1"
                    <?php checked('1', $featured); ?>>
                Markeer dit bedrijf als uitgelichte werkgever
            </label>
            <p class="description">Vacatures van dit bedrijf krijgen een speciale randkleur in de vacaturelijst.</p>
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
                Markeer dit bedrijf als recruitment partner
            </label>
            <p class="description">Vacatures van dit bedrijf krijgen het label "Recruitment" in de vacaturelijst.</p>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row">
            <label for="sj_activisme">Activisme</label>
        </th>
        <td>
            <label>
                <input type="checkbox" name="sj_activisme" id="sj_activisme" value="1"
                    <?php checked('1', get_term_meta($term->term_id, '_activisme', true)); ?>>
                Markeer dit bedrijf als activisme organisatie
            </label>
            <p class="description">Vacatures van dit bedrijf krijgen een groene randkleur in de vacaturelijst.</p>
        </td>
    </tr>
    <?php
});

/* ── Admin: veld in "Term toevoegen" scherm ─────────────────── */
add_action('job_company_add_form_fields', function () {
    ?>
    <div class="form-field">
        <label for="sj_uitgelichte_werkgever">
            <input type="checkbox" name="sj_uitgelichte_werkgever" id="sj_uitgelichte_werkgever" value="1">
            Uitgelichte Werkgever
        </label>
        <p>Vacatures van dit bedrijf krijgen een speciale randkleur in de vacaturelijst.</p>
    </div>
    <div class="form-field">
        <label for="sj_recruitment_partner">
            <input type="checkbox" name="sj_recruitment_partner" id="sj_recruitment_partner" value="1">
            Recruitment Partner
        </label>
        <p>Vacatures van dit bedrijf krijgen het label "Recruitment" in de vacaturelijst.</p>
    </div>
    <div class="form-field">
        <label for="sj_activisme">
            <input type="checkbox" name="sj_activisme" id="sj_activisme" value="1">
            Activisme
        </label>
        <p>Vacatures van dit bedrijf krijgen een groene randkleur in de vacaturelijst.</p>
    </div>
    <?php
});

/* ── Opslaan ────────────────────────────────────────────────── */
add_action('edited_job_company', 'sj_save_uitgelichte_werkgever_meta');
add_action('created_job_company', 'sj_save_uitgelichte_werkgever_meta');

function sj_save_uitgelichte_werkgever_meta(int $term_id): void {
    if (
        !isset($_POST['sj_uitgelichte_werkgever_nonce']) ||
        !wp_verify_nonce($_POST['sj_uitgelichte_werkgever_nonce'], 'sj_save_uitgelichte_werkgever')
    ) {
        return;
    }

    $value = isset($_POST['sj_uitgelichte_werkgever']) ? '1' : '0';
    update_term_meta($term_id, '_uitgelichte_werkgever', $value);

    $recruiter = isset($_POST['sj_recruitment_partner']) ? '1' : '0';
    update_term_meta($term_id, '_recruitment_partner', $recruiter);

    $activisme = isset($_POST['sj_activisme']) ? '1' : '0';
    update_term_meta($term_id, '_activisme', $activisme);
}

/* ── Helper: is dit bedrijf een activisme organisatie? ──────── */
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
        if ('1' === get_term_meta($term->term_id, '_uitgelichte_werkgever', true)) {
            return true;
        }
    }
    return false;
}
