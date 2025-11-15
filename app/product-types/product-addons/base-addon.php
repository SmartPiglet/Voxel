<?php

namespace Voxel\Product_Types\Product_Addons;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object, Base_Data_Type};

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Addon {

	protected
		$key,
		$props,
		$product_type,
		$product_field;

	public function __construct( $props = [] ) {
		$this->props = array_merge( $this->base_props(), $this->props );

		// override props if any provided as a parameter
		foreach ( $props as $key => $value ) {
			if ( array_key_exists( $key, $this->props ) ) {
				$this->props[ $key ] = $value;
			}
		}

		$this->key = $this->props['key'];
	}

	protected function base_props(): array {
		return [
			'type' => '',
			'key' => '',
			'label' => 'Add-on',
			'description' => '',
			'icon' => '',
			'required' => false,
			'repeat' => false,
		];
	}

	public function get_models(): array {
		return [];
	}

	public function get_product_field_schema(): ?Data_Object {
		//
	}

	public function get_custom_price_schema(): ?Base_Data_Type {
		//
	}

	public function get_product_form_schema(): ?Data_Object {
		return Schema::Object( [] );
	}

	public function sanitize_in_product_field( $value, $raw_value ) {
		return $value;
	}

	public function validate_in_product_field( $value ): void {
		//
	}

	public function validate_in_cart_item( $value ): void {
		//
	}

	public function get_pricing_summary( $value ) {
		//
	}

	public function update_in_product_field( $value ) {
		return $value;
	}

	public function editing_value_in_product_field( $value ) {
		return $value;
	}

	public function get_key(): string {
		return $this->props['key'];
	}

	public function get_type(): string {
		return $this->props['type'];
	}

	public function get_label(): string {
		return $this->props['label'];
	}

	public function get_description(): string {
		return $this->props['description'];
	}

	public function is_required(): bool {
		return !! $this->props['required'];
	}

	public function get_props(): array {
		return $this->props;
	}

	public function get_prop( $key ) {
		return $this->props[ $key ] ?? null;
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

	public function product_field_frontend_props(): array {
		return [];
	}

	public function get_product_field_component_key(): string {
		return 'addon-'.$this->get_type();
	}

	public function repeat_in_booking_range(): bool {
		return !! $this->props['repeat'];
	}

	protected $repeat_config = null;
	public function set_repeat_config( $repeat_config ) {
		$this->repeat_config = $repeat_config;
	}

	protected $custom_price = null;
	public function set_custom_price( $custom_price ) {
		$this->custom_price = $custom_price;
	}

	public function get_product_field_frontend_config() {
		return [
			'type' => $this->get_type(),
			'key' => $this->get_key(),
			'required' => $this->is_required(),
			'component_key' => $this->get_product_field_component_key(),
			'label' => $this->get_label(),
			'description' => $this->get_description(),
			'props' => $this->product_field_frontend_props(),
			'validation' => [
				'errors' => [],
			],
		];
	}

	public function get_product_type_editor_config(): array {
		return [
			'props' => $this->get_props(),
		];
	}

	// should this addon be displayed in the product form
	public function is_active(): bool {
		return true;
	}

	public function product_form_frontend_props(): array {
		return [];
	}

	public function get_product_form_component_key(): string {
		return 'addon-'.$this->get_type();
	}

	public function get_product_form_frontend_config() {
		return [
			'type' => $this->get_type(),
			'key' => $this->get_key(),
			'component_key' => $this->get_product_form_component_key(),
			'label' => $this->get_label(),
			'props' => $this->product_form_frontend_props(),
			'required' => $this->is_required(),
			'repeat' => $this->repeat_in_booking_range(),
		];
	}

	public function get_value() {
		$value = $this->product_field->get_value();
		return $value['addons'][ $this->get_key() ];
	}
}
