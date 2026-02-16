<?php
if (!defined('ABSPATH')) exit;

class OB_Post_Type {
  // ✅ <= 20 chars to avoid WordPress notice
  const CPT = 'ob_bericht';

  /**
   * Call this on init, e.g. add_action('init', ['OB_Post_Type','register']);
   * This method also hooks metabox & save once.
   */
  public static function register() {

    register_post_type(self::CPT, [
      'labels' => [
        'name'          => __('Overlijdensberichten', 'overlijdensberichten-plugin'),
        'singular_name' => __('Overlijdensbericht', 'overlijdensberichten-plugin'),
        'add_new_item'  => __('Nieuw overlijdensbericht', 'overlijdensberichten-plugin'),
        'edit_item'     => __('Overlijdensbericht bewerken', 'overlijdensberichten-plugin'),
      ],
      'public'        => true,
      'has_archive'   => true,
      'rewrite'       => [ 'slug' => 'overlijdensberichten' ],
      'menu_icon'     => 'dashicons-heart',
      'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail', 'author' ],
      'show_in_rest'  => true,

      // ✅ helpt WP om taxo-metaboxen te tonen in de editor
      'taxonomies' => [
        OB_Taxonomies::TAX_PROV,
        OB_Taxonomies::TAX_CITY,
        OB_Taxonomies::TAX_TYPE,
      ],
    ]);

