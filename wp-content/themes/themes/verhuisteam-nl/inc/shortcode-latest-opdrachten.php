<?php
if (!defined('ABSPATH')) exit;

/**
 * Theme shortcode: [si_latest_opdrachten]
 *
 * Verwacht een externe JSON-feed vanuit de Laravel-app.
 * Je kunt het endpoint meegeven via:
 * - shortcode attribuut: feed_url=""
 * - filter: si_latest_opdrachten_endpoint
 *
 * Voorbeeld feed-item:
 * {
 *   "title": "Content marketeer gezocht",
 *   "url": "https://platform.verhuisteam.nl/opdrachten/content-marketeer",
 *   "excerpt": "Project voor 3 maanden...",
 *   "category": "Marketing",
 *   "date": "2026-03-27T10:15:00+01:00"
 * }
 */
add_shortcode('si_latest_opdrachten', function ($atts) {
    $atts = shortcode_atts([
        'limit'        => 8,
        'title'        => 'De laatste opdrachten in ons netwerk',
        'feed_url'     => '',
        'view_all_url' => 'https://platform.verhuisteam.nl/opdrachten/',
        'cache_ttl'    => 300,
    ], $atts, 'si_latest_opdrachten');

    $limit        = max(1, (int) $atts['limit']);
    $title        = sanitize_text_field($atts['title']);
    $view_all_url = esc_url_raw($atts['view_all_url']);
    $cache_ttl    = max(60, (int) $atts['cache_ttl']);

    $feed_url = !empty($atts['feed_url']) ? esc_url_raw($atts['feed_url']) : '';
    $feed_url = apply_filters('si_latest_opdrachten_endpoint', $feed_url, $atts);

    $items = [];
    $error_message = '';

    if ($feed_url) {
        $cache_key = 'si_latest_opdrachten_' . md5($feed_url . '|' . $limit);
        $cached_items = get_transient($cache_key);

        if (is_array($cached_items)) {
            $items = $cached_items;
        } else {
            $response = wp_remote_get($feed_url, [
                'timeout' => 12,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
            } else {
                $status_code = (int) wp_remote_retrieve_response_code($response);
                $body        = wp_remote_retrieve_body($response);
                $decoded     = json_decode($body, true);

                if ($status_code >= 200 && $status_code < 300 && is_array($decoded)) {
                    $items = si_latest_opdrachten_normalize_items($decoded, $limit);
                    set_transient($cache_key, $items, $cache_ttl);
                } else {
                    $error_message = 'De opdrachtenfeed gaf geen geldige JSON terug.';
                }
            }
        }
    } else {
        $error_message = 'Er is nog geen feed_url ingesteld voor de opdrachtenfeed.';
    }

    ob_start();
    ?>
    <section class="si-latest-strip">
        <div class="si-latest-strip__head">
            <h2 class="si-latest-strip__title"><?php echo esc_html($title); ?></h2>

            <?php if ($view_all_url): ?>
                <a class="si-latest-strip__all" href="<?php echo esc_url($view_all_url); ?>">Bekijk alles</a>
            <?php endif; ?>
        </div>

        <?php if (!empty($items)): ?>
            <div class="si-latest-strip__scroller" role="list">
                <?php foreach ($items as $item): ?>
                    <a class="si-latest-card" href="<?php echo esc_url($item['url']); ?>" role="listitem">
                        <div class="si-latest-card__top">
                            <div class="si-latest-card__date"><?php echo esc_html($item['date_label']); ?></div>

                            <?php if (!empty($item['category'])): ?>
                                <div class="si-latest-card__cat"><?php echo esc_html($item['category']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="si-latest-card__title"><?php echo esc_html($item['title']); ?></div>

                        <?php if (!empty($item['excerpt'])): ?>
                            <div class="si-latest-card__excerpt"><?php echo esc_html($item['excerpt']); ?></div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="si-latest-strip__empty">
                Er konden nu geen recente opdrachten worden geladen.
                <?php if (current_user_can('manage_options') && $error_message): ?>
                    <br><small><?php echo esc_html($error_message); ?></small>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>

    <style>
        .si-latest-strip{
            max-width: 1050px;
            margin: 0 auto;
            padding: 18px 0;
        }

        .si-latest-strip__head{
            display:flex;
            align-items:center;
            justify-content: space-between;
            gap: 12px;
            margin: 0 0 12px 0;
        }

        .si-latest-strip__title{
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            color: #111827;
        }

        .si-latest-strip__all{
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: #7C5CFA;
            text-decoration: none;
        }

        .si-latest-strip__scroller{
            display: flex;
            gap: 14px;
            overflow-x: auto;
            padding: 6px 2px 12px 2px;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
        }

        .si-latest-card{
            flex: 0 0 300px;
            max-width: 300px;
            scroll-snap-align: start;
            display: block;
            text-decoration: none !important;
            color: inherit;
            background: #fff;
            border: 1px solid #DEDEDE;
            border-radius: 8px;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.15);
            padding: 14px 14px 12px 14px;
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .si-latest-card:hover{
            transform: translateY(-1px);
            box-shadow: 0 14px 34px -12px rgba(0,0,0,0.20);
        }

        .si-latest-card__top{
            display:flex;
            align-items:center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 10px;
        }

        .si-latest-card__date{
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            color: #6B7280;
            white-space: nowrap;
        }

        .si-latest-card__cat{
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            font-weight: 700;
            color: #7C5CFA;
            border: 1px solid #7C5CFA;
            background: rgba(124, 92, 250, 0.12);
            border-radius: 6px;
            padding: 6px 10px;
            white-space: nowrap;
            background-clip: padding-box;
        }

        .si-latest-card__title{
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            line-height: 1.35;
            margin: 0 0 6px 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 40px;
        }

        .si-latest-card__excerpt{
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 400;
            color: #374151;
        }

        .si-latest-strip__empty{
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #374151;
            border: 1px dashed #D1D5DB;
            background: #F9FAFB;
            border-radius: 10px;
            padding: 14px 16px;
        }

        @media (max-width: 480px){
            .si-latest-card{
                flex-basis: 260px;
                max-width: 260px;
            }
        }
    </style>
    <?php

    return ob_get_clean();
});

function si_latest_opdrachten_normalize_items(array $payload, int $limit): array
{
    $items = $payload;

    if (isset($payload['data']) && is_array($payload['data'])) {
        $items = $payload['data'];
    } elseif (isset($payload['items']) && is_array($payload['items'])) {
        $items = $payload['items'];
    } elseif (isset($payload['opdrachten']) && is_array($payload['opdrachten'])) {
        $items = $payload['opdrachten'];
    }

    $normalized = [];

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $title = $item['title'] ?? $item['naam'] ?? $item['functie'] ?? '';
        $url   = $item['url'] ?? $item['link'] ?? $item['slug_url'] ?? '';

        if (!$title || !$url) {
            continue;
        }

        $excerpt = $item['excerpt'] ?? $item['description'] ?? $item['samenvatting'] ?? '';
        $excerpt = wp_trim_words(wp_strip_all_tags((string) $excerpt), 12, '…');

        $category = $item['category'] ?? $item['categorie'] ?? $item['vakgebied'] ?? '';
        $date_raw = $item['date'] ?? $item['published_at'] ?? $item['created_at'] ?? '';

        $normalized[] = [
            'title'      => wp_strip_all_tags((string) $title),
            'url'        => esc_url_raw((string) $url),
            'excerpt'    => $excerpt,
            'category'   => wp_strip_all_tags((string) $category),
            'date_label' => si_latest_opdrachten_format_date($date_raw),
            'date_sort'  => strtotime((string) $date_raw) ?: 0,
        ];
    }

    usort($normalized, static function ($a, $b) {
        return $b['date_sort'] <=> $a['date_sort'];
    });

    return array_slice($normalized, 0, $limit);
}

function si_latest_opdrachten_format_date(string $date_raw): string
{
    if (!$date_raw) {
        return '';
    }

    $timestamp = strtotime($date_raw);
    if (!$timestamp) {
        return '';
    }

    return wp_date('j F Y', $timestamp);
}
