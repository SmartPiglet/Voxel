<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Template_Is_Post_Type_Archive extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'template:is_post_type_archive';
	}

	public function get_label(): string {
		return _x( 'Is post type archive', 'visibility rules', 'voxel-backend' );
	}

	protected function define_args(): void {
		$post_types = array_filter( \Voxel\Post_Type::get_all(), function( $post_type ) {
			return $post_type->wp_post_type->has_archive || $post_type->get_key() === 'post';
		} );

		$this->define_arg( 'post_type', [
			'type' => 'select',
			'label' => _x( 'Post type', 'visibility rules', 'voxel-backend' ),
			'choices' => array_map( function( $post_type ) {
				return $post_type->get_label();
			}, $post_types ),
		] );
	}

	public function evaluate(): bool {
		if ( $this->get_arg('post_type') === 'post' ) {
			return is_home();
		}

		return is_post_type_archive( $this->get_arg('post_type') );
	}
}
