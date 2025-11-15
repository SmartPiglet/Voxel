<?php

namespace Voxel\Product_Types\Product_Addons;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object, Base_Data_Type};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Custom_Select_Addon extends Base_Addon {

	protected $props = [
		'type' => 'custom-select',
		'key' => 'select-addon',
		'label' => 'Select',
		'display_mode' => 'dropdown', // buttons|radio|dropdown|cards
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
						<option selected value="custom-select">Vendor-defined</option>
						<option value="select">Predefined</option>
					</select>
				</div>
			<?php } ),
			'display_mode' => Form_Models::Select( [
				'label' => 'Display mode',
				'classes' => 'x-col-12',
				'choices' => [
					'buttons' => 'Buttons',
					'radio' => 'Radio',
					'dropdown' => 'Dropdown',
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
		$choice_props = [
			'price' => Schema::Float()->min(0),
			'quantity' => Schema::Object( [
				'enabled' => Schema::Bool()->default(false),
				'min' => Schema::Int()->min(0),
				'max' => Schema::Int()->min(0),
			] ),
		];

		if ( $this->props['display_mode'] === 'cards' ) {
			$choice_props['image'] = Schema::Int();
			$choice_props['subheading'] = Schema::String();
		}

		$addon_schema = Schema::Object( [
			'choices' => Schema::Keyed_Object_List( $choice_props )
				->validator( function( $item, $key ) {
					return is_string( $key ) && ! empty( $key );
				} ),
		] );

		if ( ! $this->is_required() ) {
			$addon_schema->set_prop( 'enabled', Schema::Bool()->default(false) );
		}

		return $addon_schema;
	}

	public function get_custom_price_schema(): ?Base_Data_Type {
		$schema = Schema::Keyed_Object_List( [
			'price' => Schema::Float()->min(0),
		] );

		return $schema;
	}

	public function product_field_frontend_props(): array {
		return [
			'display_mode' => $this->props['display_mode'],
			'choice' => [
				'value' => null,
				'price' => null,
				'quantity' => [
					'enabled' => false,
					'min' => 1,
					'max' => 5,
				],
				'image' => [],
				'subheading' => null,
			],
		];
	}

	public function product_form_frontend_props(): array {
		$choices = [];
		$value = $this->get_value();
		foreach ( (array) $value['choices'] as $choice_value => $choice ) {
			if ( $choice['price'] !== null ) {
				$choices[ $choice_value ] = [
					'value' => $choice_value,
					'label' => $choice_value,
					'price' => $choice['price'],
					'quantity' => [
						'enabled' => $choice['quantity']['enabled'],
						'min' => $choice['quantity']['min'],
						'max' => $choice['quantity']['max'],
					],
				];

				if ( $this->props['display_mode'] === 'cards' ) {
					$choices[ $choice_value ]['subheading'] = $choice['subheading'];
					$choices[ $choice_value ]['image'] = null;

					if ( $image_url = wp_get_attachment_image_url( $choice['image'], 'medium' ) ) {
						$choices[ $choice_value ]['image'] = [
							'url' => $image_url,
							'alt' => get_post_meta( $choice['image'], '_wp_attachment_image_alt', true ),
						];
					}
				}
			}
		}

		return [
			'display_mode' => $this->props['display_mode'],
			'choices' => (object) $choices,
		];
	}

	public function sanitize_in_product_field( $value, $raw_value ) {
		if ( $this->props['display_mode'] === 'cards' ) {
			foreach ( ( (array) $value['choices'] ?? [] ) as $choice_key => $data ) {
				$file_field = $this->get_image_field_for_choice( $choice_key );
				$value['choices'][ $choice_key ]['image'] = $file_field->sanitize( $raw_value['choices'][ $choice_key ]['image'] ?? [] );
			}
		}

		return $value;
	}

	public function validate_in_product_field( $value ): void {
		if ( $this->is_required() || $value['enabled'] ) {
			foreach ( $value['choices'] as $choice_key => $data ) {
				if ( $data['quantity']['enabled'] ) {
					if ( $data['quantity']['min'] === null || $data['quantity']['max'] === null ) {
						throw new \Exception( \Voxel\replace_vars(
							_x( '@addon_name: Minimum and maximum values are required', 'select addon', 'voxel' ), [
								'@addon_name' => $this->get_label(),
							]
						) );
					}

					if ( $data['quantity']['min'] > $data['quantity']['max'] ) {
						throw new \Exception( \Voxel\replace_vars(
							_x( '@addon_name: Minimum value cannot be larger than maximum', 'select addon', 'voxel' ), [
								'@addon_name' => $this->get_label(),
							]
						) );
					}
				}

				if ( $this->props['display_mode'] === 'cards' ) {
					$file_field = $this->get_image_field_for_choice( $choice_key );
					$file_field->validate( $data['image'] );
				}
			}
		}
	}

	public function update_in_product_field( $value ) {
		if ( $this->props['display_mode'] === 'cards' ) {
			foreach ( $value['choices'] as $choice_key => $data ) {
				$file_field = $this->get_image_field_for_choice( $choice_key );
				$value['choices'][ $choice_key ]['image'] = $file_field->prepare_for_storage( $data['image'] );
			}
		}

		return $value;
	}

	public function get_product_form_schema(): ?Data_Object {
		return Schema::Object( [
			'selected' => Schema::Object( [
				'item' => Schema::String(),
				'quantity' => Schema::Int()->default(1),
			] ),
		] );
	}

	public function validate_in_cart_item( $value ): void {
		$config = $this->get_value();
		$selected = $config['choices'][ $value['selected']['item'] ] ?? null;

		if ( $this->is_required() && $selected === null ) {
			throw new \Exception( \Voxel\replace_vars( _x( '@addon_name is required', 'select addon', 'voxel' ), [
				'@addon_name' => $this->get_label(),
			] ) );
		}

		if ( $selected !== null ) {
			if ( $selected['price'] === null ) {
				throw new \Exception( \Voxel\replace_vars( _x( '@addon_name: @choice is not available', 'select addon', 'voxel' ), [
					'@addon_name' => $this->get_label(),
					'@choice' => $value['selected']['item'],
				] ) );
			}

			if ( $selected['quantity']['enabled'] ) {
				if ( $value['selected']['quantity'] < $selected['quantity']['min'] || $value['selected']['quantity'] > $selected['quantity']['max'] ) {
					throw new \Exception( \Voxel\replace_vars(
						_x( '@addon_name: @choice quantity must be between @min_quantity and @max_quantity', 'select addon', 'voxel' ), [
							'@addon_name' => $this->get_label(),
							'@choice' =>  $value['selected']['item'],
							'@min_quantity' => $selected['quantity']['min'],
							'@max_quantity' => $selected['quantity']['max'],
						]
					) );
				}
			} else {
				if ( $value['selected']['quantity'] !== 1 ) {
					throw new \Exception( \Voxel\replace_vars( _x( '@addon_name: @choice quantity cannot be more than 1', 'select addon', 'voxel' ), [
						'@addon_name' => $this->get_label(),
						'@choice' => $value['selected']['item'],
					] ) );
				}
			}
		}
	}

	public function get_pricing_summary( $value ) {
		$config = $this->get_value();
		if ( $value['selected']['item'] === null ) {
			return null;
		}

		$selected = $config['choices'][ $value['selected']['item'] ];

		if ( $this->repeat_config !== null ) {
			$amount = 0;
			$repeat_start = new \DateTime( $this->repeat_config['start'], new \DateTimeZone('UTC') );
			$repeat_end = new \DateTime( $this->repeat_config['end'], new \DateTimeZone('UTC') );

			while ( $this->repeat_config['mode'] === 'nights' ? $repeat_start < $repeat_end : $repeat_start <= $repeat_end ) {
				$custom_price = $this->product_field->get_custom_price_for_date( $repeat_start );
				if ( $custom_price !== null ) {
					$custom_amount = $custom_price['prices']['addons'][ $this->get_key() ][ $value['selected']['item'] ]['price'];
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
				$amount *= $value['selected']['quantity'];
			}
		} elseif ( $this->custom_price !== null ) {
			$custom_amount = $this->custom_price['prices']['addons'][ $this->get_key() ][ $value['selected']['item'] ]['price'];
			if ( $custom_amount !== null ) {
				$amount = $custom_amount;
			} else {
				$amount = $selected['price'];
			}

			if ( $selected['quantity']['enabled'] ) {
				$amount *= $value['selected']['quantity'];
			}
		} else {
			$amount = $selected['price'];

			if ( $selected['quantity']['enabled'] ) {
				$amount *= $value['selected']['quantity'];
			}
		}

		return [
			'type' => 'custom-select',
			'label' => $this->get_label(),
			'selected' => $value['selected']['item'],
			// 'label' => $selected['quantity']['enabled']
			// 	? sprintf( '%s: %s × %d', $this->get_label(), $value['selected']['item'], $value['selected']['quantity'] )
			// 	: sprintf( '%s: %s', $this->get_label(), $value['selected']['item'] ),
			'quantity' => $selected['quantity']['enabled'] ? $value['selected']['quantity'] : null,
			'key' => $this->get_key(),
			'repeat' => $this->repeat_config ? [
				'length' => $this->repeat_config['length'],
				'mode' => $this->repeat_config['mode'],
			] : null,
			'amount' => $amount,
		];
	}

	private function get_image_field_for_choice( $choice_key ) {
		return new \Voxel\Utils\Object_Fields\File_Field( [
			'key' => sprintf( '%s.addons.%s.%s', $this->product_field->get_key(), $this->get_key(), $choice_key ),
			'allowed-types' => [
				'image/jpeg',
				'image/png',
				'image/webp',
			],
			'max-size' => is_numeric( $this->props['image_max_size'] ) ? absint( $this->props['image_max_size'] ) : 2000,
			'max-count' => 1,
		] );
	}

	public function editing_value_in_product_field( $value ) {
		if ( $this->props['display_mode'] === 'cards' ) {
			foreach ( ( $value['choices'] ?? [] ) as $choice_key => $data ) {
				if ( is_numeric( $data['image'] ) && ( $attachment = get_post( $data['image'] ) ) ) {
					$value['choices'][ $choice_key ]['image'] = [ [
						'source' => 'existing',
						'id' => $attachment->ID,
						'name' => wp_basename( get_attached_file( $attachment->ID ) ),
						'type' => $attachment->post_mime_type,
						'preview' => wp_get_attachment_image_url( $attachment->ID, 'medium' ),
					] ];
				} else {
					$value['choices'][ $choice_key ]['image'] = [];
				}
			}
		}

		return $value;
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
			if ( $choice['price'] !== null ) {
				if ( ! $choice['quantity']['enabled'] ) {
					return true;
				} else {
					if (
						$choice['quantity']['min'] !== null
						&& $choice['quantity']['max'] !== null
						&& $choice['quantity']['max'] >= $choice['quantity']['min']
					) {
						return true;
					}
				}
			}
		}

		return false;
	}

	protected $get_active_choices_cache;
	public function get_active_choices(): array {
		if ( $this->get_active_choices_cache === null ) {
			$value = $this->product_field->get_value();
			$choices = $value['addons'][ $this->get_key() ]['choices'] ?? [];

			$active_choices = [];

			foreach ( $choices as $choice_key => $choice ) {
				if ( $choice['price'] === null ) {
					continue;
				}

				$choice['_key'] = $choice_key;
				$active_choices[ $choice_key ] = $choice;
			}

			$this->get_active_choices_cache = $active_choices;
		}

		return $this->get_active_choices_cache;
	}
}
