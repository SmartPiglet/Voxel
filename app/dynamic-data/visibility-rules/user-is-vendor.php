<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Is_Vendor extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'user:is_vendor';
	}

	public function get_label(): string {
		return _x( 'User is a Stripe Connect vendor', 'visibility rules', 'voxel-backend' );
	}

	public function evaluate(): bool {
		$current_user = \Voxel\current_user();
		if ( ! $current_user ) {
			return false;
		}

		return $current_user->is_active_vendor();
	}
}
