<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Author_Is_Vendor extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'author:is_vendor';
	}

	public function get_label(): string {
		return _x( 'Author is a Stripe Connect vendor', 'visibility rules', 'voxel-backend' );
	}

	public function evaluate(): bool {
		$author = \Voxel\get_current_author();
		if ( ! $author ) {
			return false;
		}

		return $author->is_active_vendor();
	}
}
