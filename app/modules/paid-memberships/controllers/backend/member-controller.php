<?php

namespace Voxel\Modules\Paid_Memberships\Controllers\Backend;

use \Voxel\Modules\Paid_Memberships as Module;
use \Voxel\Utils\Config_Schema\Schema;
use \Voxel\Product_Types\Payment_Services\Base_Payment_Service as Payment_Service;
use \Voxel\Modules\Paid_Memberships\Membership\Base_Membership as Membership;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Member_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return current_user_can( 'manage_options' );
	}

	protected function hooks() {
		$this->on( 'voxel_ajax_membership.update_customer_plan', '@update_customer_plan' );
		$this->on( 'voxel_ajax_paid_members.migrate_legacy_subscription', '@migrate_legacy_subscription' );
	}

	protected function update_customer_plan() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 100 );
			}

			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_admin_edit_customer' )  ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 102 );
			}

			$payload = (array) json_decode( wp_unslash( $_REQUEST['payload'] ?? '' ), true );

			$user = \Voxel\User::get( $payload['customer_id'] ?? null );
			if ( ! $user ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 103 );
			}

			$membership = $user->get_membership();

			$new_plan = Module\Plan::get( $payload['plan'] ?? null );
			if ( ! $new_plan ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ), 104 );
			}

			if ( $membership->get_type() === 'order' ) {
				if (
					( $order = $membership->get_order() )
					&& ( $payment_method = $membership->get_payment_method() )
					&& ! $payment_method->is_subscription_canceled()
				) {
					// update order item meta
					foreach ( $order->get_items() as $order_item ) {
						if ( $order_item->get_product_field_key() === 'voxel:membership_plan' ) {
							$order_item->set_details( 'voxel:membership.plan', $new_plan->get_key() );
							$order_item->save();
						}
					}

					// update user meta
					$meta_key = \Voxel\is_test_mode() ? 'voxel:test_plan' : 'voxel:plan';

					$details = (array) json_decode( \Voxel\get_site_specific_user_meta( $user->get_id(), $meta_key, true ), true );
					$details['plan'] = $new_plan->get_key();

					Module\update_user_plan( $user->get_id(), $details );

					return wp_send_json( [
						'success' => true,
					] );
				} else {
					// unlink order (subscription cancelled permanently)
					Module\update_user_plan( $user->get_id(), [
						'plan' => 'default',
						'type' => 'default',
					] );

					return wp_send_json( [
						'success' => true,
					] );
				}
			} else {
				if ( Payment_Service::get_active()?->get_key() === 'stripe' ) {
					if (
						$new_plan->get_key() !== 'default'
						&& !! ( $payload['stripe_map']['enabled'] ?? false )
					) {
						$stripe = \Voxel\Modules\Stripe_Payments\Stripe_Client::get_client();
						$transaction_id = $payload['stripe_map']['transaction_id'] ?? null;
						if ( is_string( $transaction_id ) && str_starts_with( $transaction_id, 'sub_' ) ) {
							$subscription = $stripe->subscriptions->retrieve( $transaction_id );
							if ( $subscription->customer !== $user->get_stripe_customer_id() ) {
								throw new \Exception( 'Provided subscription does not belongs to the selected user', 106 );
							}

							if ( in_array( $subscription->status, [ 'canceled', 'sub_incomplete_expired' ], true ) ) {
								throw new \Exception( 'Provided subscription has been canceled', 107 );
							}

							$existing_order = \Voxel\Order::find( [
								'payment_method' => 'stripe_subscription',
								'transaction_id' => $transaction_id,
							] );

							if ( $existing_order ) {
								throw new \Exception( 'An order with this subscription ID already exists', 108 );
							}

							global $wpdb;

							// insert order
							$result = $wpdb->insert( $wpdb->prefix.'vx_orders', [
								'customer_id' => $user->get_id(),
								'vendor_id' => null,
								'status' => 'completed',
								'shipping_status' => null,
								'payment_method' => 'stripe_subscription',
								'transaction_id' => $subscription->id,
								'details' => wp_json_encode( Schema::optimize_for_storage( [
									'pricing' => [
										'currency' => strtoupper( $subscription->currency ),
										'subtotal' => 0,
										'total' => 0,
									],
								] ) ),
								'parent_id' => null,
								'testmode' => \Voxel\is_test_mode() ? 1 : 0,
								'created_at' => \Voxel\utc()->format( 'Y-m-d H:i:s' ),
							] );

							if ( $result === false ) {
								throw new \Exception( _x( 'Could not create order.', 'checkout', 'voxel' ) );
							}

							$order_id = $wpdb->insert_id;

							$result = $wpdb->insert( $wpdb->prefix.'vx_order_items', [
								'order_id' => $order_id,
								'post_id' => 0,
								'product_type' => 'voxel:membership_plan',
								'field_key' => 'voxel:membership_plan',
								'details' => wp_json_encode( Schema::optimize_for_storage( [
									'type' => 'regular',
									'product' => [
										'label' => $new_plan->get_label(),
									],
									'currency' => strtoupper( $subscription->currency ),
									'voxel:membership' => [
										'plan' => $new_plan->get_key(),
									],
								] ) ),
							] );

							if ( $result === false ) {
								throw new \Exception( _x( 'Could not create order.', 'checkout', 'voxel' ), 112 );
							}

							$order_item_id = $wpdb->insert_id;

							$order = \Voxel\Order::get( $order_id );
							$order_item = \Voxel\Order_Item::get( $order_item_id );

							$payment_method = $order->get_payment_method();
							$payment_method->subscription_updated( $subscription );

							$amount = $order->get_details('pricing.total');
							$order->set_details('pricing.subtotal', $amount);
							$order->save();

							$order_item->set_details('summary.total_amount', $amount);
							$order_item->save();

							return wp_send_json( [
								'success' => true,
							] );
						} else {
							throw new \Exception( 'Provide a valid Stripe subscription ID.', 104 );
						}
					}
				}

				// manually assign plan
				$details = [
					'plan' => $new_plan->get_key(),
					'type' => 'default',
				];

				if ( $new_plan->get_key() === 'default' && ( $payload['trial_allowed'] ?? null ) ) {
					$details['trial_allowed'] = true;
				}

				Module\update_user_plan( $user->get_id(), $details );

				return wp_send_json( [
					'success' => true,
				] );
			}
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function migrate_legacy_subscription() {
		try {
			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'paid_members.migrate_legacy_plan' )  ) {
				throw new \Exception( 'Invalid request.', 102 );
			}

			$schema = Schema::Object( [
				'user_id' => Schema::Int()->min(1),
				'strategy' => Schema::Enum( [ 'paid_membership_plan', 'paid_listing_plan', 'unset' ] ),
				'listing_plan' => Schema::String(),
			] );

			$schema->set_value( (array) json_decode( wp_unslash( $_POST['config'] ?? '' ), true ) );
			$config = $schema->export();

			$user = \Voxel\User::get( $config['user_id'] );
			if ( ! $user ) {
				throw new \Exception( 'User not found.', 103 );
			}

			$membership = $user->get_membership();
			$legacy_plan = Membership::get_legacy( $user->get_id() );

			if ( ! ( $legacy_plan && $membership->get_type() === 'default' ) ) {
				throw new \Exception( 'Invalid request.', 104 );
			}

			if ( ! (
				$legacy_plan->get_type() === 'legacy_subscription'
				&& ! in_array( $legacy_plan->get_status(), [ 'canceled', 'incomplete_expired' ], true )
			) ) {
				throw new \Exception( 'Invalid request.', 106 );
			}

			global $wpdb;

			$stripe = \Voxel\Modules\Stripe_Payments\Stripe_Client::get_client();

			if ( $config['strategy'] === 'paid_membership_plan' ) {
				$subscription = $stripe->subscriptions->retrieve( $legacy_plan->get_subscription_id() );

				$existing_order = \Voxel\Order::find( [
					'payment_method' => 'stripe_subscription',
					'transaction_id' => $subscription->id,
				] );

				if ( $existing_order ) {
					throw new \Exception( 'An order with this subscription ID already exists', 108 );
				}

				// insert order
				$result = $wpdb->insert( $wpdb->prefix.'vx_orders', [
					'customer_id' => $user->get_id(),
					'vendor_id' => null,
					'status' => 'completed',
					'shipping_status' => null,
					'payment_method' => 'stripe_subscription',
					'transaction_id' => $subscription->id,
					'details' => wp_json_encode( Schema::optimize_for_storage( [
						'pricing' => [
							'currency' => strtoupper( $subscription->currency ),
							'subtotal' => 0,
							'total' => 0,
						],
					] ) ),
					'parent_id' => null,
					'testmode' => \Voxel\is_test_mode() ? 1 : 0,
					'created_at' => \Voxel\utc()->format( 'Y-m-d H:i:s' ),
				] );

				if ( $result === false ) {
					throw new \Exception( _x( 'Could not create order.', 'checkout', 'voxel' ) );
				}

				$order_id = $wpdb->insert_id;

				$result = $wpdb->insert( $wpdb->prefix.'vx_order_items', [
					'order_id' => $order_id,
					'post_id' => 0,
					'product_type' => 'voxel:membership_plan',
					'field_key' => 'voxel:membership_plan',
					'details' => wp_json_encode( Schema::optimize_for_storage( [
						'type' => 'regular',
						'product' => [
							'label' => $legacy_plan->get_selected_plan()->get_label(),
						],
						'currency' => strtoupper( $subscription->currency ),
						'voxel:membership' => [
							'plan' => $legacy_plan->get_selected_plan()->get_key(),
						],
					] ) ),
				] );

				if ( $result === false ) {
					throw new \Exception( _x( 'Could not create order.', 'checkout', 'voxel' ), 112 );
				}

				$order_item_id = $wpdb->insert_id;

				$order = \Voxel\Order::get( $order_id );
				$order_item = \Voxel\Order_Item::get( $order_item_id );

				$payment_method = $order->get_payment_method();
				$payment_method->subscription_updated( $subscription );

				$amount = $order->get_details('pricing.total');
				$order->set_details('pricing.subtotal', $amount);
				$order->save();

				$order_item->set_details('summary.total_amount', $amount);
				$order_item->save();

				if ( is_multisite() ) {
					$meta_key = \Voxel\is_test_mode() ? 'voxel:test_plan' : 'voxel:plan';
					delete_user_meta( $user->get_id(), $meta_key );
				}

				return wp_send_json( [
					'success' => true,
					'redirect_to' => admin_url( 'admin.php?page=voxel-paid-members&customer='.$user->get_id() ),
				] );
			} elseif ( $config['strategy'] === 'paid_listing_plan' ) {
				$listing_plan = \Voxel\Modules\Paid_Listings\Listing_Plan::get( $config['listing_plan'] );
				if ( ! $listing_plan ) {
					throw new \Exception( 'You must select a listing plan', 108 );
				}

				$subscription = $stripe->subscriptions->retrieve( $legacy_plan->get_subscription_id() );

				$existing_order = \Voxel\Order::find( [
					'payment_method' => 'stripe_subscription',
					'transaction_id' => $subscription->id,
				] );

				if ( $existing_order ) {
					throw new \Exception( 'An order with this subscription ID already exists', 108 );
				}

				$cart_item = \Voxel\Cart_Item::create( [
					'product' => [
						'post_id' => $listing_plan->get_product_id(),
						'field_key' => 'voxel:listing_plan',
					],
				] );

				// insert order
				$result = $wpdb->insert( $wpdb->prefix.'vx_orders', [
					'customer_id' => $user->get_id(),
					'vendor_id' => null,
					'status' => 'completed',
					'shipping_status' => null,
					'payment_method' => 'stripe_subscription',
					'transaction_id' => $subscription->id,
					'details' => wp_json_encode( Schema::optimize_for_storage( [
						'pricing' => [
							'currency' => strtoupper( $subscription->currency ),
							'subtotal' => 0,
							'total' => 0,
						],
					] ) ),
					'parent_id' => null,
					'testmode' => \Voxel\is_test_mode() ? 1 : 0,
					'created_at' => \Voxel\utc()->format( 'Y-m-d H:i:s' ),
				] );

				if ( $result === false ) {
					throw new \Exception( _x( 'Could not create order.', 'checkout', 'voxel' ) );
				}

				$order_id = $wpdb->insert_id;

				$result = $wpdb->insert( $wpdb->prefix.'vx_order_items', [
					'order_id' => $order_id,
					'post_id' => $cart_item->get_post()->get_id(),
					'product_type' => $cart_item->get_product_type()->get_key(),
					'field_key' => $cart_item->get_product_field()->get_key(),
					'details' => wp_json_encode( Schema::optimize_for_storage( $cart_item->get_order_item_config() ) ),
				] );

				if ( $result === false ) {
					throw new \Exception( _x( 'Could not create order.', 'checkout', 'voxel' ), 112 );
				}

				$order_item_id = $wpdb->insert_id;

				$order = \Voxel\Order::get( $order_id );
				$order_item = \Voxel\Order_Item::get( $order_item_id );

				$payment_method = $order->get_payment_method();
				$payment_method->subscription_updated( $subscription );

				$amount = $order->get_details('pricing.total');
				$order->set_details('pricing.subtotal', $amount);
				$order->save();

				$order_item->set_details('summary.total_amount', $amount);
				$order_item->set_details('summary.summary.0.amount', $amount);
				$order_item->save();

				$meta_key = \Voxel\is_test_mode() ? 'voxel:test_plan' : 'voxel:plan';
				delete_user_meta( $user->get_id(), $meta_key );

				return wp_send_json( [
					'success' => true,
					'redirect_to' => admin_url( 'admin.php?page=voxel-paid-listings&package='.$order_item->get_id() ),
				] );
			} elseif ( $config['strategy'] === 'unset' ) {
				$meta_key = \Voxel\is_test_mode() ? 'voxel:test_plan' : 'voxel:plan';
				delete_user_meta( $user->get_id(), $meta_key );

				return wp_send_json( [
					'success' => true,
					'redirect_to' => admin_url( 'admin.php?page=voxel-paid-members&customer='.$user->get_id() ),
				] );
			} else {
				throw new \Exception( 'Invalid request.', 105 );
			}
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

}
