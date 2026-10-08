export default function product() {
  document.addEventListener('DOMContentLoaded', () => {

    // --- Single product add to cart ---
    if (document.body.classList.contains('single-product')) {
      const addToCartBtn = document.querySelector('.single_add_to_cart_button');
      if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
          const wooNoticesError = document.querySelector('.woocommerce-error');
          if (wooNoticesError) {
            wooNoticesError.remove();
          }
          window.scrollTo(500, 0);
        });
      }
    }

    // --- Upsells add to cart (obfuscation) ---
    const upsellButtons = document.querySelectorAll('#carusel_poduct_sells .add_to_cart_button');
    if (upsellButtons.length) {
      upsellButtons.forEach(btn => {
        btn.setAttribute('rel', 'nofollow noopener'); // pour Google

      });
    }

  });
}
