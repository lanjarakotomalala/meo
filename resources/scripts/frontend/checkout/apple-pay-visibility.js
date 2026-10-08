/**
 * Hide Apple Pay payment option on browsers/devices that don't support it.
 *
 * PayPlug enables its Apple Pay gateway unconditionally on the server side,
 * relying on the <apple-pay-button> web component to be self-hiding on
 * non-Safari browsers. However the WooCommerce payment method row (radio +
 * label) is still rendered for everyone.
 *
 * This module uses the native ApplePaySession browser API — the only reliable
 * way to detect actual Apple Pay support — and removes the payment option row
 * when the API is absent or reports no active card.
 */

const PAYMENT_ROW_SELECTOR = 'li.wc_payment_method.payment_method_apple_pay';
const CART_BUTTON_SELECTOR = '#apple-pay-button-wrapper';

function isApplePayAvailable() {
    return (
        typeof window.ApplePaySession !== 'undefined' &&
        ApplePaySession.canMakePayments()
    );
}

function hideApplePayElements() {
    if (isApplePayAvailable()) {
        return;
    }

    document.querySelectorAll(PAYMENT_ROW_SELECTOR).forEach((el) => el.remove());
    document.querySelectorAll(CART_BUTTON_SELECTOR).forEach((el) => el.remove());
}

// Run on initial load.
hideApplePayElements();

// Re-run after WooCommerce refreshes the payment methods via AJAX
// (e.g. when the shipping method changes on the checkout page).
jQuery(document.body).on('updated_checkout', hideApplePayElements);
