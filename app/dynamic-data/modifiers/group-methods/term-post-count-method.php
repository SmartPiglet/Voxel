<?php

namespace Voxel\Dynamic_Data\Modifiers\Group_Methods;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Term_Post_Count_Method extends Base_Group_Method {

	public function get_key(): string {
		return 'post_count';
	}

	public function get_label(): string {
		return _x( 'Post count', 'modifiers', 'voxel-backend' );
	}

	protected function define_args(): void {
		$this->define_arg( [
			'type' => 'select',
			'label' => _x( 'Post type', 'modifiers', 'voxel-backend' ),
			'choices' => [ '' => 'All' ] + array_map( function( $post_type ) {
				return $post_type->get_label();
			}, \Voxel\Post_Type::get_voxel_types() ) ,
		] );
	}

	public function run( $group ) {
		$term = $group->get_term();
		$post_type_key = $this->get_arg(0);

		if ( empty( $post_type_key ) ) {
			return array_sum( $term->post_counts->get_counts() );
		}

		return $term->post_counts->get_count_for_post_type( $post_type_key );
	}
}
