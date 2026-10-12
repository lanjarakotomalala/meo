/* Homepage express route. All cart data stays in this browser's local storage. */
(async () => {
  const store = window.MeoStore;
  const money = store.money;
  const esc = store.escape;
  // Editorial defaults for the mock-up. In WordPress these become two editable product badges.
  const homepageBadges = {
    '8399': ['BIO', '100 % ARABICA'],
    '8325': ['BIO', '100 % ARABICA'],
    '848138': ['BIO', 'COMPOSTABLES'],
    '539205': ['BIO', 'COMPATIBLES SENSEO'],
  };
  const bestSellers = {
    grain: ['8399', '8395', '18229993', '18112033'],
    moulu: ['8325', '8595', '4267758', '18112874'],
    capsules: ['848138', '848142', '848143', '848132'],
    dosettes: ['8535', '8533', '539205', '8575'],
  };
  const categoryLabels = { best: 'Meilleures ventes', bio: 'Le bio', decaf: 'Le décaféiné', specialty: 'Cafés de spécialité' };
  const formatLabels = { grain: 'Café en grains', moulu: 'Café moulu', capsules: 'Capsules', dosettes: 'Dosettes' };
  const formatIcons = {
    grain: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.5 5.5c3.5 3.5 1.4 10.4-3.9 13.7S4.2 17.9 4.5 12.9 14.6 1.6 18.5 5.5Z"/><path d="M16 5.5c-1 4.5-5.6 6-7.7 12"/></svg>',
    moulu: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 16h16M7 16c0-5.5 2-8 5-8s5 2.5 5 8M9 5h.01M13 4h.01M16 6h.01" stroke-linecap="round"/></svg>',
    capsules: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 7c0-2 2-3 5-3s5 1 5 3l1 10H6L7 7ZM5 17h14v3H5zM7 8h10"/></svg>',
    dosettes: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="5" stroke-dasharray="1.5 2"/></svg>',
  };
  const ids = ['product-grid', 'selection-categories', 'selection-count', 'cart-count', 'drawer-count', 'cart-items', 'cart-empty', 'subtotal', 'recurring-note', 'subscription-nudge', 'checkout-button', 'drawer', 'scrim', 'empty-results', 'search', 'search-wrap', 'search-toggle'];
  const els = Object.fromEntries(ids.map(id => [id, document.getElementById(id)]));
  const state = { format: 'grain', category: 'best', query: '', nudgedAway: false, productModes: {} };

  try { await store.ready; }
  catch { els['product-grid'].innerHTML = '<p>Le catalogue est momentanément indisponible. Actualisez la page pour réessayer.</p>'; return; }

  function formatOf(product) {
    const name = product.name.toLocaleLowerCase('fr');
    const ids = product.categoryIds || [];
    if (/dosette/.test(name) || ids.includes(218)) return 'dosettes';
    if (/capsule/.test(name) || ids.includes(216)) return 'capsules';
    if (/moulu/.test(name) || ids.includes(202)) return 'moulu';
    if (/grain/.test(name) || ids.includes(192)) return 'grain';
    return null;
  }
  function allForFormat(format) {
    return store.products.filter(product => product.type === 'simple' && product.inStock && product.purchasable && store.canSubscribe(product) && !/pack/i.test(product.name) && formatOf(product) === format);
  }
  function productsFor(format, category) {
    const all = allForFormat(format);
    if (category === 'best') {
      const chosen = bestSellers[format].map(id => all.find(product => product.id === id)).filter(Boolean);
      return [...chosen, ...all.filter(product => !chosen.includes(product))].slice(0, 8);
    }
    return all.filter(product => {
      const ids = product.categoryIds || [];
      if (category === 'bio') return ids.includes(222) || /biologique/i.test(product.name);
      if (category === 'decaf') return ids.includes(1250) || /décaféin/i.test(product.name);
      if (category === 'specialty') return ids.includes(1248) || ids.includes(1200) || ids.includes(1204);
      return false;
    }).slice(0, 12);
  }
  function renderCategories() {
    els['selection-categories'].innerHTML = Object.entries(categoryLabels).filter(([key]) => productsFor(state.format, key).length).map(([key, label]) => `<button class="selection-category ${state.category === key ? 'active' : ''}" type="button" data-category="${key}" aria-pressed="${state.category === key}">${label}</button>`).join('');
  }
  function summaryOf(product) {
    const head = (product.short || product.description || '').split(/NOTES GUSTATIVES/i)[0].replace(/\s+/g, ' ').trim();
    if (!head) return 'Découvrez ce café Méo et son profil aromatique.';
    return head.length > 130 ? head.slice(0, 127).replace(/\s+\S*$/, '') + '…' : head;
  }
  function notesOf(product) {
    const source = product.short || '';
    const raw = (source.split(/NOTES GUSTATIVES/i)[1] || '').split(/Produit certifié|Torréfaction Méo|Origine\s*:/i)[0].replace(/\s+/g, ' ').trim();
    const attribute = product.attributes.find(item => /profil aromatique/i.test(item.name));
    const value = raw || attribute?.options.join(' · ') || 'À découvrir sur la fiche produit';
    return value.length > 70 ? value.slice(0, 67).replace(/\s+\S*$/, '') + '…' : value;
  }
  function badgesFor(product) {
    if (Array.isArray(product.homepageBadges) && product.homepageBadges.length >= 2) return product.homepageBadges.slice(0, 2);
    if (homepageBadges[product.id]) return homepageBadges[product.id];
    const ids = product.categoryIds || [];
    const first = ids.includes(222) ? 'BIO' : ids.includes(1250) ? 'DÉCAFÉINÉ' : ids.includes(1248) ? 'CAFÉ DE SPÉCIALITÉ' : 'SÉLECTION MÉO';
    const second = ids.includes(1250) && first !== 'DÉCAFÉINÉ' ? 'DÉCAFÉINÉ' : ids.includes(1248) && first !== 'CAFÉ DE SPÉCIALITÉ' ? 'PURE ORIGINE' : 'TORRÉFIÉ EN FRANCE';
    return [first, second];
  }
  function renderProducts() {
    const products = productsFor(state.format, state.category).filter(product => !state.query || `${product.name} ${product.short || ''}`.toLocaleLowerCase('fr').includes(state.query));
    els['empty-results'].hidden = products.length > 0;
    els['selection-count'].textContent = `${products.length} café${products.length > 1 ? 's' : ''} à découvrir${state.query ? ' pour « ' + state.query + ' »' : ''}`;
    els['product-grid'].innerHTML = products.map(product => {
      const path = store.cleanPath(product.path);
      const img = product.images[0]?.src || '/assets/logo.png';
      const mode = state.productModes[product.id] || 'once';
      const badges = badgesFor(product).map(label => `<span>${esc(label)}</span>`).join('');
      return `<article class="product-card" data-product="${esc(product.id)}"><a class="product-picture" href="${esc(path)}" aria-label="Voir ${esc(product.name)}"><img src="${esc(img)}" alt="${esc(product.name)}" loading="lazy"><span class="product-fallback" aria-hidden="true">méo<small>cafés</small></span><span class="product-badges">${badges}</span></a><div class="product-info"><h3><a href="${esc(path)}">${esc(product.name)}</a></h3><span class="product-format">${formatIcons[state.format]}${esc(formatLabels[state.format])}</span><p class="product-description">${esc(summaryOf(product))}</p><p class="product-notes"><strong>Notes gustatives :</strong> ${esc(notesOf(product))}</p><div class="product-prices"><button type="button" class="price-option ${mode === 'subscribe' ? 'active' : ''}" data-price-mode="subscribe" aria-pressed="${mode === 'subscribe'}"><span>Abonnement<small>−5 % · simulation</small></span><strong>${money(store.unitPrice(product, 'subscribe'))}</strong></button><button type="button" class="price-option ${mode === 'once' ? 'active' : ''}" data-price-mode="once" aria-pressed="${mode === 'once'}"><span>Achat ponctuel<small>Sans renouvellement</small></span><strong>${money(store.unitPrice(product, 'once'))}</strong></button></div><button class="product-action" type="button" data-add="${esc(product.id)}" aria-label="${mode === 'subscribe' ? 'Simuler un abonnement pour' : 'Ajouter au panier'} : ${esc(product.name)}"><span>${mode === 'subscribe' ? 'M’ABONNER' : 'AJOUTER AU PANIER'}</span> ↗</button></div></article>`;
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
      return `<article class="cart-item"><a class="cart-item-image" href="${esc(product.path)}"><img src="${esc(product.images[0]?.src || '/assets/logo.png')}" alt="" loading="lazy"></a><div><h3><a href="${esc(product.path)}">${esc(product.name)}</a></h3><small>${money(store.unitPrice(product, item.mode))} / unité · ${item.mode === 'subscribe' ? 'Abonnement' : 'Achat unique'}${item.option ? ` · ${esc(item.option)}` : ''}</small><div class="cart-item-end"><div class="qty-stepper"><button type="button" data-qty="${index}" data-change="-1" aria-label="Diminuer la quantité de ${esc(product.name)}">−</button><span>${item.qty}</span><button type="button" data-qty="${index}" data-change="1" aria-label="Augmenter la quantité de ${esc(product.name)}">+</button></div><strong>${money(store.unitPrice(product, item.mode) * item.qty)}</strong></div><button class="remove-item" type="button" data-remove="${index}">Retirer</button></div></article>`;
    }).join('');
    const nudge = els['subscription-nudge'];
    if (store.nudgeCount() >= store.NUDGE_COUNT && !state.nudgedAway) {
      nudge.hidden = false;
      nudge.innerHTML = `<div class="nudge-kicker">Votre café à votre rythme</div><h3>Et si ces cafés revenaient régulièrement ?</h3><p>Vous avez ${store.nudgeCount()} cafés en achat unique. Comparez avec une livraison régulière. Les autres produits restent en achat unique.</p><button type="button" id="convert-cart">Voir en abonnement</button><button type="button" class="nudge-secondary" id="dismiss-nudge">Rester en achat unique</button>`;
    } else if (recurring) {
      nudge.hidden = false;
      nudge.innerHTML = `<div class="nudge-kicker">Abonnement simulé</div><h3>Une livraison à votre rythme</h3><p>Le montant récurrent inclut la remise simulée de 5 %. Cette maquette ne crée aucun abonnement.</p><div class="frequency"><label for="frequency-select">Recevoir cette sélection</label><select id="frequency-select"><option>2 semaines</option><option>4 semaines</option><option>6 semaines</option><option>8 semaines</option></select></div>`;
      nudge.querySelector('#frequency-select').value = store.frequency;
    } else nudge.hidden = true;
  }
  function openDrawer() { els.drawer.classList.add('open'); els.drawer.setAttribute('aria-hidden', 'false'); els.scrim.hidden = false; document.body.style.overflow = 'hidden'; document.getElementById('close-cart').focus(); }
  function closeDrawer() { els.drawer.classList.remove('open'); els.drawer.setAttribute('aria-hidden', 'true'); els.scrim.hidden = true; document.body.style.overflow = ''; }
  function setFilter(format, scroll = false) {
    state.format = format;
    state.category = 'best';
    if (scroll) { state.query = ''; els.search.value = ''; }
    document.querySelectorAll('.filter').forEach(button => {
      const active = button.dataset.filter === format;
      button.classList.toggle('active', active);
      button.setAttribute('aria-pressed', String(active));
    });
    document.querySelectorAll('[data-find]').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.find === format)));
    renderCategories();
    renderProducts();
    els['product-grid'].scrollLeft = 0;
    if (scroll) document.getElementById('selection').scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
  }
  function setCategory(category) {
    state.category = category;
    els['selection-categories'].querySelectorAll('[data-category]').forEach(button => {
      const active = button.dataset.category === category;
      button.classList.toggle('active', active);
      button.setAttribute('aria-pressed', String(active));
    });
    renderProducts();
    els['product-grid'].scrollLeft = 0;
  }

  document.querySelectorAll('.filter').forEach(button => button.addEventListener('click', () => setFilter(button.dataset.filter)));
  els['selection-categories'].addEventListener('click', event => {
    const button = event.target.closest('[data-category]');
    if (button) setCategory(button.dataset.category);
  });
  document.querySelectorAll('[data-find]').forEach(button => button.addEventListener('click', () => {
    if (button.dataset.find === 'bio') { setFilter('grain', true); setCategory('bio'); }
    else setFilter(button.dataset.find, true);
  }));
  document.getElementById('menu-toggle').addEventListener('click', () => {
    const nav = document.getElementById('mobile-nav');
    nav.hidden = !nav.hidden;
    document.getElementById('menu-toggle').setAttribute('aria-expanded', String(!nav.hidden));
  });
  els['product-grid'].addEventListener('click', event => {
    const priceButton = event.target.closest('[data-price-mode]');
    if (priceButton) {
      const card = priceButton.closest('.product-card');
      const mode = priceButton.dataset.priceMode;
      state.productModes[card.dataset.product] = mode;
      card.querySelectorAll('[data-price-mode]').forEach(button => {
        const active = button.dataset.priceMode === mode;
        button.classList.toggle('active', active);
        button.setAttribute('aria-pressed', String(active));
      });
      const action = card.querySelector('[data-add]');
      action.querySelector('span').textContent = mode === 'subscribe' ? 'M’ABONNER' : 'AJOUTER AU PANIER';
      action.setAttribute('aria-label', (mode === 'subscribe' ? 'Simuler un abonnement pour : ' : 'Ajouter au panier : ') + store.getProduct(card.dataset.product).name);
      return;
    }
    const button = event.target.closest('[data-add]');
    if (!button) return;
    const mode = state.productModes[button.dataset.add] || 'once';
    if (store.add(button.dataset.add, 1, mode)) { state.nudgedAway = false; renderCart(); openDrawer(); }
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

  renderCategories();
  renderProducts();
  renderCart();
})();
