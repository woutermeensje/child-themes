<?php
if (!defined('ABSPATH')) exit;

$banner_posts = get_posts([
    'post_type'      => 'attachment',
    'post_status'    => 'inherit',
    'posts_per_page' => 1,
    'title'          => 'recruiternext-marketing-blok',
]);
$banner_url = !empty($banner_posts) ? wp_get_attachment_url($banner_posts[0]->ID) : '';

$bg_style = $banner_url
    ? 'background-image:url(\'' . esc_url($banner_url) . '\');background-size:cover;background-position:center;'
    : 'background:linear-gradient(135deg,#0458ab 0%,#2f7fd6 100%);';

$banner_html = '
<div class="rn-banner">
    <div class="rn-banner__bg" style="' . $bg_style . '"></div>
    <div class="rn-banner__overlay"></div>
    <div class="rn-banner__content">
        <div class="rn-banner__text">
            <span class="rn-banner__eyebrow">Recruiternext.nl</span>
            <h2 class="rn-banner__title">Vind opdrachten als<br>freelance recruiter.</h2>
            <p class="rn-banner__desc">Dé plek waar opdrachtgevers en freelance recruiters elkaar vinden. Maak een gratis profiel aan en word gevonden.</p>
        </div>
        <a href="' . esc_url(home_url('/account-aanmaken/')) . '" class="rn-banner__btn">
            Profiel aanmaken
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a>
    </div>
</div>';
?>
</ul>

<script>
var rnBannerHtml = <?php echo wp_json_encode($banner_html); ?>;

(function () {
    if (window.__rnBannerInit) return;
    window.__rnBannerInit = true;

    function insertBanners(list) {
        list.querySelectorAll('.rn-marketing-block').forEach(function (b) { b.remove(); });
        var items = Array.from(list.querySelectorAll('li.job_listing:not(.rn-marketing-block)'));
        if (items.length === 0) return;

        function makeBlock() {
            var block = document.createElement('li');
            block.className = 'rn-marketing-block';
            block.innerHTML = rnBannerHtml;
            return block;
        }

        // Eerste blok na 3e vacature, daarna om de 7 vacatures
        for (var i = 2; i < items.length; i += 7) {
            items[i].insertAdjacentElement('afterend', makeBlock());
        }

        requestAnimationFrame(function () {
            list.querySelectorAll('.rn-marketing-block').forEach(function (block) {
                var prev = block.previousElementSibling;
                while (prev && prev.classList.contains('rn-marketing-block')) {
                    prev = prev.previousElementSibling;
                }
                if (prev) {
                    var card = prev.querySelector('.job-card');
                    if (card) block.style.height = card.offsetHeight + 'px';
                }
            });
        });
    }

    function hasRealJobChanges(mutations) {
        for (var i = 0; i < mutations.length; i++) {
            var m = mutations[i];
            for (var j = 0; j < m.addedNodes.length; j++) {
                var n = m.addedNodes[j];
                if (n.nodeType === 1 && !n.classList.contains('rn-marketing-block')) return true;
            }
            for (var j = 0; j < m.removedNodes.length; j++) {
                var n = m.removedNodes[j];
                if (n.nodeType === 1 && !n.classList.contains('rn-marketing-block')) return true;
            }
        }
        return false;
    }

    function init() {
        var list = document.querySelector('ul.job_listings');
        if (!list) return;

        insertBanners(list);

        new MutationObserver(function (mutations) {
            if (hasRealJobChanges(mutations)) insertBanners(list);
        }).observe(list, { childList: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
</script>

<style>
/* ── Marketing banner ─────────────────────────────────────── */
li.rn-marketing-block {
    margin: 15px 30px;
    border-radius: 6px;
    list-style: none;
    padding: 0;
    overflow: hidden;
}

.rn-banner {
    position: relative;
    width: 100%;
    height: 100%;
    border-radius: 6px;
    overflow: hidden;
    display: flex;
}

.rn-banner__bg {
    position: absolute;
    inset: 0;
    background-repeat: no-repeat;
    background-size: cover;
    background-position: center;
}

.rn-banner__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        115deg,
        rgba(4, 88, 171, 0.92) 0%,
        rgba(3, 68, 131, 0.78) 55%,
        rgba(3, 68, 131, 0.55) 100%
    );
}

.rn-banner__content {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 40px;
    padding: 44px 56px;
    width: 100%;
}

.rn-banner__text {
    flex: 1;
    min-width: 0;
}

.rn-banner__eyebrow {
    display: inline-block;
    font-family: 'Poppins', sans-serif !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--color-secondary, #FFFACD) !important;
    margin-bottom: 12px;
}

.rn-banner__title {
    font-family: 'Inter', sans-serif !important;
    font-size: 26px !important;
    font-weight: 700 !important;
    color: #ffffff !important;
    margin: 0 0 14px !important;
    line-height: 1.25 !important;
}

.rn-banner__desc {
    font-family: 'Poppins', sans-serif !important;
    font-size: 14px !important;
    font-weight: 400 !important;
    color: rgba(255, 255, 255, 0.85) !important;
    margin: 0 !important;
    line-height: 1.7 !important;
    max-width: 500px;
}

.rn-banner__btn {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: var(--color-secondary, #FFFACD) !important;
    color: #0458ab !important;
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

.rn-banner__btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
    background: var(--color-secondary, #FFFACD) !important;
    color: #0458ab !important;
}

@media (max-width: 960px) {
    li.rn-marketing-block {
        margin: 0 12px;
        height: auto !important;
    }
    .rn-banner__content {
        flex-direction: column;
        align-items: flex-start;
        gap: 24px;
        padding: 32px 28px;
    }
    .rn-banner__title {
        font-size: 22px !important;
    }
    .rn-banner__desc {
        max-width: 100%;
    }
    .rn-banner__btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    li.rn-marketing-block {
        margin: 0 12px;
        height: auto !important;
    }
    .rn-banner__content {
        padding: 24px 20px;
        gap: 20px;
    }
    .rn-banner__eyebrow {
        font-size: 11px !important;
    }
    .rn-banner__title {
        font-size: 19px !important;
        margin: 0 0 10px !important;
    }
    .rn-banner__desc {
        font-size: 13px !important;
    }
    .rn-banner__btn {
        font-size: 14px !important;
        padding: 12px 20px !important;
    }
}
</style>
