<?php

namespace Voxel\Events\Products\Orders\Shipping;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Vendor_Marked_Delivered_Event extends \Voxel\Events\Products\Orders\Base_Order_Event {

	static $default_enabled = [
		'customer' => true,
		'vendor' => false,
		'admin' => false,
	];

	public function get_key(): string {
		return 'products/orders/shipping/vendor:marked_delivered';
	}

	public function get_label(): string {
		return 'Shipping: Vendor marked delivered';
	}

	public static function get_customer_subject() {
		return 'Your order has been delivered.';
	}

	public static function get_customer_message() {
		return <<<HTML
		Order #@order(:id): Your order has been delivered.
		<a href="@order(:link)">View order</a>
		HTML;
	}

	public static function get_vendor_subject() {
		return 'Order #@order(:id) has been delivered.';
	}

	public static function get_vendor_message() {
		return <<<HTML
		Order #@order(:id) has been delivered.
		<a href="@order(:link)">View order</a>
		HTML;
	}

	public static function get_admin_subject() {
		return 'Order #@order(:id) has been delivered by the vendor.';
	}

	public static function get_admin_message() {
		return <<<HTML
		Order #@order(:id) has been delivered by the vendor.
		<a href="@order(:link)">View order</a>
		HTML;
	}
}
