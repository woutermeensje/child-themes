<?php
if (!defined('ABSPATH')) {
    exit;
}

$quote_count = function_exists('mh_quote_get_count') ? (int) mh_quote_get_count() : 0;
$logo_rel_path = '/site-logo/Modulaire-huisvesting-logo.svg';
$logo_abs_path = get_stylesheet_directory() . $logo_rel_path;
$logo_url      = get_stylesheet_directory_uri() . $logo_rel_path;

?>

<header id="mh-header" class="mh-header" role="banner">

    <div class="mh-header__inner">
        <div class="mh-header__brand">
            <?php if ( file_exists( $logo_abs_path ) ) : ?>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="mh-header__logo-link" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
                    <img
                        src="<?php echo esc_url( $logo_url ); ?>"
                        alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
                        class="mh-header__logo-image"
                    />
                </a>
            <?php else : ?>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="mh-header__logo-link">
                    <span class="mh-header__site-name"><?php bloginfo('name'); ?></span>
                </a>
            <?php endif; ?>
        </div>

        <nav class="mh-header__nav" aria-label="Primaire navigatie">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary_nav',
                'container'      => false,
                'menu_class'     => 'mh-nav__list',
                'fallback_cb'    => false,
                'walker'         => new MH_Nav_Walker(),
            ]);
            ?>
        </nav>

        <div class="mh-header__search mh-header-search">
            <button type="button" class="mh-header-search__toggle" aria-label="Zoek units" aria-expanded="false" aria-controls="mh-header-search-form">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>
            <form id="mh-header-search-form" class="mh-header-search__form" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <input type="hidden" name="post_type" value="mh_unit">
                <label class="screen-reader-text" for="mh-header-search-input">Zoek units</label>
                <input id="mh-header-search-input" class="mh-header-search__input" type="search" name="s" placeholder="Zoek units">
                <button type="submit" class="mh-header-search__submit">Zoeken</button>
            </form>
        </div>

        <div class="mh-header__divider"></div>

        <div class="mh-header__cta">
            <a href="<?php echo esc_url(home_url('/mijn-offerte/')); ?>" class="mh-btn mh-btn--outline mh-btn--has-badge mh-btn--nav-quote">
                <span>Mijn offerte</span>
                <?php if ($quote_count > 0) : ?>
                    <span class="mh-nav-badge" aria-label="<?php echo esc_attr($quote_count); ?> items in offerte"><?php echo esc_html($quote_count); ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo esc_url(home_url('/offerte-aanvragen/')); ?>" class="mh-btn mh-btn--accent mh-btn--nav-contact">Informatie aanvragen</a>
        </div>

        <div class="mh-header__mobile-search mh-header-search">
            <button type="button" class="mh-header-search__toggle mh-header__mobile-search-toggle" aria-label="Zoek units" aria-expanded="false" aria-controls="mh-header-mobile-search-form">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>
            <form id="mh-header-mobile-search-form" class="mh-header-search__form mh-header__mobile-search-form" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <input type="hidden" name="post_type" value="mh_unit">
                <label class="screen-reader-text" for="mh-header-mobile-search-input">Zoek units</label>
                <input id="mh-header-mobile-search-input" class="mh-header-search__input" type="search" name="s" placeholder="Zoek units">
                <button type="submit" class="mh-header-search__submit">Zoeken</button>
            </form>
        </div>

        <a href="<?php echo esc_url(home_url('/mijn-offerte/')); ?>" class="mh-header__quote-icon" aria-label="Mijn offerte">
            <svg class="mh-header__quote-icon-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M5 10h14" />
                <path d="M6 10l1.25 8.5A2 2 0 0 0 9.23 20h5.54a2 2 0 0 0 1.98-1.5L18 10" />
                <path d="M9 10V8a3 3 0 0 1 6 0v2" />
            </svg>
            <?php if ($quote_count > 0) : ?>
                <span class="mh-header__quote-badge" aria-label="<?php echo esc_attr($quote_count); ?> items in offerte"><?php echo esc_html($quote_count); ?></span>
            <?php endif; ?>
        </a>

        <button class="mh-header__hamburger" aria-label="Menu openen" aria-expanded="false" aria-controls="mh-mobile-nav">
            <span class="mh-hamburger__bar"></span>
            <span class="mh-hamburger__bar"></span>
            <span class="mh-hamburger__bar"></span>
        </button>
    </div>
</header>

