<?php

/**
 * Recommended way to include parent theme styles.
 * (Please see http://codex.wordpress.org/Child_Themes#How_to_Create_a_Child_Theme)
 *
 */

add_action('wp_enqueue_scripts', 'hello_elementor_child_style');
function hello_elementor_child_style()
{
   wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
   wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style'));
}

/**
 * Your code goes below.
 */


remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);


function custom_author_link_function()
{
   ob_start();

   echo "[yith_ywraq_button_quote product='1234']";

   $content = ob_get_clean();
   return $content;
}
add_shortcode('custom_author_link', 'custom_author_link_function');

// Shortcode to output custom PHP in Elementor
function wpc_elementor_shortcode($atts)
{
   echo "[yith_ywraq_button_quote]";
}
add_shortcode('custom_quote_button', 'wpc_elementor_shortcode');


//2. Trigger jQuery script
add_action('wp_footer', 'add_cart_quantity_plus_minus');
function add_cart_quantity_plus_minus()
{
   // Only run this on the single product page
   if (!is_product()) return; ?>
   <script type="text/javascript">
      jQuery(document).ready(function($) {
         $('form.cart').on('click', 'button.plus, button.minus', function() {
            // Get current quantity values
            var qty = $(this).closest('form.cart').find('.qty');
            var val = parseFloat(qty.val());
            var max = parseFloat(qty.attr('max'));
            var min = parseFloat(qty.attr('min'));
            var step = parseFloat(qty.attr('step'));
            // Change the value if plus or minus
            if ($(this).is('.plus')) {
               if (max && (max <= val)) {
                  qty.val(max);
               } else {
                  qty.val(val + step);
               }
            } else {
               if (min && (min >= val)) {
                  qty.val(min);
               } else if (val > 1) {
                  qty.val(val - step);
               }
            }
         });
      });
   </script>
<?php
}


//Change text strings 
function my_text_strings($translated_text, $text, $domain)
{
   switch ($translated_text) {
      case 'Subtotal':
         $translated_text = __('Your text here', 'woocommerce');
         break;
      case 'Checkout':
         $translated_text = __('Your text here', 'woocommerce');
         break;
      case 'Bekijk winkelwagen':
         $translated_text = __('Bekijk offerte', 'woocommerce');
         break;
   }
   return $translated_text;
}
add_filter('gettext', 'my_text_strings', 20, 3);

remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');


add_action('after_setup_theme', 'woocommerce_support');
function woocommerce_support()
{
   add_theme_support('woocommerce');
}
