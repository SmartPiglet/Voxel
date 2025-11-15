<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Can_Create_Post extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'user:can_create_post';
	}

	public function get_label(): string {
		return _x( 'User can create new post', 'visibility rules', 'voxel-backend' );
	}

	protected function define_args(): void {
		$this->define_arg( 'value', [
			'type' => 'select',
			'label' => _x( 'Post type', 'visibility rules', 'voxel-backend' ),
			'choices' => array_map( function( $post_type ) {
				return $post_type->get_label();
			}, \Voxel\Post_Type::get_voxel_types() ),
		] );
	}

	public function evaluate(): bool {
		$current_user = \Voxel\current_user();
		if ( ! $current_user ) {
			return false;
		}

		return $current_user->can_create_post( $this->get_arg('value') );
	}
}
