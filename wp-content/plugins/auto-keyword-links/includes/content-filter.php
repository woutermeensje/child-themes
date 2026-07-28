<?php
if (!defined('ABSPATH')) exit;

add_filter('the_content', 'akl_auto_link_keywords', 20);

/**
 * Linkt het eerste voorkomen van elk ingesteld keyword in de post content.
 * Slaat tekst binnen bestaande <a>, <script> en <style> tags over, en
 * linkt niet naar de pagina die je op dat moment al aan het bekijken bent.
 */
function akl_auto_link_keywords($content) {
    if (is_admin() || !is_singular() || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    $links = get_option('akl_keyword_links', []);
    if (empty($links)) {
        return $content;
    }

    $current_url = untrailingslashit(get_permalink());
    $links = array_filter($links, function ($link) use ($current_url) {
        return untrailingslashit($link['url']) !== $current_url;
    });
    if (empty($links)) {
        return $content;
    }

    $used = array_fill_keys(array_keys($links), false);
    $skip_tags = ['a', 'script', 'style'];
    $current_skip = null;

    $pieces = preg_split('/(<[^>]+>)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

    foreach ($pieces as &$piece) {
        if ($piece === '') {
            continue;
        }

        if ($piece[0] === '<') {
            if (preg_match('#^<(/?)([a-z0-9]+)#i', $piece, $tag_match)) {
                $is_closing = $tag_match[1] === '/';
                $tag = strtolower($tag_match[2]);

                if (in_array($tag, $skip_tags, true)) {
                    if ($is_closing && $current_skip === $tag) {
                        $current_skip = null;
                    } elseif (!$is_closing && $current_skip === null) {
                        $current_skip = $tag;
                    }
                }
            }
            continue;
        }

        if ($current_skip !== null) {
            continue;
        }

        foreach ($links as $i => $link) {
            if ($used[$i]) {
                continue;
            }

            $pattern = '/\b(' . preg_quote($link['keyword'], '/') . ')\b/iu';
            $new_piece = preg_replace_callback($pattern, function ($m) use ($link) {
                return '<a href="' . esc_url($link['url']) . '">' . $m[1] . '</a>';
            }, $piece, 1, $count);

            if ($count > 0) {
                $piece = $new_piece;
                $used[$i] = true;
            }
        }
    }
    unset($piece);

    return implode('', $pieces);
}
