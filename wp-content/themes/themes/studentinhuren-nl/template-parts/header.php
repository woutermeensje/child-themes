<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<header id="rn-header" class="rn-header" role="banner">
    <div class="rn-header__inner">
        <div class="rn-header__brand">
            <?php if (has_custom_logo()) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <a href="<?php echo esc_url(home_url('/')); ?>">
                    <span class="rn-header__site-name"><?php bloginfo('name'); ?></span>
                </a>
            <?php endif; ?>
        </div>

        <nav class="rn-header__nav" aria-label="Primaire navigatie">
            <?php wp_nav_menu([
                'theme_location' => 'primary_nav',
                'container'      => false,
                'menu_class'     => 'rn-nav__list',
                'walker'         => new RN_Nav_Walker(),
                'fallback_cb'    => false,
            ]); ?>
        </nav>

        <div class="rn-header__divider"></div>

        <div class="rn-header__cta">
            <a class="rn-account-link" href="https://platform.student-inhuren.nl/inloggen" target="_blank" rel="noopener noreferrer" aria-label="Naar mijn account">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.76-3.58-5-8-5Z"/></svg>
            </a>
            <div class="rn-info-menu">
                <button type="button" class="rn-btn rn-btn--outline rn-info-menu__toggle" aria-expanded="false" aria-controls="rn-info-menu-desktop">
                    Informatie
                </button>
                <div id="rn-info-menu-desktop" class="rn-info-menu__menu" aria-hidden="true">
                    <a href="<?php echo esc_url(home_url('/informatie-aanvragen/')); ?>" class="rn-info-menu__item">Via formulier</a>
                    <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="rn-info-menu__item">Per e-mail</a>
                    <a href="tel:0852392040" class="rn-info-menu__item">Telefonisch: 085 2392040</a>
                </div>
            </div>
            <a href="https://platform.student-inhuren.nl/aanmelden" class="rn-btn rn-btn--accent" target="_blank" rel="noopener noreferrer">Aanmelden</a>
        </div>

        <button class="rn-header__hamburger" aria-label="Menu openen" aria-expanded="false" aria-controls="rn-mobile-nav">
            <span class="rn-hamburger__bar"></span>
            <span class="rn-hamburger__bar"></span>
            <span class="rn-hamburger__bar"></span>
        </button>
    </div>
</header>

<div id="rn-mobile-nav" class="rn-mobile-nav" aria-hidden="true">
    <div class="rn-mobile-nav__panel">
        <button class="rn-mobile-nav__close" aria-label="Menu sluiten">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>

        <?php wp_nav_menu([
            'theme_location' => 'primary_nav',
            'container'      => false,
            'menu_class'     => 'rn-mobile-nav__list',
            'fallback_cb'    => false,
        ]); ?>

        <div class="rn-mobile-nav__divider"></div>
        <div class="rn-mobile-nav__ctas">
            <a href="https://platform.student-inhuren.nl/inloggen" class="rn-account-link rn-account-link--mobile" target="_blank" rel="noopener noreferrer" aria-label="Naar mijn account">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.76-3.58-5-8-5Z"/></svg>
                <span>Inloggen</span>
            </a>
            <div class="rn-info-menu rn-info-menu--mobile">
                <button type="button" class="rn-btn rn-btn--outline rn-mobile-nav__cta rn-info-menu__toggle" aria-expanded="false" aria-controls="rn-info-menu-mobile">
                    Informatie
                </button>
                <div id="rn-info-menu-mobile" class="rn-info-menu__menu" aria-hidden="true">
                    <a href="<?php echo esc_url(home_url('/informatie-aanvragen/')); ?>" class="rn-info-menu__item">Via formulier</a>
                    <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="rn-info-menu__item">Per e-mail</a>
                    <a href="tel:0852392040" class="rn-info-menu__item">Telefonisch: 085 2392040</a>
                </div>
            </div>
            <a href="https://platform.student-inhuren.nl/aanmelden" class="rn-btn rn-btn--accent rn-mobile-nav__cta" target="_blank" rel="noopener noreferrer">Aanmelden</a>
        </div>
    </div>
</div>

<script>
(function () {
  const hamburger = document.querySelector('.rn-header__hamburger');
  const mobileNav = document.getElementById('rn-mobile-nav');
  const closeBtn  = document.querySelector('.rn-mobile-nav__close');
  const infoMenus = document.querySelectorAll('.rn-info-menu');

  const closeInfoMenus = (exceptMenu = null) => {
    infoMenus.forEach((menu) => {
      if (menu === exceptMenu) return;
      const toggle = menu.querySelector('.rn-info-menu__toggle');
      const panel = menu.querySelector('.rn-info-menu__menu');

      menu.classList.remove('is-open');
      if (toggle) toggle.setAttribute('aria-expanded', 'false');
      if (panel) panel.setAttribute('aria-hidden', 'true');
    });
  };

  infoMenus.forEach((menu) => {
    const toggle = menu.querySelector('.rn-info-menu__toggle');
    const panel = menu.querySelector('.rn-info-menu__menu');

    if (!toggle || !panel) return;

    toggle.addEventListener('click', (e) => {
      e.stopPropagation();
      const shouldOpen = !menu.classList.contains('is-open');

      closeInfoMenus(menu);
      menu.classList.toggle('is-open', shouldOpen);
      toggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
      panel.setAttribute('aria-hidden', shouldOpen ? 'false' : 'true');
    });
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.rn-info-menu')) closeInfoMenus();
  });

  if (!hamburger || !mobileNav) return;

  const closeMenu = () => {
    mobileNav.classList.remove('is-open');
    hamburger.classList.remove('is-open');
    hamburger.setAttribute('aria-expanded', 'false');
    mobileNav.setAttribute('aria-hidden', 'true');
    closeInfoMenus();
    document.body.style.overflow = '';
  };

  const openMenu = () => {
    mobileNav.classList.add('is-open');
    hamburger.classList.add('is-open');
    hamburger.setAttribute('aria-expanded', 'true');
    mobileNav.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  };

  hamburger.addEventListener('click', () => {
    mobileNav.classList.contains('is-open') ? closeMenu() : openMenu();
  });

  if (closeBtn) closeBtn.addEventListener('click', closeMenu);

  mobileNav.addEventListener('click', (e) => {
    if (!e.target.closest('.rn-mobile-nav__panel')) closeMenu();
  });

  mobileNav.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMenu));

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeInfoMenus();
      closeMenu();
    }
  });

  window.addEventListener('resize', () => {
    closeInfoMenus();
    if (window.innerWidth > 1180) closeMenu();
  });
})();
</script>
