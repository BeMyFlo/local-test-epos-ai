/**
 * Achiever's Art — Child Theme JavaScript
 * 
 * Handles:
 * - Scroll fade-up animations (IntersectionObserver)
 * - Mobile menu toggle
 * - Smooth scroll for anchor links
 */

// === Fade-up scroll animations ===
document.addEventListener('DOMContentLoaded', () => {
  const fadeElements = document.querySelectorAll('.fade-up');
  
  if (fadeElements.length) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.1, rootMargin: '0px 0px -50px 0px' }
    );

    fadeElements.forEach((el) => observer.observe(el));
  }

  // === Mobile menu toggle ===
  const menuToggle = document.querySelector('.achiever-header__menu-toggle');
  const mobileNav = document.querySelector('.achiever-header__nav');
  
  if (menuToggle && mobileNav) {
    menuToggle.addEventListener('click', () => {
      const isOpen = mobileNav.classList.toggle('is-open');
      menuToggle.classList.toggle('is-active', isOpen);
      menuToggle.setAttribute('aria-expanded', String(isOpen));
      document.body.classList.toggle('menu-open', isOpen);
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
      if (!menuToggle.contains(e.target) && !mobileNav.contains(e.target)) {
        mobileNav.classList.remove('is-open');
        menuToggle.classList.remove('is-active');
        menuToggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('menu-open');
      }
    });

    // Close menu on escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && mobileNav.classList.contains('is-open')) {
        mobileNav.classList.remove('is-open');
        menuToggle.classList.remove('is-active');
        menuToggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('menu-open');
      }
    });
  }

  // === Smooth scroll for anchor links ===
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener('click', (e) => {
      const targetId = anchor.getAttribute('href');
      if (targetId === '#') return;
      
      const target = document.querySelector(targetId);
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });
});

// === Home party slider arrows ===
document.addEventListener("DOMContentLoaded",()=>{document.querySelectorAll("[data-party-slider]").forEach(slider=>{const track=slider.querySelector(".achiever-party__track"),prev=slider.querySelector(".achiever-party__arrow--prev"),next=slider.querySelector(".achiever-party__arrow--next");if(!track||!prev||!next)return;const move=dir=>{const slide=track.querySelector(".achiever-party__slide"),gap=parseFloat(getComputedStyle(track).gap||"0"),amount=slide?slide.getBoundingClientRect().width+gap:track.clientWidth*.8;track.scrollBy({left:amount*dir,behavior:"smooth"})};prev.addEventListener("click",()=>move(-1));next.addEventListener("click",()=>move(1))})});
