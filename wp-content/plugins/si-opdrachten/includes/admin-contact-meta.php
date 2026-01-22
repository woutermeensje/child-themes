<?php
if (!defined('ABSPATH')) exit;

/**
 * Metabox voor contactpersoon op si_opdracht
 */
add_action('add_meta_boxes', function () {
  add_meta_box(
    'si_opd_contact_metabox',
    __('Gegevens contactpersoon', 'si-opdrachten'),
    'si_opd_render_contact_metabox',
    'si_opdracht',
    'side',
    'default'
  );
});

function si_opd_render_contact_metabox($post){
  wp_nonce_field('si_opd_save_contact_meta', 'si_opd_contact_nonce');

  $first = get_post_meta($post->ID, '_si_contact_first_name', true);
  $last  = get_post_meta($post->ID, '_si_contact_last_name', true);
  $email = get_post_meta($post->ID, '_si_contact_email', true);
  ?>
  <p style="margin:0 0 10px;">
    <label for="si_contact_first_name" style="font-weight:600; display:block; margin-bottom:4px;">
      Voornaam contactpersoon
    </label>
    <input
      type="text"
      id="si_contact_first_name"
      name="si_contact_first_name"
      value="<?php echo esc_attr($first); ?>"
      style="width:100%;"
    />
  </p>

  <p style="margin:0 0 10px;">
    <label for="si_contact_last_name" style="font-weight:600; display:block; margin-bottom:4px;">
      Achternaam contactpersoon
    </label>
    <input
      type="text"
      id="si_contact_last_name"
      name="si_contact_last_name"
      value="<?php echo esc_attr($last); ?>"
      style="width:100%;"
    />
  </p>

  <p style="margin:0;">
    <label for="si_contact_email" style="font-weight:600; display:block; margin-bottom:4px;">
      E-mailadres contactpersoon
    </label>
    <input
      type="email"
      id="si_contact_email"
      name="si_contact_email"
      value="<?php echo esc_attr($email); ?>"
      style="width:100%;"
    />
  </p>
  <?php
}

/**
 * Opslaan meta velden
 */
add_action('save_post_si_opdracht', function($post_id){
  // Autosave / permissions
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!current_user_can('edit_post', $post_id)) return;

  // Nonce check
  if (!isset($_POST['si_opd_contact_nonce']) || !wp_verify_nonce($_POST['si_opd_contact_nonce'], 'si_opd_save_contact_meta')) {
    return;
  }

  // Sanitize & save
  $first = isset($_POST['si_contact_first_name']) ? sanitize_text_field($_POST['si_contact_first_name']) : '';
  $last  = isset($_POST['si_contact_last_name']) ? sanitize_text_field($_POST['si_contact_last_name']) : '';
  $email = isset($_POST['si_contact_email']) ? sanitize_email($_POST['si_contact_email']) : '';

  update_post_meta($post_id, '_si_contact_first_name', $first);
  update_post_meta($post_id, '_si_contact_last_name', $last);
  update_post_meta($post_id, '_si_contact_email', $email);
});
