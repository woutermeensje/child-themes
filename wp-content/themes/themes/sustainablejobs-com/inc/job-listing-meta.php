<?php
if (!defined('ABSPATH')) exit;

/* ============================================================
   Extra meta fields for job_listing posts
   - Salary range, hours per week, contact person
============================================================ */

add_action('add_meta_boxes', function () {
    add_meta_box(
        'sc_job_extra_info',
        'Job details',
        'sc_job_extra_info_meta_box',
        'job_listing',
        'normal',
        'high'
    );
    add_meta_box(
        'sc_job_contact',
        'Contact person',
        'sc_job_contact_meta_box',
        'job_listing',
        'normal',
        'default'
    );
});

/* ── Job details meta box ───────────────────────────── */
function sc_job_extra_info_meta_box($post) {
    wp_nonce_field('sc_job_extra_info_save', 'sc_job_extra_info_nonce');
    $salary = get_post_meta($post->ID, '_job_salary_range', true);
    $hours  = get_post_meta($post->ID, '_job_hours_per_week', true);
    ?>
    <style>
    .sj-meta-field { margin-bottom: 14px; }
    .sj-meta-field label { display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px; color: #374151; }
    .sj-meta-field input, .sj-meta-field select { width: 100%; padding: 7px 10px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px; }
    </style>
    <div class="sj-meta-field">
        <label for="sc_salary_range">Salary range</label>
        <input type="text" id="sc_salary_range" name="sc_salary_range"
               value="<?php echo esc_attr($salary); ?>" placeholder="E.g. €3,000 – €4,500 per month">
    </div>
    <div class="sj-meta-field">
        <label for="sc_hours_per_week">Hours per week</label>
        <input type="text" id="sc_hours_per_week" name="sc_hours_per_week"
               value="<?php echo esc_attr($hours); ?>" placeholder="E.g. 32–40 hours">
    </div>
    <?php
}

/* ── Contact person meta box ────────────────────────── */
function sc_job_contact_meta_box($post) {
    wp_nonce_field('sc_job_contact_save', 'sc_job_contact_nonce');
    $firstname = get_post_meta($post->ID, '_job_contact_firstname', true);
    $lastname  = get_post_meta($post->ID, '_job_contact_lastname', true);
    $email     = get_post_meta($post->ID, '_job_contact_email', true);
    ?>
    <div class="sj-meta-field">
        <label for="sc_contact_firstname">First name</label>
        <input type="text" id="sc_contact_firstname" name="sc_contact_firstname"
               value="<?php echo esc_attr($firstname); ?>" placeholder="First name">
    </div>
    <div class="sj-meta-field">
        <label for="sc_contact_lastname">Last name</label>
        <input type="text" id="sc_contact_lastname" name="sc_contact_lastname"
               value="<?php echo esc_attr($lastname); ?>" placeholder="Last name">
    </div>
    <div class="sj-meta-field">
        <label for="sc_contact_email">Email address</label>
        <input type="email" id="sc_contact_email" name="sc_contact_email"
               value="<?php echo esc_attr($email); ?>" placeholder="name@company.com">
    </div>
    <?php
}

function sc_render_cover_image_field($post_id) {
    $cover_image_id  = (int) get_post_meta($post_id, '_cover_image_id', true);
    $cover_image_url = get_post_meta($post_id, '_cover_image', true);

    if ($cover_image_id) {
        $preview_url = wp_get_attachment_image_url($cover_image_id, 'medium');
        $cover_image_url = $cover_image_url ?: wp_get_attachment_url($cover_image_id);
    } else {
        $preview_url = $cover_image_url;
    }
    ?>
    <style>
    .sj-cover-image__preview { margin-bottom: 12px; }
    .sj-cover-image__preview img { display: block; width: 100%; height: auto; border: 1px solid #d1d5db; border-radius: 4px; }
    .sj-cover-image__placeholder { padding: 18px 12px; border: 1px dashed #d1d5db; border-radius: 4px; text-align: center; color: #6b7280; font-size: 12px; }
    .sj-cover-image__actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .sj-cover-image__actions .button-link-delete { color: #b42318; }
    </style>
    <input type="hidden" name="sc_job_cover_image_nonce" value="<?php echo esc_attr(wp_create_nonce('sc_job_cover_image_save')); ?>">
    <input type="hidden" name="sc_cover_image_id" id="sc_cover_image_id" value="<?php echo esc_attr($cover_image_id); ?>">
    <input type="hidden" name="sc_cover_image_url" id="sc_cover_image_url" value="<?php echo esc_url($cover_image_url); ?>">

    <div class="sj-cover-image__preview" id="sc_cover_image_preview" style="max-width:320px;">
        <?php if ($preview_url) : ?>
            <img src="<?php echo esc_url($preview_url); ?>" alt="">
        <?php else : ?>
            <div class="sj-cover-image__placeholder">No vacancy image selected yet.</div>
        <?php endif; ?>
    </div>

    <div class="sj-cover-image__actions" style="margin-top:8px;">
        <button type="button" class="button button-secondary" id="sc_cover_image_select">
            <?php echo $preview_url ? 'Replace image' : 'Choose image'; ?>
        </button>
        <button
            type="button"
            class="button-link-delete"
            id="sc_cover_image_remove"
            <?php disabled(!$preview_url); ?>
        >
            Remove image
        </button>
    </div>

    <p class="description" style="max-width:520px;">
        Separate image for the vacancy page/card. The company logo stays in the default company logo field.
    </p>
    <?php
}

add_action('job_manager_job_listing_data_end', function ($post_id) {
    ?>
    <div class="form-field">
        <label><?php esc_html_e('Vacancy image', 'sustainablejobs-com'); ?></label>
        <?php sc_render_cover_image_field($post_id); ?>
    </div>
    <?php
});

add_action('admin_enqueue_scripts', function ($hook_suffix) {
    if (!in_array($hook_suffix, ['post.php', 'post-new.php'], true)) {
        return;
    }

    $screen = get_current_screen();
    if (!$screen || 'job_listing' !== $screen->post_type) {
        return;
    }

    wp_enqueue_media();
    wp_add_inline_script('jquery-core', <<<'JS'
jQuery(function ($) {
    var frame;
    var $imageId = $('#sc_cover_image_id');
    var $imageUrl = $('#sc_cover_image_url');
    var $preview = $('#sc_cover_image_preview');
    var $select = $('#sc_cover_image_select');
    var $remove = $('#sc_cover_image_remove');

    function renderPreview(url) {
        if (url) {
            $preview.html('<img src="' + url + '" alt="">');
            $select.text('Replace image');
            $remove.prop('disabled', false);
        } else {
            $preview.html('<div class="sj-cover-image__placeholder">No vacancy image selected yet.</div>');
            $select.text('Choose image');
            $remove.prop('disabled', true);
        }
    }

    $select.on('click', function (event) {
        event.preventDefault();

        if (frame) {
            frame.open();
            return;
        }

        frame = wp.media({
            title: 'Choose vacancy image',
            button: {
                text: 'Use this image'
            },
            library: {
                type: 'image'
            },
            multiple: false
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            var previewUrl = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;

            $imageId.val(attachment.id || '');
            $imageUrl.val(attachment.url || '');
            renderPreview(previewUrl);
        });

        frame.open();
    });

    $remove.on('click', function (event) {
        event.preventDefault();
        $imageId.val('');
        $imageUrl.val('');
        renderPreview('');
    });
});
JS
    );
});

/* ── Save ───────────────────────────────────────────── */
add_action('save_post_job_listing', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (
        isset($_POST['sc_job_extra_info_nonce']) &&
        wp_verify_nonce($_POST['sc_job_extra_info_nonce'], 'sc_job_extra_info_save')
    ) {
        update_post_meta($post_id, '_job_salary_range',   sanitize_text_field($_POST['sc_salary_range']    ?? ''));
        update_post_meta($post_id, '_job_hours_per_week', sanitize_text_field($_POST['sc_hours_per_week']  ?? ''));
    }

    if (
        isset($_POST['sc_job_contact_nonce']) &&
        wp_verify_nonce($_POST['sc_job_contact_nonce'], 'sc_job_contact_save')
    ) {
        update_post_meta($post_id, '_job_contact_firstname', sanitize_text_field($_POST['sc_contact_firstname'] ?? ''));
        update_post_meta($post_id, '_job_contact_lastname',  sanitize_text_field($_POST['sc_contact_lastname']  ?? ''));
        update_post_meta($post_id, '_job_contact_email',     sanitize_email($_POST['sc_contact_email']          ?? ''));
    }

    if (
        isset($_POST['sc_job_cover_image_nonce']) &&
        wp_verify_nonce($_POST['sc_job_cover_image_nonce'], 'sc_job_cover_image_save')
    ) {
        $cover_image_id  = absint($_POST['sc_cover_image_id'] ?? 0);
        $cover_image_url = esc_url_raw($_POST['sc_cover_image_url'] ?? '');

        if ($cover_image_id) {
            update_post_meta($post_id, '_cover_image_id', $cover_image_id);
            update_post_meta($post_id, '_cover_image', wp_get_attachment_url($cover_image_id) ?: $cover_image_url);
        } elseif ($cover_image_url) {
            delete_post_meta($post_id, '_cover_image_id');
            update_post_meta($post_id, '_cover_image', $cover_image_url);
        } else {
            delete_post_meta($post_id, '_cover_image_id');
            delete_post_meta($post_id, '_cover_image');
        }
    }
});
