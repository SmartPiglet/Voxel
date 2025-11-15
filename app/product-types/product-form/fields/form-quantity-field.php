<?php

namespace Voxel\Product_Types\Product_Form\Fields;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Form_Quantity_Field extends Base_Field {

	protected $props = [
		'key' => 'form-quantity',
		'label' => 'Quantity',
	];

	public function get_conditions(): array {
		return [
			'settings.product_mode' => 'regular',
			'modules.stock.enabled' => true,
		];
	}

	public function set_schema( Data_Object $schema ): void {
		$schema->set_prop( 'stock', Schema::Object( [
			'quantity' => Schema::Int()->min(1)->default(1),
		] ) );
	}

	public function validate( $value ) {
		$config = $this->product_field->get_value();
		if ( $config['stock']['enabled'] ) {
			if ( $config['stock']['sold_individually'] && $value['stock']['quantity'] > 1 ) {
				throw new \Exception( __( 'This product cannot have a quantity larger than 1', 'voxel' ), 101 );
			}

			if ( $value['stock']['quantity'] > $config['stock']['quantity'] ) {
				throw new \Exception( \Voxel\replace_vars( __( 'This product cannot have a quantity larger than @max_quantity', 'voxel' ), [
					'@max_quantity' => $config['stock']['quantity'],
				] ), 101 );
			}
		}
	}

	public function frontend_props(): array {
		$value = $this->product_field->get_value();
		return [
			'enabled' => $value['stock']['enabled'],
			'quantity' => $value['stock']['quantity'],
			'sold_individually' => $value['stock']['sold_individually'],
		];
	}

	public function get_field_templates() {
		$templates = [];
		$templates[] = locate_template( 'templates/widgets/product-form/form-quantity.php' );

		return $templates;
	}
}
