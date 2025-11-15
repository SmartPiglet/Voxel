<?php

namespace Voxel\Controllers\Frontend\Products\Orders\Modules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Stock_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel/product-types/orders/order:updated', '@order_updated' );
	}

	protected function order_updated( $order ) {
		if ( in_array( $order->get_status(), [ 'completed', 'sub_active' ], true ) ) {
			foreach ( $order->get_items() as $order_item ) {
				if ( $order_item->get_type() === 'regular' ) {
					$order_item->reduce_stock();
				} elseif ( $order_item->get_type() === 'variable' ) {
					$order_item->reduce_stock();
				} elseif ( $order_item->get_type() === 'booking' ) {
					$order_item->reduce_stock();
				}
			}
		}
	}
}
