import initMarquee from '../_shared/marquee.js';

function initClassesGalleries() {
  document.querySelectorAll('[data-classes-gallery-slider]').forEach((slider) => {
    if (slider.dataset.classesGalleryInitialized === 'true') return;

    const track = slider.querySelector('[data-classes-gallery-track]');
    const previous = slider.querySelector('[data-classes-gallery-prev]');
    const next = slider.querySelector('[data-classes-gallery-next]');
    if (!track || !previous || !next) return;

    slider.dataset.classesGalleryInitialized = 'true';
    initMarquee(slider, track, previous, next, { autoDrift: true });
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initClassesGalleries);
} else {
  initClassesGalleries();
}
