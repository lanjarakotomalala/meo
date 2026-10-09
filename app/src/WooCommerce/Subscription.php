<?php

namespace MyApp\WooCommerce;

use WCS_ATT_Cart;
use WCS_ATT_Product;
use WPEmerge\ServiceProviders\ServiceProviderInterface;

class Subscription implements ServiceProviderInterface
{
    public function register($container)
    {
        // Nothing to register.
    }

    public function bootstrap($container)
    {
        add_filter('woocommerce_cart_subscription_string_details', [$this, 'updateRecurringTotalPrice'], 10, 2);
        add_filter('woocommerce_cart_subtotal', [$this, 'updateCartSubtotal'], 10, 3);
        add_filter('woocommerce_cart_total', [$this, 'updateCartTotal'], 10);
        add_filter('woocommerce_before_calculate_totals', [$this, 'updateCartItems']);
        add_filter('woocommerce_is_subscription', [$this, 'isSub'], 9999, 3);
        add_filter('woocommerce_cart_item_subtotal', [$this, 'subItemCartPrice'], 99, 3);
        add_filter('woocommerce_bundle_price_data', [$this, 'subPriceDetailsCartSingleSub'], 10, 2);
        add_action('woocommerce_cart_loaded_from_session', [$this, 'manageSub']);
        add_filter('woocommerce_add_to_cart_validation', [$this, 'minBuySubPlanSinglePage'], 10, 3);
        add_filter('woocommerce_update_cart_validation', [$this, 'validateCartUpdate'], 10, 4);
        add_action('woocommerce_check_cart_items', [$this, 'checkCartItemsMinimumAmount']);
    }

    public function updateRecurringTotalPrice($array, $cart)
    {
        if (! isset($cart->recurring_cart_key)) {
            return $array;
        }

        $subtotal = 0;
        $update_subtotal = false;

        foreach ($cart->get_cart() as $product) {
            $sub_datas = get_post_meta($product['product_id'], '_wcsatt_schemes', true);
            if ($sub_datas) {
                $price = floatval($sub_datas[0]['subscription_pricing_method'] === 'inherit' ? $product['data']->bundled_cart_item->get_product()->get_price() : (float) $sub_datas[0]['subscription_price']);
                $discount = empty($product['data']->bundled_cart_item) ? null : $product['data']->bundled_cart_item->get_discount();

                if ($discount && $sub_datas) {
                    $price = round($price * (1 - ($discount / 100)), 2);
                }
                $subtotal += $price * $product['quantity'];
                $update_subtotal = true;
            }
        }

        if ($update_subtotal) {
            $array['recurring_amount'] = wc_price($subtotal);
        }

        return $array;
    }

    public function updateCartSubtotal($subtotal_html, $c, $cart)
    {
        if (empty($cart->recurring_carts)) {
            return $subtotal_html;
        }

        $subtotal = $this->updateCart($cart);

        if (! $subtotal) {
            return $subtotal_html;
        }

        return wc_price($subtotal);
    }

    public function updateCartTotal($total)
    {
        $cart = WC()->cart;

        if (empty($cart->recurring_carts)) {
            return $total;
        }

        $new_total = $this->updateCart($cart) + $cart->get_shipping_total();

        if (! $new_total) {
            return $total;
        }

        return wc_price($new_total);
    }

    public function updateCartItems($cart)
    {
        foreach ($cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];

            if ($product->get_type() === 'bundle' || isset($product->bundled_cart_item)) {
                if (!($sub_datas = get_post_meta($product->get_id(), '_wcsatt_schemes', true))) {
                    continue;
                }

                $price = $sub_datas[0]['subscription_price'];
                $product->set_price($price);
            }
        }

