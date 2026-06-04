<?php
if (!defined('ABSPATH')) {
    exit;
}

$admin_email      = sanitize_email(get_option('admin_email'));
$logo_url         = get_stylesheet_directory_uri() . '/site-logo/inhuren-logo.png';
$mobile_logo_url  = get_site_icon_url(96);
if (!$mobile_logo_url) {
    $mobile_logo_url = $logo_url;
}
?>

<header id="rn-header" class="rn-header" role="banner">

    <div class="rn-header__inner">
        <div class="rn-header__brand">
            <a href="<?php echo esc_url(home_url('/')); ?>">
                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php bloginfo('name'); ?>" class="rn-header__logo">
            </a>
        </div>

        <nav class="rn-header__nav" aria-label="Primaire navigatie">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary_nav',
                'container'      => false,
                'menu_class'     => 'rn-nav__list',
                'fallback_cb'    => false,
                'walker'         => new RN_Nav_Walker(),
            ]);
            ?>
        </nav>

        <div class="rn-header__divider"></div>

        <div class="rn-header__cta">
            <div class="rn-info-menu">
                <button type="button" class="rn-btn rn-btn--outline rn-info-menu__toggle" aria-expanded="false" aria-controls="rn-info-menu-desktop">
                    Ik heb een opdracht
                </button>
                <div id="rn-info-menu-desktop" class="rn-info-menu__menu" aria-hidden="true">
                    <a href="<?php echo esc_url(home_url('/informatie-aanvragen/')); ?>" class="rn-info-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/></svg>
                        Informatie formulier
                    </a>
                    <a href="tel:0852392040" class="rn-info-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.58.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.01L6.6 10.8z"/></svg>
                        085 239 2040
                    </a>
                    <a href="mailto:team@inhuren.com" class="rn-info-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg>
                        team@inhuren.com
                    </a>
                </div>
            </div>
            <a href="https://platform.inhuren.com/aanmelden" class="rn-btn rn-btn--accent" target="_blank" rel="noopener noreferrer">Aanmelden</a>
        </div>

        <div class="rn-header__divider"></div>

        <a href="https://platform.inhuren.com/" class="rn-account-link" target="_blank" rel="noopener noreferrer" aria-label="Naar mijn account">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" width="22" height="22"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.76-3.58-5-8-5Z"/></svg>
        </a>

        <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="rn-account-link" aria-label="Contact">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" width="22" height="22"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.58.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.01L6.6 10.8z"/></svg>
        </a>

        <!-- Mobiel: buttons + hamburger -->
        <div class="rn-header__mobile-cta">
            <a href="https://platform.inhuren.com/inloggen" class="rn-mobile-btn rn-mobile-btn--outline" target="_blank" rel="noopener noreferrer">Inloggen</a>
            <a href="https://platform.inhuren.com/aanmelden" class="rn-mobile-btn rn-mobile-btn--accent" target="_blank" rel="noopener noreferrer">Aanmelden</a>
            <button class="rn-header__hamburger" aria-label="Menu openen" aria-expanded="false" aria-controls="rn-mobile-nav">
                <svg class="rn-hamburger__icon rn-hamburger__icon--open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#374151" stroke-width="2" stroke-linecap="round" aria-hidden="true" width="24" height="24">
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <line x1="3" y1="12" x2="21" y2="12"/>
                    <line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
                <svg class="rn-hamburger__icon rn-hamburger__icon--close" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#374151" stroke-width="2" stroke-linecap="round" aria-hidden="true" width="24" height="24">
                    <line x1="19" y1="5" x2="5" y2="19"/>
                    <line x1="5" y1="5" x2="19" y2="19"/>
                </svg>
            </button>
        </div>
    </div>
</header>

<div id="rn-mobile-nav" class="rn-mobile-nav" aria-hidden="true">
    <div class="rn-mobile-nav__panel">
        <button class="rn-mobile-nav__close" aria-label="Menu sluiten">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.8" stroke-linecap="round" stroke-linejoin="round" width="36" height="36" aria-hidden="true"><line x1="19" y1="5" x2="5" y2="19"/><line x1="5" y1="5" x2="19" y2="19"/></svg>
        </button>

        <?php
        wp_nav_menu([
            'theme_location' => 'primary_nav',
            'container'      => false,
            'menu_class'     => 'rn-mobile-nav__list',
            'fallback_cb'    => false,
        ]);
        ?>

        <div class="rn-mobile-nav__divider"></div>
        <div class="rn-mobile-nav__ctas">
            <a href="https://platform.inhuren.com/inloggen" class="rn-btn rn-btn--outline rn-mobile-nav__cta" target="_blank" rel="noopener noreferrer">Inloggen</a>
            <div class="rn-info-menu rn-info-menu--mobile">
                <button type="button" class="rn-btn rn-btn--outline rn-mobile-nav__cta rn-info-menu__toggle" aria-expanded="false" aria-controls="rn-info-menu-mobile">
                    Ik heb een opdracht
                </button>
                <div id="rn-info-menu-mobile" class="rn-info-menu__menu" aria-hidden="true">
                    <a href="<?php echo esc_url(home_url('/informatie-aanvragen/')); ?>" class="rn-info-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/></svg>
                        Informatie formulier
                    </a>
                    <a href="tel:0852392040" class="rn-info-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.58.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.01L6.6 10.8z"/></svg>
                        085 239 2040
                    </a>
                    <a href="mailto:team@inhuren.com" class="rn-info-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg>
                        team@inhuren.com
                    </a>
                </div>
            </div>
            <a href="https://platform.inhuren.com/aanmelden" class="rn-btn rn-btn--accent rn-mobile-nav__cta" target="_blank" rel="noopener noreferrer">Aanmelden</a>
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
      const panel  = menu.querySelector('.rn-info-menu__menu');
      menu.classList.remove('is-open');
      if (toggle) toggle.setAttribute('aria-expanded', 'false');
      if (panel)  panel.setAttribute('aria-hidden', 'true');
    });
  };

  infoMenus.forEach((menu) => {
    const toggle = menu.querySelector('.rn-info-menu__toggle');
    const panel  = menu.querySelector('.rn-info-menu__menu');
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
    if (window.innerWidth > 960) closeMenu();
  });
})();
</script>
