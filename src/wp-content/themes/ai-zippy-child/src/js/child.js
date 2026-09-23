/**
 * child.js — theme-global front-end behaviour for pricemrcopper.
 *
 * Scope is deliberately tiny. `docs/build/block-map.md` §4 assigns exactly ONE
 * behaviour row to this file:
 *
 *   B14  Hamburger button (added in S2b, ≤900px)
 *        → open/close the mobile nav panel, toggle `aria-expanded`,
 *          close on click-outside / Esc / link click / resize-to-desktop,
 *          and lock body scroll while open.
 *        Target: the header nav (`nav#mainNav` in parts/header.html).
 *
 * Everything else in the behavior map belongs to a block's own `view.js`
 * (film lightbox B10/B11, ticket modal B5–B9, custom-order B1–B4, forms
 * B12/B13) or is native markup (B15–B17 real links + smooth scroll). The
 * mini-cart drawer of the prior-art build is NOT ported — WooCommerce stays
 * OFF (block-map D1) and the mockup header has no cart.
 *
 * Vanilla JS only: no jQuery, no framework. Loaded as an ES module by
 * functions.php (`script_loader_tag` filter).
 */

const BREAKPOINT = 900; // matches @media (max-width:900px) in src/scss/_header.scss

const initMobileNav = () => {
  const toggle = document.querySelector('.pmc-nav-toggle');
  const nav = document.getElementById('mainNav');
  if (!toggle || !nav) return;

  const isOpen = () => nav.classList.contains('is-open');

  const setOpen = (open) => {
    if (isOpen() === open) return;

    nav.classList.toggle('is-open', open);
    toggle.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.setAttribute('aria-label', open ? 'Close menu' : 'Menu');
    // Scroll lock — `body.pmc-nav-open { overflow:hidden }` in _header.scss.
    document.body.classList.toggle('pmc-nav-open', open);

    if (open) {
      const first = nav.querySelector('a');
      if (first) first.focus({ preventScroll: true });
    } else {
      toggle.focus({ preventScroll: true });
    }
  };

  toggle.addEventListener('click', (event) => {
    event.stopPropagation();
    setOpen(!isOpen());
  });

  // Navigate away / to an in-page anchor → close the panel.
  nav.addEventListener('click', (event) => {
    if (event.target.closest('a')) setOpen(false);
  });

  // Click-outside.
  document.addEventListener('click', (event) => {
    if (!isOpen()) return;
    if (event.target.closest('.pmc-nav') || event.target.closest('.pmc-nav-toggle')) return;
    setOpen(false);
  });

  // Esc.
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && isOpen()) setOpen(false);
  });

  // Growing back past the breakpoint must not leave the lock on <body>.
  window.addEventListener('resize', () => {
    if (window.innerWidth > BREAKPOINT && isOpen()) setOpen(false);
  });
};

const ready = (fn) => {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', fn, { once: true });
  } else {
    fn();
  }
};

ready(initMobileNav);
