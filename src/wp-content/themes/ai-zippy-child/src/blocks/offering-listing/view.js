function initOfferingListings() {
  document.querySelectorAll('[data-offering-listing="true"]').forEach((listing) => {
    if (listing.dataset.offeringInitialized === 'true') return;
    listing.dataset.offeringInitialized = 'true';

    const track = listing.querySelector('[data-offering-track]');
    const previous = listing.querySelector('[data-offering-prev]');
    const next = listing.querySelector('[data-offering-next]');
    if (!track || !previous || !next) return;

    const scroll = (direction) => {
      const card = track.querySelector('.achiever-offering-listing__card');
      const amount = card ? card.getBoundingClientRect().width + 24 : track.clientWidth * 0.8;
      const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      track.scrollBy({ left: direction * amount, behavior: reducedMotion ? 'auto' : 'smooth' });
    };

    previous.addEventListener('click', () => scroll(-1));
    next.addEventListener('click', () => scroll(1));
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initOfferingListings);
} else {
  initOfferingListings();
}
