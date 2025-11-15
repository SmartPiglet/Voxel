<?php

namespace Voxel\Product_Types\Product_Form\Fields;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\Data_Object;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Field {

	protected
		$product_type,
		$product_field;

	protected $props = [];

	public function __construct( $props = [] ) {
		$this->props = array_merge( $this->base_props(), $this->props );

		// override props if any provided as a parameter
		foreach ( $props as $key => $value ) {
			if ( array_key_exists( $key, $this->props ) ) {
				$this->props[ $key ] = $value;
			}
		}
	}

	protected function base_props(): array {
		return [
			'key' => '',
		];
	}

	public function get_key() {
		return $this->props['key'];
	}

	public function set_product_type( \Voxel\Product_Type $product_type ): void {
		$this->product_type = $product_type;
	}

	public function get_product_type(): \Voxel\Product_Type {
		return $this->product_type;
	}

	public function set_product_field( \Voxel\Post_Types\Fields\Product_Field $product_field ): void {
		$this->product_field = $product_field;
	}

	public function get_product_field(): \Voxel\Post_Types\Fields\Product_Field {
		return $this->product_field;
	}

	public function get_props(): array {
		return $this->props;
	}

	public function set_schema( Data_Object $schema ): void {
		//
	}

	public function frontend_props(): array {
		return [];
	}

	public function get_component_key(): string {
		return 'field-'.$this->get_key();
	}

	public function get_frontend_config() {
		return [
			'key' => $this->get_key(),
			'component_key' => $this->get_component_key(),
			'props' => $this->frontend_props(),
		];
	}

	public function get_conditions(): array {
		return [];
	}

	public function passes_conditions(): bool {
		return $this->product_type->repository->evaluate_conditions( $this->get_conditions() );
	}

	public function get_field_templates() {
		return [];
	}

	public function validate( $value ) {
		//
	}
}
