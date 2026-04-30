<?php
/**
 * Equitee – Custom header template
 */
if ( ! defined( 'ABSPATH' ) ) exit;
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
  width: 100% !important;
}

/* ---- Utility bar ---- */
#rn-header .rn-topbar { display: none !important; }
#rn-header .rn-topbar__inner {
  max-width: 1200px; margin: 0 auto; padding: 0 24px;
  height: 36px; display: flex; align-items: center; justify-content: space-between;
}
#rn-header .rn-topbar__left,
#rn-header .rn-topbar__right { display: flex; align-items: center; gap: 10px; }
#rn-header .rn-topbar a {
  font-family: 'Poppins', sans-serif !important;
  font-size: 13px !important; font-weight: 500 !important;
  color: rgba(255,255,255,0.9) !important;
  text-decoration: none !important;
}
#rn-header .rn-topbar a:hover { color: #fff !important; text-decoration: underline !important; }
#rn-header .rn-topbar__divider { color: rgba(255,255,255,0.4); font-size: 13px; }

/* ---- Main nav bar ---- */
#rn-header .rn-header__inner {
  max-width: 1200px; margin: 0 auto; padding: 0 24px;
  height: 68px; display: flex; align-items: center; gap: 24px;
}

/* ---- Brand / Logo ---- */
#rn-header .rn-header__brand { flex: 0 0 auto; display: flex; align-items: center; }
#rn-header .rn-header__brand a { display: block; text-decoration: none !important; }
#rn-header .rn-header__logo { display: block; height: 44px; width: auto; }

/* ---- Desktop nav ---- */
#rn-header .rn-header__nav { flex: 1 1 auto; display: flex; align-items: center; justify-content: flex-end; }
#rn-header .rn-nav__list { list-style: none !important; margin: 0 !important; padding: 0 !important; display: flex !important; align-items: center; gap: 2px; }
#rn-header .rn-nav__item { position: relative; list-style: none !important; margin: 0 !important; padding: 0 !important; }
#rn-header .rn-nav__link {
  display: inline-flex !important; align-items: center; gap: 5px;
  padding: 8px 12px !important;
  font-family: 'Poppins', sans-serif !important;
  font-size: 15px !important; font-weight: 300 !important;
  color: #333333 !important;
  text-decoration: none !important;
  border-radius: 6px; white-space: nowrap;
  height: 68px;
  transition: color .15s ease;
  background: transparent !important;
  box-shadow: none !important;
  border: none !important;
}
#rn-header .rn-nav__link:hover { color: var(--color-primary) !important; background: transparent !important; }
#rn-header .rn-nav__link.is-active { color: var(--color-primary) !important; font-weight: 600 !important; }

/* Chevron */
#rn-header .rn-nav__chev {
  width: 7px; height: 7px;
  border-right: 2px solid currentColor; border-bottom: 2px solid currentColor;
  transform: rotate(45deg) translateY(-2px); transition: transform .2s ease; flex-shrink: 0;
}
#rn-header .rn-nav__item--has-children:hover > .rn-nav__link .rn-nav__chev { transform: rotate(-135deg) translateY(-2px); }

/* Dropdown */
#rn-header .rn-nav__dropdown {
  display: block; position: absolute; top: calc(100% - 8px); left: 0;
  min-width: 250px; background: #ffffff;
  border: 1px solid #dedede; border-radius: 5px;
  box-shadow: 0 22px 44px rgba(124,92,250,0.16);
  padding: 10px; list-style: none; margin: 0; z-index: 9001;
  opacity: 0; visibility: hidden;
  transform: translateY(8px); pointer-events: none;
  transition: opacity .18s ease, transform .18s ease, visibility .18s ease;
}
#rn-header .rn-nav__item--has-children:hover > .rn-nav__dropdown {
  opacity: 1; visibility: visible; transform: translateY(0); pointer-events: auto;
}
#rn-header .rn-nav__dropdown::before {
  content: ''; position: absolute; top: -10px; left: 28px;
  width: 16px; height: 16px; background: #ffffff;
  border-left: 1px solid #dedede; border-top: 1px solid #dedede;
  transform: rotate(45deg);
}
#rn-header .rn-nav__dropdown .rn-nav__item { width: 100%; }
#rn-header .rn-nav__dropdown .rn-nav__link {
  display: flex !important; align-items: center; height: auto !important;
  border-radius: 5px; padding: 11px 14px !important;
  font-size: 16px !important; font-weight: 300 !important;
  width: 100%; box-shadow: none !important; line-height: 1.35;
  white-space: normal; color: #333333 !important; background: transparent !important;
}
#rn-header .rn-nav__dropdown .rn-nav__link:hover {
  background: transparent !important; color: var(--color-primary) !important;
}

