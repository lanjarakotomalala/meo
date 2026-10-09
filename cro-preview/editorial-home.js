/* Carousel and navigation for the standalone editorial homepage. */
(() => {
  const hero = document.querySelector('.editorial-hero');
  const slides = [...document.querySelectorAll('.editorial-slide')];
  const dots = [...document.querySelectorAll('.editorial-pagination button')];
  const header = document.querySelector('.site-header');
  const triggers = [...document.querySelectorAll('.nav-trigger')];
  const panels = [...document.querySelectorAll('.mega-panel')];
  const menuToggle = document.getElementById('menu-toggle');
  const mobileNav = document.getElementById('mobile-nav');
  const mobileClose = document.querySelector('.mobile-nav-close');
  const announcement = document.querySelector('.announcement');
  const demoBanner = document.querySelector('.demo-banner');
  const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
  if (!hero || !header || slides.length === 0) return;

  document.querySelectorAll('.editorial-product-photo').forEach(img => {
    const hideBrokenImage = () => { img.hidden = true; };
    img.addEventListener('error', hideBrokenImage, { once: true });
    if (img.complete && img.naturalWidth === 0) hideBrokenImage();
  });

  let currentSlide = 0;
  let carouselTimer;
  let heroVisible = true;
  let touchStart = null;

  function showSlide(index) {
    currentSlide = (index + slides.length) % slides.length;
    slides.forEach((slide, number) => {
      const active = number === currentSlide;
      slide.classList.toggle('is-active', active);
      slide.setAttribute('aria-hidden', String(!active));
      slide.querySelectorAll('a, button').forEach(element => { element.tabIndex = active ? 0 : -1; });
    });
    dots.forEach((dot, number) => {
      const active = number === currentSlide;
      dot.classList.toggle('is-active', active);
      dot.setAttribute('aria-current', String(active));
    });
  }

  function canAutoplay() {
    return !motion.matches && !document.hidden && heroVisible && !hero.matches(':hover') && !hero.contains(document.activeElement) && !header.classList.contains('menu-open') && mobileNav.hidden;
  }

  function stopAutoplay() {
    window.clearInterval(carouselTimer);
    carouselTimer = undefined;
  }

  function startAutoplay() {
    stopAutoplay();
    if (!canAutoplay()) return;
    carouselTimer = window.setInterval(() => {
      if (canAutoplay()) showSlide(currentSlide + 1);
      else stopAutoplay();
    }, 6500);
  }

  dots.forEach(dot => dot.addEventListener('click', () => { showSlide(Number(dot.dataset.slide)); startAutoplay(); }));
  hero.querySelectorAll('[data-direction]').forEach(button => button.addEventListener('click', () => {
    showSlide(currentSlide + Number(button.dataset.direction));
    startAutoplay();
  }));

  hero.addEventListener('keydown', event => {
    if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
    event.preventDefault();
    showSlide(currentSlide + (event.key === 'ArrowRight' ? 1 : -1));
    startAutoplay();
  });
  hero.addEventListener('mouseenter', stopAutoplay);
  hero.addEventListener('mouseleave', startAutoplay);
  hero.addEventListener('focusin', stopAutoplay);
  hero.addEventListener('focusout', () => window.setTimeout(startAutoplay, 0));
  hero.addEventListener('touchstart', event => {
    if (event.touches.length === 1) touchStart = { x: event.touches[0].clientX, y: event.touches[0].clientY };
  }, { passive: true });
  hero.addEventListener('touchend', event => {
    if (!touchStart || event.changedTouches.length !== 1) return;
    const dx = event.changedTouches[0].clientX - touchStart.x;
    const dy = event.changedTouches[0].clientY - touchStart.y;
    touchStart = null;
    if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy) * 1.4) {
      showSlide(currentSlide + (dx < 0 ? 1 : -1));
      startAutoplay();
    }
  }, { passive: true });

  if ('IntersectionObserver' in window) {
    new IntersectionObserver(([entry]) => {
      heroVisible = entry.isIntersecting;
      if (heroVisible) startAutoplay();
      else stopAutoplay();
    }, { threshold: .2 }).observe(hero);
  }
  document.addEventListener('visibilitychange', startAutoplay);
  motion.addEventListener?.('change', startAutoplay);

  function closeMegaMenu() {
    header.classList.remove('menu-open');
    triggers.forEach(trigger => trigger.setAttribute('aria-expanded', 'false'));
    panels.forEach(panel => { panel.hidden = true; });
    startAutoplay();
  }

  function openMegaMenu(name) {
    if (window.matchMedia('(max-width: 760px)').matches) return;
    header.classList.add('menu-open');
    triggers.forEach(trigger => trigger.setAttribute('aria-expanded', String(trigger.dataset.menu === name)));
    panels.forEach(panel => { panel.hidden = panel.dataset.panel !== name; });
    stopAutoplay();
  }

  triggers.forEach(trigger => {
    trigger.addEventListener('mouseenter', () => openMegaMenu(trigger.dataset.menu));
    trigger.addEventListener('focus', () => openMegaMenu(trigger.dataset.menu));
    trigger.addEventListener('click', () => {
      if (trigger.getAttribute('aria-expanded') === 'true') closeMegaMenu();
      else openMegaMenu(trigger.dataset.menu);
    });
  });
  header.querySelectorAll('.desktop-nav a').forEach(link => link.addEventListener('mouseenter', closeMegaMenu));
  header.addEventListener('mouseleave', closeMegaMenu);
  header.addEventListener('focusout', event => {
    if (!header.contains(event.relatedTarget)) closeMegaMenu();
  });
  document.addEventListener('pointerdown', event => {
    if (!header.contains(event.target)) closeMegaMenu();
  });
  document.addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;
    if (!mobileNav.hidden) { menuToggle.click(); menuToggle.focus(); }
    if (header.classList.contains('menu-open')) { closeMegaMenu(); triggers[0]?.focus(); }
  });
  panels.forEach(panel => panel.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMegaMenu)));

  function syncMobileMenu() {
    const open = !mobileNav.hidden;
    document.body.classList.toggle('mobile-menu-open', open);
    menuToggle.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
    if (open) { closeMegaMenu(); mobileClose.focus(); }
    else startAutoplay();
  }
  menuToggle.addEventListener('click', event => {
    // The older store script also handles this button. Capture the homepage
    // click so the drawer state is updated exactly once.
    event.stopImmediatePropagation();
    mobileNav.hidden = !mobileNav.hidden;
    menuToggle.setAttribute('aria-expanded', String(!mobileNav.hidden));
    syncMobileMenu();
  }, true);
  mobileClose.addEventListener('click', () => { menuToggle.click(); menuToggle.focus(); });
  mobileNav.querySelectorAll('a').forEach(link => link.addEventListener('click', () => {
    if (link.hash && !mobileNav.hidden) menuToggle.click();
  }));

  function syncHeader() {
    const offset = announcement.offsetHeight + demoBanner.offsetHeight;
    document.body.style.setProperty('--editorial-top-offset', `${offset}px`);
    header.classList.toggle('is-scrolled', window.scrollY >= offset);
  }
  window.addEventListener('scroll', syncHeader, { passive: true });
  window.addEventListener('resize', () => {
    if (window.matchMedia('(max-width: 760px)').matches) closeMegaMenu();
    syncHeader();
  });

  showSlide(0);
  syncHeader();
  startAutoplay();
})();
