<?php

namespace Voxel\Product_Types\Data_Inputs;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Base_Data_Type, Data_Object, Data_String};

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Data_Input {

	protected
		$props,
		$product_type;

	abstract public function get_type(): string;

	abstract public function get_schema(): Data_Object;

	abstract public function get_controls(): array;

	abstract public function get_product_form_schema(): Base_Data_Type;

	abstract public function validate_in_cart_item( $value ): void;

	public function sanitize_in_cart_item( $value ) {
		return $value;
	}

	public function store_in_order_item( $value ): ?array {
		return [
			'type' => $this->get_type(),
			'label' => $this->get_label(),
			'value' => $value,
		];
	}

	public function __construct( $props = [] ) {
		$schema = $this->get_schema();
		if ( ! $schema->get_prop('key') instanceof Data_String ) {
			throw new \Exception( 'Data input `key` prop must be a String' );
		}

		if ( ! $schema->get_prop('label') instanceof Data_String ) {
			throw new \Exception( 'Data input `label` prop must be a String' );
		}

		$schema->set_prop( 'type', Schema::Const( $this->get_type() ) );
		$schema->set_value( $props );
		$this->props = $schema->export();
	}

	public function get_props(): array {
		return $this->props;
	}

	public function get_prop( $key ) {
		return $this->props[ $key ] ?? null;
	}

	public function set_product_type( \Voxel\Product_Type $product_type ): void {
		$this->product_type = $product_type;
	}

	public function get_product_type(): ?\Voxel\Product_Type {
		return $this->product_type;
	}

	public function get_product_form_frontend_config(): array {
		return [
			'type' => $this->get_type(),
			'key' => $this->get_key(),
			'component_key' => sprintf( 'data-input-%s', $this->get_type() ),
			'label' => $this->get_label(),
			'props' => $this->get_product_form_frontend_props(),
			'required' => $this->is_required(),
		];
	}

	public function get_product_form_frontend_props(): array {
		return [];
	}

	public function get_key(): ?string {
		return $this->get_prop('key');
	}

	public function get_label(): ?string {
		return $this->get_prop('label');
	}

	public function is_required(): bool {
		return !! $this->get_prop('required');
	}
}
