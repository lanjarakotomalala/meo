<div id="content-cart">
    <div class="order-item-summary">
        <?php
        $is_bundle = false;
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
            $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
            $bundle_container_item = wc_pb_is_bundle_container_cart_item($cart_item);
            $bundle_item = wc_pb_get_bundled_cart_item_container($cart_item);

            $include_label = $is_bundle || null;
            $is_bundle = (bool)$bundle_container_item;
            ?>
            <?php if ($include_label) : ?>
                <div class="bundle-include-tag"><span><?php _e('Inclus:', 'meo'); ?></span></div>
            <?php endif; ?>
            <div
                class="cart-item<?php echo $bundle_item ? ' checkout-bundled-item' : ''; ?><?php echo $bundle_container_item ? ' checkout-bundle-item' : ''; ?>
                <?php echo isset($bundle_item['bundled_items']) && $cart_item['key'] === $bundle_item['bundled_items'][array_key_last($bundle_item['bundled_items'])] ? ' last-bundled-item' : ''; ?>">
                <?php if (!$bundle_item) : ?>
                    <div
                        class="thumbnail"> <?php echo apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key); ?> </div>
                <?php endif; ?>
                <div class="details">
                    <div>
                        <p class="title"><?php echo $_product->get_name(); ?></p>
                        <p class="price"><?php
                            if ($_product->get_type() === 'bundle') {
                                echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);
                            } else {
                                echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); // PHPCS: XSS ok.
                            }
                            ?></p>
                    </div>
                    <p class="quantity">x<?php echo $cart_item['quantity']; ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="order_review" class="woocommerce-checkout-review-order">
        <?php do_action('woocommerce_checkout_order_review'); ?>

        <div class="coupon coupon_checkout">
            <input type="text" name="coupon_code" class="input-text" id="coupon_code_checkout" value=""
                   placeholder="<?php esc_attr_e('Coupon code', 'woocommerce'); ?>"/>
            <div class="button" id="save_coupon_code"><?php esc_attr_e('Apply', 'woocommerce'); ?></div>
        </div>
    </div>

    <?php do_action('woocommerce_after_checkout_form', $checkout); ?>
</div>
