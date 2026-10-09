(function () {
  'use strict';
  var hero = document.querySelector('.meo-home-hero');
  if (!hero) return;
  var slides = Array.prototype.slice.call(hero.querySelectorAll('.meo-home-hero__slide'));
  var dots = Array.prototype.slice.call(hero.querySelectorAll('[data-slide]'));
  var current = 0;
  var timer;
  var hovered = false;
  var inView = true;
  var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  function syncHeader() { document.body.classList.toggle('meo-scrolled', window.scrollY > 48); }
  window.addEventListener('scroll', syncHeader, { passive: true });
  syncHeader();
  window.requestAnimationFrame(function () { document.body.classList.add('meo-ready'); });

  function show(index) {
    current = (index + slides.length) % slides.length;
    slides.forEach(function (slide, position) {
      var active = position === current;
      slide.classList.toggle('is-active', active);
      slide.setAttribute('aria-hidden', active ? 'false' : 'true');
      var link = slide.querySelector('a');
      if (link) link.tabIndex = active ? 0 : -1;
    });
    dots.forEach(function (dot, position) {
      dot.classList.toggle('is-active', position === current);
      dot.setAttribute('aria-current', position === current ? 'true' : 'false');
    });
  }

  function stop() { window.clearInterval(timer); }
  function start() {
    stop();
    if (!reducedMotion.matches && !hovered && inView && !document.hidden && !hero.contains(document.activeElement)) {
      timer = window.setInterval(function () { show(current + 1); }, 6500);
    }
  }
  dots.forEach(function (dot) {
    dot.addEventListener('click', function () { show(Number(dot.getAttribute('data-slide'))); start(); });
  });
  Array.prototype.forEach.call(hero.querySelectorAll('[data-direction]'), function (button) {
    button.addEventListener('click', function () { show(current + Number(button.getAttribute('data-direction'))); start(); });
  });
  hero.addEventListener('mouseenter', function () { hovered = true; stop(); });
  hero.addEventListener('mouseleave', function () { hovered = false; start(); });
  hero.addEventListener('focusin', stop);
  hero.addEventListener('focusout', function (event) { if (!hero.contains(event.relatedTarget)) start(); });
  hero.addEventListener('keydown', function (event) {
    if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') {
      show(current + (event.key === 'ArrowRight' ? 1 : -1));
      event.preventDefault();
    }
  });
  var startX = 0;
  var startY = 0;
  hero.addEventListener('touchstart', function (event) {
    startX = event.changedTouches[0].clientX;
    startY = event.changedTouches[0].clientY;
    stop();
  }, { passive: true });
  hero.addEventListener('touchend', function (event) {
    var deltaX = event.changedTouches[0].clientX - startX;
    var deltaY = event.changedTouches[0].clientY - startY;
    if (Math.abs(deltaX) > 45 && Math.abs(deltaX) > Math.abs(deltaY) * 1.3) {
      show(current + (deltaX < 0 ? 1 : -1));
    }
    start();
  }, { passive: true });
  document.addEventListener('visibilitychange', function () { if (document.hidden) stop(); else start(); });
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
      inView = entries[0].isIntersecting;
      if (inView) start(); else stop();
    }, { threshold: 0.2 }).observe(hero);
  }
  start();

  var nav = document.querySelector('.col-full-nav');
  var header = document.querySelector('.site-header');
  if (nav && header) {
    var closeTimer;
    var open = function () { if (window.innerWidth > 992) { window.clearTimeout(closeTimer); document.body.classList.add('meo-menu-open'); } };
    var close = function () { closeTimer = window.setTimeout(function () { document.body.classList.remove('meo-menu-open'); }, 160); };
    nav.addEventListener('mouseenter', open);
    nav.addEventListener('mouseleave', close);
    nav.addEventListener('focusin', open);
    nav.addEventListener('focusout', function (event) { if (!nav.contains(event.relatedTarget)) close(); });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape') document.body.classList.remove('meo-menu-open'); });
  }
}());
