<?php
defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}

// Extra classes die WooCommerce meegeeft (o.a. product type, instock, etc.)
$classes = wc_get_product_class( 'sj-card', $product );
?>

<li <?php wc_product_class( $classes, $product ); ?>>

    <a href="<?php the_permalink(); ?>" class="sj-card__link" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
        
        <div class="sj-card__media">
            <?php
            // Product afbeelding (met sale-flash indien actief)
            // Je kunt wc_get_template gebruiken, maar dit is directer:
            echo $product->get_image( 'woocommerce_thumbnail', [ 'class' => 'sj-card__image' ] );
            ?>
        </div>

        <div class="sj-card__body">

            <?php if ( $product->is_on_sale() ) : ?>
                <span class="sj-card__badge">Sale</span>
            <?php endif; ?>

            <h2 class="sj-card__title"><?php the_title(); ?></h2>

            <div class="sj-card__meta">
                <span class="sj-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>

                <?php
                // Rating optioneel (als je het wil)
                $rating_count = $product->get_rating_count();
                if ( $rating_count > 0 ) {
                    echo wc_get_rating_html( $product->get_average_rating() );
                }
                ?>
            </div>

            <?php
            // Korte beschrijving (optioneel)
            $short = $product->get_short_description();
            if ( $short ) : ?>
                <div class="sj-card__excerpt">
                    <?php echo wp_kses_post( wp_trim_words( $short, 18 ) ); ?>
                </div>
            <?php endif; ?>

        </div>
    </a>

    <div class="sj-card__footer">
        <?php
        // Add to cart knop (met WooCommerce classes/attributes)
        // Dit behoudt AJAX add-to-cart waar mogelijk.
        woocommerce_template_loop_add_to_cart();
        ?>
    </div>

</li>


<style>

    /* Product grid cards */
.woocommerce ul.products {
  display: grid;
  gap: 24px;
  
}

.woocommerce ul.products li.product.sj-card {
  list-style: none;
  margin: 0;
  padding: 10;
  border: 1px solid #e1e1e1;
  border-radius: 0px; 
}

.sj-card__link {
  display: block;
  text-decoration: none;
}

.sj-card__media {
  
  overflow: hidden;
  background: #fff;
}

.sj-card__image {
  width: 100%;
  height: auto;
  display: block;
}

.sj-card__body {
  padding: 12px 0 0 0;
}

.sj-card__badge {
  display: inline-block;
  font-size: 12px;
  padding: 6px 10px;
  border-radius: 999px;
  margin-bottom: 8px;
}

.sj-card__title {
  margin: 0 0 8px 0;
  font-size: 16px;
  line-height: 1.3;
}

.sj-card__meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 8px;
}

.sj-card__price {
  font-weight: 600;
}

.sj-card__excerpt {
  font-size: 14px;
  opacity: 0.85;
}

.sj-card__footer {
  margin-top: 12px;
}

.sj-card__footer .button,
.sj-card__footer a.button {
  width: 100%;
  text-align: center;
  border-radius: 0px;
  padding: 12px 16px;
  font-family: Poppins, sans-serif;
    font-weight: 600;
    background-color: #0456ab;
    color: #ffffff;
}

</style>