<?php

namespace Voxel\Product_Types\Payment_Methods;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Stripe_Transfer_Platform extends Base_Payment_Method {

	public function get_type(): string {
		return 'stripe_transfer_platform';
	}

	public function get_label(): string {
		return _x( 'Stripe platform transfer', 'payment methods', 'voxel' );
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
