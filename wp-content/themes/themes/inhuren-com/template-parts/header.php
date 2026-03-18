<?php
if (!defined('ABSPATH')) {
    exit;
}

$admin_email = sanitize_email(get_option('admin_email'));
$logo_id = get_theme_mod('custom_logo');
$logo_image = $logo_id ? wp_get_attachment_image_src($logo_id, 'full') : false;
$logo_url = $logo_image ? $logo_image[0] : '';
?>

<header id="rn-header" class="rn-header" role="banner">
    <div class="rn-header__inner">
        <div class="rn-header__brand">
            <a href="<?php echo esc_url(home_url('/')); ?>">
                <?php if ($logo_url) : ?>
                    <img src="<?php echo esc_url($logo_url); ?>" alt="<?php bloginfo('name'); ?>" class="rn-header__logo">
                <?php else : ?>
                    <span class="rn-header__site-name"><?php bloginfo('name'); ?></span>
                <?php endif; ?>
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
            <a href="https://platform.inhuren.com/inloggen" class="rn-btn rn-btn--outline">Inloggen</a>
            <a href="https://platform.inhuren.com/aanmelden" class="rn-btn rn-btn--accent">Aanmelden</a>
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
            <a href="https://platform.inhuren.com/inloggen" class="rn-btn rn-btn--outline rn-mobile-nav__cta">Inloggen</a>
            <a href="https://platform.inhuren.com/aanmelden" class="rn-btn rn-btn--accent rn-mobile-nav__cta">Aanmelden</a>
        </div>
    </div>
</div>

<script>
(function () {
  const hamburger = document.querySelector('.rn-header__hamburger');
  const mobileNav = document.getElementById('rn-mobile-nav');
  const closeBtn = document.querySelector('.rn-mobile-nav__close');

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

  mobileNav.addEventListener('click', (event) => {
    if (!event.target.closest('.rn-mobile-nav__panel')) {
      closeMenu();
    }
  });

  mobileNav.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', closeMenu);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeMenu();
    }
  });

  window.addEventListener('resize', () => {
    if (window.innerWidth > 960) {
      closeMenu();
    }
  });
})();
</script>
