<?php

namespace Voxel\Modules\Paid_Memberships\Membership;

use \Voxel\Product_Types\Orders\Order;
use \Voxel\Product_Types\Payment_Methods\Base_Payment_Method as Payment_Method;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Membership_Order extends Base_Membership {

	protected $details;

	public function get_type(): string {
		return 'order';
	}

	protected function init( array $details ) {
		$this->details = $details;
	}

	public function is_active(): bool {
		return !! ( $this->details['billing']['is_active'] ?? false );
	}

	public function is_canceled(): bool {
		return !! ( $this->details['billing']['is_canceled'] ?? false );
	}

	public function get_order_id(): ?int {
		return is_numeric( $this->details['order_id'] ?? null )
			? absint( $this->details['order_id'] )
			: null;
	}

	public function get_order_item_id(): ?int {
		return is_numeric( $this->details['order_item_id'] ?? null )
			? absint( $this->details['order_item_id'] )
			: null;
	}

	public function get_order(): ?Order {
		return Order::get( $this->get_order_id() );
	}

	public function get_amount() {
		return $this->details['billing']['amount'] ?? null;
	}

	public function get_currency() {
		return $this->details['billing']['currency'] ?? null;
	}

	public function get_price_key() {
		return $this->details['billing']['price_key'] ?? null;
	}

	public function get_interval() {
		return $this->details['billing']['interval'] ?? null;
	}

	public function get_frequency() {
		return $this->details['billing']['frequency'] ?? null;
	}

	public function get_current_period_start(): ?string {
		$start = $this->details['billing']['current_period']['start'] ?? null;
		if ( ! is_string( $start ) || empty( $start ) ) {
			return null;
		}

		$timestamp = strtotime( $start );
		if ( ! $timestamp ) {
			return null;
		}

		return date( 'Y-m-d H:i:s', $timestamp );
	}

	public function get_current_period_end(): ?string {
		$end = $this->details['billing']['current_period']['end'] ?? null;
		if ( ! is_string( $end ) || empty( $end ) ) {
			return null;
		}

		$timestamp = strtotime( $end );
		if ( ! $timestamp ) {
			return null;
		}

		return date( 'Y-m-d H:i:s', $timestamp );
	}

	public function get_payment_method(): ?Payment_Method {
		$order = $this->get_order();
		if ( ! $order ) {
			return null;
		}

		$payment_method = $order->get_payment_method();
		if ( ! ( $payment_method && $payment_method->is_subscription() ) ) {
			return null;
		}

		return $payment_method;
	}

}
