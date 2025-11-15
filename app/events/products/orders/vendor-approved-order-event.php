<?php

namespace Voxel\Events\Products\Orders;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Vendor_Approved_Order_Event extends Base_Order_Event {

	static $default_enabled = [
		'customer' => true,
		'vendor' => false,
		'admin' => false,
	];

	public function get_key(): string {
		return 'products/orders/vendor:order_approved';
	}

	public function get_label(): string {
		return 'Order approved by vendor';
	}

	public static function get_customer_subject() {
		return 'Your order has been approved.';
	}

	public static function get_customer_message() {
		return <<<HTML
		Order #@order(:id): Your order has been approved.
		<a href="@order(:link)">View order</a>
		HTML;
	}

	public static function get_vendor_subject() {
		return 'Order #@order(:id) has been approved.';
	}

	public static function get_vendor_message() {
		return <<<HTML
		Order #@order(:id) has been approved.
		<a href="@order(:link)">View order</a>
		HTML;
	}

	public static function get_admin_subject() {
		return 'Order #@order(:id) has been approved by the vendor.';
	}

	public static function get_admin_message() {
		return <<<HTML
		Order #@order(:id) has been approved by the vendor.
		<a href="@order(:link)">View order</a>
		HTML;
	}
}
