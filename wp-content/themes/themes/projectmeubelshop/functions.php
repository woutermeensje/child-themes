<?php

add_action('wp_enqueue_scripts', 'projectmeubelshop_child_assets');
function projectmeubelshop_child_assets()
{
   wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
   wp_enqueue_style('projectmeubelshop-child-style', get_stylesheet_uri(), array('parent-style'), wp_get_theme()->get('Version'));
   wp_enqueue_script(
      'projectmeubelshop-catalog-sidebar',
      get_stylesheet_directory_uri() . '/js/catalog-sidebar.js',
      array(),
      wp_get_theme()->get('Version'),
      true
   );
}

add_action('after_setup_theme', 'projectmeubelshop_child_setup');
function projectmeubelshop_child_setup()
{
   add_theme_support('woocommerce');
}

add_filter('woocommerce_product_tabs', 'projectmeubelshop_remove_reviews_tab', 98);
function projectmeubelshop_remove_reviews_tab($tabs)
{
   if (isset($tabs['reviews'])) {
      unset($tabs['reviews']);
   }

   return $tabs;
}

function pms_quote_ready()
{
   return class_exists('WooCommerce') && function_exists('WC') && is_object(WC());
}

function pms_quote_get_items()
{
   if (!pms_quote_ready()) {
      return array();
   }

   $wc = WC();
   if (empty($wc->session)) {
      return array();
   }

   $items = $wc->session->get('pms_quote_items', array());
   return is_array($items) ? $items : array();
}

function pms_quote_has_product($product_id)
{
   $product_id = absint($product_id);
   if (!$product_id) {
      return false;
   }

   $items = pms_quote_get_items();
   foreach ($items as $item) {
      $base_id = isset($item['product_id']) ? absint($item['product_id']) : 0;
      $var_id  = isset($item['variation_id']) ? absint($item['variation_id']) : 0;
      if ($base_id === $product_id || $var_id === $product_id) {
         return true;
      }
   }

   return false;
}

function pms_quote_set_items($items)
{
   if (!pms_quote_ready()) {
      return;
   }

   $wc = WC();
   if (empty($wc->session)) {
      return;
   }

   $wc->session->set('pms_quote_items', $items);
}

function pms_quote_item_key($product_id, $variation_id, $variation_data)
{
   ksort($variation_data);
   return md5($product_id . '|' . $variation_id . '|' . wp_json_encode($variation_data));
}

function pms_quote_add_item($product_id, $quantity = 1, $variation_id = 0, $variation_data = array())
{
   $product = wc_get_product($variation_id ? $variation_id : $product_id);
   if (!$product) {
      return false;
   }

   $qty   = max(1, absint($quantity));
   $key   = pms_quote_item_key((int) $product_id, (int) $variation_id, (array) $variation_data);
   $items = pms_quote_get_items();

   if (isset($items[$key])) {
      $items[$key]['quantity'] += $qty;
   } else {
      $items[$key] = array(
         'product_id'     => (int) $product_id,
         'variation_id'   => (int) $variation_id,
         'variation_data' => (array) $variation_data,
         'quantity'       => $qty,
      );
   }

   pms_quote_set_items($items);
   return true;
}

function pms_quote_collect_variation_data()
{
   $variation_data = array();

   foreach ($_POST as $key => $value) {
      if (0 === strpos((string) $key, 'attribute_')) {
         $variation_data[sanitize_key($key)] = sanitize_text_field(wp_unslash($value));
      }
   }

   return $variation_data;
}

function pms_quote_get_page_url()
{
   return home_url('/offerte-samenstellen/');
}

function pms_quote_render_actions_inside_cart()
{
   if (!function_exists('is_product') || !is_product()) {
      return;
   }

   global $product;
   if (!$product instanceof WC_Product) {
      return;
   }

   ?>
   <div class="pms-product-actions pms-inline-quote-actions">
      <?php wp_nonce_field('pms_add_to_quote', 'pms_quote_nonce'); ?>
      <input type="hidden" name="pms_product_id" value="<?php echo esc_attr($product->get_id()); ?>">
      <button type="submit" name="pms_add_to_quote" value="1" class="button pms-quote-add-button"><?php esc_html_e('Voeg toe aan offerte', 'projectmeubelshop-child'); ?></button>
      <a class="button pms-quote-view-button" href="<?php echo esc_url(pms_quote_get_page_url()); ?>"><?php esc_html_e('Offerte bekijken', 'projectmeubelshop-child'); ?></a>
   </div>
   <?php
}
// Disabled: quote button is rendered from single-product/title.php only.

