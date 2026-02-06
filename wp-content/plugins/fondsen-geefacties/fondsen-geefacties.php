<?php
/**
 * Plugin Name: Fondsen.org – Geefacties (nieuw)
 * Description: Geefacties (donatieverzoeken) met filter + listing output. (Formulier extern, bijv. Elementor.)
 * Version: 0.1.2
 */

if (!defined('ABSPATH')) exit;

define('FGA_VERSION', '0.1.2');
define('FGA_PATH', plugin_dir_path(__FILE__));
define('FGA_URL', plugin_dir_url(__FILE__));


final class FGA_Plugin {
  const CPT       = 'geefactie';
  const TAX_THEMA = 'geefactie_thema';
  const TAX_TYPE  = 'geefactie_type';
  const META_FIRSTNAME = '_fga_firstname';
  const META_LASTNAME  = '_fga_lastname';
  const META_EMAIL     = '_fga_email';
  const META_BENODIGD_KAPITAAL = '_fga_benodigd_kapitaal';
  const META_STICHTING_NAAM    = '_fga_stichting_naam';



  public function __construct() {
    add_action('init', [$this, 'register_cpt_and_taxonomies']);
    add_action('wp_enqueue_scripts', [$this, 'register_assets']);

    // Zorg dat "Uitgelichte afbeelding" kan werken (ook als theme dit niet aan heeft staan)
    add_action('after_setup_theme', [$this, 'enable_thumbnails_support']);

    // Forceer de Uitgelichte-afbeelding metabox voor geefacties (ook als editor/clean-up plugins dit verbergen)
    add_action('add_meta_boxes', [$this, 'force_featured_image_metabox'], 20, 2);


    // Single template override voor geefactie
    add_filter('single_template', [$this, 'load_single_template']);

    add_action('add_meta_boxes', [$this, 'add_contact_metabox']);
    add_action('save_post_' . self::CPT, [$this, 'save_contact_metabox'], 10, 2); 

    add_action('add_meta_boxes', [$this, 'add_details_metabox']);
    
    add_action('save_post_' . self::CPT, [$this, 'save_details_metabox'], 10, 2);



    // Includes
    require_once FGA_PATH . 'includes/class-fga-query.php';
    require_once FGA_PATH . 'includes/class-fga-shortcode.php';

    // Boot shortcode
    new FGA_Shortcode();
  }

  public function register_assets() {
    wp_register_style(
      'fga-geefacties',
      FGA_URL . 'assets/geefacties.css',
      [],
      FGA_VERSION
    );
  }

