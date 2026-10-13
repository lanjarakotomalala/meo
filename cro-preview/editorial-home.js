/* Carousel and navigation for the standalone editorial homepage. */
(() => {
  const hero = document.querySelector('.editorial-hero');
  const slides = [...document.querySelectorAll('.editorial-slide')];
  const dots = [...document.querySelectorAll('.editorial-pagination button')];
  const filmDialog = document.getElementById('hero-film-dialog');
  const filmPlayer = filmDialog?.querySelector('.hero-film-player');
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

  document.querySelectorAll('.editorial-product-photo, .home-remote-photo').forEach(img => {
    const fallback = img.parentElement.querySelector('.editorial-product-fallback, .monthly-fallback');
    const hideBrokenImage = () => { img.hidden = true; };
    const hideFallback = () => { if (fallback && img.naturalWidth > 0) fallback.hidden = true; };
    img.addEventListener('error', hideBrokenImage, { once: true });
    img.addEventListener('load', hideFallback, { once: true });
    if (img.complete) {
      if (img.naturalWidth === 0) hideBrokenImage();
      else hideFallback();
    }
  });

  let currentSlide = 0;
  let carouselTimer;
  let heroVisible = true;
  let touchStart = null;
  let filmReturnFocus;

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
    return !motion.matches && !document.hidden && heroVisible && !hero.matches(':hover') && !hero.contains(document.activeElement) && !header.classList.contains('menu-open') && mobileNav.hidden && filmDialog?.hidden !== false;
  }

  function stopAutoplay() {
    window.clearTimeout(carouselTimer);
    carouselTimer = undefined;
  }

  function startAutoplay() {
    stopAutoplay();
    if (!canAutoplay()) return;
    carouselTimer = window.setTimeout(() => {
      if (canAutoplay()) { showSlide(currentSlide + 1); startAutoplay(); }
      else stopAutoplay();
    }, currentSlide === 0 ? 9000 : 6500);
  }

  dots.forEach(dot => dot.addEventListener('click', () => { showSlide(Number(dot.dataset.slide)); startAutoplay(); }));
  hero.querySelectorAll('[data-slide-link]').forEach(button => button.addEventListener('click', () => {
    showSlide(Number(button.dataset.slideLink));
    startAutoplay();
  }));
  hero.querySelectorAll('[data-direction]').forEach(button => button.addEventListener('click', () => {
    showSlide(currentSlide + Number(button.dataset.direction));
    startAutoplay();
  }));

  function closeFilm() {
    if (!filmDialog || filmDialog.hidden) return;
    filmDialog.hidden = true;
    filmPlayer?.removeAttribute('src');
    document.body.classList.remove('film-open');
    filmReturnFocus?.focus();
    startAutoplay();
  }
  document.querySelectorAll('[data-open-film]').forEach(button => button.addEventListener('click', () => {
    if (!filmDialog || !filmPlayer) return;
    filmReturnFocus = button;
    filmDialog.hidden = false;
    filmPlayer.src = filmPlayer.dataset.src;
    document.body.classList.add('film-open');
    stopAutoplay();
    filmDialog.querySelector('.hero-film-close')?.focus();
  }));
  filmDialog?.querySelectorAll('[data-close-film]').forEach(button => button.addEventListener('click', closeFilm));
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && filmDialog && !filmDialog.hidden) { event.preventDefault(); closeFilm(); }
  });
  filmDialog?.addEventListener('keydown', event => {
    if (event.key !== 'Tab') return;
    const focusable = [...filmDialog.querySelectorAll('button, a, iframe')];
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
  });

  document.querySelectorAll('[data-rail][data-rail-dir]').forEach(button => {
    button.addEventListener('click', () => {
      const rail = document.getElementById(button.dataset.rail);
      if (!rail) return;
      const card = rail.firstElementChild;
      const distance = (card?.getBoundingClientRect().width || rail.clientWidth * .8) + 8;
      rail.scrollBy({ left: distance * Number(button.dataset.railDir), behavior: motion.matches ? 'auto' : 'smooth' });
    });
  });

  document.getElementById('journal-signup')?.addEventListener('submit', event => {
    event.preventDefault();
    document.getElementById('journal-form-status').textContent = 'Cette prévisualisation ne transmet pas votre adresse. Retrouvez les actualités dans le journal Méo.';
  });

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
    if (window.matchMedia('(max-width: 900px)').matches) return;
    header.classList.add('menu-open');
    triggers.forEach(trigger => trigger.setAttribute('aria-expanded', String(trigger.dataset.menu === name)));
    panels.forEach(panel => { panel.hidden = panel.dataset.panel !== name; });
    stopAutoplay();
  }

  triggers.forEach(trigger => {
    trigger.addEventListener('mouseenter', () => openMegaMenu(trigger.dataset.menu));
    trigger.addEventListener('focus', () => openMegaMenu(trigger.dataset.menu));
    trigger.addEventListener('click', () => openMegaMenu(trigger.dataset.menu));
  });
  header.querySelectorAll('.desktop-nav a').forEach(link => link.addEventListener('mouseenter', closeMegaMenu));
  header.addEventListener('mouseleave', closeMegaMenu);
  header.addEventListener('focusout', event => {
    if (!header.contains(event.relatedTarget)) closeMegaMenu();
  });
  document.addEventListener('pointerdown', event => {
    if (!header.contains(event.target)) {
      closeMegaMenu();
      if (!mobileNav.hidden) menuToggle.click();
    }
  });
  document.addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;
    if (!mobileNav.hidden) { menuToggle.click(); menuToggle.focus(); }
    if (header.classList.contains('menu-open')) { closeMegaMenu(); triggers[0]?.focus(); }
  });
  panels.forEach(panel => panel.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMegaMenu)));

  function showMobilePanel(name, focusHeading = true) {
    const panels = [...mobileNav.querySelectorAll('[data-menu-panel]')];
    const next = panels.find(panel => panel.dataset.menuPanel === name);
    if (!next) return;
    panels.forEach(panel => { panel.hidden = panel !== next; });
    mobileNav.scrollTop = 0;
    if (focusHeading) next.querySelector('h2')?.focus();
  }

  function syncMobileMenu() {
    const open = !mobileNav.hidden;
    document.body.classList.toggle('mobile-menu-open', open);
    menuToggle.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
    if (open) { closeMegaMenu(); showMobilePanel('root', false); mobileClose.focus(); }
    else { showMobilePanel('root', false); startAutoplay(); }
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
  mobileNav.querySelectorAll('[data-menu-go]').forEach(button => {
    button.addEventListener('click', () => showMobilePanel(button.dataset.menuGo));
  });
  mobileNav.querySelectorAll('a').forEach(link => link.addEventListener('click', () => {
    if (mobileNav.hidden) return;
    menuToggle.click();
    const destination = link.hash ? document.getElementById(link.hash.slice(1)) : null;
    window.requestAnimationFrame(() => {
      if (destination) {
        if (!destination.hasAttribute('tabindex')) destination.setAttribute('tabindex', '-1');
        destination.focus({ preventScroll: true });
      } else menuToggle.focus({ preventScroll: true });
    });
  }));
  mobileNav.addEventListener('keydown', event => {
    if (event.key !== 'Tab') return;
    const focusable = [...mobileNav.querySelectorAll('button, a')].filter(element => element.getClientRects().length && !element.closest('[hidden]'));
    if (!focusable.length) return;
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
  });

  function syncHeader() {
    const offset = announcement.offsetHeight + demoBanner.offsetHeight;
    document.body.style.setProperty('--editorial-top-offset', `${offset}px`);
    header.classList.toggle('is-scrolled', window.scrollY >= offset);
  }
  window.addEventListener('scroll', syncHeader, { passive: true });
  window.addEventListener('resize', () => {
    if (window.matchMedia('(max-width: 900px)').matches) closeMegaMenu();
    syncHeader();
  });

  const announcementSlides = [...announcement.querySelectorAll('.announcement-slide')];
  const announcementDots = [...announcement.querySelectorAll('[data-announcement]')];
  const announcementTrack = announcement.querySelector('.announcement-track');
  let currentAnnouncement = 0;
  let announcementTimer;

  function showAnnouncement(index) {
    currentAnnouncement = (index + announcementSlides.length) % announcementSlides.length;
    announcementTrack.style.transform = `translateX(-${currentAnnouncement * 100}%)`;
    announcementSlides.forEach((slide, number) => {
      const active = number === currentAnnouncement;
      slide.setAttribute('aria-hidden', String(!active));
      slide.querySelector('a').tabIndex = active ? 0 : -1;
    });
    announcementDots.forEach((dot, number) => dot.setAttribute('aria-current', String(number === currentAnnouncement)));
  }

  function stopAnnouncement() {
    window.clearTimeout(announcementTimer);
    announcementTimer = undefined;
  }

  function canAdvanceAnnouncement() {
    return !motion.matches && !document.hidden && !announcement.hidden && !announcement.matches(':hover') && !announcement.contains(document.activeElement);
  }

  function startAnnouncement() {
    stopAnnouncement();
    if (!canAdvanceAnnouncement()) return;
    announcementTimer = window.setTimeout(() => {
      if (!canAdvanceAnnouncement()) return;
      showAnnouncement(currentAnnouncement + 1);
      startAnnouncement();
    }, 5500);
  }

  announcementDots.forEach(dot => dot.addEventListener('click', () => {
    showAnnouncement(Number(dot.dataset.announcement));
    startAnnouncement();
  }));
  announcement.addEventListener('mouseenter', stopAnnouncement);
  announcement.addEventListener('mouseleave', startAnnouncement);
  announcement.addEventListener('focusin', stopAnnouncement);
  announcement.addEventListener('focusout', () => window.requestAnimationFrame(startAnnouncement));
  announcement.querySelector('.announcement-close').addEventListener('click', () => {
    stopAnnouncement();
    announcement.hidden = true;
    syncHeader();
    menuToggle.focus();
  });
  document.addEventListener('visibilitychange', startAnnouncement);
  motion.addEventListener?.('change', startAnnouncement);
  showAnnouncement(0);
  startAnnouncement();

  showSlide(0);
  syncHeader();
  startAutoplay();
})();