// Disabled: no JS fallback injection to avoid duplicate buttons.
function pms_quote_fallback_injector()
{
   if (!function_exists('is_product') || !is_product()) {
      return;
   }

   $quote_url   = pms_quote_get_page_url();
   $quote_nonce = wp_create_nonce('pms_add_to_quote');
   ?>
   <script>
   jQuery(function($){
      $('form.cart').each(function(){
         var $form = $(this);
         if ($form.find('.pms-inline-quote-actions').length) return;

         if (!$form.find('input[name="pms_quote_nonce"]').length) {
            $form.append('<input type="hidden" name="pms_quote_nonce" value="<?php echo esc_js($quote_nonce); ?>">');
         }

         var pid = $form.find('input[name="add-to-cart"]').first().val() ||
                   $form.find('button.single_add_to_cart_button[name="add-to-cart"]').first().val() ||
                   $form.find('input[name="product_id"]').first().val() || '';
         if (pid && !$form.find('input[name="pms_product_id"]').length) {
            $form.append('<input type="hidden" name="pms_product_id" value="'+pid+'">');
         }

         var $target = $form.find('button.single_add_to_cart_button').first();
         if (!$target.length) return;

         var html = '<div class="pms-product-actions pms-inline-quote-actions">' +
            '<button type="submit" name="pms_add_to_quote" value="1" class="button pms-quote-add-button">Voeg toe aan offerte</button>' +
            '<a class="button pms-quote-view-button" href="<?php echo esc_url($quote_url); ?>">Offerte bekijken</a>' +
            '</div>';

         $target.after(html);
      });
   });
   </script>
   <?php
}

add_action('template_redirect', 'pms_quote_handle_add', 1);
function pms_quote_handle_add()
{
   if (!pms_quote_ready() || empty($_POST['pms_add_to_quote'])) {
      return;
   }

   // Prevent mixed WooCommerce add-to-cart notices when this request is quote-only.
   if (function_exists('wc_clear_notices')) {
      wc_clear_notices();
   }

   $nonce = isset($_POST['pms_quote_nonce']) ? sanitize_text_field(wp_unslash($_POST['pms_quote_nonce'])) : '';
   if (!$nonce || !wp_verify_nonce($nonce, 'pms_add_to_quote')) {
      return;
   }

   $product_id   = isset($_POST['pms_product_id']) ? absint($_POST['pms_product_id']) : 0;
   if (!$product_id && isset($_POST['add-to-cart'])) {
      $product_id = absint($_POST['add-to-cart']);
   }
   $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;
   $quantity     = isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : 1;
   $variation    = pms_quote_collect_variation_data();

   if ($variation_id > 0) {
      $parent = wp_get_post_parent_id($variation_id);
      if ($parent) {
         $product_id = (int) $parent;
      }
   }

   if ($product_id && pms_quote_add_item($product_id, $quantity, $variation_id, $variation)) {
      wc_add_notice(__('Product toegevoegd aan offerteaanvraag.', 'projectmeubelshop-child'), 'success');
   } else {
      wc_add_notice(__('Kon product niet toevoegen aan offerteaanvraag.', 'projectmeubelshop-child'), 'error');
   }

   $redirect_url = wp_get_referer();
   if (!$redirect_url && $product_id) {
      $redirect_url = get_permalink($product_id);
   }
   if (!$redirect_url) {
      $redirect_url = home_url('/');
   }

   wp_safe_redirect($redirect_url);
   exit;
}

