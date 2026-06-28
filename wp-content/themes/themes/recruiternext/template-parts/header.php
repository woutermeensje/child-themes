<?php
/**
 * Recruiternext – Custom header template
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$rn_custom_logo_id = get_theme_mod( 'custom_logo' );
$rn_logo_alt       = get_bloginfo( 'name', 'display' );
$rn_fallback_logo  = get_stylesheet_directory_uri() . '/site-logo/recruiternext-site-logo.png';
$rn_desktop_logo   = $rn_custom_logo_id
    ? wp_get_attachment_image(
        $rn_custom_logo_id,
        'full',
        false,
        [
            'class'    => 'rn-header__logo',
            'alt'      => $rn_logo_alt,
            'loading'  => false,
            'decoding' => 'async',
        ]
    )
    : '';
$rn_mobile_logo    = $rn_custom_logo_id
    ? wp_get_attachment_image(
        $rn_custom_logo_id,
        'full',
        false,
        [
            'class'    => 'rn-header__mobile-logo',
            'alt'      => $rn_logo_alt,
            'loading'  => false,
            'decoding' => 'async',
        ]
    )
    : '';
?>

<style>
/* ---- Header vars ---- */
#rn-header, #rn-header *, #rn-header *::before, #rn-header *::after,
.rn-mobile-nav, .rn-mobile-nav *, .rn-mobile-nav *::before, .rn-mobile-nav *::after { box-sizing: border-box; }

/* ---- Header wrapper ---- */
#rn-header.rn-header {
  position: sticky !important;
  top: 0 !important;
  z-index: 9000 !important;
  background: #ffffff !important;
  box-shadow: 0 2px 16px rgba(0,0,0,0.08) !important;
  width: 100vw !important;
  margin-left: calc(50% - 50vw) !important;
  box-sizing: border-box !important;
}

/* ---- Main nav bar ---- */
#rn-header .rn-header__inner {
  width: min(100% - 96px, 1840px) !important; margin: 0 auto !important; padding: 0 !important;
  height: 84px; display: flex; align-items: center; gap: 24px;
}

/* ---- Brand / Logo ---- */
#rn-header .rn-header__brand { flex: 0 0 auto; display: flex; align-items: center; }
#rn-header .rn-header__brand a { display: block; text-decoration: none !important; }
#rn-header .rn-header__logo { display: block; height: 64px; width: auto; }
#rn-header .rn-header__mobile-left,
#rn-header .rn-header__mobile-brand,
#rn-header .rn-header__mobile-right { display: none; }
#rn-header .rn-header__mobile-logo {
  display: block;
  width: auto;
  max-width: 164px;
  height: 46px;
  object-fit: contain;
}
#rn-header .rn-mobile-icon-link {
  display: inline-flex !important;
  align-items: center;
  justify-content: center;
  width: 42px;
  height: 42px;
  border-radius: 999px;
  color: #0458ab !important;
  text-decoration: none !important;
  background: transparent !important;
  transition: color .18s ease, background .18s ease;
}
#rn-header .rn-mobile-icon-link:hover,
#rn-header .rn-mobile-icon-link:focus {
  color: #034483 !important;
  background: rgba(4, 88, 171, 0.08) !important;
}
#rn-header .rn-mobile-icon-link svg {
  width: 25px;
  height: 25px;
  fill: currentColor;
}

/* ---- Desktop nav ---- */
#rn-header .rn-header__nav { flex: 1 1 auto; display: flex; align-items: center; justify-content: flex-end; }
#rn-header .rn-nav__list { list-style: none !important; margin: 0 !important; padding: 0 !important; display: flex !important; align-items: center; gap: 2px; }
#rn-header .rn-nav__item { position: relative; list-style: none !important; margin: 0 !important; padding: 0 !important; }
#rn-header .rn-nav__link {
  display: inline-flex !important; align-items: center; gap: 5px;
  padding: 8px 12px !important;
  font-family: 'Roboto', sans-serif !important;
  font-size: 15px !important; font-weight: 600 !important;
  color: #333333 !important;
  text-decoration: none !important;
  border-radius: 6px; white-space: nowrap;
  height: 84px;
  transition: color .15s ease;
  background: transparent !important;
  box-shadow: none !important;
  border: none !important;
}
#rn-header .rn-nav__link:hover { color: #0458ab !important; background: transparent !important; }
#rn-header .rn-nav__link.is-active { color: #0458ab !important; font-weight: 600 !important; }

