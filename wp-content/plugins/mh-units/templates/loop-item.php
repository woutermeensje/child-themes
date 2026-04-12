<?php if (!defined('ABSPATH')) exit; ?>

<?php
$unit_id         = get_the_ID();
$aanbod_terms    = get_the_terms($unit_id, 'mh_unit_aanbod');
$aanbod_label    = (!is_wp_error($aanbod_terms) && !empty($aanbod_terms)) ? $aanbod_terms[0]->name : '';
$terms_type      = get_the_terms($unit_id, 'mh_unit_type');
$terms_conditie  = get_the_terms($unit_id, 'mh_unit_conditie');
$price_note      = get_post_meta($unit_id, '_mh_unit_price_note', true);
$excerpt         = get_the_excerpt();
$in_quote        = function_exists('mh_quote_has_product') && mh_quote_has_product($unit_id);
$quote_url       = function_exists('mh_quote_get_page_url') ? mh_quote_get_page_url() : home_url('/mijn-offerte/');
$just_added      = isset($_GET['mh_added']) && (int) $_GET['mh_added'] === $unit_id;

if (!$excerpt) {
    $excerpt = wp_strip_all_tags(get_the_content());
}
?>

<article class="mh-unit-card mh-product-card">
    <a class="mh-unit-card__link mh-unit-card__media-link" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">
        <div class="mh-product-card__media mh-unit-card__media">
            <?php if ($aanbod_label): ?>
                <span class="mh-unit-card__badge"><?php echo esc_html($aanbod_label); ?></span>
            <?php endif; ?>

            <?php
            if (has_post_thumbnail()) {
                the_post_thumbnail(
                    'large',
                    [
                        'class'   => 'mh-product-card__image mh-unit-card__image',
                        'loading' => 'lazy',
                    ]
                );
            } else {
                echo wc_placeholder_img(
                    'large',
                    [
                        'class'   => 'mh-product-card__image mh-product-card__image--placeholder mh-unit-card__image',
                        'loading' => 'lazy',
                        'alt'     => get_the_title(),
                    ]
                );
            }
            ?>
        </div>
    </a>

    <div class="mh-product-card__body mh-unit-card__body">
        <h3 class="mh-product-card__title mh-product-card__title--shortcode">
            <a class="mh-unit-card__title-link" href="<?php the_permalink(); ?>">
                <?php the_title(); ?>
            </a>
        </h3>

        <?php if ($excerpt): ?>
            <p class="mh-unit-card__excerpt"><?php echo esc_html(wp_trim_words($excerpt, 18, '…')); ?></p>
        <?php endif; ?>

        <?php if ((!is_wp_error($terms_type) && !empty($terms_type)) || (!is_wp_error($terms_conditie) && !empty($terms_conditie))): ?>
            <div class="mh-unit-card__taxonomies">
                <?php if (!is_wp_error($terms_type) && !empty($terms_type)): ?>
                    <?php foreach ($terms_type as $term): ?>
                        <span class="mh-unit-card__tag"><?php echo esc_html($term->name); ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!is_wp_error($terms_conditie) && !empty($terms_conditie)): ?>
                    <?php foreach ($terms_conditie as $term): ?>
                        <span class="mh-unit-card__tag"><?php echo esc_html($term->name); ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="mh-unit-card__footer">
            <div class="mh-unit-card__actions">
                <a class="mh-unit-card__action-button mh-unit-card__action-button--view mh-unit-card__cta" href="<?php the_permalink(); ?>">Bekijk unit</a>

                <?php if ($in_quote || $just_added): ?>
                    <a class="mh-unit-card__action-button mh-unit-card__action-button--quote mh-unit-card__action-button--quote-added mh-unit-card__quote mh-unit-card__quote--added" href="<?php echo esc_url($quote_url); ?>">
                        Bekijk offerte
                    </a>
                <?php else: ?>
                    <form method="post" action="" class="mh-unit-card__quote-form mh-unit-card__quote-form--stacked">
                        <?php wp_nonce_field('mh_add_to_quote', 'mh_nonce'); ?>
                        <input type="hidden" name="mh_action" value="add_to_quote">
                        <input type="hidden" name="product_id" value="<?php echo esc_attr($unit_id); ?>">
                        <input type="hidden" name="item_type" value="mh_unit">
                        <button type="submit" class="mh-unit-card__action-button mh-unit-card__action-button--quote mh-unit-card__quote">Toevoegen aan offerte</button>
                    </form>
                <?php endif; ?>
            </div>

            <?php if ($price_note): ?>
                <span class="mh-unit-card__price-note"><?php echo esc_html($price_note); ?></span>
            <?php endif; ?>
        </div>
    </div>
</article>
