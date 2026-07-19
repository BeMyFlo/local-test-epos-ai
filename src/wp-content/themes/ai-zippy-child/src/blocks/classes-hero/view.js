function initClassesSliders() {
  document.querySelectorAll('[data-classes-slider]').forEach((slider) => {
    if (slider.dataset.classesInitialized === 'true') return;

    const track = slider.querySelector('[data-classes-track]');
    const previous = slider.querySelector('[data-classes-prev]');
    const next = slider.querySelector('[data-classes-next]');
    if (!track || !previous || !next) return;

    slider.dataset.classesInitialized = 'true';
    const updateArrows = () => {
      const maxScroll = Math.max(0, track.scrollWidth - track.clientWidth);
      previous.disabled = track.scrollLeft <= 2;
      next.disabled = track.scrollLeft >= maxScroll - 2;
    };
    const scroll = (direction) => {
      const card = track.querySelector('.achiever-classes-hero__card');
      const gap = Number.parseFloat(getComputedStyle(track).columnGap) || 0;
      const amount = card ? card.getBoundingClientRect().width + gap : track.clientWidth * 0.8;
      const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      track.scrollBy({ left: direction * amount, behavior: reducedMotion ? 'auto' : 'smooth' });
    };

    previous.addEventListener('click', () => scroll(-1));
    next.addEventListener('click', () => scroll(1));
    track.addEventListener('scroll', updateArrows, { passive: true });
    window.addEventListener('resize', updateArrows, { passive: true });
    updateArrows();
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initClassesSliders);
} else {
  initClassesSliders();
}
