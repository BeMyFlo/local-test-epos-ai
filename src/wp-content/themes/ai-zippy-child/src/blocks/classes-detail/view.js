function initClassesGalleries() {
  document.querySelectorAll('[data-classes-gallery-slider]').forEach((slider) => {
    if (slider.dataset.classesGalleryInitialized === 'true') return;

    const track = slider.querySelector('[data-classes-gallery-track]');
    const previous = slider.querySelector('[data-classes-gallery-prev]');
    const next = slider.querySelector('[data-classes-gallery-next]');
    if (!track || !previous || !next) return;

    slider.dataset.classesGalleryInitialized = 'true';
    const updateArrows = () => {
      const maxScroll = Math.max(0, track.scrollWidth - track.clientWidth);
      previous.disabled = track.scrollLeft <= 2;
      next.disabled = track.scrollLeft >= maxScroll - 2;
    };
    const scroll = (direction) => {
      const item = track.querySelector('.achiever-classes-detail__gallery-item');
      const gap = Number.parseFloat(getComputedStyle(track).columnGap) || 0;
      const amount = item ? item.getBoundingClientRect().width + gap : track.clientWidth * 0.85;
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
  document.addEventListener('DOMContentLoaded', initClassesGalleries);
} else {
  initClassesGalleries();
}
