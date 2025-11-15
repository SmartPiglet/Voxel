<?php

namespace Voxel\Product_Types\Variations;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Variation_Stock_Field extends Base_Variation_Field {

	protected $props = [
		'key' => 'stock',
		'label' => 'Stock',
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

	public function frontend_props(): array {
		return [
			'sku' => [
				'enabled' => $this->product_type->config('modules.stock.sku.enabled'),
			],
		];
	}
}
