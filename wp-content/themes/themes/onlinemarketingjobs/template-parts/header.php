<?php
/**
 * Onlinemarketingjobs.nl custom header.
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!empty($GLOBALS['omj_header_rendered'])) {
    return;
}

$GLOBALS['omj_header_rendered'] = true;

$omj_logo_url            = get_stylesheet_directory_uri() . '/site-logo/online-marketing-jobs-logo.png';
$omj_student_inhuren_url = 'https://platform.student-inhuren.nl/';
$omj_primary_location    = has_nav_menu('primary_nav') ? 'primary_nav' : '';
$omj_mobile_location     = has_nav_menu('mobile_nav') ? 'mobile_nav' : $omj_primary_location;
$omj_nav_walker          = class_exists('OMJ_Nav_Walker') ? new OMJ_Nav_Walker() : null;

$omj_fallback_nav = [
    [
        'label' => 'Vacatures',
        'url'   => home_url('/vacatures/'),
    ],
    [
        'label' => 'Voor werkgevers',
        'url'   => home_url('/werkgever/'),
    ],
    [
        'label' => 'Voor bureaus',
        'url'   => home_url('/bureau/'),
    ],
    [
        'label' => 'Voor kandidaten',
        'url'   => home_url('/werkzoekende/'),
    ],
];
?>

<header id="rn-header" class="rn-header" role="banner">
    <div class="rn-topbar" aria-label="Snelle links">
        <div class="rn-topbar__inner">
            <div class="rn-topbar__group">
                <a class="rn-topbar__link" href="mailto:team@onlinemarketingjobs.nl">team@onlinemarketingjobs.nl</a>
                <span class="rn-topbar__divider" aria-hidden="true"></span>
                <a class="rn-topbar__link" href="<?php echo esc_url(home_url('/vacatures/')); ?>">Vacatures</a>
            </div>
            <div class="rn-topbar__group">
                <a class="rn-topbar__link" href="<?php echo esc_url(home_url('/mijn-vacatures/')); ?>">Mijn vacatures</a>
                <span class="rn-topbar__divider" aria-hidden="true"></span>
                <a class="rn-topbar__link" href="<?php echo esc_url($omj_student_inhuren_url); ?>" target="_blank" rel="noopener noreferrer">Student Inhuren</a>
            </div>
        </div>
    </div>

    <div class="rn-mainbar">
        <div class="rn-header__inner">
            <div class="rn-header__brand">
                <a class="rn-header__brand-link" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
                    <img src="<?php echo esc_url($omj_logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="rn-header__logo">
                </a>
            </div>

            <nav class="rn-header__nav" aria-label="Primaire navigatie">
                <?php if ($omj_primary_location) : ?>
                    <?php
                    wp_nav_menu([
                        'theme_location' => $omj_primary_location,
                        'container'      => false,
                        'menu_class'     => 'rn-nav__list',
                        'fallback_cb'    => false,
                        'walker'         => $omj_nav_walker,
                    ]);
                    ?>
                <?php else : ?>
                    <ul class="rn-nav__list">
                        <?php foreach ($omj_fallback_nav as $omj_nav_item) : ?>
                            <li class="rn-nav__item">
                                <a class="rn-nav__link" href="<?php echo esc_url($omj_nav_item['url']); ?>"><?php echo esc_html($omj_nav_item['label']); ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </nav>

            <div class="rn-header__cta">
                <?php if (function_exists('sj_the_job_favorites_nav_link')) : ?>
                    <?php sj_the_job_favorites_nav_link('rn-header__favorite-link'); ?>
                <?php endif; ?>
                <a class="rn-header__icon-link" href="<?php echo esc_url($omj_student_inhuren_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="Student Inhuren">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.76-3.58-5-8-5Z"/>
                    </svg>
                </a>
                <a href="<?php echo esc_url(home_url('/vacature-plaatsen/')); ?>" class="rn-btn rn-btn--accent">Vacature Plaatsen</a>
            </div>

            <button class="rn-header__hamburger" type="button" aria-label="Menu openen" aria-expanded="false" aria-controls="rn-mobile-nav" data-rn-menu-toggle>
                <span class="rn-hamburger__bar"></span>
                <span class="rn-hamburger__bar"></span>
                <span class="rn-hamburger__bar"></span>
            </button>

            <a class="rn-header__mobile-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
                <img src="<?php echo esc_url($omj_logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="rn-header__mobile-logo">
            </a>

            <div class="rn-header__mobile-actions">
                <?php if (function_exists('sj_the_job_favorites_nav_link')) : ?>
                    <?php sj_the_job_favorites_nav_link('rn-header__favorite-link'); ?>
                <?php endif; ?>
                <a class="rn-header__icon-link" href="<?php echo esc_url($omj_student_inhuren_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="Student Inhuren">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.76-3.58-5-8-5Z"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</header>

<div id="rn-mobile-nav" class="rn-mobile-nav" aria-hidden="true">
    <div class="rn-mobile-nav__panel" role="dialog" aria-modal="true" aria-label="Mobiele navigatie">
        <div class="rn-mobile-nav__head">
            <a class="rn-mobile-nav__brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
                <img src="<?php echo esc_url($omj_logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
            </a>
            <button class="rn-mobile-nav__close" type="button" aria-label="Menu sluiten" data-rn-menu-close>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6 6l12 12M18 6 6 18"/>
                </svg>
            </button>
        </div>

        <nav class="rn-mobile-nav__nav" aria-label="Mobiele navigatie">
            <?php if ($omj_mobile_location) : ?>
                <?php
                wp_nav_menu([
                    'theme_location' => $omj_mobile_location,
                    'container'      => false,
                    'menu_class'     => 'rn-mobile-nav__list',
                    'fallback_cb'    => false,
                ]);
                ?>
            <?php else : ?>
                <ul class="rn-mobile-nav__list">
                    <?php foreach ($omj_fallback_nav as $omj_nav_item) : ?>
                        <li>
                            <a href="<?php echo esc_url($omj_nav_item['url']); ?>"><?php echo esc_html($omj_nav_item['label']); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </nav>

        <div class="rn-mobile-nav__ctas">
            <?php if (function_exists('sj_the_job_favorites_nav_link')) : ?>
                <?php sj_the_job_favorites_nav_link('rn-mobile-nav__favorites'); ?>
            <?php endif; ?>
            <a href="<?php echo esc_url(home_url('/vacature-plaatsen/')); ?>" class="rn-btn rn-btn--accent rn-mobile-nav__cta">Vacature Plaatsen</a>
            <a href="<?php echo esc_url($omj_student_inhuren_url); ?>" class="rn-btn rn-btn--outline rn-mobile-nav__cta" target="_blank" rel="noopener noreferrer">Student Inhuren</a>
        </div>
    </div>
</div>

<script>
(function () {
  const header = document.getElementById('rn-header');
  const mobileNav = document.getElementById('rn-mobile-nav');
  if (!header || !mobileNav || mobileNav.dataset.omjHeaderReady === '1') return;

  mobileNav.dataset.omjHeaderReady = '1';

  const hamburger = header.querySelector('[data-rn-menu-toggle]');
  const closeButtons = mobileNav.querySelectorAll('[data-rn-menu-close]');
  const desktopQuery = window.matchMedia('(min-width: 961px)');

  if (!hamburger) return;

  const closeMenu = () => {
    mobileNav.classList.remove('is-open');
    hamburger.classList.remove('is-open');
    hamburger.setAttribute('aria-expanded', 'false');
    mobileNav.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('rn-mobile-nav-open');
  };

  const openMenu = () => {
    mobileNav.classList.add('is-open');
    hamburger.classList.add('is-open');
    hamburger.setAttribute('aria-expanded', 'true');
    mobileNav.setAttribute('aria-hidden', 'false');
    document.body.classList.add('rn-mobile-nav-open');
  };

  hamburger.addEventListener('click', () => {
    mobileNav.classList.contains('is-open') ? closeMenu() : openMenu();
  });

  closeButtons.forEach((button) => button.addEventListener('click', closeMenu));

  mobileNav.addEventListener('click', (event) => {
    if (event.target === mobileNav) closeMenu();
  });

  mobileNav.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', closeMenu);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeMenu();
  });

  const handleViewportChange = (event) => {
    if (event.matches) closeMenu();
  };

  if (desktopQuery.addEventListener) {
    desktopQuery.addEventListener('change', handleViewportChange);
  } else if (desktopQuery.addListener) {
    desktopQuery.addListener(handleViewportChange);
  }
})();
</script>
