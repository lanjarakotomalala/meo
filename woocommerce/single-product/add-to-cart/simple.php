<?php
/**
 * Simple product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/simple.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product->is_purchasable() ) {
	return;
}

echo wc_get_stock_html( $product ); // WPCS: XSS ok.

if ( $product->is_in_stock() ) : ?>

	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<?php
		do_action( 'woocommerce_before_add_to_cart_quantity' );

		woocommerce_quantity_input(
			array(
				'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
				'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
				'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
			)
		);

		do_action( 'woocommerce_after_add_to_cart_quantity' );
		?>

		<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt">
			<?php echo esc_html( $product->single_add_to_cart_text() ); ?>
			<svg viewBox="0 0 19 24" width="16" height="20" aria-hidden="true" focusable="false">
				<path d="M16.6,24H2.4c-0.7,0-1.3-0.3-1.7-0.8C0.2,22.8,0,22.1,0,21.4L0.6,7.3C0.7,6,1.8,5,3,5H16c1.3,0,2.3,1,2.4,2.3L19,21.4 c0,0.7-0.2,1.3-0.7,1.8C17.9,23.7,17.3,24,16.6,24z M3,6.6C2.6,6.6,2.2,7,2.2,7.5L1.6,21.5c0,0.2,0.1,0.4,0.2,0.6 c0.1,0.2,0.4,0.3,0.6,0.3h14.2c0.2,0,0.4-0.1,0.6-0.3c0.1-0.2,0.2-0.4,0.2-0.6L16.8,7.4c0-0.5-0.4-0.8-0.8-0.8L3,6.6L3,6.6z"/><path d="M14,6h-1.5V4.6c0-1.7-1.3-3.1-3-3.1s-3,1.4-3,3.1V6H5V4.6C5,2.1,7,0,9.5,0S14,2.1,14,4.6V6z"/>
			</svg>

		</button>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
	</form>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php endif; ?>
