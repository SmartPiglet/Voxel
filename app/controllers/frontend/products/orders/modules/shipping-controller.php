<?php

namespace Voxel\Controllers\Frontend\Products\Orders\Modules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Shipping_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_products.single_order.shipping.mark_as_shipped', '@mark_as_shipped' );
		$this->on( 'voxel_ajax_products.single_order.shipping.mark_as_delivered', '@mark_as_delivered' );
		$this->on( 'voxel_ajax_products.single_order.shipping.share_details', '@share_details' );
		$this->on( 'voxel/product-types/orders/order:updated', '@order_updated' );
		$this->filter( 'voxel/orders/view_order/components', '@register_shipping_component', 10, 2 );
	}

	protected function order_updated( $order ) {
		if ( $order->get_status() && $order->should_handle_shipping() && $order->get_shipping_status() === null ) {
			$order->set_shipping_status('processing');
			$order->save();
		}
	}

	protected function mark_as_shipped() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 99 );
			}

			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_orders' );

			$order_id = absint( $_REQUEST['order_id'] ?? null );
			if ( ! $order_id ) {
				throw new \Exception( _x( 'Missing order id.', 'orders', 'voxel' ), 107 );
			}

			$current_user = \Voxel\get_current_user();
			$order = \Voxel\Product_Types\Orders\Order::get( $order_id );
			if ( ! ( $order && $order->get_status() === 'completed' ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 108 );
			}

			if ( ! $current_user->is_vendor_of( $order->get_id() ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 111 );
			}

			$order->set_shipping_status( 'shipped' );
			$order->save();

			( new \Voxel\Events\Products\Orders\Shipping\Vendor_Marked_Shipped_Event )->dispatch( $order->get_id() );

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function mark_as_delivered() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 99 );
			}

			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_orders' );

			$order_id = absint( $_REQUEST['order_id'] ?? null );
			if ( ! $order_id ) {
				throw new \Exception( _x( 'Missing order id.', 'orders', 'voxel' ), 107 );
			}

			$current_user = \Voxel\get_current_user();
			$order = \Voxel\Product_Types\Orders\Order::get( $order_id );
			if ( ! ( $order && $order->get_status() === 'completed' && $order->get_shipping_status() === 'shipped' ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 108 );
			}

			$order->set_shipping_status( 'delivered' );
			$order->save();

			if ( $current_user->is_customer_of( $order->get_id() ) ) {
				( new \Voxel\Events\Products\Orders\Shipping\Customer_Marked_Delivered_Event )->dispatch( $order->get_id() );
			} else {
				( new \Voxel\Events\Products\Orders\Shipping\Vendor_Marked_Delivered_Event )->dispatch( $order->get_id() );
			}

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function share_details() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 99 );
			}

			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_orders' );

			$order_id = absint( $_REQUEST['order_id'] ?? null );
			if ( ! $order_id ) {
				throw new \Exception( _x( 'Missing order id.', 'orders', 'voxel' ), 107 );
			}

			$current_user = \Voxel\get_current_user();
			$order = \Voxel\Product_Types\Orders\Order::get( $order_id );
			if ( ! ( $order && $order->get_status() === 'completed' ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 108 );
			}

			if ( ! $current_user->is_vendor_of( $order->get_id() ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 111 );
			}

			$tracking_link = sanitize_url( $_REQUEST['tracking_link'] ?? null );
			if ( empty( $tracking_link ) ) {
				$tracking_link = null;
			}

			$order->set_details( 'shipping.tracking_details.link', $tracking_link );
			$order->save();

			if ( $tracking_link !== null ) {
				( new \Voxel\Events\Products\Orders\Shipping\Vendor_Shared_Tracking_Event )->dispatch( $order->get_id() );
			}

			return wp_send_json( [
				'success' => true,
				'tracking_link' => $tracking_link,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function register_shipping_component( $components, $order ) {
		if ( ! ( $order->get_status() === 'completed' && $order->should_handle_shipping() ) ) {
			return $components;
		}

		$data = [];

		$data['enabled'] = true;
		$data['status'] = [
			'key' => $order->get_shipping_status(),
			'updated_at' => $order->get_shipping_status_last_updated_for_display(),
			'label' => $order->get_shipping_status_label(),
			'long_label' => $order->get_shipping_status_long_label(),
			'class' => $order->get_shipping_status_class(),
		];

		$data['tracking_details'] = [
			'link' => $order->get_details('shipping.tracking_details.link'),
		];

		$data['l10n'] = [
			'mark_shipped' => _x( 'Mark as shipped', 'single order', 'voxel' ),
			'share_cancel' => _x( 'Cancel', 'share tracking details', 'voxel' ),
			'share_confirm' => _x( 'Share', 'share tracking details', 'voxel' ),
			'update_tracking_link' => _x( 'Update tracking link', 'single order', 'voxel' ),
			'share_tracking_link' => _x( 'Share tracking link', 'single order', 'voxel' ),
			'tracking_url' => _x( 'Tracking URL', 'share tracking details', 'voxel' ),
			'track_order' => _x( 'Track order', 'single order', 'voxel' ),
			'mark_delivered' => _x( 'Mark as delivered', 'single order', 'voxel' ),
		];

		$components[] = [
			'type' => 'order-shipping-details',
			'src' => \Voxel\get_esm_src('order-shipping-details.js'),
			'data' => $data,
		];

		return $components;
	}
}
