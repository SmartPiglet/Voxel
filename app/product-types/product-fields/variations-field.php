<?php

namespace Voxel\Product_Types\Product_Fields;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Variations_Field extends Base_Product_Field {

	protected $props = [
		'key' => 'variations',
		'label' => 'Variations',
		'description' => '',
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
			'settings.product_mode' => 'variable',
			'modules.variations.enabled' => true,
		];
	}

	public function allows_custom_attributes(): bool {
		return !! $this->product_type->config('modules.variations.vendor_attributes.enabled');
	}

	public function get_variation_schema(): Data_Object {
		$variation_schema = Schema::Object( [] );
		foreach ( $this->get_variation_fields() as $field ) {
			$field->set_schema( $variation_schema );
		}

		return $variation_schema;
	}

	public function set_schema( Data_Object $schema ): void {
		$variations_schema = Schema::Object( [
			'variations' => Schema::Keyed_Object_List( [
				'attributes' => Schema::Keyed_List(),
				'config' => Schema::Object( $this->get_variation_schema()->get_props() ),
				'image' => Schema::Int(),
				'enabled' => Schema::Bool()->default(true),
			] )->validator( function( $item, $key ) {
				return is_array( $item ) && is_string( $key ) && ! empty( $key );
			} ),
		] );

		$variations_schema->set_prop( 'attributes', Schema::Keyed_List()
			->validator( function( $item, $key ) {
				if ( ! ( is_string( $key ) && ! empty( $key ) && is_array( $item ) ) ) {
					return false;
				}

				if ( ! in_array( ( $item['type'] ?? null ), [ 'existing', 'custom' ], true ) ) {
					return false;
				}

				if ( $item['type'] === 'existing' && ! $this->product_type->repository->has_attribute( $key ) ) {
					return false;
				}

				if ( $item['type'] === 'custom' && ! $this->allows_custom_attributes() ) {
					return false;
				}

				return true;
			} )
			->transformer( function( $item, $key ) {
				if ( $item['type'] === 'existing' ) {
					$schema = Schema::Object( [
						'type' => Schema::String(),
						'choices' => Schema::Keyed_Object_List( [
							'enabled' => Schema::Bool()->default(true),
						] )
							->validator( function( $choice, $choice_key ) use ( $key ) {
								$choices = $this->product_type->repository->get_attribute( $key )->get_choices();
								return isset( $choices[ $choice_key ] );
							} )
							->default([]),
					] );

					$schema->set_value( $item );
					$schema->get_prop('type')->set_value( 'existing' );

					return [ $schema->export(), $key ];
				} else {
					$schema = Schema::Object( [
						'type' => Schema::String(),
						'label' => Schema::String(),
						'display_mode' => Schema::Enum( [ 'buttons', 'dropdown', 'radio', 'colors', 'cards', 'images' ] ),
						'choices' => Schema::Keyed_Object_List( [
							'label' => Schema::String(),
							'color' => Schema::String(),
							'subheading' => Schema::String(),
							'image' => Schema::Int(),
						] )->default([]),
					] );

					$schema->set_value( $item );
					$schema->get_prop('type')->set_value( 'custom' );

					return [ $schema->export(), $key ];
				}
			} )
		);

		$schema->set_prop( 'variations', $variations_schema );
	}

	public function sanitize( $value, $raw_value ) {
		foreach ( (array) ( $raw_value['variations']['attributes'] ?? [] ) as $attribute_id => $attribute ) {
			if ( ( $attribute['type'] ?? null ) === 'custom' && in_array( $attribute['display_mode'] ?? null, [ 'cards', 'images' ], true ) ) {
				foreach ( (array) ( $attribute['choices'] ?? [] ) as $choice_id => $choice ) {
					$file_field = $this->get_image_field_for_choice( $choice_id );
					$value['variations']['attributes'][ $attribute_id ]['choices'][ $choice_id ]['image'] = $file_field->sanitize( $choice['image'] ?? [] );
				}
			}
		}

		foreach ( (array) ( $raw_value['variations']['variations'] ?? [] ) as $variation_id => $variation ) {
			$file_field = $this->get_image_field_for_variation( $variation_id );
			$value['variations']['variations'][ $variation_id ]['image'] = $file_field->sanitize( $variation['image'] ?? [] );
		}

		return $value;
	}

	public function validate( $value ): void {
		if ( count( (array) $value['variations']['attributes'] ) > $this->max_attributes() ) {
			throw new \Exception( \Voxel\replace_vars(
				_x( '@field_name: You cannot add more than @max attributes', 'field validation', 'voxel' ), [
					'@field_name' => $this->product_field->get_label(),
					'@max' => $this->max_attributes(),
				]
			) );
		}

		foreach ( (array) $value['variations']['attributes'] as $attribute_id => $attribute ) {
			foreach ( (array) $attribute['choices'] as $choice_id => $choice ) {
				if ( $attribute['type'] === 'custom' ) {
					if ( empty( $choice['label'] ?? '' ) ) {
						throw new \Exception( \Voxel\replace_vars(
							_x( 'Product attributes: Label is required for @attribute_name choices', 'field validation', 'voxel' ), [
								'@attribute_name' => $attribute['label'] ?? '',
							]
						) );
					}

					if ( in_array( $attribute['display_mode'] ?? null, [ 'cards', 'images' ], true ) ) {
						$file_field = $this->get_image_field_for_choice( $choice_id );
						$file_field->validate( $choice['image'] );

						if ( $attribute['display_mode'] === 'images' ) {
							if ( empty( $choice['image'] ?? null ) ) {
								throw new \Exception( \Voxel\replace_vars(
									_x( 'Product attributes: Image is required for @attribute_name: @choice_name', 'field validation', 'voxel' ), [
										'@attribute_name' => $attribute['label'] ?? '',
										'@choice_name' => $choice['label'] ?? '',
									]
								) );
							}
						}
					}

					if ( $attribute['display_mode'] === 'colors' ) {
						if ( empty( $choice['color'] ?? null ) ) {
							throw new \Exception( \Voxel\replace_vars(
								_x( 'Product attributes: Color is required for @attribute_name: @choice_name', 'field validation', 'voxel' ), [
									'@attribute_name' => $attribute['label'] ?? '',
									'@choice_name' => $choice['label'] ?? '',
								]
							) );
						}
					}
				}
			}
		}

		if ( count( (array) $value['variations']['variations'] ) > $this->max_variations() ) {
			throw new \Exception( \Voxel\replace_vars(
				_x( '@field_name: You cannot add more than @max variations', 'field validation', 'voxel' ), [
					'@field_name' => $this->product_field->get_label(),
					'@max' => $this->max_variations(),
				]
			) );
		}

		foreach ( (array) $value['variations']['variations'] as $variation_id => $variation ) {
			$file_field = $this->get_image_field_for_variation( $variation_id );
			$value['variations']['variations'][ $variation_id ]['image'] = $file_field->validate( $variation['image'] );

			foreach ( $this->get_variation_fields() as $field ) {
				$field->validate( $variation );
			}
		}
	}

	public function update( $value ) {
		foreach ( (array) $value['variations']['attributes'] as $attribute_id => $attribute ) {
			if ( $attribute['type'] === 'custom' && in_array( $attribute['display_mode'] ?? null, [ 'cards', 'images' ], true ) ) {
				foreach ( (array) $attribute['choices'] as $choice_id => $choice ) {
					$file_field = $this->get_image_field_for_choice( $choice_id );
					$value['variations']['attributes'][ $attribute_id ]['choices'][ $choice_id ]['image'] = $file_field->prepare_for_storage( $choice['image'] );
				}
			}
		}

		foreach ( (array) $value['variations']['variations'] as $variation_id => $variation ) {
			$file_field = $this->get_image_field_for_variation( $variation_id );
			$value['variations']['variations'][ $variation_id ]['image'] = $file_field->prepare_for_storage( $variation['image'] );
		}

		return $value;
	}

	public function editing_value( $value ) {
		foreach ( (array) $value['variations']['attributes'] as $attribute_id => $attribute ) {
			if ( $attribute['type'] === 'custom' && in_array( $attribute['display_mode'] ?? null, [ 'cards', 'images' ], true ) ) {
				foreach ( (array) $attribute['choices'] as $choice_id => $choice ) {
					if ( is_numeric( $choice['image'] ) && ( $attachment = get_post( $choice['image'] ) ) ) {
						$value['variations']['attributes'][ $attribute_id ]['choices'][ $choice_id ]['image'] = [ [
							'source' => 'existing',
							'id' => $attachment->ID,
							'name' => wp_basename( get_attached_file( $attachment->ID ) ),
							'type' => $attachment->post_mime_type,
							'preview' => wp_get_attachment_image_url( $attachment->ID, 'medium' ),
						] ];
					} else {
						$value['variations']['attributes'][ $attribute_id ]['choices'][ $choice_id ]['image'] = [];
					}
				}
			}
		}

		foreach ( (array) $value['variations']['variations'] as $variation_id => $variation ) {
			if ( is_numeric( $variation['image'] ) && ( $attachment = get_post( $variation['image'] ) ) ) {
				$value['variations']['variations'][ $variation_id ]['image'] = [ [
					'source' => 'existing',
					'id' => $attachment->ID,
					'name' => wp_basename( get_attached_file( $attachment->ID ) ),
					'type' => $attachment->post_mime_type,
					'preview' => wp_get_attachment_image_url( $attachment->ID, 'medium' ),
				] ];
			} else {
				$value['variations']['variations'][ $variation_id ]['image'] = [];
			}
		}

		return $value;
	}

	private function get_image_field_for_choice( $choice_id ) {
		return new \Voxel\Utils\Object_Fields\File_Field( [
			'key' => sprintf( '%s.custom-attributes.%s', $this->product_field->get_key(), $choice_id ),
			'allowed-types' => [
				'image/jpeg',
				'image/png',
				'image/webp',
			],
			'max-size' => 2000,
			'max-count' => 1,
		] );
	}

	private function get_image_field_for_variation( $variation_id ) {
		return new \Voxel\Utils\Object_Fields\File_Field( [
			'key' => sprintf( '%s.variations.%s', $this->product_field->get_key(), $variation_id ),
			'allowed-types' => [
				'image/jpeg',
				'image/png',
				'image/webp',
			],
			'max-size' => 2000,
			'max-count' => 1,
		] );
	}

	public function frontend_props(): array {
		$props = [
			'attributes' => $this->product_type->config('modules.variations.predefined_attributes.enabled')
				? $this->product_type->config('modules.variations.attributes')
				: [],
			'custom_attributes' => [
				'enabled' => $this->allows_custom_attributes(),
			],
			'variation_props' => [
				'id' => null,
				'attributes' => new \stdClass,
				'config' => $this->get_variation_schema()->export(),
				'enabled' => true,
			],
			'stock' => [
				'enabled' => $this->product_type->config('modules.stock.enabled'),
			],
			'max_variations' => $this->max_variations(),
			'l10n' => [
				'new_attribute_label' => _x( 'Custom attribute', 'variations', 'voxel' ),
				'one_variation_updated' => _x( 'One variation updated', 'variations', 'voxel' ),
				'multiple_variations_updated' => _x( '@count variations updated', 'variations', 'voxel' ),
			],
		];

		foreach ( $this->get_variation_fields() as $field ) {
			$props['fields'][ $field->get_key() ] = $field->get_frontend_config();
		}

		return $props;
	}

	public function get_field_templates() {
		$templates = [];
		$templates[] = locate_template( 'templates/widgets/create-post/product-field/variations/variation-base-price.php' );
		$templates[] = locate_template( 'templates/widgets/create-post/product-field/variations/attributes.php' );
		$templates[] = locate_template( 'templates/widgets/create-post/product-field/variations/attribute.php' );
		$templates[] = locate_template( 'templates/widgets/create-post/product-field/variations/variation-stock.php' );
		$templates[] = locate_template( 'templates/widgets/create-post/product-field/variations/variation-bulk-settings.php' );

		return $templates;
	}

	protected $variation_fields;
	public function get_variation_fields() {
		if ( $this->variation_fields === null ) {
			$product_type = $this->get_product_type();
			foreach ( $product_type->repository->get_variation_fields() as $field ) {
				$field = clone $field;
				$field->set_product_field( $this->get_product_field() );
				$this->variation_fields[ $field->get_key() ] = $field;
			}
		}

		return $this->variation_fields;
	}

	public function get_variation_field( $field_key ) {
		$fields = $this->get_variation_fields();
		return $fields[ $field_key ] ?? null;
	}

	public function check_product_form_validity( $value ) {
		$variations = $this->product_field->get_form_field('form-variations')->get_enabled_variations();
		if ( empty( $variations ) ) {
			throw new \Exception;
		}

		foreach ( $variations as $variation ) {
			if ( $variation['_status'] === 'active' ) {
				return;
			}
		}

		throw new \Exception( _x( 'Product out of stock', 'products', 'voxel' ), \Voxel\PRODUCT_ERR_OUT_OF_STOCK );
	}

	public function max_variations(): int {
		return 100;
	}

	public function max_attributes(): int {
		return 10;
	}
}
