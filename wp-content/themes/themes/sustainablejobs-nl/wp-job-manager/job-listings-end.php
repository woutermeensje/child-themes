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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Zoek de banner afbeelding op naam in de mediabibliotheek.
$banner_posts = get_posts([
    'post_type'      => 'attachment',
    'post_status'    => 'inherit',
    'posts_per_page' => 1,
    'title'          => 'Sustainablejobs.nl-banner-image-1',
]);
$banner_url = !empty($banner_posts) ? wp_get_attachment_url($banner_posts[0]->ID) : '';

$bg_style = $banner_url
    ? 'background-image:url(\'' . esc_url($banner_url) . '\');background-size:cover;background-position:center;'
    : 'background:linear-gradient(135deg,#254F6E 0%,#168AAD 100%);';

$banner_html = '
<div class="sj-banner">
    <div class="sj-banner__bg" style="' . $bg_style . '"></div>
    <div class="sj-banner__overlay"></div>
    <div class="sj-banner__content">
        <div class="sj-banner__text">
            <span class="sj-banner__eyebrow">Uitgelichte werkgevers</span>
            <h2 class="sj-banner__title">Werk bij organisaties<br>die het verschil maken.</h2>
            <p class="sj-banner__desc">Ontdek vacatures van de meest duurzame en maatschappelijk betrokken werkgevers van Nederland. Organisaties die vooroplopen in de transitie naar een betere wereld.</p>
        </div>
        <a href="/uitgelichte-werkgevers/" class="sj-banner__btn">
            Bekijk werkgevers
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a>
    </div>
</div>';
?>
</ul>

<div class="sj-below-listings" id="sj-below-listings">
    <?php echo do_shortcode('[job-alerts]'); ?>
</div>

<script>
var sjBannerHtml = <?php echo wp_json_encode($banner_html); ?>;

(function () {
    if (window.__sjMarketingBlocksInit) return;
    window.__sjMarketingBlocksInit = true;

    function insertMarketingBlocks(list) {
        list.querySelectorAll('.sj-marketing-block').forEach(function (b) { b.remove(); });
        var items = Array.from(list.querySelectorAll('li.job_listing:not(.sj-marketing-block)'));
        for (var i = 2; i < items.length; i += 10) {
            var block = document.createElement('li');
            block.className = 'sj-marketing-block';
            block.innerHTML = sjBannerHtml;
            items[i].insertAdjacentElement('afterend', block);
        }
        requestAnimationFrame(function () {
            list.querySelectorAll('.sj-marketing-block').forEach(function (block) {
                var prev = block.previousElementSibling;
                while (prev && prev.classList.contains('sj-marketing-block')) {
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

    function positionBelowListings() {
        var below = document.getElementById('sj-below-listings');
        var loadMore = document.querySelector('a.load_more_jobs');
        if (below && loadMore && loadMore.nextSibling !== below) {
            loadMore.parentNode.insertBefore(below, loadMore.nextSibling);
        }
    }

    function hasRealJobChanges(mutations) {
        for (var i = 0; i < mutations.length; i++) {
            var m = mutations[i];
            for (var j = 0; j < m.addedNodes.length; j++) {
                var n = m.addedNodes[j];
                if (n.nodeType === 1 && !n.classList.contains('sj-marketing-block')) return true;
            }
            for (var j = 0; j < m.removedNodes.length; j++) {
                var n = m.removedNodes[j];
                if (n.nodeType === 1 && !n.classList.contains('sj-marketing-block')) return true;
            }
        }
        return false;
    }

    function init() {
        var list = document.querySelector('ul.job_listings');
        if (!list) return;

        insertMarketingBlocks(list);
        positionBelowListings();

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
/* ── Marketing block ─────────────────────────────────────── */
li.sj-marketing-block {
    margin: 0 30px;
    border-radius: 6px;
    list-style: none;
    padding: 0;
    overflow: hidden;
}

.sj-banner {
    position: relative;
    width: 100%;
    height: 100%;
    border-radius: 6px;
    overflow: hidden;
    display: flex;
}

.sj-banner__bg {
    position: absolute;
    inset: 0;
    background-repeat: no-repeat;
}

.sj-banner__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        115deg,
        rgba(37, 79, 110, 0.92) 0%,
        rgba(22, 138, 173, 0.78) 55%,
        rgba(22, 138, 173, 0.55) 100%
    );
}

.sj-banner__content {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 40px;
    padding: 44px 56px;
    width: 100%;
}

.sj-banner__text {
    flex: 1;
    min-width: 0;
}

.sj-banner__eyebrow {
    display: inline-block;
    font-family: 'Poppins', sans-serif !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--color-secondary, #C5E4A0) !important;
    margin-bottom: 12px;
}

.sj-banner__title {
    font-family: 'Inter', sans-serif !important;
    font-size: 26px !important;
    font-weight: 700 !important;
    color: #ffffff !important;
    margin: 0 0 14px !important;
    line-height: 1.25 !important;
}

.sj-banner__desc {
    font-family: 'Poppins', sans-serif !important;
    font-size: 14px !important;
    font-weight: 400 !important;
    color: rgba(255, 255, 255, 0.85) !important;
    margin: 0 !important;
    line-height: 1.7 !important;
    max-width: 500px;
}

.sj-banner__btn {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: var(--color-secondary, #C5E4A0) !important;
    color: var(--color-midnight-blue, #254F6E) !important;
    border: none !important;
    border-radius: 6px !important;
    padding: 14px 28px !important;
    font-family: 'Poppins', sans-serif !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    white-space: nowrap;
    box-shadow: 0 4px 20px rgba(0,0,0,.18) !important;
    transition: opacity .15s ease, transform .15s ease;
}

.sj-banner__btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
    background: var(--color-secondary, #C5E4A0) !important;
    color: var(--color-midnight-blue, #254F6E) !important;
}

/* ── Marketing block — mobiel ────────────────────────────── */
@media (max-width: 960px) {
    li.sj-marketing-block {
        margin: 0 12px;
    }

    .sj-banner__content {
        flex-direction: column;
        align-items: flex-start;
        gap: 24px;
        padding: 32px 28px;
    }

    .sj-banner__title {
        font-size: 22px !important;
    }

    .sj-banner__desc {
        max-width: 100%;
    }

    .sj-banner__btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    li.sj-marketing-block {
        margin: 0 12px;
    }

    .sj-banner__content {
        padding: 24px 20px;
        gap: 20px;
    }

    .sj-banner__eyebrow {
        font-size: 11px !important;
    }

    .sj-banner__title {
        font-size: 19px !important;
        margin: 0 0 10px !important;
    }

    .sj-banner__desc {
        font-size: 13px !important;
    }

    .sj-banner__btn {
        font-size: 14px !important;
        padding: 12px 20px !important;
    }
}

/* ── Load more button ────────────────────────────────────── */
a.load_more_jobs {
    display: block;
    text-align: center;
    margin: 8px 30px 0;
    padding: 14px 32px;
    background: transparent !important;
    border: 2px solid var(--color-primary, #2C8FAF) !important;
    border-radius: 5px;
    color: var(--color-primary, #2C8FAF) !important;
    font-family: 'Poppins', sans-serif !important;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none !important;
    transition: background .18s ease, color .18s ease;
}

a.load_more_jobs:hover {
    background: var(--color-primary, #2C8FAF) !important;
    color: #ffffff !important;
}

a.load_more_jobs strong {
    font-weight: 600;
}

/* ── Job alerts sectie ───────────────────────────────────── */
.sj-below-listings {
    max-width: 1140px;
    margin: 32px auto 0;
    padding: 0 30px;
}
</style>
