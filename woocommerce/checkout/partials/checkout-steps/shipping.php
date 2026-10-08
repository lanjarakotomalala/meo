<div class="head">
    <div class="title"><span>3</span> Méthodes de livraison</div>
    <div class="toggle">
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/resources/images/icon-chevron-checkout.svg" alt="Flèche toggle" width="24" height="10">
    </div>
</div>

<div>
    <div class="loggedUser shippingInfo" style="display: none;">
        <p></p>
        <a href="">Modifier</a>
    </div>
</div>

<div class="step-content woocommerce-review-order-shipping">
    <?php do_action('woocommerce_checkout_shipping'); ?>
    <div class="shipping-methods-wrapper">
        <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
            <?php do_action('woocommerce_review_order_before_shipping'); ?>
            <?php wc_cart_totals_shipping_html(); ?>
            <?php do_action('woocommerce_review_order_after_shipping'); ?>
        <?php endif; ?>
    </div>

    <div class="next-step" data-current-step="3">Continuer</div>
</div>
