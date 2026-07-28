import initMarquee from '../_shared/marquee.js';

function initClassesSliders() {
  document.querySelectorAll('[data-classes-slider]').forEach((slider) => {
    if (slider.dataset.classesInitialized === 'true') return;

    const track = slider.querySelector('[data-classes-track]');
    const previous = slider.querySelector('[data-classes-prev]');
    const next = slider.querySelector('[data-classes-next]');
    if (!track || !previous || !next) return;

    slider.dataset.classesInitialized = 'true';
    initMarquee(slider, track, previous, next);
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initClassesSliders);
} else {
  initClassesSliders();
}
