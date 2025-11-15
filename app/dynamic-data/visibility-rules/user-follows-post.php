<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Follows_Post extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'user:follows_post';
	}

	public function get_label(): string {
		return _x( 'User follows post', 'visibility rules', 'voxel-backend' );
	}

	protected function define_args(): void {
		$this->define_arg( 'post_id', [
			'type' => 'text',
			'label' => _x( 'Post ID', 'visibility rules', 'voxel-backend' ),
			'description' => 'Leave empty for current post',
			'placeholder' => 'Current post',
		] );
	}

	public function evaluate(): bool {
		$current_user = \Voxel\current_user();
		if ( ! $current_user ) {
			return false;
		}

		if ( is_numeric( $this->get_arg('post_id') ) ) {
			return $current_user->follows_post( $this->get_arg('post_id') );
		} else {
			$post = \Voxel\get_current_post();
			if ( ! $post ) {
				return false;
			}

			return $current_user->follows_post( $post->get_id() );
		}
	}
}
