<?php

namespace Voxel\Modules\Paid_Listings\Controllers\Frontend;

use \Voxel\Modules\Paid_Listings as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Order_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		$this->on( 'voxel/product-types/orders/order:updated', '@order_updated' );
		$this->on( 'voxel/product-types/orders/order:before_delete', '@before_order_delete' );
		$this->filter( 'voxel/order_item/product_description', '@set_product_description', 10, 2 );
		$this->filter( 'voxel/order_item/product_link', '@set_product_link', 10, 2 );
		$this->filter( 'voxel/orders/view_order/item/components', '@register_order_item_component', 10, 3 );
		$this->on( 'voxel_ajax_paid_listings.order.load_more_recents', '@load_more_recents' );
	}

	protected function order_updated( $order ) {
		if ( $order->get_status() === 'pending_payment' ) {
			return;
		}

		global $wpdb;

		foreach ( $order->get_items() as $order_item ) {
			if ( $package = Module\Listing_Package::get( $order_item ) ) {
				$package->update_usage_meta();
				$package->save();

				// payments: expire and remove postmeta if package transitions from completed to canceled/refunded
				if (
					$order->get_previous_status() === 'completed'
					&& in_array( $order->get_status(), [ 'canceled', 'refunded' ], true )
				) {
					$expired_ids = $wpdb->get_col(
						$wpdb->prepare( <<<SQL
							SELECT p.ID FROM {$wpdb->posts} p
							INNER JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = 'voxel:listing_plan' )
							WHERE p.post_author = %d AND JSON_VALID(pm.meta_value)
								AND JSON_UNQUOTE( JSON_EXTRACT( pm.meta_value, "$.package" ) ) = %d
						SQL, $order->get_customer_id(), $package->get_id() )
					);

					foreach ( $expired_ids as $post_id ) {
						if ( $post_id = absint( $post_id ) ) {
							if ( get_post_status( $post_id ) !== 'expired' ) {
								wp_update_post( [
									'ID' => $post_id,
									'post_status' => 'expired',
								] );
							}

							delete_post_meta( $post_id, 'voxel:listing_plan' );
							delete_post_meta( $post_id, 'voxel:listing_plan_expiry' );
						}
					}
				}

				// subscriptions
				$payment_method = $order->get_payment_method();
				if ( $payment_method && $payment_method->is_subscription() ) {
					if ( $payment_method->is_subscription_active() ) {
						// reactivate posts when package transitions from recoverable to active
						$expired_ids = $wpdb->get_col(
							$wpdb->prepare( <<<SQL
								SELECT p.ID FROM {$wpdb->posts} p
								INNER JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = 'voxel:listing_plan' )
								INNER JOIN {$wpdb->postmeta} pm2 ON ( p.ID = pm2.post_id AND pm2.meta_key = 'voxel:plan_reactivate_status' )
								WHERE p.post_author = %d AND JSON_VALID(pm.meta_value)
									AND JSON_UNQUOTE( JSON_EXTRACT( pm.meta_value, "$.package" ) ) = %d
							SQL, $order->get_customer_id(), $package->get_id() )
						);

						foreach ( $expired_ids as $post_id ) {
							if ( $post_id = absint( $post_id ) ) {
								$new_status = get_post_meta( $post_id, 'voxel:plan_reactivate_status', true );
								if ( ! empty( $new_status ) ) {
									wp_update_post( [
										'ID' => $post_id,
										'post_status' => $new_status,
									] );

									delete_post_meta( $post_id, 'voxel:plan_reactivate_status' );
								}
							}
						}
					} elseif ( $payment_method->is_subscription_recoverable() ) {
						// expire if package transitions from to a recoverable non-active status
						// posts are published if package gets reactivated
						$expired_ids = $wpdb->get_col(
							$wpdb->prepare( <<<SQL
								SELECT p.ID FROM {$wpdb->posts} p
								INNER JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = 'voxel:listing_plan' )
								WHERE p.post_author = %d AND JSON_VALID(pm.meta_value)
									AND JSON_UNQUOTE( JSON_EXTRACT( pm.meta_value, "$.package" ) ) = %d
							SQL, $order->get_customer_id(), $package->get_id() )
						);

						foreach ( $expired_ids as $post_id ) {
							if ( $post_id = absint( $post_id ) ) {
								update_post_meta( $post_id, 'voxel:plan_reactivate_status', get_post_status( $post_id ) );

								if ( get_post_status( $post_id ) !== 'expired' ) {
									wp_update_post( [
										'ID' => $post_id,
										'post_status' => 'expired',
									] );
								}
							}
						}
					} else {
						// expire and remove postmeta if package transitions to a non-recoverable status
						$expired_ids = $wpdb->get_col(
							$wpdb->prepare( <<<SQL
								SELECT p.ID FROM {$wpdb->posts} p
								INNER JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = 'voxel:listing_plan' )
								WHERE p.post_author = %d AND JSON_VALID(pm.meta_value)
									AND JSON_UNQUOTE( JSON_EXTRACT( pm.meta_value, "$.package" ) ) = %d
							SQL, $order->get_customer_id(), $package->get_id() )
						);

						foreach ( $expired_ids as $post_id ) {
							if ( $post_id = absint( $post_id ) ) {
								if ( get_post_status( $post_id ) !== 'expired' ) {
									wp_update_post( [
										'ID' => $post_id,
										'post_status' => 'expired',
									] );
								}

								delete_post_meta( $post_id, 'voxel:listing_plan' );
								delete_post_meta( $post_id, 'voxel:listing_plan_expiry' );
								delete_post_meta( $post_id, 'voxel:plan_reactivate_status' );
							}
						}
					}
				}
			}
		}
	}

	protected function before_order_delete( \Voxel\Order $order ) {
		if ( $order->get_status() === 'pending_payment' ) {
			return;
		}

		global $wpdb;

		// expire attached posts and remove postmeta before deleting package
		foreach ( $order->get_items() as $order_item ) {
			if ( $package = Module\Listing_Package::get( $order_item ) ) {
				$expired_ids = $wpdb->get_col(
					$wpdb->prepare( <<<SQL
						SELECT p.ID FROM {$wpdb->posts} p
						INNER JOIN {$wpdb->postmeta} pm ON ( p.ID = pm.post_id AND pm.meta_key = 'voxel:listing_plan' )
						WHERE p.post_author = %d AND JSON_VALID(pm.meta_value)
							AND JSON_UNQUOTE( JSON_EXTRACT( pm.meta_value, "$.package" ) ) = %d
					SQL, $order->get_customer_id(), $package->get_id() )
				);

				foreach ( $expired_ids as $post_id ) {
					if ( $post_id = absint( $post_id ) ) {
						if ( get_post_status( $post_id ) !== 'expired' ) {
							wp_update_post( [
								'ID' => $post_id,
								'post_status' => 'expired',
							] );
						}

						delete_post_meta( $post_id, 'voxel:listing_plan' );
						delete_post_meta( $post_id, 'voxel:listing_plan_expiry' );
					}
				}
			}
		}
	}

	protected function set_product_description( $description, $order_item ) {
		if ( $order_item->get_product_field_key() === 'voxel:listing_plan' ) {
			return join( ', ', array_filter( [
				$description,
				_x( 'Listing plan', 'order item', 'voxel' ),
			] ) );
		}

		return $description;
	}

	protected function set_product_link( $link, $order_item ) {
		if ( $order_item->get_product_field_key() === 'voxel:listing_plan' ) {
			return null;
		}

		return $link;
	}

	protected function register_order_item_component( $components, $order_item, $order ) {
		$package = Module\Listing_Package::get( $order_item );
		if ( $package === null ) {
			return $components;
		}

		if ( ! in_array( $order->get_status(), [ 'completed', 'sub_active', 'sub_trialing' ], true ) ) {
			return $components;
		}

		$details = [
			'_wpnonce' => wp_create_nonce( 'vx_order_'.$order->get_id() ),
			'limits' => [],
			'image' => \Voxel\get_image( 'create.jpg' ),
			'l10n' => [
				'block_label' => _x( 'Your listing plan is ready to use', 'listing plan details', 'voxel' ),
				// 'block_text' => _x( 'Submission limits and usage details', 'listing plan details', 'voxel' ),
				'recent_submissions' => _x( 'Recent submissions', 'listing plan details', 'voxel' ),
				'load_more' => _x( 'Load more', 'listing plan details', 'voxel' ),
			],
		];

		foreach ( $package->get_limits() as $limit_index => $limit ) {
			$limit_details = [
				'label' => $package->get_label_for_limit( $limit ),
				'description' => join( ', ', array_filter( [
					$limit['mark_verified'] ? _x( 'Verified status', 'listing plan details', 'voxel' ) : null,
					$limit['priority']['enabled'] && $limit['priority']['value'] >= 1
						? _x( 'Search priority', 'listing plan details', 'voxel' )
						: null,
					$limit['expiration']['mode'] === 'fixed_days' && $limit['expiration']['fixed_days'] !== null
						? sprintf( _x( 'Active for %d days', 'listing plan details', 'voxel' ), $limit['expiration']['fixed_days'] )
						: null,
				] ) ),
				'usage' => [
					'total' => $limit['total'],
					'used' => min( $limit['usage']['count'], $limit['total'] ),
					'text' => sprintf(
						_x( '%d/%d used', 'listing plan details', 'voxel' ),
						 min( $limit['usage']['count'], $limit['total'] ),
						 $limit['total'],
					),
				],
				'recents' => $package->get_recent_posts_for_limit( $limit, 0 ),
			];

			$details['limits'][] = $limit_details;
		}

		$src = trailingslashit( get_template_directory_uri() ).'app/modules/paid-listings/assets/scripts/order-item-listing-plan.esm.js';
		$components[] = [
			'type' => 'order-item-listing-plan',
			'src' => add_query_arg( 'v', \Voxel\get_assets_version(), $src ),
			'data' => $details,
		];

		return $components;
	}

	protected function load_more_recents() {
		try {
			$current_user = \Voxel\get_current_user();
			$order = \Voxel\Product_Types\Orders\Order::get( absint( $_REQUEST['order_id'] ?? null ) );
			if ( ! $order ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ) );
			}

			\Voxel\verify_nonce( $_REQUEST['_wpnonce'] ?? '', 'vx_order_'.$order->get_id() );

			$order_item = $order->get_item( absint( $_REQUEST['order_item_id'] ?? null ) );
			if ( ! ( $order_item && $order_item->get_product_field_key() === 'voxel:listing_plan' ) ) {
				throw new \Exception( _x( 'Permission check failed.', 'orders', 'voxel' ) );
			}

			$package = Module\Listing_Package::get( $order_item );
			if ( $package === null ) {
				throw new \Exception( _x( 'Could not retrieve posts.', 'orders', 'voxel' ) );
			}

			$cursor = absint( $_REQUEST['cursor'] ?? 0 );
			$limit_index = absint( $_REQUEST['index'] ?? 0 );
			$limit = $package->get_limits()[ $limit_index ] ?? null;
			if ( $limit === null ) {
				throw new \Exception( _x( 'Could not retrieve posts.', 'orders', 'voxel' ) );
			}

			$recents = $package->get_recent_posts_for_limit( $limit, $cursor );

			return wp_send_json( [
				'success' => true,
				'posts' => $recents,
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
