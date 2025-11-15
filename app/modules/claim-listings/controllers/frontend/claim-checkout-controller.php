<?php

namespace Voxel\Modules\Claim_Listings\Controllers\Frontend;

use \Voxel\Modules\Claim_Listings as Module;
use \Voxel\Modules\Paid_Listings as Paid_Listings;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Claim_Checkout_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel/product_types/cart_item/validate', '@validate_cart_item' );
		$this->filter( 'voxel/cart_summary/item/components', '@register_cart_item_component', 10, 2 );
		$this->on( 'voxel/checkout/before-create-order', '@save_proof_of_ownership' );
		$this->filter( 'voxel/direct_cart/metadata', '@set_claim_page_title', 10, 2 );
		$this->filter( 'voxel/cart_summary/item/frontend_config', '@cart_item_frontend_config', 100, 2 );
		$this->filter( 'voxel/product_types/cart_item/details', '@set_cart_item_details', 10, 2 );
	}

	protected function validate_cart_item( $cart_item ) {
		if ( $cart_item->is_catalog_product('claim_request') ) {
			$value = $cart_item->get_value();

			$post = \Voxel\Post::get( $value['custom_data']['voxel:claim_request']['post_id'] ?? null );
			if ( ! ( $post && Module\is_claimable( $post ) ) ) {
				throw new \Exception( _x( 'This item cannot be claimed.', 'pricing plans', 'voxel' ), 70 );
			}

			if ( is_user_logged_in() ) {
				$customer = \Voxel\get_current_user();
				$package = Paid_Listings\Listing_Package::get( $value['custom_data']['voxel:claim_request']['package_id'] ?? null );
				if ( ! ( $package && $customer->is_customer_of( $package->order->get_id() ) ) ) {
					throw new \Exception( _x( 'This plan is not available.', 'pricing plans', 'voxel' ), 75 );
				}

				if ( ! $package->can_create_post( $post->post_type ) ) {
					throw new \Exception( _x( 'This plan is not available.', 'pricing plans', 'voxel' ), 71 );
				}
			}
		}

		if ( $cart_item->is_catalog_product('paid_listings_plan') ) {
			$value = $cart_item->get_value();
			$process = $value['custom_data']['checkout_context']['process'] ?? null;

			if ( $process === 'claim' ) {
				$post_id = $value['custom_data']['checkout_context']['post_id'] ?? null;
				$post = \Voxel\Post::get( $post_id );

				if ( ! ( $post && Module\is_claimable( $post ) ) ) {
					throw new \Exception( _x( 'This item cannot be claimed.', 'pricing plans', 'voxel' ), 70 );
				}
			}
		}
	}

	protected function register_cart_item_component( $components, $cart_item ) {
		$value = $cart_item->get_value();
		if ( ! (
			$cart_item->is_catalog_product('claim_request')
			|| (
				$cart_item->is_catalog_product('paid_listings_plan')
				&& ( $value['custom_data']['checkout_context']['process'] ?? null ) === 'claim'
			)
		) ) {
			return $components;
		}

		$file_field = Module\get_proof_of_ownership_field();

		$data = [
			'allowed_file_types' => (array) $file_field->get_prop('allowed-types'),
			'max_count' => $file_field->get_prop('max-count'),
			'proof_of_ownership' => [
				'status' => \Voxel\get( 'paid_listings.settings.claims.proof_of_ownership', 'optional' ),
				'enabled' => false,
				'files' => [],
			],
			'l10n' => [
				'switcher_label' => _x( 'Add proof of ownership?', 'cart summary', 'voxel' ),
				'file_field_label' => _x( 'Proof of ownership', 'cart summary', 'voxel' ),
				'file_field_tooltip' => _x( 'Upload a business document to verify your ownership', 'cart summary', 'voxel' ),
			],
		];

		$src = trailingslashit( get_template_directory_uri() ).'app/modules/claim-listings/assets/scripts/cart-item-claim.esm.js';
		$components[] = [
			'type' => 'cart-item-claim',
			'src' => add_query_arg( 'v', \Voxel\get_assets_version(), $src ),
			'data' => $data,
		];

		return $components;
	}

	protected function save_proof_of_ownership( $cart ) {
		foreach ( $cart->get_items() as $cart_item ) {
			$value = $cart_item->get_value();
			if ( ! (
				$cart_item->is_catalog_product('claim_request')
				|| (
					$cart_item->is_catalog_product('paid_listings_plan')
					&& ( $value['custom_data']['checkout_context']['process'] ?? null ) === 'claim'
				)
			) ) {
				continue;
			}

			$file_field = Module\get_proof_of_ownership_field();
			$raw_uploaded_files = (array) json_decode( wp_unslash( $_REQUEST['proof_of_ownership'] ?? '' ), true );
			$sanitized_files = $file_field->sanitize( $raw_uploaded_files );

			$proof_of_ownership = \Voxel\get( 'paid_listings.settings.claims.proof_of_ownership', 'optional' );
			if ( $proof_of_ownership === 'required' ) {
				if ( empty( $sanitized_files ) ) {
					throw new \Exception( _x( 'Proof of ownership is required', 'claim request', 'voxel' ) );
				}
			}

			$file_field->validate( $sanitized_files );

			if ( ! empty( $sanitized_files ) ) {
				add_action( 'voxel/checkout/after-create-order', function( $order ) use ( $file_field, $sanitized_files ) {
					foreach ( $order->get_items() as $order_item ) {
						if ( in_array( $order_item->get_product_field_key(), [ 'voxel:listing_plan', 'voxel:claim_request' ], true ) ) {
							$order_item->set_details( 'proof_of_ownership', $file_field->prepare_for_storage( $sanitized_files ) );
							$order_item->save();
						}
					}
				} );
			}
		}
	}

	protected function set_claim_page_title( $meta, $cart_item ) {
		$value = $cart_item->get_value();

		if ( $cart_item->is_catalog_product('claim_request') ) {
			$post = \Voxel\Post::get( $value['custom_data']['voxel:claim_request']['post_id'] );
			$meta['cart_label'] = sprintf(
				_x( 'Claim listing', 'cart summary', 'voxel' ),
				$post->get_display_name()
			);
		} elseif (
			$cart_item->is_catalog_product('paid_listings_plan')
			&& ( $value['custom_data']['checkout_context']['process'] ?? null ) === 'claim'
		) {
			$post = \Voxel\Post::get( $value['custom_data']['checkout_context']['post_id'] );
			$meta['cart_label'] = sprintf(
				_x( 'Claim listing', 'cart summary', 'voxel' ),
				$post->get_display_name()
			);
		}

		return $meta;
	}

	protected function cart_item_frontend_config( $config, \Voxel\Cart_Item $cart_item ) {
		$value = $cart_item->get_value();

		if ( $cart_item->is_catalog_product('claim_request') ) {
			$post = \Voxel\Post::get( $value['custom_data']['voxel:claim_request']['post_id'] );
		

			$config['link'] = $post->get_link();
			$config['title'] = $post->get_display_name();
			$config['custom_class'] = 'claim-request';
			if ( $post->get_avatar_markup() ) {
				$config['logo'] = $post->get_avatar_markup();
			} else {
				$config['logo'] = sprintf( '<img src="%s">', esc_url( \Voxel\get_image( 'platform.jpg' ) ) );
			}

		} elseif (
			$cart_item->is_catalog_product('paid_listings_plan')
			&& ( $value['custom_data']['checkout_context']['process'] ?? null ) === 'claim'
		) {
			$subtitle = _x( 'Listing plan', 'cart summary', 'voxel' );
			if ( ! empty( $config['subtitle'] ) ) {
				$config['subtitle'] = sprintf( '%s, %s', $subtitle, $config['subtitle'] );
			} else {
				$config['subtitle'] = $subtitle;
			}

			$config['link'] = null;
			$config['logo'] = sprintf( '<img src="%s">', esc_url( \Voxel\get_image( 'platform.jpg' ) ) );
		}

		return $config;
	}

	protected function set_cart_item_details( $details, $cart_item ) {
		$value = $cart_item->get_value();

		if ( $cart_item->is_catalog_product('claim_request') ) {
			$post = \Voxel\Post::get( $value['custom_data']['voxel:claim_request']['post_id'] );
			$package = Paid_Listings\Listing_Package::get( $value['custom_data']['voxel:claim_request']['package_id'] );

			$details['voxel:claim_request'] = [
				'post_id' => $post->get_id(),
				'package_id' => $package->get_id(),
			];
		} elseif (
			$cart_item->is_catalog_product('paid_listings_plan')
			&& ( $value['custom_data']['checkout_context']['process'] ?? null ) === 'claim'
		) {
			//
		}

		return $details;
	}
}
