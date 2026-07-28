import initMarquee from '../_shared/marquee.js';

function initCourseGallerySliders() {
  document.querySelectorAll('[data-course-gallery-slider]').forEach((slider) => {
    if (slider.dataset.courseGallerySliderInitialized === 'true') return;

    const track = slider.querySelector('[data-course-gallery-track]');
    if (!track) return;

    slider.dataset.courseGallerySliderInitialized = 'true';
    initMarquee(
      slider,
      track,
      slider.querySelector('[data-course-gallery-prev]'),
      slider.querySelector('[data-course-gallery-next]'),
      { autoDrift: true }
    );
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initCourseGallerySliders);
} else {
  initCourseGallerySliders();
}
