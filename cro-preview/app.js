const products = [
  { id: '8399', name: 'Café en grains Biologique Original 500g', short: 'Biologique Original', type: 'grain', kind: 'Café en grains', note: 'Équilibré · 500 g', price: 10.60, img: 'https://dam.meo.fr/asset/public/original/2062', tag: 'Le grand classique' },
  { id: '8325', name: 'Café moulu Biologique Original 500g', short: 'Biologique Original', type: 'moulu', kind: 'Café moulu', note: 'Gourmand · 500 g', price: 10.60, img: 'https://dam.meo.fr/asset/public/preview/830x830/1496.jpg', tag: 'À découvrir' },
  { id: '848138', name: 'Capsules Compostables Biologique Harmonie x 20', short: 'Biologique Harmonie', type: 'capsules', kind: 'Capsules compostables', note: 'Harmonieux · x 20', price: 5.95, img: 'https://dam.meo.fr/asset/public/preview/830x830/1506.jpg', tag: 'Format pratique' },
  { id: '8395', name: 'Café en grains Gastronomique 500g', short: 'Gastronomique', type: 'grain', kind: 'Café en grains', note: 'Rond · 500 g', price: 9.70, img: 'https://dam.meo.fr/asset/public/original/2059', tag: '' },
  { id: '8595', name: 'Café moulu Dégustation 250g', short: 'Dégustation', type: 'moulu', kind: 'Café moulu', note: 'Doux · 250 g', price: 4.85, img: 'https://dam.meo.fr/asset/public/preview/830x830/1482.jpg', tag: '' },
  { id: '848132', name: 'Capsules compostables Biologique Décaféiné x20', short: 'Biologique Décaféiné', type: 'capsules', kind: 'Capsules compostables', note: 'Sans caféine · x 20', price: 5.95, img: 'https://dam.meo.fr/asset/public/preview/830x830/1505.jpg', tag: '' }
];

const money = value => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value);
const byId = id => products.find(product => product.id === id);
const els = Object.fromEntries(['product-grid', 'mode-once', 'mode-subscribe', 'cart-count', 'drawer-count', 'cart-items', 'cart-empty', 'subtotal', 'recurring-note', 'subscription-nudge', 'checkout-button', 'drawer', 'scrim', 'checkout-modal', 'summary-mode', 'summary-items', 'summary-total', 'summary-renewal', 'summary-renewal-row', 'empty-results', 'search', 'search-wrap', 'search-toggle'].map(id => [id, document.getElementById(id)]));
const state = { mode: 'once', filter: 'all', query: '', cart: [], frequency: '4 semaines', nudgedAway: false };

function setMode(mode, scroll = false) {
  state.mode = mode;
  els['mode-once'].classList.toggle('active', mode === 'once');
  els['mode-subscribe'].classList.toggle('active', mode === 'subscribe');
  els['mode-once'].setAttribute('aria-pressed', String(mode === 'once'));
  els['mode-subscribe'].setAttribute('aria-pressed', String(mode === 'subscribe'));
  renderProducts();
  if (scroll) document.getElementById('selection').scrollIntoView({ behavior: 'smooth' });
}

function renderProducts() {
  const filtered = products.filter(p => (state.filter === 'all' || state.filter === p.type || (state.filter === 'bio' && /biologique/i.test(p.name))) && (!state.query || `${p.name} ${p.kind}`.toLowerCase().includes(state.query)));
  els['empty-results'].hidden = filtered.length > 0;
  els['product-grid'].innerHTML = filtered.map(p => {
    const quantity = state.mode === 'subscribe' ? Math.ceil(20 / p.price) : 1;
    const total = p.price * quantity;
    return `<article class="product-card"><div class="product-picture">${p.tag ? `<span class="product-tag">${p.tag}</span>` : ''}<img src="${p.img}" alt="${p.name}" loading="lazy"><div class="product-fallback" aria-hidden="true">méo<small>cafés</small></div></div><div class="product-info"><span class="product-kind">${p.kind}</span><h3>${p.short}</h3><div class="product-meta"><span>${p.note}</span><span>${state.mode === 'subscribe' ? `${quantity} unités / envoi` : 'Achat unique'}</span></div><div class="product-bottom"><div class="price-group"><strong>${money(total)}</strong><small>${state.mode === 'subscribe' ? 'par livraison · simulation' : `${money(p.price)} l’unité`}</small></div><button class="product-action" type="button" data-add="${p.id}" aria-label="${state.mode === 'subscribe' ? 'Simuler un abonnement' : 'Ajouter au panier'} : ${p.name}">${state.mode === 'subscribe' ? 'M’abonner' : 'Ajouter'} <small>↗</small></button></div></div></article>`;
  }).join('');
  els['product-grid'].querySelectorAll('.product-picture img').forEach(img => {
    img.addEventListener('error', () => img.parentElement.classList.add('broken'));
    if (img.complete && img.naturalWidth === 0) img.parentElement.classList.add('broken');
  });
}