    /**
     * ⚠️ Belangrijk: hooks 1x toevoegen
     * register() wordt op init maar 1x aangeroepen, dus dit is veilig.
     */
    add_action('add_meta_boxes', [__CLASS__, 'add_metaboxes']);
    add_action('save_post_' . self::CPT, [__CLASS__, 'save_metabox'], 10, 2);
  }

  public static function add_metaboxes() {
    add_meta_box(
      'ob_details',
      __('Overlijdensbericht details', 'overlijdensberichten-plugin'),
      [__CLASS__, 'render_metabox'],
      self::CPT,
      'normal',
      'high'
    );
  }

  public static function render_metabox($post) {
    wp_nonce_field('ob_save_details', 'ob_details_nonce');

    $get = function($key) use ($post) {
      return (string) get_post_meta($post->ID, $key, true);
    };

    $fields = [
      'uitvaartondernemer_naam'    => $get('uitvaartondernemer_naam'),
      'uitvaartondernemer_contact' => $get('uitvaartondernemer_contact'),
      'uitvaartondernemer_tel'     => $get('uitvaartondernemer_tel'),
      'uitvaartondernemer_email'   => $get('uitvaartondernemer_email'),
      'uitvaartondernemer_url'     => $get('uitvaartondernemer_url'),

      'ob_nok_first'               => $get('ob_nok_first'),
      'ob_nok_last'                => $get('ob_nok_last'),
      'ob_nok_email'               => $get('ob_nok_email'),

      'ob_born_date'               => $get('ob_born_date'),
      'ob_died_date'               => $get('ob_died_date'),

      'condoleer_url'              => $get('condoleer_url'),
    ];
    ?>
    <style>
      .ob-meta-section{margin-top:18px;padding-top:10px;border-top:1px solid #eee}
      .ob-meta-section:first-child{margin-top:0;padding-top:0;border-top:none}
      .ob-meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:10px}
      .ob-meta-field label{display:block;font-weight:600;margin:0 0 6px}
      .ob-meta-field input{width:100%;padding:10px;border:1px solid #ccd0d4;border-radius:6px}
      .ob-meta-help{margin:6px 0 0;color:#666;font-size:12px}
      @media (max-width: 900px){ .ob-meta-grid{grid-template-columns:1fr} }
    </style>

    <div class="ob-meta-section">
      <h4><?php _e('Gegevens van de uitvaartondernemer', 'overlijdensberichten-plugin'); ?></h4>
      <div class="ob-meta-grid">
        <div class="ob-meta-field">
          <label>Naam</label>
          <input type="text" name="uitvaartondernemer_naam" value="<?php echo esc_attr($fields['uitvaartondernemer_naam']); ?>">
        </div>
        <div class="ob-meta-field">
          <label>Contact</label>
          <input type="text" name="uitvaartondernemer_contact" value="<?php echo esc_attr($fields['uitvaartondernemer_contact']); ?>">
        </div>
        <div class="ob-meta-field">
          <label>Telefoon</label>
          <input type="text" name="uitvaartondernemer_tel" value="<?php echo esc_attr($fields['uitvaartondernemer_tel']); ?>">
        </div>
        <div class="ob-meta-field">
          <label>E-mail</label>
          <input type="email" name="uitvaartondernemer_email" value="<?php echo esc_attr($fields['uitvaartondernemer_email']); ?>">
        </div>
        <div class="ob-meta-field">
          <label>Contact URL (optioneel)</label>
          <input type="url" name="uitvaartondernemer_url" value="<?php echo esc_attr($fields['uitvaartondernemer_url']); ?>">
          <p class="ob-meta-help">Bijv. link naar contactpagina van de uitvaartondernemer.</p>
        </div>
      </div>
    </div>

    <div class="ob-meta-section">
      <h4><?php _e('Contactgegevens van de nabestaanden', 'overlijdensberichten-plugin'); ?></h4>
      <div class="ob-meta-grid">
        <div class="ob-meta-field">
          <label>Voornaam</label>
          <input type="text" name="ob_nok_first" value="<?php echo esc_attr($fields['ob_nok_first']); ?>">
        </div>
        <div class="ob-meta-field">
          <label>Achternaam</label>
          <input type="text" name="ob_nok_last" value="<?php echo esc_attr($fields['ob_nok_last']); ?>">
        </div>
        <div class="ob-meta-field">
          <label>E-mailadres</label>
          <input type="email" name="ob_nok_email" value="<?php echo esc_attr($fields['ob_nok_email']); ?>">
          <p class="ob-meta-help">Hierop kan een notificatie komen bij een nieuwe condoleance.</p>
        </div>
      </div>
    </div>

    <div class="ob-meta-section">
      <h4><?php _e('Data', 'overlijdensberichten-plugin'); ?></h4>
      <div class="ob-meta-grid">
        <div class="ob-meta-field">
          <label>Geboren (datum)</label>
          <input type="date" name="ob_born_date" value="<?php echo esc_attr($fields['ob_born_date']); ?>">
        </div>
        <div class="ob-meta-field">
          <label>Gestorven (datum)</label>
          <input type="date" name="ob_died_date" value="<?php echo esc_attr($fields['ob_died_date']); ?>">
        </div>
      </div>
    </div>

    <div class="ob-meta-section">
      <h4><?php _e('Condoleren', 'overlijdensberichten-plugin'); ?></h4>
      <div class="ob-meta-grid">
        <div class="ob-meta-field">
          <label>Condoleer URL (optioneel)</label>
          <input type="url" name="condoleer_url" value="<?php echo esc_attr($fields['condoleer_url']); ?>">
          <p class="ob-meta-help">Laat leeg als je het formulier op dezelfde pagina gebruikt.</p>
        </div>
      </div>
    </div>
    <?php
  }

  public static function save_metabox($post_id, $post) {
    if (!isset($_POST['ob_details_nonce']) || !wp_verify_nonce($_POST['ob_details_nonce'], 'ob_save_details')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (!$post || $post->post_type !== self::CPT) return;

    $map = [
      'uitvaartondernemer_naam'    => 'sanitize_text_field',
      'uitvaartondernemer_contact' => 'sanitize_text_field',
      'uitvaartondernemer_tel'     => 'sanitize_text_field',
      'uitvaartondernemer_email'   => 'sanitize_email',
      'uitvaartondernemer_url'     => 'esc_url_raw',

      'ob_nok_first'               => 'sanitize_text_field',
      'ob_nok_last'                => 'sanitize_text_field',
      'ob_nok_email'               => 'sanitize_email',

      'ob_born_date'               => 'sanitize_text_field',
      'ob_died_date'               => 'sanitize_text_field',

      'condoleer_url'              => 'esc_url_raw',
    ];

    foreach ($map as $key => $san) {
      $val = isset($_POST[$key]) ? call_user_func($san, wp_unslash($_POST[$key])) : '';
      if ($val === '') {
        delete_post_meta($post_id, $key);
      } else {
        update_post_meta($post_id, $key, $val);
      }
    }
  }
}
