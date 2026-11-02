<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<header id="dr-header" class="dr-header" role="banner">
    <div class="dr-topbar">
        <div class="dr-topbar__inner">
            <div class="dr-topbar__left">
                <a href="https://dereceptionist.nl/veelgestelde-vragen/">Veelgestelde vragen</a>
                <span class="dr-topbar__divider">|</span>
                <a href="https://dereceptionist.nl/contact/#team">Team</a>
            </div>
            <div class="dr-topbar__right">
                <span class="dr-topbar__text">team@dereceptionist.nl</span>
                <span class="dr-topbar__divider">|</span>
                <span class="dr-topbar__text">Bel: 036 781 7810</span>
                <span class="dr-topbar__divider">|</span>
                <a href="<?php echo esc_url(home_url('/contact/')); ?>">Urenverwerking</a>
            </div>
        </div>
    </div>

    <div class="dr-header__inner">
        <div class="dr-header__brand">
            <a class="dr-header__logo-link" href="<?php echo esc_url(home_url('/')); ?>" aria-label="deReceptionist.nl – naar de homepage">
                <span class="dr-header__logo"><span class="dr-header__logo-accent">de</span>Receptionist<span class="dr-header__logo-accent">.nl</span></span>
            </a>
        </div>

        <nav class="dr-header__nav" aria-label="Primaire navigatie">
            <?php
            wp_nav_menu([
                'theme_location' => 'dr_primary_nav',
                'container'      => false,
                'menu_class'     => 'dr-nav__list',
                'fallback_cb'    => false,
                'walker'         => new DR_Nav_Walker(),
            ]);
            ?>
        </nav>

        <div class="dr-header__cta">
            <a href="https://dereceptionist.nl/informatie-aanvragen/" class="dr-btn dr-btn--primary">Informatie aanvragen</a>
        </div>

        <button class="dr-header__hamburger" aria-label="Menu openen" aria-expanded="false" aria-controls="dr-mobile-nav">
            <span class="dr-hamburger__bar"></span>
            <span class="dr-hamburger__bar"></span>
            <span class="dr-hamburger__bar"></span>
        </button>
    </div>
</header>

<div id="dr-mobile-nav" class="dr-mobile-nav" aria-hidden="true">
    <div class="dr-mobile-nav__panel">
        <button class="dr-mobile-nav__close" aria-label="Menu sluiten">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <?php
        wp_nav_menu([
            'theme_location' => 'dr_primary_nav',
            'container'      => false,
            'menu_class'     => 'dr-mobile-nav__list',
            'fallback_cb'    => false,
        ]);
        ?>
        <div class="dr-mobile-nav__footer">
            <ul class="dr-mobile-nav__contact">
                <li>
                    <a class="dr-mobile-nav__contact-link" href="tel:+31367817810">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        036 781 7810
                    </a>
                </li>
                <li>
                    <a class="dr-mobile-nav__contact-link" href="mailto:team@dereceptionist.nl">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
                        team@dereceptionist.nl
                    </a>
                </li>
            </ul>
            <div class="dr-mobile-nav__ctas">
                <a href="https://dereceptionist.nl/informatie-aanvragen/" class="dr-btn dr-btn--primary dr-mobile-nav__cta">Informatie aanvragen</a>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
  const hamburger = document.querySelector('.dr-header__hamburger');
  const mobileNav = document.getElementById('dr-mobile-nav');
  const closeBtn  = document.querySelector('.dr-mobile-nav__close');
  if (!hamburger || !mobileNav) return;

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
    if (!e.target.closest('.dr-mobile-nav__panel')) closeMenu();
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
})();
</script>
