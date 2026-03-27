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
            <a href="<?php echo esc_url(home_url('/')); ?>">
                <?php
                if (has_custom_logo()) {
                    the_custom_logo();
                } else {
                    echo '<span class="dr-header__site-name">' . esc_html(get_bloginfo('name')) . '</span>';
                }
                ?>
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
        <div class="dr-mobile-nav__divider"></div>
        <div class="dr-mobile-nav__ctas">
            <a href="https://dereceptionist.nl/informatie-aanvragen/" class="dr-btn dr-btn--primary dr-mobile-nav__cta">Informatie aanvragen</a>
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
