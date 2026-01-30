<?php
if (!defined('ABSPATH')) exit;

require_once VP_PLUGIN_DIR . 'includes/helpers.php';
require_once VP_PLUGIN_DIR . 'includes/class-vp-post-type.php';
require_once VP_PLUGIN_DIR . 'includes/class-vp-taxonomies.php';
require_once VP_PLUGIN_DIR . 'includes/class-vp-shortcodes.php';
require_once VP_PLUGIN_DIR . 'includes/class-vp-ajax.php';
require_once VP_PLUGIN_DIR . 'includes/class-vp-schema.php';
require_once VP_PLUGIN_DIR . 'includes/class-vp-templates.php';
require_once VP_PLUGIN_DIR . 'includes/class-vp-settings.php';

final class VP_Plugin {
  private static $instance = null;

  public static function instance() {
    if (self::$instance === null) self::$instance = new self();
    return self::$instance;
  }

  private function __construct() {
    add_action('init', [ 'VP_Post_Type', 'register' ]);
    add_action('init', [ 'VP_Taxonomies', 'register' ]);
    add_action('init', [ 'VP_Shortcodes', 'register' ]);
    add_action('init', [ 'VP_AJAX', 'register' ]);
    add_action('init', [ 'VP_Templates', 'register' ]); // ✅ toevoegen
    add_action('wp',  [ 'VP_Schema', 'hook' ]);
    add_action('init', [ 'VP_Settings', 'register' ]); // of admin-only

    add_action('wp_enqueue_scripts', [ $this, 'enqueue_frontend' ]);
    add_action('admin_enqueue_scripts', [ $this, 'enqueue_admin' ]);

    add_action('add_meta_boxes', [ $this, 'register_metaboxes' ]);
    add_action('save_post_vp_vacature', [ $this, 'save_metaboxes' ], 10, 2);
  }

  public function enqueue_frontend() {
    wp_register_style('vp-frontend', VP_PLUGIN_URL . 'assets/css/frontend.css', [], VP_VERSION);
    wp_register_script('vp-frontend', VP_PLUGIN_URL . 'assets/js/frontend.js', [], VP_VERSION, true);

    wp_localize_script('vp-frontend', 'VP_JOBS', [
      'ajaxurl' => admin_url('admin-ajax.php'),
      'nonce'   => wp_create_nonce('vp_jobs_nonce'),
    ]);
  }

  public function enqueue_admin() {
    // optioneel later
  }

  public function register_metaboxes() {
    add_meta_box(
      'vp_vacature_details',
      __('Vacature details', 'vacature-plugin'),
      [ $this, 'render_metabox' ],
      'vp_vacature',
      'normal',
      'high'
    );
  }

  public function render_metabox($post) {
    wp_nonce_field('vp_save_vacature_meta', 'vp_vacature_meta_nonce');

    $meta = [
      'company_name' => get_post_meta($post->ID, '_vp_company_name', true),
      'company_url'  => get_post_meta($post->ID, '_vp_company_url', true),
      'apply_url'    => get_post_meta($post->ID, '_vp_apply_url', true),
      'apply_email'  => get_post_meta($post->ID, '_vp_apply_email', true),
      'location'     => get_post_meta($post->ID, '_vp_location', true),
      'salary'       => get_post_meta($post->ID, '_vp_salary', true),
      'valid_through'=> get_post_meta($post->ID, '_vp_valid_through', true),
    ];
    ?>
    <style>
      .vp-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
      .vp-field label{display:block;font-weight:600;margin:0 0 6px}
      .vp-field input{width:100%}
      @media(max-width:960px){.vp-grid{grid-template-columns:1fr}}
      .vp-help{color:#666;font-size:12px;margin-top:6px}
    </style>

    <div class="vp-grid">
      <div class="vp-field">
        <label><?php _e('Bedrijfsnaam (schema)', 'vacature-plugin'); ?></label>
        <input type="text" name="vp_company_name" value="<?php echo esc_attr($meta['company_name']); ?>" />
        <div class="vp-help"><?php _e('Wordt gebruikt in Google for Jobs (hiringOrganization).', 'vacature-plugin'); ?></div>
      </div>

      <div class="vp-field">
        <label><?php _e('Bedrijfswebsite', 'vacature-plugin'); ?></label>
        <input type="url" name="vp_company_url" value="<?php echo esc_attr($meta['company_url']); ?>" />
      </div>

      <div class="vp-field">
        <label><?php _e('Sollicitatie URL', 'vacature-plugin'); ?></label>
        <input type="url" name="vp_apply_url" value="<?php echo esc_attr($meta['apply_url']); ?>" />
        <div class="vp-help"><?php _e('Laat leeg als je alleen e-mail wilt gebruiken.', 'vacature-plugin'); ?></div>
      </div>

      <div class="vp-field">
        <label><?php _e('Sollicitatie e-mail', 'vacature-plugin'); ?></label>
        <input type="email" name="vp_apply_email" value="<?php echo esc_attr($meta['apply_email']); ?>" />
      </div>

      <div class="vp-field">
        <label><?php _e('Locatie (stad)', 'vacature-plugin'); ?></label>
        <input type="text" name="vp_location" value="<?php echo esc_attr($meta['location']); ?>" />
      </div>

      <div class="vp-field">
        <label><?php _e('Salaris (tekst)', 'vacature-plugin'); ?></label>
        <input type="text" name="vp_salary" value="<?php echo esc_attr($meta['salary']); ?>" placeholder="€3.500 - €4.500" />
      </div>

      <div class="vp-field">
        <label><?php _e('Geldig t/m (YYYY-MM-DD)', 'vacature-plugin'); ?></label>
        <input type="text" name="vp_valid_through" value="<?php echo esc_attr($meta['valid_through']); ?>" placeholder="2026-12-31" />
      </div>
    </div>
    <?php
  }

  public function save_metaboxes($post_id, $post) {
    if (!isset($_POST['vp_vacature_meta_nonce']) || !wp_verify_nonce($_POST['vp_vacature_meta_nonce'], 'vp_save_vacature_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $map = [
      'vp_company_name'  => '_vp_company_name',
      'vp_company_url'   => '_vp_company_url',
      'vp_apply_url'     => '_vp_apply_url',
      'vp_apply_email'   => '_vp_apply_email',
      'vp_location'      => '_vp_location',
      'vp_salary'        => '_vp_salary',
      'vp_valid_through' => '_vp_valid_through',
    ];

    foreach ($map as $field => $key) {
      $val = isset($_POST[$field]) ? wp_unslash($_POST[$field]) : '';
      $val = is_string($val) ? trim($val) : '';
      update_post_meta($post_id, $key, sanitize_text_field($val));
    }
  }
}