<?php

namespace Voxel\Events\Products\Orders;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Customer_Canceled_Order_Event extends Base_Order_Event {

	static $default_enabled = [
		'customer' => false,
		'vendor' => true,
		'admin' => false,
	];

	public function get_key(): string {
		return 'products/orders/customer:order_canceled';
	}

	public function get_label(): string {
		return 'Order canceled by customer';
	}

	public static function get_customer_subject() {
		return 'You have canceled order #@order(:id).';
	}

	public static function get_customer_message() {
		return <<<HTML
		You have canceled order #@order(:id).
		<a href="@order(:link)">View order</a>
		HTML;
	}

	public static function get_vendor_subject() {
		return 'Order #@order(:id) has been canceled by the customer.';
	}

	public static function get_vendor_message() {
		return <<<HTML
		Order #@order(:id) has been canceled by the customer.
		<a href="@order(:link)">View order</a>
		HTML;
	}

	public static function get_admin_subject() {
		return 'Order #@order(:id) has been canceled by the customer.';
	}

	public static function get_admin_message() {
		return <<<HTML
		Order #@order(:id) has been canceled by the customer.
		<a href="@order(:link)">View order</a>
		HTML;
	}
}
