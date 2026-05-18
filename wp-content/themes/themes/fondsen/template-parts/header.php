<?php
if (!defined('ABSPATH')) {
    exit;
}

$admin_email = sanitize_email(get_option('admin_email'));
$logo_url = get_stylesheet_directory_uri() . '/site-logo/Logo-fondsen.png';

$rn_mobile_logo_url = get_site_icon_url(96);
if (!$rn_mobile_logo_url) {
    $rn_mobile_logo_url = $logo_url;
}
?>

<header id="rn-header" class="rn-header" role="banner">
    <div class="rn-topbar">
        <div class="rn-topbar__inner">
            <div class="rn-topbar__left">
                <a href="https://platform.fondsen.org/acties">Geefacties</a>
                <span class="rn-topbar__divider">|</span>
                <a href="https://platform.fondsen.org/registreren">Account aanmaken</a>
            </div>
            <div class="rn-topbar__right">
                <a href="https://teammate.nl/recruitment-non-profit/">teammate.nl</a>
                <span class="rn-topbar__divider">|</span>
                <a href="https://subsidiegezocht.nl/">subsidiegezocht.nl</a>
            </div>
        </div>
    </div>

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
                'walker'         => new Fondsen_Nav_Walker(),
            ]);
            ?>
        </nav>

        <div class="rn-header__cta">
            <?php if (function_exists('sj_the_job_favorites_nav_link')) sj_the_job_favorites_nav_link('rn-header__favorite-link'); ?>
            <a class="rn-mobile-icon-link rn-mobile-icon-link--account" href="https://platform.fondsen.org/inloggen/" target="_blank" rel="noopener noreferrer" aria-label="Naar mijn account">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.76-3.58-5-8-5Z"/></svg>
            </a>
            <a href="<?php echo esc_url(home_url('/vacature-plaatsen/')); ?>" class="rn-btn rn-btn--accent">Vacature Plaatsen</a>
        </div>

        <div class="rn-header__mobile-left">
            <button class="rn-header__hamburger" aria-label="Menu openen" aria-expanded="false" aria-controls="rn-mobile-nav">
                <span class="rn-hamburger__bar"></span>
                <span class="rn-hamburger__bar"></span>
                <span class="rn-hamburger__bar"></span>
            </button>
        </div>

        <a class="rn-header__mobile-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
            <img src="<?php echo esc_url($rn_mobile_logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="rn-header__mobile-logo">
        </a>

        <div class="rn-header__mobile-right">
            <?php if (function_exists('sj_the_job_favorites_nav_link')) sj_the_job_favorites_nav_link('rn-header__favorite-link'); ?>
            <a class="rn-mobile-icon-link rn-mobile-icon-link--account" href="https://platform.fondsen.org/inloggen/" target="_blank" rel="noopener noreferrer" aria-label="Naar mijn account">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.76-3.58-5-8-5Z"/></svg>
            </a>
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
            <?php if (function_exists('sj_the_job_favorites_nav_link')) sj_the_job_favorites_nav_link('rn-mobile-nav__favorites'); ?>
            <a href="<?php echo esc_url(home_url('/vacature-plaatsen/')); ?>" class="rn-btn rn-btn--accent rn-mobile-nav__cta">Vacature Plaatsen</a>
        </div>
    </div>
</div>

<script>
(function () {
  const hamburger = document.querySelector('.rn-header__hamburger');
  const mobileNav = document.getElementById('rn-mobile-nav');
  const closeBtn = document.querySelector('.rn-mobile-nav__close');
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
