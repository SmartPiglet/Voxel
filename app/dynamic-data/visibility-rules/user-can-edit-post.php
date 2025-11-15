<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Can_Edit_Post extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'user:can_edit_post';
	}

	public function get_label(): string {
		return _x( 'User can edit current post', 'visibility rules', 'voxel-backend' );
	}

	public function evaluate(): bool {
		$post = \Voxel\get_current_post();
		if ( ! ( $post && is_user_logged_in() ) ) {
			return false;
		}

		return $post->is_editable_by_current_user();
	}
}