add_action('template_redirect', 'pms_quote_handle_update_or_remove', 2);
function pms_quote_handle_update_or_remove()
{
   if (!pms_quote_ready() || (empty($_POST['pms_quote_update']) && empty($_POST['pms_quote_remove']))) {
      return;
   }

   $nonce = isset($_POST['pms_quote_page_nonce']) ? sanitize_text_field(wp_unslash($_POST['pms_quote_page_nonce'])) : '';
   if (!$nonce || !wp_verify_nonce($nonce, 'pms_quote_page_action')) {
      return;
   }

   $items = pms_quote_get_items();

   if (!empty($_POST['pms_quote_remove'])) {
      $remove_key = sanitize_text_field(wp_unslash($_POST['pms_quote_remove']));
      unset($items[$remove_key]);
      pms_quote_set_items($items);
   }

   if (!empty($_POST['pms_quote_qty']) && is_array($_POST['pms_quote_qty'])) {
      foreach ($_POST['pms_quote_qty'] as $key => $qty) {
         $item_key = sanitize_text_field(wp_unslash($key));
         if (!isset($items[$item_key])) {
            continue;
         }

         $new_qty = max(0, absint($qty));
         if (0 === $new_qty) {
            unset($items[$item_key]);
         } else {
            $items[$item_key]['quantity'] = $new_qty;
         }
      }
      pms_quote_set_items($items);
   }

   wp_safe_redirect(wp_get_referer() ? wp_get_referer() : home_url('/'));
   exit;
}

add_action('template_redirect', 'pms_quote_handle_submit', 3);
function pms_quote_handle_submit()
{
   if (!pms_quote_ready() || empty($_POST['pms_quote_submit'])) {
      return;
   }

   $nonce = isset($_POST['pms_quote_page_nonce']) ? sanitize_text_field(wp_unslash($_POST['pms_quote_page_nonce'])) : '';
   if (!$nonce || !wp_verify_nonce($nonce, 'pms_quote_page_action')) {
      return;
   }

   $items = pms_quote_get_items();
   if (empty($items)) {
      wp_safe_redirect(add_query_arg('pms_quote_error', 'empty', wp_get_referer() ? wp_get_referer() : home_url('/')));
      exit;
   }

   $name    = isset($_POST['pms_name']) ? sanitize_text_field(wp_unslash($_POST['pms_name'])) : '';
   $email   = isset($_POST['pms_email']) ? sanitize_email(wp_unslash($_POST['pms_email'])) : '';
   $phone   = isset($_POST['pms_phone']) ? sanitize_text_field(wp_unslash($_POST['pms_phone'])) : '';
   $message = isset($_POST['pms_message']) ? sanitize_textarea_field(wp_unslash($_POST['pms_message'])) : '';

   if (!$name || !$email || !is_email($email)) {
      wp_safe_redirect(add_query_arg('pms_quote_error', 'contact', wp_get_referer() ? wp_get_referer() : home_url('/')));
      exit;
   }

   $lines = array(
      'Nieuwe offerteaanvraag',
      '',
      'Naam: ' . $name,
      'E-mail: ' . $email,
      'Telefoon: ' . $phone,
      '',
      'Producten:',
   );

   foreach ($items as $item) {
      $product = wc_get_product(!empty($item['variation_id']) ? $item['variation_id'] : $item['product_id']);
      if (!$product) {
         continue;
      }
      $lines[] = '- ' . $product->get_name() . ' x ' . (int) $item['quantity'];
   }

   if ($message) {
      $lines[] = '';
      $lines[] = 'Opmerking:';
      $lines[] = $message;
   }

   $subject = sprintf(__('Offerteaanvraag van %s', 'projectmeubelshop-child'), $name);
   $headers = array('Reply-To: ' . $name . ' <' . $email . '>');
   $sent    = wp_mail(get_option('admin_email'), $subject, implode("\n", $lines), $headers);

   if ($sent) {
      pms_quote_set_items(array());
      wp_safe_redirect(add_query_arg('pms_quote_sent', '1', wp_get_referer() ? wp_get_referer() : home_url('/')));
      exit;
   }

   wp_safe_redirect(add_query_arg('pms_quote_error', 'mail', wp_get_referer() ? wp_get_referer() : home_url('/')));
   exit;
}

