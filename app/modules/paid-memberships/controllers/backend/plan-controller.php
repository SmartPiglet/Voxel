<?php

namespace Voxel\Modules\Paid_Memberships\Controllers\Backend;

use \Voxel\Modules\Paid_Memberships as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Plan_Controller extends \Voxel\Controllers\Base_Controller {

	protected function authorize() {
		return current_user_can( 'manage_options' );
	}

	protected function hooks() {
		$this->on( 'admin_post_voxel_create_membership_plan', '@create_plan' );

		$this->on( 'voxel_ajax_membership.update_plan', '@update_plan' );
		$this->on( 'voxel_ajax_membership.archive_plan', '@archive_plan' );
		$this->on( 'voxel_ajax_membership.delete_plan', '@delete_plan' );

		// role pricing templates
		$this->on( 'voxel_ajax_membership.create_pricing_template', '@create_pricing_template' );
		$this->on( 'voxel_ajax_membership.delete_pricing_template', '@delete_pricing_template' );
		$this->on( 'voxel_ajax_membership.update_pricing_template', '@update_pricing_template' );
	}

	protected function create_plan() {
		check_admin_referer( 'voxel_manage_membership_plans' );
		if ( ! current_user_can( 'manage_options' ) ) {
			die;
		}

		if ( empty( $_POST['membership_plan'] ) || ! is_array( $_POST['membership_plan'] ) ) {
			die;
		}

		$key = sanitize_key( $_POST['membership_plan']['key'] ?? '' );
		$label = sanitize_text_field( $_POST['membership_plan']['label'] ?? '' );
		$description = sanitize_textarea_field( $_POST['membership_plan']['description'] ?? '' );

		try {
			$plan = Module\Plan::create( [
				'key' => $key,
				'label' => $label,
				'description' => $description,
			] );
		} catch ( \Exception $e ) {
			wp_die( $e->getMessage() );
		}

		wp_safe_redirect( $plan->get_edit_link() );
		exit;
	}

	protected function update_plan() {
		try {
			$data = json_decode( stripslashes( $_POST['plan'] ), true );
			$key = sanitize_text_field( trim( $data['key'] ?? '' ) );
			$plan = Module\Plan::get( $key );
			if ( ! $plan ) {
				throw new \Exception( __( 'Plan not found.', 'voxel-backend' ) );
			}

			$plan->update( [
				'label' => sanitize_text_field( trim( $data['label'] ) ),
				'description' => wp_kses_post( trim( $data['description'] ) ),
				'settings' => $data['settings'] ?? [],
				'prices' => $data['prices'] ?? [],
			] );

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

	protected function archive_plan() {
		try {
			$data = $_POST['plan'] ?? [];
			$key = sanitize_text_field( trim( $data['key'] ?? '' ) );
			$plan = Module\Plan::get( $key );
			if ( ! $plan ) {
				throw new \Exception( __( 'Plan not found.', 'voxel-backend' ) );
			}

			$plan->update( 'archived', ! $plan->is_archived() );

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

	protected function delete_plan() {
		try {
			$data = $_POST['plan'] ?? [];
			$key = sanitize_text_field( trim( $data['key'] ?? '' ) );
			$plan = Module\Plan::get( $key );
			if ( ! $plan ) {
				throw new \Exception( __( 'Plan not found.', 'voxel-backend' ) );
			}

			$plans = \Voxel\get( 'plans' );
			unset( $plans[ $plan->get_key() ] );
			\Voxel\set( 'plans', $plans );

			return wp_send_json( [
				'redirect_to' => admin_url( 'admin.php?page=voxel-paid-members-plans' ),
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'errors' => [ $e->getMessage() ],
			] );
		}
	}

	protected function create_pricing_template() {
		try {
			$role = \Voxel\Role::get( sanitize_text_field( $_REQUEST['role_key'] ) );
			if ( ! ( $role && $role->is_managed_by_voxel() && $role->_is_safe_for_registration() ) ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ) );
			}

			if ( $role->get_pricing_page_id() ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ) );
			}

			$template_id = \Voxel\create_page(
				sprintf( _x( '%s pricing', 'pricing page title', 'voxel-backend' ), $role->get_label() ),
				sprintf( '%s-pricing', $role->get_key() )
			);

			if ( is_wp_error( $template_id ) ) {
				throw new \Exception( __( 'Could not create page.', 'voxel-backend' ) );
			}

			$settings = $role->get_editor_config()['settings'];
			$settings['templates']['pricing'] = $template_id;

			$role->set_config( [
				'settings' => $settings,
			] );

			return wp_send_json( [
				'success' => true,
				'template_id'=> $template_id,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function delete_pricing_template() {
		try {
			$role = \Voxel\Role::get( sanitize_text_field( $_REQUEST['role_key'] ) );
			if ( ! ( $role && $role->is_managed_by_voxel() && $role->_is_safe_for_registration() ) ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ) );
			}

			wp_delete_post( $role->get_pricing_page_id() );

			$settings = $role->get_editor_config()['settings'];
			$settings['templates']['pricing'] = null;

			$role->set_config( [
				'settings' => $settings,
			] );

			return wp_send_json( [
				'success' => true,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

	protected function update_pricing_template() {
		try {
			$role = \Voxel\Role::get( sanitize_text_field( $_REQUEST['role_key'] ) );
			if ( ! ( $role && $role->is_managed_by_voxel() && $role->_is_safe_for_registration() ) ) {
				throw new \Exception( __( 'Invalid request.', 'voxel-backend' ) );
			}

			$new_template_id = absint( $_REQUEST['template_id'] ?? null );
			if ( ! \Voxel\page_exists( $new_template_id ) ) {
				throw new \Exception( __( 'Provided page template does not exist.', 'voxel-backend' ) );
			}

			$settings = $role->get_editor_config()['settings'];
			$settings['templates']['pricing'] = $new_template_id;

			$role->set_config( [
				'settings' => $settings,
			] );

			return wp_send_json( [
				'success' => true,
				'template_id' => $new_template_id,
			] );
		} catch ( \Exception $e ) {
			return wp_send_json( [
				'success' => false,
				'message' => $e->getMessage(),
			] );
		}
	}

}
