<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Author_Plan_Is extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'author:plan';
	}

	public function get_label(): string {
		return _x( 'Author membership plan is', 'visibility rules', 'voxel-backend' );
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
		$author = \Voxel\get_current_author();
		if ( ! $author ) {
			return false;
		}

		$membership = $author->get_membership();
		$plan_key = $membership->is_active() ? $membership->get_active_plan()->get_key() : 'default';
		return $plan_key === $this->get_arg('value');
	}
}
