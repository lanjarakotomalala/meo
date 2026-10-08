const PAGE_RE = /\/page\/(\d+)\/?/;


function getPageFromPath() {
  const m = window.location.pathname.match(PAGE_RE);
  return m ? parseInt(m[1], 10) : 1;
}


// Add _page to URL before WP Grid Builder reads it
(function () {
  const page = getPageFromPath();

  if (page > 1) {
    const params = new URLSearchParams(window.location.search);

    if (params.get('_page') !== String(page)) {
      params.set('_page', page);
      const qs = params.toString();
      window.history.replaceState({}, '', window.location.pathname + (qs ? '?' + qs : ''));
    }
  }
})();


// Add -page to title page attribute
let originalTitle = null;
function updateTitlePagination(pageNum) {
  if (originalTitle === null) {
    originalTitle = document.title.replace(/\s+Page\s+\d+\s*$/, '');
  }
  document.title = pageNum > 1 ? originalTitle + ' - Page ' + pageNum : originalTitle;
}


// Hide description
function updateDescriptionVisibility(pageNum) {
  const description = document.querySelector('.archive-details-meta');
  if (!description) return;
  description.style.display = pageNum > 1 ? 'none' : '';
}


// Add rel next and rel prev
function updateRelatedPages(pageNum) {
  const head = document.head;

  head.querySelectorAll('link[rel="prev"], link[rel="next"]').forEach((el) => el.remove());

  const basePath = window.location.pathname.replace(PAGE_RE, '/');
  const origin = window.location.origin;

  if (pageNum > 1) {
    const prevLink = document.createElement('link');
    prevLink.rel = 'prev';
    prevLink.href = pageNum === 2
      ? origin + basePath
      : origin + basePath.replace(/\/$/, '') + '/page/' + (pageNum - 1) + '/';
    head.appendChild(prevLink);
  }

  const allPageLinks = document.querySelectorAll('.wpgb-pagination a[data-page]');
  const totalPages = allPageLinks.length
    ? Math.max(...Array.from(allPageLinks).map((a) => parseInt(a.dataset.page, 10)))
    : pageNum;

  if (pageNum < totalPages) {
    const nextLink = document.createElement('link');
    nextLink.rel = 'next';
    nextLink.href = origin + basePath.replace(/\/$/, '') + '/page/' + (pageNum + 1) + '/';
    head.appendChild(nextLink);
  }
}


function updateBreadcrumbPagination(pageNum) {
  const bc = document.querySelector('.archive .woocommerce-breadcrumb');
  if (!bc) return;

  bc.querySelectorAll('.breadcrumb-page-sep, .breadcrumb-page-crumb').forEach((el) => el.remove());

  Array.from(bc.childNodes).forEach((node) => {
    if (node.nodeType === 3 && /^\s*Page\s+\d+\s*$/.test(node.textContent)) {
      const prev = node.previousSibling;
      if (prev?.classList?.contains('breadcrumb-separator')) prev.remove();
      node.remove();
    }
  });

  if (pageNum > 1) {
    const sep = document.createElement('span');
    sep.className = 'breadcrumb-separator breadcrumb-page-sep';
    sep.innerHTML = '&nbsp;&#47;&nbsp;';

    const span = document.createElement('span');
    span.className = 'breadcrumb-page-crumb';
    span.textContent = 'Page ' + pageNum;

    bc.append(sep, span);
  }
}


function syncPaginationFromUrl() {
  const currentPage = getPageFromPath();
  updateBreadcrumbPagination(currentPage);
  updateTitlePagination(currentPage);
  updateDescriptionVisibility(currentPage);
  updateRelatedPages(currentPage);

  if (currentPage <= 1) return;

  const pagination = document.querySelector('.wpgb-pagination-facet .wpgb-pagination');
  if (!pagination) return;

  const selector = '.wpgb-page:not(.wpgb-page-prev):not(.wpgb-page-next) a';
  const links = pagination.querySelectorAll(selector);
  const match = Array.from(links).find((a) => parseInt(a.dataset.page, 10) === currentPage);

  if (match) {
    links.forEach((a) => a === match ? a.setAttribute('aria-current', 'page') : a.removeAttribute('aria-current'));
  }
  else if (!pagination.querySelector(`[data-page="${currentPage}"]`)) {
    links.forEach((a) => a.removeAttribute('aria-current'));

    const dots = Array.from(pagination.querySelectorAll('.wpgb-dots-page'));
    const dotsLi = dots.find((s) => {
      const next = s.closest('li')?.nextElementSibling;
      const link = next?.querySelector('a[data-page]');
      return link && parseInt(link.dataset.page, 10) > currentPage;
    })?.closest('li');

    if (dotsLi) {
      const li = document.createElement('li');
      li.className = 'wpgb-page';

      const a = document.createElement('a');
      a.href = window.location.href;
      a.dataset.page = currentPage;
      a.setAttribute('aria-current', 'page');
      a.textContent = currentPage;

      li.append(a);
      dotsLi.after(li);
    }
  }
}

// Remove apply filters button behaviour on desktop devices
window.WP_Grid_Builder && WP_Grid_Builder.on('init', function (wpgb) {

  syncPaginationFromUrl();

  const facetContainer = document.querySelector('.woocommerce-product-loop__more');

  if (facetContainer) {
    const observer = new MutationObserver(syncPaginationFromUrl);
    observer.observe(facetContainer, { childList: true, subtree: true });
    setTimeout(() => observer.disconnect(), 3000);
  }

  wpgb.facets.on('loaded', syncPaginationFromUrl);

  wpgb.facets.on('change', (slug, value) => {
    const applyFiltersBtn = document.querySelector('.wpgb-button.wpgb-apply[name="appliquer_les_filtres"]');

    // Custom pagination handling - use value from event (URL not updated yet)
    if (slug === 'page') {
      const pageNum = parseInt(value && value[0], 10) || 1;
      updateBreadcrumbPagination(pageNum);
      updateTitlePagination(pageNum);
      updateDescriptionVisibility(pageNum);
      updateRelatedPages(pageNum);
      setTimeout(() => {
        const urlParams = new URLSearchParams(window.location.search);
        let newUrl = window.location.pathname.replace(/\/page\/\d+\/?/, '/');

        if (pageNum > 1) {
          newUrl = newUrl.replace(/\/$/, '') + '/page/' + pageNum + '/';
        }

        urlParams.delete('_page');
        const qs = urlParams.toString();
        window.history.replaceState({}, '', newUrl + (qs ? '?' + qs : ''));
      }, 0);

      return;
    }

    // Filters auto apply on desktop
    if (window.innerWidth < 993) return;

    if (applyFiltersBtn) {
      const newEvent = new Event('click');
      applyFiltersBtn.dispatchEvent(newEvent);
    }
  });
});
