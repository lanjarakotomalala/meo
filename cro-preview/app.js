/* Homepage express route. All cart data stays in this browser's local storage. */
(async () => {
  const store = window.MeoStore;
  const money = store.money;
  const esc = store.escape;
  const featured = [
    { id: '8399', short: 'Biologique Original', type: 'grain', kind: 'Café en grains', note: 'Équilibré · 500 g', tag: 'Le grand classique' },
    { id: '8325', short: 'Biologique Original', type: 'moulu', kind: 'Café moulu', note: 'Gourmand · 500 g', tag: 'À découvrir' },
    { id: '848138', short: 'Biologique Harmonie', type: 'capsules', kind: 'Capsules compostables', note: 'Harmonieux · x 20', tag: 'Format pratique' },
    { id: '8395', short: 'Gastronomique', type: 'grain', kind: 'Café en grains', note: 'Rond · 500 g', tag: '' },
    { id: '8595', short: 'Dégustation', type: 'moulu', kind: 'Café moulu', note: 'Doux · 250 g', tag: '' },
    { id: '848132', short: 'Biologique Décaféiné', type: 'capsules', kind: 'Capsules compostables', note: 'Sans caféine · x 20', tag: '' },
  ];
  const ids = ['product-grid', 'mode-once', 'mode-subscribe', 'cart-count', 'drawer-count', 'cart-items', 'cart-empty', 'subtotal', 'recurring-note', 'subscription-nudge', 'checkout-button', 'drawer', 'scrim', 'empty-results', 'search', 'search-wrap', 'search-toggle'];
  const els = Object.fromEntries(ids.map(id => [id, document.getElementById(id)]));
  const state = { mode: 'once', filter: 'all', query: '', nudgedAway: false };

  try { await store.ready; }
  catch { els['product-grid'].innerHTML = '<p>Le catalogue est momentanément indisponible. Actualisez la page pour réessayer.</p>'; return; }

  function curated() { return featured.map(meta => ({ ...store.getProduct(meta.id), ...meta })).filter(product => product.id && product.name); }
  function setMode(mode, scroll = false) {
    state.mode = mode;
    ['once', 'subscribe'].forEach(value => {
      const button = els[`mode-${value}`];
      button.classList.toggle('active', value === mode);
      button.setAttribute('aria-pressed', String(value === mode));
    });
    renderProducts();
    if (scroll) document.getElementById('selection').scrollIntoView({ behavior: 'smooth' });
  }
  function renderProducts() {
    const products = curated().filter(product => (state.filter === 'all' || state.filter === product.type || (state.filter === 'bio' && /biologique/i.test(product.name))) && (!state.query || `${product.name} ${product.kind}`.toLocaleLowerCase('fr').includes(state.query)));
    els['empty-results'].hidden = products.length > 0;
    els['product-grid'].innerHTML = products.map(product => {
      const path = store.cleanPath(product.path);
      const img = product.images[0]?.src || '/assets/logo.png';
      return `<article class="product-card"><a class="product-picture" href="${esc(path)}" aria-label="Voir ${esc(product.name)}">${product.tag ? `<span class="product-tag">${esc(product.tag)}</span>` : ''}<img src="${esc(img)}" alt="${esc(product.name)}" loading="lazy"><span class="product-fallback" aria-hidden="true">méo<small>cafés</small></span></a><div class="product-info"><span class="product-kind">${esc(product.kind)}</span><h3><a href="${esc(path)}">${esc(product.short)}</a></h3><div class="product-meta"><span>${esc(product.note)}</span><span>${state.mode === 'subscribe' ? 'Par livraison' : 'Achat unique'}</span></div><div class="product-bottom"><div class="price-group"><strong>${money(product.price)}</strong><small>${state.mode === 'subscribe' ? 'par livraison · simulation' : 'l’unité'}</small></div><button class="product-action" type="button" data-add="${esc(product.id)}" aria-label="${state.mode === 'subscribe' ? 'Simuler un abonnement' : 'Ajouter au panier'} : ${esc(product.name)}">${state.mode === 'subscribe' ? 'M’abonner' : 'Ajouter'} <small>↗</small></button></div></div></article>`;
    }).join('');
    els['product-grid'].querySelectorAll('.product-picture img').forEach(img => {
      img.addEventListener('error', () => img.parentElement.classList.add('broken'), { once: true });
      if (img.complete && img.naturalWidth === 0) img.parentElement.classList.add('broken');
    });
  }
  function renderCart() {
    const count = store.count();
    const total = store.total();
    const recurring = store.total('subscribe');
    els['cart-count'].textContent = count;
    els['drawer-count'].textContent = `(${count})`;
    els['cart-empty'].hidden = count > 0;
    els['checkout-button'].disabled = count === 0;
    els['subtotal'].textContent = money(total);
    els['recurring-note'].textContent = !count ? '' : recurring ? `Aujourd’hui : ${money(total)}. Puis ${money(recurring)} toutes les ${store.frequency} pour les cafés en abonnement. Livraison éventuelle non comprise.` : 'Une seule commande, sans renouvellement. Livraison éventuelle non comprise.';
    els['cart-items'].innerHTML = store.items.map((item, index) => {
      const product = store.getProduct(item.id);
      if (!product) return '';
      return `<article class="cart-item"><a class="cart-item-image" href="${esc(product.path)}"><img src="${esc(product.images[0]?.src || '/assets/logo.png')}" alt="" loading="lazy"></a><div><h3><a href="${esc(product.path)}">${esc(product.name)}</a></h3><small>${money(product.price)} / unité · ${item.mode === 'subscribe' ? 'Abonnement' : 'Achat unique'}${item.option ? ` · ${esc(item.option)}` : ''}</small><div class="cart-item-end"><div class="qty-stepper"><button type="button" data-qty="${index}" data-change="-1" aria-label="Diminuer la quantité de ${esc(product.name)}">−</button><span>${item.qty}</span><button type="button" data-qty="${index}" data-change="1" aria-label="Augmenter la quantité de ${esc(product.name)}">+</button></div><strong>${money(product.price * item.qty)}</strong></div><button class="remove-item" type="button" data-remove="${index}">Retirer</button></div></article>`;
    }).join('');
    const nudge = els['subscription-nudge'];
    if (store.nudgeCount() >= store.NUDGE_COUNT && !state.nudgedAway) {
      nudge.hidden = false;
      nudge.innerHTML = `<div class="nudge-kicker">Votre café à votre rythme</div><h3>Et si ces cafés revenaient régulièrement ?</h3><p>Vous avez ${store.nudgeCount()} cafés en achat unique. Comparez avec une livraison régulière. Les autres produits restent en achat unique.</p><button type="button" id="convert-cart">Voir en abonnement</button><button type="button" class="nudge-secondary" id="dismiss-nudge">Rester en achat unique</button>`;
    } else if (recurring) {
      nudge.hidden = false;
      nudge.innerHTML = `<div class="nudge-kicker">Abonnement simulé</div><h3>Une livraison à votre rythme</h3><p>Le montant récurrent est visible ci-dessous. Cette maquette ne crée aucun abonnement.</p><div class="frequency"><label for="frequency-select">Recevoir cette sélection</label><select id="frequency-select"><option>2 semaines</option><option>4 semaines</option><option>6 semaines</option><option>8 semaines</option></select></div>`;
      nudge.querySelector('#frequency-select').value = store.frequency;
    } else nudge.hidden = true;
  }
  function openDrawer() { els.drawer.classList.add('open'); els.drawer.setAttribute('aria-hidden', 'false'); els.scrim.hidden = false; document.body.style.overflow = 'hidden'; document.getElementById('close-cart').focus(); }
  function closeDrawer() { els.drawer.classList.remove('open'); els.drawer.setAttribute('aria-hidden', 'true'); els.scrim.hidden = true; document.body.style.overflow = ''; }
  function setFilter(filter) {
    state.filter = filter;
    document.querySelectorAll('.filter').forEach(button => { const active = button.dataset.filter === filter; button.classList.toggle('active', active); button.setAttribute('aria-pressed', String(active)); });
    renderProducts();
  }

  document.querySelectorAll('[data-mode]').forEach(button => button.addEventListener('click', () => setMode(button.dataset.mode, true)));
  els['mode-once'].addEventListener('click', () => setMode('once'));
  els['mode-subscribe'].addEventListener('click', () => setMode('subscribe'));
  document.querySelectorAll('.filter').forEach(button => button.addEventListener('click', () => setFilter(button.dataset.filter)));
  document.getElementById('menu-toggle').addEventListener('click', () => {
    const nav = document.getElementById('mobile-nav');
    nav.hidden = !nav.hidden;
    document.getElementById('menu-toggle').setAttribute('aria-expanded', String(!nav.hidden));
  });
  els['product-grid'].addEventListener('click', event => {
    const button = event.target.closest('[data-add]');
    if (!button) return;
    if (store.add(button.dataset.add, 1, state.mode)) { state.nudgedAway = false; renderCart(); openDrawer(); }
  });
  els['cart-items'].addEventListener('click', event => {
    const qty = event.target.closest('[data-qty]');
    const remove = event.target.closest('[data-remove]');
    if (qty) { const index = Number(qty.dataset.qty); store.setQty(index, store.items[index].qty + Number(qty.dataset.change)); renderCart(); }
    if (remove) { store.remove(remove.dataset.remove); renderCart(); }
  });
  els['subscription-nudge'].addEventListener('click', event => {
    if (event.target.closest('#convert-cart')) { store.convertCoffee(); state.nudgedAway = false; renderCart(); }
    if (event.target.closest('#dismiss-nudge')) { state.nudgedAway = true; renderCart(); }
  });
  els['subscription-nudge'].addEventListener('change', event => { if (event.target.id === 'frequency-select') { store.setFrequency(event.target.value); renderCart(); } });
  document.getElementById('cart-trigger').addEventListener('click', openDrawer);
  document.getElementById('close-cart').addEventListener('click', closeDrawer);
  document.getElementById('empty-shop').addEventListener('click', () => { closeDrawer(); document.getElementById('selection').scrollIntoView({ behavior: 'smooth' }); });
  els['checkout-button'].addEventListener('click', () => { if (!els['checkout-button'].disabled) location.href = '/commander/'; });
  els.scrim.addEventListener('click', closeDrawer);
  document.addEventListener('keydown', event => { if (event.key === 'Escape') closeDrawer(); });
  document.getElementById('discover-sub').addEventListener('click', () => { location.href = '/abonnement/'; });
  els['search-toggle'].addEventListener('click', () => { els['search-wrap'].hidden = !els['search-wrap'].hidden; els['search-toggle'].setAttribute('aria-expanded', String(!els['search-wrap'].hidden)); if (!els['search-wrap'].hidden) els.search.focus(); });
  els.search.addEventListener('input', () => { state.query = els.search.value.trim().toLocaleLowerCase('fr'); renderProducts(); });
  document.addEventListener('meo:cart-change', renderCart);

  renderProducts();
  renderCart();
})();
