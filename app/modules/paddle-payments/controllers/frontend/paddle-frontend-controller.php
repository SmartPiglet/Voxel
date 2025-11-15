<?php

namespace Voxel\Modules\Paddle_Payments\Controllers\Frontend;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Paddle_Frontend_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'template_redirect', '@handle_checkout_redirect', 0 );
		$this->filter( 'voxel/orders/view_order/components', '@register_order_components', 10, 2 );
	}

	protected function handle_checkout_redirect() {
		if ( empty( $_GET['_ptxn'] ) || ! is_string( $_GET['_ptxn'] ) ) {
			return;
		}

		$transaction_id = sanitize_text_field( $_GET['_ptxn'] );
		if ( empty( $transaction_id ) ) {
			return;
		}

		$paddle = \Voxel\Modules\Paddle_Payments\Paddle_Client::get_client();
		$transaction = $paddle->transactions->get( $transaction_id );

		$order = \Voxel\Product_Types\Orders\Order::find( [
			'payment_method' => [ 'paddle_payment', 'paddle_subscription' ],
			'transaction_id' => $transaction_id,
			'customer_id' => get_current_user_id(),
		] );

		if ( ! $order ) {
			return;
		}

		if ( $order->get_details( 'cart.type' ) === 'customer_cart' ) {
			$cart = \Voxel\current_user()->get_cart();
			$cart->empty();
			$cart->update();
		}

		$order->sync();

		wp_safe_redirect( $order->get_success_redirect() );
		exit;
	}

	protected function register_order_components( $components, $order ): array {
		if (
			$order->get_payment_method_key() === 'paddle_subscription'
			&& ( $payment_method = $order->get_payment_method() )
		) {
			$state = $payment_method->get_state();

			if ( $state['status'] !== null ) {
				$components[] = [
					'type' => 'paddle-subscription-details',
					'src' => \Voxel\get_esm_src('order-paddle-subscription-details.js'),
					'data' => $state,
				];
			}
		}

		return $components;
	}

}
