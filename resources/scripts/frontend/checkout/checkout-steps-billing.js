import { CheckoutSteps } from './checkout-steps';

export class CheckoutStepsBilling extends CheckoutSteps {
    constructor()
    {
        super();
    }

    initialize()
    {
        const inputs = document.querySelectorAll('.step-content input');

        if (!inputs) {
            return;
        }

        inputs.forEach(input => {
            input.addEventListener('focus', (event) => {
                event.target.classList.remove('error-empty-field');
            });

            input.addEventListener('blur', (event) => {
                if (!event.target.value.trim()) {
                    event.target.classList.add('error-empty-field');
                }
            });
        });
    }
}

const checkoutStepsBilling = new CheckoutStepsBilling();
checkoutStepsBilling.initialize();