<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Product_Is_Available extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'product:is_available';
	}

	public function get_label(): string {
		return _x( 'Product is available', 'visibility rules', 'voxel-backend' );
	}

	public function evaluate(): bool {
		$post = \Voxel\get_current_post();
		if ( ! $post ) {
			return false;
		}

		$field = $post->get_field('product');
		if ( ! ( $field && $field->get_type() === 'product' ) ) {
			return false;
		}

		return $field->is_available();
	}
}
