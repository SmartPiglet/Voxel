<?php

namespace Voxel\Product_Types\Product_Fields;

use \Voxel\Utils\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Shipping_Field extends Base_Product_Field {

	protected $props = [
		'key' => 'shipping',
		'label' => 'Shipping',
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
				'value' => [ 'regular', 'variable' ],
			],
			'modules.shipping.enabled' => true,
		];
	}

	public function set_schema( Data_Object $schema ): void {
		$schema->set_prop( 'shipping', Schema::Object( [
			'enabled' => Schema::Bool()->default(false),
			'shipping_class' => Schema::String()->maxlength(32)->default(''),
		] ) );
	}

	public function frontend_props(): array {
		return [
			'required' => $this->product_type->config('modules.shipping.required'),
			'default_shipping_class' => $this->product_type->config('modules.shipping.default_shipping_class'),
			'shipping_classes' => array_map( function( $shipping_class ) {
				return [
					'key' => $shipping_class->get_key(),
					'label' => $shipping_class->get_label(),
					'description' => $shipping_class->get_description(),
				];
			}, \Voxel\Product_Types\Shipping\Shipping_Class::get_all() ),
		];
	}

	public function validate( $value ): void {
		if ( $this->product_type->config('modules.shipping.required') || $value['shipping']['enabled'] ) {
			if ( $value['shipping']['shipping_class'] !== '' ) {
				if ( ! \Voxel\Product_Types\Shipping\Shipping_Class::get( $value['shipping']['shipping_class'] ) ) {
					throw new \Exception( _x( 'Please select a valid shipping class', 'field validation', 'voxel' ) );
				}
			}
		}
	}
}
