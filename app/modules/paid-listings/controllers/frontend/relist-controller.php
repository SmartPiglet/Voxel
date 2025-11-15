<?php

namespace Voxel\Modules\Paid_Listings\Controllers\Frontend;

use \Voxel\Modules\Paid_Listings as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Relist_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_paid_listings.relist_post', '@relist_post' );
		$this->on( 'voxel/product-types/orders/order:updated', '@order_updated', 150 );
	}

	protected function relist_post() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_relist_post' );

			$post = \Voxel\Post::get( $_GET['post_id'] ?? null );
			$user = \Voxel\get_current_user();
			if ( ! (
				$post
				&& $post->post_type
				&& $post->is_editable_by_current_user()
				&& $post->get_status() === 'expired'
			) ) {
				throw new \Exception( __( 'Only expired posts can be relisted using this action.', 'voxel' ) );
			}

			if ( ! Module\has_plans_for_post_type( $post->post_type ) ) {
				if ( $user->can_create_post( $post->post_type->get_key() ) ) {
					wp_update_post( [
						'ID' => $post->get_id(),
						'post_status' => 'publish',
						'post_date' => current_time( 'mysql' ),
						'post_date_gmt' => current_time( 'mysql', true ),
					] );

					return wp_send_json( [
						'success' => true,
						'redirect_to' => '(reload)',
					] );
				} else {
					throw new \Exception( __( 'This item cannot be relisted currently.', 'voxel' ) );
				}
			} else {
				$redirect_to = add_query_arg( [
					'process' => 'relist',
					'post_id' => $post->get_id(),
					'redirect_to' => urlencode( wp_get_referer() ),
				], get_permalink( \Voxel\get('paid_listings.settings.templates.pricing') ) );

				return wp_send_json( [
					'success' => true,
					'redirect_to' => $redirect_to,
				] );
			}
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function order_updated( $order ) {
		if ( ! in_array( $order->get_status(), [ 'completed', 'sub_active', 'sub_trialing' ], true ) ) {
			return;
		}

		foreach ( $order->get_items() as $order_item ) {
			if ( $package = Module\Listing_Package::get( $order_item ) ) {
				if ( $order_item->get_details('voxel:checkout_context.handled') ) {
					continue;
				}

				$customer = $order->get_customer();
				if ( ! $customer ) {
					continue;
				}

				$checkout_context = $order_item->get_details( 'voxel:checkout_context' );
				if ( ( $checkout_context['process'] ?? null ) !== 'relist' ) {
					continue;
				}

				$order_item->set_details( 'voxel:checkout_context.handled', true );
				$order_item->save();

				$post = \Voxel\Post::get( $checkout_context['post_id'] ?? null );

				if ( ! (
					$post
					&& $post->post_type
					&& $post->is_editable_by_user( $customer )
					&& $post->get_status() === 'expired'
				) ) {
					continue;
				}

				if ( ! $package->can_create_post( $post->post_type ) ) {
					continue;
				}

				wp_update_post( [
					'ID' => $post->get_id(),
					'post_status' => 'publish',
					'post_date' => current_time( 'mysql' ),
					'post_date_gmt' => current_time( 'mysql', true ),
				] );

				$package->assign_to_post( $post );
			}
		}
	}

}
