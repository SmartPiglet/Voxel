<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Template_Is_Single_Post extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'template:is_single_post';
	}

	public function get_label(): string {
		return _x( 'Is single post', 'visibility rules', 'voxel-backend' );
	}

	protected function define_args(): void {
		$post_types = array_filter( \Voxel\Post_Type::get_all(), function( $post_type ) {
			return $post_type->wp_post_type->public;
		} );

		$this->define_arg( 'post_type', [
			'type' => 'select',
			'label' => _x( 'Post type', 'visibility rules', 'voxel-backend' ),
			'choices' => array_map( function( $post_type ) {
				return $post_type->get_label();
			}, $post_types ) + [ ':custom' => '— Specific post' ],
		] );

		$this->define_arg( 'post_id', [
			'v-if' => 'rule.post_type === \':custom\'',
			'type' => 'text',
			'label' => _x( 'Enter post ID or slug', 'visibility rules', 'voxel-backend' ),
		] );
	}

	public function evaluate(): bool {
		if ( $this->get_arg('post_type') === ':custom' ) {
			return is_single( $this->get_arg('post_id') );
		}

		return is_singular( $this->get_arg('post_type') );
	}
}