/* Chevron */
#rn-header .rn-nav__chev {
  width: 7px; height: 7px;
  border-right: 2px solid currentColor; border-bottom: 2px solid currentColor;
  transform: rotate(45deg) translateY(-2px); transition: transform .2s ease; flex-shrink: 0;
}
#rn-header .rn-nav__item--has-children:hover > .rn-nav__link .rn-nav__chev { transform: rotate(-135deg) translateY(-2px); }

/* Dropdown */
#rn-header .rn-nav__dropdown {
  display: none; position: absolute; top: 100%; left: 0;
  min-width: 210px; background: #fff;
  border: 1px solid #e8ecf0; border-top: 3px solid #0458ab;
  border-radius: 0 0 8px 8px;
  box-shadow: 0 12px 40px rgba(0,0,0,0.12);
  padding: 6px; list-style: none; margin: 0; z-index: 9001;
}
#rn-header .rn-nav__item--has-children:hover > .rn-nav__dropdown { display: block; }
#rn-header .rn-nav__dropdown .rn-nav__item { width: 100%; }
#rn-header .rn-nav__dropdown .rn-nav__link {
  display: block !important; height: auto !important; border-radius: 6px;
  padding: 9px 14px !important; font-size: 14px !important; font-weight: 400 !important;
  width: 100%; box-shadow: none !important;
}
#rn-header .rn-nav__dropdown .rn-nav__link:hover { background: rgba(4,88,171,0.06) !important; }

/* ---- CTA knoppen ---- */
#rn-header .rn-header__cta { flex: 0 0 auto; display: flex; align-items: center; gap: 10px; }
#rn-header .rn-btn {
  display: inline-flex !important; align-items: center; justify-content: center;
  font-family: "Work Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
  font-weight: 800 !important; font-size: 16px !important;
  padding: 6px 16px !important; border-radius: 8px !important;
  white-space: nowrap; cursor: pointer;
  text-decoration: none !important;
  transition: background .15s ease, color .15s ease, transform .15s ease;
}
#rn-header .rn-btn--outline {
  background: transparent !important; color: #0458ab !important;
  border: 2px solid #0458ab !important;
}
#rn-header .rn-btn--outline:hover { background: #0458ab !important; color: #fff !important; }
#rn-header .rn-btn--accent {
  background: #2f7fd6 !important; color: #fff !important;
  border: 5px solid #2f7fd6 !important;
  border-radius: 5px !important;
  box-shadow: 0 2px 8px rgba(47, 127, 214,0.3) !important;
}
#rn-header .rn-btn--accent:hover {
  background: #2f7fd6 !important; border-color: #2f7fd6 !important;
  color: #fff !important;
  transform: none;
}

/* ---- Hamburger ---- */
#rn-header .rn-header__hamburger {
  display: none; flex-direction: column; justify-content: center; gap: 5px;
  width: 40px; height: 40px; background: transparent !important;
  border: 1.5px solid #e8ecf0 !important; border-radius: 6px;
  cursor: pointer; padding: 8px; margin-left: auto; flex-shrink: 0;
}
#rn-header .rn-header__hamburger:hover,
#rn-header .rn-header__hamburger:focus,
#rn-header .rn-header__hamburger:active {
  background: transparent !important;
  border-color: #0458ab !important;
  box-shadow: none !important;
}
#rn-header .rn-hamburger__bar { display: block; width: 100%; height: 2px; background: #333333; border-radius: 2px; transition: transform .25s ease, opacity .25s ease; }
#rn-header .rn-header__hamburger.is-open .rn-hamburger__bar:nth-child(1) { transform: translateY(7px) rotate(45deg); }
#rn-header .rn-header__hamburger.is-open .rn-hamburger__bar:nth-child(2) { opacity: 0; transform: scaleX(0); }
#rn-header .rn-header__hamburger.is-open .rn-hamburger__bar:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

