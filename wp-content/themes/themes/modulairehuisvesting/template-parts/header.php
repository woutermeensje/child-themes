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

    <!-- Blauwe topbalk -->
    <div class="mh-topbar">
        <div class="mh-topbar__inner">
            <div class="mh-topbar__left">
                <a href="<?php echo esc_url(home_url('/nieuw/')); ?>">Nieuw</a>
                <span class="mh-topbar__divider">|</span>
                <a href="<?php echo esc_url(home_url('/gebruikt/')); ?>">Gebruikt</a>
            </div>
            <div class="mh-topbar__right">
                <a href="tel:0852392040">085 239 2040</a>
                <span class="mh-topbar__divider">|</span>
                <a href="mailto:informatie@modulairehuisvesting.nl">informatie@modulairehuisvesting.nl</a>
            </div>
        </div>
    </div>

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

        <div class="mh-header__divider"></div>

        <div class="mh-header__cta">
            <a href="<?php echo esc_url(home_url('/mijn-offerte/')); ?>" class="mh-btn mh-btn--outline mh-btn--has-badge mh-btn--nav-quote">
                <span>Mijn offerte</span>
                <?php if ($quote_count > 0) : ?>
                    <span class="mh-nav-badge" aria-label="<?php echo esc_attr($quote_count); ?> producten in offerte"><?php echo esc_html($quote_count); ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="mh-btn mh-btn--accent mh-btn--nav-contact">Contact opnemen</a>
        </div>

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
                    <span class="mh-nav-badge" aria-label="<?php echo esc_attr($quote_count); ?> producten in offerte"><?php echo esc_html($quote_count); ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="mh-btn mh-btn--accent mh-btn--nav-contact mh-mobile-nav__cta">Contact opnemen</a>
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

    if (hamburger && mobileNav) {
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
        };

        hamburger.addEventListener('click', () => {
            mobileNav.classList.contains('is-open') ? closeMenu() : openMenu();
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
