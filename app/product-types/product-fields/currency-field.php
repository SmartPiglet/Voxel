<?php

namespace Voxel\Product_Types\Product_Fields;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Currency_Field extends Base_Product_Field {

	protected $props = [
		'key' => 'currency',
		'label' => 'Currency',
	];

	public function get_conditions(): array {
		return [
			'modules.custom_currency.enabled' => true,
		];
	}

	public function set_schema( Data_Object $schema ): void {
		$schema->set_prop( 'currency', Schema::String() );
	}

	public function sanitize( $value, $raw_value ) {
		if ( ! is_string( $value ) || empty( $value ) ) {
			return null;
		}

		return strtoupper( $value );
	}

	public function validate( $value ): void {
		if ( $value !== null && ! \Voxel\Utils\Currency_List::exists( $value ) ) {
			throw new \Exception( \Voxel\replace_vars(
				_x( '@field_name: Invalid currency provided.', 'field validation', 'voxel' ), [
					'@field_name' => $this->product_field->get_label(),
				]
			) );
		}
	}

}
