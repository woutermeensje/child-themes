<?php
/**
 * Plugin Name: Vacature Plugin
 * Description: Eigen vacature plugin met filters, listings, single vacatures, custom taxonomieën en Google for Jobs schema.
 * Version: 1.0.0
 * Author: Sustainable Recruitment Marketing B.V.
 * Text Domain: vacature-plugin
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit;

define('VP_VERSION', '1.0.0');
define('VP_PLUGIN_FILE', __FILE__);
define('VP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VP_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once VP_PLUGIN_DIR . 'includes/class-vp-plugin.php';

register_activation_hook(__FILE__, function () {
  require_once VP_PLUGIN_DIR . 'includes/class-vp-post-type.php';
  require_once VP_PLUGIN_DIR . 'includes/class-vp-taxonomies.php';

  VP_Post_Type::register();
  VP_Taxonomies::register();

  flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function () {
  flush_rewrite_rules();
});

add_action('plugins_loaded', function () {
  VP_Plugin::instance();
});


// ==============================
// Admin metabox: Logo thumbnail
// Opslaat attachment ID in _vp_logo_id
// ==============================

add_action('add_meta_boxes', function () {
  add_meta_box(
    'vpjobs_logo_metabox',
    'Logo thumbnail',
    'vpjobs_logo_metabox_render',
    'vacature', // <-- CPT slug (waarschijnlijk 'vacature')
    'side',
    'default'
  );
});

function vpjobs_logo_metabox_render($post){
  wp_nonce_field('vpjobs_logo_save', 'vpjobs_logo_nonce');

  $logo_id = (int) get_post_meta($post->ID, '_vp_logo_id', true);
  $img = $logo_id ? wp_get_attachment_image($logo_id, 'thumbnail', false, ['style'=>'max-width:100%;height:auto;']) : '';

  echo '<div id="vpjobs-logo-preview" style="margin-bottom:10px;">' . ($img ?: '<em>Geen logo gekozen.</em>') . '</div>';
  echo '<input type="hidden" id="vpjobs_logo_id" name="vpjobs_logo_id" value="' . esc_attr($logo_id) . '">';

  echo '<button type="button" class="button" id="vpjobs-logo-pick">Kies logo</button> ';
  echo '<button type="button" class="button" id="vpjobs-logo-remove" style="margin-left:6px;">Verwijder</button>';
  ?>
  <script>
  (function($){
    let frame;

    $('#vpjobs-logo-pick').on('click', function(e){
      e.preventDefault();
      if(frame){ frame.open(); return; }

      frame = wp.media({
        title: 'Kies logo thumbnail',
        button: { text: 'Gebruik als logo' },
        library: { type: 'image' },
        multiple: false
      });

      frame.on('select', function(){
        const att = frame.state().get('selection').first().toJSON();
        $('#vpjobs_logo_id').val(att.id);
        const url = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
        $('#vpjobs-logo-preview').html('<img src="'+url+'" style="max-width:100%;height:auto;" />');
      });

      frame.open();
    });

    $('#vpjobs-logo-remove').on('click', function(e){
      e.preventDefault();
      $('#vpjobs_logo_id').val('');
      $('#vpjobs-logo-preview').html('<em>Geen logo gekozen.</em>');
    });
  })(jQuery);
  </script>
  <?php
}

add_action('admin_enqueue_scripts', function(){
  // nodig voor wp.media()
  wp_enqueue_media();
});

add_action('save_post_vacature', function($post_id){
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!isset($_POST['vpjobs_logo_nonce']) || !wp_verify_nonce($_POST['vpjobs_logo_nonce'], 'vpjobs_logo_save')) return;

  // (optioneel) capability check
  if (!current_user_can('edit_post', $post_id)) return;

  $logo_id = isset($_POST['vpjobs_logo_id']) ? (int) $_POST['vpjobs_logo_id'] : 0;

  if ($logo_id) update_post_meta($post_id, '_vp_logo_id', $logo_id);
  else delete_post_meta($post_id, '_vp_logo_id');
});
