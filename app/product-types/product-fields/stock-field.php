<?php

namespace Voxel\Product_Types\Product_Fields;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Stock_Field extends Base_Product_Field {

	protected $props = [
		'key' => 'stock',
		'label' => 'Stock',
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
			'settings.product_mode' => 'regular',
			'modules.stock.enabled' => true,
		];
	}

	public function set_schema( Data_Object $schema ): void {
		$stock_schema = Schema::Object( [
			'enabled' => Schema::Bool(),
			'quantity' => Schema::Int()->min(0)->default(1),
			'sold_individually' => Schema::Bool(),
		] );

		if ( $this->product_type->config('modules.stock.sku.enabled') ) {
			$stock_schema->set_prop( 'sku', Schema::String() );
		}

		$schema->set_prop( 'stock', $stock_schema );
	}

	public function validate( $value ): void {
		if ( $value['stock']['enabled'] && $value['stock']['quantity'] === null ) {
			throw new \Exception( \Voxel\replace_vars(
				_x( '@field_name: Stock quantity is required', 'field validation', 'voxel' ), [
					'@field_name' => $this->product_field->get_label(),
				]
			) );
		}
	}

	public function frontend_props(): array {
		return [
			'sku' => [
				'enabled' => $this->product_type->config('modules.stock.sku.enabled'),
			],
		];
	}

	public function check_product_form_validity( $value ) {
		if ( $value['stock']['enabled'] && $value['stock']['quantity'] < 1 ) {
			throw new \Exception( _x( 'Product out of stock', 'products', 'voxel' ), \Voxel\PRODUCT_ERR_OUT_OF_STOCK );
		}
	}
}
