<?php

namespace Voxel\Modules\Paid_Listings\Controllers\Frontend;

use \Voxel\Modules\Paid_Listings as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Switch_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_paid_listings.switch_listing_plan', '@switch_listing_plan' );
		$this->on( 'voxel/product-types/orders/order:updated', '@order_updated', 150 );
	}

	protected function switch_listing_plan() {
		try {
			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_switch_listing_plan' );

			$post = \Voxel\Post::get( $_GET['post_id'] ?? null );
			$user = \Voxel\get_current_user();
			if ( ! (
				$post
				&& $post->post_type
				&& $post->is_editable_by_current_user()
				&& $post->get_status() === 'publish'
				&& Module\has_plans_for_post_type( $post->post_type )
			) ) {
				throw new \Exception( __( 'No plans available.', 'voxel' ) );
			}

			$redirect_to = add_query_arg( [
				'process' => 'switch',
				'post_id' => $post->get_id(),
				'redirect_to' => urlencode( wp_get_referer() ),
			], get_permalink( \Voxel\get('paid_listings.settings.templates.pricing') ) );

			return wp_send_json( [
				'success' => true,
				'redirect_to' => $redirect_to,
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
				if ( ( $checkout_context['process'] ?? null ) !== 'switch' ) {
					continue;
				}

				$order_item->set_details( 'voxel:checkout_context.handled', true );
				$order_item->save();

				$post = \Voxel\Post::get( $checkout_context['post_id'] ?? null );

				if ( ! (
					$post
					&& $post->post_type
					&& $post->is_editable_by_user( $customer )
					&& $post->get_status() === 'publish'
				) ) {
					continue;
				}

				if ( ! $package->can_create_post( $post->post_type ) ) {
					continue;
				}

				$package->assign_to_post( $post );
			}
		}
	}

}
