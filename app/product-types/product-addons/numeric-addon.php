<?php

namespace Voxel\Product_Types\Product_Addons;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object, Base_Data_Type};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Numeric_Addon extends Base_Addon {

	protected $props = [
		'type' => 'numeric',
		'key' => 'numeric-addon',
		'label' => 'Numeric',
		'display_mode' => 'stepper', // stepper|input
	];

	public function get_models(): array {
		return [
			'label' => Form_Models::Text( [
				'label' => 'Label',
				'classes' => 'x-col-6',
			] ),
			'key' => Form_Models::Key( [
				'label' => 'Key',
				'classes' => 'x-col-6',
			] ),
			'description' => Form_Models::Textarea( [
				'label' => 'Description',
				'classes' => 'x-col-12',
			] ),
			'display_mode' => Form_Models::Select( [
				'label' => 'Display mode',
				'classes' => 'x-col-12',
				'choices' => [
					'stepper' => 'Stepper',
					'input' => 'Input',
				],
			] ),
			'required' => Form_Models::Switcher( [
				'label' => 'Is required?',
				'classes' => 'x-col-12',
			] ),
			'icon' => Form_Models::Icon( [
				'label' => 'Icon',
				'classes' => 'x-col-12',
			] ),
			'repeat' => Form_Models::Switcher( [
				'v-if' => \Voxel\replace_vars( '@module.enabled && @module.type === \'days\'', [
					'@module' => '$root.config.modules.booking',
				] ),
				'label' => 'Apply pricing to each day in booked day range',
				'classes' => 'x-col-12',
			] ),
		];
	}

	public function get_product_field_schema(): ?Data_Object {
		$addon_schema = Schema::Object( [
			'price' => Schema::Float()->min(0),
			'min' => Schema::Int()->min(1),
			'max' => Schema::Int()->min(1),
		] );

		if ( ! $this->is_required() ) {
			$addon_schema->set_prop( 'enabled', Schema::Bool()->default(false) );
		}

		return $addon_schema;
	}

	public function validate_in_product_field( $value ): void {
		if ( $this->is_required() || $value['enabled'] ) {
			if ( $value['price'] === null ) {
				throw new \Exception( \Voxel\replace_vars(
					_x( '@addon_name: Price is required', 'numeric addon', 'voxel' ), [
						'@addon_name' => $this->get_label(),
					]
				) );
			}

			if ( $value['min'] === null || $value['max'] === null ) {
				throw new \Exception( \Voxel\replace_vars(
					_x( '@addon_name: Minimum and maximum values are required', 'numeric addon', 'voxel' ), [
						'@addon_name' => $this->get_label(),
					]
				) );
			}

			if ( $value['min'] > $value['max'] ) {
				throw new \Exception( \Voxel\replace_vars(
					_x( '@addon_name: Minimum value cannot be larger than maximum', 'numeric addon', 'voxel' ), [
						'@addon_name' => $this->get_label(),
					]
				) );
			}
		}
	}

	public function get_custom_price_schema(): ?Base_Data_Type {
		return Schema::Object( [
			'price' => Schema::Float()->min(0),
		] );
	}

	public function get_product_form_schema(): ?Data_Object {
		return Schema::Object( [
			'quantity' => Schema::Int()->min(0),
		] );
	}

	public function product_form_frontend_props(): array {
		$value = $this->get_value();
		return [
			'price' => $value['price'],
			'min_units' => $value['min'],
			'max_units' => $value['max'],
			'display_mode' => $this->props['display_mode'],
		];
	}

	public function is_active(): bool {
		$value = $this->get_value();
		return ( $this->is_required() || $value['enabled'] )
			&& $value['price'] !== null
			&& $value['min'] !== null
			&& $value['max'] !== null
			&& $value['max'] >= $value['min'];
	}

	public function validate_in_cart_item( $value ): void {
		$config = $this->get_value();

		if ( $this->is_required() && $value['quantity'] <= 0 ) {
			throw new \Exception( \Voxel\replace_vars( _x( '@addon_name is required', 'numeric addon', 'voxel' ), [
				'@addon_name' => $this->get_label(),
			] ) );
		}

		if ( $value['quantity'] > 0 && $value['quantity'] < $config['min'] || $value['quantity'] > $config['max'] ) {
			throw new \Exception( \Voxel\replace_vars(
				_x( '@addon_name: Quantity must be between @min_quantity and @max_quantity', 'numeric addon', 'voxel' ), [
					'@addon_name' => $this->get_label(),
					'@min_quantity' => $config['min'],
					'@max_quantity' => $config['max'],
				]
			) );
		}
	}

	public function get_pricing_summary( $value ) {
		$config = $this->get_value();
		if ( $value['quantity'] < 1 ) {
			return null;
		}

		if ( $this->repeat_config !== null ) {
			$amount = 0;
			$repeat_start = new \DateTime( $this->repeat_config['start'], new \DateTimeZone('UTC') );
			$repeat_end = new \DateTime( $this->repeat_config['end'], new \DateTimeZone('UTC') );

			while ( $this->repeat_config['mode'] === 'nights' ? $repeat_start < $repeat_end : $repeat_start <= $repeat_end ) {
				$custom_price = $this->product_field->get_custom_price_for_date( $repeat_start );
				if ( $custom_price !== null ) {
					$custom_amount = $custom_price['prices']['addons'][ $this->get_key() ]['price'];
					if ( $custom_amount !== null ) {
						$amount += $custom_amount;
					} else {
						$amount += $config['price'];
					}
				} else {
					$amount += $config['price'];
				}

				$repeat_start->modify('+1 day');
			}

			$amount *= $value['quantity'];
		} elseif ( $this->custom_price !== null ) {
			$custom_amount = $this->custom_price['prices']['addons'][ $this->get_key() ]['price'];
			if ( $custom_amount !== null ) {
				$amount = $custom_amount * $value['quantity'];
			} else {
				$amount = $config['price'] * $value['quantity'];
			}
		} else {
			$amount = $config['price'] * $value['quantity'];
		}

		return [
			'type' => 'numeric',
			'label' => $this->get_label(),
			// 'label' => sprintf( '%s × %d', $this->get_label(), $value['quantity'] ),
			'key' => $this->get_key(),
			'quantity' => $value['quantity'],
			'repeat' => $this->repeat_config ? [
				'length' => $this->repeat_config['length'],
				'mode' => $this->repeat_config['mode'],
			] : null,
			'amount' => $amount,
		];
	}

}
