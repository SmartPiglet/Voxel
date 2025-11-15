<?php

namespace Voxel\Modules\Claim_Listings\Controllers\Frontend;

use \Voxel\Modules\Claim_Listings as Module;
use \Voxel\Modules\Paid_Listings as Paid_Listings;
use \Voxel\Utils\Config_Schema\Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Claim_Order_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel/product-types/orders/order:updated', '@claim_order_updated' );
		$this->on( 'voxel/product-types/orders/order:updated', '@listing_plan_order_updated', 100 );
		$this->filter( 'voxel/orders/view_order/item/components', '@register_claim_component', 10, 3 );
	}

	protected function claim_order_updated( $order ) {
		foreach ( $order->get_items() as $order_item ) {
			if ( $order_item->get_product_field_key() !== 'voxel:claim_request' ) {
				continue;
			}

			if ( ! in_array( $order->get_status(), [ 'completed', 'canceled', 'pending_approval' ], true ) ) {
				continue;
			}

			if ( $order_item->get_details('voxel:claim_request.handled') ) {
				continue;
			}

			$post_id = $order_item->get_details('voxel:claim_request.post_id');
			$package_id = $order_item->get_details('voxel:claim_request.package_id');

			$post = \Voxel\Post::get( $post_id );
			$package = Paid_Listings\Listing_Package::get( $package_id );
			$customer = $order->get_customer();
			if ( ! ( $post && $package && $customer && $customer->is_customer_of( $package->order->get_id() ) ) ) {
				continue;
			}

			if ( ! ( Module\is_claimable( $post ) && $package->can_create_post( $post->post_type ) ) ) {
				continue;
			}

			if ( $order->get_status() === 'pending_approval' ) {
				if ( ! $order_item->get_meta('sent_claim_submitted_event') ) {
					$order_item->set_meta('sent_claim_submitted_event', true);
					$order_item->save();

					( new Module\App_Events\Claim_Submitted_Event )->dispatch( $order_item );
				}
			} elseif ( $order->get_status() === 'completed' ) {
				$original_author = $post->get_author();

				wp_update_post( [
					'ID' => $post->get_id(),
					'post_author' => $customer->get_id(),
				] );

				$package->assign_to_post( $post );
				$post->set_verified(true);

				delete_user_meta( $customer->get_id(), 'voxel:post_stats' );
				if ( $original_author ) {
					delete_user_meta( $original_author->get_id(), 'voxel:post_stats' );
				}

				$order_item->set_details( 'voxel:claim_request.handled', true );
				$order_item->save();

				( new Module\App_Events\Claim_Approved_Event )->dispatch( $order_item );
			} elseif ( $order->get_status() === 'canceled' ) {
				$order_item->set_details( 'voxel:claim_request.handled', true );
				$order_item->save();

				( new Module\App_Events\Claim_Declined_Event )->dispatch( $order_item );
			}
		}
	}

	protected function listing_plan_order_updated( $order ) {
		if ( ! in_array( $order->get_status(), [ 'completed', 'sub_active', 'sub_trialing' ], true ) ) {
			return;
		}

		foreach ( $order->get_items() as $order_item ) {
			if ( $package = Paid_Listings\Listing_Package::get( $order_item ) ) {
				if ( $order_item->get_details('voxel:checkout_context.handled') ) {
					continue;
				}

				$customer = $order->get_customer();
				if ( ! $customer ) {
					continue;
				}

				$checkout_context = $order_item->get_details( 'voxel:checkout_context' );
				if ( ( $checkout_context['process'] ?? null ) !== 'claim' ) {
					continue;
				}

				$order_item->set_details( 'voxel:checkout_context.handled', true );
				$order_item->save();

				$post = \Voxel\Post::get( $checkout_context['post_id'] ?? null );
				if ( ! ( $post && Module\is_claimable( $post ) ) ) {
					continue;
				}

				global $wpdb;

				$wpdb->insert( $wpdb->prefix.'vx_orders', [
					'customer_id' => $customer->get_id(),
					'status' => 'pending_approval',
					'payment_method' => 'offline_payment',
					'details' => wp_json_encode( Schema::optimize_for_storage( [
						'pricing' => [
							'currency' => \Voxel\get_primary_currency(),
							'subtotal' => 0,
							'total' => 0,
						],
					] ) ),
					'testmode' => $order->is_test_mode() ? 1 : 0,
					'created_at' => \Voxel\utc()->format( 'Y-m-d H:i:s' ),
				] );

				$order_id = $wpdb->insert_id;
				$wpdb->insert( $wpdb->prefix.'vx_order_items', [
					'order_id' => $order_id,
					'post_id' => Module\get_product()->get_id(),
					'product_type' => 'voxel:claim_request',
					'field_key' => 'voxel:claim_request',
					'details' => wp_json_encode( Schema::optimize_for_storage( [
						'type' => 'regular',
						'product' => [
							'label' => _x( 'Claim request', 'cart summary', 'voxel' ),
						],
						'currency' => \Voxel\get_primary_currency(),
						'summary' => [
							'total_amount' => 0,
						],
						'voxel:claim_request' => [
							'post_id' => $post->get_id(),
							'package_id' => $package->get_id(),
						],
					] ) ),
				] );

				$order_item_id = $wpdb->insert_id;

				$proof_of_ownership = $order_item->get_details( 'proof_of_ownership', '' );
				if ( ! empty( $proof_of_ownership ) ) {
					$claim_order_item = \Voxel\Order_Item::get( $order_item_id );
					$claim_order_item->set_details( 'proof_of_ownership', $proof_of_ownership );
					$claim_order_item->save();
				}

				$approval = \Voxel\get( 'paid_listings.settings.claims.approval', 'manual' );
				$order = \Voxel\Order::get( $order_id );
				$order->set_status( $approval === 'automatic' ? 'completed' : 'pending_approval' );
				$order->save();
			}
		}
	}

	protected function register_claim_component( $components, $order_item, $order ) {
		if ( $order_item->get_product_field_key() !== 'voxel:claim_request' ) {
			return $components;
		}

		$post_id = $order_item->get_details('voxel:claim_request.post_id');
		$package_id = $order_item->get_details('voxel:claim_request.package_id');

		$post = \Voxel\Post::get( $post_id );
		$package = Paid_Listings\Listing_Package::get( $package_id );
		if ( ! ( $post && $package ) ) {
			return $components;
		}

		$details = [
			'status' => $order->get_status(),
			'listing' => [
				'title' => $post->get_display_name(),
				'link' => $post->get_link(),
			],
			'package' => [
				'id' => $package->get_id(),
				'order_id' => $package->order->get_id(),
				'order_link' => $package->order->get_link(),
			],
			'l10n' => [
				'proof_of_ownership' => _x( 'Proof of ownership', 'single order', 'voxel' ),
				'claim_submitted' => _x( 'Your claim request has been submitted', 'single order', 'voxel' ),
				'claim_declined' => _x( 'Your claim request has been declined', 'single order', 'voxel' ),
				'claim_successful' => _x( 'Your claim request has been approved', 'single order', 'voxel' ),
				'view_listing' => _x( 'View listing', 'single order', 'voxel' ),
				'view_plan' => _x( 'View plan', 'single order', 'voxel' ),
			],
		];

		// proof of ownership
		$proof_of_ownership = [];
		$attachment_ids = explode( ',', $order_item->get_details( 'proof_of_ownership', '' ) );
		foreach ( $attachment_ids as $attachment_id ) {
			if ( $attachment_url = wp_get_attachment_url( $attachment_id ) ) {
				$display_filename = get_post_meta( $attachment_id, '_display_filename', true );
				$proof_of_ownership[] = [
					'name' => ! empty( $display_filename )
						? $display_filename
						: wp_basename( get_attached_file( $attachment_id ) ),
					'url' => $attachment_url,
				];
			}
		}

		$details['proof_of_ownership'] = $proof_of_ownership;

		$src = trailingslashit( get_template_directory_uri() ).'app/modules/claim-listings/assets/scripts/order-item-claim.esm.js';
		$components[] = [
			'type' => 'order-item-claim',
			'src' => add_query_arg( 'v', \Voxel\get_assets_version(), $src ),
			'data' => $details,
		];

		return $components;
	}
}
