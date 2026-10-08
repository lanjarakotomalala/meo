<?php

namespace MyApp\WooCommerce;

use WPEmerge\ServiceProviders\ServiceProviderInterface;

class MiniCart implements ServiceProviderInterface
{
    public function register($container)
    {
        // Nothing to register.
    }
    public function bootstrap($container)
    {
        add_filter('woocommerce_mini_cart_item_class', [$this, 'isSub'], 10, 2);
    }

    public function isSub($classes, $cart_item)
    {
        if ($cart_item['wcsatt_data']['active_subscription_scheme'] || $cart_item['data'] instanceof \WC_Product_Subscription) {
            $classes .= ' is-subscription';
        }

        return $classes;
    }
}