        return $cart;
    }

    public function isSub($is_subscription, $product_id, $product)
    {
        // A product with optional plans can be bought once or as a subscription.
        // Keep native subscription products recurring even without WCSATT cart data.
        if ((is_cart() || is_checkout()) && ! empty(WC()->cart->cart_contents) && ! wcs_cart_contains_renewal()) {
            $found_in_cart = false;
            foreach (WC()->cart->cart_contents as $cart_item) {
                if (isset($cart_item['product_id']) && (int) $cart_item['product_id'] === (int) $product_id) {
                    $found_in_cart = true;
                    if (($product instanceof \WC_Product && $product->is_type(['subscription', 'subscription_variation']))
                        || !empty($cart_item['wcsatt_data']['active_subscription_scheme'])) {
                        return true;
                    }
                }
            }
            if ($found_in_cart) {
                return false;
            }
        }

        if (get_post_meta($product_id, '_wcsatt_schemes', true)) {
            return true;
        }

        return $is_subscription;
    }

    public function subItemCartPrice($price, $cart_item, $cart_item_key)
    {
        if ($cart_item['data']->get_type() === 'bundle' || isset($cart_item['data']->bundled_cart_item)) {
            if (!($sub_datas = get_post_meta($cart_item['product_id'], '_wcsatt_schemes', true))) {
                if (isset($cart_item['bundled_by'])) {
                    $price = $cart_item['data']->bundled_item_price;
                    return wc_price($price);
                }

                return $price;
            }

            $price = (float)$sub_datas[0]['subscription_price'] * $cart_item['quantity'];
            $discount = isset($cart_item['data']->bundled_cart_item) ? $cart_item['data']->bundled_cart_item->get_discount() : null;

            if ($discount) {
                $price = round($price * (1 - ($discount / 100)), 2);
            }

            return wc_price($price);
        }

        return $price;
    }

    public function subPriceDetailsCartSingleSub($data, $bundle)
    {

        $bundled_items = $bundle->get_bundled_items();

        if (empty($bundled_items)) {
            return $data;
        }

        foreach ($bundled_items as $bundled_item) {
            if (!($sub_datas = get_post_meta($bundled_item->product->id, '_wcsatt_schemes', true))) {
                continue;
            }

            if ($bundled_item->is_priced_individually()) {
                $bundle_product = $bundled_item->get_product();
                $subscription_price = floatval($bundle_product->get_price());
                $subscription_regular_price = floatval($bundle_product->get_regular_price());
                $discount = null;

                if ($sub_datas[0]['subscription_pricing_method'] === 'override') {
                    $subscription_price = floatval($sub_datas[0]['subscription_price']);
                    $subscription_regular_price = floatval($sub_datas[0]['subscription_regular_price']);
                } else {
                    if ($sub_datas[0]['subscription_discount'] !== '') {
                        $discount = floatval($sub_datas[0]['subscription_discount']);
                    } elseif ($bundled_item->get_discount() !== '') {
                        $discount = $bundled_item->get_discount();
                    }
                }

                if ($discount) {
                    $subscription_price = round($subscription_price * (1 - ($discount / 100)), 2);
                    $subscription_regular_price = round($subscription_price * (1 - ($discount / 100)), 2);
                }

                $data['prices'][$bundled_item->get_id()] = 0;
                $data['regular_prices'][$bundled_item->get_id()] = 0;
                $data['recurring_prices'][$bundled_item->get_id()] = $subscription_price;
                $data['regular_recurring_prices'][$bundled_item->get_id()] = $subscription_regular_price;
                $data['recurring_keys'][$bundled_item->get_id()] = sprintf('%sly', $sub_datas[0]['subscription_period']);
                $data['recurring_html'][$bundled_item->get_id()] = \WC_PB_Product_Prices::get_recurring_price_html_component($bundled_item->product);
                $data['price_string_recurring'] = '<span class="bundled_subscriptions_price_html">%r</span>';
                $data['price_string_recurring_up_front'] = sprintf(_x('%1$s<span class="bundled_subscriptions_price_html"> une fois%2$s</span>', 'subscription price html', 'woocommerce-product-bundles'), '%s', '%r');
            }
        }

        return $data;
    }

    public function manageSub()
    {
        $cart = WC()->cart;

        if (empty($cart->cart_contents)) {
            return;
        }

        remove_action('woocommerce_check_cart_items', [WCS_ATT_Cart::class, 'check_applied_subscription_schemes']);

        foreach ($cart->cart_contents as $cart_item_key => $cart_item) {
            if (!isset($cart_item['bundled_by'])) {
                continue;
            }

            $sub_scheme = get_post_meta($cart_item['product_id'], '_wcsatt_schemes', true);

            if (!$sub_scheme) {
                continue;
            }

            $scheme_to_apply = sprintf('%s_%s', $sub_scheme[0]['subscription_period_interval'], $sub_scheme[0]['subscription_period']);

            if (!$scheme_to_apply) {
                continue;
            }

            $cart->cart_contents[$cart_item_key]['wcsatt_data']['active_subscription_scheme'] = !empty($scheme_to_apply) ? $scheme_to_apply : false;

            WCS_ATT_Product::set_runtime_meta($cart->cart_contents[$cart_item_key]['data'], 'active_subscription_scheme_key', $scheme_to_apply);
            WCS_ATT_Product::set_runtime_meta($cart->cart_contents[$cart_item_key]['data'], 'subscription_period', $sub_scheme[0]['subscription_period']);
            WCS_ATT_Product::set_runtime_meta($cart->cart_contents[$cart_item_key]['data'], 'subscription_period_interval', $sub_scheme[0]['subscription_period_interval']);
            WCS_ATT_Product::set_runtime_meta($cart->cart_contents[$cart_item_key]['data'], 'subscription_length', $sub_scheme[0]['subscription_length']);
        }
    }

    private function updateCart($cart)
    {
        $total = false;

        foreach ($cart->get_cart() as $product) {
            $update = false;
            $discount = false;
            $sub_datas = null;

            if (
                $product['data']->get_type() === 'bundle'
                || isset($product['data']->bundled_cart_item)
                || ($sub_datas = get_post_meta($product['product_id'], '_wcsatt_schemes', true))
            ) {
                if (isset($product['data']->bundled_cart_item)) {
                    $discount = $product['data']->bundled_cart_item->get_discount();
                }

                if ($sub_datas) {
                    $price = $sub_datas[0]['subscription_pricing_method'] === 'inherit' ? $product['data']->get_price() : (float) $sub_datas[0]['subscription_price'];
                    $update = true;
                } else {
                    if ($product['data'] === 'subscription') {
                        $price = (float) $product['data']->get_price();
                        $update = true;
                    } else {
                        $price = (float) $product['data']->bundled_item_price;
                    }
                }

                if ($price === 0.0 && $product['data']->get_type() !== 'bundle') {
                    $price = (float) $product['data']->get_price();
                }

                if (isset($discount) && is_numeric($discount) && $discount !== '' && $update) {
                    $price = round($price * (1 - ($discount / 100)), 2);
                }
            } else {
                $price = (float) $product['data']->get_price();
            }

            $total = (float) $total + ($price * $product['quantity']);
        }

        return $total;
    }

    public function minBuySubPlanSinglePage(bool $passed, int $product_id, int $quantity): bool
    {
        $product = wc_get_product($product_id);

        if ($product->get_type() === 'bundle') {
            return $passed;
        }

        $sub_datas = get_post_meta($product_id, '_wcsatt_schemes', true);
        $is_sub_plan = $sub_datas && isset($_POST['convert_to_sub_'.$product_id]) && $_POST['convert_to_sub_'.$product_id] !== '0';

        // Si le produit a un plan d'abonnement ou est un produit de type abonnement
        if ($is_sub_plan || $product->get_type() === 'subscription') {
            $price = $product->get_price();

            if ($is_sub_plan) { // si plan d'abonnement sur un produit simple
                if ($sub_datas[0]['subscription_pricing_method'] === 'override') { // si le produit de l'abonnement n'est pas celui du produit simple
                    $price = $sub_datas[0]['subscription_price'];
                } else { // Si on garde le proix de l'abonnement
                    if (! empty($sub_datas[0]['subscription_discount'])) { // si on ajoute une réduction au prix de l'abonnement
                        $price = $price - ($price * ($sub_datas[0]['subscription_discount'] / 100));
                    }
                }

                $total = floatval($price) * $quantity;
            } else { // Si produit de type abonnement
                $total = $price * $quantity;
            }

            if ($total < 20) { // si le montant total de l'abonnement est < 20, on affiche une notice et le produit n'est pas ajouté au panier
                wc_add_notice(sprintf(
                    __('Le montant minimum d\'achat pour cet abonnement est de 20€. Votre sélection actuelle est de %s€.', 'woocommerce'),
                    number_format($total, 2)
                ), 'error');
                return false;
            }
        }

        return $passed;
    }

    public function validateCartUpdate($passed, $cart_item_key, $values, $quantity)
    {
        $product = $values['data'];

        $is_subscription = $product->is_type(['subscription', 'subscription_variation']);
        $is_wcsatt = isset($values['wcsatt_data']['active_subscription_scheme']) && $values['wcsatt_data']['active_subscription_scheme'] !== false;

        if ($is_subscription || $is_wcsatt) {
            $price = $product->get_price();
            $total = $price * $quantity;

            if ($total < 20) {
                wc_add_notice(
                    sprintf(
                        __('Le produit d’abonnement "%s" nécessite un montant minimum de 20€, actuellement %s€.', 'woocommerce'),
                        $product->get_name(),
                        number_format($total, 2, ',', '')
                    ),
                    'error'
                );
                return false;
            }
        }

        return $passed;
    }

    public function checkCartItemsMinimumAmount()
    {
        $sub_total = 0;
        $has_subscription = false;

        foreach (WC()->cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            $quantity = $cart_item['quantity'];

            $is_subscription = $product->is_type(['subscription', 'subscription_variation']);
            $is_wcsatt = isset($cart_item['wcsatt_data']['active_subscription_scheme']) && $cart_item['wcsatt_data']['active_subscription_scheme'] !== false;

            if ($is_subscription || $is_wcsatt) {
                $has_subscription = true;
                $price = $product->get_price();
                $sub_total += $price * $quantity;
            }
        }

        if ($has_subscription) {
            if ($sub_total < 20) {
                wc_add_notice(
                    sprintf(
                        __('Les produits d’abonnement nécessitent un montant minimum de 20€, actuellement %s€.', 'woocommerce'),
                        number_format($sub_total, 2, ',', '')
                    ),
                    'error'
                );
            }
        }
    }
}