/* ---- Mobile nav (side drawer) ---- */
.rn-mobile-nav {
  display: none; position: fixed !important; top: 0; left: 0;
  width: 100vw; height: 100dvh;
  background: rgba(17,24,39,.32);
  z-index: 9100 !important; opacity: 0; visibility: hidden;
  transition: opacity .2s ease, visibility .2s ease;
}
.rn-mobile-nav.is-open { opacity: 1 !important; visibility: visible !important; }
.rn-mobile-nav__panel {
  position: absolute !important; top: 0; right: 0;
  width: 80vw; max-width: 420px; min-width: 280px;
  height: 100dvh; background: #fff !important;
  border-left: 1px solid #e5e7eb;
  overflow-y: auto; -webkit-overflow-scrolling: touch;
  padding: 76px 18px 18px;
  transform: translateX(100%); transition: transform .2s ease;
}
.rn-mobile-nav.is-open .rn-mobile-nav__panel { transform: translateX(0) !important; }

/* Close button */
.rn-mobile-nav__close {
  position: absolute; top: 16px; right: 16px;
  width: 52px !important; height: 52px !important; min-width: 52px !important; min-height: 52px !important;
  display: flex !important; align-items: center; justify-content: center;
  padding: 0 !important;
  background: transparent !important; border: 1px solid #e5e7eb !important;
  border-radius: 8px !important; cursor: pointer; color: #333 !important;
  box-shadow: none !important;
  appearance: none;
  transition: background .18s ease, border-color .18s ease, color .18s ease;
}
.rn-mobile-nav__close svg {
  display: block;
  width: 36px !important;
  height: 36px !important;
  stroke-width: 3.8 !important;
  flex-shrink: 0;
}
.rn-mobile-nav__close:hover,
.rn-mobile-nav__close:focus,
.rn-mobile-nav__close:active {
  background: rgba(4, 88, 171, 0.08) !important;
  border-color: #0458ab !important;
  color: #0458ab !important;
  outline: none !important;
  box-shadow: none !important;
}

/* Nav links */
.rn-mobile-nav__list,
.rn-mobile-nav__list ul { list-style: none !important; margin: 0 !important; padding: 0 !important; display: flex !important; flex-direction: column !important; }
.rn-mobile-nav__list li { list-style: none !important; width: 100%; display: block !important; border-bottom: 1px solid #e5e7eb; }
.rn-mobile-nav__list li:last-child { border-bottom: none; }
.rn-mobile-nav__list a {
  display: block !important;
  font-family: 'Roboto', sans-serif !important;
  font-size: 15px !important; font-weight: 500 !important;
  color: #111827 !important; padding: 14px 4px !important;
  text-decoration: none !important; box-shadow: none !important;
  background: transparent !important;
}
.rn-mobile-nav__list a:hover { color: #0458ab !important; }
.rn-mobile-nav__list .rn-nav__chev { display: none !important; }

/* Submenu mobile */
.rn-mobile-nav__list ul {
  border-left: 3px solid #e5e7eb !important; padding: 0 0 8px 12px !important;
  background: transparent !important; position: static !important; box-shadow: none !important; border-radius: 0 !important;
}
.rn-mobile-nav__list ul li { border-bottom: none !important; }
.rn-mobile-nav__list ul a { font-size: 15px !important; padding: 8px 4px !important; color: #6b7280 !important; }

/* Divider + CTA knoppen */
.rn-mobile-nav__divider { height: 1px; background: #e5e7eb; margin: 14px 0; }
.rn-mobile-nav__ctas { display: flex !important; flex-direction: column; gap: 10px; }
.rn-mobile-nav .rn-btn {
  display: inline-flex !important;
  align-items: center;
  justify-content: center;
  font-family: "Work Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
  text-decoration: none !important;
  white-space: nowrap;
  cursor: pointer;
  transition: background .15s ease, color .15s ease;
}
.rn-mobile-nav .rn-btn--outline {
  background: transparent !important;
  color: #0458ab !important;
  border: 2px solid #0458ab !important;
}
.rn-mobile-nav .rn-btn--outline:hover {
  background: #0458ab !important;
  color: #fff !important;
}
.rn-mobile-nav .rn-btn--accent {
  background: #2f7fd6 !important;
  color: #fff !important;
  border: 5px solid #2f7fd6 !important;
  border-radius: 5px !important;
  box-shadow: 0 2px 8px rgba(47, 127, 214, 0.3) !important;
}
.rn-mobile-nav .rn-btn--accent:hover {
  background: #2f7fd6 !important;
  border-color: #2f7fd6 !important;
  color: #fff !important;
}
.rn-mobile-nav__cta { width: 100%; justify-content: center; font-size: 15px !important; font-weight: 600 !important; padding: 13px 16px !important; border-radius: 8px !important; }

/* ---- Responsive ---- */
@media (max-width: 960px) {
  #rn-header .rn-header__inner {
    width: calc(100% - 48px) !important;
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
    gap: 0 !important;
    height: 64px;
  }
  #rn-header .rn-header__brand,
  #rn-header .rn-header__nav,
  #rn-header .rn-header__cta { display: none !important; }
  #rn-header .rn-header__mobile-left,
  #rn-header .rn-header__mobile-brand,
  #rn-header .rn-header__mobile-right {
    display: flex !important;
    align-items: center;
  }
  #rn-header .rn-header__mobile-left {
    justify-self: start;
    gap: 8px;
  }
  #rn-header .rn-header__mobile-brand {
    justify-self: center;
    text-decoration: none !important;
  }
  #rn-header .rn-header__mobile-right {
    justify-self: end;
    gap: 4px;
  }
  #rn-header .rn-header__hamburger { display: flex !important; margin: 0 !important; align-self: center; }
  .rn-mobile-nav { display: block; }
}
@media (min-width: 961px) {
  .rn-mobile-nav { display: none !important; }
}
@media (max-width: 480px) {
  #rn-header .rn-header__inner { width: calc(100% - 32px) !important; }
  #rn-header .rn-header__mobile-logo { max-width: 136px; height: 42px; }
}
</style>

