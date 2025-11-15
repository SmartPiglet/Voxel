<?php

namespace Voxel\Modules\Paid_Listings\Controllers\Frontend;

use \Voxel\Modules\Paid_Listings as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Cart_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->filter( 'voxel/product_types/cart_item/details', '@set_cart_item_details', 10, 2 );
		$this->on( 'voxel/product_types/cart_item/validate', '@validate_cart_item' );
	}

	protected function set_cart_item_details( $details, $cart_item ) {
		if ( ! $cart_item->is_catalog_product( 'paid_listings_plan' ) ) {
			return $details;
		}

		$value = $cart_item->get_value();

		$product = $cart_item->get_product();
		$plan_key = (string) get_post_meta( $product->get_id(), '_vx_plan_key', true );
		$plan = Module\Listing_Plan::get( $plan_key );

		$details['voxel:listing_plan'] = [
			'plan' => $plan->get_key(),
			'limits' => $plan->get_limits(),
		];

		$checkout_context = $value['custom_data']['checkout_context'] ?? null;
		if ( $checkout_context !== null ) {
			$details['voxel:checkout_context'] = $checkout_context;
		}

		return $details;
	}

	protected function validate_cart_item( $cart_item ) {
		if ( ! $cart_item->is_catalog_product( 'paid_listings_plan' ) ) {
			return;
		}

		global $wpdb;

		$value = $cart_item->get_value();

		$product = $cart_item->get_product();
		$plan_key = (string) get_post_meta( $product->get_id(), '_vx_plan_key', true );
		$plan = Module\Listing_Plan::get( $plan_key );

		if ( $plan === null ) {
			throw new \Exception( _x( 'This plan is no longer available.', 'listing plans', 'voxel' ), 91 );
		}

		$customer = \Voxel\get_current_user();
		if ( $customer ) {
			if ( $plan->config('billing.disable_repeat_purchase') ) {
				$testmode = \Voxel\is_test_mode() ? 'true' : 'false';
				$sql = $wpdb->prepare( <<<SQL
					SELECT 1 FROM {$wpdb->prefix}vx_order_items
						AS items
					LEFT JOIN {$wpdb->prefix}vx_orders
						AS orders ON ( items.order_id = orders.id )
					WHERE orders.customer_id = %d
						AND orders.status IN ('completed','sub_active','sub_trialing')
						AND items.field_key = 'voxel:listing_plan'
						AND JSON_VALID( items.details )
						AND JSON_UNQUOTE( JSON_EXTRACT(
							items.details,
							'$."voxel:listing_plan".plan'
						) ) = %s
						AND orders.testmode IS {$testmode}
					LIMIT 1
				SQL, $customer->get_id(), $plan->get_key() );

				$has_purchased_plan = !! $wpdb->get_var( $sql );
				if ( $has_purchased_plan ) {
					throw new \Exception( _x( 'This plan can only be purchased once.', 'listing plans', 'voxel' ), 91 );
				}
			}
		}
	}
}
