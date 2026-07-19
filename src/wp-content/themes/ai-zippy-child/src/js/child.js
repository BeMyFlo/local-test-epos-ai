/**
 * Achiever's Art — Child Theme JavaScript
 * 
 * Handles:
 * - Scroll fade-up animations (IntersectionObserver)
 * - Mobile menu toggle
 * - Smooth scroll for anchor links
 */

// === Fade-up scroll animations ===
document.addEventListener('DOMContentLoaded', () => {
  const fadeElements = document.querySelectorAll('.fade-up');
  
  if (fadeElements.length) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.1, rootMargin: '0px 0px -50px 0px' }
    );

    fadeElements.forEach((el) => observer.observe(el));
  }

  // === Gutenberg mobile navigation fallback ===
  document.querySelectorAll('.achiever-header__nav').forEach((navigation, index) => {
    if (navigation.dataset.mobileNavigationInitialized === 'true') return;

    const openButton = navigation.querySelector('.wp-block-navigation__responsive-container-open');
    const container = navigation.querySelector('.wp-block-navigation__responsive-container');
    const closeButton = navigation.querySelector('.wp-block-navigation__responsive-container-close');
    const content = navigation.querySelector('.wp-block-navigation__responsive-container-content');
    const dialog = navigation.querySelector('.wp-block-navigation__responsive-dialog');
    if (!openButton || !container || !closeButton || !content) return;

    navigation.dataset.mobileNavigationInitialized = 'true';
    if (!container.id) container.id = `achiever-mobile-navigation-${index + 1}`;
    openButton.setAttribute('aria-controls', container.id);
    openButton.setAttribute('aria-expanded', 'false');
    container.setAttribute('aria-hidden', 'true');
    dialog?.setAttribute('role', 'dialog');
    dialog?.setAttribute('aria-modal', 'true');
    dialog?.setAttribute('aria-label', 'Site navigation');

    const isOpen = () => container.classList.contains('is-menu-open');
    const closeMenu = ({ restoreFocus = true } = {}) => {
      container.classList.remove('is-menu-open', 'has-modal-open');
      openButton.setAttribute('aria-expanded', 'false');
      container.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('menu-open');
      if (restoreFocus && openButton.offsetParent !== null) openButton.focus();
    };
    const openMenu = () => {
      container.classList.add('is-menu-open', 'has-modal-open');
      openButton.setAttribute('aria-expanded', 'true');
      container.setAttribute('aria-hidden', 'false');
      document.body.classList.add('menu-open');
      closeButton.focus();
    };

    openButton.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopImmediatePropagation();
      isOpen() ? closeMenu() : openMenu();
    });
    closeButton.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopImmediatePropagation();
      closeMenu();
    });
    content.querySelectorAll('a[href]').forEach((link) => {
      link.addEventListener('click', () => closeMenu({ restoreFocus: false }));
    });
    container.addEventListener('click', (event) => {
      if (event.target === container || (!content.contains(event.target) && !closeButton.contains(event.target))) {
        closeMenu();
      }
    });
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && isOpen()) {
        event.preventDefault();
        closeMenu();
      }
    });
    window.addEventListener('resize', () => {
      if (window.innerWidth > 1023 && isOpen()) closeMenu({ restoreFocus: false });
    });
  });

  // === Keep floating controls clear of important content ===
  const floatingControls = [...document.querySelectorAll('.achiever-chat-bubble, .az-scroll-top')];
  if (floatingControls.length) {
    const protectedElements = [...document.querySelectorAll('main h1, main h2, main h3, main form, main a[href], main button, main [data-floating-protected], .achiever-footer')];
    let floatingFrame = 0;

    const intersects = (first, second) => (
      first.left < second.right
      && first.right > second.left
      && first.top < second.bottom
      && first.bottom > second.top
    );
    const setContentGuardState = (control, hidden) => {
      const shouldHide = hidden && document.activeElement !== control;
      const isScrollTop = control.classList.contains('az-scroll-top');
      control.classList.toggle('is-content-obscured', shouldHide);
      if (shouldHide) {
        if (!Object.hasOwn(control.dataset, 'contentGuardTabindex')) {
          control.dataset.contentGuardTabindex = control.hasAttribute('tabindex')
            ? control.getAttribute('tabindex')
            : '__none__';
        }
        control.setAttribute('tabindex', '-1');
        control.setAttribute('aria-hidden', 'true');
      } else if (isScrollTop) {
        const isVisible = control.classList.contains('is-visible');
        control.setAttribute('tabindex', isVisible ? '0' : '-1');
        control.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
        delete control.dataset.contentGuardTabindex;
      } else {
        if (control.dataset.contentGuardTabindex === '__none__') {
          control.removeAttribute('tabindex');
        } else if (Object.hasOwn(control.dataset, 'contentGuardTabindex')) {
          control.setAttribute('tabindex', control.dataset.contentGuardTabindex);
        }
        delete control.dataset.contentGuardTabindex;
        control.removeAttribute('aria-hidden');
      }
    };
    const updateFloatingControls = () => {
      floatingFrame = 0;
      const activeProtectedRects = protectedElements
        .map((element) => element.getBoundingClientRect())
        .filter((rect) => rect.width > 0 && rect.height > 0 && rect.bottom > 0 && rect.top < window.innerHeight);
      floatingControls.forEach((control) => {
        const controlRect = control.getBoundingClientRect();
        const shouldHide = activeProtectedRects.some((protectedRect) => intersects(controlRect, protectedRect));
        setContentGuardState(control, shouldHide);
      });
    };
    const scheduleFloatingControls = () => {
      if (floatingFrame) return;
      floatingFrame = window.requestAnimationFrame(updateFloatingControls);
    };

    updateFloatingControls();
    window.addEventListener('scroll', scheduleFloatingControls, { passive: true });
    window.addEventListener('resize', scheduleFloatingControls, { passive: true });
    window.addEventListener('load', scheduleFloatingControls, { once: true });
    document.fonts?.ready.then(scheduleFloatingControls);
  }

  // === Smooth scroll for anchor links ===
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener('click', (e) => {
      const targetId = anchor.getAttribute('href');
      if (targetId === '#') return;
      
      const target = document.querySelector(targetId);
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });
});

// === Source mockup horizontal sliders ===
function initAchieverScrollSliders() {
  document.querySelectorAll('[data-scroll-slider]').forEach((slider) => {
    if (slider.dataset.scrollInitialized === 'true') return;

    const track = slider.querySelector('[data-scroll-track]');
    const previous = slider.querySelector('[data-scroll-prev]');
    const next = slider.querySelector('[data-scroll-next]');
    if (!track || !previous || !next) return;

    slider.dataset.scrollInitialized = 'true';

    const move = (direction) => {
      const item = track.firstElementChild;
      const styles = window.getComputedStyle(track);
      const parsedGap = Number.parseFloat(styles.columnGap || styles.gap || '0');
      const gap = Number.isFinite(parsedGap) ? parsedGap : 0;
      const amount = item ? item.getBoundingClientRect().width + gap : track.clientWidth * 0.8;
      track.scrollBy({ left: direction * Math.max(1, amount), behavior: 'smooth' });
    };

    previous.addEventListener('click', () => move(-1));
    next.addEventListener('click', () => move(1));
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initAchieverScrollSliders);
} else {
  initAchieverScrollSliders();
}
