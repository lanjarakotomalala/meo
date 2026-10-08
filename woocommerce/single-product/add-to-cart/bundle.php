<?php
/**
 * Product Bundle single-product template
 *
 * Override this template by copying it to 'yourtheme/woocommerce/single-product/add-to-cart/bundle.php'.
 *
 * On occasion, this template file may need to be updated and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility.
 * We try to do this as little as possible, but it does happen.
 * When this occurs the version of the template file will be bumped and the readme will list any important changes.
 *
 * @version 5.5.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/** WC Core action. */
do_action('woocommerce_before_add_to_cart_form'); ?>

<form method="post" enctype="multipart/form-data"
      class="cart cart_group bundle_form <?php echo esc_attr($classes); ?>"><?php

    /**
     * 'woocommerce_before_bundled_items' action.
     *
     * @param WC_Product_Bundle $product
     */
    do_action('woocommerce_before_bundled_items', $product);

    do_action('meo_subscription_steps', $product);

    /**
     * 'woocommerce_after_bundled_items' action.
     *
     * @param WC_Product_Bundle $product
     */
    do_action('woocommerce_after_bundled_items', $product);

    /**
     * 'woocommerce_bundles_add_to_cart_wrap' action.
     *
     * @param WC_Product_Bundle $product
     * @since  5.5.0
     *
     */
    do_action('woocommerce_bundles_add_to_cart_wrap', $product);

    ?></form><?php
/** WC Core action. */
do_action('woocommerce_after_add_to_cart_form');
?>
