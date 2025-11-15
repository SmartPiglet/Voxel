<?php

namespace Voxel\Dynamic_Data\Visibility_Rules;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Product_Type_Is extends Base_Visibility_Rule {

	public function get_type(): string {
		return 'product_type:is';
	}

	public function get_label(): string {
		return _x( 'Product type is', 'visibility rules', 'voxel-backend' );
	}

	protected function define_args(): void {
		$choices = [];
		foreach ( \Voxel\Product_Type::all(true) as $product_type ) {
			$choices[ $product_type->get_key() ] = $product_type->get_label();
		}

		$this->define_arg( 'value', [
			'type' => 'select',
			'label' => _x( 'Value', 'visibility rules', 'voxel-backend' ),
			'choices' => $choices,
		] );
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

		$product_type = $field->get_product_type();
		if ( ! $product_type ) {
			return false;
		}

		return $product_type->get_key() === $this->get_arg('value');
	}
}
