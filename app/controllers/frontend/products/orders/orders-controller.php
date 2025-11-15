<?php

namespace Voxel\Controllers\Frontend\Products\Orders;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Orders_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_products.orders.list', '@list' );
		$this->on( 'voxel_ajax_orders.get_stats', '@get_stats' );
		$this->on( 'voxel/product-types/orders/order:updated', '@order_updated' );
	}

	protected function list() {
		try {
			$page = absint( $_GET['pg'] ?? 1 );
			$per_page = 10;
			$status = sanitize_text_field( $_GET['status'] ?? 'all' );
			$shipping_status = sanitize_text_field( $_GET['shipping_status'] ?? 'all' );
			$product_type = sanitize_text_field( $_GET['product_type'] ?? 'all' );
			$search = trim( sanitize_text_field( $_GET['search'] ?? '' ) );

			$args = [
				'limit' => $per_page + 1,
				'offset' => $page <= 1 ? null : ( ( $page - 1 ) * $per_page ),
				'with_items' => false,
				'parent_id' => 0,
			];

			if ( current_user_can( 'administrator' ) ) {
				//
			} else {
				$args['party_id'] = get_current_user_id();
				$args['parent_id'] = null;
			}

			if ( empty( $status ) || $status === 'all' ) {
				$args['status_not_in'] = [ 'pending_payment' ];
			} else {
				$args['status'] = $status;
			}

			if ( ! empty( $shipping_status ) && $shipping_status !== 'all' ) {
				$args['shipping_status'] = $shipping_status;
			}

			if ( ! empty( $product_type ) && $product_type !== 'all' ) {
				$args['product_type'] = $product_type;
			}

			if ( ! empty( $search ) ) {
				$args['search'] = $search;
			}

			$orders = \Voxel\Order::query( $args );
			$has_more = count( $orders ) > $per_page;
			if ( $has_more ) {
				array_pop( $orders );
			}

			$items = [];
			foreach ( $orders as $order ) {
				$customer = $order->get_customer();
				$items[] = [
					'id' => $order->get_id(),
					'item_count' => $order->get_item_count(),
					'status' => $order->get_status(),
					'shipping_status' => $order->get_shipping_status(),
					'currency' => $order->get_currency(),
					'subtotal' => $order->get_subtotal(),
					'total' => $order->get_total(),
					'created_at' => $order->get_created_at_for_display(),
					'customer' => [
						'name' => $customer ? $customer->get_display_name() : _x( '(deleted account)', 'deleted user account', 'voxel' ),
						'avatar' => $customer ? $customer->get_avatar_markup() : null,
						'link' => $customer ? $customer->get_link() : null,
					],
				];
			}

			return wp_send_json( [
				'success' => true,
				'items' => $items,
				'has_more' => $has_more,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function order_updated( $order ) {
		if ( $order->get_previous_status() === \Voxel\ORDER_PENDING_PAYMENT ) {
			if ( in_array( $order->get_status(), [ 'completed', 'pending_approval', 'sub_active' ], true ) ) {
				( new \Voxel\Events\Products\Orders\Customer_Placed_Order_Event )->dispatch( $order->get_id() );
			}
		}

		// refresh vendor stats cache
		if ( $order->get_previous_status() !== $order->get_status() ) {
			$vendors = [];
			if ( $vendor = $order->get_vendor() ) {
				$vendors[ $vendor->get_id() ] = $vendor;
			}

			foreach ( $order->get_items() as $order_item ) {
				if ( $vendor = $order_item->get_vendor() ) {
					$vendors[ $vendor->get_id() ] = $vendor;
				}
			}

			foreach ( $vendors as $vendor ) {
				$vendor_stats = $vendor->get_vendor_stats();

				$vendor_stats->expire_general_stats();
				$vendor_stats->expire_last31_stats();
			}
		}
	}

	protected function get_stats() {
		try {
			$chart = $_GET['chart'] ?? null;
			$direction = ( $_GET['direction'] ?? null ) === 'next' ? 'next' : 'prev';
			$date = strtotime( $_GET['date'] ?? null );

			if ( ! in_array( $chart, [ 'this-week', 'this-month', 'this-year' ], true ) || ! $date ) {
				throw new \Exception( __( 'Invalid request.', 'voxel' ) );
			}

			$user = \Voxel\current_user();
			$stats = $user->get_vendor_stats();

			if ( $chart === 'this-week' ) {
				$change = $direction === 'next' ? '+7 days' : '-7 days';
				$data = $stats->get_week_chart( date( 'Y-m-d', strtotime( $change, $date ) ) );
			} elseif ( $chart === 'this-month' ) {
				$change = $direction === 'next' ? '+1 month' : '-1 month';
				$data = $stats->get_month_chart( date( 'Y-m-01', strtotime( $change, $date ) ) );
			} else {
				$change = $direction === 'next' ? '+1 year' : '-1 year';
				$data = $stats->get_year_chart( (int) date( 'Y', strtotime( $change, $date ) ) );
			}

			return wp_send_json( [
				'success' => true,
				'data' => $data,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}
}