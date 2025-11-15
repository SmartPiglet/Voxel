<?php

namespace Voxel\Events\Products\Orders;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Vendor_Declined_Order_Event extends Base_Order_Event {

	static $default_enabled = [
		'customer' => true,
		'vendor' => false,
		'admin' => false,
	];

	public function get_key(): string {
		return 'products/orders/vendor:order_declined';
	}

	public function get_label(): string {
		return 'Order declined by vendor';
	}

	public static function get_customer_subject() {
		return 'Order #@order(:id): Your order has been declined by the vendor.';
	}

	public static function get_customer_message() {
		return <<<HTML
		Order #@order(:id): Your order has been declined by the vendor.
		<a href="@order(:link)">View order</a>
		HTML;
	}

	public static function get_vendor_subject() {
		return 'Order #@order(:id) has been declined.';
	}

	public static function get_vendor_message() {
		return <<<HTML
		Order #@order(:id) has been declined.
		<a href="@order(:link)">View order</a>
		HTML;
	}

	public static function get_admin_subject() {
		return 'Order #@order(:id) has been declined by the vendor.';
	}

	public static function get_admin_message() {
		return <<<HTML
		Order #@order(:id) has been declined by the vendor.
		<a href="@order(:link)">View order</a>
		HTML;
	}
}
