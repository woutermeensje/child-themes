<?php
if (!defined('ABSPATH')) exit;

get_header();

if (!have_posts()) {
    get_footer();
    return;
}

while (have_posts()) :
    the_post();

    $unit_id      = get_the_ID();
    $price_note   = get_post_meta($unit_id, '_mh_unit_price_note', true);
    $gallery_meta = get_post_meta($unit_id, '_mh_unit_gallery_ids', true);
    $gallery_ids  = array_values(array_filter(array_map('absint', explode(',', (string) $gallery_meta))));
    $image_ids    = array_values(array_filter(array_unique(array_merge(
        [get_post_thumbnail_id($unit_id)],
        $gallery_ids
    ))));

    $terms_type   = get_the_terms($unit_id, 'mh_unit_type');
    $terms_cond   = get_the_terms($unit_id, 'mh_unit_conditie');
    $terms_aanbod = get_the_terms($unit_id, 'mh_unit_aanbod');
    $excerpt      = get_the_excerpt();
    $content      = get_the_content();
    $in_quote     = function_exists('mh_quote_has_product') && mh_quote_has_product($unit_id);
    $quote_url    = function_exists('mh_quote_get_page_url') ? mh_quote_get_page_url() : home_url('/mijn-offerte/');
    $just_added   = isset($_GET['mh_added']) && (int) $_GET['mh_added'] === $unit_id;

    $all_terms = [];
    foreach ([$terms_aanbod, $terms_type, $terms_cond] as $group) {
        if (!empty($group) && !is_wp_error($group)) {
            foreach ($group as $term) {
                $all_terms[$term->taxonomy . ':' . $term->term_id] = $term;
            }
        }
    }

    $related_tax_query = [];
    $type_ids = !empty($terms_type) && !is_wp_error($terms_type) ? wp_list_pluck($terms_type, 'term_id') : [];
    $cond_ids = !empty($terms_cond) && !is_wp_error($terms_cond) ? wp_list_pluck($terms_cond, 'term_id') : [];

    if (!empty($type_ids)) {
        $related_tax_query[] = [
            'taxonomy' => 'mh_unit_type',
            'field'    => 'term_id',
            'terms'    => $type_ids,
        ];
    }

    if (!empty($cond_ids)) {
        $related_tax_query[] = [
            'taxonomy' => 'mh_unit_conditie',
            'field'    => 'term_id',
            'terms'    => $cond_ids,
        ];
    }

    if (count($related_tax_query) > 1) {
        $related_tax_query = array_merge([['relation' => 'OR']], $related_tax_query);
    }

    $related_units = new WP_Query([
        'post_type'      => 'mh_unit',
        'posts_per_page' => 6,
        'post__not_in'   => [$unit_id],
        'tax_query'      => !empty($related_tax_query) ? $related_tax_query : [],
    ]);
    ?>

    <style>
    .mhu-single-page .mh-product-wrapper {
        max-width: 1200px;
        margin: 40px auto;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
    }

    .mhu-single-page .mhu-breadcrumb {
        max-width: 1200px;
        margin: 32px auto 0;
        padding-top: 8px;
        color: #777;
        font-size: 13px;
        line-height: 1.5;
    }

    .mhu-single-page .mhu-breadcrumb a {
        color: #777;
        text-decoration: none;
    }

    .mhu-single-page .mhu-breadcrumb a:hover {
        color: var(--color-primary, #25476B);
    }

    .mhu-single-page .mhu-breadcrumb__sep {
        margin: 0 8px;
        color: #b4b4b4;
    }

    .mhu-single-page .mh-col-left {
        border: 1px solid #DEDEDE;
        border-radius: 5px;
        overflow: hidden;
        background: #fff;
    }

    .mhu-single-page .mh-col-right {
        background: #fff;
        border: 1px solid #DEDEDE;
        padding: 24px;
        border-radius: 5px;
    }

    .mhu-single-page .product_title.entry-title {
        display: block !important;
        margin: 0 0 12px !important;
        color: #111 !important;
        font-family: Inter, sans-serif;
        font-size: 32px;
        font-weight: 700;
        line-height: 1.2;
    }

    .mhu-single-page .mh-gallery-preview {
        position: relative;
        overflow: hidden;
        border-radius: 5px;
        background: #fff;
        line-height: 0;
    }

    .mhu-single-page .mh-gallery-preview img {
        width: 100%;
        height: auto;
        max-height: 500px;
        object-fit: contain;
        object-position: center top;
        display: none;
        vertical-align: top;
        margin: 0 auto;
    }

    .mhu-single-page .mh-gallery-preview img.is-active {
        display: block;
    }

    .mhu-single-page .mh-preview-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0,0,0,0.45);
        border: none;
        color: #fff;
        font-size: 22px;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 5;
        transition: background 0.2s;
    }

    .mhu-single-page .mh-preview-arrow:hover {
        background: rgba(0,0,0,0.7);
    }

    .mhu-single-page .mh-preview-arrow--prev { left: 12px; }
    .mhu-single-page .mh-preview-arrow--next { right: 12px; }

    .mhu-single-page .mh-preview-counter {
        position: absolute;
        bottom: 12px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 6px;
    }

    .mhu-single-page .mh-preview-counter span {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: rgba(255,255,255,0.5);
        display: block;
    }

    .mhu-single-page .mh-preview-counter span.is-active {
        background: #fff;
    }

    .mhu-single-page .mh-product-cats {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin: 4px 0 16px;
        padding: 0;
        list-style: none;
    }

    .mhu-single-page .mh-product-cats a,
    .mhu-single-page .mh-product-cats span {
        display: inline-flex;
        align-items: center;
        padding: 5px 14px;
        background: #fff;
        border: 1px solid var(--color-border-strong, #8AC697);
        border-radius: 999px;
        font-family: Inter, sans-serif;
        font-size: 13px;
        font-weight: 700;
        color: #333;
        text-decoration: none;
    }

    .mhu-single-page .mh-quote-cta {
        margin-top: 16px;
        margin-bottom: 28px;
    }

    .mhu-single-page .mh-quote-cta__divider {
        border: none;
        border-top: 1px solid #EBEBEB;
        margin: 20px 0 16px;
    }

    .mhu-single-page .mh-quote-cta__label {
        font-family: Inter, sans-serif;
        font-size: 13px;
        font-weight: 600;
        color: var(--color-text-soft, #39749B);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin: 0 0 10px;
    }

    .mhu-single-page .mh-quote-fallback {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .mhu-single-page .mh-qty-stepper {
        display: flex !important;
        align-items: center !important;
        border: 1px solid var(--color-border, #BFE396) !important;
        border-radius: 8px !important;
        overflow: hidden !important;
        background: #fff !important;
        flex: 0 0 auto !important;
        height: 46px !important;
    }

    .mhu-single-page .mh-qty-btn {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 36px !important;
        height: 100% !important;
        background: none !important;
        border: none !important;
        padding: 0 !important;
        color: #555 !important;
        cursor: pointer !important;
        transition: background 0.15s, color 0.15s !important;
        flex-shrink: 0 !important;
    }

    .mhu-single-page .mh-qty-btn:hover {
        background: var(--color-bg, #f5f5f5) !important;
        color: var(--color-primary, #25476B) !important;
    }

    .mhu-single-page .mh-qty-input {
        -webkit-appearance: none !important;
        appearance: none !important;
        width: 44px !important;
        height: 100% !important;
        border: none !important;
        border-left: 1px solid var(--color-border, #BFE396) !important;
        border-right: 1px solid var(--color-border, #BFE396) !important;
        padding: 0 !important;
        font-family: Inter, sans-serif !important;
        font-size: 15px !important;
        font-weight: 600 !important;
        color: #333 !important;
        background: #fff !important;
        text-align: center !important;
        box-shadow: none !important;
        outline: none !important;
    }

    .mhu-single-page .mh-quote-fallback-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex: 1 1 0 !important;
        min-width: 0 !important;
        height: 46px !important;
        padding: 0 16px !important;
        background: var(--color-primary, #25476B) !important;
        color: #fff !important;
        border: none !important;
        border-radius: 8px !important;
        font-family: Inter, sans-serif !important;
        font-size: 14px !important;
        font-weight: 700 !important;
        text-decoration: none !important;
        cursor: pointer !important;
        white-space: nowrap !important;
        transition: background-color 0.15s !important;
        box-sizing: border-box !important;
    }

    .mhu-single-page .mh-quote-fallback-btn:hover {
        background: var(--color-primary-hover, #325F8D) !important;
        color: #fff !important;
    }

    .mhu-single-page .mh-contact-info {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin: 0 0 28px;
    }

    .mhu-single-page .mh-contact-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 15px;
        color: #333 !important;
        text-decoration: none;
    }

    .mhu-single-page .mh-contact-item:hover {
        color: var(--color-secondary, #39749B);
    }

    .mhu-single-page .mh-intro-text {
        font-size: 15px;
        line-height: 1.7;
        color: #555;
        margin-bottom: 24px;
    }

    .mhu-single-page .mh-col-description {
        max-width: 1200px;
        margin: 40px auto 0;
        background: #fff;
        border: 1px solid #DEDEDE;
        padding: 24px;
        border-radius: 5px;
    }

    .mhu-single-page .mh-related {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin-top: 40px;
    }

    .mhu-single-page .mh-related-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .mhu-single-page .mh-related-item {
        display: flex !important;
        flex-direction: column !important;
        gap: 0 !important;
        text-decoration: none !important;
        color: #222 !important;
        background: transparent !important;
        border: 1px solid #DEDEDE;
        border-radius: 5px;
        overflow: hidden;
    }

    .mhu-single-page .mh-related-item:hover {
        border-color: #d2c19a;
    }

    .mhu-single-page .mh-related-thumb {
        width: 100%;
        height: 180px;
        overflow: hidden;
        flex-shrink: 0;
        background: #fff;
    }

    .mhu-single-page .mh-related-thumb img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        display: block !important;
    }

    .mhu-single-page .mh-related-name {
        font-weight: 600;
        font-size: 15px;
        color: #222;
        padding: 10px 12px 4px;
        display: block;
    }

    .mhu-single-page .mh-related-cats {
        font-size: 13px;
        color: #888;
        padding: 0 12px 12px;
        display: block;
    }

    .mhu-single-page .mh-lightbox {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.9);
        z-index: 99999;
        align-items: center;
        justify-content: center;
    }

    .mhu-single-page .mh-lightbox.is-open { display: flex; }
    .mhu-single-page .mh-lightbox-inner {
        position: relative;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
    }

    .mhu-single-page .mh-lightbox-strip {
        display: flex;
        align-items: center;
        gap: 24px;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        scrollbar-width: none;
        padding: 40px 80px;
        width: 100%;
        height: 100%;
        box-sizing: border-box;
    }

    .mhu-single-page .mh-lightbox-strip::-webkit-scrollbar { display: none; }

    .mhu-single-page .mh-lightbox-strip img {
        flex: 0 0 auto;
        max-height: calc(100vh - 120px);
        max-width: 85vw;
        object-fit: contain;
        scroll-snap-align: center;
        border-radius: 4px;
    }

    .mhu-single-page .mh-lightbox-arrow,
    .mhu-single-page .mh-lightbox-close {
        position: absolute;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: #fff;
        cursor: pointer;
        z-index: 10;
    }

    .mhu-single-page .mh-lightbox-arrow {
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255,255,255,0.15);
        border: none;
        font-size: 28px;
        width: 52px;
        height: 52px;
    }

    .mhu-single-page .mh-lightbox-arrow--prev { left: 16px; }
    .mhu-single-page .mh-lightbox-arrow--next { right: 16px; }

    .mhu-single-page .mh-lightbox-close {
        top: 20px;
        right: 20px;
        width: 52px;
        height: 52px;
        background: rgba(255,255,255,0.16);
        border: 1px solid rgba(255,255,255,0.22);
        font-size: 30px;
        line-height: 1;
    }

    @media (max-width: 980px) {
        .mhu-single-page .mh-product-wrapper {
            grid-template-columns: 1fr;
            gap: 20px;
            margin: 20px auto 0;
        }

        .mhu-single-page .mh-col-right,
        .mhu-single-page .mh-col-description {
            padding: 20px;
        }

        .mhu-single-page .product_title.entry-title {
            font-size: 28px;
            margin-bottom: 14px !important;
        }

        .mhu-single-page .mh-quote-fallback {
            flex-wrap: wrap !important;
        }

        .mhu-single-page .mh-qty-stepper,
        .mhu-single-page .mh-quote-fallback-btn {
            width: 100% !important;
            flex: 1 1 100% !important;
        }

        .mhu-single-page .mh-related-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
    }

    @media (max-width: 640px) {
        .mhu-single-page .mh-product-wrapper {
            gap: 16px;
            margin-top: 16px;
        }

        .mhu-single-page .mh-col-right,
        .mhu-single-page .mh-col-description {
            padding: 16px;
        }

        .mhu-single-page .product_title.entry-title {
            font-size: 24px;
            line-height: 1.25;
        }

        .mhu-single-page .mh-gallery-preview img {
            max-height: 280px;
        }

        .mhu-single-page .mh-related-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>

    <main class="mhu-single-page">
        <nav class="mhu-breadcrumb" aria-label="Breadcrumb">
            <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
            <span class="mhu-breadcrumb__sep" aria-hidden="true">›</span>
            <a href="<?php echo esc_url(get_post_type_archive_link('mh_unit') ?: home_url('/units/')); ?>">Units</a>
            <span class="mhu-breadcrumb__sep" aria-hidden="true">›</span>
            <span aria-current="page"><?php the_title(); ?></span>
        </nav>

        <div class="mh-product-wrapper">
            <div class="mh-col-left">
                <?php if (!empty($image_ids)) : ?>
                    <div class="mh-gallery-preview" id="mh-open-gallery">
                        <?php foreach ($image_ids as $index => $image_id) : ?>
                            <?php echo wp_get_attachment_image($image_id, 'large', false, ['class' => 0 === $index ? 'is-active' : '']); ?>
                        <?php endforeach; ?>

                        <?php if (count($image_ids) > 1) : ?>
                            <button class="mh-preview-arrow mh-preview-arrow--prev" id="mh-preview-prev" aria-label="Vorige">&#8592;</button>
                            <button class="mh-preview-arrow mh-preview-arrow--next" id="mh-preview-next" aria-label="Volgende">&#8594;</button>
                            <div class="mh-preview-counter" id="mh-preview-counter">
                                <?php for ($j = 0; $j < count($image_ids); $j++) : ?>
                                    <span class="<?php echo 0 === $j ? 'is-active' : ''; ?>"></span>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mh-lightbox" id="mh-lightbox" role="dialog" aria-modal="true">
                        <div class="mh-lightbox-inner">
                            <button class="mh-lightbox-close" id="mh-lightbox-close" aria-label="Sluiten">&times;</button>
                            <button class="mh-lightbox-arrow mh-lightbox-arrow--prev" id="mh-arrow-prev" aria-label="Vorige">&#8592;</button>
                            <div class="mh-lightbox-strip" id="mh-lightbox-strip">
                                <?php foreach ($image_ids as $image_id) : ?>
                                    <?php echo wp_get_attachment_image($image_id, 'full'); ?>
                                <?php endforeach; ?>
                            </div>
                            <button class="mh-lightbox-arrow mh-lightbox-arrow--next" id="mh-arrow-next" aria-label="Volgende">&#8594;</button>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="mh-gallery-preview">
                        <div style="display:flex;align-items:center;justify-content:center;min-height:420px;background:#f7fbf7;color:#9aa6b2;">
                            Geen afbeeldingen toegevoegd
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mh-col-right">
                <?php the_title('<h1 class="product_title entry-title">', '</h1>'); ?>

                <?php if (!empty($all_terms)) : ?>
                    <div class="mh-product-cats">
                        <?php foreach ($all_terms as $term) : ?>
                            <a href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo esc_html($term->name); ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="mh-quote-cta">
                    <hr class="mh-quote-cta__divider">
                    <p class="mh-quote-cta__label">Interesse in deze unit?</p>

                    <?php if ($in_quote || $just_added) : ?>
                        <a href="<?php echo esc_url($quote_url); ?>" class="mh-quote-fallback-btn">Offerte bekijken</a>
                    <?php else : ?>
                        <form method="post" action="" class="mh-quote-fallback" id="mh-quote-form">
                            <?php wp_nonce_field('mh_add_to_quote', 'mh_nonce'); ?>
                            <input type="hidden" name="mh_action" value="add_to_quote">
                            <input type="hidden" name="product_id" value="<?php echo esc_attr($unit_id); ?>">
                            <input type="hidden" name="item_type" value="mh_unit">
                            <div class="mh-qty-stepper">
                                <button type="button" class="mh-qty-btn mh-qty-btn--minus" aria-label="Minder">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/></svg>
                                </button>
                                <input type="number" class="mh-qty-input" name="quantity" value="1" min="1" aria-label="Aantal">
                                <button type="button" class="mh-qty-btn mh-qty-btn--plus" aria-label="Meer">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                </button>
                            </div>
                            <button type="submit" class="mh-quote-fallback-btn">Toevoegen aan offerte</button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="mh-contact-info">
                    <a href="mailto:informatie@modulairehuisvesting.nl" class="mh-contact-item">informatie@modulairehuisvesting.nl</a>
                    <a href="tel:0852392040" class="mh-contact-item">085 239 2040</a>
                </div>

                <?php if (!empty($excerpt)) : ?>
                    <div class="mh-intro-text"><?php echo wpautop(esc_html($excerpt)); ?></div>
                <?php endif; ?>

                <?php if ($price_note) : ?>
                    <div class="mh-intro-text"><strong>Prijsindicatie:</strong> <?php echo esc_html($price_note); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty(trim(wp_strip_all_tags($content)))) : ?>
            <div class="mh-col-description">
                <h2>Unit beschrijving</h2>
                <?php echo apply_filters('the_content', $content); ?>
            </div>
        <?php endif; ?>

        <?php if ($related_units->have_posts()) : ?>
            <div class="mh-col-description mh-related">
                <h2>Gerelateerde units</h2>
                <div class="mh-related-grid">
                    <?php while ($related_units->have_posts()) : $related_units->the_post(); ?>
                        <a href="<?php the_permalink(); ?>" class="mh-related-item">
                            <div class="mh-related-thumb">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('medium'); ?>
                                <?php else : ?>
                                    <div style="width:100%;height:100%;background:#f7fbf7;"></div>
                                <?php endif; ?>
                            </div>
                            <span class="mh-related-name"><?php the_title(); ?></span>
                            <?php
                            $related_meta = [];
                            $related_types = get_the_terms(get_the_ID(), 'mh_unit_type');
                            $related_cond = get_the_terms(get_the_ID(), 'mh_unit_conditie');
                            if (!empty($related_types) && !is_wp_error($related_types)) {
                                $related_meta[] = $related_types[0]->name;
                            }
                            if (!empty($related_cond) && !is_wp_error($related_cond)) {
                                $related_meta[] = $related_cond[0]->name;
                            }
                            ?>
                            <?php if (!empty($related_meta)) : ?>
                                <span class="mh-related-cats"><?php echo esc_html(implode(' • ', $related_meta)); ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php wp_reset_postdata(); ?>
        <?php endif; ?>
    </main>

    <script>
    (function () {
      const previewImgs = document.querySelectorAll('#mh-open-gallery img');
      const dots = document.querySelectorAll('#mh-preview-counter span');
      const prevBtn = document.getElementById('mh-preview-prev');
      const nextBtn = document.getElementById('mh-preview-next');
      let current = 0;

      function showSlide(index) {
        if (!previewImgs.length) return;
        current = (index + previewImgs.length) % previewImgs.length;
        previewImgs.forEach((img, i) => img.classList.toggle('is-active', i === current));
        dots.forEach((dot, i) => dot.classList.toggle('is-active', i === current));
      }

      if (prevBtn) prevBtn.addEventListener('click', function (e) { e.stopPropagation(); showSlide(current - 1); });
      if (nextBtn) nextBtn.addEventListener('click', function (e) { e.stopPropagation(); showSlide(current + 1); });

      const lightbox = document.getElementById('mh-lightbox');
      const closeBtn = document.getElementById('mh-lightbox-close');
      const strip = document.getElementById('mh-lightbox-strip');
      const lbPrev = document.getElementById('mh-arrow-prev');
      const lbNext = document.getElementById('mh-arrow-next');

      if (lightbox && strip) {
        const lbImgs = strip.querySelectorAll('img');
        let lbCurrent = 0;

        function lbScrollTo(index) {
          if (!lbImgs.length) return;
          lbCurrent = Math.max(0, Math.min(lbImgs.length - 1, index));
          lbImgs[lbCurrent].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        }

        previewImgs.forEach(function (img) {
          img.style.cursor = 'zoom-in';
          img.addEventListener('click', function () {
            lbScrollTo(current);
            lightbox.classList.add('is-open');
            document.body.style.overflow = 'hidden';
          });
        });

        if (closeBtn) closeBtn.addEventListener('click', closeLb);
        lightbox.addEventListener('click', function (e) { if (e.target === lightbox) closeLb(); });
        if (lbPrev) lbPrev.addEventListener('click', function () { lbScrollTo(lbCurrent - 1); });
        if (lbNext) lbNext.addEventListener('click', function () { lbScrollTo(lbCurrent + 1); });

        document.addEventListener('keydown', function (e) {
          if (!lightbox.classList.contains('is-open')) return;
          if (e.key === 'Escape') closeLb();
          if (e.key === 'ArrowLeft') lbScrollTo(lbCurrent - 1);
          if (e.key === 'ArrowRight') lbScrollTo(lbCurrent + 1);
        });

        function closeLb() {
          lightbox.classList.remove('is-open');
          document.body.style.overflow = '';
        }
      }

      document.querySelectorAll('.mh-qty-stepper').forEach(function (stepper) {
        var input = stepper.querySelector('.mh-qty-input');
        var minus = stepper.querySelector('.mh-qty-btn--minus');
        var plus = stepper.querySelector('.mh-qty-btn--plus');
        if (!input) return;
        if (minus) minus.addEventListener('click', function () {
          var v = parseInt(input.value, 10) || 1;
          if (v > 1) input.value = v - 1;
        });
        if (plus) plus.addEventListener('click', function () {
          var v = parseInt(input.value, 10) || 1;
          input.value = v + 1;
        });
      });
    })();
    </script>
<?php
endwhile;

get_footer();
