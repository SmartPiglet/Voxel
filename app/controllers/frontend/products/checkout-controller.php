<?php

namespace Voxel\Controllers\Frontend\Products;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Checkout_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel_ajax_products.checkout', '@checkout' );
		$this->on( 'voxel_ajax_products.promotions.checkout', '@promotion_checkout' );
	}

	protected function checkout() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 99 );
			}

			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_checkout' );

			$redirect_to = null;
			if ( ! empty( $_REQUEST['redirect_to'] ) && wp_validate_redirect( $_REQUEST['redirect_to'] ) ) {
				$redirect_to = wp_validate_redirect( $_REQUEST['redirect_to'] );
			}

			$source = $_REQUEST['source'] ?? null;
			if ( $source === 'cart' ) {
				$cart = \Voxel\current_user()->get_cart();

				$cart->set_order_notes( $this->_get_order_notes() );

				if ( $cart->has_shippable_products() ) {
					if ( $cart->get_shipping_method() === 'vendor_rates' ) {
						$shipping = (array) json_decode( wp_unslash( $_REQUEST['shipping'] ?? '' ), true );
						$shipping_country = $shipping['country'] ?? '';
						$rates_by_vendor = [];
						foreach ( $cart->get_vendors() as $vendor ) {
							if ( $vendor['has_shippable_products'] ) {
								$shipping_zone_key = $shipping['vendors'][ $vendor['key'] ]['zone'] ?? null;
								$shipping_rate_key = $shipping['vendors'][ $vendor['key'] ]['rate'] ?? null;

								if ( $vendor['key'] !== 'platform' ) {
									$vendor_shipping_zones = ( \Voxel\User::get( $vendor['id'] ) )->get_vendor_shipping_zones();
									$shipping_zone = $vendor_shipping_zones[ $shipping_zone_key ];
									if ( $shipping_zone === null ) {
										throw new \Exception( _x( 'No shipping zone selected', 'shipping', 'voxel' ) );
									}

									$shipping_rate = $shipping_zone->get_rate( $shipping_rate_key );
									if ( $shipping_rate === null ) {
										throw new \Exception( _x( 'No shipping rate selected', 'shipping', 'voxel' ) );
									}

									if ( ! $shipping_zone->supports_country( $shipping_country ) ) {
										throw new \Exception( _x( 'Please select a valid shipping zone', 'shipping', 'voxel' ) );
									}

									$rates_by_vendor[ $vendor['key'] ] = [
										'zone' => $shipping_zone,
										'rate' => $shipping_rate,
									];
								} else {
									$shipping_zone = \Voxel\Product_Types\Shipping\Shipping_Zone::get( $shipping_zone_key );
									if ( ! $shipping_zone ) {
										throw new \Exception( _x( 'No shipping zone selected', 'shipping', 'voxel' ) );
									}

									$shipping_rate = $shipping_zone->get_rate( $shipping_rate_key );
									if ( ! $shipping_rate ) {
										throw new \Exception( _x( 'No shipping rate selected', 'shipping', 'voxel' ) );
									}

									if ( ! $shipping_zone->supports_country( $shipping_country ) ) {
										throw new \Exception( _x( 'Please select a valid shipping zone', 'shipping', 'voxel' ) );
									}

									$rates_by_vendor[ $vendor['key'] ] = [
										'zone' => $shipping_zone,
										'rate' => $shipping_rate,
									];
								}
							}
						}

						$cart->set_shipping_rates_by_vendor( $shipping_country, $rates_by_vendor );
					} else {
						$shipping = (array) json_decode( wp_unslash( $_REQUEST['shipping'] ?? '' ), true );
						$cart->set_shipping_rate( $shipping['country'] ?? null, $shipping['zone'] ?? null, $shipping['rate'] ?? null );
					}
				}

				do_action( 'voxel/checkout/before-create-order', $cart );

				$order = \Voxel\Product_Types\Orders\Order::create_from_cart( $cart, [
					'meta' => [
						'redirect_to' => $redirect_to,
					],
				] );

				$payment_method = $order->get_payment_method();
				if ( $payment_method === null ) {
					throw new \Exception( __( 'Could not process payment', 'voxel' ), 101 );
				}

				do_action( 'voxel/checkout/after-create-order', $order );

				return wp_send_json( $payment_method->process_payment() );
			} elseif ( $_REQUEST['source'] === 'direct_cart' ) {
				$config = (array) json_decode( wp_unslash( $_REQUEST['items'] ?? '' ), true );
				$cart_item = \Voxel\Product_Types\Cart_Items\Cart_Item::create( (array) ( $config[0] ?? [] ) );

				$cart = new \Voxel\Product_Types\Cart\Direct_Cart;
				$cart->add_item( $cart_item );

				$cart->set_order_notes( $this->_get_order_notes() );

				if ( $cart->has_shippable_products() ) {
					if ( $cart->get_shipping_method() === 'vendor_rates' ) {
						$shipping = (array) json_decode( wp_unslash( $_REQUEST['shipping'] ?? '' ), true );
						$shipping_country = $shipping['country'] ?? '';
						$rates_by_vendor = [];
						foreach ( $cart->get_vendors() as $vendor ) {
							if ( $vendor['has_shippable_products'] ) {
								$shipping_zone_key = $shipping['vendors'][ $vendor['key'] ]['zone'] ?? null;
								$shipping_rate_key = $shipping['vendors'][ $vendor['key'] ]['rate'] ?? null;

								if ( $vendor['key'] !== 'platform' ) {
									$vendor_shipping_zones = ( \Voxel\User::get( $vendor['id'] ) )->get_vendor_shipping_zones();
									$shipping_zone = $vendor_shipping_zones[ $shipping_zone_key ];
									if ( $shipping_zone === null ) {
										throw new \Exception( _x( 'No shipping zone selected', 'shipping', 'voxel' ) );
									}

									$shipping_rate = $shipping_zone->get_rate( $shipping_rate_key );
									if ( $shipping_rate === null ) {
										throw new \Exception( _x( 'No shipping rate selected', 'shipping', 'voxel' ) );
									}

									if ( ! $shipping_zone->supports_country( $shipping_country ) ) {
										throw new \Exception( _x( 'Please select a valid shipping zone', 'shipping', 'voxel' ) );
									}

									$rates_by_vendor[ $vendor['key'] ] = [
										'zone' => $shipping_zone,
										'rate' => $shipping_rate,
									];
								} else {
									$shipping_zone = \Voxel\Product_Types\Shipping\Shipping_Zone::get( $shipping_zone_key );
									if ( ! $shipping_zone ) {
										throw new \Exception( _x( 'No shipping zone selected', 'shipping', 'voxel' ) );
									}

									$shipping_rate = $shipping_zone->get_rate( $shipping_rate_key );
									if ( ! $shipping_rate ) {
										throw new \Exception( _x( 'No shipping rate selected', 'shipping', 'voxel' ) );
									}

									if ( ! $shipping_zone->supports_country( $shipping_country ) ) {
										throw new \Exception( _x( 'Please select a valid shipping zone', 'shipping', 'voxel' ) );
									}

									$rates_by_vendor[ $vendor['key'] ] = [
										'zone' => $shipping_zone,
										'rate' => $shipping_rate,
									];
								}
							}
						}

						$cart->set_shipping_rates_by_vendor( $shipping_country, $rates_by_vendor );
					} else {
						$shipping = (array) json_decode( wp_unslash( $_REQUEST['shipping'] ?? '' ), true );
						$cart->set_shipping_rate( $shipping['country'] ?? null, $shipping['zone'] ?? null, $shipping['rate'] ?? null );
					}
				}

				do_action( 'voxel/checkout/before-create-order', $cart );

				$order = \Voxel\Product_Types\Orders\Order::create_from_cart( $cart, [
					'meta' => [
						'redirect_to' => $redirect_to,
					],
				] );

				$payment_method = $order->get_payment_method();
				if ( $payment_method === null ) {
					throw new \Exception( __( 'Could not process payment', 'voxel' ), 101 );
				}

				do_action( 'voxel/checkout/after-create-order', $order );

				return wp_send_json( $payment_method->process_payment() );
			} else {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 100 );
			}
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}

	protected function _get_order_notes(): ?string {
		$content = trim( sanitize_textarea_field( $_REQUEST['order_notes'] ?? '' ) );
		$maxlength = 2000;
		if ( mb_strlen( $content ) > $maxlength ) {
			throw new \Exception( \Voxel\replace_vars(
				_x( 'Order notes can\'t be longer than @maxlength characters', 'checkout', 'voxel' ), [
					'@maxlength' => $maxlength,
				]
			) );
		}

		if ( empty( $content ) ) {
			return null;
		}

		return $content;
	}

	protected function promotion_checkout() {
		try {
			if ( ( $_SERVER['REQUEST_METHOD'] ?? null ) !== 'POST' ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 99 );
			}

			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_checkout' );

			if ( empty( $_REQUEST['post_id'] ?? null ) || empty( $_REQUEST['promotion_package'] ?? null ) ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 100 );
			}

			$post = \Voxel\Post::get( $_REQUEST['post_id'] );
			$package_key = sanitize_text_field( $_REQUEST['promotion_package'] );
			$user = \Voxel\get_current_user();

			if ( ! ( $post && $post->promotions->is_promotable_by_user( $user ) ) ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 101 );
			}

			$available_packages = $post->promotions->get_available_packages();
			if ( ! isset( $available_packages[ $package_key ] ) ) {
				throw new \Exception( __( 'Could not process request', 'voxel' ), 102 );
			}

			$package = $available_packages[ $package_key ];

			$cart_item = \Voxel\Product_Types\Cart_Items\Cart_Item::create( [
				'product' => [
					'post_id' => $post->get_id(),
					'field_key' => 'voxel:promotion',
				],
				'promotion_package' => $package->get_key(),
			] );

			$cart = new \Voxel\Product_Types\Cart\Direct_Cart;
			$cart->add_item( $cart_item );

			do_action( 'voxel/checkout/before-create-order', $cart );

			$order = \Voxel\Product_Types\Orders\Order::create_from_cart( $cart );
			$payment_method = $order->get_payment_method();
			if ( $payment_method === null ) {
				throw new \Exception( __( 'Could not process payment', 'voxel' ), 101 );
			}

			do_action( 'voxel/checkout/after-create-order', $order );

			return wp_send_json( $payment_method->process_payment() );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			] );
		}
	}
}
