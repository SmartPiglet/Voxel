<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Is_Verified extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'user:is_verified';
	}

	public function get_label(): string {
		return _x( 'User is verified', 'visibility rules', 'voxel-backend' );
	}

	public function evaluate(): bool {
		$current_user = \Voxel\current_user();
		if ( ! $current_user ) {
			return false;
		}

		return $current_user->is_verified();
	}
}
