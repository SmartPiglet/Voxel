<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Follows_Author extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'user:follows_author';
	}

	public function get_label(): string {
		return _x( 'User follows author', 'visibility rules', 'voxel-backend' );
	}

	protected function define_args(): void {
		$this->define_arg( 'author_id', [
			'type' => 'text',
			'label' => _x( 'Author ID', 'visibility rules', 'voxel-backend' ),
			'description' => 'Leave empty for current author',
			'placeholder' => 'Current author',
		] );
	}

	public function evaluate(): bool {
		$current_user = \Voxel\current_user();
		if ( ! $current_user ) {
			return false;
		}

		if ( is_numeric( $this->get_arg('author_id') ) ) {
			return $current_user->follows_user( $this->get_arg('author_id') );
		} else {
			$author = \Voxel\get_current_author();
			if ( ! $author ) {
				return false;
			}

			return $current_user->follows_user( $author->get_id() );
		}
	}
}
