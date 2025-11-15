<?php

namespace Voxel\Product_Types\Payment_Methods;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Stripe_Transfer extends Base_Payment_Method {

	public function get_type(): string {
		return 'stripe_transfer';
	}

	public function get_label(): string {
		return _x( 'Stripe vendor transfer', 'payment methods', 'voxel' );
	}

	public function process_payment() {
		throw new \Exception( 'This payment method cannot be used in checkout' );
	}

	public function should_sync(): bool {
		return false;
	}

	public function sync(): void {
		//
	}

	public function get_vendor_fees_summary(): array {
		$parent_order = $this->order->get_parent_order();
		if ( ! $parent_order ) {
			return [];
		}

		$payment_method = $parent_order->get_payment_method();
		if ( $payment_method === null || $payment_method->get_type() !== 'stripe_payment' ) {
			return [];
		}

		$vendor_fees = (array) $parent_order->get_details( sprintf( 'multivendor.transfer_data.%d.vendor_fees', $this->order->get_vendor_id() ), [] );
		$fee_in_cents = $parent_order->get_details( sprintf( 'multivendor.transfer_data.%d.fee_in_cents', $this->order->get_vendor_id() ) );
		$currency = $this->order->get_currency();

		if ( ! is_numeric( $fee_in_cents ) ) {
			return [];
		}

		if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $currency ) ) {
			$fee_in_cents /= 100;
		}

		$details = [
			'total' => $fee_in_cents,
			'breakdown' => [],
		];

		foreach ( (array) $vendor_fees as $fee ) {
			if ( ( $fee['type'] ?? null ) === 'fixed' ) {
				if ( ! is_numeric( $fee['fixed_amount'] ?? null ) && $fee['fixed_amount'] > 0 ) {
					continue;
				}

				$details['breakdown'][] = [
					'label' => $fee['label'] ?? _x( 'Platform fee', 'vendor fees', 'voxel' ),
					'content' => \Voxel\currency_format( $fee['fixed_amount'], $currency, false ),
				];
			} elseif ( ( $fee['type'] ?? null ) === 'percentage' ) {
				if ( ! is_numeric( $fee['percentage_amount'] ?? null ) && $fee['percentage_amount'] > 0 && $fee['percentage_amount'] <= 100 ) {
					continue;
				}

				$details['breakdown'][] = [
					'label' => $fee['label'] ?? _x( 'Platform fee', 'vendor fees', 'voxel' ),
					'content' => round( $fee['percentage_amount'], 2 ).'%',
				];
			}
		}

		$shipping_fee_in_cents = $parent_order->get_details( sprintf( 'multivendor.transfer_data.%d.shipping_fee_in_cents', $this->order->get_vendor_id() ) );
		if ( is_numeric( $shipping_fee_in_cents ) ) {
			$shipping_fee = $shipping_fee_in_cents;
			if ( ! \Voxel\Utils\Currency_List::is_zero_decimal( $currency ) ) {
				$shipping_fee /= 100;
			}

			$details['breakdown'][] = [
				'label' => _x( 'Shipping fee', 'vendor fees', 'voxel' ),
				'content' => \Voxel\currency_format( $shipping_fee, $currency, false ),
			];
		}

		return $details;
	}

	public function get_customer_details(): array {
		$parent_order = $this->order->get_parent_order();
		if ( ! $parent_order ) {
			return [];
		}

		$payment_method = $parent_order->get_payment_method();
		if ( $payment_method === null || $payment_method->get_type() !== 'stripe_payment' ) {
			return [];
		}

		return $payment_method->get_customer_details();
	}

	public function get_shipping_details(): array {
		$parent_order = $this->order->get_parent_order();
		if ( ! $parent_order ) {
			return [];
		}

		$payment_method = $parent_order->get_payment_method();
		if ( $payment_method === null || $payment_method->get_type() !== 'stripe_payment' ) {
			return [];
		}

		return $payment_method->get_shipping_details();
	}
}
