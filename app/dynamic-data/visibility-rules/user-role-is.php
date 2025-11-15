<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Role_Is extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'user:role';
	}

	public function get_label(): string {
		return _x( 'User role is', 'visibility rules', 'voxel-backend' );
	}

	protected function define_args(): void {
		$this->define_arg( 'value', [
			'type' => 'select',
			'label' => _x( 'Value', 'visibility rules', 'voxel-backend' ),
			'choices' => array_map( function( $role ) {
				return $role['name'];
			}, wp_roles()->roles ),
		] );
	}

	public function evaluate(): bool {
		$current_user = \Voxel\current_user();
		if ( ! $current_user ) {
			return false;
		}

		return $current_user->has_role( $this->get_arg('value') );
	}
}
