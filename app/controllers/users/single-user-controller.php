<?php

namespace Voxel\Controllers\Users;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Single_User_Controller extends \Voxel\Controllers\Base_Controller {

	protected function hooks() {
		if ( current_user_can( 'manage_options' ) ) {
			$this->on( 'edit_user_profile', '@show_custom_fields' );
			$this->on( 'show_user_profile', '@show_custom_fields' );
			$this->on( 'personal_options_update', '@save_custom_fields' );
			$this->on( 'edit_user_profile_update', '@save_custom_fields' );
		}
	}

	protected function show_custom_fields( $user ) {
		$user = \Voxel\User::get( $user );
		$profile = $user->get_or_create_profile();
		$membership = $user->get_membership();
		$plan = $membership->get_selected_plan();

		require locate_template( 'templates/backend/user-custom-fields.php' );
	}

	protected function save_custom_fields( $user_id ) {
		if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'update-user_' . $user_id ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		$data = $_POST['vx_details'] ?? [];
		if ( ! is_array( $data ) || empty( $data ) ) {
			return;
		}
	}
}