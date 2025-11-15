<?php

namespace Voxel\Modules\Stripe_Connect\Controllers\Frontend;

use Voxel\Utils\Config_Schema\Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Frontend_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_stripe_connect.account.onboard', '@onboard_account' );
		$this->on( 'voxel_ajax_stripe_connect.account.save_shipping', '@save_vendor_shipping' );
		$this->on( 'voxel_ajax_stripe_connect.account.login', '@access_dashboard' );
		$this->on( 'voxel/stripe_connect/event:account.updated', '@account_updated', 10, 2 );

		$this->on( 'voxel/product-types/orders/order:updated', '@process_separate_charges_and_transfers' );
	}

	protected function onboard_account() {
		try {
			$stripe = \Voxel\Modules\Stripe_Payments\Stripe_Client::getClient();
			$user = \Voxel\get_current_user();
			$account = $user->get_or_create_stripe_vendor();

			$onboarding_key = \Voxel\random_string(8);
			update_user_meta( $user->get_id(), 'voxel:connect_onboarding_key', $onboarding_key );

			$link = $stripe->accountLinks->create( [
				'account' => $account->id,
				'refresh_url' => add_query_arg( [
					'vx' => 1,
					'action' => 'stripe.account.onboard',
				], home_url('/') ),
				'return_url' => add_query_arg( 'onboarding_key', $onboarding_key, \Voxel\get_template_link('stripe_account') ),
				'type' => 'account_onboarding',
			] );

			wp_redirect( $link->url );
			die;
		} catch ( \Exception $e ) {
			wp_die( $e->getMessage() );
		}
	}

	protected function save_vendor_shipping() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_vendor_dashboard' );
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$shipping_details = (array) json_decode( wp_unslash( $_REQUEST['shipping_details'] ?? '' ), true );
			$vendor = \Voxel\get_current_user();
			$schema = $vendor->get_vendor_shipping_zones_schema();

			$schema->set_value( $shipping_details );
			$value = $schema->export();

			if ( count( $value ) > 25 ) {
				throw new \Exception( _x( 'You cannot add more than 25 shipping zones', 'stripe vendor shipping', 'voxel' ) );
			}

			$validate_delivery_estimate = function( $estimate ) {
				// @todo
			};

			$shipping_countries = \Voxel\Modules\Stripe_Payments\Country_Codes::shipping_supported();
			foreach ( $value as $zone_index => $zone ) {
				if ( $zone['key'] === null || mb_strlen( $zone['key'] ) !== 8 ) {
					throw new \Exception( _x( 'Could not save shipping details', 'stripe vendor shipping', 'voxel' ), 90 );
				}

				if ( $zone['label'] === null || mb_strlen( $zone['label'] ) > 32 ) {
					throw new \Exception( _x( 'Shipping zone label is required', 'stripe vendor shipping', 'voxel' ), 91 );
				}

				if ( empty( $zone['countries'] ) ) {
					throw new \Exception( _x( 'Shipping zones must have at least one country selected', 'stripe vendor shipping', 'voxel' ), 92 );
				}

				if ( empty( $zone['rates'] ) ) {
					throw new \Exception( _x( 'Shipping zones must contain at least one shipping rate', 'stripe vendor shipping', 'voxel' ), 93 );
				}

				foreach ( $zone['rates'] as $rate_index => $rate ) {
					if ( $rate['key'] === null || mb_strlen( $rate['key'] ) !== 8 ) {
						throw new \Exception( _x( 'Could not save shipping details', 'stripe vendor shipping', 'voxel' ), 94 );
					}

					if ( $rate['label'] === null || mb_strlen( $rate['label'] ) > 32 ) {
						throw new \Exception( _x( 'Shipping rate label is required', 'stripe vendor shipping', 'voxel' ), 95 );
					}

					if ( $rate['type'] === 'free_shipping' ) {
						if ( $rate['free_shipping']['delivery_estimate']['enabled'] ) {
							$validate_delivery_estimate( $rate['free_shipping']['delivery_estimate'] );
						}
					} elseif ( $rate['type'] === 'fixed_rate' ) {
						if ( $rate['fixed_rate']['delivery_estimate']['enabled'] ) {
							$validate_delivery_estimate( $rate['fixed_rate']['delivery_estimate'] );
						}

						if ( count( $rate['fixed_rate']['shipping_classes'] ) > 100 ) {
							throw new \Exception( _x( 'Could not save shipping details', 'stripe vendor shipping', 'voxel' ), 96 );
						}
					}
				}
			}

			update_user_meta( $vendor->get_id(), 'voxel:vendor_shipping_zones', wp_slash( wp_json_encode( Schema::optimize_for_storage( $value ) ) ) );

			return wp_send_json( [
				'success' => true,
				'message' => _x( 'Shipping details saved.', 'stripe vendor shipping', 'voxel' ),
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function access_dashboard() {
		try {
			$stripe = \Voxel\Modules\Stripe_Payments\Stripe_Client::getClient();
			$user = \Voxel\get_current_user();
			$link = $stripe->accounts->createLoginLink( $user->get_stripe_vendor_id(), [
				'redirect_url' => \Voxel\get_template_link('stripe_account'),
			] );

			wp_redirect( $link->url );
			die;
		} catch ( \Exception $e ) {
			wp_die( $e->getMessage() );
		}
	}

	protected function account_updated( $event, $account ) {
		if ( $user = \Voxel\User::get_by_stripe_vendor_id( $account->id ) ) {
			$user->stripe_vendor_updated( $account );
		}
	}

	protected function process_separate_charges_and_transfers( \Voxel\Product_Types\Orders\Order $order ) {
		global $wpdb;

		if ( $order->get_status() !== \Voxel\ORDER_COMPLETED ) {
			return;
		}

		$payment_method = $order->get_payment_method();
		if ( ! ( $payment_method !== null && $payment_method->get_type() === 'stripe_payment' ) ) {
			return;
		}

		if ( $order->get_details('payment_intent.status') !== 'succeeded' ) {
			return;
		}

		if ( ! $order->get_details('payment_intent.latest_charge') ) {
			return;
		}

		if ( $order->get_details('multivendor.mode') !== 'separate_charges_and_transfers' ) {
			return;
		}

		if ( $order->get_details('multivendor.processed_transfers') ) {
			return;
		}

		$order->set_details( 'multivendor.processed_transfers', true );
		$order->save();

		$items_by_vendor = [];
		$generate_sub_orders = false;
		foreach ( $order->get_items() as $order_item ) {
			$vendor = $order_item->get_vendor();

			if ( $vendor !== null ) {
				if ( ! isset( $items_by_vendor[ $vendor->get_id() ] ) ) {
					$items_by_vendor[ $vendor->get_id() ] = [
						'vendor_type' => 'user',
						'vendor' => $vendor,
						'items' => [],
					];
				}

				$items_by_vendor[ $vendor->get_id() ]['items'][] = $order_item;
				$generate_sub_orders = true;
			} else {
				if ( ! isset( $items_by_vendor['platform'] ) ) {
					$items_by_vendor['platform'] = [
						'vendor_type' => 'platform',
						'items' => [],
					];
				}

				$items_by_vendor['platform']['items'][] = $order_item;
			}
		}

		if ( ! $generate_sub_orders ) {
			return;
		}

		foreach ( $items_by_vendor as $item_group ) {
			if ( $item_group['vendor_type'] === 'user' ) {
				$sub_order_subtotal = 0;
				foreach ( $item_group['items'] as $order_item ) {
					$sub_order_subtotal += $order_item->get_subtotal();
				}

				$vendor = $item_group['vendor'];
				$result = $wpdb->insert( $wpdb->prefix.'vx_orders', [
					'customer_id' => $order->get_customer_id(),
					'vendor_id' => $vendor->get_id(),
					'status' => 'pending_payment',
					'payment_method' => 'stripe_transfer',
					'transaction_id' => null,
					'details' => wp_json_encode( Schema::optimize_for_storage( [
						'pricing' => [
							'currency' => $order->get_currency(),
							'subtotal' => $sub_order_subtotal,
							'total' => $sub_order_subtotal,
						],
					] ) ),
					'parent_id' => $order->get_id(),
					'testmode' => $order->is_test_mode() ? 1 : 0,
					'created_at' => \Voxel\utc()->format( 'Y-m-d H:i:s' ),
				] );

				if ( $result === false ) {
					// throw new \Exception( _x( 'Could not create order.', 'checkout', 'voxel' ) );
					continue;
				}

				$sub_order_id = (int) $wpdb->insert_id;

				foreach ( $item_group['items'] as $order_item ) {
					$order_item->set_order_id( $sub_order_id );
					$order_item->save();
				}
			} elseif ( $item_group['vendor_type'] === 'platform' ) {
				$sub_order_subtotal = 0;
				foreach ( $item_group['items'] as $order_item ) {
					$sub_order_subtotal += $order_item->get_subtotal();
				}

				$sub_order_total = $sub_order_subtotal;
				$sub_order_shipping = null;
				if ( is_numeric( $order->get_details('shipping.amounts_by_vendor.platform.amount_in_cents') ) ) {
					$sub_order_shipping = (int) $order->get_details('shipping.amounts_by_vendor.platform.amount_in_cents');
					if ( $sub_order_shipping > 0 && ! \Voxel\Utils\Currency_List::is_zero_decimal( $order->get_currency() ) ) {
						$sub_order_shipping /= 100;
					}

					$sub_order_total += $sub_order_shipping;
				}

				$result = $wpdb->insert( $wpdb->prefix.'vx_orders', [
					'customer_id' => $order->get_customer_id(),
					'vendor_id' => null,
					'status' => \Voxel\ORDER_COMPLETED,
					'payment_method' => 'stripe_transfer_platform',
					'transaction_id' => null,
					'details' => wp_json_encode( Schema::optimize_for_storage( [
						'pricing' => [
							'currency' => $order->get_currency(),
							'subtotal' => $sub_order_subtotal,
							'shipping' => $sub_order_shipping,
							'total' => $sub_order_total,
						],
					] ) ),
					'parent_id' => $order->get_id(),
					'testmode' => $order->is_test_mode() ? 1 : 0,
					'created_at' => \Voxel\utc()->format( 'Y-m-d H:i:s' ),
				] );

				if ( $result === false ) {
					continue;
				}

				$sub_order_id = (int) $wpdb->insert_id;

				foreach ( $item_group['items'] as $order_item ) {
					$order_item->set_order_id( $sub_order_id );
					$order_item->save();
				}
			}
		}

		$transfer_data = $order->get_details('multivendor.transfer_data');
		if ( ! is_array( $transfer_data ) || empty( $transfer_data ) ) {
			return;
		}

		$stripe = \Voxel\Modules\Stripe_Payments\Stripe_Client::getClient();

		$schema = Schema::Keyed_Object_List( [
			'vendor_id' => Schema::Int()->min(0),
			'subtotal_in_cents' => Schema::Int()->min(0),
			'total_in_cents' => Schema::Int()->min(0)->default(0),
			'fee_in_cents' => Schema::Int()->min(0),
			'shipping_fee_in_cents' => Schema::Int()->min(0),
		] )->default([]);

		$schema->set_value( $transfer_data );
		$transfer_data = $schema->export();

		$payment_intent = $stripe->paymentIntents->retrieve( $order->get_details('payment_intent.id') );
		$latest_charge = $stripe->charges->retrieve( $order->get_details('payment_intent.latest_charge'), [
			'expand' => [ 'balance_transaction' ],
		] );

		if ( ! $latest_charge->balance_transaction ) {
			return;
		}

		foreach ( $transfer_data as $vendor_id => $vendor_data ) {
			if ( $vendor_data['vendor_id'] === null || $vendor_data['total_in_cents'] === null || $vendor_data['fee_in_cents'] === null ) {
				continue;
			}

			$vendor = \Voxel\User::get( $vendor_data['vendor_id'] );
			if ( $vendor === null || ! $vendor->is_active_vendor() ) {
				continue;
			}

			if ( $vendor_data['total_in_cents'] <= $vendor_data['fee_in_cents'] ) {
				continue;
			}

			$sub_order = \Voxel\Product_Types\Orders\Order::find( [
				'vendor_id' => $vendor->get_id(),
				'parent_id' => $order->get_id(),
			] );

			if ( ! $sub_order ) {
				continue;
			}

			if ( $sub_order->get_details( 'transfer.id' ) || $sub_order->get_status() === \Voxel\ORDER_COMPLETED ) {
				continue;
			}

			try {
				/**
				 * Balance transaction currency and transfer currency must match. If they're different,
				 * transfer amount and currency must be converted to the balance currency using the
				 * balance transaction's exchange rate property.
				 *
				 * @link https://docs.stripe.com/api/balance_transactions/object
				 */
				$currency = $latest_charge->balance_transaction->currency;
				$amount_to_transfer = $vendor_data['total_in_cents'] - $vendor_data['fee_in_cents'];
				if ( $payment_intent->currency !== $latest_charge->balance_transaction->currency ) {
					$amount_to_transfer *= $latest_charge->balance_transaction->exchange_rate;
				}

				$amount_to_transfer = (int) round( $amount_to_transfer );

				$transfer = $stripe->transfers->create( [
					'amount' => $amount_to_transfer,
					'currency' => $currency,
					'destination' => $vendor->get_stripe_vendor_id(),
					'transfer_group' => sprintf( 'ORDER_%d', $order->get_id() ),
					'source_transaction' => $order->get_details('payment_intent.latest_charge'),
				], [
					'idempotency_key' => sprintf( 'TRANSFER_ORDER_%d_VENDOR_%d', $order->get_id(), $vendor->get_id() ),
				] );

				$sub_order->set_transaction_id( $transfer->id );
				$sub_order->set_status( \Voxel\ORDER_COMPLETED );
				$sub_order->set_details( 'transfer', [
					'id' => $transfer->id,
					'amount' => $transfer->amount,
					'currency' => $transfer->currency,
					'destination' => $transfer->destination,
					'amount_reversed' => $transfer->amount_reversed,
					'created' => $transfer->created,
					'destination_payment' => $transfer->destination_payment,
					'livemode' => $transfer->livemode,
					'reversed' => $transfer->reversed,
					'source_transaction' => $transfer->source_transaction,
					'source_type' => $transfer->source_type,
					'transfer_group' => $transfer->transfer_group,
				] );

				$total_in_cents = $vendor_data['total_in_cents'];
				if ( $total_in_cents > 0 && ! \Voxel\Utils\Currency_List::is_zero_decimal( $order->get_currency() ) ) {
					$total_in_cents /= 100;
				}
				$sub_order->set_details( 'pricing.total', $total_in_cents );

				$sub_order->save();
			} catch ( \Exception $e ) {
				\Voxel\log( 'Stripe transfer failed: '.$e->getMessage());
			}
		}

	}
}