/* ---- CTA knoppen ---- */
#rn-header .rn-header__cta { flex: 0 0 auto; display: flex; align-items: center; gap: 10px; }
#rn-header .rn-btn {
  display: inline-flex !important; align-items: center; justify-content: center;
  font-family: 'Balgin-Bold', serif !important;
  font-weight: 700 !important; font-size: 14px !important;
  padding: 9px 20px !important; border-radius: 8px !important;
  white-space: nowrap; cursor: pointer;
  text-decoration: none !important;
  transition: background .15s ease, color .15s ease, transform .15s ease;
}
#rn-header .rn-btn--outline {
  background: transparent !important; color: var(--color-primary) !important;
  border: 2px solid var(--color-primary) !important;
}
#rn-header .rn-btn--outline:hover { background: var(--color-primary) !important; color: #fff !important; }
#rn-header .rn-btn--accent {
  background: var(--color-primary) !important; color: #fff !important;
  border: 2px solid var(--color-primary) !important;
  box-shadow: 0 2px 8px rgba(124,92,250,0.22) !important;
}
#rn-header .rn-btn--accent:hover {
  background: var(--color-primary-dk) !important; border-color: var(--color-primary-dk) !important;
  transform: translateY(-1px);
}

/* ---- Hamburger ---- */
#rn-header .rn-header__hamburger {
  display: none; flex-direction: column; justify-content: center; gap: 5px;
  width: 40px; height: 40px;
  background: none !important; background-color: transparent !important;
  border: none !important; outline: none;
  box-shadow: none !important;
  cursor: pointer; padding: 8px; margin-left: auto; flex-shrink: 0;
}
#rn-header .rn-header__hamburger:hover,
#rn-header .rn-header__hamburger:focus,
#rn-header .rn-header__hamburger:active {
  background: none !important; background-color: transparent !important;
  border: none !important; box-shadow: none !important;
}
#rn-header .rn-hamburger__bar { display: block; width: 100%; height: 2px; background: #333333 !important; border-radius: 2px; transition: transform .25s ease, opacity .25s ease; }
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
  overflow-y: auto;
  padding: 76px 18px 18px;
  transform: translateX(100%); transition: transform .2s ease;
}
.rn-mobile-nav.is-open .rn-mobile-nav__panel { transform: translateX(0) !important; }

