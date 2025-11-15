<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Is_Author extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'user:is_author';
	}

	public function get_label(): string {
		return _x( 'User is author of current post', 'visibility rules', 'voxel-backend' );
	}

	public function evaluate(): bool {
		$post = \Voxel\get_current_post();
		if ( ! ( $post && is_user_logged_in() ) ) {
			return false;
		}

		return $post->get_author_id() === absint( get_current_user_id() );
	}
}
