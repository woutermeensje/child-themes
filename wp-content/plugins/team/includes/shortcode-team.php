<?php
if (!defined('ABSPATH')) exit;

function mh_team_get_member_name(int $post_id): string {
    $first_name = get_post_meta($post_id, '_mh_team_first_name', true);
    $last_name  = get_post_meta($post_id, '_mh_team_last_name', true);
    $full_name  = trim($first_name . ' ' . $last_name);

    return $full_name !== '' ? $full_name : get_the_title($post_id);
}

function mh_team_get_initials(int $post_id): string {
    $name  = trim(mh_team_get_member_name($post_id));
    $parts = preg_split('/\s+/', $name);

    if (!$parts) {
        return 'T';
    }

    $first = $parts[0] ?? '';
    $last  = count($parts) > 1 ? $parts[count($parts) - 1] : '';

    $first_initial = function_exists('mb_substr') ? mb_substr($first, 0, 1) : substr($first, 0, 1);
    $last_initial  = function_exists('mb_substr') ? mb_substr($last, 0, 1) : substr($last, 0, 1);
    $initials      = $first_initial . $last_initial;

    return strtoupper($initials ?: 'T');
}

function mh_team_tel_href(string $phone): string {
    $phone = trim($phone);
    if ($phone === '') {
        return '';
    }

    return 'tel:' . preg_replace('/[^\d+]/', '', $phone);
}

function mh_team_get_entry_type(int $post_id): string {
    $type = get_post_meta($post_id, '_mh_team_entry_type', true);

    return 'partner' === $type ? 'partner' : 'team_member';
}

function mh_team_query_args_for_type(string $entry_type, int $per_page): array {
    $args = [
        'post_type'      => 'mh_team_member',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
    ];

    if ('partner' === $entry_type) {
        $args['meta_query'] = [
            [
                'key'   => '_mh_team_entry_type',
                'value' => 'partner',
            ],
        ];

        return $args;
    }

    $args['meta_query'] = [
        'relation' => 'OR',
        [
            'key'     => '_mh_team_entry_type',
            'compare' => 'NOT EXISTS',
        ],
        [
            'key'   => '_mh_team_entry_type',
            'value' => '',
        ],
        [
            'key'   => '_mh_team_entry_type',
            'value' => 'team_member',
        ],
    ];

    return $args;
}

function mh_team_render_member_card(int $post_id, bool $show_contact, int $description_words): string {
    $name        = mh_team_get_member_name($post_id);
    $role        = get_post_meta($post_id, '_mh_team_role', true);
    $email       = get_post_meta($post_id, '_mh_team_email', true);
    $phone       = get_post_meta($post_id, '_mh_team_phone', true);
    $phone_href  = mh_team_tel_href((string) $phone);
    $description = trim(wp_strip_all_tags(get_post_field('post_content', $post_id)));
    $card_class  = 'mh-team-card';

    if ('partner' === mh_team_get_entry_type($post_id)) {
        $card_class .= ' mh-team-card--partner';
    }

    if ($description_words > 0 && $description !== '') {
        $description = wp_trim_words($description, $description_words, '...');
    }

    ob_start();
    ?>
    <article class="<?php echo esc_attr($card_class); ?>">
        <div class="mh-team-card__photo-wrap">
            <?php if (has_post_thumbnail($post_id)) : ?>
                <?php
                echo get_the_post_thumbnail($post_id, 'medium_large', [
                    'class'   => 'mh-team-card__photo',
                    'loading' => 'lazy',
                ]);
                ?>
            <?php else : ?>
                <div class="mh-team-card__photo mh-team-card__photo--placeholder" aria-hidden="true">
                    <?php echo esc_html(mh_team_get_initials($post_id)); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="mh-team-card__body">
            <div class="mh-team-card__header">
                <h3 class="mh-team-card__name"><?php echo esc_html($name); ?></h3>

                <?php if ($role) : ?>
                    <p class="mh-team-card__role"><?php echo esc_html($role); ?></p>
                <?php endif; ?>

                <?php if ($show_contact && ($email || ($phone && $phone_href))) : ?>
                    <div class="mh-team-card__contact">
                        <?php if ($email) : ?>
                            <a class="mh-team-card__contact-link" href="mailto:<?php echo esc_attr(sanitize_email($email)); ?>">
                                <?php echo esc_html($email); ?>
                            </a>
                        <?php endif; ?>

                        <?php if ($phone && $phone_href) : ?>
                            <a class="mh-team-card__contact-link mh-team-card__contact-link--phone" href="<?php echo esc_url($phone_href); ?>">
                                <?php echo esc_html($phone); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($description) : ?>
                <p class="mh-team-card__description"><?php echo esc_html($description); ?></p>
            <?php endif; ?>
        </div>
    </article>
    <?php

    return (string) ob_get_clean();
}

function mh_team_shortcode($atts = []): string {
    $atts = shortcode_atts([
        'columns'           => 3,
        'per_page'          => -1,
        'show_contact'      => 'true',
        'description_words' => 34,
        'show_partners'     => 'true',
        'partners_heading'  => 'Partners',
        'partners_columns'  => 3,
    ], $atts, 'team');

    $columns           = max(1, min(4, (int) $atts['columns']));
    $partners_columns  = max(1, min(4, (int) $atts['partners_columns']));
    $per_page          = (int) $atts['per_page'];
    $description_words = max(0, (int) $atts['description_words']);
    $show_contact      = filter_var($atts['show_contact'], FILTER_VALIDATE_BOOLEAN);
    $show_partners     = filter_var($atts['show_partners'], FILTER_VALIDATE_BOOLEAN);
    $partners_heading  = sanitize_text_field((string) $atts['partners_heading']);

    if (0 === $per_page) {
        $per_page = -1;
    }

    $team_query    = new WP_Query(mh_team_query_args_for_type('team_member', $per_page));
    $partner_query = $show_partners ? new WP_Query(mh_team_query_args_for_type('partner', -1)) : null;
    $has_team      = $team_query->have_posts();
    $has_partners  = $partner_query instanceof WP_Query && $partner_query->have_posts();

    ob_start();
    ?>
    <section class="mh-team-overview mh-team-overview--cols-<?php echo esc_attr((string) $columns); ?>" aria-label="Ons team">
        <?php if ($has_team) : ?>
            <div class="mh-team-section mh-team-section--members mh-team-section--cols-<?php echo esc_attr((string) $columns); ?>">
                <div class="mh-team-grid">
                    <?php while ($team_query->have_posts()) : $team_query->the_post(); ?>
                        <?php echo mh_team_render_member_card((int) get_the_ID(), $show_contact, $description_words); ?>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($has_partners) : ?>
            <div class="mh-team-section mh-team-section--partners mh-team-section--cols-<?php echo esc_attr((string) $partners_columns); ?>">
                <?php if ($partners_heading !== '') : ?>
                    <h2 class="mh-team-section__heading"><?php echo esc_html($partners_heading); ?></h2>
                <?php endif; ?>

                <div class="mh-team-grid">
                    <?php while ($partner_query->have_posts()) : $partner_query->the_post(); ?>
                        <?php echo mh_team_render_member_card((int) get_the_ID(), $show_contact, $description_words); ?>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!$has_team && !$has_partners) : ?>
            <div class="mh-team-empty">Er zijn nog geen teamleden of partners toegevoegd.</div>
        <?php endif; ?>
    </section>
    <?php

    wp_reset_postdata();

    return (string) ob_get_clean();
}
add_shortcode('team', 'mh_team_shortcode');
add_shortcode('mh_team', 'mh_team_shortcode');