/* Close button */
.rn-mobile-nav__close {
  position: absolute; top: 16px; right: 16px;
  width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;
  background: transparent !important; border: 1px solid #e5e7eb !important;
  border-radius: 8px; cursor: pointer; color: #333;
}
.rn-mobile-nav__close:hover { background: #f3f4f6 !important; }

/* Nav links */
.rn-mobile-nav__list,
.rn-mobile-nav__list ul { list-style: none !important; margin: 0 !important; padding: 0 !important; display: flex !important; flex-direction: column !important; }
.rn-mobile-nav__list li { list-style: none !important; width: 100%; display: block !important; border-bottom: 1px solid #e5e7eb; }
.rn-mobile-nav__list li:last-child { border-bottom: none; }
.rn-mobile-nav__list a {
  display: block !important;
  font-family: 'Poppins', sans-serif !important;
  font-size: 16px !important; font-weight: 400 !important;
  color: #111827 !important; padding: 14px 4px !important;
  text-decoration: none !important; box-shadow: none !important;
  background: transparent !important;
}
.rn-mobile-nav__list a:hover { color: var(--color-primary) !important; }
.rn-mobile-nav__list .rn-nav__chev { display: none !important; }

/* Submenu mobile */
.rn-mobile-nav__list ul {
  border-left: 3px solid #e5e7eb !important; padding: 0 0 8px 12px !important;
  background: transparent !important; position: static !important; box-shadow: none !important;
}
.rn-mobile-nav__list ul li { border-bottom: none !important; }
.rn-mobile-nav__list ul a { font-size: 14px !important; padding: 8px 4px !important; color: #6b7280 !important; }

/* Divider + CTA knoppen */
.rn-mobile-nav__divider { height: 1px; background: #e5e7eb; margin: 14px 0; }
.rn-mobile-nav__ctas { display: flex !important; flex-direction: column; gap: 10px; }
.rn-mobile-nav__cta { width: 100%; justify-content: center; font-size: 15px !important; font-weight: 600 !important; padding: 13px 16px !important; border-radius: 8px !important; }

/* ---- Responsive ---- */
@media (max-width: 960px) {
  #rn-header .rn-header__nav,
  #rn-header .rn-header__cta { display: none !important; }
  #rn-header .rn-header__hamburger { display: flex !important; }
  .rn-mobile-nav { display: block; }
  #rn-header .rn-topbar { display: none !important; }
}
@media (min-width: 961px) {
  .rn-mobile-nav { display: none !important; }
}
@media (max-width: 480px) {
  #rn-header .rn-header__inner { padding: 0 16px !important; }
  #rn-header .rn-topbar__inner { padding: 0 16px !important; }
}

</style>

<header id="rn-header" class="rn-header" role="banner">

    <!-- Main nav -->
    <div class="rn-header__inner">

        <!-- Logo -->
        <div class="rn-header__brand">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <?php
                $custom_logo_id = get_theme_mod( 'custom_logo' );
                $logo_alt       = $custom_logo_id ? get_post_meta( $custom_logo_id, '_wp_attachment_image_alt', true ) : '';
                $site_logo      = '';

                if ( $custom_logo_id ) {
                    $site_logo = wp_get_attachment_image(
                        $custom_logo_id,
                        'full',
                        false,
                        [
                            'class'    => 'rn-header__logo',
                            'alt'      => $logo_alt ?: get_bloginfo( 'name' ),
                            'loading'  => 'eager',
                            'decoding' => 'async',
                        ]
                    );
                }

                if ( $site_logo ) {
                    echo $site_logo;
                } else {
                    $logo_path = get_stylesheet_directory_uri() . '/site-logo/site-logo.png';
                ?>
                    <img src="<?php echo esc_url( $logo_path ); ?>"
                         alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
                         class="rn-header__logo"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <?php } ?>
                <span style="display:none; font-family:'Balgin-Bold',serif; font-size:22px; color:var(--color-primary);"><?php bloginfo('name'); ?></span>
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
                'walker'         => new Equitee_Nav_Walker(),
            ]);
            ?>
        </nav>

        <!-- CTA knoppen -->
        <div class="rn-header__cta">
            <a href="/verkopen/" class="rn-btn rn-btn--accent">
                Ik wil verkopen
            </a>
        </div>

        <!-- Hamburger (mobile) -->
        <button class="rn-header__hamburger" aria-label="Menu openen" aria-expanded="false" aria-controls="rn-mobile-nav">
            <span class="rn-hamburger__bar"></span>
            <span class="rn-hamburger__bar"></span>
            <span class="rn-hamburger__bar"></span>
        </button>

    </div><!-- /.rn-header__inner -->

</header>

<!-- Mobile nav (side drawer) -->
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
            <a href="/verkopen/" class="rn-btn rn-btn--accent rn-mobile-nav__cta">
                Ik wil verkopen
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

  if (closeBtn) closeBtn.addEventListener('click', closeMenu);

  mobileNav.addEventListener('click', (e) => {
    if (!e.target.closest('.rn-mobile-nav__panel')) closeMenu();
  });

  mobileNav.querySelectorAll('a').forEach(link => {
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
