<?php

namespace Voxel\Events\Products\Orders;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Customer_Placed_Order_Event extends Base_Order_Event {

	static $default_enabled = [
		'customer' => false,
		'vendor' => true,
		'admin' => false,
	];

	public function get_key(): string {
		return 'products/orders/customer:order_placed';
	}

	public function get_label(): string {
		return 'New order placed by customer';
	}

	public static function get_customer_subject() {
		return 'Your order has been placed.';
	}

	public static function get_customer_message() {
		return <<<HTML
		Your order has been placed.
		<a href="@order(:link)">View order</a>
		HTML;
	}

	public static function get_vendor_subject() {
		return 'New order placed by @customer(:display_name)';
	}

	public static function get_vendor_message() {
		return <<<HTML
		A new order has been placed by <strong>@customer(:display_name)</strong>.
		<a href="@order(:link)">View order</a>
		HTML;
	}

	public static function get_admin_subject() {
		return 'New order placed by @customer(:display_name)';
	}

	public static function get_admin_message() {
		return <<<HTML
		A new order has been placed by <strong>@customer(:display_name)</strong>.
		<a href="@order(:link)">View order</a>
		HTML;
	}
}
