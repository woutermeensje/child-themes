<?php

/**
 * Content shown after job listings in `[jobs]` shortcode.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/job-listings-end.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @version     1.15.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

$get_banner_url = static function ($title) {
    $banner_posts = get_posts([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 1,
        'title'          => $title,
    ]);

    return !empty($banner_posts) ? wp_get_attachment_url($banner_posts[0]->ID) : '';
};

$marketing_blocks = [
    [
        'eyebrow'     => 'Partner van Fondsen.org',
        'title'       => 'Teammate.nl - Recruitment Specialist voor Non Profits',
        'description' => 'Teammate.nl verbindt non-profits met professionals die werk met impact zoeken.',
        'button'      => 'Contact opnemen',
        'url'         => 'https://teammate.nl/recruitment-non-profit/',
        'image_title' => 'Teammate.nl-partner',
        'gradient'    => 'linear-gradient(135deg,#055D92 0%,#0884CC 100%)',
    ],
    [
        'eyebrow'     => 'Fondsen.org platform',
        'title'       => 'Fondsenwerving platform',
        'description' => 'Plaats jouw oproep voor fondsen, donaties of geefacties op het platform van Fondsen.org.',
        'button'      => 'Account aanmaken',
        'url'         => 'https://platform.fondsen.org/registreren',
        'image_title' => 'fondsen.org-fondsenwerving',
        'gradient'    => 'linear-gradient(135deg,#FF8C2C 0%,#055D92 100%)',
    ],
];

$marketing_html = [];

foreach ($marketing_blocks as $block) {
    $banner_url = $get_banner_url($block['image_title']);
    $bg_style   = $banner_url
        ? 'background-image:url(\'' . esc_url($banner_url) . '\');background-size:cover;background-position:center top;'
        : 'background:#0884CC;';
    $banner_class = $banner_url ? 'fn-banner' : 'fn-banner fn-banner--no-image';

    $marketing_html[] = '
<div class="' . esc_attr($banner_class) . '">
    <div class="fn-banner__bg" style="' . $bg_style . '"></div>
    <div class="fn-banner__overlay"></div>
    <div class="fn-banner__content">
        <div class="fn-banner__text">
            <span class="fn-banner__eyebrow">' . esc_html($block['eyebrow']) . '</span>
            <h2 class="fn-banner__title">' . esc_html($block['title']) . '</h2>
            <p class="fn-banner__desc">' . esc_html($block['description']) . '</p>
        </div>
        <a href="' . esc_url($block['url']) . '" class="fn-banner__btn" target="_blank" rel="noopener noreferrer">
            ' . esc_html($block['button']) . '
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a>
    </div>
</div>';
}
?>
</ul>

<script>
var fnMarketingBlocks = <?php echo wp_json_encode($marketing_html); ?>;

(function () {
    if (window.__fnMarketingBlocksInit) return;
    window.__fnMarketingBlocksInit = true;

    function insertMarketingBlocks(list) {
        list.querySelectorAll('.fn-marketing-block').forEach(function (block) {
            block.remove();
        });

        var items = Array.from(list.querySelectorAll('li.job_listing:not(.fn-marketing-block)'));
        if (items.length === 0 || !Array.isArray(fnMarketingBlocks) || fnMarketingBlocks.length === 0) return;

        function makeBlock(index) {
            var block = document.createElement('li');
            block.className = 'fn-marketing-block';
            block.innerHTML = fnMarketingBlocks[index % fnMarketingBlocks.length];
            return block;
        }

        var blockIndex = 0;

        for (var i = 2; i < items.length; i += 5) {
            items[i].insertAdjacentElement('afterend', makeBlock(blockIndex));
            blockIndex++;
        }

        requestAnimationFrame(function () {
            list.querySelectorAll('.fn-marketing-block').forEach(function (block) {
                var prev = block.previousElementSibling;
                while (prev && prev.classList.contains('fn-marketing-block')) {
                    prev = prev.previousElementSibling;
                }
                if (prev) {
                    var card = prev.querySelector('.job-card');
                    if (card) {
                        block.style.height = card.offsetHeight + 'px';
                    }
                }
            });
        });
    }

    function hasRealJobChanges(mutations) {
        for (var i = 0; i < mutations.length; i++) {
            var mutation = mutations[i];
            for (var j = 0; j < mutation.addedNodes.length; j++) {
                var added = mutation.addedNodes[j];
                if (added.nodeType === 1 && !added.classList.contains('fn-marketing-block')) return true;
            }
            for (var k = 0; k < mutation.removedNodes.length; k++) {
                var removed = mutation.removedNodes[k];
                if (removed.nodeType === 1 && !removed.classList.contains('fn-marketing-block')) return true;
            }
        }
        return false;
    }

    function init() {
        var list = document.querySelector('ul.job_listings');
        if (!list) return;

        insertMarketingBlocks(list);

        var observer = new MutationObserver(function (mutations) {
            if (hasRealJobChanges(mutations)) {
                insertMarketingBlocks(list);
            }
        });
        observer.observe(list, { childList: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
</script>

<style>
li.fn-marketing-block {
    margin: 15px 30px;
    border-radius: 6px;
    list-style: none;
    padding: 0;
    overflow: hidden;
}

.fn-banner {
    position: relative;
    width: 100%;
    height: 100%;
    border-radius: 6px;
    overflow: hidden;
    display: flex;
}

.fn-banner__bg {
    position: absolute;
    inset: 0;
    background-repeat: no-repeat;
}

.fn-banner__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        115deg,
        rgba(5, 93, 146, 0.94) 0%,
        rgba(8, 132, 204, 0.86) 56%,
        rgba(255, 140, 44, 0.58) 100%
    );
}

.fn-banner--no-image .fn-banner__overlay {
    background: linear-gradient(
        115deg,
        rgba(5, 93, 146, 0.62) 0%,
        rgba(8, 132, 204, 0.26) 100%
    );
}

.fn-banner__content {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 34px;
    padding: 40px 50px;
    width: 100%;
}

.fn-banner__text {
    flex: 1;
    min-width: 0;
}

.fn-banner__eyebrow {
    display: inline-block;
    font-family: 'Inter', sans-serif !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    letter-spacing: 0;
    text-transform: none;
    color: #FFE0C0 !important;
    margin-bottom: 12px;
}

.fn-banner__title {
    font-family: 'Inter', sans-serif !important;
    font-size: 25px !important;
    font-weight: 700 !important;
    color: #ffffff !important;
    margin: 0 0 12px !important;
    line-height: 1.25 !important;
}

.fn-banner__desc {
    font-family: 'Poppins', sans-serif !important;
    font-size: 14px !important;
    font-weight: 400 !important;
    color: rgba(255, 255, 255, 0.88) !important;
    margin: 0 !important;
    line-height: 1.65 !important;
    max-width: 560px;
}

.fn-banner__btn {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: var(--color-primary, #FF8C2C) !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 6px !important;
    padding: 14px 26px !important;
    font-family: 'Poppins', sans-serif !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    white-space: nowrap;
    box-shadow: 0 4px 20px rgba(0, 0, 0, .18) !important;
    transition: opacity .15s ease, transform .15s ease, background .15s ease;
}

.fn-banner__btn:hover {
    opacity: 0.92;
    transform: translateY(-1px);
    background: var(--color-primary-dk, #E47012) !important;
    color: #ffffff !important;
}

@media (max-width: 960px) {
    li.fn-marketing-block {
        margin: 0 12px;
        height: auto !important;
    }

    .fn-banner__content {
        flex-direction: column;
        align-items: flex-start;
        gap: 24px;
        padding: 32px 28px;
    }

    .fn-banner__title {
        font-size: 22px !important;
    }

    .fn-banner__desc {
        max-width: 100%;
    }

    .fn-banner__btn {
        width: auto;
        max-width: 100%;
        align-self: flex-start;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    li.fn-marketing-block {
        margin: 0 12px;
        height: auto !important;
    }

    .fn-banner__content {
        padding: 24px 20px;
        gap: 20px;
    }

    .fn-banner__eyebrow {
        font-size: 11px !important;
    }

    .fn-banner__title {
        font-size: 19px !important;
        margin: 0 0 10px !important;
    }

    .fn-banner__desc {
        font-size: 13px !important;
    }

    .fn-banner__btn {
        font-size: 14px !important;
        padding: 12px 20px !important;
    }
}
</style>
