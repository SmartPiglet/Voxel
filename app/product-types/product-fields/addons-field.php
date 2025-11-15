<?php

namespace Voxel\Product_Types\Product_Fields;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Addons_Field extends Base_Product_Field {

	protected $props = [
		'key' => 'addons',
		'label' => 'Addons',
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
			'settings.product_mode' => [
				'compare' => 'in_array',
				'value' => [ 'regular', 'booking' ],
			],
			'modules.addons.enabled' => true,
		];
	}

	public function set_schema( Data_Object $schema ): void {
		$addons_schema = Schema::Object( [] );
		foreach ( $this->get_addons() as $addon ) {
			$addons_schema->set_prop( $addon->get_key(), $addon->get_product_field_schema() );
		}

		$schema->set_prop( 'addons', $addons_schema );
	}

	public function sanitize( $value, $raw_value ) {
		foreach ( $this->get_addons() as $addon ) {
			$value['addons'][ $addon->get_key() ] = $addon->sanitize_in_product_field(
				$value['addons'][ $addon->get_key() ],
				$raw_value['addons'][ $addon->get_key() ] ?? []
			);
		}

		return $value;
	}

	public function validate( $value ): void {
		foreach ( $this->get_addons() as $addon ) {
			$addon->validate_in_product_field( $value['addons'][ $addon->get_key() ] );
		}
	}

	public function update( $value ) {
		foreach ( $this->get_addons() as $addon ) {
			$value['addons'][ $addon->get_key() ] = $addon->update_in_product_field( $value['addons'][ $addon->get_key() ] );
		}

		return $value;
	}

	public function editing_value( $value ) {
		foreach ( $this->get_addons() as $addon ) {
			$value['addons'][ $addon->get_key() ] = $addon->editing_value_in_product_field( $value['addons'][ $addon->get_key() ] );
		}

		return $value;
	}

	public function frontend_props(): array {
		return [
			'addons' => array_map( function( $addon ) {
				return $addon->get_product_field_frontend_config();
			}, $this->get_addons() ),
		];
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
				$addon->set_product_field( $this->product_field );

				$this->addons_cache[ $addon->get_key() ] = $addon;
			}
		}

		return $this->addons_cache;
	}

	public function get_addon( $addon_key ): ?\Voxel\Product_Types\Product_Addons\Base_Addon {
		return $this->get_addons()[ $addon_key ] ?? null;
	}

	public function get_field_templates() {
		$templates = [];
		foreach ( $this->get_addons() as $addon ) {
			if ( $template = locate_template( sprintf( 'templates/widgets/create-post/product-field/addons/%s.php', $addon->get_type() ) ) ) {
				$templates[] = $template;
			}
		}

		return $templates;
	}

}
