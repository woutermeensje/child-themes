<?php
if (!defined('ABSPATH')) exit;

class VP_Settings {
  const OPTION_KEY = 'vp_settings';

  public static function register() {
    add_action('admin_menu', [__CLASS__, 'menu']);
    add_action('admin_init', [__CLASS__, 'register_settings']);
  }

  public static function menu() {
    add_options_page(
      __('Vacature Plugin', 'vacature-plugin'),
      __('Vacature Plugin', 'vacature-plugin'),
      'manage_options',
      'vp-settings',
      [__CLASS__, 'render_page']
    );
  }

  public static function register_settings() {
    register_setting(self::OPTION_KEY, self::OPTION_KEY, [
      'type' => 'array',
      'sanitize_callback' => [__CLASS__, 'sanitize'],
      'default' => [],
    ]);

    add_settings_section(
      'vp_texts',
      __('Teksten & knoppen', 'vacature-plugin'),
      function () {
        echo '<p>' . esc_html__('Stel teksten in die per website kunnen verschillen (Recruiternext/OnlineMarketingjobs).', 'vacature-plugin') . '</p>';
      },
      'vp-settings'
    );

    add_settings_field(
      'listings_heading',
      __('Heading boven listings', 'vacature-plugin'),
      [__CLASS__, 'field_text'],
      'vp-settings',
      'vp_texts',
      [
        'key' => 'listings_heading',
        'placeholder' => 'Doorzoek alle vacatures van Recruiternext.nl',
      ]
    );

    add_settings_field(
      'reset_button_text',
      __('Tekst reset knop', 'vacature-plugin'),
      [__CLASS__, 'field_text'],
      'vp-settings',
      'vp_texts',
      [
        'key' => 'reset_button_text',
        'placeholder' => 'Wis alles',
      ]
    );
  }

  public static function sanitize($input) {
    $out = [];
    $out['listings_heading']  = isset($input['listings_heading']) ? sanitize_text_field($input['listings_heading']) : '';
    $out['reset_button_text'] = isset($input['reset_button_text']) ? sanitize_text_field($input['reset_button_text']) : '';
    return $out;
  }

  public static function field_text($args) {
    $key = $args['key'];
    $opt = get_option(self::OPTION_KEY, []);
    $val = isset($opt[$key]) ? $opt[$key] : '';
    $ph  = isset($args['placeholder']) ? $args['placeholder'] : '';
    echo '<input type="text" class="regular-text" name="'.esc_attr(self::OPTION_KEY).'['.esc_attr($key).']" value="'.esc_attr($val).'" placeholder="'.esc_attr($ph).'" />';
  }

  public static function render_page() {
    ?>
    <div class="wrap">
      <h1><?php echo esc_html__('Vacature Plugin', 'vacature-plugin'); ?></h1>
      <form method="post" action="options.php">
        <?php
          settings_fields(self::OPTION_KEY);
          do_settings_sections('vp-settings');
          submit_button();
        ?>
      </form>
    </div>
    <?php
  }
}