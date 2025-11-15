<?php

namespace Voxel\Product_Types\Product_Attributes;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Attribute {

	protected
		$product_type,
		$product_field;

	public function __construct( $props = [] ) {
		foreach ( $props as $key => $value ) {
			if ( array_key_exists( $key, $this->props ) ) {
				$this->props[ $key ] = $value;
			}
		}
	}

	public function get_key() {
		return $this->props['key'];
	}

	public function get_label() {
		return $this->props['label'];
	}

	public function get_display_mode() {
		return $this->props['display_mode'];
	}

	public function get_props(): array {
		return $this->props;
	}

	public function set_product_type( \Voxel\Product_Type $product_type ) {
		$this->product_type = $product_type;
	}

	public function get_product_type() {
		return $this->product_type;
	}

	public function set_product_field( \Voxel\Post_Types\Fields\Product_Field $product_field ) {
		$this->product_field = $product_field;
	}

	public function get_product_field() {
		return $this->product_field;
	}
}
