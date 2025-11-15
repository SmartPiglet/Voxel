<?php

namespace Voxel\Modules\Paid_Listings\Controllers\Backend;

use \Voxel\Modules\Paid_Listings as Module;
use \Voxel\Utils\Config_Schema\Schema;
use \Voxel\Product_Types\Payment_Services\Base_Payment_Service as Payment_Service;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Packages_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return current_user_can( 'manage_options' );
	}

	protected function hooks() {
		$this->on( 'voxel/paid-listings/backend/packages-screen', '@render_packages_screen' );
		$this->on( 'admin_post_paid_listings.edit_package', '@save_package_settings' );
		$this->on( 'voxel_ajax_paid_listings.add_package', '@add_package' );
	}

	protected function render_packages_screen() {
		if ( ! empty( $_GET['add_new_plan'] ) ) {
			require locate_template( 'app/modules/paid-listings/templates/backend/add-package.php' );
		} elseif ( ! empty( $_GET['package'] ) ) {
			$package = Module\Listing_Package::get( absint( $_GET['package'] ) );
			if ( ! $package ) {
				echo '<p>Plan not found</p>';
				return;
			}

			$order = $package->order;
			$order_item = $package->order_item;
			$plan = $package->get_plan();
			$customer = $order->get_customer();
			$payment_method = $order->get_payment_method();
			$limits = $package->get_limits();

			$config = [];
			require locate_template( 'app/modules/paid-listings/templates/backend/edit-package.php' );
		} else {
			$table = new Module\Package_List_Table;
			$table->prepare_items();
			require locate_template( 'app/modules/paid-listings/templates/backend/package-list-table.php' );
		}
	}

	protected function save_package_settings() {
		check_admin_referer( 'paid_listings.edit_package' );

		$package = Module\Listing_Package::get( absint( $_REQUEST['package_id'] ?? null ) );
		if ( ! $package ) {
			wp_die( 'Plan not found.', '', [
				'back_link' => true,
			] );
		}

		$limits = $package->get_limits();
		$new_totals = $_REQUEST['limits'] ?? null;

		if ( ! is_array( $new_totals ) ) {
			wp_die( 'Invalid request.', '', [
				'back_link' => true,
			] );
		}

		foreach ( $new_totals as $limit_index => $new_total ) {
			if ( ! isset( $limits[ $limit_index ] ) ) {
				continue;
			}

			if ( ! is_numeric( $new_total ) ) {
				continue;
			}

			$new_total = absint( $new_total );
			if ( $new_total < $limits[ $limit_index ]['usage']['count'] ) {
				continue;
			}

			$limits[ $limit_index ]['total'] = $new_total;
		}

		$package->set_limits( $limits );
		$package->update_usage_meta();
		$package->save();

		wp_safe_redirect( $package->get_backend_edit_link() );
		exit;
	}

	protected function add_package() {
		try {
			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'paid_listings.add_package' )  ) {
				throw new \Exception( 'Invalid request.', 102 );
			}

			$schema = Schema::Object( [
				'user_id' => Schema::Int()->min(1),
				'plan' => Schema::String(),
				'stripe_map' => Schema::Object( [
					'enabled' => Schema::Bool(),
					'transaction_id' => Schema::String()->default(''),
				] ),
			] );

			$schema->set_value( (array) json_decode( wp_unslash( $_POST['config'] ?? '' ), true ) );
			$config = $schema->export();

			$user = \Voxel\User::get( $config['user_id'] );
			if ( ! $user ) {
				throw new \Exception( 'User not found.', 103 );
			}

			$plan = Module\Listing_Plan::get( $config['plan'] );
			if ( ! $plan ) {
				throw new \Exception( 'Plan not found.', 104 );
			}

			global $wpdb;

			$payment_service = Payment_Service::get_active();
			if ( $config['stripe_map']['enabled'] && $payment_service?->get_key() === 'stripe' ) {
				$transaction_id = $config['stripe_map']['transaction_id'];

				$stripe = \Voxel\Modules\Stripe_Payments\Stripe_Client::get_client();

				if ( str_starts_with( $transaction_id, 'sub_' ) ) {
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

					add_action(
						'voxel/paid-listings/backend/assigned-package',
						function( $order ) use ( $subscription ) {
							$order->set_payment_method('stripe_subscription');
							$order->set_transaction_id( $subscription->id );
							$order->set_details('pricing.currency', strtoupper( $subscription->currency ));
							$order->save();

							$payment_method = $order->get_payment_method();
							$payment_method->subscription_updated( $subscription );

							$amount = $order->get_details('pricing.total');
							$order->set_details('pricing.subtotal', $amount);
							$order->save();

							foreach ( $order->get_items() as $order_item ) {
								$order_item->set_details('currency', strtoupper( $subscription->currency ));
								$order_item->set_details('summary.total_amount', $amount);
								$order_item->set_details('summary.summary.0.amount', $amount);
								$order_item->save();
							}
						}
					);
				} elseif ( str_starts_with( $transaction_id, 'pi_' ) ) {
					$payment_intent = $stripe->paymentIntents->retrieve( $transaction_id );
					if ( $payment_intent->customer !== $user->get_stripe_customer_id() ) {
						throw new \Exception( 'Provided payment does not belongs to the selected user', 106 );
					}

					if ( in_array( $payment_intent->status, [ 'canceled' ], true ) ) {
						throw new \Exception( 'Provided payment has been canceled', 107 );
					}

					$existing_order = \Voxel\Order::find( [
						'payment_method' => 'stripe_payment',
						'transaction_id' => $transaction_id,
					] );

					if ( $existing_order ) {
						throw new \Exception( 'An order with this payment ID already exists', 108 );
					}

					add_action(
						'voxel/paid-listings/backend/assigned-package',
						function( $order ) use ( $payment_intent ) {
							$order->set_payment_method('stripe_payment');
							$order->set_transaction_id( $payment_intent->id );
							$order->set_details('pricing.currency', strtoupper( $payment_intent->currency ) );
							$order->save();

							$payment_method = $order->get_payment_method();
							$payment_method->payment_intent_updated( $payment_intent );

							$amount = $order->get_details('pricing.total');
							$order->set_details('pricing.subtotal', $amount);
							$order->save();

							foreach ( $order->get_items() as $order_item ) {
								$order_item->set_details('currency', strtoupper( $payment_intent->currency ) );
								$order_item->set_details('summary.total_amount', $amount);
								$order_item->set_details('summary.summary.0.amount', $amount);
								$order_item->save();
							}
						}
					);
				} else {
					throw new \Exception( 'Provide a valid Stripe subscription or payment ID.', 104 );
				}
			}

			add_filter( 'voxel/paid-listings/registered-product-field', function( $field ) {
				$value = $field->get_value();
				$value['product_type'] = 'voxel:listing_plan_payment';
				$value['base_price'] = [
					'amount' => 0,
				];

				$field->_set_value( $value );
			} );

			$cart_item = \Voxel\Cart_Item::create( [
				'product' => [
					'post_id' => $plan->get_product_id(),
					'field_key' => 'voxel:listing_plan',
				],
			] );

			$cart = new \Voxel\Product_Types\Cart\Direct_Cart;
			$cart->add_item( $cart_item );

			// insert order
			$result = $wpdb->insert( $wpdb->prefix.'vx_orders', [
				'customer_id' => $user->get_id(),
				'vendor_id' => null,
				'status' => 'completed',
				'shipping_status' => null,
				'payment_method' => 'offline_payment',
				'transaction_id' => null,
				'details' => wp_json_encode( Schema::optimize_for_storage( [
					'cart' => [
						'type' => $cart->get_type(),
						'items' => array_map( function( $cart_item ) {
							return $cart_item->get_value_for_storage();
						}, $cart->get_items() ),
					],
					'pricing' => [
						'currency' => $cart->get_currency(),
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
			$package = Module\Listing_Package::get( $order_item_id );

			$order->set_transaction_id( sprintf( 'offline_%d', $order->get_id() ) );
			$order->save();

			do_action( 'voxel/paid-listings/backend/assigned-package', $order, $user, $plan );

			return wp_send_json( [
				'success' => true,
				'redirect_to' => $package->get_backend_edit_link(),
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

}
