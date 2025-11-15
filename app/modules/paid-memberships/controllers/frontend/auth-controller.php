<?php

namespace Voxel\Modules\Paid_Memberships\Controllers\Frontend;

use \Voxel\Modules\Paid_Memberships as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Auth_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->filter( 'voxel/register/redirect_to', '@set_registration_redirect', 50, 3 );
	}

	protected function set_registration_redirect( $redirect_to, $user, $role ) {
		if ( ! empty( $_REQUEST['plan'] ) ) {
			try {
				if ( ( $_REQUEST['plan'] ?? '' ) === 'default' ) {
					$plan = Module\Plan::get_or_create_default_plan();
					$price = null;
				} else {
					$price = Module\Price::from_checkout_key( sanitize_text_field( $_REQUEST['plan'] ?? '' ) );
					$plan = $price->plan;
				}

				if ( $plan->is_archived() ) {
					throw new \Exception( _x( 'This plan is no longer available.', 'pricing plans', 'voxel' ) );
				}

				if ( $plan->get_key() === 'default' ) {
					Module\update_user_plan( $user->get_id(), [
						'plan' => 'default',
						'type' => 'default',
					] );

					return $redirect_to;
				} else {
					$product = $price->get_product();
					$cart_item = \Voxel\Product_Types\Cart_Items\Cart_Item::create( [
						'product' => [
							'post_id' => $product->get_id(),
							'field_key' => 'voxel:membership_plan',
						],
					] );

					$cart = new \Voxel\Product_Types\Cart\Direct_Cart;
					$cart->add_item( $cart_item );

					$order = \Voxel\Product_Types\Orders\Order::create_from_cart( $cart, [
						'meta' => [
							'redirect_to' => $redirect_to,
						],
					] );

					$payment_method = $order->get_payment_method();
					if ( $payment_method === null ) {
						throw new \Exception( __( 'Could not process payment', 'voxel' ), 101 );
					}

					$response = $payment_method->process_payment();
					if ( isset( $response['redirect_url'] ) ) {
						return $response['redirect_url'];
					}
				}
			} catch ( \Exception $e ) {
				//
			}
		}

		if ( $role->has_plans_enabled() && $role->config( 'registration.show_plans_on_signup', true ) ) {
			$plans_page = get_permalink( $role->get_pricing_page_id() ) ?: home_url('/');
			return add_query_arg( [
				'redirect_to' => urlencode( $redirect_to ),
			], $plans_page );
		}

		return $redirect_to;
	}
}
