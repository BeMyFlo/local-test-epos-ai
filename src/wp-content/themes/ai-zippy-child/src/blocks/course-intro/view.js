import initMarquee from '../_shared/marquee.js';

function initCourseGalleries() {
  document.querySelectorAll('[data-course-gallery]').forEach((gallery) => {
    if (gallery.dataset.courseGalleryInitialized === 'true') return;

    const track = gallery.querySelector('.achiever-course-intro__gallery-track');
    const previous = gallery.querySelector('.achiever-course-intro__gallery-arrow--prev');
    const next = gallery.querySelector('.achiever-course-intro__gallery-arrow--next');
    if (!track || !previous || !next) return;

    gallery.dataset.courseGalleryInitialized = 'true';
    initMarquee(gallery, track, previous, next, { autoDrift: true });
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initCourseGalleries);
} else {
  initCourseGalleries();
}
