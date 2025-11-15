<?php

namespace Voxel\Product_Types\Shipping\Rates;

use Voxel\Utils\Config_Schema\Schema as Schema;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Free_Shipping_Rate extends Base_Shipping_Rate {

	protected
		$requirements,
		$minimum_order_amount;

	protected function init( array $data ): void {
		$schema = Schema::Object( [
			'requirements' => Schema::Enum( [ 'none', 'minimum_order_amount' ] )->default('none'),
			'minimum_order_amount' => Schema::Float()->min(0),
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
		] );

		$schema->set_value( $data['free_shipping'] ?? [] );

		$config = $schema->export();

		$this->requirements = $config['requirements'];
		$this->minimum_order_amount = $config['minimum_order_amount'];

		if ( $config['delivery_estimate']['minimum']['value'] !== null && $config['delivery_estimate']['maximum']['value'] !== null ) {
			$this->min_delivery_unit = $config['delivery_estimate']['minimum']['unit'];
			$this->min_delivery_time = $config['delivery_estimate']['minimum']['value'];
			$this->max_delivery_unit = $config['delivery_estimate']['maximum']['unit'];
			$this->max_delivery_time = $config['delivery_estimate']['maximum']['value'];
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

	/*public function get_order_item_config(): array {
		$config = [
			'type' => $this->get_type(),
			'key' => $this->get_key(),
			'label' => $this->get_label(),
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
