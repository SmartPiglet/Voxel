<?php

namespace Voxel\Post_Types\Fields\Product_Field;

use \Voxel\Dynamic_Data\Tag as Tag;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Exports {

	public function dynamic_data() {
		return Tag::Object( $this->get_label() )->properties( function() {
			return [
				'min_price' => Tag::Number('Minimum price')->render( function() {
					$reference_date = $GLOBALS['_availability_start_date'] ?? \Voxel\now();
					return $this->get_minimum_price_for_date( $reference_date, [
						'with_discounts' => true,
						'addons' => $GLOBALS['_addon_filters'] ?? null,
					] );
				} ),
				'is_available' => Tag::Bool('Is available?')->render( function() {
					return $this->is_available();
				} ),
				'product_type' => Tag::Object('Product type')->properties( function() {
					$properties = [
						':key' => Tag::String('Key')->render( function() {
							$product_type = $this->get_product_type();
							return $product_type ? $product_type->get_key() : '';
						} ),
						':label' => Tag::String('Label')->render( function() {
							$product_type = $this->get_product_type();
							return $product_type ? $product_type->get_label() : '';
						} ),
					];

					foreach ( $this->get_supported_product_types() as $product_type ) {
						$product_type_props = $this->get_dynamic_data_for_product_type( $product_type );
						if ( ! empty( $product_type_props ) ) {
							$properties[ $product_type->get_key() ] = Tag::Object( $product_type->get_label() )->properties( function() use ( $product_type_props ) {
								return $product_type_props;
							} );
						}
					}

					return $properties;
				} ),
			];
		} );
	}

	protected function get_dynamic_data_for_product_type( \Voxel\Product_Type $product_type ) {
		$properties = [];

		if ( $product_type->config( 'modules.addons.enabled' ) ) {
			$addons = $product_type->repository->get_addons();
			foreach ( $addons as $addon ) {
				if ( in_array( $addon->get_type(), [ 'custom-select', 'custom-multiselect' ], true ) ) {
					$properties[ $addon->get_key() ] = Tag::Object_List( $addon->get_label() )->items( function() use ( $addon ) {
						$product_addons = $this->get_product_field('addons');
						if ( ! $product_addons ) {
							return null;
						}

						$product_addon = $product_addons->get_addon( $addon->get_key() );
						if ( ! ( $product_addon && in_array( $product_addon->get_type(), [ 'custom-select', 'custom-multiselect' ], true ) ) ) {
							return null;
						}

						return $product_addon->get_active_choices();
					} )->properties( function( $index, $choice ) use ( $addon ) {
						$properties = [
							'id' => Tag::String('ID')->render( function() use ( $choice, $addon ) {
								return base64_encode( wp_json_encode( [
									'addon' => $addon->get_key(),
									'choice' => $choice['_key'],
								] ) );
							} ),
							'label' => Tag::String('Label')->render( function() use ( $choice, $addon ) {
								return $choice['_key'];
							} ),
							'price' => Tag::Number('Price')->render( function() use ( $choice, $addon ) {
								return $choice['price'];
							} ),
							'has_quantity' => Tag::Bool('Has quantity?')->render( function() use ( $choice, $addon ) {
								return $choice['quantity']['enabled'] ? '1' : '';
							} ),
							'min' => Tag::Number('Min quantity')->render( function() use ( $choice, $addon ) {
								return $choice['quantity']['min'];
							} ),
							'max' => Tag::Number('Max quantity')->render( function() use ( $choice, $addon ) {
								return $choice['quantity']['max'];
							} ),
						];

						if ( $addon->get_prop('display_mode') === 'cards' ) {
							$properties['subheading'] = Tag::String('Subheading')->render( function() use ( $choice, $addon ) {
								return $choice['subheading'];
							} );

							$properties['image'] = Tag::Number('Image')->render( function() use ( $choice, $addon ) {
								return $choice['image'];
							} );
						}

						return $properties;
					} );
				}
			}
		}

		if ( $product_type->get_product_mode() === 'regular' && $product_type->config( 'modules.stock.enabled' ) ) {
			$properties['stock'] = Tag::Object('Stock')->properties( function() {
				return [
					'enabled' => Tag::Bool('Enabled')->render( function() {
						$product_type = $this->get_product_type();
						if ( ! ( $product_type && $product_type->get_product_mode() === 'regular' && $product_type->config( 'modules.stock.enabled' ) ) ) {
							return '';
						}

						$value = $this->get_value();
						return $value['stock']['enabled'] ? '1' : '';
					} ),
					'quantity' => Tag::Number('Quantity')->render( function() {
						$product_type = $this->get_product_type();
						if ( ! ( $product_type && $product_type->get_product_mode() === 'regular' && $product_type->config( 'modules.stock.enabled' ) ) ) {
							return '';
						}

						$value = $this->get_value();
						return $value['stock']['quantity'];
					} ),
					'sku' => Tag::String('SKU')->render( function() {
						$product_type = $this->get_product_type();
						if ( ! ( $product_type && $product_type->get_product_mode() === 'regular' && $product_type->config( 'modules.stock.enabled' ) ) ) {
							return '';
						}

						$value = $this->get_value();
						return $value['stock']['sku'] ?? '';
					} ),
				];
			} );
		}

		if ( $product_type->config( 'modules.variations.enabled' ) ) {
			$properties['variation_images'] = Tag::String('Variation images')->render( function() {
				if ( ! $this->get_product_field('variations') ) {
					return '';
				}

				$images = [];
				foreach ( (array) ( $this->get_value()['variations']['variations'] ?? [] ) as $variation ) {
					if ( $variation['enabled'] && is_numeric( $variation['image'] ) ) {
						$images[] = $variation['image'];
					}
				}

				return join( ',', $images );
			} );
		}

		return $properties;
	}
}
