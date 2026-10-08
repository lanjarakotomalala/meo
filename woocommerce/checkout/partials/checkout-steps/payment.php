<div class="head">
    <div class="title"><span>4</span> Paiement</div>
    <div class="toggle">
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/resources/images/icon-chevron-checkout.svg" alt="Flèche toggle" width="24" height="10">
    </div>
</div>

<div class="step-content">
    <div style="display: flex;"></div>
    <?php woocommerce_checkout_payment(); ?>
</div>
