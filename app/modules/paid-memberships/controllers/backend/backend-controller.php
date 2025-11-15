<?php

namespace Voxel\Modules\Paid_Memberships\Controllers\Backend;

use \Voxel\Modules\Paid_Memberships as Module;
use \Voxel\Product_Types\Payment_Services\Base_Payment_Service as Payment_Service;
use \Voxel\Modules\Paid_Memberships\Membership\Base_Membership as Membership;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Backend_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return current_user_can( 'manage_options' );
	}

	protected function hooks() {
		$this->on( 'admin_menu', '@add_menu_page', 10 );
	}

	protected function add_menu_page() {
		add_menu_page(
			__( 'Paid Members', 'voxel-backend' ),
			__( 'Paid Members', 'voxel-backend' ),
			'manage_options',
			'voxel-paid-members',
			function() {
				if ( ! empty( $_GET['migrate'] ) ) {
					$customer = \Voxel\User::get( $_GET['migrate'] );
					if ( ! $customer ) {
						echo '<div class="wrap">'.__( 'Customer not found.', 'voxel-backend' ).'</div>';
						return;
					}

					$membership = $customer->get_membership();
					$legacy_plan = Membership::get_legacy( $customer->get_id() );
					if ( ! ( $legacy_plan && $membership->get_type() === 'default' ) ) {
						return;
					}

					if (
						$legacy_plan->get_type() === 'legacy_subscription'
						&& ! in_array( $legacy_plan->get_status(), [ 'canceled', 'incomplete_expired' ], true )
					) {
						require locate_template( 'app/modules/paid-memberships/templates/backend/migrate-legacy-subscription.php' );
					} else {
						return;
					}

				} elseif ( ! empty( $_GET['customer'] ) ) {
					$customer = \Voxel\User::get( $_GET['customer'] );
					if ( ! $customer ) {
						echo '<div class="wrap">'.__( 'Customer not found.', 'voxel-backend' ).'</div>';
						return;
					}

					$membership = $customer->get_membership();
					$plan = $membership->get_selected_plan();

					$config = [
						'customer' => [
							'id' => $customer->get_id(),
							'email' => $customer->get_email(),
							'display_name' => $customer->get_display_name(),
							'edit_link' => $customer->get_edit_link(),
							'avatar_markup' => $customer->get_avatar_markup(),
						],
						'plan' => [
							'key' => $plan->get_key(),
							'label' => $plan->get_label(),
							'edit_link' => $plan->get_edit_link(),
						],
						'membership' => [
							'type' => $membership->get_type(),
						],
						'edit' => [
							'customer_id' => $customer->get_id(),
							'plan' => $plan->get_key(),
							'subscription' => [
								'canceled' => false,
								'active' => false,
								'recoverable' => false,
							],
							'trial_allowed' => false,
							'stripe_map' => [
								'enabled' => false,
								'transaction_id' => '',
							],
						],
						'_wpnonce' => wp_create_nonce( 'vx_admin_edit_customer' ),
					];

					if ( $membership->get_type() === 'order' ) {
						if ( ( $order = $membership->get_order() ) && ( $payment_method = $membership->get_payment_method() ) ) {
							$config['membership']['order_id'] = $order->get_id();
							$config['membership']['canceled'] = $payment_method->is_subscription_canceled();
						} else {
							$config['membership']['canceled'] = true;
						}
					}

					if ( $membership->get_type() === 'default' && $plan->get_key() === 'default' ) {
						$config['edit']['trial_allowed'] = $customer->is_eligible_for_free_trial();
					}

					wp_enqueue_script( 'vx:customer-editor.js' );
					require locate_template( 'app/modules/paid-memberships/templates/backend/customer-details.php' );
				} else {
					$table = new Module\Customer_List_Table;
					$table->prepare_items();
					require locate_template( 'app/modules/paid-memberships/templates/backend/customer-list-table.php' );
				}
			},
			sprintf( 'data:image/svg+xml;base64,%s', base64_encode( \Voxel\paint_svg(
				file_get_contents( locate_template( 'assets/images/svgs/users.svg' ) ),
				'#a7aaad'
			) ) ),
			'0.392'
		);

		add_submenu_page(
			'voxel-paid-members',
			__( 'Plans', 'voxel-backend' ),
			__( 'Plans', 'voxel-backend' ),
			'manage_options',
			'voxel-paid-members-plans',
			function() {
				$action = sanitize_text_field( $_GET['action'] ?? 'manage-types' );
				$payment_service = Payment_Service::get_active();

				if ( $action === 'create-plan' ) {
					require locate_template( 'templates/backend/membership/create-plan.php' );
				} elseif ( $action === 'edit-plan' ) {
					$plan = Module\Plan::get( $_GET['plan'] ?? '' );
					if ( ! $plan ) {
						return;
					}

					$post_types = [];
					foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
						$post_types[ $post_type->get_key() ] = [
							'key' => $post_type->get_key(),
							'label' => $post_type->get_label(),
							'submittable' => ! in_array( $post_type->get_key(), [ 'profile' ], true ),
						];
					}

					$config = [
						'plan' => $plan->get_editor_config(),
						'postTypes' => $post_types,
					];

					wp_enqueue_script('vue');
					wp_enqueue_script('sortable');
					wp_enqueue_script('vue-draggable');
					wp_enqueue_script('vx:plan-editor.js');

					require locate_template( 'templates/backend/membership/edit-plan.php' );
				} else {
					$default_plan = Module\Plan::get_or_create_default_plan();
					$active_plans = Module\Plan::active();
					$archived_plans = Module\Plan::archived();
					$add_plan_url = admin_url('admin.php?page=voxel-paid-members-plans&action=create-plan');

					require locate_template( 'templates/backend/membership/view-plans.php' );
				}
			}
		);
	}

}
