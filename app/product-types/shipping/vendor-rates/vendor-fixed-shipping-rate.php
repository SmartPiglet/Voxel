<?php

namespace Voxel\Product_Types\Shipping\Vendor_Rates;

use Voxel\Utils\Config_Schema\Schema as Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Vendor_Fixed_Shipping_Rate extends Vendor_Base_Shipping_Rate {

	protected
		$amount_per_unit,
		$shipping_classes;

	protected function init( array $data ): void {
		if ( $data['fixed_rate']['delivery_estimate']['enabled'] ) {
			$this->has_delivery_estimate = true;
			$this->min_delivery_unit = $data['fixed_rate']['delivery_estimate']['minimum']['unit'];
			$this->min_delivery_time = $data['fixed_rate']['delivery_estimate']['minimum']['value'];
			$this->max_delivery_unit = $data['fixed_rate']['delivery_estimate']['maximum']['unit'];
			$this->max_delivery_time = $data['fixed_rate']['delivery_estimate']['maximum']['value'];
		}

		$this->amount_per_unit = $data['fixed_rate']['amount_per_unit'];
		$this->shipping_classes = [];
		foreach ( $data['fixed_rate']['shipping_classes'] as $shipping_class_amount ) {
			$shipping_class = \Voxel\Product_Types\Shipping\Shipping_Class::get( $shipping_class_amount['shipping_class'] );
			if ( $shipping_class ) {
				$this->shipping_classes[ $shipping_class->get_key() ] = [
					'shipping_class' => $shipping_class,
					'amount_per_unit' => $shipping_class_amount['amount_per_unit'],
				];
			}
		}
	}

	public function get_type(): string {
		return 'fixed_rate';
	}

	public function get_default_amount_per_unit(): float {
		return $this->amount_per_unit;
	}

	public function get_amount_per_unit_for_shipping_class( string $shipping_class ) {
		return $this->shipping_classes[ $shipping_class ]['amount_per_unit'] ?? $this->amount_per_unit;
	}

	public function get_tax_code(): ?string {
		return null;
	}

	public function get_tax_behavior(): ?string {
		return null;
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
}
