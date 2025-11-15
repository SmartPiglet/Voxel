<?php

namespace Voxel\Controllers\Frontend\Products;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Direct_Cart_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_products.get_direct_cart', '@get_direct_cart' );
		$this->on( 'voxel_ajax_nopriv_products.get_direct_cart', '@get_direct_cart' );
	}

	protected function get_direct_cart() {
		try {
			$config = (array) json_decode( wp_unslash( $_REQUEST['item'] ?? '' ), true );
			$cart_item = \Voxel\Product_Types\Cart_Items\Cart_Item::create( $config );

			$cart = new \Voxel\Product_Types\Cart\Direct_Cart;
			$cart->add_item( $cart_item );

			if ( ! empty( $_REQUEST['item_quantity'] ?? null ) ) {
				$quantity = absint( $_REQUEST['item_quantity'] ?? null );
				$cart->set_item_quantity( $cart_item->get_key(), $quantity );
			}

			$checkout_link = get_permalink( \Voxel\get( 'templates.checkout' ) ) ?: home_url('/');
			$checkout_link = add_query_arg( 'checkout_item', $cart_item->get_key(), $checkout_link );

			if ( $cart_item->get_type() === 'booking' ) {
				$cart_context = 'booking';
				$cart_label = _x( 'Booking confirmation', 'cart summary', 'voxel' );
			} elseif ( $cart_item->get_type() === 'regular' && $cart_item->get_product_field()->get_key() === 'voxel:promotion' ) {
				$cart_context = 'promote';
				$cart_label = _x( 'Promote post', 'cart summary', 'voxel' );
			} else {
				$cart_context = 'direct_order';
			}

			return wp_send_json( [
				'success' => true,
				'item' => $cart_item->get_frontend_config(),
				'checkout_link' => $checkout_link,
				'metadata' => apply_filters( 'voxel/direct_cart/metadata', [
					'cart_context' => $cart_context,
					'cart_label' => $cart_label ?? null,
				], $cart_item ),
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}
}
