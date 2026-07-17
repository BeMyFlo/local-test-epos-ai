/**
 * Hero Slider — Frontend behavior
 * Auto-rotates slides, supports prev/next arrows and dot navigation.
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    const heros = document.querySelectorAll('.achiever-hero');

    heros.forEach(function (hero) {
      const slidesContainer = hero.querySelector('.achiever-hero__slides');
      if (!slidesContainer) return;

      const slides = slidesContainer.querySelectorAll('.achiever-hero__slide');
      const dots = hero.querySelectorAll('.achiever-hero__dot');
      const prevBtn = hero.querySelector('.achiever-hero__arrow--prev');
      const nextBtn = hero.querySelector('.achiever-hero__arrow--next');

      if (slides.length < 2) return;

      let current = 0;
      let timer = null;
      const autoplay = hero.dataset.autoplay === 'true';
      const speed = parseInt(hero.dataset.speed, 10) || 5000;

      function goTo(index) {
        slides[current].classList.remove('achiever-hero__slide--active');
        if (dots[current]) dots[current].classList.remove('achiever-hero__dot--active');

        current = (index + slides.length) % slides.length;

        slides[current].classList.add('achiever-hero__slide--active');
        if (dots[current]) dots[current].classList.add('achiever-hero__dot--active');
      }

      function next() {
        goTo(current + 1);
      }

      function prev() {
        goTo(current - 1);
      }

      function startAutoplay() {
        if (autoplay && !timer) {
          timer = setInterval(next, speed);
        }
      }

      function stopAutoplay() {
        if (timer) {
          clearInterval(timer);
          timer = null;
        }
      }

      // Arrow clicks
      if (prevBtn) {
        prevBtn.addEventListener('click', function () {
          stopAutoplay();
          prev();
          startAutoplay();
        });
      }

      if (nextBtn) {
        nextBtn.addEventListener('click', function () {
          stopAutoplay();
          next();
          startAutoplay();
        });
      }

      // Dot clicks
      dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
          stopAutoplay();
          goTo(parseInt(dot.dataset.goto, 10));
          startAutoplay();
        });
      });

      // Pause on hover
      hero.addEventListener('mouseenter', stopAutoplay);
      hero.addEventListener('mouseleave', startAutoplay);

      // Start
      startAutoplay();
    });
  });
})();
