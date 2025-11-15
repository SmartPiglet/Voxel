<?php

namespace Voxel\Product_Types\Variations;

use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Variation_Base_Price_Field extends Base_Variation_Field {

	protected $props = [
		'key' => 'base-price',
		'label' => 'Base price',
	];

	public function set_schema( Data_Object $schema ): void {
		$field_schema = Schema::Object( [
			'amount' => Schema::Float()->min(0),
		] );

		if ( $this->product_type->config( 'modules.base_price.discount_price.enabled' ) ) {
			$field_schema->set_prop( 'discount_amount', Schema::Float()->min(0) );
		}

		$schema->set_prop( 'base_price', $field_schema );
	}

	public function validate( $variation ): void {
		if ( $variation['enabled'] === true ) {
			if ( $variation['config']['base_price']['amount'] === null ) {
				throw new \Exception( \Voxel\replace_vars(
					_x( '@field_name: Base price is required', 'field validation', 'voxel' ), [
						'@field_name' => $this->product_field->get_label(),
					]
				) );
			}

			if ( $variation['config']['base_price']['discount_amount'] !== null && $variation['config']['base_price']['discount_amount'] > $variation['config']['base_price']['amount'] ) {
				throw new \Exception( \Voxel\replace_vars(
					_x( '@field_name: Discount price cannot be larger than regular price', 'field validation', 'voxel' ), [
						'@field_name' => $this->product_field->get_label(),
					]
				) );
			}
		}
	}

	public function frontend_props(): array {
		return [
			'discount_price' => [
				'enabled' => $this->product_type->config( 'modules.base_price.discount_price.enabled' ),
			],
		];
	}
}
