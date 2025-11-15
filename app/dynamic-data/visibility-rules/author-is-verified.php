<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Author_Is_Verified extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'author:is_verified';
	}

	public function get_label(): string {
		return _x( 'Author is verified', 'visibility rules', 'voxel-backend' );
	}

	public function evaluate(): bool {
		$author = \Voxel\get_current_author();
		if ( ! $author ) {
			return false;
		}

		return $author->is_verified();
	}
}
