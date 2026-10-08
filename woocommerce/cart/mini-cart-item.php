<li id="product-<?php echo $product_id; ?>" class="woocommerce-mini-cart-item <?php echo esc_attr(apply_filters('woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key)); ?>">
    <div class="mini-cart-item-inner-left">
        <div>
            <?php echo $thumbnail ?>
        </div>
        <div>
            <?php if (empty($product_permalink)) : ?>
                <a href="<?php echo esc_url($product_permalink); ?>">
            <?php endif; ?>

            <?php echo wp_kses_post($product_name) . wc_get_formatted_cart_item_data($cart_item) ?>
                    <div class="price"><?= $product_price ?> x <span class="item-quantity"><?= $cart_item['quantity'] ?></span></div>
                    <div class="item-subtotal"><?= WC()->cart->get_product_subtotal($_product, $cart_item['quantity']) ?></div>
                    <?php if (empty($product_permalink)) : ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="mini-cart-item-inner-right">
            <?php
            $product_price = apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($cart_item['data']), $cart_item, $cart_item_key);
            echo woocommerce_quantity_input(
                array(
                    'input_name' => "{$cart_item_key}",
                    'input_value' => $cart_item['quantity'],
                    'max_value' => $_product->get_max_purchase_quantity(),
                    'min_value' => '0',
                    'product_name' => $_product->get_name(),
                    ),
                $cart_item['data'],
                false
            );
            ?>

        <?php
        echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            'woocommerce_cart_item_remove_link',
            sprintf(
                '<a href="%s" class="remove remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s">Supprimer</a>',
                esc_url(wc_get_cart_remove_url($cart_item_key)),
                /* translators: %s is the product name */
                esc_attr(sprintf(__('Remove test %s from cart', 'woocommerce'), wp_strip_all_tags($product_name))),
                esc_attr($product_id),
                esc_attr($cart_item_key),
                esc_attr($_product->get_sku())
            ),
            $cart_item_key
        );
        ?>
    </div>
</li>
