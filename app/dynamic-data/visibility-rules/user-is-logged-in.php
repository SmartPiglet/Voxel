<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Is_Logged_In extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'user:logged_in';
	}

	public function get_label(): string {
		return _x( 'User is logged in', 'visibility rules', 'voxel-backend' );
	}

	public function evaluate(): bool {
		return !! is_user_logged_in();
	}

}
