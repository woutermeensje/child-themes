<?php
if (!defined('ABSPATH')) exit;

class OB_AJAX {

  public static function register() {
    add_action('wp_ajax_ob_filter', [ __CLASS__, 'handle' ]);
    add_action('wp_ajax_nopriv_ob_filter', [ __CLASS__, 'handle' ]);

    // ✅ Condoleance submit
    add_action('wp_ajax_ob_submit_condolence', [ __CLASS__, 'submit_condolence' ]);
    add_action('wp_ajax_nopriv_ob_submit_condolence', [ __CLASS__, 'submit_condolence' ]);
  }

  public static function build_query($args) {
    $per_page = max(1, (int)($args['per_page'] ?? 12));
    $paged    = max(1, (int)($args['paged'] ?? 1));

    $selected = $args['selected'] ?? [];
    $keywords = (string)($args['keywords'] ?? '');

    $tax_query = [ 'relation' => 'AND' ];

    if (!empty($selected['provincie'])) {
      $tax_query[] = [
        'taxonomy' => OB_Taxonomies::TAX_PROV,
        'field'    => 'slug',
        'terms'    => $selected['provincie'],
      ];
    }
    if (!empty($selected['stad'])) {
      $tax_query[] = [
        'taxonomy' => OB_Taxonomies::TAX_CITY,
        'field'    => 'slug',
        'terms'    => $selected['stad'],
      ];
    }
    if (!empty($selected['type'])) {
      $tax_query[] = [
        'taxonomy' => OB_Taxonomies::TAX_TYPE,
        'field'    => 'slug',
        'terms'    => $selected['type'],
      ];
    }

    $qargs = [
      'post_type'      => OB_Post_Type::CPT,
      'post_status'    => 'publish',
      'posts_per_page' => $per_page,
      'paged'          => $paged,
      'orderby'        => 'date',
      'order'          => 'DESC',
    ];

    if (trim($keywords) !== '') {
      $qargs['s'] = $keywords;
    }

    if (count($tax_query) > 1) {
      $qargs['tax_query'] = $tax_query;
    }

    return new WP_Query($qargs);
  }

  public static function handle() {
    check_ajax_referer('ob_nonce', 'nonce');

    $per_page = (int) ob_get_req_string('per_page', 12);
    $paged    = (int) ob_get_req_string('paged', 1);

    $selected = [
      'provincie' => ob_clean_slugs(ob_get_req_array('provincie')),
      'stad'      => ob_clean_slugs(ob_get_req_array('stad')),
      'type'      => ob_clean_slugs(ob_get_req_array('type')),
    ];

    $keywords = ob_get_req_string('search_keywords', '');

    $q = self::build_query([
      'keywords' => $keywords,
      'selected' => $selected,
      'per_page' => $per_page,
      'paged'    => $paged,
    ]);

    wp_send_json_success([
      'html' => ob_template('listings.php', [ 'query' => $q ]),
    ]);
  }

  // ✅ A + B: AJAX handler voor condoleance + opslaan
  public static function submit_condolence() {
    // Nonce check (komt uit je formulier: <input name="nonce" ...>)
    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
    if (!wp_verify_nonce($nonce, 'ob_condoleer')) {
      wp_send_json_error([ 'message' => 'Ongeldige aanvraag.' ], 403);
    }

    $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
    if (!$post_id || get_post_status($post_id) !== 'publish') {
      wp_send_json_error([ 'message' => 'Overlijdensbericht niet gevonden.' ], 404);
    }

    $name    = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $email   = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $message = isset($_POST['message']) ? wp_strip_all_tags(wp_unslash($_POST['message'])) : '';

    if (mb_strlen(trim($message)) < 5) {
      wp_send_json_error([ 'message' => 'Schrijf een iets langer bericht (minimaal 5 tekens).' ], 400);
    }

    // Opslaan als comment (WP moderatie handig)
    $commentdata = [
      'comment_post_ID'      => $post_id,
      'comment_author'       => $name ?: 'Anoniem',
      'comment_author_email' => $email,
      'comment_content'      => $message,
      'comment_type'         => 'ob_condolence',
      'comment_approved'     => 0, // eerst modereren
    ];

    $comment_id = wp_insert_comment($commentdata);
    if (!$comment_id) {
      wp_send_json_error([ 'message' => 'Opslaan mislukt. Probeer het opnieuw.' ], 500);
    }

    // Optioneel mailen naar nabestaanden email (meta key: ob_nok_email)
    $nok_email = get_post_meta($post_id, 'ob_nok_email', true);
    if ($nok_email && is_email($nok_email)) {
      $subject = 'Nieuwe condoleance ontvangen';
      $body    = "Er is een nieuwe condoleance geplaatst.\n\n"
              . "Bericht:\n{$message}\n\n"
              . "Naam: " . ($name ?: 'Anoniem') . "\n"
              . "E-mail: " . ($email ?: '—') . "\n\n"
              . "Overlijdensbericht: " . get_permalink($post_id);

      wp_mail($nok_email, $subject, $body);
    }

    wp_send_json_success([ 'message' => 'Ontvangen' ]);
  }
}
