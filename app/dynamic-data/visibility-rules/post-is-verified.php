<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Post_Is_Verified extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'post:is_verified';
	}

	public function get_label(): string {
		return _x( 'Post is verified', 'visibility rules', 'voxel-backend' );
	}

	public function evaluate(): bool {
		$post = \Voxel\get_current_post();
		return $post ? $post->is_verified() : false;
	}
}
