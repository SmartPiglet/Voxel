<?php

namespace Voxel\Controllers\Frontend\Products\Orders\Modules;

use Voxel\Utils\Config_Schema\Schema as Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Promotion_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel/product-types/orders/order:updated', '@order_updated' );
		$this->on( 'voxel_ajax_products.single_order.promotions.cancel_promotion', '@cancel_promotion' );
	}

	protected function order_updated( $order ) {
		$schema = Schema::Object( [
			'key' => Schema::String(),
			'duration' => Schema::Object( [
				'type' => Schema::Enum( ['days'] ),
				'amount' => Schema::Int()->min(1),
			] ),
			'priority' => Schema::Int()->min(1)->default(2),
		] );

		if ( in_array( $order->get_status(), [ 'completed', 'sub_active' ], true ) ) {
			foreach ( $order->get_items() as $order_item ) {
				if ( ! ( $order_item->get_type() === 'regular' && $order_item->get_product_field_key() === 'voxel:promotion' ) ) {
					continue;
				}

				$post = $order_item->get_post();
				if ( ! $post ) {
					continue;
				}

				if ( $order_item->get_details( 'promotion.start_date' ) ) {
					continue;
				}

				$schema->set_value( $order_item->get_details( 'promotion_package' ) );
				$config = $schema->export();

				$details = [
					'package_key' => $config['key'],
					'order_id' => $order->get_id(),
					'priority' => $config['priority'],
					'start_date' => \Voxel\utc()->format( 'Y-m-d H:i:s' ),
					'end_date' => \Voxel\utc()->modify('+'.($config['duration']['amount']).' days')->format('Y-m-d H:i:s'),
					'status' => 'active',
				];

				// store promotion details in postmeta
				update_post_meta( $post->get_id(), 'voxel:promotion', wp_unslash( wp_json_encode( $details ) ) );

				// reindex post
				$post = \Voxel\Post::force_get( $post->get_id() );
				$post->should_index() ? $post->index() : $post->unindex();

				// store state in order item meta
				$order_item->set_details( 'promotion.start_date', $details['start_date'] );
				$order_item->set_details( 'promotion.end_date', $details['end_date'] );
				$order_item->save();

				( new \Voxel\Events\Promotions\Promotion_Activated_Event )->dispatch( $order_item->get_id() );
			}
		} else {
			foreach ( $order->get_items() as $order_item ) {
				if ( ! ( $order_item->get_type() === 'regular' && $order_item->get_product_field_key() === 'voxel:promotion' ) ) {
					continue;
				}

				$post = $order_item->get_post();
				if ( ! $post ) {
					continue;
				}

				if ( ! $order_item->get_details( 'promotion.start_date' ) ) {
					continue;
				}

				$schema->set_value( $order_item->get_details( 'promotion_package' ) );
				$config = $schema->export();

				$details = (array) json_decode( get_post_meta( $post->get_id(), 'voxel:promotion', true ), true );

				if ( empty( $config['key'] ) || $config['key'] !== ( $details['package_key'] ?? null ) ) {
					continue;
				}

				$details['status'] = 'inactive';

				// store promotion details in postmeta
				update_post_meta( $post->get_id(), 'voxel:promotion', wp_unslash( wp_json_encode( $details ) ) );

				// reindex post
				$post = \Voxel\Post::force_get( $post->get_id() );
				$post->should_index() ? $post->index() : $post->unindex();
			}
		}
	}

	protected function cancel_promotion() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 99 );
			}

			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_orders' );

			$order_id = absint( $_REQUEST['order_id'] ?? null );
			if ( ! $order_id ) {
				throw new \Exception( _x( 'Missing order id.', 'orders', 'voxel' ), 107 );
			}

			$order = \Voxel\Product_Types\Orders\Order::find( [
				'id' => $order_id,
				'party_id' => get_current_user_id(),
				'status' => [ 'completed', 'sub_active' ],
			] );

			if ( ! $order ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ), 108 );
			}

			foreach ( $order->get_items() as $order_item ) {
				if ( ! ( $order_item->get_type() === 'regular' && $order_item->get_product_field_key() === 'voxel:promotion' ) ) {
					continue;
				}

				$post = $order_item->get_post();
				if ( ! $post ) {
					continue;
				}

				$details = (array) json_decode( get_post_meta( $post->get_id(), 'voxel:promotion', true ), true );

				if ( ( $details['order_id'] ?? null ) !== $order->get_id() || ( $details['status'] ?? null ) !== 'active' ) {
					continue;
				}

				$details['status'] = 'canceled';

				update_post_meta( $post->get_id(), 'voxel:promotion', wp_unslash( wp_json_encode( $details ) ) );

				$post = \Voxel\Post::force_get( $post->get_id() );
				$post->should_index() ? $post->index() : $post->unindex();

				$order_item->set_details( 'promotion.canceled_at', \Voxel\utc()->format( 'Y-m-d H:i:s' ) );
				$order_item->save();

				( new \Voxel\Events\Promotions\Promotion_Canceled_Event )->dispatch( $order_item->get_id() );

				return wp_send_json( [
					'success' => true,
				] );
			}

			throw new \Exception( __( 'Something went wrong', 'voxel' ), 119 );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}
}
