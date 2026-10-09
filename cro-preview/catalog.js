/* Visual storefront routes backed by a dated public catalogue snapshot. */
(async () => {
  const store = window.MeoStore;
  const page = document.getElementById('page-content');
  const mini = document.getElementById('mini-cart');
  const miniScrim = document.getElementById('mini-scrim');
  const toast = document.getElementById('toast');
  const money = store.money;
  const esc = store.escape;
  const pathKey = value => (value || '/').replace(/\/+$/, '') || '/';
  const imageOf = product => product?.images?.[0]?.src || '/assets/logo.png';
  let listing = null;
  let productMode = 'once';
  let toastTimer;

  try { await store.ready; }
  catch {
    page.innerHTML = '<div class="shell loading-state"><h1>Le catalogue ne peut pas se charger</h1><p>Actualisez la page pour réessayer.</p><a class="outline-btn" href="/">Retour à l’accueil</a></div>';
    return;
  }

  function setTitle(title) { document.title = `${title} — Méo · Prévisualisation`; }
  function updateCount() { document.getElementById('cart-count').textContent = store.count(); }
  document.addEventListener('meo:cart-change', updateCount);
  updateCount();

  function productKind(product) {
    if (product.categoryIds.includes(192)) return 'Café en grains';
    if (product.categoryIds.includes(202)) return 'Café moulu';
    if (product.categoryIds.includes(214)) return 'Capsules & dosettes';
    if (product.categoryIds.includes(224)) return 'Machine ou accessoire';
    if (product.categoryIds.includes(1224)) return 'Épicerie';
    return store.isCoffee(product) ? 'Café Méo' : 'La boutique Méo';
  }
  function formatOf(product) {
    if (product.categoryIds.includes(192)) return 'grain';
    if (product.categoryIds.includes(202)) return 'moulu';
    if (product.categoryIds.includes(214)) return 'capsules';
    if (product.categoryIds.includes(224)) return 'machines';
    return 'autres';
  }
  function breadcrumb(items) {
    return `<nav class="breadcrumbs shell" aria-label="Fil d’Ariane"><a href="/">Accueil</a>${items.map(item => `<span class="sep">›</span>${item.path ? `<a href="${esc(store.cleanPath(item.path))}">${esc(item.name)}</a>` : `<span aria-current="page">${esc(item.name)}</span>`}`).join('')}</nav>`;
  }
  function categoryLineage(category) {
    const line = [];
    let current = category;
    while (current && line.length < 8) {
      line.unshift(current);
      current = store.getCategory(current.parent);
    }
    return line;
  }
  function categoryChildren(id) { return store.categories.filter(category => category.parent === id); }
  function descendantIds(id) {
    const result = new Set([id]);
    let changed = true;
    while (changed) {
      changed = false;
      store.categories.forEach(category => {
        if (result.has(category.parent) && !result.has(category.id)) { result.add(category.id); changed = true; }
      });
    }
    return result;
  }
  function priceHtml(product) {
    const sale = product.onSale && product.regularPrice > product.price;
    return `<div class="price-row"><strong>${product.type === 'variable' ? 'Dès ' : ''}${money(product.price)}</strong>${sale ? `<del>${money(product.regularPrice)}</del>` : ''}</div>`;
  }
  function card(product, mode = 'once') {
    const baseUrl = store.cleanPath(product.path);
    const url = mode === 'subscribe' ? `${baseUrl}?mode=subscribe` : baseUrl;
    const simple = product.type === 'simple' && product.purchasable && product.inStock;
    const action = mode === 'subscribe' ? 'M’abonner' : 'Ajouter au panier';
    return `<article class="product-card"><a class="product-picture" href="${esc(url)}" aria-label="Voir ${esc(product.name)}">${product.onSale ? '<span class="sale-tag">Bon plan</span>' : ''}<img src="${esc(imageOf(product))}" alt="${esc(product.images[0]?.alt || product.name)}" loading="lazy"><span class="product-fallback" aria-hidden="true">méo<small>cafés</small></span></a><div class="product-info"><span class="product-kind">${esc(productKind(product))}</span><h3><a href="${esc(url)}">${esc(product.name)}</a></h3><div class="product-bottom">${priceHtml(product)}${simple ? `<button class="product-action" type="button" data-quick-add="${esc(product.id)}" data-mode="${mode}" aria-label="${action} : ${esc(product.name)}">${action} <small>↗</small></button>` : `<a class="product-action" href="${esc(url)}">${product.inStock ? 'Choisir les options' : 'Voir le produit'} <small>↗</small></a>`}</div></div></article>`;
  }
  function markBrokenImages(root = page) {
    root.querySelectorAll('.product-picture img').forEach(img => {
      img.addEventListener('error', () => img.parentElement.classList.add('broken'), { once: true });
      if (img.complete && img.naturalWidth === 0) img.parentElement.classList.add('broken');
    });
  }
  function categoryIndex() {
    const roots = [1246, 224, 1224, 268];
    return `<section class="category-index"><div class="shell"><h2>TOUTES LES CATÉGORIES MÉO</h2><div class="category-groups">${roots.map(id => {
      const root = store.getCategory(id);
      const descendants = store.categories.filter(category => category.id === id || descendantIds(id).has(category.id));
      return `<div class="category-group"><h3><a href="${esc(root.path)}">${esc(root.name)}</a></h3><ul>${descendants.filter(category => category.id !== id).slice(0, 13).map(category => `<li><a href="${esc(category.path)}">${esc(category.name)}</a></li>`).join('')}</ul></div>`;
    }).join('')}</div><details class="all-categories"><summary>Voir les 49 catégories de la capture publique</summary><div class="category-band">${store.categories.map(category => `<a href="${esc(category.path)}">${esc(category.name)}</a>`).join('')}</div></details></div></section>`;
  }

  function renderListing(title, description, products, trail, { showIndex = false, children = [] } = {}) {
    listing = { base: products, limit: 12, query: '', sort: 'default', facets: new Set() };
    const available = ['grain', 'moulu', 'capsules', 'machines', 'autres'].filter(format => products.some(product => formatOf(product) === format));
    const names = { grain: 'Café en grains', moulu: 'Café moulu', capsules: 'Capsules et dosettes', machines: 'Machines et accessoires', autres: 'Autres produits' };
    page.innerHTML = `${breadcrumb(trail)}<div class="shell catalog-intro"><div class="page-intro"><span class="eyebrow">LA BOUTIQUE MÉO</span><h1>${esc(title.toUpperCase())}</h1>${description ? `<p class="long-copy">${esc(description)}</p>` : '<p>Découvrez la sélection Méo et choisissez le café qui vous ressemble.</p>'}</div>${children.length ? `<nav class="category-band" aria-label="Sous-catégories">${children.map(child => `<a href="${esc(child.path)}">${esc(child.name)}</a>`).join('')}</nav>` : ''}</div>${showIndex ? categoryIndex() : ''}<div class="shell catalog-layout"><aside class="catalog-sidebar"><h2>Affiner la sélection</h2>${available.length > 1 ? `<div class="facet"><h3>Type de produit</h3>${available.map(format => `<label><input type="checkbox" data-facet="${format}">${names[format]}</label>`).join('')}</div>` : ''}<div class="facet"><h3>Découvrir</h3>${products.some(product => product.categoryIds.includes(222)) ? '<label><input type="checkbox" data-facet="bio">Café biologique</label>' : ''}${products.some(product => product.onSale) ? '<label><input type="checkbox" data-facet="sale">Bons plans</label>' : ''}</div><div class="facet"><a href="/catalogue/">Voir tous les produits →</a></div></aside><section aria-label="Produits"><div class="catalog-toolbar"><span id="result-count"></span><input class="catalog-search" id="catalog-search" type="search" placeholder="Rechercher dans cette sélection" aria-label="Rechercher dans cette sélection"><label>Trier par <select id="catalog-sort"><option value="default">Sélection Méo</option><option value="price-asc">Prix croissant</option><option value="price-desc">Prix décroissant</option><option value="name">Nom</option><option value="rating">Avis clients</option></select></label></div><div class="catalog-grid" id="listing-grid"></div><button class="show-more" id="show-more" type="button" hidden>Voir plus de produits</button></section></div>`;
    if (description.length > 260) {
      const intro = page.querySelector('.page-intro .long-copy');
      intro.textContent = `${description.slice(0, 257).replace(/\s+\S*$/, '')}…`;
      const details = document.createElement('details');
      details.className = 'read-more';
      details.innerHTML = `<summary>Lire la suite</summary><p>${esc(description)}</p>`;
      intro.after(details);
    }
    renderListingCards();
  }
  function renderListingCards() {
    if (!listing) return;
    const query = listing.query.toLocaleLowerCase('fr');
    const formats = ['grain', 'moulu', 'capsules', 'machines', 'autres'].filter(item => listing.facets.has(item));
    let products = listing.base.filter(product => {
      if (query && !`${product.name} ${product.short}`.toLocaleLowerCase('fr').includes(query)) return false;
      if (formats.length && !formats.includes(formatOf(product))) return false;
      if (listing.facets.has('bio') && !product.categoryIds.includes(222)) return false;
      if (listing.facets.has('sale') && !product.onSale) return false;
      return true;
    });
    if (listing.sort === 'price-asc') products = products.sort((a, b) => a.price - b.price);
    if (listing.sort === 'price-desc') products = products.sort((a, b) => b.price - a.price);
    if (listing.sort === 'name') products = products.sort((a, b) => a.name.localeCompare(b.name, 'fr'));
    if (listing.sort === 'rating') products = products.sort((a, b) => b.rating - a.rating);
    document.getElementById('result-count').textContent = `${products.length} produit${products.length > 1 ? 's' : ''}`;
    document.getElementById('listing-grid').innerHTML = products.length ? products.slice(0, listing.limit).map(card).join('') : '<div class="no-products"><p>Aucun produit dans cette sélection.</p><a class="outline-btn" href="/catalogue/">Voir tout le catalogue</a></div>';
    document.getElementById('show-more').hidden = products.length <= listing.limit;
    markBrokenImages();
  }

  function renderCatalog() {
    setTitle('Tous les produits');
    renderListing('Tous les produits Méo', 'Grains, cafés moulus, capsules, dosettes, machines et accessoires : explorez la boutique dans cette prévisualisation.', store.products, [{ name: 'Tous les produits' }], { showIndex: true });
  }
  function renderCategory(category) {
    setTitle(category.name);
    const ids = descendantIds(category.id);
    const products = store.products.filter(product => product.categoryIds.some(id => ids.has(id)));
    const line = categoryLineage(category);
    renderListing(category.name, category.description, products, [...line.slice(0, -1).map(item => ({ name: item.name, path: item.path })), { name: category.name }], { children: categoryChildren(category.id) });
    if (!products.length) {
      const grid = document.getElementById('listing-grid');
      grid.innerHTML = `<div class="no-products"><p>La capture du catalogue public ne contient pas de produit dans cette catégorie.</p><a class="outline-btn" href="https://www.meo.fr${esc(category.path)}" target="_blank" rel="noopener">Voir la catégorie actuelle sur meo.fr</a></div>`;
    }
  }
  function renderProduct(product) {
    listing = null;
    setTitle(product.name);
    const mainCategory = store.getCategory(product.categoryIds.find(id => [192, 202, 214, 224, 1224].includes(id)) || product.categoryIds[0]);
    const trail = [{ name: 'Boutique', path: '/catalogue/' }];
    if (mainCategory) trail.push({ name: mainCategory.name, path: mainCategory.path });
    trail.push({ name: product.name });
    const canSubscribe = store.canSubscribe(product);
    productMode = canSubscribe && new URLSearchParams(location.search).get('mode') === 'subscribe' ? 'subscribe' : 'once';
    const gallery = product.images.length ? product.images : [{ src: '/assets/logo.png', alt: product.name }];
    const variationAttrs = product.type === 'variable' ? product.attributes.filter(attribute => attribute.hasVariations) : [];
    const siblingCategory = mainCategory ? mainCategory.id : product.categoryIds[0];
    const related = store.products.filter(item => item.id !== product.id && item.categoryIds.includes(siblingCategory)).slice(0, 4);
    page.innerHTML = `${breadcrumb(trail)}<div class="shell product-page"><div class="product-detail"><div class="product-gallery"><div class="gallery-main"><img id="gallery-main" src="${esc(gallery[0].src)}" alt="${esc(gallery[0].alt || product.name)}"></div>${gallery.length > 1 ? `<div class="gallery-thumbs" role="group" aria-label="Images du produit">${gallery.map((image, index) => `<button type="button" data-gallery="${index}" class="${index === 0 ? 'active' : ''}" aria-label="Image ${index + 1}"><img src="${esc(image.src)}" alt=""></button>`).join('')}</div>` : ''}</div><div class="product-summary"><span class="eyebrow">${esc(productKind(product))}</span><h1>${esc(product.name)}</h1>${product.reviewCount ? `<div class="review-line"><span class="stars">★★★★★</span> ${product.rating.toFixed(1).replace('.', ',')} / 5 · ${product.reviewCount} avis</div>` : ''}<div class="product-price"><strong>${product.type === 'variable' ? 'Dès ' : ''}${money(product.price)}</strong>${product.onSale && product.regularPrice > product.price ? `<del>${money(product.regularPrice)}</del>` : ''}</div><p class="description">${esc((product.short || product.description).slice(0, 260))}</p><form class="purchase-box" id="product-form"><h2>Choisissez votre mode d’achat</h2><div class="purchase-modes" role="group" aria-label="Mode d’achat"><button type="button" data-purchase-mode="once" class="${productMode === 'once' ? 'active' : ''}" aria-pressed="${productMode === 'once'}">Achat unique<small>Une seule commande</small></button>${canSubscribe ? `<button type="button" data-purchase-mode="subscribe" class="${productMode === 'subscribe' ? 'active' : ''}" aria-pressed="${productMode === 'subscribe'}">Abonnement<small>Livraison régulière</small></button>` : ''}</div><p class="purchase-note">${canSubscribe ? 'L’abonnement est simulé. Aucune remise ni condition commerciale n’est supposée.' : 'Ce produit est présenté en achat unique dans la maquette.'}</p>${variationAttrs.map((attribute, index) => `<div class="field-row"><label for="variant-${index}">${esc(attribute.name)}</label><select class="variant-select" id="variant-${index}" required><option value="">Choisir une option</option>${attribute.options.map(option => `<option value="${esc(option)}">${esc(option)}</option>`).join('')}</select></div>`).join('')}<div class="field-row" id="frequency-row" ${productMode !== 'subscribe' ? 'hidden' : ''}><label for="product-frequency">Livraison</label><select id="product-frequency"><option>2 semaines</option><option>4 semaines</option><option>6 semaines</option><option>8 semaines</option></select></div><div class="field-row"><label for="product-qty">Quantité</label><input id="product-qty" type="number" min="1" max="99" step="1" value="1" inputmode="numeric"></div><button class="primary-btn" type="submit" ${!product.purchasable || !product.inStock ? 'disabled' : ''}>${product.inStock ? 'Ajouter au panier →' : 'Indisponible'}</button><p class="inline-error" id="product-error" role="alert" hidden></p></form><div class="product-reassurance"><span>Livraison offerte dès 40 € d’achat</span><span>Paiement simulé dans cette prévisualisation</span><span>Mode d’achat choisi explicitement</span><span>Panier modifiable avant validation</span></div></div></div><section class="product-tabs"><h2>DESCRIPTION</h2><p>${esc(product.description || product.short || 'Informations détaillées à consulter sur la fiche actuelle meo.fr.')}</p>${product.attributes.length ? `<h2>CARACTÉRISTIQUES</h2><dl class="attribute-list">${product.attributes.filter(attribute => !attribute.hasVariations).map(attribute => `<div><dt>${esc(attribute.name)}</dt><dd>${esc(attribute.options.join(', '))}</dd></div>`).join('')}</dl>` : ''}<p class="selection-note">Données du catalogue public capturées le ${esc(store.snapshot)}. Les prix et disponibilités peuvent évoluer sur meo.fr.</p></section>${related.length ? `<section class="related-products"><h2>VOUS AIMEREZ AUSSI</h2><div class="catalog-grid">${related.map(card).join('')}</div></section>` : ''}</div>`;
    const frequency = document.getElementById('product-frequency');
    if (frequency) frequency.value = store.frequency;
    markBrokenImages();
  }

  function cartRow(item, index) {
    const product = store.getProduct(item.id);
    if (!product) return '';
    return `<article class="cart-row"><a href="${esc(product.path)}"><img src="${esc(imageOf(product))}" alt="${esc(product.name)}"></a><div><h2><a href="${esc(product.path)}">${esc(product.name)}</a></h2><small>${item.mode === 'subscribe' ? `Abonnement · toutes les ${esc(store.frequency)}` : 'Achat unique'}${item.option ? ` · ${esc(item.option)}` : ''}</small><small>${money(product.price)} l’unité</small><div class="qty-stepper"><button type="button" data-cart-qty="${index}" data-change="-1" aria-label="Diminuer la quantité">−</button><span>${item.qty}</span><button type="button" data-cart-qty="${index}" data-change="1" aria-label="Augmenter la quantité">+</button></div><button type="button" class="remove-item" data-cart-remove="${index}">Retirer</button></div><strong class="price">${money(product.price * item.qty)}</strong></article>`;
  }
  function cartNudge() {
    if (store.nudgeCount() < store.NUDGE_COUNT) return '';
    return `<div class="cart-nudge"><span class="eyebrow">VOTRE CAFÉ À VOTRE RYTHME</span><h2>Et si ces cafés revenaient régulièrement ?</h2><p>Vous avez ${store.nudgeCount()} cafés en achat unique dans le panier. Comparez avec une livraison régulière, sans engagement ou remise supposée dans cette maquette. Les autres produits restent en achat unique.</p><button class="primary-btn" type="button" data-convert-coffee>Voir ces cafés en abonnement</button></div>`;
  }
  function frequencyBox() {
    return store.total('subscribe') ? `<div class="frequency-box"><label for="cart-frequency">Recevoir les cafés en abonnement</label><select id="cart-frequency"><option>2 semaines</option><option>4 semaines</option><option>6 semaines</option><option>8 semaines</option></select><p class="summary-note">Le montant récurrent estimé apparaît dans le récapitulatif. Aucun renouvellement réel dans cette maquette.</p></div>` : '';
  }
  function summaryBox(checkout = false) {
    const one = store.total('once');
    const recurring = store.total('subscribe');
    return `<aside class="cart-summary"><h2>VOTRE RÉCAPITULATIF</h2><div class="total-line"><span>${store.count()} article${store.count() > 1 ? 's' : ''}</span><strong>${money(store.total())}</strong></div>${one ? `<div class="total-line"><span>Achat unique</span><strong>${money(one)}</strong></div>` : ''}${recurring ? `<div class="total-line"><span>Abonnement · toutes les ${esc(store.frequency)}</span><strong>${money(recurring)}</strong></div>` : ''}<div class="total-line grand"><span>Aujourd’hui, indicatif</span><strong>${money(store.total())}</strong></div>${recurring ? `<div class="total-line"><span>Puis à chaque livraison, indicatif</span><strong>${money(recurring)}</strong></div>` : ''}<p class="summary-note">Frais de livraison non calculés. Conditions, disponibilité et prix à confirmer dans WooCommerce avant toute mise en production.</p>${checkout ? '<a class="outline-btn" href="/panier/" style="width:100%">Modifier mon panier</a>' : '<a class="primary-btn" href="/commander/">Voir le paiement simulé →</a>'}</aside>`;
  }
  function renderCart() {
    listing = null;
    setTitle('Mon panier');
    page.innerHTML = `${breadcrumb([{ name: 'Boutique', path: '/catalogue/' }, { name: 'Mon panier' }])}<div class="shell cart-page"><span class="eyebrow">VOTRE SÉLECTION</span><h1>MON PANIER</h1><p class="section-lead">Modifiez les quantités, puis vérifiez le mode d’achat avant de poursuivre.</p>${store.items.length ? `<div class="checkout-layout"><div><div class="cart-list">${store.items.map(cartRow).join('')}</div>${cartNudge()}${frequencyBox()}<p class="selection-note">Les produits et prix proviennent du catalogue public capturé le ${esc(store.snapshot)}.</p></div>${summaryBox()}</div>` : `<div class="cart-empty-page"><h2>Votre panier attend son café.</h2><p>Explorez les produits Méo et ajoutez vos favoris.</p><a class="primary-btn" href="/catalogue/">Voir les produits →</a></div>`}</div>`;
    const frequency = document.getElementById('cart-frequency');
    if (frequency) frequency.value = store.frequency;
  }
  function renderCheckout() {
    listing = null;
    setTitle('Paiement simulé');
    page.innerHTML = `${breadcrumb([{ name: 'Mon panier', path: '/panier/' }, { name: 'Paiement simulé' }])}<div class="shell checkout-page"><span class="eyebrow">DERNIÈRE ÉTAPE DE LA DÉMONSTRATION</span><h1>VÉRIFIER MA COMMANDE</h1><p class="section-lead">Le parcours s’arrête ici : aucune donnée personnelle, commande ni paiement n’est traité dans cette prévisualisation.</p>${store.items.length ? `<div class="checkout-layout"><div><section class="checkout-step"><h2>1. Coordonnées et livraison</h2><p>Sur la boutique finale, le client choisira une adresse et un mode de livraison. Les frais exacts seront calculés par WooCommerce.</p><div class="fake-fields"><input type="text" placeholder="Prénom" disabled aria-label="Prénom désactivé"><input type="text" placeholder="Nom" disabled aria-label="Nom désactivé"><input type="email" placeholder="Adresse e-mail" disabled aria-label="Adresse e-mail désactivée"><input type="text" placeholder="Adresse de livraison" disabled aria-label="Adresse de livraison désactivée"><span>Champs désactivés dans la maquette.</span></div></section><section class="checkout-step"><h2>2. Paiement</h2><p>Le moyen de paiement sera sélectionné sur meo.fr, après validation du panier et des conditions applicables.</p></section><section class="checkout-step"><h2>3. Récapitulatif</h2><p>${store.total('subscribe') ? `Les cafés en abonnement seraient renouvelés toutes les ${esc(store.frequency)} après confirmation explicite.` : 'Une commande unique, sans renouvellement.'} Cette page ne permet pas de confirmer un achat.</p></section><a class="outline-btn" href="/panier/">← Retour au panier</a></div>${summaryBox(true)}</div>` : `<div class="cart-empty-page"><h2>Votre panier est vide.</h2><a class="primary-btn" href="/catalogue/">Choisir un café</a></div>`}</div>`;
  }
  function renderSubscription() {
    listing = null;
    setTitle('L’abonnement café');
    const picks = store.products.filter(product => store.canSubscribe(product) && product.type === 'simple').slice(0, 8);
    page.innerHTML = `${breadcrumb([{ name: 'L’abonnement' }])}<div class="shell subscription-page"><div class="subscription-hero"><div><span class="eyebrow">UN CAFÉ MÉO À VOTRE RYTHME</span><h1>VOTRE CAFÉ PRÉFÉRÉ, QUAND VOUS LE VOULEZ</h1><p>Choisissez vos cafés, la quantité et une fréquence de livraison. Le panier distingue le premier montant du montant récurrent avant toute décision.</p><a class="outline-btn" href="#sub-products">Choisir mes cafés ↓</a></div><img src="https://www.meo.fr/app/uploads/2024/04/avril-mai_a_h-scaled-e1713176107238-1024x870.jpg" alt="Café Méo prêt à être dégusté"></div><div class="subscription-steps"><article><strong>01</strong><h2>Choisissez vos cafés</h2><p>Chaque fiche affiche le mode d’achat et le prix du produit.</p></article><article><strong>02</strong><h2>Réglez votre rythme</h2><p>Sélectionnez 2, 4, 6 ou 8 semaines dans la maquette.</p></article><article><strong>03</strong><h2>Vérifiez le panier</h2><p>Le total du jour et le montant par livraison sont présentés séparément.</p></article></div><section class="subscription-choices" id="sub-products"><span class="eyebrow">POUR COMMENCER</span><h2>CHOISISSEZ VOTRE CAFÉ</h2><div class="catalog-grid">${picks.map(product => card(product, 'subscribe')).join('')}</div><p class="selection-note">Abonnement simulé. Les conditions réelles et l’éligibilité des produits doivent être vérifiées sur la boutique.</p></section></div>`;
    markBrokenImages();
  }
  function renderSearch() {
    const query = new URLSearchParams(location.search).get('s')?.trim() || '';
    setTitle(query ? `Recherche : ${query}` : 'Recherche');
    renderListing(query ? `Résultats pour « ${query} »` : 'Rechercher un produit', 'Recherchez par nom, format ou café préféré.', store.products, [{ name: 'Recherche' }]);
    listing.query = query;
    document.getElementById('catalog-search').value = query;
    renderListingCards();
  }
  function renderNotFound() {
    setTitle('Page introuvable');
    page.innerHTML = `${breadcrumb([{ name: 'Page introuvable' }])}<div class="shell loading-state"><h1>Cette page n’est pas dans la prévisualisation.</h1><p>Retrouvez tous les produits et catégories Méo dans le catalogue.</p><a class="primary-btn" href="/catalogue/">Voir le catalogue →</a></div>`;
  }

  const route = pathKey(location.pathname);
  const category = store.categories.find(item => pathKey(item.path) === route);
  const product = store.products.find(item => pathKey(item.path) === route);
  if (category) renderCategory(category);
  else if (product) renderProduct(product);
  else if (route === '/catalogue') renderCatalog();
  else if (route === '/panier') renderCart();
  else if (route === '/commander') renderCheckout();
  else if (route === '/abonnement') renderSubscription();
  else if (route === '/recherche') renderSearch();
  else renderNotFound();

  const menuToggle = document.getElementById('menu-toggle');
  menuToggle.addEventListener('click', () => {
    const nav = document.getElementById('mobile-nav');
    nav.hidden = !nav.hidden;
    menuToggle.setAttribute('aria-expanded', String(!nav.hidden));
  });
  document.getElementById('search-toggle').addEventListener('click', event => {
    const form = document.getElementById('search-wrap');
    form.hidden = !form.hidden;
    event.currentTarget.setAttribute('aria-expanded', String(!form.hidden));
    if (!form.hidden) form.querySelector('input').focus();
  });
  function openMini(product, mode, qty, option) {
    document.getElementById('mini-content').innerHTML = `<div class="mini-product"><img src="${esc(imageOf(product))}" alt=""><div><strong>${esc(product.name)}</strong><small>${qty} × ${money(product.price)} · ${mode === 'subscribe' ? `Abonnement toutes les ${esc(store.frequency)}` : 'Achat unique'}</small>${option ? `<small>${esc(option)}</small>` : ''}</div></div>${store.nudgeCount() >= store.NUDGE_COUNT ? `<div class="cart-nudge"><strong>Votre café à votre rythme</strong><p>Dès ${store.NUDGE_COUNT} cafés, comparez votre panier avec un abonnement.</p></div>` : ''}`;
    mini.classList.add('open'); mini.setAttribute('aria-hidden', 'false'); miniScrim.hidden = false; document.body.style.overflow = 'hidden'; document.getElementById('mini-close').focus();
  }
  function closeMini() { mini.classList.remove('open'); mini.setAttribute('aria-hidden', 'true'); miniScrim.hidden = true; document.body.style.overflow = ''; }
  function announce(message) { toast.textContent = message; toast.hidden = false; clearTimeout(toastTimer); toastTimer = setTimeout(() => { toast.hidden = true; }, 3500); }
  document.getElementById('mini-close').addEventListener('click', closeMini);
  miniScrim.addEventListener('click', closeMini);
  document.addEventListener('keydown', event => { if (event.key === 'Escape') closeMini(); });

  page.addEventListener('click', event => {
    const quick = event.target.closest('[data-quick-add]');
    if (quick) {
      const item = store.getProduct(quick.dataset.quickAdd);
      const mode = quick.dataset.mode || 'once';
      if (item && store.add(item.id, 1, mode)) openMini(item, mode, 1, '');
      return;
    }
    if (event.target.closest('#show-more') && listing) { listing.limit += 12; renderListingCards(); return; }
    const mode = event.target.closest('[data-purchase-mode]');
    if (mode) {
      productMode = mode.dataset.purchaseMode;
      page.querySelectorAll('[data-purchase-mode]').forEach(button => { button.classList.toggle('active', button.dataset.purchaseMode === productMode); button.setAttribute('aria-pressed', String(button.dataset.purchaseMode === productMode)); });
      document.getElementById('frequency-row').hidden = productMode !== 'subscribe';
      return;
    }
    const gallery = event.target.closest('[data-gallery]');
    if (gallery && product) {
      const image = product.images[Number(gallery.dataset.gallery)];
      if (image) { const main = document.getElementById('gallery-main'); main.src = image.src; main.alt = image.alt || product.name; page.querySelectorAll('[data-gallery]').forEach(button => button.classList.toggle('active', button === gallery)); }
      return;
    }
    const qty = event.target.closest('[data-cart-qty]');
    if (qty) { const index = Number(qty.dataset.cartQty); store.setQty(index, store.items[index].qty + Number(qty.dataset.change)); renderCart(); return; }
    const remove = event.target.closest('[data-cart-remove]');
    if (remove) { store.remove(remove.dataset.cartRemove); renderCart(); return; }
    if (event.target.closest('[data-convert-coffee]')) { store.convertCoffee(); renderCart(); announce('Vos cafés sont présentés en abonnement dans cette maquette.'); }
  });
  page.addEventListener('change', event => {
    if (event.target.matches('[data-facet]') && listing) { event.target.checked ? listing.facets.add(event.target.dataset.facet) : listing.facets.delete(event.target.dataset.facet); listing.limit = 12; renderListingCards(); }
    if (event.target.id === 'catalog-sort' && listing) { listing.sort = event.target.value; renderListingCards(); }
    if (event.target.id === 'cart-frequency') { store.setFrequency(event.target.value); renderCart(); }
  });
  page.addEventListener('input', event => {
    if (event.target.id === 'catalog-search' && listing) { listing.query = event.target.value.trim(); listing.limit = 12; renderListingCards(); }
  });
  page.addEventListener('submit', event => {
    if (event.target.id !== 'product-form' || !product) return;
    event.preventDefault();
    const form = event.target;
    const error = document.getElementById('product-error');
    error.hidden = true;
    const qty = Number(document.getElementById('product-qty').value);
    const selected = [...form.querySelectorAll('.variant-select')];
    if (selected.some(input => !input.value)) { error.textContent = 'Choisissez une option avant d’ajouter ce produit.'; error.hidden = false; return; }
    const option = selected.map(input => `${input.labels[0]?.textContent}: ${input.value}`).join(' · ');
    if (productMode === 'subscribe') store.setFrequency(document.getElementById('product-frequency').value);
    if (store.add(product.id, qty, productMode, option)) openMini(product, productMode, qty, option);
    else { error.textContent = 'Ce produit ne peut pas être ajouté dans ce mode.'; error.hidden = false; }
  });
})();


