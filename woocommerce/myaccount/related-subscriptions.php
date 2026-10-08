<?php
/**
 * Related Subscriptions section beneath order details table
 *
 * @author   Prospress
 * @category WooCommerce Subscriptions/Templates
 * @version  7.3.0 - Migrated from WooCommerce Subscriptions v2.6.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<header>
    <h2><?php esc_html_e('Related subscriptions', 'woocommerce-subscriptions'); ?></h2>
</header>

<table
    class="shop_table my_account_orders woocommerce-orders-table woocommerce-MyAccount-subscriptions woocommerce-orders-table--subscriptions">
    <tbody>
    <?php foreach ($subscriptions as $subscription_id => $subscription) : ?>
        <tr class="order woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr($subscription->get_status()); ?>">
            <th class="subscription-id order-number woocommerce-orders-table__header woocommerce-orders-table__header-order-number woocommerce-orders-table__header-subscription-id">
                <span class="nobr"><?php esc_html_e('Subscription', 'woocommerce-subscriptions'); ?></span></th>
            <td class="subscription-id order-number woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-id woocommerce-orders-table__cell-order-number"
                data-title="<?php esc_attr_e('ID', 'woocommerce-subscriptions'); ?>">
                <?php // translators: placeholder is a subscription number. ?>
                <a href="<?php echo esc_url($subscription->get_view_order_url()); ?>"
                   aria-label="<?php echo esc_attr(sprintf(__('View subscription number %s', 'woocommerce-subscriptions'), $subscription->get_order_number())) ?>">
                    <span
                        class="td-value"><?php echo sprintf(esc_html_x('#%s', 'hash before order number', 'woocommerce-subscriptions'), esc_html($subscription->get_order_number())); ?></span>
                </a>
            </td>
        </tr>
        <tr class="order woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr($subscription->get_status()); ?>">

            <th class="subscription-status order-status woocommerce-orders-table__header woocommerce-orders-table__header-order-status woocommerce-orders-table__header-subscription-status">
                <span class="nobr"><?php esc_html_e('Status', 'woocommerce-subscriptions'); ?></span></th>
            <td class="subscription-status order-status woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-status woocommerce-orders-table__cell-order-status"
                style="white-space:nowrap;" data-title="<?php esc_attr_e('Status', 'woocommerce-subscriptions'); ?>">
                <span
                    class="td-value"><?php echo esc_html(wcs_get_subscription_status_name($subscription->get_status())); ?></span>
            </td>
        </tr>
        <tr class="order woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr($subscription->get_status()); ?>">

            <th class="subscription-next-payment order-date woocommerce-orders-table__header woocommerce-orders-table__header-order-date woocommerce-orders-table__header-subscription-next-payment">
                <span
                    class="nobr"><?php echo esc_html_x('Next payment', 'table heading', 'woocommerce-subscriptions'); ?></span>
            </th>
            <td class="subscription-next-payment order-date woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-next-payment woocommerce-orders-table__cell-order-date"
                data-title="<?php echo esc_attr_x('Next payment', 'table heading', 'woocommerce-subscriptions'); ?>">
                <span
                    class="td-value"><?php echo esc_html($subscription->get_date_to_display('next_payment')); ?></span>
            </td>
        </tr>
        <tr class="order woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr($subscription->get_status()); ?>">

            <th class="subscription-total order-total woocommerce-orders-table__header woocommerce-orders-table__header-order-total woocommerce-orders-table__header-subscription-total">
                <span
                    class="nobr"><?php echo esc_html_x('Total', 'table heading', 'woocommerce-subscriptions'); ?></span>
            </th>
            <td class="subscription-total order-total woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-total woocommerce-orders-table__cell-order-total"
                data-title="<?php echo esc_attr_x('Total', 'Used in data attribute. Escaped', 'woocommerce-subscriptions'); ?>">
                <span class="td-value"><?php echo wp_kses_post($subscription->get_formatted_order_total()); ?></span>
            </td>
        </tr>
        <tr class="order woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr($subscription->get_status()); ?>">

            <th class="subscription-actions order-actions woocommerce-orders-table__header woocommerce-orders-table__header-order-actions woocommerce-orders-table__header-subscription-actions">
                &nbsp;
            </th>
            <td class="subscription-actions order-actions woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-actions woocommerce-orders-table__cell-order-actions">
                <a href="<?php echo esc_url($subscription->get_view_order_url()); ?>"
                   class="woocommerce-button button view<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>"><?php echo esc_html_x('View', 'view a subscription', 'woocommerce-subscriptions'); ?></a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php do_action('woocommerce_subscription_after_related_subscriptions_table', $subscriptions, $order_id); ?>