function addToCart(id) {
  const product = byId(id);
  if (!product) return;
  const quantity = state.mode === 'subscribe' ? Math.ceil(20 / product.price) : 1;
  const existing = state.cart.find(item => item.id === id);
  if (existing) existing.qty += quantity;
  else state.cart.push({ id, qty: quantity });
  state.nudgedAway = false;
  renderCart();
  openDrawer();
}

function cartTotal() { return state.cart.reduce((sum, item) => sum + byId(item.id).price * item.qty, 0); }
function cartQty() { return state.cart.reduce((sum, item) => sum + item.qty, 0); }

function renderCart() {
  const count = cartQty();
  const total = cartTotal();
  els['cart-count'].textContent = count;
  els['drawer-count'].textContent = `(${count})`;
  els['cart-empty'].hidden = count > 0;
  els['checkout-button'].disabled = count === 0 || (state.mode === 'subscribe' && total < 20);
  els['subtotal'].textContent = money(total);
  els['recurring-note'].textContent = !count ? '' : state.mode === 'subscribe' ? `Simulation : ${money(total)} aujourd’hui, puis ${money(total)} toutes les ${state.frequency}. Livraison éventuelle non comprise.` : 'Une seule commande, sans renouvellement. Livraison éventuelle non comprise.';
  els['cart-items'].innerHTML = state.cart.map(item => {
    const p = byId(item.id);
    return `<article class="cart-item"><div class="cart-item-image"><img src="${p.img}" alt="" loading="lazy"></div><div><h3>${p.name}</h3><small>${money(p.price)} / unité</small><div class="cart-item-end"><div class="qty-stepper"><button type="button" data-qty="${p.id}" data-change="-1" aria-label="Diminuer la quantité de ${p.short}">−</button><span>${item.qty}</span><button type="button" data-qty="${p.id}" data-change="1" aria-label="Augmenter la quantité de ${p.short}">+</button></div><strong>${money(p.price * item.qty)}</strong></div><button class="remove-item" type="button" data-remove="${p.id}">Retirer</button></div></article>`;
  }).join('');
  const nudge = els['subscription-nudge'];
  nudge.hidden = !count;
  if (count && state.mode === 'once' && count >= 2 && total >= 20 && !state.nudgedAway) {
    nudge.innerHTML = `<div class="nudge-kicker">Votre sélection pourrait revenir</div><h3>Et si votre café arrivait à votre rythme ?</h3><p>Comparez votre panier avec une livraison régulière. Choix explicite avant confirmation, sans remise supposée.</p><button type="button" id="convert-cart">Voir en abonnement</button><button type="button" class="nudge-secondary" id="dismiss-nudge">Rester en achat unique</button>`;
  } else if (state.mode === 'subscribe') {
    nudge.innerHTML = total >= 20 ? `<div class="nudge-kicker">Abonnement simulé</div><h3>Une livraison à votre rythme.</h3><p>Vous pourrez vérifier tous les montants avant confirmation. Cette maquette ne crée aucun abonnement.</p><div class="frequency"><label for="frequency-select">Recevoir cette sélection</label><select id="frequency-select"><option>2 semaines</option><option>4 semaines</option><option>6 semaines</option><option>8 semaines</option></select></div><button type="button" class="nudge-secondary" id="back-once">Revenir à l’achat unique</button>` : `<div class="nudge-kicker">Minimum de démonstration</div><h3>Encore ${money(20 - total)} pour atteindre 20 €.</h3><p>Dans cette maquette, le panier d’abonnement est simulé à partir de 20 €. Ajoutez un article ou revenez à l’achat unique.</p><button type="button" class="nudge-secondary" id="back-once">Revenir à l’achat unique</button>`;
    const select = nudge.querySelector('#frequency-select');
    if (select) select.value = state.frequency;
  } else {
    nudge.hidden = true;
  }
}

function updateQty(id, change) {
  const item = state.cart.find(entry => entry.id === id);
  if (!item) return;
  item.qty += change;
  if (item.qty <= 0) state.cart = state.cart.filter(entry => entry.id !== id);
  renderCart();
}

