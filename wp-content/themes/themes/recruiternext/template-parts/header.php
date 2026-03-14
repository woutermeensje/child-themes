<?php
/**
 * Recruiternext – Custom header template
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<header id="rn-header" class="rn-header" role="banner">

    <!-- Utility bar -->
    <div class="rn-topbar">
        <div class="rn-topbar__inner">
            <div class="rn-topbar__left">
                <a href="<?php echo esc_url( home_url( '/vacature-plaatsen/' ) ); ?>">Vacature Plaatsen</a>
                <span class="rn-topbar__divider">|</span>
                <a href="<?php echo esc_url( home_url( '/freelance-opdrachten/' ) ); ?>">Freelance Opdrachten</a>
            </div>
            <div class="rn-topbar__right">
                <a href="<?php echo esc_url( home_url( '/tarieven/' ) ); ?>">Tarieven</a>
                <span class="rn-topbar__divider">|</span>
                <a href="mailto:support@recruiternext.nl">Support@recruiternext.nl</a>
            </div>
        </div>
    </div>

    <!-- Main nav -->
    <div class="rn-header__inner">

        <!-- Logo -->
        <div class="rn-header__brand">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/site-logo/recruiternext-site-logo.png' ); ?>"
                     alt="<?php bloginfo( 'name' ); ?>"
                     class="rn-header__logo">
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
            <a href="<?php echo esc_url( home_url( '/inloggen/' ) ); ?>" class="rn-btn rn-btn--outline">
                Inloggen
            </a>
            <a href="<?php echo esc_url( home_url( '/aanmelden/' ) ); ?>" class="rn-btn rn-btn--accent">
                Aanmelden
            </a>
        </div>

        <!-- Hamburger (mobile) -->
        <button class="rn-header__hamburger" aria-label="Menu openen" aria-expanded="false" aria-controls="rn-mobile-nav">
            <span class="rn-hamburger__bar"></span>
            <span class="rn-hamburger__bar"></span>
            <span class="rn-hamburger__bar"></span>
        </button>

    </div><!-- /.rn-header__inner -->

    <!-- Mobile nav -->
    <div id="rn-mobile-nav" class="rn-mobile-nav" aria-hidden="true">
        <?php
        wp_nav_menu([
            'theme_location' => 'primary_nav',
            'container'      => false,
            'menu_class'     => 'rn-mobile-nav__list',
            'fallback_cb'    => false,
        ]);
        ?>
        <div class="rn-mobile-nav__ctas">
            <a href="<?php echo esc_url( home_url( '/inloggen/' ) ); ?>" class="rn-btn rn-btn--outline rn-mobile-nav__cta">
                Inloggen
            </a>
            <a href="<?php echo esc_url( home_url( '/aanmelden/' ) ); ?>" class="rn-btn rn-btn--accent rn-mobile-nav__cta">
                Aanmelden
            </a>
        </div>
    </div>

</header>


<style>
:root {
  /* Gekoppeld aan thema :root kleuren uit style.css */
  --rn-primary:    var(--color-primary,    #0458AB);
  --rn-primary-dk: var(--color-primary-dk, #034085);
  --rn-primary-mid:var(--color-primary-mid,#3B82F6);
  --rn-accent:     var(--color-secondary,  #CADDF6);
  --rn-accent-dk:  #b8cde8;
  --rn-text:       var(--color-text,       #333333);
  --rn-bg:         var(--color-bg,         #ffffff);
  --rn-border:     var(--color-border,     #e8ecf0);
  --rn-shadow:     0 2px 16px rgba(0,0,0,0.08);
  --rn-header-h:   68px;
  --rn-topbar-h:   36px;
}

.rn-header *, .rn-header *::before, .rn-header *::after { box-sizing: border-box; }

/* ---- Header wrapper ---- */
.rn-header {
  position: sticky;
  top: 0;
  z-index: 9000;
  background: var(--rn-bg);
  box-shadow: var(--rn-shadow);
  width: 100%;
}

/* ---- Utility bar ---- */
.rn-topbar {
  background: var(--rn-primary);
  width: 100%;
}

.rn-topbar__inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  height: var(--rn-topbar-h);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.rn-topbar__left,
.rn-topbar__right {
  display: flex;
  align-items: center;
  gap: 10px;
}

.rn-topbar a {
  font-family: 'Poppins', sans-serif;
  font-size: 13px;
  font-weight: 500;
  color: rgba(255,255,255,0.9) !important;
  text-decoration: none !important;
  transition: color .15s ease;
}

.rn-topbar a:hover { color: #fff !important; text-decoration: underline !important; }

.rn-topbar__divider {
  color: rgba(255,255,255,0.4);
  font-size: 13px;
}

/* ---- Main nav bar ---- */
.rn-header__inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  height: var(--rn-header-h);
  display: flex;
  align-items: center;
  gap: 24px;
}


/* ---- Brand / Logo ---- */
.rn-header__brand { flex: 0 0 auto; display: flex; align-items: center; }
.rn-header__brand a { display: block; text-decoration: none !important; }
.rn-header__logo { display: block; height: 44px; width: auto; }

.rn-header__site-name {
  font-family: 'Poppins', sans-serif;
  font-size: 22px;
  font-weight: 700;
  color: var(--rn-primary) !important;
  text-decoration: none !important;
  white-space: nowrap;
}

/* ---- Desktop nav ---- */
.rn-header__nav { flex: 1 1 auto; display: flex; align-items: center; justify-content: flex-end; }

.rn-nav__list { list-style: none; margin: 0; padding: 0; display: flex; align-items: center; gap: 2px; }
.rn-nav__item { position: relative; list-style: none; margin: 0; padding: 0; }

.rn-nav__link {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 8px 12px;
  font-family: 'Poppins', sans-serif;
  font-size: 15px;
  font-weight: 300;
  color: var(--rn-text) !important;
  text-decoration: none !important;
  border-radius: 6px;
  transition: background .15s ease, color .15s ease;
  white-space: nowrap;
  height: var(--rn-header-h);
}

.rn-nav__link:hover { color: var(--rn-primary) !important; }
.rn-nav__link.is-active { color: var(--rn-primary) !important; font-weight: 600; }


/* Chevron */
.rn-nav__chev {
  width: 7px; height: 7px;
  border-right: 2px solid currentColor;
  border-bottom: 2px solid currentColor;
  transform: rotate(45deg) translateY(-2px);
  transition: transform .2s ease;
  flex-shrink: 0;
}
.rn-nav__item--has-children:hover > .rn-nav__link .rn-nav__chev { transform: rotate(-135deg) translateY(-2px); }

/* Dropdown */
.rn-nav__dropdown {
  display: none;
  position: absolute;
  top: 100%;
  left: 0;
  min-width: 210px;
  background: #fff;
  border: 1px solid var(--rn-border);
  border-top: 3px solid var(--rn-primary);
  border-radius: 0 0 8px 8px;
  box-shadow: 0 12px 40px rgba(0,0,0,0.12);
  padding: 6px;
  list-style: none;
  margin: 0;
  z-index: 9001;
}
.rn-nav__item--has-children:hover > .rn-nav__dropdown { display: block; }
.rn-nav__dropdown .rn-nav__item { width: 100%; }
.rn-nav__dropdown .rn-nav__link {
  display: block;
  height: auto;
  border-radius: 6px;
  padding: 9px 14px;
  font-size: 14px;
  font-weight: 400;
  width: 100%;
  box-shadow: none !important;
}
.rn-nav__dropdown .rn-nav__link:hover { background: rgba(4,88,171,0.06); }

/* ---- CTA knoppen ---- */
.rn-header__cta {
  flex: 0 0 auto;
  display: flex;
  align-items: center;
  gap: 10px;
}

.rn-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  text-decoration: none !important;
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  font-size: 14px;
  padding: 9px 20px;
  border-radius: 6px;
  transition: background .15s ease, color .15s ease, transform .15s ease;
  white-space: nowrap;
  cursor: pointer;
}

.rn-btn--outline {
  background: transparent;
  color: var(--rn-primary) !important;
  border: 2px solid var(--rn-primary);
}
.rn-btn--outline:hover {
  background: var(--rn-primary);
  color: #fff !important;
}

.rn-btn--accent {
  background: var(--rn-primary-mid);
  color: #fff !important;
  border: 2px solid var(--rn-primary-mid);
  box-shadow: 0 2px 8px rgba(59,130,246,0.3);
}
.rn-btn--accent:hover {
  background: var(--rn-primary);
  border-color: var(--rn-primary);
  transform: translateY(-1px);
}

/* ---- Hamburger ---- */
.rn-header__hamburger {
  display: none;
  flex-direction: column;
  justify-content: center;
  gap: 5px;
  width: 40px; height: 40px;
  background: transparent;
  border: 1.5px solid var(--rn-border);
  border-radius: 6px;
  cursor: pointer;
  padding: 8px;
  margin-left: auto;
  flex-shrink: 0;
}
.rn-hamburger__bar { display: block; width: 100%; height: 2px; background: var(--rn-text); border-radius: 2px; transition: transform .25s ease, opacity .25s ease; }
.rn-header__hamburger.is-open .rn-hamburger__bar:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.rn-header__hamburger.is-open .rn-hamburger__bar:nth-child(2) { opacity: 0; transform: scaleX(0); }
.rn-header__hamburger.is-open .rn-hamburger__bar:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

/* ---- Mobile nav ---- */
.rn-mobile-nav {
  display: none;
  flex-direction: column;
  background: #fff;
  border-top: 1px solid var(--rn-border);
  padding: 0 20px;
  max-height: 0;
  overflow: hidden;
  transition: max-height .35s ease, padding .35s ease;
}
.rn-mobile-nav.is-open { max-height: 700px; padding: 16px 20px 24px; }

.rn-mobile-nav__list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; }
.rn-mobile-nav__list .rn-nav__item { width: 100%; border-bottom: 1px solid var(--rn-border); }
.rn-mobile-nav__list .rn-nav__item:last-child { border-bottom: none; }
.rn-mobile-nav__list .rn-nav__link {
  display: flex;
  justify-content: space-between;
  height: auto;
  font-size: 15px;
  font-weight: 500;
  padding: 13px 4px;
  border-radius: 0;
  box-shadow: none !important;
  color: var(--rn-text) !important;
}
.rn-mobile-nav__list .rn-nav__link:hover { background: transparent; color: var(--rn-primary) !important; }

/* Submenu mobile */
.rn-mobile-nav__list .rn-nav__dropdown {
  display: block;
  position: static;
  box-shadow: none;
  border: none;
  border-top: 3px solid var(--rn-primary);
  border-radius: 0;
  padding: 4px 0 8px;
  background: transparent;
  margin: 0;
}
.rn-mobile-nav__list .rn-nav__dropdown .rn-nav__item { border-bottom: none; }
.rn-mobile-nav__list .rn-nav__dropdown .rn-nav__link { padding: 8px 12px; font-size: 14px; font-weight: 400; color: #555 !important; }

/* CTA knoppen mobile */
.rn-mobile-nav__ctas {
  display: flex;
  gap: 10px;
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid var(--rn-border);
}
.rn-mobile-nav__cta { flex: 1; justify-content: center; font-size: 14px; padding: 11px 16px; }

/* ---- Responsive ---- */
@media (max-width: 960px) {
  .rn-header__nav, .rn-header__cta { display: none; }
  .rn-header__hamburger { display: flex; }
  .rn-mobile-nav { display: flex; }
  .rn-topbar__right { display: none; }
}

@media (max-width: 480px) {
  .rn-header__inner { padding: 0 16px; gap: 12px; }
  .rn-topbar__inner { padding: 0 16px; }
}
</style>


<script>
(function () {
  const hamburger = document.querySelector('.rn-header__hamburger');
  const mobileNav = document.getElementById('rn-mobile-nav');
  if (!hamburger || !mobileNav) return;

  hamburger.addEventListener('click', () => {
    const isOpen = hamburger.classList.toggle('is-open');
    mobileNav.classList.toggle('is-open', isOpen);
    hamburger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    mobileNav.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('#rn-header')) {
      hamburger.classList.remove('is-open');
      mobileNav.classList.remove('is-open');
      hamburger.setAttribute('aria-expanded', 'false');
      mobileNav.setAttribute('aria-hidden', 'true');
    }
  });
})();
</script>
