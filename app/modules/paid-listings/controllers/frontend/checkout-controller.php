<?php

namespace Voxel\Modules\Paid_Listings\Controllers\Frontend;

use \Voxel\Modules\Paid_Listings as Module;
use \Voxel\Modules\Claim_Listings as Claim_Listings;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Checkout_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_paid_listings.choose_plan', '@choose_plan' );
		$this->on( 'voxel_ajax_nopriv_paid_listings.choose_plan', '@choose_plan_guest_user' );
	}

	protected function choose_plan() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_choose_plan' );

			$customer = \Voxel\get_current_user();

			// support custom redirect
			$redirect_to = null;
			if ( ! empty( $_REQUEST['redirect_to'] ) && wp_validate_redirect( $_REQUEST['redirect_to'] ) ) {
				$redirect_to = wp_validate_redirect( $_REQUEST['redirect_to'] );
			}

			$process = \Voxel\from_list( $_REQUEST['process'] ?? null, [ 'new', 'relist', 'claim', 'switch' ], null );

			if ( $process === 'new' ) {
				$post_type = \Voxel\Post_Type::get( $_REQUEST['item_type'] ?? null );
				if ( $post_type === null ) {
					throw new \Exception( _x( 'This plan is not available.', 'pricing plans', 'voxel' ), 70 );
				}

				if ( ! empty( $_REQUEST['package_id'] ) ) {
					$package = Module\Listing_Package::get( absint( $_REQUEST['package_id'] ) );
					if ( ! ( $package && $customer->is_customer_of( $package->order->get_id() ) ) ) {
						throw new \Exception( _x( 'This plan is not available.', 'pricing plans', 'voxel' ), 75 );
					}

					if ( ! $package->can_create_post( $post_type ) ) {
						throw new \Exception( _x( 'This plan is not available.', 'pricing plans', 'voxel' ), 71 );
					}

					$draft = Module\get_or_create_draft( $package, $post_type, $customer );
					if ( $draft === null ) {
						throw new \Exception( _x( 'This plan is not available.', 'pricing plans', 'voxel' ), 72 );
					}

					return wp_send_json( [
						'success' => true,
						'type' => 'redirect',
						'redirect_to' => $draft->get_edit_link(),
					] );
				} else {
					$plan = Module\Listing_Plan::get( sanitize_text_field( $_REQUEST['plan'] ?? '' ) );
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

					return wp_send_json( $payment_method->process_payment() );
				}
			} elseif ( $process === 'relist' ) {
				$post = \Voxel\Post::get( $_REQUEST['post_id'] ?? null );
				if ( ! ( $post && $post->post_type && $post->is_editable_by_current_user() && $post->get_status() === 'expired' ) ) {
					throw new \Exception( _x( 'This item cannot be relisted.', 'pricing plans', 'voxel' ), 70 );
				}

				if ( ! empty( $_REQUEST['package_id'] ) ) {
					$package = Module\Listing_Package::get( absint( $_REQUEST['package_id'] ) );
					if ( ! ( $package && $customer->is_customer_of( $package->order->get_id() ) ) ) {
						throw new \Exception( _x( 'This plan is not available.', 'pricing plans', 'voxel' ), 75 );
					}

					if ( ! $package->can_create_post( $post->post_type ) ) {
						throw new \Exception( _x( 'This plan is not available.', 'pricing plans', 'voxel' ), 71 );
					}

					wp_update_post( [
						'ID' => $post->get_id(),
						'post_status' => 'publish',
						'post_date' => current_time( 'mysql' ),
						'post_date_gmt' => current_time( 'mysql', true ),
					] );

					$package->assign_to_post( $post );

					return wp_send_json( [
						'success' => true,
						'type' => 'redirect',
						'redirect_to' => $redirect_to ?? $post->get_link(),
					] );
				} else {
					$plan = Module\Listing_Plan::get( sanitize_text_field( $_REQUEST['plan'] ?? '' ) );
					if ( $plan === null || ! $plan->supports_post_type( $post->post_type ) ) {
						throw new \Exception( _x( 'This plan is not available.', 'pricing plans', 'voxel' ), 80 );
					}

					$cart_item = \Voxel\Cart_Item::create( [
						'product' => [
							'post_id' => $plan->get_product_id(),
							'field_key' => 'voxel:listing_plan',
						],
						'custom_data' => [
							'checkout_context' => [
								'process' => 'relist',
								'post_id' => $post->get_id(),
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

					return wp_send_json( $payment_method->process_payment() );
				}
			} elseif ( $process === 'switch' ) {
				$post = \Voxel\Post::get( $_REQUEST['post_id'] ?? null );
				if ( ! (
					$post
					&& $post->post_type
					&& $post->is_editable_by_current_user()
					&& $post->get_status() === 'publish'
				) ) {
					throw new \Exception( _x( 'This plan is not available.', 'pricing plans', 'voxel' ), 70 );
				}

				if ( ! empty( $_REQUEST['package_id'] ) ) {
					$package = Module\Listing_Package::get( absint( $_REQUEST['package_id'] ) );
					if ( ! ( $package && $customer->is_customer_of( $package->order->get_id() ) ) ) {
						throw new \Exception( _x( 'This plan is not available.', 'pricing plans', 'voxel' ), 75 );
					}

					if ( ! $package->can_create_post( $post->post_type ) ) {
						throw new \Exception( _x( 'This plan is not available.', 'pricing plans', 'voxel' ), 71 );
					}

					$package->assign_to_post( $post );

					return wp_send_json( [
						'success' => true,
						'type' => 'redirect',
						'redirect_to' => $redirect_to ?? $post->get_link(),
					] );
				} else {
					$plan = Module\Listing_Plan::get( sanitize_text_field( $_REQUEST['plan'] ?? '' ) );
					if ( $plan === null || ! $plan->supports_post_type( $post->post_type ) ) {
						throw new \Exception( _x( 'This plan is not available.', 'pricing plans', 'voxel' ), 80 );
					}

					$cart_item = \Voxel\Cart_Item::create( [
						'product' => [
							'post_id' => $plan->get_product_id(),
							'field_key' => 'voxel:listing_plan',
						],
						'custom_data' => [
							'checkout_context' => [
								'process' => 'switch',
								'post_id' => $post->get_id(),
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

					return wp_send_json( $payment_method->process_payment() );
				}
			} elseif ( $process === 'claim' ) {
				$post = \Voxel\Post::get( $_REQUEST['post_id'] ?? null );
				if ( ! ( $post && Claim_Listings\is_claimable( $post ) ) ) {
					throw new \Exception( _x( 'This item cannot be claimed.', 'pricing plans', 'voxel' ), 70 );
				}

				if ( ! empty( $_REQUEST['package_id'] ) ) {
					$package = Module\Listing_Package::get( absint( $_REQUEST['package_id'] ) );
					if ( ! ( $package && $customer->is_customer_of( $package->order->get_id() ) ) ) {
						throw new \Exception( _x( 'This plan is not available.', 'pricing plans', 'voxel' ), 75 );
					}

					if ( ! $package->can_create_post( $post->post_type ) ) {
						throw new \Exception( _x( 'This plan is not available.', 'pricing plans', 'voxel' ), 71 );
					}

					$cart_item = \Voxel\Cart_Item::create( [
						'product' => [
							'post_id' => Claim_Listings\get_product()->get_id(),
							'field_key' => 'voxel:claim_request',
						],
						'custom_data' => [
							'voxel:claim_request' => [
								'post_id' => $post->get_id(),
								'package_id' => $package->get_id(),
							],
						],
					] );

					$checkout_link = get_permalink( \Voxel\get( 'templates.checkout' ) ) ?: home_url('/');
					$checkout_link = add_query_arg( 'checkout_item', $cart_item->get_key(), $checkout_link );

					if ( $redirect_to !== null ) {
						$checkout_link = add_query_arg(
							'redirect_to',
							urlencode( $redirect_to ),
							$checkout_link
						);
					}

					return wp_send_json( [
						'success' => true,
						'type' => 'checkout',
						'item' => $cart_item->get_frontend_config(),
						'checkout_link' => $checkout_link,
					] );
				} else {
					$plan = Module\Listing_Plan::get( sanitize_text_field( $_REQUEST['plan'] ?? '' ) );
					if ( $plan === null || ! $plan->supports_post_type( $post->post_type ) ) {
						throw new \Exception( _x( 'This plan is not available.', 'pricing plans', 'voxel' ), 80 );
					}

					$cart_item = \Voxel\Cart_Item::create( [
						'product' => [
							'post_id' => $plan->get_product_id(),
							'field_key' => 'voxel:listing_plan',
						],
						'custom_data' => [
							'checkout_context' => [
								'process' => 'claim',
								'post_id' => $post->get_id(),
							],
						],
					] );

					$checkout_link = get_permalink( \Voxel\get( 'templates.checkout' ) ) ?: home_url('/');
					$checkout_link = add_query_arg( 'checkout_item', $cart_item->get_key(), $checkout_link );

					if ( $redirect_to !== null ) {
						$checkout_link = add_query_arg(
							'redirect_to',
							urlencode( $redirect_to ),
							$checkout_link
						);
					}

					return wp_send_json( [
						'success' => true,
						'type' => 'checkout',
						'item' => $cart_item->get_frontend_config(),
						'checkout_link' => $checkout_link,
					] );
				}
			} else {
				$plan = Module\Listing_Plan::get( sanitize_text_field( $_GET['plan'] ?? '' ) );
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

				$order = \Voxel\Order::create_from_cart( $cart, [
					'meta' => [
						'redirect_to' => $redirect_to,
					],
				] );

				$payment_method = $order->get_payment_method();
				if ( $payment_method === null ) {
					throw new \Exception( __( 'Could not process payment', 'voxel' ), 101 );
				}

				return wp_send_json( $payment_method->process_payment() );
			}
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function choose_plan_guest_user() {
		try {
			$redirect_to = add_query_arg( [
				'_ctx' => 'listing_plans',
				'plan' => $_REQUEST['plan'] ?? '',
				'process' => $_REQUEST['process'] ?? '',
				'item_type' => $_REQUEST['item_type'] ?? '',
			], wp_get_referer() );

			return wp_send_json( [
				'success' => true,
				'type' => 'redirect',
				'redirect_to' => add_query_arg( [
					'register' => '',
					'redirect_to' => urlencode( $redirect_to ),
				], \Voxel\get_auth_url() ),
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

}