  public function enable_thumbnails_support() {
    add_theme_support('post-thumbnails');
    add_post_type_support(self::CPT, 'thumbnail');
  }

 

public function add_contact_metabox() {
  add_meta_box(
    'fga_contact',
    __('Contactgegevens'),
    [$this, 'render_contact_metabox'],
    self::CPT,
    'side',
    'high'
  );
}

public function render_contact_metabox($post) {
  $first = get_post_meta($post->ID, self::META_FIRSTNAME, true);
  $last  = get_post_meta($post->ID, self::META_LASTNAME, true);
  $email = get_post_meta($post->ID, self::META_EMAIL, true);

  wp_nonce_field('fga_contact_save', 'fga_contact_nonce');
  ?>
  <p style="margin:0 0 10px;">
    <label for="fga_firstname" style="display:block;font-weight:600;margin-bottom:4px;">Voornaam</label>
    <input type="text" id="fga_firstname" name="fga_firstname" value="<?php echo esc_attr($first); ?>" style="width:100%;" />
  </p>

  <p style="margin:0 0 10px;">
    <label for="fga_lastname" style="display:block;font-weight:600;margin-bottom:4px;">Achternaam</label>
    <input type="text" id="fga_lastname" name="fga_lastname" value="<?php echo esc_attr($last); ?>" style="width:100%;" />
  </p>

  <p style="margin:0;">
    <label for="fga_email" style="display:block;font-weight:600;margin-bottom:4px;">E-mailadres</label>
    <input type="email" id="fga_email" name="fga_email" value="<?php echo esc_attr($email); ?>" style="width:100%;" />
  </p>
  <?php
}

public function save_contact_metabox($post_id, $post) {
  // autosave / revisions
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (wp_is_post_revision($post_id)) return;

  // nonce
  if (!isset($_POST['fga_contact_nonce']) || !wp_verify_nonce($_POST['fga_contact_nonce'], 'fga_contact_save')) {
    return;
  }

  // permissions
  if (!current_user_can('edit_post', $post_id)) return;

  $first = isset($_POST['fga_firstname']) ? sanitize_text_field(wp_unslash($_POST['fga_firstname'])) : '';
  $last  = isset($_POST['fga_lastname']) ? sanitize_text_field(wp_unslash($_POST['fga_lastname'])) : '';
  $email = isset($_POST['fga_email']) ? sanitize_email(wp_unslash($_POST['fga_email'])) : '';

  update_post_meta($post_id, self::META_FIRSTNAME, $first);
  update_post_meta($post_id, self::META_LASTNAME, $last);
  update_post_meta($post_id, self::META_EMAIL, $email);
}



public function add_details_metabox() {
  add_meta_box(
    'fga_details',
    __('Geefactie details'),
    [$this, 'render_details_metabox'],
    self::CPT,
    'normal',
    'high'
  );
}

public function render_details_metabox($post) {
  $kapitaal  = get_post_meta($post->ID, self::META_BENODIGD_KAPITAAL, true);
  $stichting = get_post_meta($post->ID, self::META_STICHTING_NAAM, true);

  wp_nonce_field('fga_details_save', 'fga_details_nonce');
  ?>
  <p style="margin:0 0 10px;">
    <label for="fga_benodigd_kapitaal" style="display:block;font-weight:600;margin-bottom:4px;">
      Benodigd kapitaal (€)
    </label>
    <input
      type="number"
      id="fga_benodigd_kapitaal"
      name="fga_benodigd_kapitaal"
      value="<?php echo esc_attr($kapitaal); ?>"
      step="1"
      min="0"
      style="width:100%;"
    />
  </p>

  <p style="margin:0;">
    <label for="fga_stichting_naam" style="display:block;font-weight:600;margin-bottom:4px;">
      Achterliggende stichting (optioneel)
    </label>
    <input
      type="text"
      id="fga_stichting_naam"
      name="fga_stichting_naam"
      value="<?php echo esc_attr($stichting); ?>"
      placeholder="Bijv. Stichting XYZ"
      style="width:100%;"
    />
  </p>
  <?php
}

public function save_details_metabox($post_id, $post) {
  // autosave / revisions
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (wp_is_post_revision($post_id)) return;

  // nonce
  if (!isset($_POST['fga_details_nonce']) || !wp_verify_nonce($_POST['fga_details_nonce'], 'fga_details_save')) {
    return;
  }

  // permissions
  if (!current_user_can('edit_post', $post_id)) return;

  $kapitaal  = isset($_POST['fga_benodigd_kapitaal']) ? intval(wp_unslash($_POST['fga_benodigd_kapitaal'])) : 0;
  $stichting = isset($_POST['fga_stichting_naam']) ? sanitize_text_field(wp_unslash($_POST['fga_stichting_naam'])) : '';

  update_post_meta($post_id, self::META_BENODIGD_KAPITAAL, $kapitaal);
  update_post_meta($post_id, self::META_STICHTING_NAAM, $stichting);
}


  /**
   * Gebruik plugin-template voor single geefactie.
   */
  public function load_single_template($single) {
    if (is_singular(self::CPT)) {
      $tpl = FGA_PATH . 'templates/single-geefactie.php';
      if (file_exists($tpl)) {
        return $tpl;
      }
    }
    return $single;
  }

  public function register_cpt_and_taxonomies() {

    $labels = [
      'name'               => 'Geefacties',
      'singular_name'      => 'Geefactie',
      'add_new'            => 'Nieuwe geefactie',
      'add_new_item'       => 'Nieuwe geefactie toevoegen',
      'edit_item'          => 'Geefactie bewerken',
      'new_item'           => 'Nieuwe geefactie',
      'view_item'          => 'Geefactie bekijken',
      'search_items'       => 'Geefacties zoeken',
      'not_found'          => 'Geen geefacties gevonden',
      'not_found_in_trash' => 'Geen geefacties in de prullenbak',
      'menu_name'          => 'Geefacties',
    ];

    register_post_type(self::CPT, [
      'labels'       => $labels,
      'public'       => true,
      'has_archive'  => true,
      'rewrite'      => ['slug' => 'geefacties'],
      'menu_icon'    => 'dashicons-heart',
      'supports'     => ['title', 'editor', 'excerpt', 'thumbnail', 'author'],
      'show_in_rest' => true,
    ]);

    register_taxonomy(self::TAX_THEMA, [self::CPT], [
      'label'        => 'Thema',
      'public'       => true,
      'hierarchical' => true,
      'show_in_rest' => true,
      'rewrite'      => ['slug' => 'geefactie-thema'],
    ]);

    register_taxonomy(self::TAX_TYPE, [self::CPT], [
      'label'        => 'Soort geefactie',
      'public'       => true,
      'hierarchical' => true,
      'show_in_rest' => true,
      'rewrite'      => ['slug' => 'geefactie-type'],
    ]);
  }
}

new FGA_Plugin();
