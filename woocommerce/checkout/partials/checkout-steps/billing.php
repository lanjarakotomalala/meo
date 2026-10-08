<div class="head">
    <div class="title"><span>2</span> Informations</div>
    <div class="toggle">
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/resources/images/icon-chevron-checkout.svg" alt="Flèche toggle" width="24" height="10">
    </div>
</div>

<div>
    <div class="loggedUser addressShortInfo" style="display: none;">
        <p></p>
        <a href="">Modifier</a>
    </div>
</div>

<div class="step-content">
    <?php do_action('woocommerce_checkout_billing'); ?>

    <div class="next-step" data-current-step="2">Continuer</div>
</div>
