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
?>
</ul>

<div class="sj-below-listings" id="sj-below-listings">
    <?php echo do_shortcode('[job-alerts]'); ?>
</div>

<script>
(function () {
    if (window.__sjMarketingBlocksInit) return;
    window.__sjMarketingBlocksInit = true;

    function insertMarketingBlocks(list) {
        list.querySelectorAll('.sj-marketing-block').forEach(function (b) { b.remove(); });
        var items = Array.from(list.querySelectorAll('li.job_listing:not(.sj-marketing-block)'));
        for (var i = 6; i < items.length; i += 7) {
            var block = document.createElement('li');
            block.className = 'sj-marketing-block';
            items[i].insertAdjacentElement('afterend', block);
        }
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
li.sj-marketing-block {
    background: #ffffff;
    border-radius: 6px;
    border: 1px solid #DEDEDE;
    min-height: 200px;
    list-style: none;
    padding: 0;
}

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

.sj-below-listings {
    max-width: 1140px;
    margin: 32px auto 0;
    padding: 0 30px;
}
</style>
