<?php

namespace Voxel\Modules\Paid_Listings\Dynamic_Data\Visibility_Rules;

use \Voxel\Modules\Paid_Listings as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Has_Listing_Plan extends \Voxel\Dynamic_Data\Visibility_Rules\Base_Visibility_Rule {

	public function get_type(): string {
		return 'user:has_listing_plan';
	}

	public function get_label(): string {
		return _x( 'User has bought listing plan', 'visibility rules', 'voxel-backend' );
	}

	protected function define_args(): void {
		$choices = [];
		foreach ( Module\Listing_Plan::all() as $plan ) {
			$choices[ $plan->get_key() ] = $plan->get_label();
		}

		$this->define_arg( 'value', [
			'type' => 'select',
			'label' => _x( 'Value', 'visibility rules', 'voxel-backend' ),
			'choices' => $choices,
		] );
	}

	public function evaluate(): bool {
		$user = \Voxel\get_current_user();
		if ( ! $user ) {
			return false;
		}

		$plan = Module\Listing_Plan::get( $this->get_arg('value') );
		if ( ! $plan ) {
			return false;
		}

		return Module\user_has_bought_plan( $user, $plan );
	}
}
