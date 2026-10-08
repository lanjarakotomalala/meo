<div id="checkout-login" class="step" <?php if (!$isUserLoggedIn): ?> data-step="login" data-next-allowed="1" data-toggle="<?= intval(!$isUserLoggedIn); ?>" <?php endif; ?>>
    <?php include(locate_template('woocommerce/checkout/partials/checkout-steps/login.php')); ?>
</div>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">
    <div id="checkout-op-steps">

        <div class="recap">
            <a href="<?= wc_get_cart_url(); ?>">
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/resources/images/arrow-left.svg" alt="Flèche toggle" width="10" height="6">
                <span>Retourner au panier</span>
            </a>
            <div>
                <div class="recapShipping">
                    <span class="label">Expédition</span>
                    <span class="cost">--</span>
                </div>
                <div class="recapTotal">
                    <span class="label">Total :</span>
                    <span class="cost">00,00 €</span>
                </div>
            </div>
        </div>

        <div class="step" data-step="billing" data-next-allowed="<?= intval($isUserLoggedIn); ?>" data-toggle="<?= intval($isUserLoggedIn); ?>">
            <?php include(locate_template('woocommerce/checkout/partials/checkout-steps/billing.php')); ?>
        </div>
        <div class="step" data-step="shipping" data-next-allowed="<?= intval($isUserLoggedIn); ?>" data-toggle="0">
            <?php include(locate_template('woocommerce/checkout/partials/checkout-steps/shipping.php')); ?>
        </div>
        <div class="step" data-step="payment" data-next-allowed="<?= intval($isUserLoggedIn); ?>" data-toggle="0">
            <?php include(locate_template('woocommerce/checkout/partials/checkout-steps/payment.php')); ?>
        </div>
    </div>
</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
