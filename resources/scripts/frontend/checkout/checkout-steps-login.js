import { CheckoutSteps } from './checkout-steps';

export class CheckoutStepsLogin extends CheckoutSteps {
    constructor()
    {
        super();
    }

    initializeLoginActions()
    {
        if (! document.querySelector('.woocommerce-checkout')) {
            return;
        }


        let checkoutLogin = document.getElementById('checkout-login');
        let checkoutOpSteps = document.getElementById('checkout-op-steps');
        let firstStep = checkoutOpSteps.querySelector('.step');
        checkoutOpSteps.insertBefore(checkoutLogin, firstStep);

        const toggleLoginForm = document.querySelector('.step-content.step-login .toggle-login-form');

        if (!toggleLoginForm) {
            return;
        }

        const createAccount = toggleLoginForm.querySelector('.toggle-login-form .create-account');
        const loginAccount = toggleLoginForm.querySelector('.toggle-login-form .login-account');


        if (!createAccount || !loginAccount) {
            return;
        }

        createAccount.addEventListener('click', () => {
            toggleLoginForm.classList.add('showRegisterForm');
            toggleLoginForm.classList.remove('showLoginForm');
        });

        loginAccount.addEventListener('click', () => {
            toggleLoginForm.classList.add('showLoginForm');
            toggleLoginForm.classList.remove('showRegisterForm');
        })
    }
}

const checkoutStepsLogin = new CheckoutStepsLogin();
checkoutStepsLogin.initializeLoginActions();
