<?php

namespace Voxel\Product_Types\Product_Fields;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Base_Price_Field extends Base_Product_Field {

	protected $props = [
		'key' => 'base-price',
		'label' => 'Price',
		'description' => '',
		'placeholder' => '',
	];

	public function get_models(): array {
		return [
			'label' => Form_Models::Text( [
				'label' => 'Label',
				'classes' => 'x-col-12',
			] ),
			'description' => Form_Models::Textarea( [
				'label' => 'Description',
				'classes' => 'x-col-12',
			] ),
		];
	}

	public function get_conditions(): array {
		return [
			'settings.product_mode' => [
				'compare' => 'in_array',
				'value' => [ 'regular', 'booking' ],
			],
			'modules.base_price.enabled' => true,
		];
	}

	public function set_schema( Data_Object $schema ): void {
		$field_schema = Schema::Object( [
			'amount' => Schema::Float()->min(0),
			'discount_amount' => Schema::Float()->min(0),
		] );

		$schema->set_prop( 'base_price', $field_schema );
	}

	public function validate( $value ): void {
		if ( $value['base_price']['amount'] === null ) {
			throw new \Exception( \Voxel\replace_vars(
				_x( '@field_name: Base price is required', 'field validation', 'voxel' ), [
					'@field_name' => $this->product_field->get_label(),
				]
			) );
		}

		if ( $value['base_price']['discount_amount'] !== null && $value['base_price']['discount_amount'] > $value['base_price']['amount'] ) {
			throw new \Exception( \Voxel\replace_vars(
				_x( '@field_name: Discount price cannot be larger than regular price', 'field validation', 'voxel' ), [
					'@field_name' => $this->product_field->get_label(),
				]
			) );
		}
	}

	public function frontend_props(): array {
		return [
			'discount_price' => [
				'enabled' => $this->product_type->config( 'modules.base_price.discount_price.enabled' ),
			],
		];
	}

	public function check_product_form_validity( $value ) {
		if ( $value['base_price']['amount'] === null ) {
			throw new \Exception;
		}
	}
}
