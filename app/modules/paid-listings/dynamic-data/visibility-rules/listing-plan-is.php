<?php

namespace Voxel\Modules\Paid_Listings\Dynamic_Data\Visibility_Rules;

use \Voxel\Modules\Paid_Listings as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Listing_Plan_Is extends \Voxel\Dynamic_Data\Visibility_Rules\Base_Visibility_Rule {

	public function get_type(): string {
		return 'listing:plan';
	}

	public function get_label(): string {
		return _x( 'Listing plan is', 'visibility rules', 'voxel-backend' );
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
		$post = \Voxel\get_current_post();
		if ( ! $post ) {
			return false;
		}

		$assigned_package = Module\get_assigned_package( $post );
		$assigned_plan = $assigned_package['plan'];
		if ( $assigned_plan === null ) {
			return false;
		}

		return $assigned_plan->get_key() === $this->get_arg('value');
	}
}
