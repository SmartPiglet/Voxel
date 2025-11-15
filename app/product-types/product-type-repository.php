<?php

namespace Voxel\Product_Types;

use Voxel\Utils\Config_Schema\{Schema, Data_Object, Base_Data_Type};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Product_Type_Repository {

	protected $product_type;

	public function __construct( \Voxel\Product_Type $product_type ) {
		$this->product_type = $product_type;
	}

	public function get_editor_config(): array {
		$schema = $this->get_settings_schema();
		$schema->set_value( $this->product_type->config );

		return $schema->export();
	}

	/**
	 * Save product type configuration to database.
	 *
	 * @since 1.0
	 */
	public function set_config( $new_config ) {
		$schema = $this->get_settings_schema();
		$schema->set_value( $this->product_type->config );

		foreach ( $new_config as $group_key => $group_values ) {
			if ( $prop = $schema->get_prop( $group_key ) ) {
				$prop->set_value( $group_values );
			}
		}

		$product_types = \Voxel\get( 'product_types', [] );
		$product_types[ $this->product_type->get_key() ] = Schema::optimize_for_storage( $schema->export() );

        // cleanup product_types array
        foreach ( $product_types as $key => $config ) {
        	if ( ! is_string( $key ) || empty( $key ) || empty( $config ) ) {
        		unset( $product_types[ $key ] );
        	}
        }

		\Voxel\set( 'product_types', $product_types );
	}

	public function remove() {
		$product_types = \Voxel\get( 'product_types', [] );
		unset( $product_types[ $this->product_type->get_key() ] );
		\Voxel\set( 'product_types', $product_types );
	}

	public function get_settings_schema(): Data_Object {
		return Schema::Object( [
			'modules' => Schema::Object( [
				'base_price' => Schema::Object( [
					'enabled' => Schema::Bool()->default(true),
					'discount_price' => Schema::Object( [
						'enabled' => Schema::Bool()->default(false),
					] ),
				] ),

				'booking' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
					'type' => Schema::Enum( [ 'days', 'timeslots' ] )->default('days'),
					'date_ranges' => Schema::Object( [
						'count_mode' => Schema::Enum( [ 'days', 'nights' ] )->default('days'),
					] ),
					'quantity_per_slot' => Schema::Object( [
						'enabled' => Schema::Bool()->default(false),
					] ),
					'actions' => Schema::Object( [
						'add_to_gcal' => Schema::Object( [
							'customer' => Schema::Object( [
								'enabled' => Schema::Bool()->default(true),
							] ),
							'vendor' => Schema::Object( [
								'enabled' => Schema::Bool()->default(true),
							] ),
						] ),
						'add_to_ical' => Schema::Object( [
							'customer' => Schema::Object( [
								'enabled' => Schema::Bool()->default(true),
							] ),
							'vendor' => Schema::Object( [
								'enabled' => Schema::Bool()->default(true),
							] ),
						] ),
						'cancel' => Schema::Object( [
							'customer' => Schema::Object( [
								'enabled' => Schema::Bool()->default(true),
							] ),
							'vendor' => Schema::Object( [
								'enabled' => Schema::Bool()->default(true),
							] ),
						] ),
						'reschedule' => Schema::Object( [
							'customer' => Schema::Object( [
								'enabled' => Schema::Bool()->default(false),
							] ),
							'vendor' => Schema::Object( [
								'enabled' => Schema::Bool()->default(true),
							] ),
						] ),
					] ),
				] ),

				'addons' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
					'items' => Schema::List()
						->validator( function( $item ) {
							$addons = \Voxel\config('product_types.product_addons');
							return isset( $item['key'] ) && isset( $addons[ $item['type'] ?? '' ] );
						} )
						->transformer( function( $item ) {
							$addons = \Voxel\config('product_types.product_addons');
							$addon = new $addons[ $item['type'] ]( $item );
							return $addon->get_props();
						} )
						->default([]),
				] ),

				'variations' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
					'attributes' => Schema::List()
						->transformer( function( $item ) {
							$attribute = new \Voxel\Product_Types\Product_Attributes\Predefined_Attribute( $item );
							return $attribute->get_props();
						} )
						->default([]),
					'predefined_attributes' => Schema::Object( [
						'enabled' => Schema::Bool()->default(true),
					] ),
					'vendor_attributes' => Schema::Object( [
						'enabled' => Schema::Bool()->default(true),
					] ),
				] ),

				'stock' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
					'sku' => Schema::Object( [
						'enabled' => Schema::Bool()->default(true),
					] ),
				] ),

				'custom_prices' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
				] ),

				'custom_currency' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
				] ),

				'deliverables' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
					'delivery_methods' => Schema::Object( [
						'automatic' => Schema::Bool()->default(true),
						'manual' => Schema::Bool()->default(true),
					] ),
					// 'download_limit' => Schema::Int()->default(3),
					'uploads' => Schema::Object( [
						'allowed_file_types' => Schema::List()
							->validator('is_string')
							->default( [ 'image/jpeg', 'image/png', 'image/webp' ] ),
						'max_count' => Schema::Int()->default(5),
						'max_size' => Schema::Int()->default(2000),
					] ),
				] ),

				'shipping' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
					'required' => Schema::Bool()->default(false),
					'default_shipping_class' => Schema::String()->default(''),
				] ),

				'cart' => Schema::Object( [
					'enabled' => Schema::Bool()->default(true),
				] ),

				'data_inputs' => Schema::Object( [
					'enabled' => Schema::Bool()->default(false),
					'items' => Schema::List()
						->validator( function( $item ) {
							$data_inputs = \Voxel\config('product_types.data_inputs');
							return is_string( $item['key'] ?? null ) && isset( $data_inputs[ $item['type'] ?? '' ] );
						} )
						->transformer( function( $item ) {
							$data_inputs = \Voxel\config('product_types.data_inputs');
							$data_input = new $data_inputs[ $item['type'] ]( $item );
							return $data_input->get_props();
						} )
						->default([]),
				] ),
			] ),

			'settings' => Schema::Object( [
				'product_mode' => Schema::Enum( [
					'regular',
					'variable',
					'booking',
				] )->default('regular'),
				'key' => Schema::String(),
				'label' => Schema::String(),
				'payments' => Schema::Object( [
					'mode' => Schema::Enum( [ 'payment', 'subscription', 'offline' ] )->default('payment'),
					'mode_payment' => Schema::Object( [
						'skip_zero_amount_checkout' => Schema::Bool()->default(false),
					] ),
					'mode_offline' => Schema::Object( [
						'order_approval' => Schema::Enum( [ 'automatic', 'manual' ] ),
					] ),
				] ),
				'product_fields' => Schema::Keyed_List()
					->validator( function( $item, $key ) {
						$fields = \Voxel\config('product_types.product_fields');
						return isset( $fields[ $item['key'] ?? '' ] ) && $item['key'] === $key;
					} )
					->transformer( function( $item, $key ) {
						$fields = \Voxel\config('product_types.product_fields');
						$field = new $fields[ $item['key'] ]( $item );
						return [ $field->get_props(), $field->get_key() ];
					} )
					->default([]),

				// whether products of this type can be assigned and sold by marketplace vendors
				'supports_marketplace' => Schema::Bool()->default(true),
			] ),
		] );
	}

	protected $product_fields_cache;
	public function get_product_fields(): array {
		if ( $this->product_fields_cache === null ) {
			$config = $this->config('settings.product_fields');
			$this->product_fields_cache = [];

			foreach ( \Voxel\config('product_types.product_fields') as $field_key => $field_class ) {
				$field = new $field_class( $config[ $field_key ] ?? [] );
				$field->set_product_type( $this->product_type );

				if ( ! $field->passes_conditions() ) {
					continue;
				}

				$this->product_fields_cache[ $field->get_key() ] = $field;
			}

			// preserve order of fields
			$order = array_flip( array_keys( $config ) );
			uasort( $this->product_fields_cache, function( $a, $b ) use ( $order ) {
				return ( $order[ $a->get_key() ] ?? 1000 ) <=> ( $order[ $b->get_key() ] ?? 1000 );
			} );
		}

		return $this->product_fields_cache;
	}

	protected $addons_cache;
	public function get_addons(): array {
		if ( $this->addons_cache === null ) {
			$items = $this->product_type->config('modules.addons.items');
			$classes = \Voxel\config('product_types.product_addons');
			$this->addons_cache = [];

			foreach ( $items as $props ) {
				$addon = new $classes[ $props['type'] ]( $props );
				$addon->set_product_type( $this->product_type );

				$this->addons_cache[ $addon->get_key() ] = $addon;
			}
		}

		return $this->addons_cache;
	}

	protected $variation_fields_cache;
	public function get_variation_fields(): array {
		if ( $this->variation_fields_cache === null ) {
			$fields = [
				'base-price' => \Voxel\Product_Types\Variations\Variation_Base_Price_Field::class,
			];

			if ( $this->config('modules.stock.enabled') ) {
				$fields['stock'] = \Voxel\Product_Types\Variations\Variation_Stock_Field::class;
			}

			$this->variation_fields_cache = [];
			foreach ( $fields as $field_key => $field_class ) {
				$field = new $field_class;
				$field->set_product_type( $this->product_type );

				// if ( ! $field->passes_conditions() ) {
				// 	continue;
				// }

				$this->variation_fields_cache[ $field->get_key() ] = $field;
			}
		}

		return $this->variation_fields_cache;
	}

	protected $form_fields_cache;
	public function get_form_fields(): array {
		if ( $this->form_fields_cache === null ) {
			$fields = [
				'form-quantity' => \Voxel\Product_Types\Product_Form\Fields\Form_Quantity_Field::class,
				'form-booking' => \Voxel\Product_Types\Product_Form\Fields\Form_Booking_Field::class,
				'form-variations' => \Voxel\Product_Types\Product_Form\Fields\Form_Variations_Field::class,
				'form-addons' => \Voxel\Product_Types\Product_Form\Fields\Form_Addons_Field::class,
				'form-data-inputs' => \Voxel\Product_Types\Product_Form\Fields\Form_Data_Inputs_Field::class,
			];

			$this->form_fields_cache = [];

			foreach ( $fields as $field_key => $field_class ) {
				$field = new $field_class( $config[ $field_key ] ?? [] );
				$field->set_product_type( $this->product_type );

				if ( ! $field->passes_conditions() ) {
					continue;
				}

				$this->form_fields_cache[ $field->get_key() ] = $field;
			}
		}

		return $this->form_fields_cache;
	}


	protected $config_schema_cache;
	public function config( $option, $default = null ) {
		if ( $this->config_schema_cache === null ) {
			$this->config_schema_cache = $this->get_settings_schema();
		}

		$path = explode( '.', $option );

		$schema_item = $this->config_schema_cache;
		foreach ( $path as $item_key ) {
			if ( ! $schema_item instanceof \Voxel\Utils\Config_Schema\Data_Object ) {
				return $default;
			}

			$schema_item = $schema_item->get_prop( $item_key );
		}

		if ( $schema_item === null ) {
			return $default;
		}

		if ( $schema_item->get_meta('exported') === true ) {
			return $schema_item->get_meta('exported_value') ?? $default;
		}

		$config = $this->product_type->config;
		foreach ( $path as $item_key ) {
			if ( ! isset( $config[ $item_key ] ) ) {
				$config = $default;
				break;
			}

			$config = $config[ $item_key ];
		}

		$schema_item->set_value( $config );
		$value = $schema_item->export();
		$schema_item->set_meta('exported', true);
		$schema_item->set_meta('exported_value', $value);

		return $value;
	}

	protected $attributes_cache;
	public function get_attributes(): array {
		if ( ! $this->config('modules.variations.predefined_attributes.enabled') ) {
			return [];
		}

		if ( $this->attributes_cache === null ) {
			$this->attributes_cache = [];
			$attributes = $this->product_type->config('modules.variations.attributes');
			foreach ( $attributes as $props ) {
				$attribute = new \Voxel\Product_Types\Product_Attributes\Predefined_Attribute( $props );
				$attribute->set_product_type( $this->product_type );

				$this->attributes_cache[ $attribute->get_key() ] = $attribute;
			}
		}

		return $this->attributes_cache;
	}

	public function get_attribute( $key ) {
		return $this->get_attributes()[ $key ] ?? null;
	}

	public function has_attribute( $key ): bool {
		$attributes = $this->get_attributes();
		return isset( $attributes[ $key ] );
	}

	protected $data_inputs_cache;
	public function get_data_inputs(): array {
		if ( $this->data_inputs_cache === null ) {
			$items = $this->product_type->config('modules.data_inputs.items');
			$classes = \Voxel\config('product_types.data_inputs');
			$this->data_inputs_cache = [];

			foreach ( $items as $props ) {
				$data_input = new $classes[ $props['type'] ]( $props );
				$data_input->set_product_type( $this->product_type );

				$this->data_inputs_cache[ $data_input->get_key() ] = $data_input;
			}
		}

		return $this->data_inputs_cache;
	}

	public function evaluate_conditions( array $conditions ): bool {
		foreach ( $conditions as $setting_path => $value ) {
			if ( is_array( $value ) ) {
				$comparison = $value['compare'];
				$value = $value['value'];

				if ( $comparison === 'equals' ) {
					if ( $this->product_type->config( $setting_path ) !== $value ) {
						return false;
					}
				} elseif ( $comparison === 'not_equals' ) {
					if ( $this->product_type->config( $setting_path ) === $value ) {
						return false;
					}
				} elseif ( $comparison === 'in_array' ) {
					if ( ! in_array( $this->product_type->config( $setting_path ), $value, true ) ) {
						return false;
					}
				} else {
					return false;
				}
			} else {
				if ( $this->product_type->config( $setting_path ) !== $value ) {
					return false;
				}
			}
		}

		return true;
	}
}
