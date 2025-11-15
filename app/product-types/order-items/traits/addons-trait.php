<?php

namespace Voxel\Product_Types\Order_Items\Traits;

use \Voxel\Utils\Config_Schema\Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

trait Addons_Trait {

	protected $_get_addons_cache;
	public function get_addons(): array {
		if ( $this->_get_addons_cache === null ) {
			$config = $this->get_summary_item('addons');
			$schema = Schema::Object( [
				'key' => Schema::String()->default('addons'),
				'amount' => Schema::Float()->min(0),
				'summary' => Schema::List()
					->validator( function( $item ) {
						$allowed_types = [ 'numeric', 'switcher', 'select', 'multiselect', 'custom-select', 'custom-multiselect' ];
						return is_array( $item ) && in_array( $item['type'] ?? null, $allowed_types, true );
					} )
					->transformer( function( $item ) {
						$schema = Schema::Object( [
							'type' => Schema::String()->default('numeric'),
							'label' => Schema::String()->default(''),
							'key' => Schema::String()->default(''),
							'amount' => Schema::Float()->min(0),
							'repeat' => Schema::Object( [
								'length' => Schema::Int(),
								'mode' => Schema::String(),
							] ),
						] );

						if ( $item['type'] === 'numeric' ) {
							$schema->set_prop( 'quantity', Schema::Int()->min(1)->default(1) );
						} elseif ( $item['type'] === 'select' ) {
							$schema->set_prop( 'selected', Schema::String()->default('') );
						} elseif ( $item['type'] === 'multiselect' ) {
							$schema->set_prop( 'selected', Schema::List()->default([]) );
							$schema->set_prop( 'summary', Schema::Object_List( [
								'key' => Schema::String()->default(''),
								'amount' => Schema::Float()->min(0),
							] )->default([]) );
						} elseif ( $item['type'] === 'custom-select' ) {
							$schema->set_prop( 'selected', Schema::String()->default('') );
							$schema->set_prop( 'quantity', Schema::Int() );
						} elseif ( $item['type'] === 'custom-multiselect' ) {
							$schema->set_prop( 'summary', Schema::Object_List( [
								'label' => Schema::String()->default(''),
								'key' => Schema::String()->default(''),
								'amount' => Schema::Float()->min(0),
								'quantity' => Schema::Int(),
							] )->default([]) );
						}

						$schema->set_value( $item );
						return $schema->export();
					} )
					->default([]),
			] );

			$schema->set_value( $config );

			$this->_get_addons_cache = $schema->export();
		}

		return $this->_get_addons_cache;
	}

	public function get_addon_summary() {
		$summary = [];
		foreach ( $this->get_addons()['summary'] as $item ) {
			if ( $item['type'] === 'switcher' ) {
				$summary[] = $item['label'];
			} elseif ( $item['type'] === 'numeric' ) {
				$summary[] = sprintf( '%s × %d', $item['label'], $item['quantity'] );
			} elseif ( $item['type'] === 'select' ) {
				$summary[] = sprintf( '%s: %s', $item['label'], $item['selected'] );
			} elseif ( $item['type'] === 'multiselect' ) {
				$summary[] = sprintf( '%s: %s', $item['label'], join( ', ', $item['selected'] ) );
			} elseif ( $item['type'] === 'custom-select' ) {
				$summary[] = $item['quantity'] !== null
					? sprintf( '%s: %s × %d', $item['label'], $item['selected'], $item['quantity'] )
					: sprintf( '%s: %s', $item['label'], $item['selected'] );
			} elseif ( $item['type'] === 'custom-multiselect' ) {
				$summary[] = sprintf( '%s: %s', $item['label'], join( ', ', array_map( function( $choice ) {
					return $choice['quantity'] !== null
						? sprintf( '%s × %d', $choice['label'], $choice['quantity'] )
						: $choice['label'];
				}, $item['summary'] ) ) );
			}
		}

		return join( ', ', $summary );
	}

}

