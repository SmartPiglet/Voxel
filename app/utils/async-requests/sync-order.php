<?php

namespace Voxel\Utils\Async_Requests;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Sync_Order extends WP_Async_Request {

	protected $action = 'sync_order';

	protected function handle() {
		$order_id = $_POST['order_id'] ?? null;
		if ( is_numeric( $order_id ) ) {
			$order = \Voxel\Product_Types\Orders\Order::find( [
				'id' => absint( $order_id ),
			] );

			if ( $order && $order->get_payment_method() ) {
				$order->get_payment_method()->sync();
			}
		}
	}
}
