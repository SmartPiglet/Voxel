<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Plan_Is extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'user:plan';
	}

	public function get_label(): string {
		return _x( 'User membership plan is', 'visibility rules', 'voxel-backend' );
	}

	protected function define_args(): void {
		$this->define_arg( 'value', [
			'type' => 'select',
			'label' => _x( 'Value', 'visibility rules', 'voxel-backend' ),
			'choices' => array_map( function( $plan ) {
				return $plan->get_label();
			}, \Voxel\Modules\Paid_Memberships\Plan::all() ),
		] );
	}

	public function evaluate(): bool {
		$current_user = \Voxel\current_user();
		if ( ! $current_user ) {
			return false;
		}

		$membership = $current_user->get_membership();
		$plan_key = $membership->is_active() ? $membership->get_active_plan()->get_key() : 'default';

		return $plan_key === $this->get_arg('value');
	}
}
