/* Shared, local-only state for the visual preview. No WordPress cart is touched. */
(() => {
  const KEY = 'meo-cro-preview-cart-v2';
  const NUDGE_COUNT = 2;
  const money = value => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value || 0);
  const escape = value => String(value ?? '').replace(/[&<>"']/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[character]);
  const cleanPath = value => {
    const path = String(value || '');
    return path.startsWith('/') && !path.startsWith('//') ? path : '/catalogue/';
  };
  let saved = {};
  try { saved = JSON.parse(localStorage.getItem(KEY) || '{}'); } catch { saved = {}; }
  const store = {
    products: [],
    categories: [],
    snapshot: '',
    items: Array.isArray(saved.items) ? saved.items : [],
    frequency: ['2 semaines', '4 semaines', '6 semaines', '8 semaines'].includes(saved.frequency) ? saved.frequency : '4 semaines',
    NUDGE_COUNT,
    money,
    escape,
    cleanPath,
    ready: null,
    getProduct(id) { return this.products.find(product => product.id === String(id)); },
    getCategory(id) { return this.categories.find(category => category.id === Number(id)); },
    isCoffee(product) { return Boolean(product?.categoryIds?.includes(1246)); },
    canSubscribe(product) { return this.isCoffee(product); },
    count() { return this.items.reduce((sum, item) => sum + item.qty, 0); },
    total(mode) {
      return this.items.reduce((sum, item) => {
        if (mode && item.mode !== mode) return sum;
        return sum + (this.getProduct(item.id)?.price || 0) * item.qty;
      }, 0);
    },
    nudgeCount() {
      return this.items.reduce((sum, item) => sum + (item.mode === 'once' && this.isCoffee(this.getProduct(item.id)) ? item.qty : 0), 0);
    },
    save() {
      try { localStorage.setItem(KEY, JSON.stringify({ items: this.items, frequency: this.frequency })); } catch { /* Private browsing can prevent writes. */ }
      document.dispatchEvent(new CustomEvent('meo:cart-change'));
    },
    add(id, qty = 1, mode = 'once', option = '') {
      const product = this.getProduct(id);
      if (!product || !product.purchasable || !product.inStock) return false;
      if (mode === 'subscribe' && !this.canSubscribe(product)) return false;
      const safeQty = Math.min(99, Math.max(1, Math.floor(Number(qty) || 1)));
      const safeOption = String(option || '').slice(0, 120);
      if (product.type === 'variable' && !safeOption) return false;
      const existing = this.items.find(item => item.id === product.id && item.mode === mode && item.option === safeOption);
      if (existing) existing.qty = Math.min(99, existing.qty + safeQty);
      else this.items.push({ id: product.id, qty: safeQty, mode, option: safeOption });
      this.save();
      return true;
    },
    setQty(index, qty) {
      const item = this.items[Number(index)];
      if (!item) return;
      const next = Math.min(99, Math.max(0, Math.floor(Number(qty) || 0)));
      if (next) item.qty = next;
      else this.items.splice(Number(index), 1);
      this.save();
    },
    remove(index) {
      if (this.items[Number(index)]) this.items.splice(Number(index), 1);
      this.save();
    },
    convertCoffee() {
      this.items.forEach(item => {
        if (this.isCoffee(this.getProduct(item.id))) item.mode = 'subscribe';
      });
      const merged = [];
      this.items.forEach(item => {
        const existing = merged.find(other => other.id === item.id && other.mode === item.mode && other.option === item.option);
        if (existing) existing.qty += item.qty;
        else merged.push(item);
      });
      this.items = merged;
      this.save();
    },
    setFrequency(value) {
      if (['2 semaines', '4 semaines', '6 semaines', '8 semaines'].includes(value)) {
        this.frequency = value;
        this.save();
      }
    },
  };
  store.ready = fetch('/data/catalog.json')
    .then(response => { if (!response.ok) throw new Error('Catalogue indisponible'); return response.json(); })
    .then(data => {
      store.products = data.products;
      store.categories = data.categories;
      store.snapshot = data.snapshot;
      store.items = store.items.filter(item => store.getProduct(item.id) && Number.isInteger(item.qty) && item.qty > 0 && ['once', 'subscribe'].includes(item.mode));
      store.save();
      return store;
    });
  window.MeoStore = store;
})();
