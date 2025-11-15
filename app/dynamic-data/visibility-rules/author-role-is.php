<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Author_Role_Is extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'author:role';
	}

	public function get_label(): string {
		return _x( 'Author role is', 'visibility rules', 'voxel-backend' );
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
		$author = \Voxel\get_current_author();
		if ( ! $author ) {
			return false;
		}

		return $author->has_role( $this->get_arg('value') );
	}
}
