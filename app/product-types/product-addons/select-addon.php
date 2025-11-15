<?php

namespace Voxel\Product_Types\Product_Addons;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object, Base_Data_Type};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Select_Addon extends Base_Addon {

	protected $props = [
		'type' => 'select',
		'key' => 'select-addon',
		'label' => 'Select',
		'choices' => [],
		'display_mode' => 'radio', // buttons|radio|dropdown
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
			'choices_mode' => Form_Models::Raw( function() { ?>
				<div class="ts-form-group x-col-12">
					<label>Add-on choices</label>
					<select @change="updateAddonType($event.target.value)">
						<option value="custom-select">Vendor-defined</option>
						<option selected value="select">Predefined</option>
					</select>
				</div>
			<?php } ),
			'choices' => Form_Models::Raw( function() { ?>
				<div class="ts-form-group x-col-12">
					<addon-choices :addon="addon" :models="[
						{ key: 'label', type: 'text', columns: 5, columnLabel: 'Label', },
						{ key: 'value', type: 'key', columns: 5, columnLabel: 'Value', },
					]"></addon-choices>
				</div>
			<?php } ),
			'display_mode' => Form_Models::Select( [
				'label' => 'Display mode',
				'classes' => 'x-col-12',
				'choices' => [
					'buttons' => 'Buttons',
					'radio' => 'Radio',
					'dropdown' => 'Dropdown',
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
		}

		return $choices;
	}

	public function get_active_choices(): array {
		$choices = [];
		$value = $this->get_value();
		foreach ( $this->get_choices() as $choice ) {
			$config = $value['choices'][ $choice['value'] ];
			if ( $config['enabled'] && $config['price'] !== null ) {
				$choices[ $choice['value'] ] = [
					'value' => $choice['value'],
					'label' => $choice['label'],
					'price' => $config['price'],
				];
			}
		}

		return $choices;
	}

	public function get_product_field_schema(): ?Data_Object {
		$choices = [];
		foreach ( $this->get_choices() as $choice ) {
			$choices[ $choice['value'] ] = Schema::Object( [
				'enabled' => Schema::Bool()->default(false),
				'price' => Schema::Float()->min(0),
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

	public function validate_in_product_field( $value ): void {
		if ( $this->is_required() || $value['enabled'] ) {
			$has_single_price = false;
			foreach ( $value['choices'] as $choice ) {
				if ( $choice['enabled'] ) {
					if ( $choice['price'] === null ) {
						throw new \Exception( \Voxel\replace_vars(
							_x( '@addon_name: Price is required', 'select addon', 'voxel' ), [
								'@addon_name' => $this->get_label(),
							]
						) );
					}

					$has_single_price = true;
				}
			}

			if ( ! $has_single_price ) {
				throw new \Exception( \Voxel\replace_vars(
					_x( '@addon_name: Price is required', 'select addon', 'voxel' ), [
						'@addon_name' => $this->get_label(),
					]
				) );
			}
		}
	}

	public function get_custom_price_schema(): ?Base_Data_Type {
		$schema = Schema::Object( [] );

		foreach ( $this->get_choices() as $choice ) {
			$schema->set_prop( $choice['value'], Schema::Object( [
				'price' => Schema::Float()->min(0),
			] ) );
		}

		return $schema;
	}

	public function product_field_frontend_props(): array {
		return [
			'choices' => $this->get_choices(),
		];
	}

	public function get_product_form_schema(): ?Data_Object {
		return Schema::Object( [
			'selected' => Schema::String(),
		] );
	}

	public function validate_in_cart_item( $value ): void {
		$config = $this->get_value();
		$selected = $config['choices'][ $value['selected'] ] ?? null;

		if ( $this->is_required() && $selected === null ) {
			throw new \Exception( \Voxel\replace_vars( _x( '@addon_name is required', 'select addon', 'voxel' ), [
				'@addon_name' => $this->get_label(),
			] ) );
		}

		if ( $selected !== null && ! $selected['enabled'] ) {
			throw new \Exception( \Voxel\replace_vars( _x( '@addon_name: @choice is not available', 'select addon', 'voxel' ), [
				'@addon_name' => $this->get_label(),
				'@choice' => $value['selected'],
			] ) );
		}
	}

	public function get_pricing_summary( $value ) {
		$config = $this->get_value();
		if ( $value['selected'] === null ) {
			return null;
		}

		$selected = $config['choices'][ $value['selected'] ];

		if ( $this->repeat_config !== null ) {
			$amount = 0;
			$repeat_start = new \DateTime( $this->repeat_config['start'], new \DateTimeZone('UTC') );
			$repeat_end = new \DateTime( $this->repeat_config['end'], new \DateTimeZone('UTC') );

			while ( $this->repeat_config['mode'] === 'nights' ? $repeat_start < $repeat_end : $repeat_start <= $repeat_end ) {
				$custom_price = $this->product_field->get_custom_price_for_date( $repeat_start );
				if ( $custom_price !== null ) {
					$custom_amount = $custom_price['prices']['addons'][ $this->get_key() ][ $value['selected'] ]['price'];
					if ( $custom_amount !== null ) {
						$amount += $custom_amount;
					} else {
						$amount += $selected['price'];
					}
				} else {
					$amount += $selected['price'];
				}

				$repeat_start->modify('+1 day');
			}
		} elseif ( $this->custom_price !== null ) {
			$custom_amount = $this->custom_price['prices']['addons'][ $this->get_key() ][ $value['selected'] ]['price'];
			if ( $custom_amount !== null ) {
				$amount = $custom_amount;
			} else {
				$amount = $selected['price'];
			}
		} else {
			$amount = $selected['price'];
		}

		return [
			'type' => 'select',
			'label' => $this->get_label(),
			'selected' => $value['selected'],
			// 'label' => sprintf( '%s: %s', $this->get_label(), $value['selected'] ),
			'key' => $this->get_key(),
			'repeat' => $this->repeat_config ? [
				'length' => $this->repeat_config['length'],
				'mode' => $this->repeat_config['mode'],
			] : null,
			'amount' => $amount,
		];
	}

	public function product_form_frontend_props(): array {
		$choices = [];
		$value = $this->get_value();
		foreach ( $this->get_choices() as $choice ) {
			$config = $value['choices'][ $choice['value'] ];
			if ( $config['enabled'] && $config['price'] !== null ) {
				$choices[ $choice['value'] ] = [
					'value' => $choice['value'],
					'label' => $choice['label'],
					'price' => $config['price'],
				];
			}
		}

		return [
			'display_mode' => $this->props['display_mode'],
			'choices' => (object) $choices,
		];
	}

	public function is_active(): bool {
		$value = $this->get_value();
		if ( ! ( $this->is_required() || $value['enabled'] ) ) {
			return false;
		}

		if ( $value['choices'] === null ) {
			return false;
		}

		foreach ( $value['choices'] as $choice ) {
			if ( $choice['enabled'] && $choice['price'] !== null ) {
				return true;
			}
		}

		return false;
	}
}
