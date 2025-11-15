<?php

namespace Voxel\Modules\Paid_Listings\Controllers\Backend;

use \Voxel\Modules\Paid_Listings as Module;
use \Voxel\Utils\Config_Schema\Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Backend_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return current_user_can( 'manage_options' );
	}

	protected function hooks() {
		$this->on( 'admin_menu', '@add_menu_page', 10 );
		$this->on( 'admin_post_voxel_create_listing_plan', '@create_listing_plan' );
		$this->on( 'voxel_ajax_paid_listings.update_listing_plan', '@update_listing_plan' );
		$this->on( 'voxel_ajax_paid_listings.delete_listing_plan', '@delete_listing_plan' );
	}

	protected function add_menu_page() {
		add_menu_page(
			__( 'Assigned Plans', 'voxel-backend' ),
			__( 'Paid Listings ', 'voxel-backend' ),
			'manage_options',
			'voxel-paid-listings',
			function() {
				do_action( 'voxel/paid-listings/backend/packages-screen' );
			},
			sprintf( 'data:image/svg+xml;base64,%s', base64_encode( \Voxel\paint_svg(
				file_get_contents( locate_template( 'assets/images/svgs/box-dollar.svg' ) ),
				'#a7aaad'
			) ) ),
			'0.395'
		);

		add_submenu_page(
			'voxel-paid-listings',
			__( 'Plans', 'voxel-backend' ),
			__( 'Plans', 'voxel-backend' ),
			'manage_options',
			'voxel-paid-listings-plans',
			function() {
				$action = sanitize_text_field( $_GET['action'] ?? 'manage-types' );

				if ( $action === 'create-plan' ) {
					require locate_template( 'app/modules/paid-listings/templates/backend/create-plan.php' );
				} elseif ( $action === 'edit-plan' ) {
					$plan = Module\Listing_Plan::get( $_GET['plan'] ?? '' );
					if ( ! $plan ) {
						return;
					}

					$config = [
						'plan' => $plan->get_editor_config(),
						'post_types' => [],
					];

					foreach ( \Voxel\Post_Type::get_voxel_types() as $post_type ) {
						if ( in_array( $post_type->get_key(), [ 'profile', 'collection' ], true ) ) {
							continue;
						}

						$config['post_types'][ $post_type->get_key() ] = [
							'key' => $post_type->get_key(),
							'label' => $post_type->get_label(),
						];
					}

					wp_enqueue_script('vue');
					wp_enqueue_script('sortable');
					wp_enqueue_script('vue-draggable');
					wp_enqueue_script('vx:listing-plan-editor.js');

					require locate_template( 'app/modules/paid-listings/templates/backend/edit-plan.php' );
				} else {
					$plans = Module\Listing_Plan::all();
					$add_plan_url = admin_url('admin.php?page=voxel-paid-listings-plans&action=create-plan');

					require locate_template( 'app/modules/paid-listings/templates/backend/view-plans.php' );
				}
			}
		);

		add_submenu_page(
			'voxel-paid-listings',
			__( 'Settings', 'voxel-backend' ),
			__( 'Settings', 'voxel-backend' ),
			'manage_options',
			'voxel-paid-listings-settings',
			function() {
				$config = [
					'_wpnonce' => wp_create_nonce('vx_admin_plans'),
				];

				$schema = Module\get_settings_schema();
				$schema->set_value( \Voxel\get( 'paid_listings.settings', [] ) );
				$config['settings'] = $schema->export();

				wp_enqueue_script('vue');
				wp_enqueue_script('sortable');
				wp_enqueue_script('vue-draggable');
				wp_enqueue_script('vx:listing-plan-editor.js');

				require locate_template( 'app/modules/paid-listings/templates/backend/settings.php' );
			}
		);
	}

	protected function create_listing_plan() {
		check_admin_referer( 'voxel_manage_listing_plans' );
		if ( ! current_user_can( 'manage_options' ) ) {
			die;
		}

		if ( empty( $_POST['plan'] ) || ! is_array( $_POST['plan'] ) ) {
			die;
		}

		$key = sanitize_key( $_POST['plan']['key'] ?? '' );
		$label = sanitize_text_field( $_POST['plan']['label'] ?? '' );

		try {
			$plan = Module\Listing_Plan::create( [
				'key' => $key,
				'label' => $label,
			] );
		} catch ( \Exception $e ) {
			wp_die( $e->getMessage() );
		}

		wp_safe_redirect( $plan->get_edit_link() );
		exit;
	}

	protected function update_listing_plan() {
		try {
			$data = json_decode( stripslashes( $_POST['plan'] ), true );
			$key = $data['key'] ?? '';
			$plan = Module\Listing_Plan::get( $key );
			if ( ! $plan ) {
				throw new \Exception( __( 'Plan not found.', 'voxel-backend' ) );
			}

			$plan->update( $data );

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'errors' => [ $e->getMessage() ],
			] );
		}
	}

	protected function delete_listing_plan() {
		try {
			$data = $_POST['plan'] ?? [];
			$key = $data['key'] ?? '';
			$plan = Module\Listing_Plan::get( $key );
			if ( ! $plan ) {
				throw new \Exception( __( 'Plan not found.', 'voxel-backend' ) );
			}

			$plan->delete();

			return wp_send_json( [
				'redirect_to' => admin_url( 'admin.php?page=voxel-paid-listings-plans' ),
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'errors' => [ $e->getMessage() ],
			] );
		}
	}

}
