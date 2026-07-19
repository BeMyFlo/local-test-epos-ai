document.querySelectorAll('[data-service-gallery]').forEach((gallery) => {
  if (gallery.dataset.serviceGalleryInitialized === 'true') return;

  const track = gallery.querySelector('.achiever-service-detail__gallery-grid');
  const previous = gallery.querySelector('.achiever-service-detail__gallery-arrow--prev');
  const next = gallery.querySelector('.achiever-service-detail__gallery-arrow--next');
  if (!track || !previous || !next) return;

  gallery.dataset.serviceGalleryInitialized = 'true';
  const scroll = (direction) => {
    const item = track.firstElementChild;
    const gap = Number.parseFloat(window.getComputedStyle(track).columnGap) || 0;
    const amount = item ? item.getBoundingClientRect().width + gap : track.clientWidth;
    track.scrollBy({ left: direction * amount, behavior: 'smooth' });
  };

  previous.addEventListener('click', () => scroll(-1));
  next.addEventListener('click', () => scroll(1));
});