<div id="mh-mobile-nav" class="mh-mobile-nav" aria-hidden="true">
    <div class="mh-mobile-nav__panel">
        <button class="mh-mobile-nav__close" aria-label="Menu sluiten">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>

        <?php
        wp_nav_menu([
            'theme_location' => 'primary_nav',
            'container'      => false,
            'menu_class'     => 'mh-mobile-nav__list',
            'fallback_cb'    => false,
        ]);
        ?>

        <div class="mh-mobile-nav__divider"></div>
        <div class="mh-mobile-nav__ctas">
            <a href="<?php echo esc_url(home_url('/mijn-offerte/')); ?>" class="mh-btn mh-btn--outline mh-btn--has-badge mh-btn--nav-quote mh-mobile-nav__cta">
                <span>Mijn offerte</span>
                <?php if ($quote_count > 0) : ?>
                    <span class="mh-nav-badge" aria-label="<?php echo esc_attr($quote_count); ?> items in offerte"><?php echo esc_html($quote_count); ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo esc_url(home_url('/offerte-aanvragen/')); ?>" class="mh-btn mh-btn--accent mh-btn--nav-contact mh-mobile-nav__cta">Informatie aanvragen</a>
        </div>
    </div>
</div>

<!-- Sticky bottom CTA balk -->
<div class="mh-cta-bar" id="mh-cta-bar" role="complementary" aria-label="Hulp nodig?">
    <div class="mh-cta-bar__inner">
        <p class="mh-cta-bar__text">
            Laat één van onze adviseurs jou helpen in het uitzoeken van jouw modulaire huisvesting.
        </p>
        <div class="mh-cta-bar__actions">
            <a href="<?php echo esc_url(home_url('/offerte-aanvragen/')); ?>" class="mh-btn mh-btn--accent mh-cta-bar__btn">
                Neem contact op
            </a>
        </div>
        <button class="mh-cta-bar__close" id="mh-cta-bar-close" aria-label="Balk sluiten">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
</div>

<script>
(function () {
    // ── Mobile nav ────────────────────────────────────────────
    const hamburger = document.querySelector('.mh-header__hamburger');
    const mobileNav = document.getElementById('mh-mobile-nav');
    const closeBtn  = document.querySelector('.mh-mobile-nav__close');
    const searchBlocks = Array.from(document.querySelectorAll('.mh-header-search')).map((wrap) => ({
        wrap,
        toggle: wrap.querySelector('.mh-header-search__toggle'),
        form: wrap.querySelector('.mh-header-search__form'),
        input: wrap.querySelector('.mh-header-search__input')
    })).filter((block) => block.toggle && block.form);

    if (hamburger && mobileNav) {
        const closeSearch = (targetBlock = null) => {
            searchBlocks.forEach((block) => {
                if (targetBlock && block !== targetBlock) {
                    return;
                }
                block.form.classList.remove('is-open');
                block.toggle.setAttribute('aria-expanded', 'false');
            });
        };

        const openSearch = (targetBlock) => {
            if (!targetBlock) return;
            closeSearch();
            targetBlock.form.classList.add('is-open');
            targetBlock.toggle.setAttribute('aria-expanded', 'true');
            if (targetBlock.input) {
                setTimeout(() => targetBlock.input.focus(), 50);
            }
        };

        const openMenu = () => {
            mobileNav.classList.add('is-open');
            hamburger.classList.add('is-open');
            hamburger.setAttribute('aria-expanded', 'true');
            mobileNav.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        };

        const closeMenu = () => {
            mobileNav.classList.remove('is-open');
            hamburger.classList.remove('is-open');
            hamburger.setAttribute('aria-expanded', 'false');
            mobileNav.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            closeSearch();
        };

        hamburger.addEventListener('click', () => {
            mobileNav.classList.contains('is-open') ? closeMenu() : openMenu();
        });

        searchBlocks.forEach((block) => {
            block.toggle.addEventListener('click', () => {
                if (block.form.classList.contains('is-open')) {
                    closeSearch(block);
                    return;
                }
                openSearch(block);
            });
        });

        if (closeBtn) closeBtn.addEventListener('click', closeMenu);

        mobileNav.addEventListener('click', (e) => {
            if (!e.target.closest('.mh-mobile-nav__panel')) closeMenu();
        });

        mobileNav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', closeMenu);
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeMenu();
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 960) closeMenu();
        });
    }

    // ── Sticky CTA balk ───────────────────────────────────────
    const ctaBar   = document.getElementById('mh-cta-bar');
    const ctaClose = document.getElementById('mh-cta-bar-close');

    if (ctaBar && ctaClose) {
        // Verborgen houden als eerder weggeklikt
        if (sessionStorage.getItem('mh_cta_bar_closed') === '1') {
            ctaBar.classList.add('is-hidden');
        }

        ctaClose.addEventListener('click', () => {
            ctaBar.classList.add('is-hidden');
            sessionStorage.setItem('mh_cta_bar_closed', '1');
        });
    }
})();
</script>
