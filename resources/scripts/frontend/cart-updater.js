class DynamicQuantity {
    constructor()
    {
        this.ajaxObject = {
            nonce: ajax_data.nonce,
            ajaxUrl: ajax_data.ajax_url,
            isCart: ajax_data.is_cart,
        };
        this.miniCart = {
            total: 'p.woocommerce-mini-cart__total.total .amount',
            item: '.woocommerce-mini-cart-item',
            qtyButton: 'dynamic-qty-btn',
        };
        this.debounceTimers = {};
        this.inFlightKeys = new Set();
        this.pendingUpdates = {};
        this.savedScrollY = null;
        this.fallbackTimer = null;
    }

    initialize()
    {
        $(document).ready(() => {
            if (this.ajaxObject.isCart === '1') {
                this.classicCartQtyUpdate();
            } else {
                this.bindEvents();
            }
        });
    }

    bindEvents()
    {
        $('body').on('click', `.${this.miniCart.qtyButton}`, (event) => {
            this.miniCartQtyUpdate($(event.currentTarget));
        });
        $('body').on('change', '.qty', (event) => {
            this.miniCartQtyUpdate($(event.currentTarget));
        });
    }

    classicCartQtyUpdate()
    {
      this.initScrollLock();

      let submitTimer = null;
        $('body').on('change', '.qty', (event) => {
            event.preventDefault();
            this.lockScroll();
            const input = $(event.currentTarget);
            const row = input.closest('tr[data-cart_item_key]');

            if (row.length) {
              const key = row.data('cart_item_key');
              const number = parseInt(input.val(), 10) || 1;

              $.post(this.ajaxObject.ajaxUrl, {
                action: 'dynamic_qty_update',
                key,
                number,
                product_id: 0,
                security: this.ajaxObject.nonce,
              }).done((res) => {
                if (!res.data.error && res.data.item_price) {
                  row.find('.item-quantity').text(number);
                  row.find('.item-subtotal').html(res.data.item_price);
                }
              });
            }

          clearTimeout(submitTimer);
            submitTimer = setTimeout(() => {
                const submit = $('button[name="update_cart"]');
                submit.removeAttr('disabled');
                submit.trigger('click');
            }, 400);
        });
    }

  initScrollLock()
  {
    const html = document.documentElement;

    window.addEventListener('scroll', () => {
      if (this.savedScrollY !== null) {
        html.style.scrollBehavior = 'auto';
        window.scrollTo(0, this.savedScrollY);
      }
    }, true);

    $(document).ajaxComplete((_, __, settings) => {
      if (this.savedScrollY !== null &&
        typeof settings.data === 'string' &&
        settings.data.includes('get_coupon_product_selection_html')) {
        setTimeout(() => this.unlockScroll(), 50);
      }
    });

    $(document.body).on('wc_fragments_refreshed', () => {
      if (this.savedScrollY !== null) {
        this.fallbackTimer = setTimeout(() => this.unlockScroll(), 700);
      }
    });
  }

  lockScroll()
  {
    this.savedScrollY = window.scrollY;
    clearTimeout(this.fallbackTimer);
  }

  unlockScroll()
  {
    const html = document.documentElement;
    clearTimeout(this.fallbackTimer);
    this.fallbackTimer = null;
    if (this.savedScrollY !== null) {
      html.style.scrollBehavior = 'auto';
      window.scrollTo(0, this.savedScrollY);
      this.savedScrollY = null;
    }
    requestAnimationFrame(() => { html.style.scrollBehavior = ''; });
  }

  miniCartQtyUpdate(el)
    {
        const errorWrapper = document.querySelector('.minicart-qty-error');
        if (errorWrapper) {
            errorWrapper.remove();
        }

        const wrap = el.closest(this.miniCart.item);
        if (!wrap.length) {
            return;
        }

        const input = wrap.find('.qty');
        const key = input.attr('name');
        if (!key) {
            return;
        }

        const maxQty = parseInt(input.attr('max'), 10) || 9999;
        const step = parseInt(input.attr('step'), 10) || 1;
        const current = parseInt(input.val(), 10) || 1;
        let number;

        if (el.hasClass(this.miniCart.qtyButton)) {
            number = el.data('type') === 'plus'
                ? Math.min(current + step, maxQty)
                : Math.max(current - step, 1);
        } else {
            number = Math.min(Math.max(current, 1), maxQty);
        }

        input.val(number).attr('value', number);
        wrap.find('.item-quantity').text(number);

      clearTimeout(this.debounceTimers[key]);

        if (this.inFlightKeys.has(key)) {
            this.pendingUpdates[key] = { number, el };
            return;
        }

        this.debounceTimers[key] = setTimeout(() => {
            delete this.debounceTimers[key];
            this.updateCart(key, number, el);
        }, 400);
    }

    displayStepError(wrapper, message)
    {
        const minicartQtyError = document.createElement('ul');
        minicartQtyError.classList.add('minicart-qty-error', 'pods-step-error', 'woocommerce-error');
        const minicartQtyText = document.createElement('li');
        minicartQtyText.textContent = message;
        minicartQtyError.appendChild(minicartQtyText);
        wrapper.prepend(minicartQtyError);
    }

    updateCart(key, number, el)
    {
        const cartItem = el.closest(this.miniCart.item);
        if (!cartItem.length) {
            return;
        }

        const data = {
            action: 'dynamic_qty_update',
            key,
            number,
            product_id: cartItem.hasClass('is-subscription') ? cartItem[0].id : 0,
            security: this.ajaxObject.nonce,
        };
        const input = cartItem.find('.qty');

        this.inFlightKeys.add(key);

        $.post(this.ajaxObject.ajaxUrl, data)
        .done((res) => {
            this.inFlightKeys.delete(key);

            const pending = this.pendingUpdates[key];
            if (pending) {
                delete this.pendingUpdates[key];
                this.updateCart(key, pending.number, pending.el);
                return;
            }

            if (res.data.error) {
                const wrapper = document.querySelector('.shoptimizer-mini-cart-wrap');
                const list = wrapper ? wrapper.querySelector('.cart_list') : null;
                if (list) {
                    this.displayStepError(list, 'Vous devez prendre pour un minimum de 20€ d\'abonnement');
                }
                input.val(res.data.qty).attr('value', res.data.qty);
                cartItem.find('.item-quantity').text(res.data.qty);
            } else {
                input.val(number).attr('value', number);
                cartItem.find('.item-quantity').text(number);
                if (res.data.item_price) {
                  cartItem.find('.item-subtotal').html(res.data.item_price);
                }
            }

            $(this.miniCart.total).html(res.data.total);
        })
        .fail((jqXHR, textStatus, errorThrown) => {
            this.inFlightKeys.delete(key);
            delete this.pendingUpdates[key];
            $(document.body).trigger('wc_fragment_refresh');
            console.error('Error updating cart quantity:', errorThrown);
        });
    }
}

const dynamicQuantity = new DynamicQuantity();
dynamicQuantity.initialize();
