<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class User_Has_Bought_Product extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'user:has_bought_product';
	}

	public function get_label(): string {
		return _x( 'User has bought product', 'visibility rules', 'voxel-backend' );
	}

	protected function define_args(): void {
		$this->define_arg( 'product_id', [
			'type' => 'text',
			'label' => _x( 'Product ID', 'visibility rules', 'voxel-backend' ),
			'description' => 'Leave empty for current product',
			'placeholder' => 'Current product',
		] );
	}

	public function evaluate(): bool {
		$current_user = \Voxel\current_user();
		if ( ! $current_user ) {
			return false;
		}

		if ( is_numeric( $this->get_arg('product_id') ) ) {
			return $current_user->has_bought_product( $this->get_arg('product_id') );
		} else {
			$post = \Voxel\get_current_post();
			if ( ! $post ) {
				return false;
			}

			return $current_user->has_bought_product( $post->get_id() );
		}
	}
}
