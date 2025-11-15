<?php

namespace Voxel\Product_Types\Shipping\Rates;

use Voxel\Utils\Config_Schema\Schema as Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Fixed_Shipping_Rate extends Base_Shipping_Rate {

	protected
		$tax_behavior,
		$tax_code,
		$amount_per_unit,
		$shipping_classes;

	protected function init( array $data ): void {
		$schema = Schema::Object( [
			'tax_behavior' => Schema::Enum( [ 'default', 'inclusive', 'exclusive' ] )->default('default'),
			'tax_code' => Schema::Enum( [ 'shipping', 'nontaxable' ] )->default('shipping'),
			'delivery_estimate' => Schema::Object( [
				'minimum' => Schema::Object( [
					'unit' => Schema::Enum( [ 'hour', 'day', 'business_day', 'week', 'month' ] )->default('business_day'),
					'value' => Schema::Int()->min(1),
				] ),
				'maximum' => Schema::Object( [
					'unit' => Schema::Enum( [ 'hour', 'day', 'business_day', 'week', 'month' ] )->default('business_day'),
					'value' => Schema::Int()->min(1),
				] ),
			] ),
			'amount_per_unit' => Schema::Float()->min(0)->default(0),
			'shipping_classes' => Schema::Object_List( [
				'shipping_class' => Schema::String(),
				'amount_per_unit' => Schema::Float()->min(0)->default(0),
			] )->default([]),
		] );

		$schema->set_value( $data['fixed_rate'] ?? [] );

		$config = $schema->export();

		$this->tax_behavior = $config['tax_behavior'];
		$this->tax_code = $config['tax_code'];
		if ( $config['delivery_estimate']['minimum']['value'] !== null && $config['delivery_estimate']['maximum']['value'] !== null ) {
			$this->min_delivery_unit = $config['delivery_estimate']['minimum']['unit'];
			$this->min_delivery_time = $config['delivery_estimate']['minimum']['value'];
			$this->max_delivery_unit = $config['delivery_estimate']['maximum']['unit'];
			$this->max_delivery_time = $config['delivery_estimate']['maximum']['value'];
		}

		$this->amount_per_unit = $config['amount_per_unit'];
		$this->shipping_classes = [];
		foreach ( $config['shipping_classes'] as $shipping_class_amount ) {
			$shipping_class = \Voxel\Product_Types\Shipping\Shipping_Class::get( $shipping_class_amount['shipping_class'] );
			if ( ! $shipping_class ) {
				continue;
			}

			$this->shipping_classes[ $shipping_class->get_key() ] = [
				'shipping_class' => $shipping_class,
				'amount_per_unit' => $shipping_class_amount['amount_per_unit'],
			];
		}
	}

	public function get_type(): string {
		return 'fixed_rate';
	}

	public function get_tax_behavior(): ?string {
		return $this->tax_behavior;
	}

	public function get_default_amount_per_unit(): float {
		return $this->amount_per_unit;
	}

	public function get_amount_per_unit_for_shipping_class( string $shipping_class ) {
		return $this->shipping_classes[ $shipping_class ]['amount_per_unit'] ?? $this->amount_per_unit;
	}

	public function get_tax_code(): ?string {
		return $this->tax_code;
	}

	public function get_frontend_config(): array {
		return [
			'type' => $this->get_type(),
			'key' => $this->get_key(),
			'label' => $this->get_label(),
			'delivery_estimate' => $this->get_delivery_estimate_message(),
			'amount_per_unit' => $this->get_default_amount_per_unit(),
			'shipping_classes' => (object) array_map( function( $shipping_class ) {
				return $shipping_class['amount_per_unit'];
			}, $this->shipping_classes ),
		];
	}

	/*public function get_order_item_config(): array {
		$config = [
			'type' => $this->get_type(),
			'key' => $this->get_key(),
			'label' => $this->get_label(),
			'tax_code' => $this->get_tax_code(),
			'tax_behavior' => $this->get_tax_behavior(),
			'amount_per_unit' => $this->get_default_amount_per_unit(),
			'shipping_classes' => (object) array_map( function( $shipping_class ) {
				return $shipping_class['amount_per_unit'];
			}, $this->shipping_classes ),
		];

		if ( $this->has_delivery_estimate() ) {
			$config['delivery_estimate'] = [
				'min_time' => $this->get_minimum_delivery_time(),
				'min_unit' => $this->get_minimum_delivery_unit(),
				'max_time' => $this->get_maximum_delivery_time(),
				'max_unit' => $this->get_maximum_delivery_unit(),
			];
		}

		return $config;
	}*/
}
