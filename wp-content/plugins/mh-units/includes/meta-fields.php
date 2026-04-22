<?php
if (!defined('ABSPATH')) exit;

add_action('add_meta_boxes', function () {
    add_meta_box(
        'mh_unit_prices',
        'Prijsindicatie',
        'mh_unit_prices_callback',
        'mh_unit',
        'side',
        'default'
    );

    add_meta_box(
        'mh_unit_gallery',
        'Galerij afbeeldingen',
        'mh_unit_gallery_callback',
        'mh_unit',
        'normal',
        'high'
    );
});

function mh_unit_prices_callback($post) {
    $price_huren = get_post_meta($post->ID, '_mh_unit_price_huren', true);
    $price_kopen = get_post_meta($post->ID, '_mh_unit_price_kopen', true);
    wp_nonce_field('mh_unit_meta_fields_save', 'mh_unit_meta_fields_nonce');
    ?>
    <p style="font-size:12px;font-weight:600;margin:0 0 4px;">Huren</p>
    <input
        type="text"
        name="mh_unit_price_huren"
        value="<?php echo esc_attr($price_huren); ?>"
        style="width:100%;margin-bottom:12px;"
        placeholder="Bijv. € 450,- p/m of Prijs op aanvraag"
    />
    <p style="font-size:12px;font-weight:600;margin:0 0 4px;">Kopen</p>
    <input
        type="text"
        name="mh_unit_price_kopen"
        value="<?php echo esc_attr($price_kopen); ?>"
        style="width:100%;margin-bottom:12px;"
        placeholder="Bijv. € 24.900,- of Prijs op aanvraag"
    />
    <p style="font-size:12px;font-weight:600;margin:0 0 4px;">Toelichting</p>
    <textarea
        name="mh_unit_price_toelichting"
        rows="3"
        style="width:100%;resize:vertical;"
        placeholder="Bijv. Prijzen zijn exclusief BTW en afhankelijk van configuratie."
    ><?php echo esc_textarea(get_post_meta($post->ID, '_mh_unit_price_toelichting', true)); ?></textarea>
    <p style="font-size:12px;color:#666;margin-top:8px;">
        Laat velden leeg om ze niet te tonen op de detailpagina.
    </p>
    <?php
}

function mh_unit_gallery_callback($post) {
    $gallery_ids = get_post_meta($post->ID, '_mh_unit_gallery_ids', true);
    $gallery_ids = is_string($gallery_ids) ? $gallery_ids : '';
    $ids         = array_values(array_filter(array_map('absint', explode(',', $gallery_ids))));
    ?>
    <div class="mh-unit-gallery-field" data-target-input="#mh-unit-gallery-ids">
        <input type="hidden" id="mh-unit-gallery-ids" name="mh_unit_gallery_ids" value="<?php echo esc_attr(implode(',', $ids)); ?>">

        <div class="mh-unit-gallery-field__actions" style="display:flex;gap:10px;align-items:center;margin-bottom:14px;">
            <button type="button" class="button button-secondary mh-unit-gallery-open">Galerij kiezen</button>
            <button type="button" class="button-link-delete mh-unit-gallery-clear"<?php echo empty($ids) ? ' style="display:none;"' : ''; ?>>Leegmaken</button>
        </div>

        <div class="mh-unit-gallery-field__preview" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:12px;">
            <?php foreach ($ids as $attachment_id) :
                $thumb = wp_get_attachment_image_url($attachment_id, 'medium');
                if (!$thumb) {
                    continue;
                }
                ?>
                <div class="mh-unit-gallery-thumb" data-attachment-id="<?php echo esc_attr($attachment_id); ?>" style="position:relative;border:1px solid #dcdcde;border-radius:6px;overflow:hidden;background:#fff;">
                    <img src="<?php echo esc_url($thumb); ?>" alt="" style="display:block;width:100%;height:110px;object-fit:cover;">
                </div>
            <?php endforeach; ?>
        </div>

        <p style="font-size:12px;color:#666;margin-top:12px;">
            Gebruik hier extra afbeeldingen voor de productgalerij. De uitgelichte afbeelding blijft de hoofdafbeelding.
        </p>
    </div>
    <?php
}

add_action('admin_enqueue_scripts', function ($hook) {
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }

    $screen = get_current_screen();
    if (!$screen || 'mh_unit' !== $screen->post_type) {
        return;
    }

    wp_enqueue_media();
    wp_add_inline_script(
        'jquery-core',
        <<<JS
document.addEventListener('DOMContentLoaded', function () {
  var field = document.querySelector('.mh-unit-gallery-field');
  if (!field || typeof wp === 'undefined' || !wp.media) return;

  var input = field.querySelector('#mh-unit-gallery-ids');
  var openButton = field.querySelector('.mh-unit-gallery-open');
  var clearButton = field.querySelector('.mh-unit-gallery-clear');
  var preview = field.querySelector('.mh-unit-gallery-field__preview');
  var frame;

  function render(ids) {
    preview.innerHTML = '';

    if (!ids.length) {
      clearButton.style.display = 'none';
      return;
    }

    clearButton.style.display = '';

    ids.forEach(function (attachment) {
      var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
      if (!url) return;

      var item = document.createElement('div');
      item.className = 'mh-unit-gallery-thumb';
      item.style.cssText = 'position:relative;border:1px solid #dcdcde;border-radius:6px;overflow:hidden;background:#fff;';

      var image = document.createElement('img');
      image.src = url;
      image.alt = '';
      image.style.cssText = 'display:block;width:100%;height:110px;object-fit:cover;';

      item.appendChild(image);
      preview.appendChild(item);
    });
  }

  openButton.addEventListener('click', function (event) {
    event.preventDefault();

    if (frame) {
      frame.open();
      return;
    }

    frame = wp.media({
      title: 'Selecteer galerij afbeeldingen',
      button: { text: 'Gebruik deze afbeeldingen' },
      multiple: true,
      library: { type: 'image' }
    });

    frame.on('select', function () {
      var selection = frame.state().get('selection').toJSON();
      var ids = selection.map(function (attachment) { return attachment.id; });
      input.value = ids.join(',');
      render(selection);
    });

    frame.open();
  });

  clearButton.addEventListener('click', function (event) {
    event.preventDefault();
    input.value = '';
    render([]);
  });
});
JS
    );
});

add_action('save_post', function ($post_id) {
    if (!isset($_POST['mh_unit_meta_fields_nonce'])) return;
    if (!wp_verify_nonce($_POST['mh_unit_meta_fields_nonce'], 'mh_unit_meta_fields_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['post_type']) && 'mh_unit' !== $_POST['post_type']) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['mh_unit_price_huren'])) {
        update_post_meta($post_id, '_mh_unit_price_huren', sanitize_text_field(wp_unslash($_POST['mh_unit_price_huren'])));
    }

    if (isset($_POST['mh_unit_price_kopen'])) {
        update_post_meta($post_id, '_mh_unit_price_kopen', sanitize_text_field(wp_unslash($_POST['mh_unit_price_kopen'])));
    }

    if (isset($_POST['mh_unit_price_toelichting'])) {
        update_post_meta($post_id, '_mh_unit_price_toelichting', sanitize_textarea_field(wp_unslash($_POST['mh_unit_price_toelichting'])));
    }

    if (isset($_POST['mh_unit_gallery_ids'])) {
        $ids = array_values(
            array_filter(
                array_map(
                    'absint',
                    explode(',', (string) wp_unslash($_POST['mh_unit_gallery_ids']))
                )
            )
        );

        update_post_meta($post_id, '_mh_unit_gallery_ids', implode(',', $ids));
    }
});
