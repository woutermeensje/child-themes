<?php
if (!defined('ABSPATH')) exit;
?>

<header id="rn-header" class="rn-header" role="banner">

    <div class="rn-topbar">
        <div class="rn-topbar__inner">
            <div class="rn-topbar__left">
                <a href="<?php echo esc_url(home_url('/over-ons/')); ?>">Over ons</a>
                <span class="rn-topbar__divider">|</span>
                <a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact</a>
            </div>
            <div class="rn-topbar__right">
                <a href="<?php echo esc_url(home_url('/vacature-plaatsen/')); ?>">Vacature plaatsen</a>
                <span class="rn-topbar__divider">|</span>
                <a href="<?php echo esc_url(home_url('/vacature-alert/')); ?>">Vacature alert</a>
            </div>
        </div>
    </div>

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
                'walker'         => new IVB_Nav_Walker(),
                'fallback_cb'    => false,
            ]); ?>
        </nav>

        <div class="rn-header__divider"></div>

        <div class="rn-header__cta">
            <a href="<?php echo esc_url(home_url('/vacature-plaatsen/')); ?>" class="rn-btn rn-btn--accent">Vacature plaatsen</a>
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
            <a href="<?php echo esc_url(home_url('/vacature-plaatsen/')); ?>" class="rn-btn rn-btn--accent rn-mobile-nav__cta">Vacature plaatsen</a>
        </div>
    </div>
</div>

<script>
(function () {
  const hamburger = document.querySelector('.rn-header__hamburger');
  const mobileNav = document.getElementById('rn-mobile-nav');
  const closeBtn  = document.querySelector('.rn-mobile-nav__close');
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
    if (!e.target.closest('.rn-mobile-nav__panel')) closeMenu();
  });

  mobileNav.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMenu));
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMenu(); });
  window.addEventListener('resize', () => { if (window.innerWidth > 960) closeMenu(); });
})();
</script>
