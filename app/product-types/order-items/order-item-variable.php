<?php

namespace Voxel\Product_Types\Order_Items;

use \Voxel\Utils\Config_Schema\Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Order_Item_Variable extends Order_Item {

	public function get_type(): string {
		return 'variable';
	}

	public function get_product_description() {
		return join( ', ', array_map( function( $attribute ) {
			return sprintf(
				'%s: %s',
				$attribute['attribute']['label'],
				$attribute['value']['label']
			);
		}, $this->get_variation()['attributes'] ) );
	}

	protected $_get_variation_cache;
	public function get_variation(): array {
		if ( $this->_get_variation_cache === null ) {
			$config = $this->get_details( 'variation' );
			$schema = Schema::Object( [
				'attributes' => Schema::Keyed_Object_List( [
					'attribute' => Schema::Object( [
						'key' => Schema::String()->default(''),
						'label' => Schema::String()->default(''),
					] ),
					'value' => Schema::Object( [
						'key' => Schema::String()->default(''),
						'label' => Schema::String()->default(''),
					] ),
				] )->default([]),
			] );

			$schema->set_value( $config );

			$this->_get_variation_cache = $schema->export();
		}

		return $this->_get_variation_cache;
	}

	public function reduce_stock() {
		$schema = Schema::Object( [
			'handled' => Schema::Bool()->default( false ),
			'reduced' => Schema::Bool()->default( false ),
			'quantity' => Schema::Int()->min(1),
		] );

		$schema->set_value( $this->get_details( 'stock' ) );
		$config = $schema->export();

		// stock already handled for this order item
		if ( $config['handled'] ) {
			return;
		}

		$reserved_quantity = $this->get_quantity();
		if ( $reserved_quantity === null ) {
			$this->set_details( 'stock.handled', true );
			$this->save();
			return;
		}

		$field = $this->get_product_field();
		if ( ! $field ) {
			$this->set_details( 'stock.handled', true );
			$this->save();
			return;
		}

		$variation_id = $this->get_details( 'variation.variation_id' );
		if ( empty( $variation_id ) ) {
			$this->set_details( 'stock.handled', true );
			$this->save();
			return;
		}

		$value = $field->get_value();
		if ( ! isset( $value['variations']['variations'][ $variation_id ] ) ) {
			$this->set_details( 'stock.handled', true );
			$this->save();
			return;
		}

		if ( ! ( $value['variations']['variations'][ $variation_id ]['config']['stock']['enabled'] ?? false ) ) {
			$this->set_details( 'stock.handled', true );
			$this->save();
			return;
		}

		$value['variations']['variations'][ $variation_id ]['config']['stock']['quantity'] = max(
			0,
			$value['variations']['variations'][ $variation_id ]['config']['stock']['quantity'] - $reserved_quantity
		);

		$field->_direct_update( $value );

		$post = \Voxel\Post::force_get( $this->post_id ); // refresh post cache
		$post->should_index() ? $post->index() : $post->unindex();

		$this->set_details( 'stock', [
			'handled' => true,
			'reduced' => true,
			'quantity' => $reserved_quantity,
		] );

		$this->save();
	}
}
