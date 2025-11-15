<?php

namespace Voxel\Product_Types\Product_Form\Fields;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Form_Variations_Field extends Base_Field {

	protected $props = [
		'key' => 'form-variations',
		'label' => 'Variations',
	];

	public function get_conditions(): array {
		return [
			'settings.product_mode' => 'variable',
			'modules.variations.enabled' => true,
		];
	}

	protected $attributes_cache;
	public function get_active_attributes() {
		if ( $this->attributes_cache === null ) {
			$attributes = [];
			$value = $this->product_field->get_value();
			$variations = $this->product_field->get_product_field('variations');

			foreach ( (array) $value['variations']['attributes'] as $key => $props ) {
				if ( $props['type'] === 'existing' ) {
					$attribute = clone $this->product_type->repository->get_attribute( $key );
					$attribute->set_product_field( $this->product_field );

					$attributes[ $attribute->get_key() ] = $attribute;
				} elseif ( $props['type'] === 'custom' ) {
					$attribute = new \Voxel\Product_Types\Product_Attributes\Custom_Attribute( [
						'type' => 'custom',
						'key' => $key,
						'label' => $props['label'],
						'choices' => $props['choices'],
						'display_mode' => $props['display_mode'],
					] );

					$attribute->set_product_type( $this->product_type );
					$attribute->set_product_field( $this->product_field );

					$attributes[ $attribute->get_key() ] = $attribute;
				}
			}

			$this->attributes_cache = $attributes;
		}

		return $this->attributes_cache;
	}

	protected $variations_cache;
	public function get_enabled_variations(): array {
		if ( $this->variations_cache === null ) {
			$value = $this->product_field->get_value();
			$variations = [];
			foreach ( (array) $value['variations']['variations'] as $id => $variation ) {
				if ( $variation['enabled'] !== true ) {
					continue;
				}

				$variations[ $id ] = [
					'id' => $id,
					'attributes' => (array) $variation['attributes'],
					'config' => [
						'base_price' => $variation['config']['base_price'],
					],
					'image' => null,
					'_status' => 'active',
				];

				if ( $image_url = wp_get_attachment_image_url( $variation['image'], 'medium' ) ) {
					$variations[ $id ]['image'] = [
						'id' => $variation['image'],
						'url' => $image_url,
						'alt' => get_post_meta( $variation['image'], '_wp_attachment_image_alt', true ),
					];
				}

				if ( $this->product_type->config('modules.stock.enabled') ) {
					$variations[ $id ]['config']['stock'] = $variation['config']['stock'];

					if ( $variation['config']['stock']['enabled'] && $variation['config']['stock']['quantity'] < 1 ) {
						$variations[ $id ]['_status'] = 'out_of_stock';
					}
				}
			}

			$this->variations_cache = $variations;
		}

		return $this->variations_cache;
	}

	public function set_schema( Data_Object $schema ): void {
		$schema->set_prop( 'variations', Schema::Object( [
			'variation_id' => Schema::String(),
			'quantity' => Schema::Int()->min(1)->default(1),
		] ) );
	}

	public function validate( $value ) {
		$variations = $this->get_enabled_variations();
		$variation_id = $value['variations']['variation_id'];
		$quantity = $value['variations']['quantity'];
		if ( ! isset( $variations[ $variation_id ] ) ) {
			throw new \Exception( __( 'This product is not available', 'voxel' ), 101 );
		}

		$variation = $variations[ $variation_id ];
		if ( $variation['config']['base_price']['amount'] === null && $variation['config']['base_price']['discount_amount'] === null ) {
			throw new \Exception( __( 'This product is not available', 'voxel' ), 102 );
		}

		if ( $this->product_type->config('modules.stock.enabled') ) {
			$stock = $variations[ $variation_id ]['config']['stock'];

			if ( $stock['enabled'] ) {
				if ( $stock['sold_individually'] && $quantity > 1 ) {
					throw new \Exception( __( 'This product cannot have a quantity larger than 1', 'voxel' ), 103 );
				}

				if ( $quantity > $stock['quantity'] ) {
					throw new \Exception( \Voxel\replace_vars( __( 'This product cannot have a quantity larger than @max_quantity', 'voxel' ), [
						'@max_quantity' => $stock['quantity'],
					] ), 104 );
				}
			}
		}
	}

	public function frontend_props(): array {
		return [
			'selections' => array_map( function( $attribute ) {
				return null;
			}, $this->get_active_attributes() ),
			'attributes' => array_map( function( $attribute ) {
				return $attribute->get_product_form_frontend_config();
			}, $this->get_active_attributes() ),
			'variations' => $this->get_enabled_variations(),
			'stock' => [
				'enabled' => $this->product_type->config('modules.stock.enabled'),
			],
		];
	}

	public function get_field_templates() {
		$templates = [];
		$templates[] = locate_template( 'templates/widgets/product-form/form-variations.php' );

		return $templates;
	}
}
