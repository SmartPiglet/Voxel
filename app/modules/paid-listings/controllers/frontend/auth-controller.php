<?php

namespace Voxel\Modules\Paid_Listings\Controllers\Frontend;

use \Voxel\Modules\Paid_Listings as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Auth_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->filter( 'voxel/register/redirect_to', '@set_registration_redirect', 100, 4 );
	}

	protected function set_registration_redirect( $redirect_to, $user, $role, $raw_redirect_url ) {
		if ( ! is_string( $raw_redirect_url ) || empty( $raw_redirect_url ) ) {
			return $redirect_to;
		}

		$query = parse_url( $raw_redirect_url, PHP_URL_QUERY );
		if ( empty( $query ) ) {
			return $redirect_to;
		}

		$params = [];
		parse_str( $query, $params );
		if ( ( $params['_ctx'] ?? null ) !== 'listing_plans' ) {
			return $redirect_to;
		}

		try {
			$process = \Voxel\from_list( $params['process'] ?? null, [ 'new', 'relist', 'claim', 'switch' ], null );
			if ( $process === 'new' ) {
				$post_type = \Voxel\Post_Type::get( $params['item_type'] ?? null );
				if ( $post_type === null ) {
					throw new \Exception( _x( 'This plan is not available.', 'pricing plans', 'voxel' ), 70 );
				}

				$plan = Module\Listing_Plan::get( sanitize_text_field( $params['plan'] ?? '' ) );
				if ( $plan === null || ! $plan->supports_post_type( $post_type ) ) {
					throw new \Exception( _x( 'This plan is not available.', 'pricing plans', 'voxel' ), 80 );
				}

				$cart_item = \Voxel\Cart_Item::create( [
					'product' => [
						'post_id' => $plan->get_product_id(),
						'field_key' => 'voxel:listing_plan',
					],
					'custom_data' => [
						'checkout_context' => [
							'process' => 'new',
							'post_type' => $post_type->get_key(),
						],
					],
				] );

				$cart = new \Voxel\Product_Types\Cart\Direct_Cart;
				$cart->add_item( $cart_item );

				$order = \Voxel\Order::create_from_cart( $cart );

				$payment_method = $order->get_payment_method();
				if ( $payment_method === null ) {
					throw new \Exception( __( 'Could not process payment', 'voxel' ), 101 );
				}

				$response = $payment_method->process_payment();
				if ( isset( $response['redirect_url'] ) ) {
					return $response['redirect_url'];
				}

				throw new \Exception( __( 'Could not process payment', 'voxel' ), 101 );
			} elseif ( in_array( $process, [ 'relist', 'switch' ] ) ) {
				throw new \Exception( __( 'Could not process payment', 'voxel' ), 101 );
			} elseif ( $process === 'claim' ) {
				throw new \Exception( __( 'Could not process payment', 'voxel' ), 101 );
			} else {
				$plan = Module\Listing_Plan::get( sanitize_text_field( $params['plan'] ?? '' ) );
				if ( $plan === null ) {
					throw new \Exception( _x( 'This plan is not available.', 'pricing plans', 'voxel' ), 150 );
				}

				$cart_item = \Voxel\Cart_Item::create( [
					'product' => [
						'post_id' => $plan->get_product_id(),
						'field_key' => 'voxel:listing_plan',
					],
				] );

				$cart = new \Voxel\Product_Types\Cart\Direct_Cart;
				$cart->add_item( $cart_item );

				$order = \Voxel\Order::create_from_cart( $cart );

				$payment_method = $order->get_payment_method();
				if ( $payment_method === null ) {
					throw new \Exception( __( 'Could not process payment', 'voxel' ), 101 );
				}

				$response = $payment_method->process_payment();
				if ( isset( $response['redirect_url'] ) ) {
					return $response['redirect_url'];
				}

				throw new \Exception( __( 'Could not process payment', 'voxel' ), 101 );
			}
		} catch ( \Exception $e ) {
			return $redirect_to;
		}
	}
}
