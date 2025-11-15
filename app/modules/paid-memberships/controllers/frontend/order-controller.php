<?php

namespace Voxel\Modules\Paid_Memberships\Controllers\Frontend;

use \Voxel\Modules\Paid_Memberships as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Order_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel/product-types/orders/order:updated', '@order_updated' );
		$this->filter( 'voxel/order_item/product_description', '@set_product_description', 10, 2 );
		$this->filter( 'voxel/order_item/product_link', '@set_product_link', 10, 2 );
	}

	protected function order_updated( $order ) {
		if ( $order->get_status() === 'pending_payment' ) {
			return;
		}

		$customer = $order->get_customer();
		if ( ! $customer ) {
			return;
		}

		$payment_method = $order->get_payment_method();
		if ( ! ( $payment_method && $payment_method->is_subscription() ) ) {
			return;
		}

		foreach ( $order->get_items() as $order_item ) {
			if ( $order_item->get_product_field_key() !== 'voxel:membership_plan' ) {
				continue;
			}

			$meta_key = $order->is_test_mode() ? 'voxel:test_plan' : 'voxel:plan';
			$plan_key = $order_item->get_details('voxel:membership.plan');
			$price_key = $order_item->get_details('voxel:membership.price');

			$billing_interval = $payment_method->get_billing_interval();
			$current_period = $payment_method->get_current_billing_period();

			$details = [
				'plan' => $plan_key,
				'type' => 'order',
				'order_id' => $order->get_id(),
				'order_item_id' => $order_item->get_id(),
				'billing' => [
					'price_key' => $price_key,
					'amount' => $order->get_total(),
					'currency' => $order->get_currency(),
					'interval' => $billing_interval['interval'] ?? null,
					'frequency' => $billing_interval['interval_count'] ?? null,
					'current_period' => [
						'start' => $current_period['start'] ?? null,
						'end' => $current_period['end'] ?? null,
					],
					'is_active' => $payment_method->is_subscription_active(),
					'is_canceled' => $payment_method->is_subscription_canceled(),
					// 'is_recoverable' => $payment_method->is_subscription_recoverable(),
				],
			];

			if ( $payment_method->is_subscription_active() ) {
				Module\update_user_plan(
					user_id: $customer->get_id(),
					details: $details,
					is_test_mode: $order->is_test_mode(),
				);

				// maybe switch role
				$switch_role_key = $order_item->get_details('voxel:membership.switch_role');
				if ( ! empty( $switch_role_key ) ) {
					$this->maybe_switch_role( $customer, $order_item, $order );
				}
			} elseif ( $payment_method->is_subscription_recoverable() ) {
				// subscription can still be reactivated through the order page
				Module\update_user_plan(
					user_id: $customer->get_id(),
					details: $details,
					is_test_mode: $order->is_test_mode(),
				);
			} else {
				// subscription canceled, update user meta only if meta.order_id = order.id
				$existing_details = (array) json_decode(
					\Voxel\get_site_specific_user_meta( $customer->get_id(), $meta_key, true ),
					true
				);

				if (
					( $existing_details['type'] ?? null ) === 'order'
					&& ( $existing_details['order_id'] ?? null ) === $order->get_id()
				) {
					Module\update_user_plan(
						user_id: $customer->get_id(),
						details: $details,
						is_test_mode: $order->is_test_mode(),
					);
				}
			}
		}
	}

	protected function maybe_switch_role( $customer, $order_item, $order ) {
		if ( $order_item->get_meta('_processed_role_switch') ) {
			return;
		}

		$switch_role_key = $order_item->get_details('voxel:membership.switch_role');

		$switch_role = \Voxel\Role::get( $switch_role_key );
		if ( ! ( $switch_role && $switch_role->is_switching_enabled() ) ) {
			return;
		}

		$switchable_roles = $customer->get_switchable_roles();
		if ( ! isset( $switchable_roles[ $switch_role->get_key() ] ) ) {
			return;
		}

		$membership = $customer->get_membership();
		if ( ! $membership->get_active_plan()->supports_role( $switch_role->get_key() ) ) {
			return;
		}

		if ( $customer->has_role('administrator') || $customer->has_role('editor') ) {
			return;
		}

		if ( $customer->has_role( $switch_role->get_key() ) ) {
			return;
		}

		$customer->set_role( $switch_role->get_key() );

		$order_item->set_meta('_processed_role_switch', true);
		$order_item->save();

		// \Voxel\log( sprintf( 'Customer "%s" role updated to "%s"', $customer->get_username(), $switch_role->get_key() ) );
	}

	protected function set_product_description( $description, $order_item ) {
		if ( $order_item->get_product_field_key() === 'voxel:membership_plan' ) {
			$plan_key = $order_item->get_details('voxel:membership.plan');

			$plan = Module\Plan::get( $plan_key );

			if ( $plan ) {
				return join( ', ', array_filter( [
					$description,
					sprintf( _x( 'Membership plan: %s', 'order item', 'voxel' ), $plan->get_label() ),
				] ) );
			} else {
				return join( ', ', array_filter( [
					$description,
					_x( 'Membership plan', 'order item', 'voxel' ),
				] ) );
			}
		}

		return $description;
	}


	protected function set_product_link( $link, $order_item ) {
		if ( $order_item->get_product_field_key() === 'voxel:membership_plan' ) {
			return null;
		}

		return $link;
	}
}
