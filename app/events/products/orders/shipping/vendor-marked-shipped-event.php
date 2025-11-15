<?php

namespace Voxel\Events\Products\Orders\Shipping;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Vendor_Marked_Shipped_Event extends \Voxel\Events\Products\Orders\Base_Order_Event {

	static $default_enabled = [
		'customer' => true,
		'vendor' => false,
		'admin' => false,
	];

	public function get_key(): string {
		return 'products/orders/shipping/vendor:marked_shipped';
	}

	public function get_label(): string {
		return 'Shipping: Vendor marked shipped';
	}

	public static function get_customer_subject() {
		return 'Your order has been shipped.';
	}

	public static function get_customer_message() {
		return <<<HTML
		Order #@order(:id): Your order has been shipped.
		<a href="@order(:link)">View order</a>
		HTML;
	}

	public static function get_vendor_subject() {
		return 'Order #@order(:id) has been shipped.';
	}

	public static function get_vendor_message() {
		return <<<HTML
		Order #@order(:id) has been shipped.
		<a href="@order(:link)">View order</a>
		HTML;
	}

	public static function get_admin_subject() {
		return 'Order #@order(:id) has been shipped by the vendor.';
	}

	public static function get_admin_message() {
		return <<<HTML
		Order #@order(:id) has been shipped by the vendor.
		<a href="@order(:link)">View order</a>
		HTML;
	}
}
