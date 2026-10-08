<?php

namespace MyApp\WooCommerce;

use WPEmerge\ServiceProviders\ServiceProviderInterface;

class Bundle implements ServiceProviderInterface
{
    public function register($container)
    {
        // Nothing to register.
    }

    public function bootstrap($container)
    {
        add_filter('woocommerce_get_availability_text', '__return_empty_string', 10, 2);
        add_filter('woocommerce_bundle_is_editable_in_cart', '__return_false');
    }
}