function openDrawer() {
  els.drawer.classList.add('open');
  els.drawer.setAttribute('aria-hidden', 'false');
  els.scrim.hidden = false;
  document.body.style.overflow = 'hidden';
  document.getElementById('close-cart').focus();
}
function closeDrawer() {
  els.drawer.classList.remove('open');
  els.drawer.setAttribute('aria-hidden', 'true');
  if (!els['checkout-modal'].classList.contains('open')) { els.scrim.hidden = true; document.body.style.overflow = ''; }
}
function openCheckout() {
  if (els['checkout-button'].disabled) return;
  closeDrawer();
  els['summary-mode'].textContent = state.mode === 'subscribe' ? `Abonnement · toutes les ${state.frequency}` : 'Achat unique';
  els['summary-items'].textContent = `${cartQty()} article${cartQty() > 1 ? 's' : ''}`;
  els['summary-total'].textContent = money(cartTotal());
  els['summary-renewal-row'].hidden = state.mode !== 'subscribe';
  els['summary-renewal'].textContent = `${money(cartTotal())} / ${state.frequency}`;
  els['checkout-modal'].classList.add('open');
  els['checkout-modal'].setAttribute('aria-hidden', 'false');
  els.scrim.hidden = false;
  document.getElementById('close-checkout').focus();
}
function closeCheckout() {
  els['checkout-modal'].classList.remove('open');
  els['checkout-modal'].setAttribute('aria-hidden', 'true');
  els.scrim.hidden = true;
  document.body.style.overflow = '';
}

document.querySelectorAll('[data-mode]').forEach(button => button.addEventListener('click', () => setMode(button.dataset.mode, true)));
els['mode-once'].addEventListener('click', () => setMode('once'));
els['mode-subscribe'].addEventListener('click', () => setMode('subscribe'));
function setFilter(filter) {
  state.filter = filter;
  document.querySelectorAll('.filter').forEach(item => { const active = item.dataset.filter === filter; item.classList.toggle('active', active); item.setAttribute('aria-pressed', String(active)); });
  renderProducts();
}
document.querySelectorAll('.filter').forEach(button => button.addEventListener('click', () => setFilter(button.dataset.filter)));
document.querySelectorAll('[data-nav-filter]').forEach(link => link.addEventListener('click', () => {
  setFilter(link.dataset.navFilter);
  document.getElementById('mobile-nav').hidden = true;
  document.getElementById('menu-toggle').setAttribute('aria-expanded', 'false');
}));
document.getElementById('menu-toggle').addEventListener('click', () => {
  const nav = document.getElementById('mobile-nav');
  nav.hidden = !nav.hidden;
  document.getElementById('menu-toggle').setAttribute('aria-expanded', String(!nav.hidden));
});
els['product-grid'].addEventListener('click', event => { const button = event.target.closest('[data-add]'); if (button) addToCart(button.dataset.add); });
els['cart-items'].addEventListener('click', event => {
  const qty = event.target.closest('[data-qty]');
  const remove = event.target.closest('[data-remove]');
  if (qty) updateQty(qty.dataset.qty, Number(qty.dataset.change));
  if (remove) { state.cart = state.cart.filter(item => item.id !== remove.dataset.remove); renderCart(); }
});
els['subscription-nudge'].addEventListener('click', event => {
  if (event.target.closest('#convert-cart')) { state.mode = 'subscribe'; setMode('subscribe'); renderCart(); }
  if (event.target.closest('#dismiss-nudge')) { state.nudgedAway = true; renderCart(); }
  if (event.target.closest('#back-once')) { state.mode = 'once'; setMode('once'); renderCart(); }
});
els['subscription-nudge'].addEventListener('change', event => { if (event.target.id === 'frequency-select') { state.frequency = event.target.value; renderCart(); } });
document.getElementById('cart-trigger').addEventListener('click', openDrawer);
document.getElementById('close-cart').addEventListener('click', closeDrawer);
document.getElementById('empty-shop').addEventListener('click', () => { closeDrawer(); document.getElementById('selection').scrollIntoView({ behavior: 'smooth' }); });
els['checkout-button'].addEventListener('click', openCheckout);
document.getElementById('close-checkout').addEventListener('click', closeCheckout);
document.getElementById('finish-demo').addEventListener('click', () => { closeCheckout(); document.getElementById('selection').scrollIntoView({ behavior: 'smooth' }); });
els.scrim.addEventListener('click', () => { closeDrawer(); closeCheckout(); });
document.addEventListener('keydown', event => { if (event.key === 'Escape') { closeDrawer(); closeCheckout(); } });
document.getElementById('discover-sub').addEventListener('click', () => setMode('subscribe', true));
els['search-toggle'].addEventListener('click', () => { els['search-wrap'].hidden = !els['search-wrap'].hidden; els['search-toggle'].setAttribute('aria-expanded', String(!els['search-wrap'].hidden)); if (!els['search-wrap'].hidden) els.search.focus(); });
els.search.addEventListener('input', () => { state.query = els.search.value.trim().toLowerCase(); renderProducts(); });

renderProducts();
renderCart();