<header id="rn-header" class="rn-header" role="banner">

    <!-- Main nav -->
    <div class="rn-header__inner">

        <!-- Logo -->
        <div class="rn-header__brand">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <?php
                if ( $rn_desktop_logo ) {
                    echo $rn_desktop_logo;
                } else {
                    ?>
                    <img src="<?php echo esc_url( $rn_fallback_logo ); ?>"
                         alt="<?php echo esc_attr( $rn_logo_alt ); ?>"
                         class="rn-header__logo">
                    <?php
                }
                ?>
            </a>
        </div>

        <!-- Desktop nav -->
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

        <!-- CTA knoppen -->
        <div class="rn-header__cta">
            <a href="<?php echo esc_url( home_url( '/vacature-plaatsen/' ) ); ?>" class="rn-btn rn-btn--accent">
                Vacature Plaatsen
            </a>
        </div>

        <div class="rn-header__mobile-left">
            <button class="rn-header__hamburger" aria-label="Menu openen" aria-expanded="false" aria-controls="rn-mobile-nav">
                <span class="rn-hamburger__bar"></span>
                <span class="rn-hamburger__bar"></span>
                <span class="rn-hamburger__bar"></span>
            </button>
        </div>

        <a class="rn-header__mobile-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( $rn_logo_alt ); ?>">
            <?php
            if ( $rn_mobile_logo ) {
                echo $rn_mobile_logo;
            } else {
                ?>
                <img src="<?php echo esc_url( $rn_fallback_logo ); ?>"
                     alt="<?php echo esc_attr( $rn_logo_alt ); ?>"
                     class="rn-header__mobile-logo">
                <?php
            }
            ?>
        </a>

        <div class="rn-header__mobile-right">
            <a class="rn-mobile-icon-link rn-mobile-icon-link--account" href="<?php echo esc_url( home_url( '/profiel-aanmaken/' ) ); ?>" aria-label="Profiel aanmaken">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-2.76-3.58-5-8-5Z"/></svg>
            </a>
        </div>

    </div><!-- /.rn-header__inner -->


</header>

<!-- Mobile nav (side drawer) – buiten <header> zodat position:fixed correct werkt -->
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
            <a href="<?php echo esc_url( home_url( '/profiel-aanmaken/' ) ); ?>" class="rn-btn rn-btn--outline rn-mobile-nav__cta">
                Profiel aanmaken
            </a>
            <a href="<?php echo esc_url( home_url( '/vacature-plaatsen/' ) ); ?>" class="rn-btn rn-btn--accent rn-mobile-nav__cta">
                Vacature Plaatsen
            </a>
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

  // Close button inside panel
  if (closeBtn) closeBtn.addEventListener('click', closeMenu);

  // Click on backdrop (outside panel) closes menu
  mobileNav.addEventListener('click', (e) => {
    if (!e.target.closest('.rn-mobile-nav__panel')) closeMenu();
  });

  // Close on nav link click
  mobileNav.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', closeMenu);
  });

  // Close on Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeMenu();
  });

  // Close on resize to desktop
  window.addEventListener('resize', () => {
    if (window.innerWidth > 960) closeMenu();
  });
})();
</script>
