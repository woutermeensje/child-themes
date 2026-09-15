<?php
if (!defined('ABSPATH')) exit;

/*
 * Voegt een optie toe aan job_company taxonomy terms waarmee een organisatie
 * kan instellen dat het "Stel een vraag" formulier op al haar vacatures naar
 * één vast e-mailadres wordt gestuurd, in plaats van naar het contactpersoon
 * e-mailadres per vacature.
 * Meta: _fondsen_centraal_contact_email_enabled (1/0), _fondsen_centraal_contact_email.
 */

/* ── Admin: veld in "Term bewerken" scherm ───────────────────── */
add_action('job_company_edit_form_fields', function (WP_Term $term) {
    $enabled = get_term_meta($term->term_id, '_fondsen_centraal_contact_email_enabled', true);
    $email   = get_term_meta($term->term_id, '_fondsen_centraal_contact_email', true);
    ?>
    <tr class="form-field">
        <th scope="row">
            <label for="fondsen_centraal_contact_email_enabled">Centraal contact e-mailadres</label>
        </th>
        <td>
            <?php wp_nonce_field('fondsen_save_centraal_contact_email', 'fondsen_centraal_contact_email_nonce'); ?>
            <label>
                <input type="checkbox" name="fondsen_centraal_contact_email_enabled" id="fondsen_centraal_contact_email_enabled" value="1"
                    <?php checked('1', $enabled); ?>>
                Stuur vragen over alle vacatures van dit bedrijf naar één vast e-mailadres
            </label>
            <p>
                <input type="email" name="fondsen_centraal_contact_email" id="fondsen_centraal_contact_email" class="regular-text" value="<?php echo esc_attr($email); ?>" placeholder="contact@bedrijf.nl">
            </p>
            <p class="description">Als dit is aangevinkt en ingevuld, gaat het "Stel een vraag" formulier op elke vacature van dit bedrijf altijd naar dit e-mailadres, in plaats van het contactpersoon e-mailadres van de losse vacature.</p>
        </td>
    </tr>
    <?php
});

/* ── Admin: veld in "Term toevoegen" scherm ─────────────────── */
add_action('job_company_add_form_fields', function () {
    ?>
    <div class="form-field">
        <label for="fondsen_centraal_contact_email_enabled">
            <input type="checkbox" name="fondsen_centraal_contact_email_enabled" id="fondsen_centraal_contact_email_enabled" value="1">
            Stuur vragen over alle vacatures van dit bedrijf naar één vast e-mailadres
        </label>
        <p>
            <input type="email" name="fondsen_centraal_contact_email" id="fondsen_centraal_contact_email" placeholder="contact@bedrijf.nl">
        </p>
        <p>Als dit is aangevinkt en ingevuld, gaat het "Stel een vraag" formulier op elke vacature van dit bedrijf altijd naar dit e-mailadres.</p>
    </div>
    <?php
});

/* ── Opslaan ────────────────────────────────────────────────── */
add_action('edited_job_company', 'fondsen_save_centraal_contact_email_meta');
add_action('created_job_company', 'fondsen_save_centraal_contact_email_meta');

function fondsen_save_centraal_contact_email_meta(int $term_id): void {
    if (
        !isset($_POST['fondsen_centraal_contact_email_nonce']) ||
        !wp_verify_nonce($_POST['fondsen_centraal_contact_email_nonce'], 'fondsen_save_centraal_contact_email')
    ) {
        return;
    }

    $enabled = isset($_POST['fondsen_centraal_contact_email_enabled']) ? '1' : '0';
    update_term_meta($term_id, '_fondsen_centraal_contact_email_enabled', $enabled);

    $email = sanitize_email(wp_unslash($_POST['fondsen_centraal_contact_email'] ?? ''));
    if ($email && is_email($email)) {
        update_term_meta($term_id, '_fondsen_centraal_contact_email', $email);
    } else {
        delete_term_meta($term_id, '_fondsen_centraal_contact_email');
    }
}

/* ── Helper: centraal contact e-mailadres voor de vacature (indien actief) ── */
function fondsen_get_job_company_central_contact_email(int $post_id): string {
    $terms = get_the_terms($post_id, 'job_company');
    if (empty($terms) || is_wp_error($terms)) {
        return '';
    }

    foreach ($terms as $term) {
        $enabled = get_term_meta($term->term_id, '_fondsen_centraal_contact_email_enabled', true);
        if ('1' !== $enabled) {
            continue;
        }

        $email = sanitize_email(get_term_meta($term->term_id, '_fondsen_centraal_contact_email', true));
        if ($email && is_email($email)) {
            return $email;
        }
    }

    return '';
}