function pms_quote_render_page()
{
   if (!pms_quote_ready()) {
      return '<div class="pms-quote-shell"><p class="pms-quote-notice pms-quote-notice-error">' . esc_html__('WooCommerce is niet actief.', 'projectmeubelshop-child') . '</p></div>';
   }

   $items    = pms_quote_get_items();
   $shop_url = wc_get_page_permalink('shop');

   ob_start();
   ?>
   <section class="pms-quote-shell">
      <?php if (isset($_GET['pms_quote_sent']) && '1' === $_GET['pms_quote_sent']) : ?>
         <p class="pms-quote-notice pms-quote-notice-success"><?php esc_html_e('Bedankt, je offerteaanvraag is verzonden.', 'projectmeubelshop-child'); ?></p>
      <?php endif; ?>
      <?php if (isset($_GET['pms_quote_error']) && ('contact' === $_GET['pms_quote_error'] || 'mail' === $_GET['pms_quote_error'])) : ?>
         <p class="pms-quote-notice pms-quote-notice-error"><?php esc_html_e('Er ging iets mis. Controleer je gegevens en probeer opnieuw.', 'projectmeubelshop-child'); ?></p>
      <?php endif; ?>

      <?php if (empty($items)) : ?>
         <div class="pms-quote-card">
            <p><?php esc_html_e('Er staan nog geen producten in je offerteaanvraag.', 'projectmeubelshop-child'); ?></p>
            <a class="pms-btn pms-btn-secondary" href="<?php echo esc_url($shop_url ? $shop_url : home_url('/')); ?>"><?php esc_html_e('Bekijk producten', 'projectmeubelshop-child'); ?></a>
         </div>
      <?php else : ?>
         <div class="pms-quote-layout">
            <form method="post" class="pms-quote-card">
               <?php wp_nonce_field('pms_quote_page_action', 'pms_quote_page_nonce'); ?>
               <h3><?php esc_html_e('Producten', 'projectmeubelshop-child'); ?></h3>
               <table class="pms-quote-table">
                  <thead>
                     <tr>
                        <th><?php esc_html_e('Product', 'projectmeubelshop-child'); ?></th>
                        <th><?php esc_html_e('Aantal', 'projectmeubelshop-child'); ?></th>
                        <th><?php esc_html_e('Actie', 'projectmeubelshop-child'); ?></th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php foreach ($items as $key => $item) :
                        $product = wc_get_product(!empty($item['variation_id']) ? $item['variation_id'] : $item['product_id']);
                        if (!$product) {
                           continue;
                        }
                     ?>
                        <tr>
                           <td><?php echo esc_html($product->get_name()); ?></td>
                           <td><input class="pms-qty-input" type="number" min="1" name="pms_quote_qty[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($item['quantity']); ?>"></td>
                           <td><button class="pms-btn pms-btn-ghost" type="submit" name="pms_quote_remove" value="<?php echo esc_attr($key); ?>"><?php esc_html_e('Verwijderen', 'projectmeubelshop-child'); ?></button></td>
                        </tr>
                     <?php endforeach; ?>
                  </tbody>
               </table>
               <p><button class="pms-btn pms-btn-secondary" type="submit" name="pms_quote_update" value="1"><?php esc_html_e('Update aantallen', 'projectmeubelshop-child'); ?></button></p>
            </form>

            <form method="post" class="pms-quote-card">
               <?php wp_nonce_field('pms_quote_page_action', 'pms_quote_page_nonce'); ?>
               <h3><?php esc_html_e('Je gegevens', 'projectmeubelshop-child'); ?></h3>
               <p><label for="pms_name"><?php esc_html_e('Naam', 'projectmeubelshop-child'); ?></label><input class="pms-input" id="pms_name" type="text" name="pms_name" required></p>
               <p><label for="pms_email"><?php esc_html_e('E-mail', 'projectmeubelshop-child'); ?></label><input class="pms-input" id="pms_email" type="email" name="pms_email" required></p>
               <p><label for="pms_phone"><?php esc_html_e('Telefoon', 'projectmeubelshop-child'); ?></label><input class="pms-input" id="pms_phone" type="text" name="pms_phone"></p>
               <p><label for="pms_message"><?php esc_html_e('Opmerking', 'projectmeubelshop-child'); ?></label><textarea class="pms-input" id="pms_message" name="pms_message" rows="5"></textarea></p>
               <p><button class="pms-btn pms-btn-primary" type="submit" name="pms_quote_submit" value="1"><?php esc_html_e('Offerte aanvragen', 'projectmeubelshop-child'); ?></button></p>
            </form>
         </div>
      <?php endif; ?>
   </section>
   <?php

   return ob_get_clean();
}
add_shortcode('pms_quote_page', 'pms_quote_render_page');
