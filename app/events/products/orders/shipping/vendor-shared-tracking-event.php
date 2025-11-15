<?php

namespace Voxel\Events\Products\Orders\Shipping;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Vendor_Shared_Tracking_Event extends \Voxel\Events\Products\Orders\Base_Order_Event {

	static $default_enabled = [
		'customer' => true,
		'vendor' => false,
		'admin' => false,
	];

	public function get_key(): string {
		return 'products/orders/shipping/vendor:shared_tracking';
	}

	public function get_label(): string {
		return 'Shipping: Vendor shared tracking link';
	}

	public static function get_customer_subject() {
		return 'Tracking link available for order #@order(:id)';
	}

	public static function get_customer_message() {
		return <<<HTML
		Order #@order(:id): Tracking link shared by the vendor.
		<a href="@order(:shipping.tracking_link)">Track order</a>
		<a href="@order(:link)">View order</a>
		HTML;
	}

	public static function get_vendor_subject() {
		return 'Tracking link shared for order #@order(:id)';
	}

	public static function get_vendor_message() {
		return <<<HTML
		Order #@order(:id): Tracking link shared by the vendor.
		<a href="@order(:shipping.tracking_link)">Track order</a>
		<a href="@order(:link)">View order</a>
		HTML;
	}

	public static function get_admin_subject() {
		return 'Tracking link shared for order #@order(:id)';
	}

	public static function get_admin_message() {
		return <<<HTML
		Order #@order(:id): Tracking link shared by the vendor.
		<a href="@order(:shipping.tracking_link)">Track order</a>
		<a href="@order(:link)">View order</a>
		HTML;
	}
}
