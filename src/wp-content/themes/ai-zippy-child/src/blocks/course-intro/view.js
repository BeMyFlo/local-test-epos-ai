document.querySelectorAll('[data-course-gallery]').forEach((gallery) => {
  if (gallery.dataset.courseGalleryInitialized === 'true') return;

  const track = gallery.querySelector('.achiever-course-intro__gallery-track');
  const previous = gallery.querySelector('.achiever-course-intro__gallery-arrow--prev');
  const next = gallery.querySelector('.achiever-course-intro__gallery-arrow--next');
  if (!track || !previous || !next) return;

  gallery.dataset.courseGalleryInitialized = 'true';
  const scroll = (direction) => {
    const slide = track.querySelector('.achiever-course-intro__gallery-item');
    const gap = Number.parseFloat(window.getComputedStyle(track).columnGap) || 0;
    const amount = slide ? slide.getBoundingClientRect().width + gap : track.clientWidth;
    track.scrollBy({ left: direction * amount, behavior: 'smooth' });
  };

  previous.addEventListener('click', () => scroll(-1));
  next.addEventListener('click', () => scroll(1));
});
