<?php

namespace Voxel\Modules\Paid_Listings\Controllers;

use \Voxel\Modules\Paid_Listings as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Paid_Listings_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return !! \Voxel\get('settings.addons.paid_listings.enabled');
	}

	protected function dependencies() {
		\Voxel\use_ecommerce();

		new Backend\Backend_Controller;
		new Backend\Settings_Controller;
		new Backend\Packages_Controller;
		new Backend\Post_Controller;
		new Backend\Post_Table_Controller;

		new Frontend\Checkout_Controller;
		new Frontend\Cart_Controller;
		new Frontend\Order_Controller;
		new Frontend\Create_Post_Controller;
		new Frontend\Auth_Controller;
		new Frontend\Relist_Controller;
		new Frontend\Switch_Controller;

		new Common\Dynamic_Data_Controller;
	}

	protected function hooks() {
		$this->register_payment_product_type();
		$this->register_subscription_product_type();
		$this->filter( 'voxel/post/get_field/voxel:listing_plan', '@register_product_field', 10, 3 );
		$this->on( 'elementor/widgets/register', '@register_widgets', 1000 );

		$this->filter( 'voxel/order/success_redirect', '@checkout_success_redirect', 100, 2 );
		$this->on( 'voxel/schedule:check_for_expired_posts', '@check_for_expired_plans', 50 );

		$this->filter( 'voxel/advanced-list/actions', '@register_relist_action' );
		$this->on( 'voxel/advanced-list/action:relist_post', '@render_relist_action', 10, 2 );

		$this->filter( 'voxel/advanced-list/actions', '@register_switch_action' );
		$this->on( 'voxel/advanced-list/action:switch_listing_plan', '@render_switch_action', 10, 2 );

		$this->on( 'trashed_post', '@handle_post_deletion' );
		$this->on( 'before_delete_post', '@handle_post_deletion' );

		$this->on( 'transition_post_status', '@save_publish_time', 10, 3 );
		$this->on( 'transition_post_status', '@handle_post_rejection', 10, 3 );
	}

	protected function register_payment_product_type() {
		return \Voxel\Product_Type::register_virtual( [
			'settings' => [
				'key' => 'voxel:listing_plan_payment',
				'label' => 'Listing plan',
				'product_mode' => 'regular',
				'payments' => [
					'mode' => 'payment',
					'mode_payment' =>[
						'skip_zero_amount_checkout' => true,
					],
				],
				'supports_marketplace' => false,
			],
			'modules' => [
				'base_price' => [
					'enabled' => true,
					'discount_price' => [
						'enabled' => true,
					],
				],
				'cart' => [
					'enabled' => false,
				],
			],
		] );
	}

	protected function register_subscription_product_type() {
		return \Voxel\Product_Type::register_virtual( [
			'settings' => [
				'key' => 'voxel:listing_plan_subscription',
				'label' => 'Listing plan (Subscription)',
				'product_mode' => 'regular',
				'payments' => [
					'mode' => 'subscription',
				],
				'supports_marketplace' => false,
			],
			'modules' => [
				'base_price' => [
					'enabled' => true,
					'discount_price' => [
						'enabled' => true,
					],
				],
				'cart' => [
					'enabled' => false,
				],
			],
		] );
	}

	protected function register_product_field( $field, $post, $post_type ) {
		if ( $post_type->get_key() !== '_vx_catalog' ) {
			return null;
		}

		$catalog_category = get_post_meta( $post->get_id(), '_vx_catalog_category', true );
		if ( $catalog_category !== 'paid_listings_plan' ) {
			return null;
		}

		$plan_key = (string) get_post_meta( $post->get_id(), '_vx_plan_key', true );
		$plan = Module\Listing_Plan::get( $plan_key );
		if ( $plan === null ) {
			return null;
		}

		$field = new \Voxel\Post_Types\Fields\Product_Field( [
			'label' => 'Listing plan',
			'key' => 'voxel:listing_plan',
			'product-types' => [
				'voxel:listing_plan_payment',
				'voxel:listing_plan_subscription',
			],
		] );

		$field->set_post( $post );

		if ( $plan->get_billing_mode() === 'subscription' ) {
			$field->_set_value( [
				'product_type' => 'voxel:listing_plan_subscription',
				'enabled' => true,
				'base_price' => [
					'amount' => $plan->get_billing_amount(),
					'discount_amount' => $plan->get_billing_discount_amount(),
				],
				'subscription' => [
					'frequency' => $plan->get_billing_frequency(),
					'unit' => $plan->get_billing_interval(),
				],
			] );
		} else {
			$field->_set_value( [
				'product_type' => 'voxel:listing_plan_payment',
				'enabled' => true,
				'base_price' => [
					'amount' => $plan->get_billing_amount(),
					'discount_amount' => $plan->get_billing_discount_amount(),
				],
			] );
		}

		do_action( 'voxel/paid-listings/registered-product-field', $field, $post, $post_type );

		return $field;
	}

	protected function register_widgets() {
		$manager = \Elementor\Plugin::instance()->widgets_manager;
		$manager->register( new Module\Widgets\Listing_Plans_Widget );
	}

	protected function checkout_success_redirect( $redirect_to, \Voxel\Order $order ) {
		if ( ! in_array( $order->get_status(), [ 'completed', 'sub_active', 'sub_trialing' ] ) ) {
			return $redirect_to;
		}

		foreach ( $order->get_items() as $order_item ) {
			$package = Module\Listing_Package::get( $order_item );
			if ( $package === null ) {
				continue;
			}

			if ( $order_item->get_details('voxel:checkout_context.handled') ) {
				continue;
			}

			$checkout_context = $order_item->get_details( 'voxel:checkout_context' );
			if ( ( $checkout_context['process'] ?? null ) === 'new' ) {
				$order_item->set_details( 'voxel:checkout_context.handled', true );
				$order_item->save();

				$post_type = \Voxel\Post_Type::get( $checkout_context['post_type'] ?? null );
				if ( $post_type ) {
					$draft = Module\get_or_create_draft(
						$package,
						$post_type,
						$order->get_customer()
					);

					if ( $draft !== null ) {
						return $draft->get_edit_link();
					}
				}
			}
		}

		return $redirect_to;
	}

	protected function check_for_expired_plans() {
		global $wpdb;

		$expired_posts_map = [];

		// posts expired by plan expiry date
		$expired_ids = $wpdb->get_col(
			$wpdb->prepare( <<<SQL
				SELECT ID FROM {$wpdb->posts} AS p
				INNER JOIN {$wpdb->postmeta} AS pm ON ( p.ID = pm.post_id )
				WHERE post_status IN ('publish','unpublished')
					AND pm.meta_key IN ('voxel:listing_plan_expiry')
					AND pm.meta_value < %s
			SQL, current_time( 'mysql' ) )
		);

		foreach ( $expired_ids as $post_id ) {
			$expired_posts_map[ absint( $post_id ) ] = absint( $post_id );
		}

		// run through all found post ids and change status to expired
		foreach ( $expired_posts_map as $post_id ) {
			wp_update_post( [
				'ID' => $post_id,
				'post_status' => 'expired',
			] );

			delete_post_meta( $post_id, 'voxel:listing_plan_expiry' );
		}
	}

	protected function register_relist_action( $actions ) {
		$actions['relist_post'] = __( 'Relist expired listing', 'voxel-elementor' );
		return $actions;
	}

	protected function render_relist_action( $widget, $action ) {
		require locate_template( 'app/modules/paid-listings/templates/frontend/relist-post-action.php' );
	}

	protected function register_switch_action( $actions ) {
		$actions['switch_listing_plan'] = __( 'Switch listing plan', 'voxel-elementor' );
		return $actions;
	}

	protected function render_switch_action( $widget, $action ) {
		require locate_template( 'app/modules/paid-listings/templates/frontend/switch-listing-plan-action.php' );
	}

	protected function handle_post_deletion( $post_id ) {
		$post = \Voxel\Post::get( $post_id );
		if ( ! $post ) {
			return;
		}

		// Get the assigned package for this post
		$assigned_package_data = Module\get_assigned_package( $post );
		$plan = $assigned_package_data['plan'];
		$package = $assigned_package_data['package'];

		// If no plan or package is assigned, nothing to do
		if ( ! $plan || ! $package ) {
			return;
		}

		// Check if the plan is a subscription plan
		if ( $plan->get_billing_mode() !== 'subscription' ) {
			return;
		}

		// Check if the plan has the restore_slot_on_delete setting enabled
		$restore_slot_on_delete = $plan->config('billing.restore_slot_on_delete');
		if ( ! $restore_slot_on_delete ) {
			return;
		}

		// Check if the order is active
		if ( ! in_array( $package->order->get_status(), [ 'sub_active', 'sub_trialing' ], true ) ) {
			return;
		}

		// Restore the slot
		$package->remove_from_post( $post, $restore_slot = true );
	}

	protected function save_publish_time( $new_status, $old_status, $wp_post ) {
		if ( $new_status === 'publish' && $old_status !== 'publish' ) {
			$post_id = is_object( $wp_post ) ? $wp_post->ID : $wp_post;
			
			update_post_meta( $post_id, '_vx_published_at', time() );
		}
	}

	protected function handle_post_rejection( $new_status, $old_status, $wp_post ) {
		// Only handle transition from pending to rejected or trash
		if ( $old_status !== 'pending' || ! in_array( $new_status, [ 'rejected', 'trash' ], true ) ) {
			return;
		}

		$post = \Voxel\Post::get( $wp_post );
		if ( ! $post ) {
			return;
		}

		// Only restore slot if post was never published (first submission)
		$published_at = get_post_meta( $post->get_id(), '_vx_published_at', true );
		if ( $published_at ) {
			return;
		}

		// Get the assigned package for this post
		$assigned_package_data = Module\get_assigned_package( $post );
		$plan = $assigned_package_data['plan'];
		$package = $assigned_package_data['package'];

		// If no plan or package is assigned, nothing to do
		if ( ! $plan || ! $package ) {
			return;
		}

		// Check if the order is in a valid status
		if ( ! in_array( $package->order->get_status(), [ 'completed', 'sub_active', 'sub_trialing' ], true ) ) {
			return;
		}

		// Restore the slot (works for both subscription and payment plans)
		$package->remove_from_post( $post, $restore_slot = true );
	}

}
