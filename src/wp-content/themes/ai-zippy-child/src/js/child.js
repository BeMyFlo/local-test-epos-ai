/**
 * Achiever's Art — Child Theme JavaScript
 * 
 * Handles:
 * - Scroll fade-up animations (IntersectionObserver)
 * - Mobile menu toggle
 * - Smooth scroll for anchor links
 */

import initMarquee from '../blocks/_shared/marquee.js';

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
    if (!track) return;

    slider.dataset.scrollInitialized = 'true';

    // Store original index on each item for dot tracking
    const initialItems = Array.from(track.children);
    initialItems.forEach((item, index) => {
      item.dataset.originalIndex = index;
    });

    // Unified smooth slider mechanics. Auto-drift only for full-width photo
    // strips marked with data-scroll-marquee / data-scroll-autoplay — card
    // sliders stay still and move only on arrow clicks (smooth, infinite loop).
    const marqueeApi = initMarquee(slider, track, previous, next, {
      speed: Number.parseFloat(slider.dataset.scrollMarquee) || 1.6,
      autoDrift: slider.hasAttribute('data-scroll-marquee') || slider.hasAttribute('data-scroll-autoplay'),
      onWrap: () => updateDots(),
    });

    // Support dots click & scroll active state sync with dynamic item count.
    // Only direct siblings count — a deep querySelector let unrelated sliders
    // higher in the DOM claim (and hijack) another section's dots.
    const parent = slider.parentElement;
    let dotsContainer = parent
      ? Array.from(parent.children).find((el) => el.matches('.achiever-testimonials__dots, .achiever-scroll-dots'))
      : null;
    if (!dotsContainer) {
      dotsContainer = slider.querySelector('.achiever-scroll-dots');
    }
    if (dotsContainer && dotsContainer.dataset.dotsBound === 'true') {
      dotsContainer = null;
    }
    if (dotsContainer) {
      dotsContainer.dataset.dotsBound = 'true';
    }

    let dots = [];
    if (dotsContainer) {
      const dotClassName = dotsContainer.classList.contains('achiever-testimonials__dots')
        ? 'achiever-testimonials__dot'
        : 'achiever-scroll-dot';

      if (initialItems.length > 0) {
        dotsContainer.innerHTML = '';
        initialItems.forEach((_, index) => {
          const dot = document.createElement('span');
          dot.className = `${dotClassName}${index === 0 ? ` ${dotClassName}--active active` : ''}`;
          dot.setAttribute('aria-label', `Go to slide ${index + 1}`);
          dotsContainer.appendChild(dot);
        });
      }

      dots = Array.from(dotsContainer.querySelectorAll(`.${dotClassName}, .achiever-testimonials__dot, .achiever-scroll-dot`));

      dots.forEach((dot, index) => {
        dot.style.cursor = 'pointer';
        dot.addEventListener('click', () => {
          // Glide the track itself (circular, shortest direction) — never
          // scrollIntoView, which also scrolls the page vertically.
          const total = initialItems.length;
          const current = Number.parseInt(track.firstElementChild?.dataset.originalIndex ?? '0', 10) || 0;
          const forward = (index - current + total) % total;
          const backward = (current - index + total) % total;
          if (forward === 0 || !marqueeApi) return;
          if (forward <= backward) marqueeApi.glideItems(forward, 1);
          else marqueeApi.glideItems(backward, -1);
          setTimeout(updateDots, 600);
        });
      });
    }

    const updateDots = () => {
      if (!dots.length) return;

      const currentItems = Array.from(track.children);
      if (!currentItems.length) return;

      let activeIndex = 0;
      let minDistance = Infinity;

      currentItems.forEach((item) => {
        const dist = Math.abs(item.offsetLeft - track.scrollLeft);
        if (dist < minDistance) {
          minDistance = dist;
          if (item.dataset.originalIndex !== undefined) {
            activeIndex = parseInt(item.dataset.originalIndex, 10);
          }
        }
      });

      dots.forEach((dot, i) => {
        const isAct = i === activeIndex;
        dot.classList.toggle('achiever-testimonials__dot--active', isAct);
        dot.classList.toggle('achiever-scroll-dot--active', isAct);
        dot.classList.toggle('active', isAct);
      });
    };

    track.addEventListener('scroll', updateDots, { passive: true });
    updateDots();
    // Layout shifts while images lazy-load can leave the initial computation
    // stale — re-sync once everything has settled.
    window.addEventListener('load', updateDots, { once: true });
    setTimeout(updateDots, 1500);
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initAchieverScrollSliders);
} else {
  initAchieverScrollSliders();
}
