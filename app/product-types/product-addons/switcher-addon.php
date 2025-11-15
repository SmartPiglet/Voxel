<?php

namespace Voxel\Product_Types\Product_Addons;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object, Base_Data_Type};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Switcher_Addon extends Base_Addon {

	protected $props = [
		'type' => 'switcher',
		'key' => 'switcher-addon',
		'label' => 'Switcher',
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
					_x( '@addon_name: Price is required', 'switcher addon', 'voxel' ), [
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
			'enabled' => Schema::Bool()->default(false),
		] );
	}

	public function validate_in_cart_item( $value ): void {
		if ( $this->is_required() && ! $value['enabled'] ) {
			throw new \Exception( \Voxel\replace_vars( _x( '@addon_name is required', 'switcher addon', 'voxel' ), [
				'@addon_name' => $this->get_label(),
			] ) );
		}
	}

	public function get_pricing_summary( $value ) {
		$config = $this->get_value();
		if ( ! $value['enabled'] ) {
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
		} elseif ( $this->custom_price !== null ) {
			$custom_amount = $this->custom_price['prices']['addons'][ $this->get_key() ]['price'];
			if ( $custom_amount !== null ) {
				$amount = $custom_amount;
			} else {
				$amount = $config['price'];
			}
		} else {
			$amount = $config['price'];
		}

		return [
			'type' => 'switcher',
			'label' => $this->get_label(),
			'key' => $this->get_key(),
			'repeat' => $this->repeat_config ? [
				'length' => $this->repeat_config['length'],
				'mode' => $this->repeat_config['mode'],
			] : null,
			'amount' => $amount,
		];
	}

	public function product_form_frontend_props(): array {
		$value = $this->get_value();
		return [
			'price' => $value['price'],
		];
	}

	public function is_active(): bool {
		$value = $this->get_value();
		return ( $this->is_required() || $value['enabled'] ) && $value['price'] !== null;
	}
}
