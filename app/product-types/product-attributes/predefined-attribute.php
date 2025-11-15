<?php

namespace Voxel\Product_Types\Product_Attributes;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Predefined_Attribute extends Base_Attribute {

	protected $props = [
		'key' => 'attribute',
		'label' => 'Attribute',
		'description' => '',
		'choices' => [],
		'display_mode' => 'dropdown',
	];

	public function get_choices(): array {
		$choices = [];
		foreach ( (array) $this->props['choices'] as $choice ) {
			if ( ! is_array( $choice ) || empty( $choice['value'] ) ) {
				continue;
			}

			$choices[ $choice['value'] ] = [
				'value' => $choice['value'],
				'label' => $choice['label'] ?? '',
			];

			if ( $this->props['display_mode'] === 'colors' ) {
				$choices[ $choice['value'] ]['color'] = $choice['color'] ?? null;
			}
		}

		return $choices;
	}

	public function get_product_field_schema(): ?Data_Object {
		$choices = [];
		foreach ( $this->get_choices() as $choice ) {
			$choices[ $choice['value'] ] = Schema::Object( [
				'enabled' => Schema::Bool()->default(false),
			] );
		}

		$addon_schema = Schema::Object( [
			'choices' => Schema::Object( $choices ),
		] );

		if ( ! $this->is_required() ) {
			$addon_schema->set_prop( 'enabled', Schema::Bool()->default(false) );
		}

		return $addon_schema;
	}

	public function product_field_frontend_props(): array {
		return [
			'choices' => $this->get_choices(),
		];
	}

	public function validate_in_product_field( $value ): void {
		if ( $this->is_required() || $value['enabled'] ) {
			$has_single_price = false;
			foreach ( $value['choices'] as $choice ) {
				if ( $choice['enabled'] ) {
					if ( $choice['price'] === null ) {
						throw new \Exception( \Voxel\replace_vars(
							_x( '@addon_name: Price is required', 'attributes', 'voxel' ), [
								'@addon_name' => $this->get_label(),
							]
						) );
					}

					$has_single_price = true;
				}
			}

			if ( ! $has_single_price ) {
				throw new \Exception( \Voxel\replace_vars(
					_x( '@addon_name: Price is required', 'attributes', 'voxel' ), [
						'@addon_name' => $this->get_label(),
					]
				) );
			}
		}
	}

	public function get_description(): string {
		return $this->props['description'];
	}

	public function get_product_field_frontend_config() {
		return [
			'key' => $this->get_key(),
			'label' => $this->get_label(),
			'description' => $this->get_description(),
			'props' => $this->product_field_frontend_props(),
			'validation' => [
				'errors' => [],
			],
		];
	}

	public function get_product_form_frontend_config() {
		$value = $this->product_field->get_value();
		$config = $value['variations']['attributes'][ $this->get_key() ];

		$frontend_choices = [];
		foreach ( $this->get_choices() as $choice ) {
			if ( ( $config['choices'][ $choice['value'] ]['enabled'] ?? null ) === true ) {
				$frontend_choices[ 'choice_'.$choice['value'] ] = $choice;
			}
		}

		return [
			'key' => $this->get_key(),
			'label' => $this->get_label(),
			'props' => [
				'display_mode' => $this->props['display_mode'],
				'choices' => (object) $frontend_choices,
			],
		];
	}

}
