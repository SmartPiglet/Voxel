<?php

namespace Voxel\Product_Types\Product_Addons;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Custom_Multiselect_Addon extends Custom_Select_Addon {

	protected $props = [
		'type' => 'custom-multiselect',
		'key' => 'multiselect-addon',
		'label' => 'Multi-select',
		'display_mode' => 'checkboxes', // buttons|checkboxes|cards
		'image_max_size' => 2000,
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
						<option selected value="custom-multiselect">Vendor-defined</option>
						<option value="multiselect">Predefined</option>
					</select>
				</div>
			<?php } ),
			'display_mode' => Form_Models::Select( [
				'label' => 'Display mode',
				'classes' => 'x-col-12',
				'choices' => [
					'buttons' => 'Buttons',
					'checkboxes' => 'Checkboxes',
					'cards' => 'Cards',
				],
			] ),
			'image_max_size' => Form_Models::Number( [
				'v-if' => 'addon.display_mode === "cards"',
				'label' => 'Max image size (kB)',
				'classes' => 'x-col-12',
			] ),
			'required' => Form_Models::Switcher( [
				'label' => 'Is required?',
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

	public function get_product_form_schema(): ?Data_Object {
		return Schema::Object( [
			'selected' => Schema::Object_List( [
				'item' => Schema::String(),
				'quantity' => Schema::Int()->default(1),
			] )->default([]),
		] );
	}


	public function validate_in_cart_item( $value ): void {
		$config = $this->get_value();

		if ( $this->is_required() && empty( $value['selected'] ) ) {
			throw new \Exception( \Voxel\replace_vars( _x( '@addon_name is required', 'select addon', 'voxel' ), [
				'@addon_name' => $this->get_label(),
			] ) );
		}

		foreach ( $value['selected'] as $choice ) {
			$selected = $config['choices'][ $choice['item'] ] ?? null;

			if ( $selected === null || $selected['price'] === null ) {
				throw new \Exception( \Voxel\replace_vars( _x( '@addon_name: @choice is not available', 'select addon', 'voxel' ), [
					'@addon_name' => $this->get_label(),
					'@choice' => $choice['item'],
				] ) );
			}

			if ( $selected['quantity']['enabled'] ) {
				if ( $choice['quantity'] < $selected['quantity']['min'] || $choice['quantity'] > $selected['quantity']['max'] ) {
					throw new \Exception( \Voxel\replace_vars(
						_x( '@addon_name: @choice quantity must be between @min_quantity and @max_quantity', 'select addon', 'voxel' ), [
							'@addon_name' => $this->get_label(),
							'@choice' =>  $choice['item'],
							'@min_quantity' => $selected['quantity']['min'],
							'@max_quantity' => $selected['quantity']['max'],
						]
					) );
				}
			} else {
				if ( $choice['quantity'] !== 1 ) {
					throw new \Exception( \Voxel\replace_vars( _x( '@addon_name: @choice quantity cannot be more than 1', 'select addon', 'voxel' ), [
						'@addon_name' => $this->get_label(),
						'@choice' => $choice['item'],
					] ) );
				}
			}
		}
	}

	public function get_pricing_summary( $value ) {
		$config = $this->get_value();
		if ( empty( $value['selected'] ) ) {
			return null;
		}

		$summary = [];
		$total_amount = 0;

		foreach ( $value['selected'] as $choice ) {
			$selected = $config['choices'][ $choice['item'] ];

			if ( $this->repeat_config !== null ) {
				$amount = 0;
				$repeat_start = new \DateTime( $this->repeat_config['start'], new \DateTimeZone('UTC') );
				$repeat_end = new \DateTime( $this->repeat_config['end'], new \DateTimeZone('UTC') );

				while ( $this->repeat_config['mode'] === 'nights' ? $repeat_start < $repeat_end : $repeat_start <= $repeat_end ) {
					$custom_price = $this->product_field->get_custom_price_for_date( $repeat_start );
					if ( $custom_price !== null ) {
						$custom_amount = $custom_price['prices']['addons'][ $this->get_key() ][ $choice['item'] ]['price'];
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

				if ( $selected['quantity']['enabled'] ) {
					$amount *= $choice['quantity'];
				}
			} elseif ( $this->custom_price !== null ) {
				$custom_amount = $this->custom_price['prices']['addons'][ $this->get_key() ][ $choice['item'] ]['price'];
				if ( $custom_amount !== null ) {
					$amount = $custom_amount;
				} else {
					$amount = $selected['price'];
				}

				if ( $selected['quantity']['enabled'] ) {
					$amount *= $choice['quantity'];
				}
			} else {
				$amount = $selected['price'];

				if ( $selected['quantity']['enabled'] ) {
					$amount *= $choice['quantity'];
				}
			}

			$summary[] = [
				'label' => $choice['item'],
				'key' => $choice['item'],
				'quantity' => $selected['quantity']['enabled'] ? $choice['quantity'] : null,
				'amount' => $amount,
			];

			$total_amount += $amount;
		}

		return [
			'type' => 'custom-multiselect',
			'label' => $this->get_label(),
			// 'label' => sprintf( '%s: %s', $this->get_label(), join( ', ', array_map( function( $choice ) use ( $config ) {
			// 	$selected = $config['choices'][ $choice['item'] ];
			// 	return $selected['quantity']['enabled']
			// 		? sprintf( '%s × %d', $choice['item'], $choice['quantity'] )
			// 		: $choice['item'];
			// }, $value['selected'] ) ) ),
			'key' => $this->get_key(),
			'summary' => $summary,
			'repeat' => $this->repeat_config ? [
				'length' => $this->repeat_config['length'],
				'mode' => $this->repeat_config['mode'],
			] : null,
			'amount' => $total_amount,
		];
	}
}
