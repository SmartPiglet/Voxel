<?php

namespace Voxel\Product_Types\Shipping\Vendor_Rates;

use Voxel\Utils\Config_Schema\Schema as Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Vendor_Free_Shipping_Rate extends Vendor_Base_Shipping_Rate {

	protected
		$requirements,
		$minimum_order_amount;

	protected function init( array $data ): void {
		$this->requirements = $data['free_shipping']['requirements'];
		$this->minimum_order_amount = $data['free_shipping']['minimum_order_amount'];

		if ( $data['free_shipping']['delivery_estimate']['enabled'] ) {
			$this->has_delivery_estimate = true;
			$this->min_delivery_unit = $data['free_shipping']['delivery_estimate']['minimum']['unit'];
			$this->min_delivery_time = $data['free_shipping']['delivery_estimate']['minimum']['value'];
			$this->max_delivery_unit = $data['free_shipping']['delivery_estimate']['maximum']['unit'];
			$this->max_delivery_time = $data['free_shipping']['delivery_estimate']['maximum']['value'];
		}
	}

	public function get_type(): string {
		return 'free_shipping';
	}

	public function get_requirements(): string {
		return $this->requirements;
	}

	public function get_minimum_order_amount(): ?float {
		if ( $this->requirements === 'minimum_order_amount' ) {
			return $this->minimum_order_amount;
		}

		return null;
	}

	public function get_frontend_config(): array {
		return [
			'type' => $this->get_type(),
			'key' => $this->get_key(),
			'label' => $this->get_label(),
			'requirements' => $this->get_requirements(),
			'minimum_order_amount' => $this->get_minimum_order_amount(),
			'delivery_estimate' => $this->get_delivery_estimate_message(),
		];
	}
}
