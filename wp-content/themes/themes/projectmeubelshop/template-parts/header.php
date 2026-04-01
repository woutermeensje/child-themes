<?php
if (!defined('ABSPATH')) {
    exit;
}

$quote_count = function_exists('pms_quote_get_count') ? (int) pms_quote_get_count() : 0;
?>

<header id="pms-header" class="pms-header" role="banner">

    <!-- Groene topbalk -->
    <div class="pms-topbar">
        <div class="pms-topbar__inner">
            <div class="pms-topbar__left">
                <a href="tel:0852392040">085 239 2040</a>
                <span class="pms-topbar__divider">|</span>
                <a href="mailto:support@projectmeubelshop.nl">support@projectmeubelshop.nl</a>
            </div>
            <div class="pms-topbar__right">
                <a href="<?php echo esc_url(home_url('/over-ons/')); ?>">Over ons</a>
                <span class="pms-topbar__divider">|</span>
                <a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact</a>
            </div>
        </div>
    </div>

    <div class="pms-header__inner">
        <div class="pms-header__brand">
            <?php if ( has_custom_logo() ) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <?php bloginfo( 'name' ); ?>
                </a>
            <?php endif; ?>
        </div>

        <nav class="pms-header__nav" aria-label="Primaire navigatie">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary_nav',
                'container'      => false,
                'menu_class'     => 'pms-nav__list',
                'fallback_cb'    => false,
                'walker'         => new PMS_Nav_Walker(),
            ]);
            ?>
        </nav>

        <div class="pms-header__divider"></div>

        <div class="pms-header__cta">
            <div class="pms-header-search">
                <button type="button" class="pms-header-search__toggle" aria-label="Zoek producten" aria-expanded="false" aria-controls="pms-header-search-form">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
                <form id="pms-header-search-form" class="pms-header-search__form" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                    <input type="hidden" name="post_type" value="product">
                    <label class="screen-reader-text" for="pms-header-search-input">Zoek producten</label>
                    <input id="pms-header-search-input" class="pms-header-search__input" type="search" name="s" placeholder="Zoek producten">
                    <button type="submit" class="pms-header-search__submit">Zoeken</button>
                </form>
            </div>
            <a href="<?php echo esc_url(home_url('/offerte-samenstellen/')); ?>" class="pms-btn pms-btn--outline pms-btn--has-badge" style="font-weight: 700 !important;">
                <span>Mijn offerte</span>
                <?php if ($quote_count > 0) : ?>
                    <span class="pms-nav-badge" aria-label="<?php echo esc_attr($quote_count); ?> producten in offerte"><?php echo esc_html($quote_count); ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="pms-btn pms-btn--accent" style="font-weight: 700 !important;">Contact opnemen</a>
        </div>

        <a href="<?php echo esc_url(home_url('/offerte-samenstellen/')); ?>" class="pms-header__quote-icon" aria-label="Mijn offerte">
            <svg class="pms-header__quote-icon-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M5 10h14" />
                <path d="M6 10l1.25 8.5A2 2 0 0 0 9.23 20h5.54a2 2 0 0 0 1.98-1.5L18 10" />
                <path d="M9 10V8a3 3 0 0 1 6 0v2" />
            </svg>
            <?php if ($quote_count > 0) : ?>
                <span class="pms-header__quote-badge" aria-label="<?php echo esc_attr($quote_count); ?> producten in offerte"><?php echo esc_html($quote_count); ?></span>
            <?php endif; ?>
        </a>

        <button class="pms-header__hamburger" aria-label="Menu openen" aria-expanded="false" aria-controls="pms-mobile-nav">
            <span class="pms-hamburger__bar"></span>
            <span class="pms-hamburger__bar"></span>
            <span class="pms-hamburger__bar"></span>
        </button>
    </div>
</header>

<div id="pms-mobile-nav" class="pms-mobile-nav" aria-hidden="true">
    <div class="pms-mobile-nav__panel">
        <button class="pms-mobile-nav__close" aria-label="Menu sluiten">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>

        <?php
        wp_nav_menu([
            'theme_location' => 'primary_nav',
            'container'      => false,
            'menu_class'     => 'pms-mobile-nav__list',
            'fallback_cb'    => false,
        ]);
        ?>

        <div class="pms-mobile-nav__divider"></div>
        <div class="pms-mobile-nav__ctas">
            <a href="<?php echo esc_url(home_url('/offerte-samenstellen/')); ?>" class="pms-btn pms-btn--outline pms-btn--has-badge pms-mobile-nav__cta" style="font-weight: 700 !important;">
                <span>Mijn offerte</span>
                <?php if ($quote_count > 0) : ?>
                    <span class="pms-nav-badge" aria-label="<?php echo esc_attr($quote_count); ?> producten in offerte"><?php echo esc_html($quote_count); ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="pms-btn pms-btn--accent pms-mobile-nav__cta" style="font-weight: 700 !important;">Contact opnemen</a>
        </div>
    </div>
</div>

<script>
(function () {
  const hamburger = document.querySelector('.pms-header__hamburger');
  const mobileNav = document.getElementById('pms-mobile-nav');
  const closeBtn  = document.querySelector('.pms-mobile-nav__close');
  const searchToggle = document.querySelector('.pms-header-search__toggle');
  const searchForm = document.getElementById('pms-header-search-form');
  const searchInput = document.getElementById('pms-header-search-input');
  const searchWrap = document.querySelector('.pms-header-search');

  if (!hamburger || !mobileNav) {
    return;
  }

  const closeMenu = () => {
    mobileNav.classList.remove('is-open');
    hamburger.classList.remove('is-open');
    hamburger.setAttribute('aria-expanded', 'false');
    mobileNav.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  };

  const openMenu = () => {
    mobileNav.classList.add('is-open');
    hamburger.classList.add('is-open');
    hamburger.setAttribute('aria-expanded', 'true');
    mobileNav.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  };

  const closeSearch = () => {
    if (!searchToggle || !searchForm) return;
    searchForm.classList.remove('is-open');
    searchToggle.setAttribute('aria-expanded', 'false');
  };

  const openSearch = () => {
    if (!searchToggle || !searchForm) return;
    searchForm.classList.add('is-open');
    searchToggle.setAttribute('aria-expanded', 'true');
    if (searchInput) {
      setTimeout(() => searchInput.focus(), 50);
    }
  };

  hamburger.addEventListener('click', () => {
    if (mobileNav.classList.contains('is-open')) {
      closeMenu();
      return;
    }
    openMenu();
  });

  if (closeBtn) {
    closeBtn.addEventListener('click', closeMenu);
  }

  if (searchToggle && searchForm) {
    searchToggle.addEventListener('click', () => {
      if (searchForm.classList.contains('is-open')) {
        closeSearch();
        return;
      }
      openSearch();
    });
  }

  mobileNav.addEventListener('click', (event) => {
    if (!event.target.closest('.pms-mobile-nav__panel')) {
      closeMenu();
    }
  });

  mobileNav.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', closeMenu);
  });

  document.addEventListener('click', (event) => {
    if (searchWrap && !event.target.closest('.pms-header-search')) {
      closeSearch();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeMenu();
      closeSearch();
    }
  });

  window.addEventListener('resize', () => {
    if (window.innerWidth > 960) {
      closeMenu();
      return;
    }
    closeSearch();
  });
})();
</script>
