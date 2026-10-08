export class CheckoutMobile {
    constructor() {
        this.shippingCostDiv = document.querySelector('.recapShipping > .cost');
        this.shippingMethods = document.querySelectorAll('ul#shipping_method > li > input');
        this.totalCostDiv = document.querySelector('.recapTotal > .cost');
        this.priceElement = document.querySelector('.woocommerce-Price-amount > bdi');
    }

    initialize() {
        this.updateShippingCost();
        this.updateTotalCost();
        this.addEventListeners();
    }

    updateShippingCost() {
        const selectedInput = document.querySelector('ul#shipping_method > li > input:checked');
        if (selectedInput) {
            const label = selectedInput.nextElementSibling;
            if (label && this.shippingCostDiv) {
                const truncatedText = label.textContent.split(' ').slice(0, 3).join(' ') + '...';
                this.shippingCostDiv.textContent = truncatedText;
            }
        }
    }

    updateTotalCost() {
        if (this.priceElement && this.totalCostDiv) {
            const priceText = this.priceElement.textContent;
            this.totalCostDiv.textContent = priceText;
        }
    }

    addEventListeners() {
        this.shippingMethods.forEach(input => {
            input.addEventListener('change', () => this.updateShippingCost());
        });

        // Ajout d'un écouteur pour les changements de prix
        const observer = new MutationObserver(() => {
            this.updateTotalCost();
        });

        // Observer les changements dans le contenu du prix
        if (this.priceElement) {
            observer.observe(this.priceElement, { childList: true, subtree: true });
        }
    }
}

const checkoutMobile = new CheckoutMobile();
checkoutMobile.initialize();
